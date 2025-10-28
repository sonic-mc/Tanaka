<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles
     */
    public function handle(Request $request, Closure $next, $roles): Response
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Unauthorized');
        }

        // Split roles by pipe (e.g., "admin|nurse")
        $allowedRoles = explode('|', $roles);

        // Check if user's role matches any allowed role
        if (! in_array($user->role, $allowedRoles)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
