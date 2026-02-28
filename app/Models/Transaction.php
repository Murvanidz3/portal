<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'user_id',
        'amount',
        'payment_date',
        'purpose',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Purpose constants
    const PURPOSE_BALANCE_TOPUP = 'balance_topup';
    const PURPOSE_SHIPPING = 'shipping';
    const PURPOSE_VEHICLE_PAYMENT = 'vehicle_payment';
    const PURPOSE_WALLET_TO_CAR = 'wallet_to_car';
    const PURPOSE_CAR_TO_CAR_OUT = 'car_to_car_out';
    const PURPOSE_CAR_TO_CAR_IN = 'car_to_car_in';
    const PURPOSE_OTHER = 'other';

    public static function getPurposes(): array
    {
        return [
            self::PURPOSE_BALANCE_TOPUP => 'ბალანსის შევსება',
            self::PURPOSE_SHIPPING => 'ტრანსპორტირება',
            self::PURPOSE_VEHICLE_PAYMENT => 'ავტომობილის გადახდა',
            self::PURPOSE_WALLET_TO_CAR => 'საფულიდან მანქანაზე',
            self::PURPOSE_CAR_TO_CAR_OUT => 'მანქანიდან გადაცემა',
            self::PURPOSE_CAR_TO_CAR_IN => 'მანქანაზე მიღება',
            self::PURPOSE_OTHER => 'სხვა',
        ];
    }

    // Relationships
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByPurpose($query, $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    public function scopeBalanceTopups($query)
    {
        return $query->where('purpose', self::PURPOSE_BALANCE_TOPUP);
    }

    public function scopeForCar($query, $carId)
    {
        return $query->where('car_id', $carId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('payment_date', '>=', now()->subDays($days));
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getPurposeLabelAttribute(): string
    {
        return self::getPurposes()[$this->purpose] ?? 'უცნობი';
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->payment_date?->format('d.m.Y') ?? '';
    }

    // Helpers
    public function isBalanceTopup(): bool
    {
        return $this->purpose === self::PURPOSE_BALANCE_TOPUP;
    }
}
