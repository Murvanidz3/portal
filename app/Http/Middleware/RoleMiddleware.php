<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Check if user has one of the required roles
        if (!empty($roles) && !in_array($request->user()->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Insufficient permissions.'], 403);
            }
            abort(403, 'არ გაქვთ საკმარისი უფლებები.');
        }

        return $next($request);
    }
}
