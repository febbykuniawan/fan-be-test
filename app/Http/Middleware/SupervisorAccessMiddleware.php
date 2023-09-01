<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SupervisorAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user->npp_supervisor) {
            // User memiliki npp_supervisor, yang berarti dia adalah user biasa
            return response()->json(['message' => 'You do not have access'], 403);
        }

        // User tidak memiliki npp_supervisor, yang berarti dia adalah supervisor
        return $next($request);
    }
}
