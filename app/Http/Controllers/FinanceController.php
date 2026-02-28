<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * Display finance overview.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get dealers with their financial summaries
        if ($user->isAdmin()) {
            $dealersQuery = User::dealers()
                ->approved()
                ->with('cars')
                ->orderBy('full_name');

            if ($request->filled('search')) {
                $search = $request->search;
                $dealersQuery->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('full_name', 'like', "%{$search}%");
                });
            }

            $dealers = $dealersQuery->get()->map(function ($dealer) {
                $dealer->cars_count = $dealer->cars->count();
                $dealer->total_cost = $dealer->cars->sum('total_cost');
                $dealer->total_paid = $dealer->cars->sum('paid_amount');
                $dealer->total_debt = $dealer->cars->sum(function ($car) {
                    return max(0, $car->debt);
                });
                return $dealer;
            });
        } else {
            // For dealers, show only their own summary
            $dealers = collect([$user])->map(function ($dealer) {
                $dealer->load('cars');
                $dealer->cars_count = $dealer->cars->count();
                $dealer->total_cost = $dealer->cars->sum('total_cost');
                $dealer->total_paid = $dealer->cars->sum('paid_amount');
                $dealer->total_debt = $dealer->cars->sum(function ($car) {
                    return max(0, $car->debt);
                });
                return $dealer;
            });
        }

        // Calculate totals directly from cars for accuracy
        $allCars = Car::forUser($user)->get();
        $totals = [
            'total_cost' => $allCars->sum('total_cost'),
            'total_paid' => $allCars->sum('paid_amount'),
            'total_debt' => $allCars->sum(function ($car) {
                return max(0, $car->debt);
            }),
            'total_cars' => $allCars->count(),
        ];

        // Recent transactions
        $recentTransactions = Transaction::with(['car', 'user'])
            ->when(!$user->isAdmin(), function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('finance.index', compact('dealers', 'totals', 'recentTransactions'));
    }

    /**
     * Show dealer's detailed financial info.
     */
    public function show(User $dealer)
    {
        if (!auth()->user()->isAdmin() && auth()->id() !== $dealer->id) {
            abort(403);
        }

        $cars = Car::where('user_id', $dealer->id)
            ->with('transactions')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($car) {
                $car->calculated_debt = $car->debt;
                return $car;
            });

        $transactions = Transaction::where('user_id', $dealer->id)
            ->with('car')
            ->orderBy('payment_date', 'desc')
            ->paginate(20);

        $summary = [
            'total_cost' => $cars->sum('total_cost'),
            'total_paid' => $cars->sum('paid_amount'),
            'total_debt' => $cars->sum('calculated_debt'),
            'cars_with_debt' => $cars->filter(fn($car) => $car->calculated_debt > 0)->count(),
        ];

        return view('finance.show', compact('dealer', 'cars', 'transactions', 'summary'));
    }
}
