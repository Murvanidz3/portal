<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$userId = 1;
$service = app(\App\Services\ShippingRateService::class);

echo "Has custom rates: " . ($service->hasCustomRates($userId) ? 'YES' : 'NO') . "\n";

$rates = $service->getUserRates($userId);
echo "Copart rates count: " . $rates['copart']->count() . "\n";
echo "IAAI rates count: " . $rates['iaai']->count() . "\n";

if ($rates['copart']->count() > 0) {
    echo "First Copart location: " . $rates['copart']->first()->location_name . "\n";
    echo "First Copart price: " . $rates['copart']->first()->price . "\n";
}
