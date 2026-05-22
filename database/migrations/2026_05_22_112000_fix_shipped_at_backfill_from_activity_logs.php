<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix shipped_at backfill: use activity_logs to find the actual timestamp
 * when each order transitioned to 'shipped', instead of updated_at which
 * gets modified by Pathao syncs and other updates.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Find the actual shipped timestamp from activity_logs for each order
        // The activity log records status changes with old/new values in details JSON
        $shippedLogs = DB::table('activity_logs')
            ->where('model_type', 'App\\Models\\Order')
            ->where('action', 'updated')
            ->where('details', 'like', '%"shipped"%')
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($shippedLogs as $log) {
            $details = json_decode($log->details, true);

            // Check if this log entry represents a transition TO shipped
            $newStatus = $details['new']['status'] ?? null;
            if ($newStatus !== 'shipped') {
                continue;
            }

            // Update the order's shipped_at with the activity log timestamp
            DB::table('orders')
                ->where('id', $log->model_id)
                ->update(['shipped_at' => $log->created_at]);
        }
    }

    public function down(): void
    {
        // Revert to updated_at as fallback
        DB::table('orders')
            ->whereIn('status', ['shipped', 'delivered', 'failed', 'rejected', 'return_delivered'])
            ->whereNotNull('shipped_at')
            ->update(['shipped_at' => DB::raw('updated_at')]);
    }
};
