<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuperAdmin extends Authenticatable
{
    use HasFactory;

    protected $table = 'super_admins';

    protected $guard = 'god';

    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // Relationships
    public function auditLogs()
    {
        return $this->hasMany(GodModeAuditLog::class);
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function updateLoginInfo(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }
}
