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
        $configuredSecret = config('services.pathao.webhook_secret');
        
        // Very basic security: if a secret is configured, ensure it matches either a header or a query param.
        if ($configuredSecret) {
            $providedSecret = $request->header('X-Webhook-Secret') 
                           ?? $request->header('X-PATHAO-Signature') 
                           ?? $request->header('X-Pathao-Merchant-Webhook-Integration-Secret')
                           ?? $request->query('secret')
                           ?? $request->input('secret');

            if ($providedSecret !== $configuredSecret) {
                Log::warning('Pathao Webhook Unauthorized access attempt', [
                    'ip' => $request->ip(),
                    'provided_secret' => $providedSecret
                ]);
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        Log::info('Pathao Webhook Received', $request->all());

        // Handle Pathao's initial webhook test ping
        if ($request->input('event') === 'webhook_integration') {
            return response()->json(['status' => 'success', 'message' => 'Integration verified'])
                             ->setStatusCode(202)
                             ->header('X-Pathao-Merchant-Webhook-Integration-Secret', $configuredSecret);
        }

        $consignmentId = $request->input('consignment_id');
        $orderStatus = $request->input('order_status');
        
        if (!$consignmentId || !$orderStatus) {
            return response()->json(['message' => 'Invalid payload: missing consignment_id or order_status'], 400);
        }

        // Find the order
        $order = Order::where('pathao_consignment_id', $consignmentId)->first();

        if (!$order) {
            Log::warning("Pathao Webhook: Order not found for consignment_id: {$consignmentId}");
            return response()->json(['message' => 'Order not found'], 404);
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
