<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixShippedAtTimestamps extends Command
{
    protected $signature = 'orders:fix-shipped-at';
    protected $description = 'Reset all shipped_at to NULL for a clean start. Going forward, transitionStatus sets it correctly.';

    public function handle()
    {
        $count = DB::table('orders')
            ->whereNotNull('shipped_at')
            ->update(['shipped_at' => null]);

        $this->info("Reset shipped_at to NULL for {$count} orders. All future shipments will record the correct date.");

        return 0;
    }
}
