<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Transaction::with(['car', 'user'])
            ->orderBy('payment_date', 'desc');

        // Non-admin users only see their transactions
        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('car', function ($q) use ($user) {
                      $q->where('user_id', $user->id);
                  });
            });
        }

        // Filter by purpose
        if ($request->filled('purpose')) {
            $query->where('purpose', $request->purpose);
        }

        // Filter by user
        if ($request->filled('user_id') && $user->isAdmin()) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        // Search by car VIN or comment
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhereHas('car', function ($q) use ($search) {
                      $q->where('vin', 'like', "%{$search}%")
                        ->orWhere('make_model', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        // Get totals
        $totalsQuery = Transaction::query();
        if (!$user->isAdmin()) {
            $totalsQuery->where('user_id', $user->id);
        }
        $totals = [
            'total_amount' => (clone $totalsQuery)->sum('amount'),
            'this_month' => (clone $totalsQuery)->whereMonth('payment_date', now()->month)->sum('amount'),
        ];

        $purposes = Transaction::getPurposes();
        $users = $user->isAdmin() ? User::orderBy('full_name')->get() : collect();

        return view('transactions.index', compact('transactions', 'totals', 'purposes', 'users'));
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create(Request $request)
    {
        $car = null;
        if ($request->filled('car_id')) {
            $car = Car::findOrFail($request->car_id);
        }

        $cars = Car::forUser(auth()->user())
            ->orderBy('created_at', 'desc')
            ->get();

        $users = auth()->user()->isAdmin() 
            ? User::orderBy('full_name')->get() 
            : collect([auth()->user()]);

        $purposes = Transaction::getPurposes();

        return view('transactions.create', compact('car', 'cars', 'users', 'purposes'));
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'nullable|exists:cars,id',
            'user_id' => 'nullable|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'purpose' => 'required|in:' . implode(',', array_keys(Transaction::getPurposes())),
            'comment' => 'nullable|string|max:500',
            'update_car_paid' => 'boolean',
        ]);

        // Set user_id if not admin
        if (!auth()->user()->isAdmin()) {
            $validated['user_id'] = auth()->id();
        }

        $transaction = Transaction::create($validated);
        
        // Observer automatically handles paid_amount and balance updates

        return redirect()->route('transactions.index')
            ->with('success', 'ტრანზაქცია წარმატებით დაემატა!');
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction)
    {
        $this->authorizeView($transaction);
        
        $transaction->load(['car', 'user']);
        
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Transaction $transaction)
    {
        $this->authorizeEdit($transaction);

        // Load car relationship if exists
        $transaction->load('car');

        $cars = Car::forUser(auth()->user())
            ->orderBy('created_at', 'desc')
            ->get();

        $users = auth()->user()->isAdmin() 
            ? User::orderBy('full_name')->get() 
            : collect([auth()->user()]);

        $purposes = Transaction::getPurposes();

        return view('transactions.edit', compact('transaction', 'cars', 'users', 'purposes'));
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $this->authorizeEdit($transaction);

        $validated = $request->validate([
            'car_id' => 'nullable|exists:cars,id',
            'user_id' => 'nullable|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'purpose' => 'required|in:' . implode(',', array_keys(Transaction::getPurposes())),
            'comment' => 'nullable|string|max:500',
        ]);

        $transaction->update($validated);
        
        // Observer automatically handles paid_amount and balance updates

        return redirect()->route('transactions.index')
            ->with('success', 'ტრანზაქცია წარმატებით განახლდა!');
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(Transaction $transaction)
    {
        $this->authorizeEdit($transaction);

        // Load relationships before deletion
        $transaction->load(['car', 'user']);

        $transaction->delete();
        
        // Observer automatically handles paid_amount and balance updates

        return redirect()->route('transactions.index')
            ->with('success', 'ტრანზაქცია წარმატებით წაიშალა და ფინანსური ბალანსები განახლდა!');
    }

    /**
     * Check if user can view the transaction
     */
    protected function authorizeView(Transaction $transaction): void
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return;
        }

        if ($transaction->user_id === $user->id) {
            return;
        }

        if ($transaction->car && $transaction->car->user_id === $user->id) {
            return;
        }

        abort(403);
    }

    /**
     * Check if user can edit the transaction
     */
    protected function authorizeEdit(Transaction $transaction): void
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'მხოლოდ ადმინს შეუძლია ტრანზაქციის რედაქტირება.');
        }
    }
}
