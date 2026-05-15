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
     * Or multiple (ANY match): ->middleware('permission:academic_years.view,academic_years.create')
     * Or ALL match: ->middleware('permission:academic_years.view&academic_years.create')
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Authentication required.'], 401);
            }
            abort(401);
        }

        // Admins always pass
        if ($user->role === 'admin') {
            return $next($request);
        }

        if (empty($permissions)) {
            return $next($request);
        }

        // Check if any permission uses & (ALL match mode)
        $firstPerm = $permissions[0] ?? '';
        if (str_contains($firstPerm, '&')) {
            $allPerms = explode('&', $firstPerm);
            if (!$user->hasAllPermissions($allPerms)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'You do not have all required permissions.',
                        'required' => $allPerms,
                    ], 403);
                }
                abort(403, 'You do not have all required permissions to access this section.');
            }
        } else {
            // ANY match mode (default)
            if (!$user->hasAnyPermission($permissions)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'You do not have permission to perform this action.',
                        'required_any' => $permissions,
                    ], 403);
                }
                abort(403, 'You do not have permission to access this section.');
            }
        }

        return $next($request);
    }
}
