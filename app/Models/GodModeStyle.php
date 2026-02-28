<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GodModeStyle extends Model
{
    protected $table = 'god_mode_styles';

    protected $fillable = [
        'style_key',
        'style_name',
        'style_group',
        'style_type',
        'style_value',
        'default_value',
        'description',
        'sort_order',
    ];

    // Cache key
    const CACHE_KEY = 'god_mode_styles';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get all styles from cache or database.
     */
    public static function getAllCached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::orderBy('sort_order')->get()->keyBy('style_key')->toArray();
        });
    }

    /**
     * Clear the styles cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get a style value with fallback to default.
     */
    public static function getValue(string $styleKey, string $default = null): ?string
    {
        $styles = self::getAllCached();

        if (!isset($styles[$styleKey])) {
            return $default;
        }

        $style = $styles[$styleKey];
        return $style['style_value'] ?? $style['default_value'] ?? $default;
    }

    /**
     * Get styles grouped by style_group.
     */
    public static function getGrouped(): array
    {
        return self::orderBy('style_group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('style_group')
            ->toArray();
    }

    /**
     * Generate CSS variables string.
     */
    public static function generateCssVariables(): string
    {
        $styles = self::getAllCached();
        $css = ":root {\n";

        foreach ($styles as $key => $style) {
            if ($style['style_type'] === 'color') {
                $value = $style['style_value'] ?? $style['default_value'];
                $varName = str_replace('_', '-', $key);
                $css .= "    --{$varName}: {$value};\n";
            }
        }

        $css .= "}\n";
        return $css;
    }

    /**
     * Get all branding values.
     */
    public static function getBranding(): array
    {
        $styles = self::getAllCached();
        $branding = [];

        foreach ($styles as $key => $style) {
            if ($style['style_group'] === 'branding') {
                $branding[$key] = $style['style_value'] ?? $style['default_value'];
            }
        }

        return $branding;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when style is saved
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
