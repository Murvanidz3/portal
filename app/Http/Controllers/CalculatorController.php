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
     * Calculate shipping cost estimate.
     * Uses user-specific rates from uploaded Excel if available.
     */
    public function calculateShipping(Request $request)
    {
        $validated = $request->validate([
            'origin' => 'required|string|max:200',
            'vehicle_type' => 'required|in:sedan,suv,truck,motorcycle',
            'is_running' => 'boolean',
            'auction_type' => 'nullable|in:copart,iaai',
        ]);

        $userId = auth()->id();
        $auctionType = $validated['auction_type'] ?? 'copart';
        $origin = $validated['origin'];
        $vehicleType = $validated['vehicle_type'];
        $isRunning = $request->boolean('is_running', true);

        // Try to find user-specific rate first
        $userRate = null;
        if ($userId) {
            $userRate = \App\Models\UserShippingRate::findRate($userId, $auctionType, $origin);
        }

        if ($userRate !== null) {
            // Use user-specific rate
            $shippingCost = $userRate;

            // Add vehicle type adjustment
            $vehicleAdjustments = [
                'sedan' => 0,
                'suv' => 200,
                'truck' => 400,
                'motorcycle' => -500,
            ];
            $shippingCost += $vehicleAdjustments[$vehicleType] ?? 0;

            // Add towing fee if not running
            if (!$isRunning) {
                $shippingCost += 150;
            }

            return response()->json([
                'estimated_cost' => round(max($shippingCost, 0), 2),
                'origin' => $origin,
                'vehicle_type' => $vehicleType,
                'is_running' => $isRunning,
                'auction_type' => $auctionType,
                'rate_source' => 'custom',
                'base_rate' => $userRate,
            ]);
        }

        // Fall back to default rates
        $baseRates = [
            'sedan' => 1500,
            'suv' => 1800,
            'truck' => 2200,
            'motorcycle' => 800,
        ];

        // Location multipliers (simplified)
        $locationMultipliers = [
            'CA' => 1.0,
            'TX' => 1.1,
            'FL' => 1.15,
            'NY' => 1.2,
            'NJ' => 1.2,
            'default' => 1.15,
        ];

        $originPrefix = strtoupper(substr($origin, 0, 2));

        $baseRate = $baseRates[$vehicleType];
        $multiplier = $locationMultipliers[$originPrefix] ?? $locationMultipliers['default'];

        $shippingCost = $baseRate * $multiplier;

        // Add towing fee if not running
        if (!$isRunning) {
            $shippingCost += 150;
        }

        return response()->json([
            'estimated_cost' => round($shippingCost, 2),
            'origin' => $origin,
            'vehicle_type' => $vehicleType,
            'is_running' => $isRunning,
            'auction_type' => $auctionType,
            'rate_source' => 'default',
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

    /**
     * Search locations for autocomplete.
     */
    public function searchLocations(Request $request)
    {
        $query = $request->get('q', '');
        $auctionType = $request->get('auction_type', 'copart');
        $userId = auth()->id();

        if (!$userId || strlen($query) < 2) {
            return response()->json([]);
        }

        $service = app(\App\Services\ShippingRateService::class);
        $results = $service->searchLocation($userId, $query);

        // Filter by auction type
        $filtered = array_filter($results, function ($r) use ($auctionType) {
            return $r['auction_type'] === $auctionType;
        });

        return response()->json(array_values($filtered));
    }

    /**
     * Check if user has custom shipping rates.
     */
    public function hasCustomRates()
    {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json(['has_rates' => false]);
        }

        $service = app(\App\Services\ShippingRateService::class);
        $hasRates = $service->hasCustomRates($userId);
        $rates = [];

        if ($hasRates) {
            $rates = $service->getUserRates($userId);
        }

        return response()->json([
            'has_rates' => $hasRates,
            'copart_count' => count($rates['copart'] ?? []),
            'iaai_count' => count($rates['iaai'] ?? []),
        ]);
    }

    /**
     * Get locations for dropdown based on auction type.
     * Returns locations from user's uploaded CSV.
     */
    public function getLocations(Request $request)
    {
        $userId = auth()->id();
        $auctionType = strtoupper($request->get('auction', 'COPART'));

        if (!$userId) {
            return response()->json(['locations' => [], 'has_rates' => false, 'debug' => 'No user ID']);
        }

        // Direct approach: Find the latest file for the user
        $latestFile = \App\Models\UserShippingFile::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestFile) {
            return response()->json([
                'locations' => [],
                'has_rates' => false,
                'debug' => 'No shipping file found for user'
            ]);
        }

        // Fetch rates directly from this file
        $rates = \App\Models\UserShippingRate::where('user_id', $userId)
            ->where('shipping_file_id', $latestFile->id)
            ->where('auction_type', strtolower($auctionType))
            ->orderBy('location_name')
            ->get();

        $locations = $rates->map(function ($r) {
            return [
                'name' => $r->location_name,
                'price' => (float) $r->price
            ];
        })->values();

        return response()->json([
            'locations' => $locations,
            'has_rates' => $locations->isNotEmpty(),
            'auction' => $auctionType,
            'debug' => [
                'user_id' => $userId,
                'file_id' => $latestFile->id,
                'file_active' => $latestFile->is_active,
                'rates_count' => $rates->count()
            ]
        ]);
    }

    /**
     * Calculate shipping from user's rates.
     */
    public function calculateShippingFromRates(Request $request)
    {
        $userId = auth()->id();

        $validated = $request->validate([
            'vehicle_type' => 'required|string',
            'auction' => 'required|in:COPART,IAAI',
            'location' => 'required|string',
            'destination_port' => 'required|in:poti,batumi',
        ]);

        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $service = app(\App\Services\ShippingRateService::class);

        // Get base rate from user's uploaded CSV
        $auctionType = strtolower($validated['auction']);
        $baseRate = \App\Models\UserShippingRate::findRate($userId, $auctionType, $validated['location']);

        if ($baseRate === null) {
            return response()->json([
                'success' => false,
                'error' => 'ტარიფი ვერ მოიძებნა ამ ლოკაციისთვის',
            ]);
        }

        // Vehicle type adjustments
        $vehicleTypeAdjustments = [
            'sedan' => 0,
            'sm_suv' => 0,
            'big_suv' => 150,
            'van' => 100,
            'sprinter' => 200,
            'pickup' => 250,
            'heavy_equip' => 500,
            'bob_cat' => 400,
        ];

        // Port adjustments (Batumi is slightly more expensive)
        $portAdjustments = [
            'poti' => 0,
            'batumi' => 50,
        ];

        $vehicleAdjustment = $vehicleTypeAdjustments[$validated['vehicle_type']] ?? 0;
        $portAdjustment = $portAdjustments[$validated['destination_port']] ?? 0;

        $totalCost = $baseRate + $vehicleAdjustment + $portAdjustment;

        return response()->json([
            'success' => true,
            'base_rate' => $baseRate,
            'vehicle_adjustment' => $vehicleAdjustment,
            'port_adjustment' => $portAdjustment,
            'total_cost' => round($totalCost, 2),
            'vehicle_type' => $validated['vehicle_type'],
            'auction' => $validated['auction'],
            'location' => $validated['location'],
            'destination_port' => $validated['destination_port'],
        ]);
    }
}


