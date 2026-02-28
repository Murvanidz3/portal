<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'full_name',
        'email',
        'phone',
        'role',
        'balance',
        'sms_enabled',
        'approved',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'sms_enabled' => 'boolean',
            'approved' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure administrators are always approved
        static::saving(function ($user) {
            if ($user->role === self::ROLE_ADMIN) {
                $user->approved = true;
            }
        });
    }

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_DEALER = 'dealer';
    const ROLE_CLIENT = 'client';

    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'ადმინი',
            self::ROLE_DEALER => 'დილერი',
            self::ROLE_CLIENT => 'კლიენტი',
        ];
    }

    // Relationships
    public function cars()
    {
        return $this->hasMany(Car::class, 'user_id');
    }

    public function clientCars()
    {
        return $this->hasMany(Car::class, 'client_user_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function shippingFiles()
    {
        return $this->hasMany(UserShippingFile::class);
    }

    public function shippingRates()
    {
        return $this->hasMany(UserShippingRate::class);
    }

    // Scopes
    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeDealers($query)
    {
        return $query->where('role', self::ROLE_DEALER);
    }

    public function scopeClients($query)
    {
        return $query->where('role', self::ROLE_CLIENT);
    }

    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    public function scopeSmsEnabled($query)
    {
        return $query->where('sms_enabled', true);
    }

    // Helpers
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isDealer(): bool
    {
        return $this->role === self::ROLE_DEALER;
    }

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    public function isApproved(): bool
    {
        // Administrators are always approved
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }
        return $this->approved === true;
    }

    public function canReceiveSms(): bool
    {
        return $this->sms_enabled && !empty($this->phone);
    }

    public function getUnreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    public function getFormattedBalance(): string
    {
        return '$' . number_format($this->balance, 2);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::getRoles()[$this->role] ?? 'უცნობი';
    }

    public function getTotalDebt(): float
    {
        return $this->cars()->get()->sum(function ($car) {
            return max(0, $car->total_cost - $car->paid_amount);
        });
    }

    public function getTotalPaid(): float
    {
        return $this->cars()->sum('paid_amount');
    }

    public function getTotalCost(): float
    {
        return $this->cars()->get()->sum('total_cost');
    }
}
