<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session Keep-Alive Middleware
 *
 * Updates the sessions table last_activity on every request.
 * This makes AJAX activity count as active session usage.
 */
class SessionKeepAlive
{
    public function handle(Request $request, Closure $next): Response
    {
        // Override PHP's native GC settings as safety measure
        $lifetime = config('session.lifetime', 480) * 60;
        @ini_set('session.gc_maxlifetime', $lifetime);
        @ini_set('session.gc_probability', 0);
        @ini_set('session.gc_divisor', 1);

        $response = $next($request);

        // Update session's last_activity timestamp
        if ($request->hasSession()) {
            $session = $request->session();
            $session->put('_last_touch', time());

            // Database driver: explicitly update last_activity
            if (config('session.driver') === 'database') {
                try {
                    DB::table(config('session.table', 'sessions'))
                        ->where('id', $session->getId())
                        ->update(['last_activity' => time()]);
                } catch (\Throwable $e) {}
            }

            if ($request->ajax() || $request->wantsJson()) {
                $response->headers->set('X-Session-Refreshed', '1');
            }
        }

        return $response;
    }
}
