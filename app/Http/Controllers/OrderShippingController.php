<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\PathaoService;
use App\Services\OrderService;
use App\SystemAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * PHASE-1.5: Pathao shipping operations, split from OrderController.
 */
class OrderShippingController extends Controller
{
    public function shipWithPathao(Request $request, Order $order, PathaoService $pathao)
    {
        if ($order->status !== 'confirmed') {
            return redirect()->back()->with('error', 'Only confirmed orders can be shipped.');
        }

        if (!$order->pathao_city_id || !$order->pathao_zone_id) {
            return redirect()->back()->with('error', 'Pathao location IDs are missing. Please edit the order to set the location.');
        }

        $result = $pathao->createOrder($order);

        if ($result['success']) {
            // NEW-FIN-03: Use OrderService for atomic stock deduction
            DB::transaction(function () use ($order, $result) {
                $order->update(['pathao_consignment_id' => $result['consignment_id']]);
                app(OrderService::class)->transitionStatus($order, 'shipped');
            });

            $printType = $request->input('print_type', 'both');
            return redirect()->route('orders.printLabel', ['order' => $order->id, 'type' => $printType])->with('success', 'Order shipped via Pathao! Consignment ID: ' . $result['consignment_id']);
        }

        return redirect()->back()->with('error', 'Pathao Error: ' . ($result['error'] ?? 'Unknown error'));
    }

