<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::first();
auth()->login($user);

$response = $kernel->handle(Illuminate\Http\Request::create('/orders?order_type=custom_print', 'GET'));
echo $response->getStatusCode() . "\n";
if ($response->getStatusCode() == 500) {
    if (isset($response->exception)) {
        echo $response->exception->getMessage() . "\n";
        echo $response->exception->getFile() . " on line " . $response->exception->getLine() . "\n";
    }
}
