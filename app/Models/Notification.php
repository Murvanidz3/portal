<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_id',
        'message',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // Accessors
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at?->diffForHumans() ?? '';
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at?->format('d.m.Y H:i') ?? '';
    }

    // Methods
    public function markAsRead(): bool
    {
        if (!$this->is_read) {
            $this->is_read = true;
            return $this->save();
        }
        return true;
    }

    public function markAsUnread(): bool
    {
        if ($this->is_read) {
            $this->is_read = false;
            return $this->save();
        }
        return true;
    }

    // Static methods
    public static function createForUser(int $userId, string $message, ?int $carId = null): self
    {
        return self::create([
            'user_id' => $userId,
            'car_id' => $carId,
            'message' => $message,
            'is_read' => false,
        ]);
    }

    public static function markAllReadForUser(int $userId): int
    {
        return self::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}
