<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session Keep-Alive Middleware
 *
 * Runs on every web request to:
 * 1. Disable PHP's native GC on every request
 * 2. Touch the session to update its last_activity
 * 3. For file driver: touch the session file to update mtime
 * 4. Set response headers so the client knows the session is alive
 */
class SessionKeepAlive
{
    public function handle(Request $request, Closure $next): Response
    {
        // Disable PHP's native GC on every request
        @ini_set('session.gc_maxlifetime', 28800);
        @ini_set('session.gc_probability', 0);
        @ini_set('session.gc_divisor', 1);
        @ini_set('session.cookie_lifetime', 28800);

        $response = $next($request);

        // Update session's last activity
        if ($request->hasSession()) {
            try {
                $session = $request->session();
                $session->put('_last_touch', time());

                // Touch the session file to update its modification time
                // This helps Laravel's own session expiry check see it as active
                $driver = config('session.driver');
                if ($driver === 'file' || $driver === 'safe_file') {
                    $sessionId = $session->getId();
                    $sessionPath = config('session.files', storage_path('framework/sessions'));
                    $sessionFile = $sessionPath . DIRECTORY_SEPARATOR . 'sess_' . $sessionId;
                    if ($sessionId && file_exists($sessionFile)) {
                        @touch($sessionFile);
                    }
                }

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
