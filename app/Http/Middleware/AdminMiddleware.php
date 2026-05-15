<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        // Legacy check: users.role column
        if ($user->isAdmin()) {
            return $next($request);
        }

        // RBAC check: user has any role that grants admin panel access
        try {
            if ($user->roles()->exists()) {
                return $next($request);
            }
        } catch (\Throwable $e) {}

        abort(403, 'You do not have access to the admin panel.');
    }
}
