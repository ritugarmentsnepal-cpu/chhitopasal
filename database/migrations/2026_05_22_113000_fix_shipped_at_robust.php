<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix shipped_at backfill using multiple strategies:
 * 1. Activity logs (for individually shipped orders)
 * 2. pathao_status_updated_at (for bulk-shipped orders with pathao tracking)
 * 3. created_at fallback (for everything else - better than updated_at which changes on every sync)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Strategy 1: Use activity_logs for orders that have them
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

        // Strategy 2: For bulk-shipped orders (no activity log) that have pathao tracking,
        // use pathao_status_updated_at as a proxy for ship date
        DB::table('orders')
            ->whereIn('status', ['shipped', 'delivered', 'failed', 'rejected', 'return_delivered'])
            ->whereNotNull('pathao_status_updated_at')
            ->whereColumn('shipped_at', 'updated_at')
            ->update(['shipped_at' => DB::raw('pathao_status_updated_at')]);

        // Strategy 3: For any remaining orders still using updated_at,
        // fall back to created_at (conservative but won't show wrong dates)
        DB::table('orders')
            ->whereIn('status', ['shipped', 'delivered', 'failed', 'rejected', 'return_delivered'])
            ->whereColumn('shipped_at', 'updated_at')
            ->update(['shipped_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        // No reliable way to revert — leave as-is
    }
};
