<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'მომხმარებელი აუცილებელია',
            'password.required' => 'პაროლი აუცილებელია',
        ]);

        // Find user by username
        $user = User::where('username', $request->username)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['მომხმარებელი ან პაროლი არასწორია'],
            ]);
        }

        // Check if user is active
        if (isset($user->is_active) && !$user->is_active) {
            throw ValidationException::withMessages([
                'username' => ['თქვენი ანგარიში დაბლოკილია'],
            ]);
        }

        // Login user
        Auth::login($user, $request->boolean('remember'));

        // Regenerate session
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
