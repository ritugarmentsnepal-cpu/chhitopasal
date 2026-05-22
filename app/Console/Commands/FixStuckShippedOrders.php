<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixStuckShippedOrders extends Command
{
    protected $signature = 'orders:fix-stuck-shipped';
    protected $description = 'One-time fix: transition orders stuck as "shipped" when Pathao already reports delivered/returned/cancelled';

    public function handle(OrderService $orderService)
    {
        $this->info('Scanning for stuck shipped orders...');

        $orders = Order::with('orderItems.product')
            ->where('status', 'shipped')
            ->whereNotNull('pathao_status')
            ->where('pathao_status', '!=', '')
            ->get();

        $this->info("Found {$orders->count()} shipped orders with a Pathao status.");

        $fixed = 0;
        $skipped = 0;

        foreach ($orders as $order) {
            $normalizedStatus = strtolower($order->pathao_status);
            $newLocalStatus = null;

            if (in_array($normalizedStatus, ['delivered', 'successful'])) {
                $newLocalStatus = 'delivered';
            } elseif (in_array($normalizedStatus, ['returned', 'return'])) {
                $newLocalStatus = 'return_delivered';
            } elseif (in_array($normalizedStatus, ['cancelled', 'cancel', 'pickup cancel', 'pickup cancelled'])) {
                $newLocalStatus = 'rejected';
            }

            if ($newLocalStatus) {
                try {
                    DB::transaction(function () use ($order, $newLocalStatus, $orderService) {
                        $orderService->transitionStatus($order, $newLocalStatus);
                    });
                    $this->info("  ✓ Order #{$order->id}: shipped → {$newLocalStatus} (Pathao: {$order->pathao_status})");
                    $fixed++;
                } catch (\Exception $e) {
                    $this->error("  ✗ Order #{$order->id}: {$e->getMessage()}");
                    $skipped++;
                }
            } else {
                $skipped++;
            }
        }

        $this->info("Done. Fixed: {$fixed}, Skipped: {$skipped} (still in-transit).");
    }
}
