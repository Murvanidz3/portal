<?php

namespace App\Http\Middleware;

use App\Models\GodModePermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckGodModePermission
{
    /**
     * Handle an incoming request.
     * Checks if a feature is enabled for the current user's role.
     *
     * @param  string  $featureKey  The permission key to check
     */
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $user = auth()->user();

        // If no user is logged in, let normal auth handle it
        if (!$user) {
            return $next($request);
        }

        // Determine user role
        $role = $user->role ?? 'client';

        // Check permission
        if (!GodModePermission::isEnabled($featureKey, $role)) {
            // Return appropriate response based on request type
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'თქვენ არ გაქვთ წვდომა ამ ფუნქციაზე.',
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'თქვენ არ გაქვთ წვდომა ამ ფუნქციაზე.');
        }

        return $next($request);
    }
}
