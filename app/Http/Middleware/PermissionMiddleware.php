<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('permission:academic_years.view')
     * Or multiple: ->middleware('permission:academic_years.view,academic_years.create')
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        // Admins always pass
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Check if user has any of the required permissions
        if (!empty($permissions) && !$user->hasAnyPermission($permissions)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have permission to perform this action.'], 403);
            }
            abort(403, 'You do not have permission to access this section.');
        }

        return $next($request);
    }
}
