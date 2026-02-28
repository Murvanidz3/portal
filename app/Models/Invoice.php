<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'type',
        'personal_id',
        'vin',
        'make_model',
        'year',
        'client_name',
        'vehicle_cost',
        'shipping_cost',
        'total_amount',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'vehicle_cost' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'vehicle' => 'ავტომობილის ღირებულება',
            'shipping' => 'ტრანსპორტირების გადასახადი',
            default => 'უცნობი',
        };
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }
}
