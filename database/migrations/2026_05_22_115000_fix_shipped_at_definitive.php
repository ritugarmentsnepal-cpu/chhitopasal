<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Final definitive fix for shipped_at timestamps.
 *
 * Uses 3 strategies in order of reliability:
 * 1. Activity logs showing status -> shipped (best: exact timestamp)
 * 2. Activity logs showing pathao_consignment_id being set (good: ship time proxy)
 * 3. Leave as created_at for truly unknown orders (already set by previous migration)
 *
 * DOES NOT touch orders where shipped_at was correctly set by
 * transitionStatus() after the code fix was deployed.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Collect the best shipped_at for each order from activity logs
        $orderShippedAt = [];

        $logs = DB::table('activity_logs')
            ->where('model_type', 'App\\Models\\Order')
            ->where('action', 'updated')
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($logs as $log) {
            $details = json_decode($log->details, true);
            if (!$details) continue;

            $orderId = $log->model_id;

            // Strategy 1: Direct status transition to shipped
            $newStatus = $details['new']['status'] ?? null;
            if ($newStatus === 'shipped') {
                $orderShippedAt[$orderId] = $log->created_at;
                continue;
            }

            // Strategy 2: pathao_consignment_id was set (happens right before shipping)
            // Only use if we don't already have a better timestamp
            if (!isset($orderShippedAt[$orderId])) {
                $newConsignment = $details['new']['pathao_consignment_id'] ?? null;
                $oldConsignment = $details['old']['pathao_consignment_id'] ?? null;
                if ($newConsignment && !$oldConsignment) {
                    $orderShippedAt[$orderId] = $log->created_at;
                }
            }
        }

        // Apply the fixes — only update orders where shipped_at equals created_at
        // (meaning it was set by the previous fallback migration, not by transitionStatus)
        foreach ($orderShippedAt as $orderId => $shippedAt) {
            DB::table('orders')
                ->where('id', $orderId)
                ->whereColumn('shipped_at', 'created_at')
                ->update(['shipped_at' => $shippedAt]);
        }
    }

    public function down(): void
    {
        // No revert needed
    }
};
