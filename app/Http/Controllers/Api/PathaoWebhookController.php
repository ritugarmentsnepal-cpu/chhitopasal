<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Services\OrderService;

class PathaoWebhookController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function handle(Request $request)
    {
        // SEC: secret lives in Settings → Integrations (encrypted at rest),
        // with .env as fallback. No hardcoded fallback secrets.
        $configuredSecret = setting('pathao_webhook_secret') ?: config('services.pathao.webhook_secret');

        Log::info('Pathao Webhook Received', $request->all());

        // Handle Pathao's initial webhook test ping FIRST, before security checks
        if ($request->input('event') === 'webhook_integration') {
            $secretToReturn = $configuredSecret
                              ?: $request->header('X-Pathao-Merchant-Webhook-Integration-Secret');

            return response()->json(['status' => 'success', 'message' => 'Integration verified'])
                             ->setStatusCode(202)
                             ->header('X-Pathao-Merchant-Webhook-Integration-Secret', $secretToReturn);
        }

        // SEC: without a configured secret anyone can flip order statuses —
        // warn loudly so it gets set. With one configured, enforce it.
        if (!$configuredSecret) {
            Log::warning('Pathao Webhook: no webhook secret configured — request accepted UNVERIFIED. Set one in Settings → Integrations.');
        } else {
            $providedSecret = $request->header('X-Webhook-Secret')
                           ?? $request->header('X-PATHAO-Signature')
                           ?? $request->header('X-Pathao-Merchant-Webhook-Integration-Secret');

            if (!is_string($providedSecret) || !hash_equals($configuredSecret, $providedSecret)) {
                Log::warning('Pathao Webhook Unauthorized access attempt', [
                    'ip' => $request->ip(),
                ]);
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        $consignmentId = $request->input('consignment_id');
        $orderStatus = $request->input('order_status');
        $eventLower = strtolower($request->input('event') ?? '');
        
        if (!$consignmentId) {
            if ($eventLower !== 'webhook_integration') {
                return response()->json(['message' => 'Invalid payload: missing consignment_id'], 400);
            }
        }

        // Find the order
        $order = Order::where('pathao_consignment_id', $consignmentId)->first();

        if (!$order && $eventLower !== 'webhook_integration') {
            Log::warning("Pathao Webhook: Order not found for consignment_id: {$consignmentId}");
            return response()->json(['message' => 'Order not found'], 404);
        }

        $issueId = $request->input('issue_id') ?? $request->input('id') ?? ('issue_' . time());
        $commentText = $request->input('issue_description') ?? $request->input('reason') ?? $request->input('comment');

        // If it's an issue event or it contains a reason/comment, log it
        if ($order && ($eventLower === 'issue' || !empty($commentText))) {
            // Default to json if we know it's an issue but text is empty
            if (empty($commentText) && $eventLower === 'issue') {
                $commentText = json_encode($request->all());
            }
            
            if (!empty($commentText)) {
                \App\Models\RiderComment::updateOrCreate(
                    ['order_id' => $order->id, 'pathao_issue_id' => $issueId],
                    ['rider_comment' => $commentText, 'status' => 'unread']
                );
            }

            if ($eventLower === 'issue') {
                return response()->json(['status' => 'success'])
                                 ->setStatusCode(202)
                                 ->header('X-Pathao-Merchant-Webhook-Integration-Secret', $configuredSecret);
            }
        }

        try {
            // Map the Pathao status to our internal status logic
            $statusLower = strtolower($orderStatus);
            $newStatus = null;

            if ($statusLower === 'delivered') {
                $newStatus = 'delivered';
            } elseif (in_array($statusLower, ['return', 'returned'])) {
                $newStatus = 'return_delivered';
            } elseif (in_array($statusLower, ['cancel', 'cancelled'])) {
                $newStatus = 'rejected';
            }

            if ($newStatus && $order->status !== $newStatus) {
                Log::info("Pathao Webhook: Updating order {$order->id} from {$order->status} to {$newStatus}");
                
                // Suppress logging here so we don't spam the activity log with "System updated order..." for every webhook
                // if not desired, or let it log to show it was automated
                $this->orderService->transitionStatus($order, $newStatus);
            }

            // Always update the raw pathao status if they provide it
            $order->update([
                'pathao_status' => $orderStatus,
                'pathao_status_updated_at' => now(),
            ]);

            return response()->json(['status' => 'success'])
                             ->setStatusCode(202)
                             ->header('X-Pathao-Merchant-Webhook-Integration-Secret', $configuredSecret);

        } catch (\Exception $e) {
            Log::error("Pathao Webhook Processing Error: " . $e->getMessage(), [
                'order_id' => $order->id,
                'consignment_id' => $consignmentId
            ]);
            
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
