<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_id',
        'phone',
        'message',
        'status',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'მოლოდინში',
            self::STATUS_SENT => 'გაგზავნილია',
            self::STATUS_FAILED => 'შეცდომა',
        ];
    }

    // Scopes
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'უცნობი';
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at?->format('d.m.Y H:i') ?? '';
    }

    public function getShortMessageAttribute(): string
    {
        return \Str::limit($this->message, 50);
    }

    // Static methods
    public static function log(string $phone, string $message, string $status = 'sent', ?string $referenceId = null): self
    {
        return self::create([
            'phone' => $phone,
            'message' => $message,
            'status' => $status,
            'reference_id' => $referenceId,
        ]);
    }
}
