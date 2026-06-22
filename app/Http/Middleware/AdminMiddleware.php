<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Allow access to the admin panel for:
     *  1. Users with legacy role = 'admin'
     *  2. Users with RBAC roles assigned (role_user table)
     *  3. Users with legacy role = 'teacher' (they access via RBAC permissions)
     *  4. Users with legacy role = 'staff' / 'super_admin'
     *
     * Students and parents should NOT access the admin panel —
     * they get their own dedicated routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            // For AJAX/fetch requests, return 401 JSON instead of redirecting.
            // fetch() follows redirects, and a 302 redirect from POST to GET /login
            // causes a 404 because there's no POST route for /login.
            if ($request->expectsJson() || $request->ajax() ||
                $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                $request->header('Accept') === 'application/json') {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login')->withIntended('/' . $request->path());
        }

        // Inactive users cannot access the panel
        if (method_exists($user, 'is_active') && !$user->is_active) {
            Auth()->logout();
            abort(403, 'Your account has been deactivated.');
        }

        // 1. Legacy check: users.role column = admin
        if ($user->isAdmin()) {
            return $next($request);
        }

        // 2. RBAC check: user has any role assigned via role_user table
        try {
            if ($user->roles()->exists()) {
                return $next($request);
            }
        } catch (\Throwable $e) {}

        // 3. Legacy role check: teacher, staff, super_admin should access admin panel
        $allowedLegacyRoles = ['teacher', 'staff', 'super_admin', 'branch_principal', 'general_manager', 'librarian', 'cashier', 'registrar', 'finance', 'hr'];
        if (in_array($user->role, $allowedLegacyRoles)) {
            return $next($request);
        }

        abort(403, 'You do not have access to the admin panel.');
    }
}
