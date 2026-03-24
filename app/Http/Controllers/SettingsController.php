<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Display settings page.
     */
    public function index()
    {
        // Exclude system settings that should not be editable from UI
        $excludedKeys = ['maintenance_mode', 'default_currency', 'registration_enabled'];
        
        $settings = Setting::whereNotIn('setting_key', $excludedKeys)
            ->get()
            ->groupBy('setting_group');

        $groups = [
            'general' => 'ზოგადი',
            'sms' => 'SMS',
            'finance' => 'ფინანსები',
            'appearance' => 'გარეგნობა',
        ];

        // Shipping rates CSV info
        $csvPath = storage_path('app/public/shipping-rates/rates.csv');
        $csvInfo = null;
        if (file_exists($csvPath)) {
            $csv = array_map('str_getcsv', file($csvPath));
            array_shift($csv);
            $copart = count(array_filter($csv, fn($r) => count($r) >= 9 && strtoupper(trim($r[0])) === 'COPART'));
            $iaai = count(array_filter($csv, fn($r) => count($r) >= 9 && strtoupper(trim($r[0])) === 'IAAI'));
            $csvInfo = [
                'size' => round(filesize($csvPath) / 1024, 1),
                'modified' => date('Y-m-d H:i', filemtime($csvPath)),
                'copart' => $copart,
                'iaai' => $iaai,
                'total' => $copart + $iaai,
            ];
        }

        return view('settings.index', compact('settings', 'groups', 'csvInfo'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:1000',
        ]);

        // Exclude system settings that should not be editable from UI
        $excludedKeys = ['maintenance_mode', 'default_currency', 'registration_enabled'];

        foreach ($validated['settings'] as $key => $value) {
            // Skip excluded settings
            if (in_array($key, $excludedKeys)) {
                continue;
            }
            Setting::set($key, $value);
        }

        // Clear settings cache
        Setting::clearCache();

        return redirect()->back()->with('success', 'პარამეტრები შენახულია!');
    }

    /**
     * Update single setting.
     * Only allows whitelisted keys to prevent arbitrary setting writes.
     */
    public function updateSingle(Request $request)
    {
        // Whitelist of keys that can be updated via this endpoint
        $allowedKeys = [
            'company_name', 'company_address', 'company_phone', 'company_email',
            'bank_name', 'bank_recipient', 'bank_iban', 'bank_swift',
            'site_logo', 'site_logo_dark', 'site_favicon',
            'sms_sender', 'sms_enabled',
            'transfer_commission_rate', 'default_currency',
        ];

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', \Illuminate\Validation\Rule::in($allowedKeys)],
            'value' => 'nullable|string|max:1000',
            'group' => 'nullable|string|max:50',
        ]);

        Setting::set(
            $validated['key'],
            $validated['value'],
            $validated['group'] ?? 'general'
        );

        return response()->json([
            'success' => true,
            'message' => 'პარამეტრი შენახულია!'
        ]);
    }

    /**
     * Upload shipping rates CSV.
     */
    public function uploadShippingRates(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));

        if (count($rows) < 2 || count($rows[0]) < 9) {
            return response()->json([
                'success' => false,
                'error' => 'CSV ფაილი არასწორი ფორმატია. მინიმუმ 9 სვეტი საჭიროა.',
            ], 422);
        }

        $dir = storage_path('app/public/shipping-rates');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file->move($dir, 'rates.csv');

        $csv = array_map('str_getcsv', file(storage_path('app/public/shipping-rates/rates.csv')));
        array_shift($csv);
        $copart = count(array_filter($csv, fn($r) => count($r) >= 9 && strtoupper(trim($r[0])) === 'COPART'));
        $iaai = count(array_filter($csv, fn($r) => count($r) >= 9 && strtoupper(trim($r[0])) === 'IAAI'));

        Log::info('Shipping rates CSV uploaded via admin settings', [
            'copart_locations' => $copart,
            'iaai_locations' => $iaai,
        ]);

        return response()->json([
            'success' => true,
            'message' => "CSV წარმატებით აიტვირთა! Copart: {$copart}, IAAI: {$iaai} ლოკაცია.",
        ]);
    }

    /**
     * Download current shipping rates CSV.
     */
    public function downloadShippingRates()
    {
        $path = storage_path('app/public/shipping-rates/rates.csv');
        if (!file_exists($path)) {
            abort(404, 'CSV ფაილი ვერ მოიძებნა.');
        }
        return response()->download($path, 'rates.csv');
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        Cache::flush();
        Setting::clearCache();

        // Clear Laravel caches
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');

        return response()->json([
            'success' => true,
            'message' => 'ქეში გასუფთავდა!'
        ]);
    }

    /**
     * Get system info.
     */
    public function systemInfo()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'disk_free' => $this->formatBytes(disk_free_space(base_path())),
            'memory_limit' => ini_get('memory_limit'),
            'max_upload' => ini_get('upload_max_filesize'),
            'post_max' => ini_get('post_max_size'),
        ];

        return response()->json($info);
    }

    /**
     * Format bytes to human readable.
     */
    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
    }
}
