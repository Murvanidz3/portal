<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GodModeAuth
{
    /**
     * Handle an incoming request.
     * Ensures only authenticated Super Admins can access God Mode routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via 'god' guard
        if (!Auth::guard('god')->check()) {
            // Store intended URL for redirect after login
            session()->put('god_url.intended', $request->url());
            return redirect()->route('god.login');
        }

        // Check if super admin is active
        $superAdmin = Auth::guard('god')->user();
        if (!$superAdmin->isActive()) {
            Auth::guard('god')->logout();
            return redirect()->route('god.login')
                ->withErrors(['error' => 'თქვენი ანგარიში დეაქტივირებულია.']);
        }

        return $next($request);
    }
}
