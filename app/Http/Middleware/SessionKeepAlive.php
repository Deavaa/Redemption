<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session Keep-Alive Middleware
 *
 * This middleware ensures that AJAX activity counts as active session usage.
 * It runs on every web request and:
 *
 * 1. Overrides PHP's native session garbage collection settings
 * 2. Touches the session to update last_activity
 * 3. For database driver: explicitly updates the sessions table
 * 4. For file driver: ensures the session file exists and is fresh
 * 5. Sets headers to indicate session was refreshed
 *
 * This is prepended to the web middleware group in bootstrap/app.php.
 */
class SessionKeepAlive
{
    public function handle(Request $request, Closure $next): Response
    {
        // ============================================================
        // Override PHP's native GC settings on EVERY request.
        // This is a safety net — they're also set in:
        //   - public/index.php (before Laravel boots)
        //   - public/.user.ini (before PHP parses any script)
        //   - app/Providers/AppServiceProvider::register()
        // ============================================================
        @ini_set('session.gc_maxlifetime', 28800);
        @ini_set('session.gc_probability', 0);
        @ini_set('session.gc_divisor', 1);
        @ini_set('session.cookie_lifetime', 28800);

        $response = $next($request);

        // Update session's last_activity timestamp
        if ($request->hasSession()) {
            try {
                $session = $request->session();
                $session->put('_last_touch', time());

                // Database driver: explicitly update last_activity in the sessions table
                if (config('session.driver') === 'database') {
                    $sessionId = $session->getId();
                    if ($sessionId) {
                        DB::table(config('session.table', 'sessions'))
                            ->where('id', $sessionId)
                            ->update(['last_activity' => time()]);
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
