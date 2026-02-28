<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Car;
use App\Models\Transaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Display wallet overview.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            // Admin sees all users with balances
            $usersQuery = User::query()
                ->where('balance', '>', 0)
                ->orWhereHas('transactions', function ($q) {
                    $q->where('purpose', Transaction::PURPOSE_BALANCE_TOPUP);
                })
                ->orderBy('balance', 'desc');

            if ($request->filled('search')) {
                $search = $request->search;
                $usersQuery->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('full_name', 'like', "%{$search}%");
                });
            }

            $users = $usersQuery->paginate(15)->withQueryString();

            // Get totals
            $totals = [
                'total_balance' => User::sum('balance'),
                'users_with_balance' => User::where('balance', '>', 0)->count(),
            ];

            $currentUser = auth()->user();
            $carsForTransfer = Car::forUser($currentUser)->orderBy('vin')->get();
            $carsWithOverpayment = Car::forUser($currentUser)->get()
                ->filter(fn ($c) => (float) $c->paid_amount > (float) $c->total_cost)
                ->values()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'vin' => $c->vin,
                    'make_model' => $c->make_model,
                    'transferable' => round((float) $c->paid_amount - (float) $c->total_cost, 2),
                ])
                ->all();

            return view('wallet.index', compact('users', 'totals', 'currentUser', 'carsForTransfer', 'carsWithOverpayment'));
        }

        // Non-admin users see their own wallet
        $transactions = Transaction::where('user_id', $user->id)
            ->balanceTopups()
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        $carsForTransfer = Car::forUser($user)->orderBy('vin')->get();
        $carsWithOverpayment = Car::forUser($user)->get()
            ->filter(fn ($c) => (float) $c->paid_amount > (float) $c->total_cost)
            ->values()
            ->map(fn ($c) => [
                'id' => $c->id,
                'vin' => $c->vin,
                'make_model' => $c->make_model,
                'transferable' => round((float) $c->paid_amount - (float) $c->total_cost, 2),
            ])
            ->all();

        return view('wallet.show', compact('user', 'transactions', 'carsForTransfer', 'carsWithOverpayment'));
    }

    /**
     * Show add balance form.
     */
    public function create()
    {
        $users = User::orderBy('full_name')->get();
        return view('wallet.create', compact('users'));
    }

    /**
     * Add balance to user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Create transaction
        // Observer automatically handles balance updates
        Transaction::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'payment_date' => now(),
            'purpose' => Transaction::PURPOSE_BALANCE_TOPUP,
            'comment' => $validated['comment'] ?? 'ბალანსის შევსება',
        ]);

        return redirect()->route('wallet.index')
            ->with('success', 'ბალანსი წარმატებით შეივსო!');
    }

    /**
     * Show user wallet details.
     */
    public function show(User $user)
    {
        if (!auth()->user()->isAdmin() && auth()->id() !== $user->id) {
            abort(403);
        }

        $transactions = Transaction::where('user_id', $user->id)
            ->balanceTopups()
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        $carsForTransfer = Car::forUser($user)->orderBy('vin')->get();
        $carsWithOverpayment = Car::forUser($user)->get()
            ->filter(fn ($c) => (float) $c->paid_amount > (float) $c->total_cost)
            ->values()
            ->map(fn ($c) => [
                'id' => $c->id,
                'vin' => $c->vin,
                'make_model' => $c->make_model,
                'transferable' => round((float) $c->paid_amount - (float) $c->total_cost, 2),
            ])
            ->all();

        return view('wallet.show', compact('user', 'transactions', 'carsForTransfer', 'carsWithOverpayment'));
    }

    /**
     * Transfer from current user's wallet to a car.
     */
    public function transferWalletToCar(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $car = Car::findOrFail($validated['car_id']);
        if (! Car::forUser($user)->where('id', $car->id)->exists()) {
            abort(403, 'ამ მანქანაზე წვდომა არ გაქვთ.');
        }

        $balance = (float) $user->balance;
        $amount = (float) $validated['amount'];
        if ($amount > $balance) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'საფულეში არასაკმარისი თანხა. მაქსიმუმ: $' . number_format($balance, 2)], 422);
            }
            return redirect()->back()->with('error', 'საფულეში არასაკმარისი თანხა. მაქსიმუმ: $' . number_format($balance, 2));
        }

        Transaction::create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'amount' => $amount,
            'payment_date' => now(),
            'purpose' => Transaction::PURPOSE_WALLET_TO_CAR,
            'comment' => 'საფულიდან მანქანაზე (VIN: ' . $car->vin . ')',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'თანხა წარმატებით გადაირიცხა მანქანაზე.',
                'balance' => (float) $user->fresh()->balance,
            ]);
        }
        return redirect()->back()->with('success', 'თანხა წარმატებით გადაირიცხა მანქანაზე.');
    }

    /**
     * Transfer from one car to another (only overpayment amount).
     */
    public function transferCarToCar(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'from_car_id' => 'required|exists:cars,id',
            'to_car_id' => 'required|exists:cars,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validated['from_car_id'] === $validated['to_car_id']) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'აირჩიეთ სხვადასხვა მანქანა.'], 422);
            }
            return redirect()->back()->with('error', 'აირჩიეთ სხვადასხვა მანქანა.');
        }

        $fromCar = Car::findOrFail($validated['from_car_id']);
        $toCar = Car::findOrFail($validated['to_car_id']);

        if (! Car::forUser($user)->where('id', $fromCar->id)->exists() || ! Car::forUser($user)->where('id', $toCar->id)->exists()) {
            abort(403, 'ამ მანქანებზე წვდომა არ გაქვთ.');
        }

        $transferable = max(0, (float) $fromCar->paid_amount - (float) $fromCar->total_cost);
        $amount = (float) $validated['amount'];
        if ($amount > $transferable) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'გადასატანი თანხა არ უნდა აღემატებოდეს პლიუს დეპოზიტს. მაქსიმუმ: $' . number_format($transferable, 2)], 422);
            }
            return redirect()->back()->with('error', 'გადასატანი თანხა არ უნდა აღემატებოდეს პლიუს დეპოზიტს. მაქსიმუმ: $' . number_format($transferable, 2));
        }

        Transaction::create([
            'user_id' => $user->id,
            'car_id' => $fromCar->id,
            'amount' => $amount,
            'payment_date' => now(),
            'purpose' => Transaction::PURPOSE_CAR_TO_CAR_OUT,
            'comment' => 'გადაცემა მანქანაზე VIN: ' . $toCar->vin,
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'car_id' => $toCar->id,
            'amount' => $amount,
            'payment_date' => now(),
            'purpose' => Transaction::PURPOSE_CAR_TO_CAR_IN,
            'comment' => 'მიღებული მანქანიდან VIN: ' . $fromCar->vin,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'თანხა წარმატებით გადაირიცხა მანქანიდან მანქანაზე.']);
        }
        return redirect()->back()->with('success', 'თანხა წარმატებით გადაირიცხა მანქანიდან მანქანაზე.');
    }
}
