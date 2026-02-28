<?php

namespace App\Services;

use App\Models\GodModeAuditLog;
use App\Models\GodModePermission;
use App\Models\GodModeStyle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GodModeService
{
    /**
     * Check if a feature is enabled for the current user.
     */
    public function can(string $featureKey): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        $role = $user->role ?? 'client';
        return GodModePermission::isEnabled($featureKey, $role);
    }

    /**
     * Check if a feature is globally enabled.
     */
    public function isGloballyEnabled(string $featureKey): bool
    {
        return GodModePermission::isEnabled($featureKey);
    }

    /**
     * Get all permissions grouped.
     */
    public function getGroupedPermissions(): array
    {
        return GodModePermission::getGrouped();
    }

    /**
     * Update a permission.
     */
    public function updatePermission(int $permissionId, array $data): GodModePermission
    {
        $permission = GodModePermission::findOrFail($permissionId);
        $oldValue = $permission->toArray();

        $permission->update($data);

        // Log the change
        $this->logAction('permission.updated', GodModePermission::class, $permissionId, $oldValue, $permission->fresh()->toArray());

        return $permission;
    }

    /**
     * Get all styles grouped.
     */
    public function getGroupedStyles(): array
    {
        return GodModeStyle::getGrouped();
    }

    /**
     * Get a style value.
     */
    public function getStyle(string $styleKey, ?string $default = null): ?string
    {
        return GodModeStyle::getValue($styleKey, $default);
    }

    /**
     * Update a style.
     */
    public function updateStyle(int $styleId, string $value): GodModeStyle
    {
        $style = GodModeStyle::findOrFail($styleId);
        $oldValue = ['style_value' => $style->style_value];

        $style->update(['style_value' => $value]);

        // Log the change
        $this->logAction('style.updated', GodModeStyle::class, $styleId, $oldValue, ['style_value' => $value]);

        return $style;
    }

    /**
     * Upload a logo/image.
     */
    public function uploadLogo(int $styleId, $file): GodModeStyle
    {
        $style = GodModeStyle::findOrFail($styleId);
        $oldValue = ['style_value' => $style->style_value];

        // Delete old file if exists and is not default
        if ($style->style_value && $style->style_value !== $style->default_value) {
            $oldPath = str_replace('/storage/', '', $style->style_value);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        // Store new file
        $path = $file->store('god-mode/branding', 'public');
        $url = '/storage/' . $path;

        $style->update(['style_value' => $url]);

        // Log the change
        $this->logAction('logo.uploaded', GodModeStyle::class, $styleId, $oldValue, ['style_value' => $url]);

        return $style;
    }

    /**
     * Generate CSS variables string.
     */
    public function getCssVariables(): string
    {
        return GodModeStyle::generateCssVariables();
    }

    /**
     * Get branding values.
     */
    public function getBranding(): array
    {
        return GodModeStyle::getBranding();
    }

    /**
     * Log an action to audit log.
     */
    public function logAction(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?array $oldValue = null,
        ?array $newValue = null
    ): void {
        $superAdmin = Auth::guard('god')->user();

        if ($superAdmin) {
            GodModeAuditLog::log(
                $superAdmin->id,
                $action,
                $targetType,
                $targetId,
                $oldValue,
                $newValue
            );
        }
    }

    /**
     * Get recent audit logs.
     */
    public function getRecentLogs(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return GodModeAuditLog::with('superAdmin')
            ->recent($limit)
            ->get();
    }
}
