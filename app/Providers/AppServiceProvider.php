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
        try {
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
        } catch (\Throwable $e) {
            // Silently fail — app key will be empty but container won't crash
        }

        // ============================================================
        // AUTO-DETECT APP_URL from the actual HTTP request
        // ============================================================
        // CRITICAL: The .env file may have APP_URL=http://localhost/Redemption
        // (from XAMPP), but on cPanel/ByetHost the actual URL is different.
        // Dotenv loads .env and OVERWRITES any APP_URL we set in index.php.
        // We must override config('app.url') HERE (after Dotenv loads, but
        // before URL generation) to prevent redirect-to-localhost bugs.
        // ============================================================
        try {
            $detectedAppUrl = $this->detectAppUrl();
            if ($detectedAppUrl) {
                config(['app.url' => $detectedAppUrl]);
            }
        } catch (\Throwable $e) {
            // Silently fail — will use .env APP_URL as fallback
        }

        // ============================================================
        // FORCE correct database: always MySQL (never sqlite)
        // ============================================================
        try {
            config(['database.default' => 'mysql']);
        } catch (\Throwable $e) {
            // Silently fail
        }

        // ============================================================
        // SESSION: Use 'file' driver with 1-YEAR lifetime.
        //
        // File driver is fast and doesn't depend on the sessions DB table
        // (which can have AUTO_INCREMENT issues on XAMPP). Sessions are
        // stored in storage/framework/sessions/.
        //
        // Lifetime: 525600 minutes = 1 YEAR (indefinite).
        // Users stay logged in for a full year unless they manually log out.
        //
        // Key settings:
        // - session.driver = file (respects .env override)
        // - session.lifetime = 525600 (1 year, respects .env override)
        // - session.lottery = [2, 100] (2% chance of Laravel GC)
        // ============================================================
        try {
            // Respect .env if explicitly set, otherwise use file + 1 year
            $driver = env('SESSION_DRIVER', 'file');
            $lifetime = (int) env('SESSION_LIFETIME', 525600);
            config(['session.driver' => $driver]);
            config(['session.lifetime' => $lifetime]);
            config(['session.encrypt' => false]);
            // Use a host-specific cookie name to avoid conflicts between
            // XAMPP (localhost) and cPanel (byethost4.com) when the same
            // browser accesses both. The hash is based on HTTP_HOST.
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $hostSuffix = substr(md5($host), 0, 6);
            config(['session.cookie' => 'redemption_sess_' . $hostSuffix]);
            config(['session.path' => '/']);
            config(['session.domain' => null]);
            config(['session.secure' => false]);
            config(['session.http_only' => true]);
            config(['session.same_site' => 'lax']);
            config(['session.expire_on_close' => false]);
            config(['session.lottery' => [2, 100]]);     // 2% — clean expired sessions occasionally

            // Disable PHP's native session GC (file driver uses Laravel's GC)
            @ini_set('session.gc_maxlifetime', 525600);   // 1 year
            @ini_set('session.gc_probability', 0);
            @ini_set('session.gc_divisor', 1);
            @ini_set('session.cookie_lifetime', 525600);  // 1 year
        } catch (\Throwable $e) {
            // Silently fail — session config will use defaults
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ============================================================
        // AUTO-CREATE STORAGE SYMLINK (storage:link equivalent)
        // ============================================================
        // On XAMPP / shared hosting, users often forget to run
        // 'php artisan storage:link'. This creates the public/storage
        // -> storage/app/public symlink at boot time. If symlink fails
        // (Windows permissions), it silently falls back — controllers
        // that upload files are also responsible for copying to
        // public/news-images/ as a backup location.
        // ============================================================
        try {
            $publicStorage = public_path('storage');
            $target = storage_path('app/public');
            if (!file_exists($publicStorage) && is_dir($target)) {
                @symlink($target, $publicStorage);
            }
        } catch (\Throwable $e) {
            // Silently fail — upload handlers have fallback copy logic
        }

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
        try {
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
        } catch (\Throwable $e) {
            // Silently fail — URL generation may use defaults
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
        try {
            $isHttps = false;
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                $isHttps = true;
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
                $isHttps = true;
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
                $isHttps = true;
            } elseif (($appUrl ?? null) && str_starts_with($appUrl, 'https://')) {
                $isHttps = true;
            } elseif (($_SERVER['SERVER_PORT'] ?? '') === '443') {
                $isHttps = true;
            }
            if ($isHttps) {
                config(['session.secure' => true]);
                URL::forceScheme('https');
            }
        } catch (\Throwable $e) {
            // Silently fail — session secure flag will use default
        }

        // Ensure session directory exists (still needed for any file-based fallback)
        try {
            $sessionDir = storage_path('framework/sessions');
            if (!is_dir($sessionDir)) {
                mkdir($sessionDir, 0755, true);
            }
        } catch (\Throwable $e) {}

        // ============================================================
        // SESSION DRIVER: Auto-create sessions table + DB health check
        // ============================================================
        // On cPanel/ByetHost, the user may not have run php artisan migrate.
        // Without the sessions table, the database session driver fails,
        // causing "connection refused" or "session expired" errors.
        //
        // We do TWO things here:
        // 1. AUTO-CREATE the sessions table if it doesn't exist (runs once,
        //    tracked by a flag file).
        // 2. CHECK DB CONNECTIVITY with a short file-based cache (1-minute
        //    TTL). If the DB is unreachable, fall back to file sessions for
        //    the next minute. This handles intermittent DB outages on shared
        //    hosting where MySQL connections get dropped.
        // ============================================================
        try {
            $flagFile = storage_path('framework/sessions_table_created');

            if (!file_exists($flagFile)) {
                // First run: try to create the sessions table
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
                @file_put_contents($flagFile, date('Y-m-d H:i:s'));
            }

            // DB health check with 1-minute file cache.
            // On shared hosting, MySQL can become temporarily unavailable
            // (connection limit reached, server restart, etc.). If we detect
            // this, switch to file sessions until the DB comes back.
            $healthFile = storage_path('framework/db_health_' . date('YmdHi')); // changes every minute
            if (!file_exists($healthFile)) {
                try {
                    \DB::connection()->getPdo();
                    // DB is healthy — write health file so we skip the check
                    // for the rest of this minute
                    @file_put_contents($healthFile, 'ok');
                } catch (\Throwable $dbError) {
                    // DB is down — switch to file sessions
                    \Log::warning('DB health check failed, falling back to file sessions: ' . $dbError->getMessage());
                    config(['session.driver' => 'file']);

                    // Clean up stale health files from previous minutes
                    try {
                        $staleFiles = glob(storage_path('framework/db_health_*'));
                        foreach ($staleFiles as $f) {
                            @unlink($f);
                        }
                    } catch (\Throwable $e) {}
                }
            }
        } catch (\Throwable $e) {
            // If we can't create the table (no DB connection, etc.),
            // fall back to file driver for this request
            \Log::warning('Session setup failed, falling back to file driver: ' . $e->getMessage());
            try {
                config(['session.driver' => 'file']);
            } catch (\Throwable $e2) {
                // Even fallback failed — truly silent
            }
        }

        // Share active announcements with admin layout
        try {
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
        } catch (\Throwable $e) {
            // Silently fail — announcements won't be shared but app won't crash
        }
    }

    /**
     * Detect the actual APP_URL from the current HTTP request.
     *
     * This is CRITICAL for shared hosting (cPanel/ByetHost) where the .env
     * file still has APP_URL=http://localhost/Redemption from XAMPP.
     * Dotenv loads .env and overwrites any APP_URL we set in index.php,
     * so we must detect and override it HERE (after Dotenv, before routing).
     *
     * Also works for CLI (artisan) — falls back to existing config value.
     */
    private function detectAppUrl(): ?string
    {
        try {
            // CLI (artisan) — no HTTP request, use existing config
            if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
                return null;
            }

            // No HTTP_HOST — can't detect URL
            $httpHost = $_SERVER['HTTP_HOST'] ?? null;
            if (!$httpHost) {
                return null;
            }

            // Detect HTTPS — check multiple indicators (ByetHost uses reverse proxy)
            $isHttps = false;
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                $isHttps = true;
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
                $isHttps = true;
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
                $isHttps = true;
            } elseif (($_SERVER['SERVER_PORT'] ?? '') === '443') {
                $isHttps = true;
            }
            $scheme = $isHttps ? 'https' : 'http';

            // Detect subdirectory/base path from SCRIPT_NAME or DOCUMENT_ROOT
            $basePath = '';
            $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
            $currentDir = realpath(base_path()); // Laravel base_path() = project root

            if ($documentRoot && $currentDir && str_starts_with($currentDir, $documentRoot)) {
                $basePath = substr($currentDir, strlen($documentRoot));
                $basePath = str_replace('\\', '/', $basePath);
                $basePath = rtrim($basePath, '/');

                // Case-fix: match the case from REQUEST_URI
                $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
                if ($basePath !== '' && $uriPath !== '' && stripos($uriPath, $basePath) === 0) {
                    $basePath = substr($uriPath, 0, strlen($basePath));
                }
            }

            $detectedUrl = $scheme . '://' . $httpHost . $basePath;

            // Validate: if the detected URL is localhost and the .env value is also
            // localhost, there's nothing to override (XAMPP local dev)
            $envUrl = config('app.url');
            if ($envUrl && str_contains($envUrl, 'localhost') && str_contains($detectedUrl, 'localhost')) {
                return null; // Both are localhost, no override needed
            }

            // If .env has localhost but actual host is NOT localhost, we MUST override
            if ($envUrl && str_contains($envUrl, 'localhost') && !str_contains($detectedUrl, 'localhost')) {
                return $detectedUrl;
            }

            // If detected URL differs from config, override
            if ($envUrl && $detectedUrl !== $envUrl) {
                return $detectedUrl;
            }

            return null;
        } catch (\Throwable $e) {
            // Silently fail — return null so the caller falls back to .env APP_URL
            return null;
        }
    }
}
