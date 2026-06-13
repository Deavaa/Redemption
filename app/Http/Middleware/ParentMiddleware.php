<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ParentMiddleware
{
    /**
     * Allow access only to parent users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            // Use path() (relative to app root) instead of getRequestUri() (which includes subdirectory)
            // to avoid the double-path 404 bug where /redemption/parent/... becomes /redemption/redemption/parent/...
            return redirect()->route('login')->withIntended('/' . $request->path());
        }

        if (isset($user->is_active) && !$user->is_active) {
            Auth::logout();
            abort(403, 'Your account has been deactivated.');
        }

        if ($user->role !== 'parent') {
            abort(403, 'You do not have access to the parent portal.');
        }

        return $next($request);
    }
}
