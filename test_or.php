<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$agent = app(App\Services\AiAgentService::class);
$res = $agent->testResponse('hello');
print_r($res);
