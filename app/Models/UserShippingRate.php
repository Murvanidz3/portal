<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserShippingRate extends Model
{
    protected $table = 'user_shipping_rates';

    protected $fillable = [
        'user_id',
        'shipping_file_id',
        'auction_type',
        'location_name',
        'location_normalized',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    // Auction type constants
    const AUCTION_COPART = 'copart';
    const AUCTION_IAAI = 'iaai';

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shippingFile()
    {
        return $this->belongsTo(UserShippingFile::class, 'shipping_file_id');
    }

    // Scopes
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeCopart($query)
    {
        return $query->where('auction_type', self::AUCTION_COPART);
    }

    public function scopeIaai($query)
    {
        return $query->where('auction_type', self::AUCTION_IAAI);
    }

    public function scopeActive($query)
    {
        return $query->whereHas('shippingFile', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Normalize location name for searching.
     */
    public static function normalizeLocation(string $location): string
    {
        // Remove everything after first parenthesis
        $location = preg_replace('/\s*\(.*\)/', '', $location);

        // Convert to lowercase
        $location = Str::lower($location);

        // Remove extra whitespace
        $location = preg_replace('/\s+/', ' ', trim($location));

        // Remove special characters
        $location = preg_replace('/[^a-z0-9\s\-]/', '', $location);

        return $location;
    }

    /**
     * Find rate for a location.
     */
    public static function findRate(int $userId, string $auctionType, string $location): ?float
    {
        $normalized = self::normalizeLocation($location);

        // First try exact match on normalized
        $rate = self::forUser($userId)
            ->active()
            ->where('auction_type', $auctionType)
            ->where('location_normalized', $normalized)
            ->first();

        if ($rate) {
            return (float) $rate->price;
        }

        // Try partial match
        $rate = self::forUser($userId)
            ->active()
            ->where('auction_type', $auctionType)
            ->where(function ($query) use ($normalized, $location) {
                $query->where('location_normalized', 'LIKE', '%' . $normalized . '%')
                    ->orWhere('location_name', 'LIKE', '%' . $location . '%');
            })
            ->first();

        return $rate ? (float) $rate->price : null;
    }

    /**
     * Get all rates for a user grouped by auction type.
     */
    public static function getUserRates(int $userId): array
    {
        $rates = self::forUser($userId)
            ->active()
            ->orderBy('auction_type')
            ->orderBy('location_name')
            ->get();

        return [
            'copart' => $rates->where('auction_type', self::AUCTION_COPART)->values(),
            'iaai' => $rates->where('auction_type', self::AUCTION_IAAI)->values(),
        ];
    }
}
