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
        // Use a host-specific cookie name to avoid conflicts between
        // XAMPP (localhost) and cPanel (byethost4.com) when the same
        // browser accesses both. The hash is based on HTTP_HOST.
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $hostSuffix = substr(md5($host), 0, 6);
        config(['session.cookie' => 'redemption_sess_' . $hostSuffix]);
        config(['session.path' => '/']);
        config(['session.domain' => null]);
        // NOTE: session.secure is set dynamically in boot() based on the
        // actual request scheme. Default false here for XAMPP (HTTP).
        config(['session.secure' => false]);
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
        // URL FIX FOR SUBDIRECTORY HOSTING
        // ============================================================
        // The root index.php auto-detects the subdirectory from
        // __DIR__ relative to DOCUMENT_ROOT, and forces SCRIPT_NAME
        // so Laravel's Request object detects the correct base path.
        //
        // As a SAFETY NET (for artisan, queue workers, etc.), we also
        // call forceRootUrl() with the detected APP_URL. The root
        // index.php sets APP_URL in $_SERVER/$_ENV/putenv before
        // Laravel boots, so config('app.url') should always be correct.
        // ============================================================
        $appUrl = config('app.url');

        if ($appUrl) {
            URL::forceRootUrl($appUrl);

            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }

            // Set ASSET_URL so asset() helper generates correct paths
            $basePath = parse_url($appUrl, PHP_URL_PATH);
            if ($basePath && $basePath !== '/') {
                config(['app.asset_url' => $appUrl]);
            }
        }

        // ============================================================
        // AUTO-DETECT HTTPS for session.secure cookie flag
        // ============================================================
        // On cPanel/ByetHost the site is served over HTTPS, but
        // $_SERVER['HTTPS'] may not be set (reverse proxy).
        // We check multiple indicators: HTTPS server var, X-Forwarded-Proto
        // header, and the APP_URL scheme.
        // If HTTPS is detected, set session.secure = true so browsers
        // send the cookie only over HTTPS (required by some browsers when
        // SameSite=Lax on an HTTPS page).
        // ============================================================
        $isHttps = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $isHttps = true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $isHttps = true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
            $isHttps = true;
        } elseif (($appUrl && str_starts_with($appUrl, 'https://'))) {
            $isHttps = true;
        } elseif (($_SERVER['SERVER_PORT'] ?? '') === '443') {
            $isHttps = true;
        }
        if ($isHttps) {
            config(['session.secure' => true]);
            URL::forceScheme('https');
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

        // ============================================================
        // AUTO-CREATE sessions table if it doesn't exist
        // ============================================================
        // On cPanel/ByetHost, the user may not have run php artisan migrate.
        // Without the sessions table, the database session driver silently
        // fails, causing "session expired" loops. We auto-create it here.
        // ============================================================
        try {
            if (!\Schema::hasTable('sessions')) {
                \Schema::create('sessions', function ($table) {
                    $table->string('id')->primary();
                    $table->foreignId('user_id')->nullable()->index();
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->longText('payload');
                    $table->integer('last_activity')->index();
                });
            }
        } catch (\Throwable $e) {
            // If we can't create the table (no DB connection, etc.),
            // fall back to file driver for this request
            config(['session.driver' => 'file']);
        }

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
}
