<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query()->orderBy('created_at', 'desc');

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by approval status
        if ($request->filled('approved')) {
            $query->where('approved', $request->approved === '1');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = User::getRoles();

        // Get role counts
        $roleCounts = User::selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        return view('users.index', compact('users', 'roles', 'roleCounts'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = User::getRoles();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'full_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => ['required', 'confirmed', Password::min(6)],
            'role' => 'required|in:admin,dealer,client',
            'balance' => 'nullable|numeric|min:0',
            'sms_enabled' => 'boolean',
            'approved' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['sms_enabled'] = $request->boolean('sms_enabled');
        
        // Administrators are always approved
        if ($validated['role'] === 'admin') {
            $validated['approved'] = true;
        } else {
            $validated['approved'] = $request->boolean('approved', true);
        }
        
        $validated['balance'] = $validated['balance'] ?? 0;

        $user = User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'მომხმარებელი წარმატებით დაემატა!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['cars', 'transactions']);
        
        $stats = [
            'total_cars' => $user->cars()->count(),
            'delivered_cars' => $user->cars()->delivered()->count(),
            'in_transit_cars' => $user->cars()->inTransit()->count(),
            'total_debt' => $user->getTotalDebt(),
            'total_paid' => $user->getTotalPaid(),
        ];

        return view('users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = User::getRoles();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'full_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'password' => ['nullable', 'confirmed', Password::min(6)],
            'role' => 'required|in:admin,dealer,client',
            'balance' => 'nullable|numeric|min:0',
            'sms_enabled' => 'boolean',
            'approved' => 'boolean',
        ]);

        // Only update password if provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['sms_enabled'] = $request->boolean('sms_enabled');
        
        // Administrators are always approved - don't allow changing their approval status
        if ($user->role === 'admin') {
            unset($validated['approved']);
            // Ensure admin is always approved
            $validated['approved'] = true;
        } else {
            $validated['approved'] = $request->boolean('approved');
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'მომხმარებელი წარმატებით განახლდა!');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'თქვენ ვერ წაშლით საკუთარ თავს!');
        }

        // Prevent deleting if user has cars
        if ($user->cars()->exists()) {
            return redirect()->back()
                ->with('error', 'მომხმარებელს აქვს მანქანები. ჯერ წაშალეთ ან გადაიტანეთ მისი მანქანები.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'მომხმარებელი წარმატებით წაიშალა!');
    }

    /**
     * Toggle user approval status
     */
    public function toggleApproval(User $user)
    {
        // Prevent toggling yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'თქვენ ვერ შეცვლით საკუთარ სტატუსს!'
            ], 403);
        }

        // Prevent toggling administrator - they are always active
        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'ადმინისტრატორი მუდმივად აქტიურია!'
            ], 403);
        }

        $user->approved = !$user->approved;
        $user->save();

        return response()->json([
            'success' => true,
            'approved' => $user->approved,
            'message' => $user->approved ? 'მომხმარებელი დადასტურებულია!' : 'მომხმარებელი დაბლოკილია!'
        ]);
    }

    /**
     * Toggle user SMS status
     */
    public function toggleSms(User $user)
    {
        $user->sms_enabled = !$user->sms_enabled;
        $user->save();

        return response()->json([
            'success' => true,
            'sms_enabled' => $user->sms_enabled,
            'message' => $user->sms_enabled ? 'SMS ჩართულია!' : 'SMS გამორთულია!'
        ]);
    }

    /**
     * Update user balance
     */
    public function updateBalance(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'action' => 'required|in:add,subtract,set',
        ]);

        $amount = (float) $validated['amount'];

        switch ($validated['action']) {
            case 'add':
                $user->balance += $amount;
                break;
            case 'subtract':
                $user->balance -= $amount;
                break;
            case 'set':
                $user->balance = $amount;
                break;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'balance' => $user->balance,
            'formatted_balance' => $user->getFormattedBalance(),
            'message' => 'ბალანსი განახლდა!'
        ]);
    }
}
