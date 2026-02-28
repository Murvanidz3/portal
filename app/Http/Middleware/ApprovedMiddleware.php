<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApprovedMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->isApproved()) {
            auth()->logout();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'თქვენი ანგარიში არ არის დადასტურებული.'
                ], 403);
            }
            
            return redirect()->route('login')
                ->with('error', 'თქვენი ანგარიში არ არის დადასტურებული. დაუკავშირდით ადმინისტრატორს.');
        }

        return $next($request);
    }
}
