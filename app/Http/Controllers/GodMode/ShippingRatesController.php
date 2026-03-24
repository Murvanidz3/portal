<?php

namespace App\Http\Controllers\GodMode;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingRatesController extends Controller
{
    /**
     * Path to the global rates CSV file.
     */
    private function csvPath(): string
    {
        return storage_path('app/public/shipping-rates/rates.csv');
    }

    /**
     * Show the shipping rates management page.
     */
    public function index()
    {
        $path = $this->csvPath();
        $fileInfo = null;
        $summary = null;

        if (file_exists($path)) {
            $fileInfo = [
                'size' => round(filesize($path) / 1024, 1), // KB
                'modified' => date('Y-m-d H:i:s', filemtime($path)),
            ];

            $parsed = $this->parseCsv($path);
            $summary = [
                'copart' => count($parsed['COPART'] ?? []),
                'iaai' => count($parsed['IAAI'] ?? []),
                'total' => array_sum(array_map('count', $parsed)),
            ];
        }

        return view('god-mode.shipping-rates', compact('fileInfo', 'summary'));
    }

    /**
     * Upload a new CSV file (replaces existing).
     */
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120', // max 5MB
        ]);

        $file = $request->file('csv_file');

        // Validate CSV structure
        $tempPath = $file->getRealPath();
        $rows = array_map('str_getcsv', file($tempPath));

        if (count($rows) < 2) {
            return response()->json([
                'success' => false,
                'error' => 'CSV ფაილი ცარიელია ან არასწორი ფორმატია.',
            ], 422);
        }

        // Check header row has enough columns
        $header = $rows[0];
        if (count($header) < 9) {
            return response()->json([
                'success' => false,
                'error' => 'CSV ფაილს უნდა ჰქონდეს მინიმუმ 9 სვეტი (Auction, Location, Port, Sedan, SUV, Pickup, Van, Sprinter, Moto).',
            ], 422);
        }

        // Ensure destination directory exists
        $dir = dirname($this->csvPath());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Replace existing file
        $file->move($dir, 'rates.csv');

        // Parse the new file for summary
        $parsed = $this->parseCsv($this->csvPath());
        $copartCount = count($parsed['COPART'] ?? []);
        $iaaiCount = count($parsed['IAAI'] ?? []);

        Log::info('Shipping rates CSV uploaded via God Mode', [
            'copart_locations' => $copartCount,
            'iaai_locations' => $iaaiCount,
            'file_size' => round(filesize($this->csvPath()) / 1024, 1) . ' KB',
        ]);

        return response()->json([
            'success' => true,
            'message' => "CSV წარმატებით აიტვირთა! Copart: {$copartCount} ლოკაცია, IAAI: {$iaaiCount} ლოკაცია.",
            'summary' => [
                'copart' => $copartCount,
                'iaai' => $iaaiCount,
                'total' => $copartCount + $iaaiCount,
            ],
        ]);
    }

    /**
     * Download the current CSV file.
     */
    public function download()
    {
        $path = $this->csvPath();

        if (!file_exists($path)) {
            abort(404, 'CSV ფაილი ვერ მოიძებნა.');
        }

        return response()->download($path, 'rates.csv');
    }

    /**
     * Get parsed CSV data as JSON for preview.
     */
    public function preview()
    {
        $path = $this->csvPath();

        if (!file_exists($path)) {
            return response()->json(['success' => false, 'error' => 'ფაილი ვერ მოიძებნა.']);
        }

        $parsed = $this->parseCsv($path);
        $result = [];

        foreach ($parsed as $auction => $locations) {
            $auctionData = [];
            foreach ($locations as $name => $prices) {
                $auctionData[] = array_merge(['location' => $name], $prices);
            }
            // Sort alphabetically
            usort($auctionData, fn($a, $b) => strcmp($a['location'], $b['location']));
            $result[$auction] = $auctionData;
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * Parse the CSV file into a structured array.
     */
    private function parseCsv(string $path): array
    {
        $csv = array_map('str_getcsv', file($path));
        array_shift($csv); // Remove header

        $rates = [];
        foreach ($csv as $row) {
            if (count($row) < 9) continue;

            $auction = strtoupper(trim($row[0]));
            $location = trim($row[1]);

            $rates[$auction][$location] = [
                'sedan'    => (float) str_replace(',', '', $row[3]),
                'suv'      => (float) str_replace(',', '', $row[4]),
                'pickup'   => (float) str_replace(',', '', $row[5]),
                'minivan'  => (float) str_replace(',', '', $row[6]),
                'sprinter' => (float) str_replace(',', '', $row[7]),
                'moto'     => (float) str_replace(',', '', $row[8]),
            ];
        }

        return $rates;
    }
}
