<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    /**
     * Display calculator page.
     */
    public function index()
    {
        // Default values for calculation
        $defaults = [
            'auction_fee_percent' => 10,
            'shipping_base' => 1500,
            'customs_rate' => 0.15,
            'vat_rate' => 0.18,
            'excise_rates' => [
                'petrol' => [
                    '0-1000' => 0.05,
                    '1001-1500' => 0.10,
                    '1501-2000' => 0.15,
                    '2001-2500' => 0.20,
                    '2501-3000' => 0.40,
                    '3001+' => 0.60,
                ],
                'diesel' => [
                    '0-1500' => 0.05,
                    '1501-2000' => 0.10,
                    '2001-2500' => 0.15,
                    '2501-3000' => 0.20,
                    '3001+' => 0.40,
                ],
                'hybrid' => [
                    '0-2000' => 0.00,
                    '2001-2500' => 0.05,
                    '2501-3000' => 0.10,
                    '3001+' => 0.15,
                ],
                'electric' => [
                    'all' => 0.00,
                ],
            ],
        ];

        return view('calculator.index', compact('defaults'));
    }

    /**
     * Calculate total cost.
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'vehicle_price' => 'required|numeric|min:0',
            'auction_fee' => 'nullable|numeric|min:0',
            'shipping_cost' => 'required|numeric|min:0',
            'engine_type' => 'required|in:petrol,diesel,hybrid,electric',
            'engine_volume' => 'required|integer|min:0',
            'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        $vehiclePrice = (float) $validated['vehicle_price'];
        $auctionFee = (float) ($validated['auction_fee'] ?? $vehiclePrice * 0.10);
        $shippingCost = (float) $validated['shipping_cost'];
        $engineType = $validated['engine_type'];
        $engineVolume = (int) $validated['engine_volume'];
        $vehicleYear = (int) $validated['vehicle_year'];

        // Calculate vehicle age
        $vehicleAge = date('Y') - $vehicleYear;

        // Base for customs calculation
        $customsBase = $vehiclePrice + $auctionFee + $shippingCost;

        // Calculate customs duty (15% base, adjusted by age)
        $customsDutyRate = 0.15;
        if ($vehicleAge > 10) {
            $customsDutyRate = 0.20;
        } elseif ($vehicleAge > 5) {
            $customsDutyRate = 0.17;
        }
        $customsDuty = $customsBase * $customsDutyRate;

        // Calculate excise tax based on engine type and volume
        $exciseTax = $this->calculateExcise($engineType, $engineVolume, $customsBase);

        // Calculate VAT (18%)
        $vatBase = $customsBase + $customsDuty + $exciseTax;
        $vat = $vatBase * 0.18;

        // Total cost
        $totalCost = $vehiclePrice + $auctionFee + $shippingCost + $customsDuty + $exciseTax + $vat;

        $result = [
            'vehicle_price' => round($vehiclePrice, 2),
            'auction_fee' => round($auctionFee, 2),
            'shipping_cost' => round($shippingCost, 2),
            'customs_duty' => round($customsDuty, 2),
            'customs_duty_rate' => $customsDutyRate * 100,
            'excise_tax' => round($exciseTax, 2),
            'vat' => round($vat, 2),
            'total_cost' => round($totalCost, 2),
            'breakdown' => [
                'usa_costs' => round($vehiclePrice + $auctionFee, 2),
                'shipping' => round($shippingCost, 2),
                'georgia_taxes' => round($customsDuty + $exciseTax + $vat, 2),
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return view('calculator.result', compact('result', 'validated'));
    }

    /**
     * Parse global rates from Excel CSV.
     * @return array
     */
    private function parseRatesCsv()
    {
        $path = storage_path('app/public/shipping-rates/rates.csv');
        if (!file_exists($path)) {
            \Illuminate\Support\Facades\Log::error("Rates CSV not found at: {$path}");
            return [];
        }

        $csv = array_map('str_getcsv', file($path));
        $headers = array_shift($csv);

        $rates = [];
        foreach ($csv as $row) {
            if (count($row) < 9)
                continue; // Ensure row has enough columns

            $auction = strtoupper(trim($row[0]));
            $location = trim($row[1]);

            $rates[$auction][$location] = [
                'sedan' => (float) str_replace(',', '', $row[3]),
                'suv' => (float) str_replace(',', '', $row[4]),
                'pickup' => (float) str_replace(',', '', $row[5]),
                'minivan' => (float) str_replace(',', '', $row[6]),
                'sprinter' => (float) str_replace(',', '', $row[7]),
                'moto' => (float) str_replace(',', '', $row[8]),
            ];
        }

        return $rates;
    }

    /**
     * Get locations for dropdown based on auction type.
     * Returns global locations from rates.csv.
     */
    public function getLocations(Request $request)
    {
        $auctionType = strtoupper($request->get('auction', 'COPART'));

        $ratesData = $this->parseRatesCsv();
        $auctionRates = $ratesData[$auctionType] ?? [];
        \Illuminate\Support\Facades\Log::info("Calculator getLocations called", [
            'auction' => $auctionType,
            'ratesDataCount' => count($ratesData),
            'auctionRatesCount' => count($auctionRates)
        ]);

        $locations = [];
        foreach ($auctionRates as $location => $prices) {
            $locations[] = [
                'name' => $location,
                'price' => $prices['sedan'] // Sending default sedan price for display if needed
            ];
        }

        // Sort locations alphabetically
        usort($locations, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return response()->json([
            'locations' => $locations,
            'has_rates' => !empty($locations),
            'auction' => $auctionType,
            'debug' => [
                'is_global' => true,
                'rates_count' => count($locations)
            ]
        ]);
    }

    /**
     * Calculate shipping from global rates.
     */
    public function calculateShippingFromRates(Request $request)
    {
        $validated = $request->validate([
            'vehicle_type' => 'required|string',
            'auction' => 'required|in:COPART,IAAI',
            'location' => 'required|string',
        ]);

        $auctionType = strtoupper($validated['auction']);
        $location = trim($validated['location']);
        $vehicleType = strtolower($validated['vehicle_type']);

        $ratesData = $this->parseRatesCsv();

        if (!isset($ratesData[$auctionType][$location])) {
            return response()->json([
                'success' => false,
                'error' => 'ტარიფი ვერ მოიძებნა ამ ლოკაციისთვის',
            ]);
        }

        $locationRates = $ratesData[$auctionType][$location];

        // Base rate is determined directly from the specific vehicle type column in CSV
        // If the exact type isn't found, fallback to sedan
        $baseRate = $locationRates[$vehicleType] ?? $locationRates['sedan'] ?? 0;

        return response()->json([
            'success' => true,
            'base_rate' => $baseRate,
            'total_cost' => round($baseRate, 2),
            'vehicle_type' => $validated['vehicle_type'],
            'auction' => $validated['auction'],
            'location' => $validated['location'],
        ]);
    }

    /**
     * Calculate excise tax based on engine type and volume.
     */
    protected function calculateExcise(string $engineType, int $engineVolume, float $baseAmount): float
    {
        if ($engineType === 'electric') {
            return 0;
        }

        $rates = [
            'petrol' => [
                1000 => 0.05,
                1500 => 0.10,
                2000 => 0.15,
                2500 => 0.20,
                3000 => 0.40,
                PHP_INT_MAX => 0.60,
            ],
            'diesel' => [
                1500 => 0.05,
                2000 => 0.10,
                2500 => 0.15,
                3000 => 0.20,
                PHP_INT_MAX => 0.40,
            ],
            'hybrid' => [
                2000 => 0.00,
                2500 => 0.05,
                3000 => 0.10,
                PHP_INT_MAX => 0.15,
            ],
        ];

        $typeRates = $rates[$engineType] ?? $rates['petrol'];
        $exciseRate = 0;

        foreach ($typeRates as $volumeLimit => $rate) {
            if ($engineVolume <= $volumeLimit) {
                $exciseRate = $rate;
                break;
            }
        }

        return $baseAmount * $exciseRate;
    }




}


