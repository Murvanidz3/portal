<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

// Manually verify user login simulation
$userId = 1;

// Simulate request
$request = Request::create('/calculator/get-locations', 'GET', ['auction' => 'COPART']);

// Mock Auth
Auth::loginUsingId($userId);

// Handle request via kernel logic manually to verify controller logic
$controller = new \App\Http\Controllers\CalculatorController();
$response = $controller->getLocations($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