    public function syncPathaoStatus(Order $order, PathaoService $pathao)
    {
        if (!$order->pathao_consignment_id) {
            return redirect()->back()->with('error', 'Order has no Pathao consignment ID.');
        }

        // Force-refresh: user explicitly requested a sync, bypass the 2-min cache
        $status = $pathao->getOrderStatus($order->pathao_consignment_id, true);

        if (!$status) {
            return redirect()->back()->with('error', 'Could not fetch status from Pathao.');
        }

        $normalizedStatus = strtolower($status);
        $newLocalStatus = $order->status;

        // Always save the raw Pathao status for badge display
        $order->update([
            'pathao_status' => $status,
            'pathao_status_updated_at' => now(),
        ]);

        if (in_array($normalizedStatus, ['delivered', 'successful'])) {
            $newLocalStatus = 'delivered';
        } elseif (in_array($normalizedStatus, ['returned', 'return'])) {
            $newLocalStatus = 'return_delivered';
        } elseif (in_array($normalizedStatus, ['cancelled', 'cancel', 'pickup cancel', 'pickup cancelled'])) {
            $newLocalStatus = 'rejected';
        }

        if ($newLocalStatus !== $order->status) {
            $orderService = app(OrderService::class);
            try {
                DB::transaction(function () use ($order, $newLocalStatus, $orderService) {
                    $orderService->transitionStatus($order, $newLocalStatus);
                });
            } catch (\InvalidArgumentException $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
            return redirect()->back()->with('success', 'Pathao status synced successfully. Order is now ' . $newLocalStatus);
        }

        return redirect()->back()->with('success', 'Pathao status is still ' . $status);
    }

    public function masterSyncPathao()
    {
        set_time_limit(600); // 10 minutes max for force sync

        // MED-05: Cooldown lock to prevent Pathao API flooding
        $lockKey = 'pathao_master_sync_lock';
        if (\Illuminate\Support\Facades\Cache::has($lockKey)) {
            return redirect()->back()->with('error', 'Please wait at least 5 minutes between sync operations.');
        }
        \Illuminate\Support\Facades\Cache::put($lockKey, true, now()->addMinutes(5));

        $exitCode = \Illuminate\Support\Facades\Artisan::call('pathao:sync', ['--force' => true]);
        
        if ($exitCode !== 0) {
            return redirect()->back()->with('error', \Illuminate\Support\Facades\Artisan::output());
        }
        
        return redirect()->back()->with('success', 'Master sync completed successfully. ' . \Illuminate\Support\Facades\Artisan::output());
    }

    public function getPathaoDetails(Order $order, \App\Services\PathaoService $pathao)
    {
        if (!$order->pathao_consignment_id) {
            return response()->json(['error' => 'No Pathao consignment ID found'], 404);
        }

        // Check if data is already in cache (to avoid updating timestamp for stale data)
        $cacheKey = "pathao_order_{$order->pathao_consignment_id}";
        $wasCached = \Illuminate\Support\Facades\Cache::has($cacheKey);

        // Use cached data (2-min TTL) — does not hit Pathao API on every click
        $pathaoData = $pathao->getOrderDetails($order->pathao_consignment_id);
        
        // Only update DB timestamp when we actually fetched fresh data from Pathao
        if (!$wasCached && $pathaoData && isset($pathaoData['order_status'])) {
            $order->update([
                'pathao_status' => $pathaoData['order_status'],
                'pathao_status_updated_at' => now(),
            ]);

            // BUG-FIX: Also transition local status when Pathao reports a terminal state
            // This ensures delivered/returned orders leave the shipped list immediately
            $nonTerminalStatuses = ['shipped', 'delivered', 'failed'];
            if (in_array($order->status, $nonTerminalStatuses)) {
                $normalizedStatus = strtolower($pathaoData['order_status']);
                $newLocalStatus = null;

                if (in_array($normalizedStatus, ['delivered', 'successful'])) {
                    $newLocalStatus = 'delivered';
                } elseif (in_array($normalizedStatus, ['returned', 'return'])) {
                    $newLocalStatus = 'return_delivered';
                } elseif (in_array($normalizedStatus, ['cancelled', 'cancel', 'pickup cancel', 'pickup cancelled'])) {
                    $newLocalStatus = 'rejected';
                }

                if ($newLocalStatus) {
                    $orderService = app(OrderService::class);
                    try {
                        DB::transaction(function () use ($order, $newLocalStatus, $orderService) {
                            $orderService->transitionStatus($order, $newLocalStatus);
                        });
                    } catch (\InvalidArgumentException $e) {
                        Log::warning("Auto-transition failed for Order #{$order->id}: " . $e->getMessage());
                    }
                }
            }
        }

        // Build response combining local + Pathao data
        $order->load('orderItems.product');
        
        return response()->json([
            'order' => [
                'id' => $order->id,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'address' => $order->address,
                'city' => $order->city,
                'total_amount' => $order->total_amount,
                'delivery_charge' => $order->delivery_charge,
                'paid_amount' => $order->paid_amount ?? 0,
                'payment_status' => $order->payment_status,
                'pathao_consignment_id' => $order->pathao_consignment_id,
                'created_at' => $order->created_at->format('M d, Y g:i A'),
                'shipped_date' => ($order->shipped_at ?? $order->created_at)->format('M d, Y'),
                'items' => $order->orderItems->map(fn($item) => [
                    'name' => $item->product->name ?? 'Unknown Product',
                    'quantity' => $item->quantity,
                    'price' => $item->price_at_purchase,
                    'total' => $item->quantity * $item->price_at_purchase,
                ]),
                'item_count' => $order->orderItems->sum('quantity'),
                'weight_kg' => max(0.5, $order->orderItems->sum(fn($item) => ($item->product->weight_grams ?? 500) * $item->quantity) / 1000),
            ],
            'pathao' => $pathaoData ? [
                'status' => $pathaoData['order_status'] ?? $order->pathao_status ?? 'Unknown',
                'invoice' => $pathaoData['invoice_id'] ?? null,
                'cod_amount' => $pathaoData['amount_to_collect'] ?? null,
                'delivery_fee' => $pathaoData['delivery_fee'] ?? null,
                'cod_charge' => $pathaoData['cod_fee'] ?? null,
                'created_at' => $pathaoData['created_at'] ?? null,
                'updated_at' => $pathaoData['updated_at'] ?? null,
                'picked_at' => $pathaoData['picked_at'] ?? null,
                'delivered_at' => $pathaoData['delivered_at'] ?? null,
                'rider_name' => $pathaoData['delivery_man_name'] ?? $pathaoData['rider_name'] ?? null,
                'rider_phone' => $pathaoData['delivery_man_phone'] ?? $pathaoData['rider_phone'] ?? null,
                'comments' => $pathaoData['comments'] ?? $pathaoData['order_comment'] ?? $pathaoData['special_instruction'] ?? null,
                'failed_reason' => $pathaoData['failed_delivery_comment'] ?? $pathaoData['failed_reason'] ?? null,
            ] : [
                'status' => $order->pathao_status ?? 'Awaiting Pickup',
                'invoice' => null,
                'cod_amount' => null,
                'delivery_fee' => null,
                'cod_charge' => null,
                'created_at' => null,
                'updated_at' => null,
                'picked_at' => null,
                'delivered_at' => null,
                'rider_name' => null,
                'rider_phone' => null,
                'comments' => null,
                'failed_reason' => null,
            ],
            'status_updated_at' => $order->pathao_status_updated_at ? \Carbon\Carbon::parse($order->pathao_status_updated_at)->diffForHumans() : 'Never',
        ]);
    }

    // ── Custom Print Orders ─────────────────────────────────────────────

}
