<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
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
        // Rate limiting: max 5 attempts per IP per 5 minutes
        $key = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'username' => ["ძალიან ბევრი მცდელობა. სცადეთ {$seconds} წამში."],
            ]);
        }

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
            RateLimiter::hit($key, 300); // 5 minute lockout
            throw ValidationException::withMessages([
                'username' => ['მომხმარებელი ან პაროლი არასწორია'],
            ]);
        }

        // Check if user is active
        if (isset($user->is_active) && !$user->is_active) {
            RateLimiter::hit($key, 300);
            throw ValidationException::withMessages([
                'username' => ['თქვენი ანგარიში დაბლოკილია'],
            ]);
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($key);

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
