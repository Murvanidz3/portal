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

        // Clients see all their cars; admins/dealers see 4 most recent
        $carsQuery = (clone $query)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        $recentCars = $user->isClient()
            ? $carsQuery->get()
            : $carsQuery->limit(4)->get();

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

        // Financial stats - use aggregate queries instead of loading all cars into memory
        $financials = (clone $query)->selectRaw('
            COALESCE(SUM(vehicle_cost + auction_fee + shipping_cost + additional_cost), 0) as total_cost,
            COALESCE(SUM(paid_amount), 0) as total_paid,
            COALESCE(SUM(GREATEST(0, (vehicle_cost + auction_fee + shipping_cost + additional_cost) - paid_amount)), 0) as total_debt
        ')->first();

        $stats['total_cost'] = (float) $financials->total_cost;
        $stats['total_paid'] = (float) $financials->total_paid;
        $stats['total_debt'] = (float) $financials->total_debt;

        // Admin-specific stats
        if ($user->isAdmin()) {
            $stats['total_users'] = User::count();
            $stats['total_dealers'] = User::dealers()->count();
        }

        return $stats;
    }
}
