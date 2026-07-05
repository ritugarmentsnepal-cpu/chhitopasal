<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Traits\Loggable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * PHASE-5: Local development sample data. NEVER run in production —
 * guarded against non-local environments.
 *
 * Usage: php artisan db:seed --class=DevSampleSeeder
 */
class DevSampleSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local')) {
            $this->command->error('DevSampleSeeder only runs in the local environment.');
            return;
        }

        Loggable::$suppressLogging = true;

        // Dev admin login: dev@local.test / password
        User::updateOrCreate(
            ['email' => 'dev@local.test'],
            [
                'name' => 'Dev Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $cash = Account::firstOrCreate(['name' => 'Cash'], ['type' => 'cash', 'balance' => 50000]);
        Account::firstOrCreate(['name' => 'Bank - NIC Asia'], ['type' => 'bank', 'balance' => 250000]);

        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command->warn('No products found — create a few products first.');
            Loggable::$suppressLogging = false;
            return;
        }

        $names = ['Sita Sharma', 'Ram Thapa', 'Anita Gurung', 'Bikash KC', 'Puja Shrestha', 'Suresh Magar', 'Gita Rai', 'Dipak Adhikari'];
        $cities = ['Kathmandu', 'Lalitpur', 'Bhaktapur', 'Pokhara', 'Chitwan'];
        $statuses = ['pending', 'pending', 'pending', 'confirmed', 'confirmed', 'shipped', 'shipped', 'delivered', 'delivered', 'delivered', 'return_delivered', 'rejected'];
        $productionStatuses = Order::productionStatuses();

        foreach (range(1, 36) as $i) {
            $isCustom = $i % 3 === 0; // every 3rd order is custom print
            $status = $statuses[array_rand($statuses)];
            $product = $products->random();
            $qty = rand(1, $isCustom ? 30 : 3);
            $price = (float) ($product->price ?? rand(300, 1500));
            $total = $price * $qty;

            $order = Order::create([
                'customer_name' => $names[array_rand($names)],
                'customer_phone' => '98' . rand(10000000, 99999999),
                'address' => 'Ward ' . rand(1, 32) . ', ' . $cities[array_rand($cities)],
                'city' => $cities[array_rand($cities)],
                'total_amount' => $total,
                'delivery_charge' => rand(0, 1) ? 100 : 150,
                'status' => $status,
                'source' => rand(0, 1) ? 'web' : 'manual',
                'order_type' => $isCustom ? 'custom_print' : 'standard',
                'production_status' => $isCustom ? $productionStatuses[array_rand($productionStatuses)] : null,
                'design_notes' => $isCustom ? 'Logo on front pocket, big print on back.' : null,
                'print_method' => $isCustom ? (rand(0, 1) ? 'dtf' : 'embroidery') : null,
                'paid_amount' => in_array($status, ['delivered']) ? $total : ($isCustom ? round($total * 0.5) : 0),
                'payment_status' => in_array($status, ['delivered']) ? 'paid' : ($isCustom ? 'partial' : 'unpaid'),
                'shipped_at' => in_array($status, ['shipped', 'delivered', 'return_delivered']) ? now()->subDays(rand(1, 20)) : null,
                'pathao_consignment_id' => in_array($status, ['shipped', 'delivered']) ? 'DT' . rand(100000, 999999) : null,
                'pathao_status' => in_array($status, ['shipped', 'delivered']) ? ($status === 'delivered' ? 'Delivered' : 'In Transit') : null,
                'created_at' => now()->subDays(rand(0, 30))->subMinutes(rand(0, 1400)),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'price_at_purchase' => $price,
                'cost_at_purchase' => round($price * 0.6),
                'color' => rand(0, 1) ? 'Black' : 'White',
                'size' => ['S', 'M', 'L', 'XL'][array_rand(['S', 'M', 'L', 'XL'])],
            ]);
        }

        Loggable::$suppressLogging = false;

        $this->command->info('Seeded 36 sample orders (12 custom print), dev admin dev@local.test / password.');
    }
}
