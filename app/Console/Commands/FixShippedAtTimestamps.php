<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixShippedAtTimestamps extends Command
{
    protected $signature = 'orders:fix-shipped-at';
    protected $description = 'Fix shipped_at timestamps using activity logs, with created_at as fallback for bulk-shipped orders';

    public function handle()
    {
        $this->info('Fixing shipped_at timestamps...');

        // Strategy 1: Use activity_logs for orders that have them
        $fixedFromLogs = 0;
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

            $affected = DB::table('orders')
                ->where('id', $log->model_id)
                ->update(['shipped_at' => $log->created_at]);

            if ($affected) $fixedFromLogs++;
        }

        $this->info("Fixed {$fixedFromLogs} orders from activity logs.");

        // Strategy 2: For remaining orders (bulk-shipped, no activity log),
        // find the earliest pathao_status_updated_at as a proxy for when they were shipped
        $fixedFromPathao = DB::table('orders')
            ->whereIn('status', ['shipped', 'delivered', 'failed', 'rejected', 'return_delivered'])
            ->whereNotNull('pathao_status_updated_at')
            ->whereColumn('shipped_at', 'updated_at') // still has the wrong backfill value
            ->update(['shipped_at' => DB::raw('pathao_status_updated_at')]);

        $this->info("Fixed {$fixedFromPathao} orders from pathao_status_updated_at.");

        // Strategy 3: For any remaining orders with no activity log AND no pathao timestamps,
        // use created_at as a conservative fallback (better than updated_at which changes on every sync)
        $fixedFromCreated = DB::table('orders')
            ->whereIn('status', ['shipped', 'delivered', 'failed', 'rejected', 'return_delivered'])
            ->whereColumn('shipped_at', 'updated_at') // still has the wrong backfill value
            ->update(['shipped_at' => DB::raw('created_at')]);

        $this->info("Fixed {$fixedFromCreated} orders using created_at as fallback.");

        $total = $fixedFromLogs + $fixedFromPathao + $fixedFromCreated;
        $this->info("Done! Total fixed: {$total}");

        return 0;
    }
}
