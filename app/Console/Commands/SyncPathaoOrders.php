<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\PathaoService;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncPathaoOrders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pathao:sync';

    /**
     * The console command description.
     */
    protected $description = 'Automatically sync shipped orders with Pathao tracking status';

    /**
     * INT-01: Uses OrderService instead of OrderController for CLI-safe execution.
     */
    public function handle(PathaoService $pathao, OrderService $orderService)
    {
        $this->info('Starting Pathao status sync...');

        $count = 0;
        $errors = 0;

        // PERF-BUG-04: Use chunk() to avoid loading all shipped orders into memory
        Order::with('orderItems.product')
            ->whereIn('status', ['shipped', 'delivered'])
            ->whereNotNull('pathao_consignment_id')
            ->chunk(50, function ($orders) use ($pathao, $orderService, &$count, &$errors) {
                foreach ($orders as $order) {
                    try {
                        $status = $pathao->getOrderStatus($order->pathao_consignment_id);

                        if (!$status) {
                            $errors++;
                            continue;
                        }

                        // Always save the raw Pathao status for display
                        $order->update([
                            'pathao_status' => $status,
                            'pathao_status_updated_at' => now(),
                        ]);

                        $normalizedStatus = strtolower($status);
                        $newLocalStatus = null;

                        // Map Pathao's status strings to our local database statuses
                        if (in_array($normalizedStatus, ['delivered', 'successful'])) {
                            $newLocalStatus = 'delivered';
                        } elseif (in_array($normalizedStatus, ['returned', 'return'])) {
                            $newLocalStatus = 'return_delivered';
                        } elseif (in_array($normalizedStatus, ['cancelled', 'cancel', 'pickup cancel', 'pickup cancelled'])) {
                            $newLocalStatus = 'rejected';
                        }

                        if ($newLocalStatus && $newLocalStatus !== $order->status) {
                            DB::transaction(function () use ($order, $newLocalStatus, $orderService) {
                                $orderService->transitionStatus($order, $newLocalStatus);
                            });

                            $this->info("Updated Order #{$order->id} from {$order->status} to {$newLocalStatus}.");
                            $count++;
                        }

                    } catch (\Exception $e) {
                        Log::error("Pathao Sync Error for Order #{$order->id}: " . $e->getMessage());
                        $errors++;
                    }

                    // INT-01: Rate limit API calls — 200ms delay between requests
                    usleep(200000);
                }
            });

        $this->info("Sync completed. Updated {$count} orders. Encountered {$errors} errors.");
    }
}
