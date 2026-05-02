<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActivityLog;

class PruneActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:prune {--days=90 : Number of days to retain}';

    /**
     * The console command description.
     */
    protected $description = 'Delete activity log entries older than the specified number of days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');

        if ($days < 7) {
            $this->error('Minimum retention period is 7 days.');
            return 1;
        }

        $cutoff = now()->subDays($days);
        $deleted = ActivityLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} activity log entries older than {$days} days.");
        return 0;
    }
}
