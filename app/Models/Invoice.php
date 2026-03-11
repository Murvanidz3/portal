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

    /**
     * Generate the next sequential invoice number.
     * Format: INV-CR00101, INV-CR00102, ...
     * Starts from INV-CR00101 if no invoices exist.
     */
    public static function generateNextNumber(): string
    {
        $prefix = 'INV-CR';
        $startNumber = 101;

        // Get the last invoice number matching our format
        $last = static::where('invoice_number', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(invoice_number, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->value('invoice_number');

        if ($last) {
            // Extract numeric part and increment
            $numericPart = (int) substr($last, strlen($prefix));
            $next = $numericPart + 1;
        } else {
            $next = $startNumber;
        }

        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
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
