<?php

namespace App\Http\Controllers\GodMode;

use App\Http\Controllers\Controller;
use App\Models\GodModePermission;
use App\Services\GodModeService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected GodModeService $godModeService;

    public function __construct(GodModeService $godModeService)
    {
        $this->godModeService = $godModeService;
    }

    /**
     * Show permissions management page.
     */
    public function index()
    {
        $permissions = $this->godModeService->getGroupedPermissions();

        // Group labels in Georgian
        $groupLabels = [
            'transactions' => 'ტრანზაქციები',
            'invoices' => 'ინვოისები',
            'users' => 'მომხმარებლები',
            'sms' => 'SMS სისტემა',
            'calculator' => 'კალკულატორი',
            'settings' => 'პარამეტრები',
            'finance' => 'ფინანსები',
            'cars' => 'მანქანები',
            'wallet' => 'საფულე',
            'notifications' => 'შეტყობინებები',
        ];

        return view('god-mode.permissions', compact('permissions', 'groupLabels'));
    }

    /**
     * Update a permission via AJAX.
     */
    public function update(Request $request, GodModePermission $permission)
    {
        $validated = $request->validate([
            'is_enabled_global' => 'sometimes|boolean',
            'is_enabled_admin' => 'sometimes|boolean',
            'is_enabled_dealer' => 'sometimes|boolean',
            'is_enabled_client' => 'sometimes|boolean',
        ]);

        $this->godModeService->updatePermission($permission->id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'უფლება განახლდა!',
        ]);
    }

    /**
     * Bulk update permissions.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*.id' => 'required|exists:god_mode_permissions,id',
            'permissions.*.is_enabled_global' => 'sometimes|boolean',
            'permissions.*.is_enabled_admin' => 'sometimes|boolean',
            'permissions.*.is_enabled_dealer' => 'sometimes|boolean',
            'permissions.*.is_enabled_client' => 'sometimes|boolean',
        ]);

        foreach ($validated['permissions'] as $permData) {
            $id = $permData['id'];
            unset($permData['id']);
            $this->godModeService->updatePermission($id, $permData);
        }

        return response()->json([
            'success' => true,
            'message' => 'ყველა უფლება განახლდა!',
        ]);
    }

    /**
     * Reset all permissions to defaults.
     */
    public function reset()
    {
        // Reset all to enabled
        GodModePermission::query()->update([
            'is_enabled_global' => true,
            'is_enabled_admin' => true,
            'is_enabled_dealer' => true,
            'is_enabled_client' => false,
        ]);

        GodModePermission::clearCache();

        $this->godModeService->logAction('permissions.reset');

        return response()->json([
            'success' => true,
            'message' => 'ყველა უფლება აღდგენილია!',
        ]);
    }
}
