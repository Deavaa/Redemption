<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\Models\CalendarEvent;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ============================================================
        // SAFETY NET: Auto-generate APP_KEY if missing.
        // ============================================================
        if (empty(config('app.key'))) {
            $key = 'base64:' . base64_encode(random_bytes(32));
            config(['app.key' => $key]);
            $envPath = base_path('.env');
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                if (preg_match('/^APP_KEY\s*=/m', $envContent)) {
                    $envContent = preg_replace('/^APP_KEY\s*=.*/m', "APP_KEY={$key}", $envContent);
                } else {
                    $envContent .= "\nAPP_KEY={$key}\n";
                }
                @file_put_contents($envPath, $envContent);
            }
        }

        // ============================================================
        // FORCE correct database: always MySQL (never sqlite)
        // ============================================================
        config(['database.default' => 'mysql']);

        // ============================================================
        // SESSION: Use 'database' driver.
        //
        // This is the ONLY reliable session driver on XAMPP.
        // File-based sessions get killed by PHP's native garbage
        // collection (gc) regardless of ini_set settings, because
        // XAMPP's PHP SAPI ignores gc_probability=0 overrides.
        //
        // Database sessions store session data in MySQL, where Laravel
        // has FULL control over the session lifecycle. PHP's native GC
        // cannot touch database rows. Session expiry is handled entirely
        // by Laravel checking the last_activity column against
        // session.lifetime (480 min = 8 hours).
        //
        // Key settings:
        // - session.driver = database
        // - session.cookie = redemption_session_v5 (NEW name to avoid
        //   conflicts with old session cookies from file/cookie drivers)
        // - session.lottery = [2, 100] (2% chance of Laravel GC,
        //   which only deletes expired rows — SAFE)
        // - session.lifetime = 480 (8 hours)
        // ============================================================
        config(['session.driver' => 'database']);
        config(['session.lifetime' => 480]);           // 8 hours
        config(['session.encrypt' => false]);
        config(['session.cookie' => 'redemption_session_v5']);  // NEW cookie name!
        config(['session.path' => '/']);
        config(['session.domain' => null]);
        config(['session.secure' => false]);            // false for XAMPP
        config(['session.http_only' => true]);
        config(['session.same_site' => 'lax']);
        config(['session.expire_on_close' => false]);
        config(['session.lottery' => [2, 100]]);       // 2% lottery (safe for DB)

        // Disable PHP's native session GC (belt-and-suspenders —
        // database driver doesn't use PHP file sessions, but we
        // keep these settings in case any PHP code uses $_SESSION)
        @ini_set('session.gc_maxlifetime', 28800);
        @ini_set('session.gc_probability', 0);
        @ini_set('session.gc_divisor', 1);
        @ini_set('session.cookie_lifetime', 28800);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ============================================================
        // FORCE URL ROOT — Fixes URL generation in subdirectories
        // ============================================================
        // When running from a subdirectory (e.g. XAMPP: localhost/Redemption)
        // or using root index.php instead of public/index.php, Laravel's
        // URL generator may produce incorrect URLs missing the subdirectory
        // prefix (e.g. localhost/login instead of localhost/Redemption/login).
        //
        // We auto-detect the root URL from server variables, which is
        // more reliable than relying on APP_URL being set correctly.
        // ============================================================
        $this->forceCorrectRootUrl();

        // Also set ASSET_URL so asset() helper generates correct paths
        $appUrl = config('app.url');
        if ($appUrl) {
            $basePath = parse_url($appUrl, PHP_URL_PATH);
            if ($basePath && $basePath !== '/') {
                config(['app.asset_url' => $appUrl]);
            }
        }

        // Delete stale cached config EVERY request (ensures fresh config)
        $cachedConfig = base_path('bootstrap/cache/config.php');
        if (file_exists($cachedConfig)) {
            @unlink($cachedConfig);
        }

        // Ensure session directory exists (still needed for any file-based fallback)
        try {
            $sessionDir = storage_path('framework/sessions');
            if (!is_dir($sessionDir)) {
                mkdir($sessionDir, 0755, true);
            }
        } catch (\Throwable $e) {}

        // NOTE: The 'safe_file' driver registration is REMOVED.
        // We no longer need NoGarbageSessionHandler because we use
        // the 'database' driver. PHP's GC cannot touch DB rows.

        // Share active announcements with admin layout
        View::composer('layouts.admin', function ($view) {
            try {
                $activeAnnouncements = CalendarEvent::where('is_announcement', true)
                    ->where(function ($q) {
                        $q->where('start_date', '>=', now()->subDays(30))
                          ->orWhere('start_date', '>=', now());
                    })
                    ->orderBy('start_date', 'desc')
                    ->limit(10)
                    ->get();
            } catch (\Throwable $e) {
                $activeAnnouncements = collect([]);
            }

            $view->with('activeAnnouncements', $activeAnnouncements);
        });
    }

    /**
     * Auto-detect and force the correct root URL.
     *
     * This solves the subdirectory problem on XAMPP/shared hosting where
     * Laravel generates URLs like http://localhost/login instead of
     * http://localhost/Redemption/login.
     *
     * Detection strategy (in order of priority):
     * 1. Auto-detect from SCRIPT_NAME (most reliable for web requests)
     * 2. Fall back to APP_URL from .env
     * 3. If SCRIPT_NAME is /index.php (root), just use APP_URL
     */
    private function forceCorrectRootUrl(): void
    {
        // For CLI (artisan), just use APP_URL
        if (app()->runningInConsole()) {
            if (config('app.url')) {
                URL::forceRootUrl(config('app.url'));
            }
            return;
        }

        // Auto-detect from server variables
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                 || ($_SERVER['SERVER_PORT'] ?? '') === '443';
        $scheme = $https ? 'https' : 'http';

        // SCRIPT_NAME examples:
        //   /Redemption/index.php          → base = /Redemption
        //   /Redemption/public/index.php   → base = /Redemption
        //   /index.php                     → base = "" (root)
        //   /public/index.php              → base = "" (root)
        $basePath = rtrim(dirname($scriptName), '/');

        // If SCRIPT_NAME is inside public/, go up one level
        // e.g. /Redemption/public/index.php → /Redemption
        if (str_ends_with($basePath, '/public')) {
            $basePath = substr($basePath, 0, -7); // Remove '/public'
        }

        // Don't use '.' (current dir) as base path
        if ($basePath === '.' || $basePath === '') {
            // Running at root — use APP_URL as-is
            if (config('app.url')) {
                URL::forceRootUrl(config('app.url'));
            }
            return;
        }

        // Build the detected root URL
        $detectedUrl = $scheme . '://' . $httpHost . $basePath;

        // Force Laravel to use this root URL for ALL generated URLs
        URL::forceRootUrl($detectedUrl);

        // Force scheme
        if ($https) {
            URL::forceScheme('https');
        }
    }
}
