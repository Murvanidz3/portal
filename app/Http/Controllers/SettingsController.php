<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

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

        return view('settings.index', compact('settings', 'groups'));
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
     */
    public function updateSingle(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100',
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
     * Toggle maintenance mode.
     */
    public function toggleMaintenance()
    {
        $currentMode = Setting::get('maintenance_mode', '0');
        $newMode = $currentMode === '1' ? '0' : '1';
        
        Setting::set('maintenance_mode', $newMode);

        return response()->json([
            'success' => true,
            'maintenance_mode' => $newMode === '1',
            'message' => $newMode === '1' 
                ? 'მეინთენანს რეჟიმი ჩართულია!' 
                : 'მეინთენანს რეჟიმი გამორთულია!'
        ]);
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
