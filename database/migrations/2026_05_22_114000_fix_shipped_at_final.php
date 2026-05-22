<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Final fix for shipped_at timestamps.
 *
 * Previous migrations used updated_at and pathao_status_updated_at which are
 * unreliable (both change on every Pathao sync). This migration:
 * 1. Resets all shipped_at to created_at as a safe baseline
 * 2. Overwrites with exact activity_log timestamps where available
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Reset ALL shipped orders to created_at as baseline
        // This is safe — created_at never changes and is a reasonable lower bound
        DB::table('orders')
            ->whereIn('status', ['shipped', 'delivered', 'failed', 'rejected', 'return_delivered'])
            ->update(['shipped_at' => DB::raw('created_at')]);

        // Step 2: Overwrite with exact timestamps from activity_logs
        // These are the ground-truth timestamps for individually shipped orders
        $logs = DB::table('activity_logs')
            ->where('model_type', 'App\\Models\\Order')
            ->where('action', 'updated')
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($logs as $log) {
            $details = json_decode($log->details, true);
            if (!$details) continue;

            $newStatus = $details['new']['status'] ?? null;
            if ($newStatus !== 'shipped') continue;

            DB::table('orders')
                ->where('id', $log->model_id)
                ->update(['shipped_at' => $log->created_at]);
        }
    }

    public function down(): void
    {
        // No reliable revert possible
    }
};
