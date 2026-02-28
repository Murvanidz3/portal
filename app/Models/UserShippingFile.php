<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserShippingFile extends Model
{
    protected $table = 'user_shipping_files';

    protected $fillable = [
        'user_id',
        'file_path',
        'original_name',
        'uploaded_at',
        'uploaded_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function uploadedByUser()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function rates()
    {
        return $this->hasMany(UserShippingRate::class, 'shipping_file_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Deactivate other files for the same user.
     */
    public function activateExclusive(): void
    {
        // Deactivate all other files for this user
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);

        // Activate this one
        $this->update(['is_active' => true]);
    }
}
