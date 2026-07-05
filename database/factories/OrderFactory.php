<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'customer_phone' => '98' . fake()->numberBetween(10000000, 99999999),
            'address' => fake()->streetAddress(),
            'city' => 'Kathmandu',
            'total_amount' => 2000,
            'delivery_charge' => 100,
            'status' => 'pending',
            'source' => 'manual',
            'order_type' => 'standard',
            'payment_status' => 'unpaid',
            'paid_amount' => 0,
        ];
    }

    public function customPrint(): static
    {
        return $this->state(fn () => ['order_type' => 'custom_print']);
    }
}
