<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pathao = new \App\Services\PathaoService();
try {
    $reflection = new ReflectionClass($pathao);
    $method = $reflection->getMethod('getAccessToken');
    $method->setAccessible(true);
    
    $token = $method->invoke($pathao);
    echo "Token retrieved successfully: " . substr($token, 0, 10) . "...\n";
    
    $cities = $pathao->getCities();
    echo "Cities fetched: " . count($cities) . "\n";
    if (count($cities) > 0) {
        echo "First city: " . $cities[0]['city_name'] . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
