<?php

namespace App\Http\Controllers\GodMode;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\GodModeAuditLog;
use App\Models\Transaction;
use App\Models\User;
use App\Services\GodModeService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected GodModeService $godModeService;

    public function __construct(GodModeService $godModeService)
    {
        $this->godModeService = $godModeService;
    }

    /**
     * Show the God Mode dashboard.
     */
    public function index()
    {
        // Get system statistics
        $stats = [
            'total_users' => User::count(),
            'total_cars' => Car::count(),
            'total_transactions' => Transaction::count(),
            'dealers_count' => User::dealers()->count(),
            'clients_count' => User::clients()->count(),
            'cars_in_transit' => Car::inTransit()->count(),
        ];

        // Get recent audit logs
        $recentLogs = $this->godModeService->getRecentLogs(10);

        // Get grouped permissions and styles for quick overview
        $permissionGroups = count($this->godModeService->getGroupedPermissions());
        $styleGroups = count($this->godModeService->getGroupedStyles());

        return view('god-mode.dashboard', compact(
            'stats',
            'recentLogs',
            'permissionGroups',
            'styleGroups'
        ));
    }

    /**
     * Show audit logs.
     */
    public function auditLogs(Request $request)
    {
        $logs = GodModeAuditLog::with('superAdmin')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('god-mode.audit-logs', compact('logs'));
    }
}
