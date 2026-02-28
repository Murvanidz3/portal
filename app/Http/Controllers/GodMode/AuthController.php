<?php

namespace App\Http\Controllers\GodMode;

use App\Http\Controllers\Controller;
use App\Models\GodModeAuditLog;
use App\Models\SuperAdmin;
use App\Services\GodModeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected GodModeService $godModeService;

    public function __construct(GodModeService $godModeService)
    {
        $this->godModeService = $godModeService;
    }

    /**
     * Show the God Mode login form.
     */
    public function showLoginForm()
    {
        // If already logged in, redirect to dashboard
        if (Auth::guard('god')->check()) {
            return redirect()->route('god.dashboard');
        }

        return view('god-mode.login');
    }

    /**
     * Handle God Mode login.
     */
    public function login(Request $request)
    {
        // Rate limiting
        $key = 'god-login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'username' => "ძალიან ბევრი მცდელობა. სცადეთ {$seconds} წამში.",
            ]);
        }

        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find super admin by username or email
        $superAdmin = SuperAdmin::where('username', $credentials['username'])
            ->orWhere('email', $credentials['username'])
            ->first();

        if (!$superAdmin || !Hash::check($credentials['password'], $superAdmin->password)) {
            RateLimiter::hit($key, 300); // 5 minutes lockout
            throw ValidationException::withMessages([
                'username' => 'არასწორი მონაცემები.',
            ]);
        }

        if (!$superAdmin->isActive()) {
            throw ValidationException::withMessages([
                'username' => 'ანგარიში დეაქტივირებულია.',
            ]);
        }

        // Clear rate limiter on success
        RateLimiter::clear($key);

        // Login
        Auth::guard('god')->login($superAdmin, $request->boolean('remember'));

        // Update login info
        $superAdmin->updateLoginInfo($request->ip());

        // Log the login
        GodModeAuditLog::log($superAdmin->id, 'login');

        // Regenerate session
        $request->session()->regenerate();

        // Redirect to intended or dashboard
        return redirect()->intended(route('god.dashboard'));
    }

    /**
     * Handle God Mode logout.
     */
    public function logout(Request $request)
    {
        $superAdmin = Auth::guard('god')->user();

        if ($superAdmin) {
            GodModeAuditLog::log($superAdmin->id, 'logout');
        }

        Auth::guard('god')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('god.login');
    }
}
