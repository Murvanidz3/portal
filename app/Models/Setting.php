<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_group',
    ];

    // Cache key
    const CACHE_KEY = 'app_settings';
    const CACHE_TTL = 3600; // 1 hour

    // Scopes
    public function scopeByGroup($query, $group)
    {
        return $query->where('setting_group', $group);
    }

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $settings = self::getAllCached();
        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $group = 'general'): bool
    {
        $setting = self::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value, 'setting_group' => $group]
        );

        self::clearCache();
        
        return $setting->wasRecentlyCreated || $setting->wasChanged();
    }

    /**
     * Get all settings as key-value array (cached)
     */
    public static function getAllCached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::pluck('setting_value', 'setting_key')->toArray();
        });
    }

    /**
     * Get settings by group
     */
    public static function getByGroup(string $group): array
    {
        return self::where('setting_group', $group)
            ->pluck('setting_value', 'setting_key')
            ->toArray();
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Check if maintenance mode is enabled
     */
    public static function isMaintenanceMode(): bool
    {
        return self::get('maintenance_mode', '0') === '1';
    }

    /**
     * Check if registration is enabled
     */
    public static function isRegistrationEnabled(): bool
    {
        return self::get('registration_enabled', '0') === '1';
    }

    /**
     * Get SMS API key
     */
    public static function getSmsApiKey(): ?string
    {
        return self::get('sms_api_key');
    }

    /**
     * Get site name
     */
    public static function getSiteName(): string
    {
        return self::get('site_name', 'OneCar CRM');
    }
}
