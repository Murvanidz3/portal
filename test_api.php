<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

// Simulate a request
$request = Illuminate\Http\Request::create('/calculator/get-locations?auction=COPART', 'GET');

// Manually set authentication - simulate admin login
$user = App\Models\User::find(1);
Auth::login($user);

$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
