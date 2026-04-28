<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Neon Buds Pro',
                'description' => 'True wireless earbuds with active noise cancellation and a glowing neon charging case. Perfect for your daily commute.',
                'price' => 4500,
                'weight_grams' => 150,
                'stock' => 50,
                'image_path' => 'products/earbuds.png',
                'category_id' => 5, // Accessories
            ],
            [
                'name' => 'Electric Vault 20k',
                'description' => 'High-capacity 20,000mAh powerbank with 100W PD fast charging. Sleek black design with neon accents.',
                'price' => 3200,
                'weight_grams' => 450,
                'stock' => 100,
                'image_path' => 'products/powerbank.png',
                'category_id' => 5, // Accessories
            ],
            [
                'name' => 'Pulse Watch X',
                'description' => 'Futuristic smartwatch with a vibrant AMOLED screen, heart rate monitoring, and 14-day battery life.',
                'price' => 8500,
                'weight_grams' => 80,
                'stock' => 30,
                'image_path' => 'products/smartwatch.png',
                'category_id' => 5, // Accessories
            ],
            [
                'name' => 'HyperCharge 65W',
                'description' => 'Premium 65W GaN fast charger adapter with a durable braided Type-C cable included.',
                'price' => 2100,
                'weight_grams' => 120,
                'stock' => 200,
                'image_path' => 'products/charger.png',
                'category_id' => 5, // Accessories
            ]
        ];

        foreach ($products as $product) {
            $product['slug'] = Str::slug($product['name']);
            Product::create($product);
        }
    }
}
