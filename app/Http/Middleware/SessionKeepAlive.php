<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session Keep-Alive Middleware
 *
 * Runs on every web request to:
 * 1. Disable PHP's native GC on every request (belt-and-suspenders)
 * 2. Touch the session to update its last_activity timestamp
 * 3. For database driver: Laravel automatically updates last_activity
 *    on every session write — no manual file touch needed.
 * 4. Set response headers so the client knows the session is alive
 */
class SessionKeepAlive
{
    public function handle(Request $request, Closure $next): Response
    {
        // Disable PHP's native GC on every request (belt-and-suspenders —
        // database driver doesn't use PHP file sessions, but keep for safety)
        @ini_set('session.gc_maxlifetime', 28800);
        @ini_set('session.gc_probability', 0);
        @ini_set('session.gc_divisor', 1);
        @ini_set('session.cookie_lifetime', 28800);

        $response = $next($request);

        // Update session's last activity timestamp.
        // For the database driver, Laravel automatically updates
        // the last_activity column on every session write, so
        // the session will never expire while the user is active.
        if ($request->hasSession()) {
            try {
                $session = $request->session();
                $session->put('_last_touch', time());

                // Set response header so the client knows the session is alive
                if ($request->ajax() || $request->wantsJson()) {
                    $response->headers->set('X-Session-Refreshed', '1');
                }
            } catch (\Throwable $e) {
                // Don't crash the request if session touch fails
            }
        }

        return $response;
    }
}
