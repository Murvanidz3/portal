<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GodModeAuditLog extends Model
{
    protected $table = 'god_mode_audit_logs';

    protected $fillable = [
        'super_admin_id',
        'action',
        'target_type',
        'target_id',
        'old_value',
        'new_value',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'array',
            'new_value' => 'array',
        ];
    }

    // Relationships
    public function superAdmin()
    {
        return $this->belongsTo(SuperAdmin::class);
    }

    // Scopes
    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Log an action.
     */
    public static function log(
        int $superAdminId,
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?array $oldValue = null,
        ?array $newValue = null
    ): self {
        return self::create([
            'super_admin_id' => $superAdminId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get human-readable action name.
     */
    public function getActionLabelAttribute(): string
    {
        $labels = [
            'permission.updated' => 'უფლების შეცვლა',
            'style.updated' => 'სტილის შეცვლა',
            'logo.uploaded' => 'ლოგოს ატვირთვა',
            'login' => 'შესვლა',
            'logout' => 'გასვლა',
        ];

        return $labels[$this->action] ?? $this->action;
    }
}
