<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session Keep-Alive Middleware
 *
 * Prevents session expiry during AJAX activity (mark entry, attendance, etc.)
 * by explicitly updating the session's last_activity timestamp.
 *
 * Works with BOTH session drivers:
 * - database: Explicitly updates the last_activity column in the sessions table
 * - file: Overrides PHP's native GC, creates backup files, and restores from backup
 *
 * KEY FEATURE for file driver: Session backup & restore.
 * PHP's native session.gc_maxlifetime on XAMPP can be as low as 300 seconds (5 min)
 * and it deletes session files regardless of Laravel's lifetime setting.
 * This middleware creates backup copies of session files that PHP's GC won't find,
 * and restores them if the main file gets deleted by GC.
 */
class SessionKeepAlive
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Override PHP's native session garbage collection settings.
        // This is critical for the FILE driver on XAMPP, where PHP's
        // session.gc_maxlifetime can be as low as 300 seconds (5 min).
        $lifetime = config('session.lifetime', 480) * 60; // minutes to seconds
        @ini_set('session.gc_maxlifetime', $lifetime);
        @ini_set('session.gc_probability', 0);
        @ini_set('session.gc_divisor', 1);

        // 2. Override PHP's session cookie parameters to match Laravel's config.
        $cookieParams = [
            'lifetime' => $lifetime,
            'path'     => config('session.path', '/'),
            'domain'   => config('session.domain', null) ?? '',
            'secure'   => config('session.secure', false),
            'httponly' => config('session.http_only', true),
            'samesite' => config('session.same_site', 'lax'),
        ];
        if (session_status() === PHP_SESSION_NONE) {
            @session_set_cookie_params($cookieParams);
        }

        // 3. For file driver: BEFORE processing the request, check if the session
        //    file was deleted by PHP's GC. If so, restore it from backup.
        //    This MUST happen before Laravel's StartSession middleware reads the session.
        $driver = config('session.driver');
        if ($driver === 'file') {
            $this->restoreSessionFromBackup($request);
        }

        // 4. Process the request
        $response = $next($request);

        // 5. After the request, explicitly touch the session to keep it alive.
        //    This is the KEY mechanism that treats AJAX activity as "active" session
        //    usage, preventing expiry during mark entry and other long operations.
        if ($request->hasSession()) {
            $session = $request->session();

            // Write a timestamp to force Laravel to save the session.
            $session->put('_last_activity_touch', time());

            // For database driver: explicitly update the last_activity column.
            if ($driver === 'database') {
                try {
                    $table = config('session.table', 'sessions');
                    $sessionId = $session->getId();
                    DB::table($table)
                        ->where('id', $sessionId)
                        ->update(['last_activity' => time()]);
                } catch (\Throwable $e) {
                    // Database might not be available — don't crash
                }
            }

            // For file driver: create a backup copy that PHP's GC won't delete.
            // The backup is stored with a 'backup_' prefix, which PHP's GC
            // won't recognize as a session file, so it's safe from deletion.
            if ($driver === 'file') {
                $this->backupSessionFile($session->getId());
            }

            // Add a response header for AJAX requests so the client can confirm
            // the session was refreshed.
            if ($request->ajax() || $request->wantsJson()) {
                $response->headers->set('X-Session-Refreshed', '1');
                $response->headers->set('X-Session-Driver', $driver);
            }
        }

        return $response;
    }

    /**
     * Restore a session file from backup if PHP's GC deleted the original.
     *
     * This is the CRITICAL fix for the <5 minute session expiry on XAMPP.
     * PHP's garbage collection runs before our middleware can override it,
     * so it can delete session files that are still valid.
     * By restoring from backup, we ensure the session survives.
     */
    private function restoreSessionFromBackup(Request $request): void
    {
        try {
            $sessionId = $request->cookies->get(config('session.cookie', 'redemption_session'));
            if (!$sessionId) return;

            $sessionPath = storage_path('framework/sessions');
            $mainFile = $sessionPath . '/' . $sessionId;
            $backupFile = $sessionPath . '/backup_' . $sessionId;

            // If the main session file doesn't exist but the backup does,
            // PHP's GC deleted the original. Restore it!
            if (!file_exists($mainFile) && file_exists($backupFile)) {
                @copy($backupFile, $mainFile);
                // Update the file's modification time so it looks fresh
                @touch($mainFile);
            }
        } catch (\Throwable $e) {
            // Restore failed — don't crash
        }
    }

    /**
     * Create a backup copy of the session file that PHP's GC won't delete.
     */
    private function backupSessionFile(string $sessionId): void
    {
        try {
            $sessionPath = storage_path('framework/sessions');
            $mainFile = $sessionPath . '/' . $sessionId;
            $backupFile = $sessionPath . '/backup_' . $sessionId;

            if (file_exists($mainFile)) {
                @copy($mainFile, $backupFile);
            }
        } catch (\Throwable $e) {
            // Backup failed — don't crash
        }
    }
}
