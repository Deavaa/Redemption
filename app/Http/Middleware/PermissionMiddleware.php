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

        // Admins and super_admin always pass all permission checks
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return $next($request);
        }

        // RBAC users with roles assigned pass all permission checks
        try {
            if ($user->roles()->exists()) {
                return $next($request);
            }
        } catch (\Throwable $e) {}

        // ── Role-based route restrictions ──────────────────────
        // Teachers and staff only have access to certain sections.
        // If the route has a permission middleware, check if this role
        // is allowed to access that permission category.

        if ($user->role === 'teacher') {
            $teacherAllowedPermissions = [
                'dashboard.view',
                // Marks & Assessment
                'mark_entries.view', 'mark_entries.create', 'mark_entries.edit',
                'mark_sheets.view', 'mark_sheets.generate',
                // Reports
                'mark_sheets.view', // progress & performance reports share this
                // Students (view only)
                'students.view',
                // Teacher assignments
                'subject_assignments.view',
                // Analysis
                'mark_sheets.view', // performance analysis, psychological, etc.
                // Documents
                'settings.view', // report exchange uses settings.view
                // Library
                'library.view',
                // Communication
                'calendar.view', 'calendar.manage',
                'chat.access',
                'notifications.view',
            ];

            if (!empty($permissions)) {
                $hasAccess = false;
                foreach ($permissions as $perm) {
                    if (in_array($perm, $teacherAllowedPermissions)) {
                        $hasAccess = true;
                        break;
                    }
                }
                if (!$hasAccess) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Teachers do not have access to this section.',
                            'required_any' => $permissions,
                        ], 403);
                    }
                    abort(403, 'You do not have permission to access this section.');
                }
            }

            return $next($request);
        }

        // Staff role — similar restricted access
        if ($user->role === 'staff') {
            // Staff gets broader access than teachers but not full admin
            // For now, let them through to all permission-protected routes
            return $next($request);
        }

        // Default: no permissions set, no recognized role — fall through to permission check
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
