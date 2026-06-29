<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = App\Models\User::first();
    if ($user) {
        auth()->login($user);
    }
    $html = view('orders.index', [
        'orders' => App\Models\Order::where('order_type', 'custom_print')->paginate(20),
        'status' => 'pending',
        'orderType' => 'custom_print',
        'products' => App\Models\Product::all(),
        'accounts' => \App\Models\Account::all()
    ])->render();
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
