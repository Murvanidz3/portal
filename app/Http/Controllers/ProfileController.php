<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display profile edit form.
     */
    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update profile.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'full_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'პროფილი განახლდა!');
    }

    /**
     * Show password change form.
     */
    public function showChangePassword()
    {
        return view('profile.change-password');
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = auth()->user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'მიმდინარე პაროლი არასწორია!']);
        }

        // Update password
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('profile.edit')
            ->with('success', 'პაროლი წარმატებით შეიცვალა!');
    }

    /**
     * Get user statistics.
     */
    public function stats()
    {
        $user = auth()->user();

        $stats = [
            'total_cars' => $user->cars()->count(),
            'delivered_cars' => $user->cars()->delivered()->count(),
            'in_transit_cars' => $user->cars()->inTransit()->count(),
            'total_debt' => $user->getTotalDebt(),
            'total_paid' => $user->getTotalPaid(),
            'unread_notifications' => $user->getUnreadNotificationsCount(),
            'balance' => $user->balance,
        ];

        return response()->json($stats);
    }
}
