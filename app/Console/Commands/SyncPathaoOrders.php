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
        $processed = 0;
        $maxPerRun = 30;      // Cap per cycle — runs every 5 min, so 360 orders/hour
        $maxErrors = 5;       // Abort threshold — if >5 failures, Pathao is likely rate-limiting

        // Sync stalest orders first — prioritize orders that haven't been checked recently
        $orders = Order::with('orderItems.product')
            ->where('status', 'shipped')
            ->whereNotNull('pathao_consignment_id')
            ->orderBy('pathao_status_updated_at', 'asc')
            ->orderBy('updated_at', 'asc')
            ->limit($maxPerRun)
            ->get();

        $this->info("Found {$orders->count()} shipped orders to sync (max {$maxPerRun} per run).");

        foreach ($orders as $order) {
            try {
                // Always force-refresh: sync command must get live data, not cached
                $status = $pathao->getOrderStatus($order->pathao_consignment_id, true);

                if (!$status) {
                    $errors++;
                    $this->warn("Failed to fetch status for Order #{$order->id}");

                    // Abort if too many total errors — likely rate-limited
                    if ($errors >= $maxErrors) {
                        Log::warning("Pathao sync aborted after {$errors} errors — likely rate-limited. Will retry next cycle.");
                        $this->error("Sync aborted — {$errors} errors detected (likely rate-limited). Will retry next cycle.");
                        break;
                    }
                    continue;
                }

                $processed++;

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

                if ($errors >= $maxErrors) {
                    Log::warning("Pathao sync aborted after {$errors} errors — likely rate-limited.");
                    $this->error("Sync aborted — {$errors} errors. Will retry next cycle.");
                    break;
                }
            }

            // Rate limit: 2 second delay between Pathao API calls
            sleep(2);
        }

        $this->info("Sync completed. Processed: {$processed}, Updated: {$count}, Errors: {$errors}.");
    }
}
