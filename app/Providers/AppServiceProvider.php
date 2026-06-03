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
     * 1. Use LARAVEL_BASE_PATH set by root index.php (most reliable)
     * 2. Auto-detect from SCRIPT_FILENAME + DOCUMENT_ROOT
     * 3. Fall back to APP_URL from .env
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

        $httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                 || ($_SERVER['SERVER_PORT'] ?? '') === '443';
        $scheme = $https ? 'https' : 'http';

        // ── METHOD 1: Use LARAVEL_BASE_PATH set by root index.php ──
        // This is set in the root index.php and is the most reliable
        // because __DIR__ is always correct regardless of Apache rewrites.
        $basePath = $_SERVER['LARAVEL_BASE_PATH'] ?? null;
        if ($basePath === null) {
            // ── METHOD 2: Detect from SCRIPT_FILENAME + DOCUMENT_ROOT ──
            // SCRIPT_FILENAME is the actual file path on disk, which is
            // more reliable than SCRIPT_NAME (which Apache can modify).
            // Example: C:/xampp/htdocs/Redemption/index.php
            $scriptFilename = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
            $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? $_SERVER['APPL_PHYSICAL_PATH'] ?? '');

            if ($scriptFilename && $documentRoot && str_starts_with($scriptFilename, $documentRoot)) {
                $relativePath = substr($scriptFilename, strlen($documentRoot));
                $relativePath = str_replace('\\', '/', $relativePath); // Windows backslash fix

                // Remove /index.php or /public/index.php from the end
                $relativePath = preg_replace('#/(public/)?index\.php$#', '', $relativePath);
                $basePath = $relativePath;
            }
        }

        // If we detected a base path, build the URL
        if ($basePath !== null && $basePath !== '') {
            $detectedUrl = $scheme . '://' . $httpHost . $basePath;
            URL::forceRootUrl($detectedUrl);

            if ($https) {
                URL::forceScheme('https');
            }
            return;
        }

        // ── METHOD 3: Fall back to APP_URL from .env ──
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));

            if (str_starts_with(config('app.url'), 'https://')) {
                URL::forceScheme('https');
            }
        }
    }
}
