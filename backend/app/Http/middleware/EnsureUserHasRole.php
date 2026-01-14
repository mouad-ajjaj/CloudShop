<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Check if user is logged in
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 2. Check permissions (Admin can access everything, otherwise role must match)
        if ($request->user()->role !== $role && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access forbidden. You do not have the required role.'], 403);
        }

        return $next($request);
    }
}