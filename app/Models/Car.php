<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_user_id',
        'vin',
        'make',
        'model',
        'make_model', // Keep for backward compatibility
        'year',
        'lot_number',
        'container_number',
        'auction_name',
        'auction_location',
        'purchase_date',
        'arrival_date',
        'document_received_at',
        'document_issued_at',
        'booking_number',
        'shipping_line',
        'vessel',
        'loading_date',
        'estimated_arrival_date',
        'terminal',
        'status',
        'vehicle_cost',
        'auction_fee',
        'dealer_profit',
        'discount',
        'shipping_cost',
        'additional_cost',
        'transfer_commission',
        'paid_amount',
        'main_photo',
        'client_name',
        'client_phone',
        'client_id_number',
        'dealer_phone',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'vehicle_cost' => 'decimal:2',
            'auction_fee' => 'decimal:2',
            'dealer_profit' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'additional_cost' => 'decimal:2',
            'transfer_commission' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'purchase_date' => 'date',
            'arrival_date' => 'date',
            'document_received_at' => 'date',
            'document_issued_at' => 'date',
            'loading_date' => 'date',
            'estimated_arrival_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Status constants
    const STATUS_PURCHASED = 'purchased';
    const STATUS_WAREHOUSE = 'warehouse';
    const STATUS_LOADED = 'loaded';
    const STATUS_ON_WAY = 'on_way';
    const STATUS_POTI = 'poti';
    const STATUS_GREEN = 'green';
    const STATUS_DELIVERED = 'delivered';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PURCHASED => ['label' => 'შეძენილია', 'icon' => 'shopping-cart', 'color' => 'yellow'],
            self::STATUS_WAREHOUSE => ['label' => 'საწყობშია', 'icon' => 'warehouse', 'color' => 'orange'],
            self::STATUS_LOADED => ['label' => 'ჩატვირთულია', 'icon' => 'box', 'color' => 'blue'],
            self::STATUS_ON_WAY => ['label' => 'გზაშია', 'icon' => 'ship', 'color' => 'indigo'],
            self::STATUS_POTI => ['label' => 'ფოთშია', 'icon' => 'anchor', 'color' => 'purple'],
            self::STATUS_GREEN => ['label' => 'მწვანეშია', 'icon' => 'check-circle', 'color' => 'green'],
            self::STATUS_DELIVERED => ['label' => 'გაყვანილია', 'icon' => 'flag-checkered', 'color' => 'emerald'],
        ];
    }

    public static function getStatusOptions(): array
    {
        $options = [];
        foreach (self::getStatuses() as $key => $status) {
            $options[$key] = $status['label'];
        }
        return $options;
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function files()
    {
        return $this->hasMany(CarFile::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // File relationships by category
    public function auctionPhotos()
    {
        return $this->files()->where('category', 'auction')->where('file_type', 'image');
    }

    public function portPhotos()
    {
        return $this->files()->where('category', 'port')->where('file_type', 'image');
    }

    public function terminalPhotos()
    {
        return $this->files()->where('category', 'terminal')->where('file_type', 'image');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOnWay($query)
    {
        return $query->where('status', self::STATUS_ON_WAY);
    }

    public function scopeInTransit($query)
    {
        return $query->whereIn('status', [self::STATUS_LOADED, self::STATUS_ON_WAY, self::STATUS_POTI]);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeWithDebt($query)
    {
        return $query->whereRaw('(vehicle_cost + auction_fee + shipping_cost + additional_cost) > paid_amount');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('vin', 'like', "%{$term}%")
                ->orWhere('make_model', 'like', "%{$term}%")
                ->orWhere('make', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%")
                ->orWhere('lot_number', 'like', "%{$term}%")
                ->orWhere('container_number', 'like', "%{$term}%")
                ->orWhere('client_name', 'like', "%{$term}%");
        });
    }

    public function scopeForUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isDealer()) {
            return $query->where('user_id', $user->id);
        }

        return $query->where('client_user_id', $user->id);
    }

    // Accessors
    public function getTotalCostAttribute(): float
    {
        return $this->vehicle_cost + $this->auction_fee + $this->shipping_cost + $this->additional_cost;
    }

    public function getDebtAttribute(): float
    {
        return $this->total_cost - $this->paid_amount;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status]['label'] ?? 'უცნობია';
    }

    public function getStatusIconAttribute(): string
    {
        return self::getStatuses()[$this->status]['icon'] ?? 'question';
    }

    public function getStatusColorAttribute(): string
    {
        return self::getStatuses()[$this->status]['color'] ?? 'gray';
    }

    public function getMainPhotoUrlAttribute(): string
    {
        if (empty($this->main_photo)) {
            // Keep assert for fallback image
            return asset('images/no-photo.png');
        }

        if (str_starts_with($this->main_photo, 'http')) {
            return $this->main_photo;
        }

        return \Illuminate\Support\Facades\Storage::url($this->main_photo);
    }

    public function getFormattedDebtAttribute(): string
    {
        return '$' . number_format($this->debt, 2);
    }

    /** Surplus when paid_amount > total_cost (positive balance). */
    public function getOverpaymentAttribute(): float
    {
        return max(0, (float) $this->paid_amount - (float) $this->total_cost);
    }

    public function getFormattedOverpaymentAttribute(): string
    {
        return '$' . number_format($this->overpayment, 2);
    }

    public function hasOverpayment(): bool
    {
        return (float) $this->paid_amount > (float) $this->total_cost;
    }

    public function getFormattedPaidAttribute(): string
    {
        return '$' . number_format($this->paid_amount, 2);
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total_cost, 2);
    }

    // Helpers
    public function isPaid(): bool
    {
        return $this->debt <= 0;
    }

    public function hasDebt(): bool
    {
        return $this->debt > 0;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function getClientDisplayName(): string
    {
        if ($this->client) {
            return $this->client->full_name ?? $this->client->username;
        }
        return $this->client_name ?? 'უცნობი';
    }

    public function getClientPhone(): string
    {
        if ($this->client) {
            return $this->client->phone ?? '';
        }
        return $this->client_phone ?? '';
    }
}
