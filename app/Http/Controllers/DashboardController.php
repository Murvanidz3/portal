<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Car::with('user');

        // Role-based filtering
        if ($user->isDealer()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isClient()) {
            $query->where('client_user_id', $user->id);
        }

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Get recent cars - order by created_at desc, then by id desc to ensure consistent ordering
        // This ensures all cars are shown, including the first one (id-1)
        // Show only 4 most recent cars on dashboard
        $recentCars = (clone $query)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(4)
            ->get();

        // Status counts for current user
        $statusCounts = Car::forUser($user)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Stats
        $stats = $this->getStats($user);

        // Recent transactions (for admin/dealer)
        $recentTransactions = [];
        if (!$user->isClient()) {
            $transQuery = Transaction::with(['car', 'user'])->orderBy('created_at', 'desc')->limit(5);
            
            if ($user->isDealer()) {
                $transQuery->whereHas('car', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }
            
            $recentTransactions = $transQuery->get();
        }

        return view('dashboard.index', compact('recentCars', 'statusCounts', 'stats', 'recentTransactions'));
    }

    protected function getStats(User $user): array
    {
        $stats = [
            'total_cars' => 0,
            'in_transit' => 0,
            'delivered' => 0,
            'total_debt' => 0,
            'total_paid' => 0,
            'total_cost' => 0,
        ];

        $query = Car::forUser($user);

        $stats['total_cars'] = (clone $query)->count();
        $stats['in_transit'] = (clone $query)->inTransit()->count();
        $stats['delivered'] = (clone $query)->delivered()->count();

        // Financial stats - load cars and use Car model accessors for accurate calculation
        $cars = (clone $query)->get();
        
        $stats['total_cost'] = $cars->sum('total_cost');
        $stats['total_paid'] = $cars->sum('paid_amount');
        $stats['total_debt'] = $cars->sum(function ($car) {
            return max(0, $car->debt);
        });

        // Admin-specific stats
        if ($user->isAdmin()) {
            $stats['total_users'] = User::count();
            $stats['total_dealers'] = User::dealers()->count();
        }

        return $stats;
    }
}
