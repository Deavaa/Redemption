<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session Keep-Alive Middleware
 *
 * With the COOKIE session driver, PHP garbage collection is no longer
 * a concern (no server-side state = nothing for GC to delete).
 *
 * This middleware now serves simpler purposes:
 * 1. Extra safety: disable PHP's native GC on every request
 * 2. Touch the session to keep it alive
 * 3. Set response headers so the client knows the session is alive
 */
class SessionKeepAlive
{
    public function handle(Request $request, Closure $next): Response
    {
        // Disable PHP's native GC as extra safety net
        @ini_set('session.gc_maxlifetime', 28800);
        @ini_set('session.gc_probability', 0);
        @ini_set('session.gc_divisor', 1);
        @ini_set('session.cookie_lifetime', 28800);

        $response = $next($request);

        // Touch the session to keep it alive
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
