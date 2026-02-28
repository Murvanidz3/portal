<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GodModePermission extends Model
{
    protected $table = 'god_mode_permissions';

    protected $fillable = [
        'feature_key',
        'feature_name',
        'feature_group',
        'description',
        'is_enabled_global',
        'is_enabled_admin',
        'is_enabled_dealer',
        'is_enabled_client',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled_global' => 'boolean',
            'is_enabled_admin' => 'boolean',
            'is_enabled_dealer' => 'boolean',
            'is_enabled_client' => 'boolean',
        ];
    }

    // Cache key
    const CACHE_KEY = 'god_mode_permissions';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get all permissions from cache or database.
     */
    public static function getAllCached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::orderBy('sort_order')->get()->keyBy('feature_key')->toArray();
        });
    }

    /**
     * Clear the permissions cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Check if a feature is enabled for a specific role.
     */
    public static function isEnabled(string $featureKey, string $role = null): bool
    {
        $permissions = self::getAllCached();

        if (!isset($permissions[$featureKey])) {
            return true; // If not defined, allow by default
        }

        $permission = $permissions[$featureKey];

        // Check global toggle first
        if (!$permission['is_enabled_global']) {
            return false;
        }

        // If no role specified, just check global
        if ($role === null) {
            return true;
        }

        // Check role-specific toggle
        $roleKey = 'is_enabled_' . $role;
        return $permission[$roleKey] ?? false;
    }

    /**
     * Get permissions grouped by feature_group.
     */
    public static function getGrouped(): array
    {
        return self::orderBy('feature_group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('feature_group')
            ->toArray();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when permission is saved
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
