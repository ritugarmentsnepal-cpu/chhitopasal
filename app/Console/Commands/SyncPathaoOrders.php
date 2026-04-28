<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\PathaoService;
use Illuminate\Http\Request;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Log;

class SyncPathaoOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pathao:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically sync shipped orders with Pathao tracking status';

    /**
     * Execute the console command.
     */
    public function handle(PathaoService $pathao, OrderController $orderController)
    {
        $this->info('Starting Pathao status sync...');

        // Fetch all shipped orders that have a consignment ID
        $orders = Order::where('status', 'shipped')
                       ->whereNotNull('pathao_consignment_id')
                       ->get();

        $count = 0;
        $errors = 0;

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
                $newLocalStatus = $order->status;

                // Map Pathao's status strings to our local database statuses
                if (in_array($normalizedStatus, ['delivered', 'successful'])) {
                    $newLocalStatus = 'delivered';
                } elseif (in_array($normalizedStatus, ['returned', 'return'])) {
                    $newLocalStatus = 'return_delivered';
                } elseif (in_array($normalizedStatus, ['cancelled', 'cancel', 'pickup cancel', 'pickup cancelled'])) {
                    $newLocalStatus = 'rejected';
                }

                if ($newLocalStatus !== $order->status) {
                    // Re-use the controller's logic to handle stock, finance, and status update
                    $request = new Request(['status' => $newLocalStatus]);
                    $orderController->updateStatus($request, $order);
                    
                    $this->info("Updated Order #{$order->id} from shipped to {$newLocalStatus}.");
                    $count++;
                }

            } catch (\Exception $e) {
                Log::error("Pathao Sync Error for Order #{$order->id}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("Sync completed. Updated {$count} orders. Encountered {$errors} errors.");
    }
}
