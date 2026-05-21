<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\CalendarEvent;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ============================================================
        // SAFETY NET: Auto-generate APP_KEY if missing.
        // This prevents MissingAppKeyException crashes. The key is
        // generated once and written to .env so it persists.
        // ============================================================
        if (empty(config('app.key'))) {
            $key = 'base64:' . base64_encode(random_bytes(32));
            config(['app.key' => $key]);

            // Persist to .env so it survives across requests
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
        // FORCE correct database and session configuration.
        // These overrides take effect BEFORE any request processing,
        // BEFORE middleware runs, and OVERRIDE .env, config files,
        // AND cached config. This prevents the recurring SQLite /
        // session errors caused by wrong .env values on the user's
        // local machine (which is gitignored and can't be fixed via git).
        // ============================================================

        // Database: always MySQL/MariaDB (never sqlite)
        config(['database.default' => 'mysql']);

        // Session driver: always file-based (never database)
        config(['session.driver' => 'file']);

        // Session cookie: fixed name, independent of APP_NAME/SESSION_COOKIE
        config(['session.cookie' => 'redemption_session']);

        // Cookie path: '/' is the most permissive — ensures the session
        // cookie is always sent for ALL paths, including subdirectory
        // installs like XAMPP (https://localhost/Redemption/public/)
        config(['session.path' => '/']);

        // Cookie domain: null = no domain restriction (correct default)
        config(['session.domain' => null]);

        // Secure cookies: must be false for XAMPP self-signed HTTPS
        config(['session.secure' => false]);

        // SameSite: 'lax' allows normal navigation while preventing CSRF
        config(['session.same_site' => 'lax']);

        // Session encryption: MUST be false — encrypting with a wrong
        // or missing APP_KEY would corrupt all session data and cause
        // 419 Page Expired (CSRF token mismatch) errors.
        config(['session.encrypt' => false]);

        // Do NOT expire on close — would cause constant logouts
        config(['session.expire_on_close' => false]);

        // HTTP-only cookies: true for XSS protection
        config(['session.http_only' => true]);

        // Partitioned cookies: false — not needed
        config(['session.partitioned' => false]);

        // Session lifetime: 120 minutes (2 hours)
        config(['session.lifetime' => 120]);

        // Session connection and store: null (using file driver)
        config(['session.connection' => null]);
        config(['session.store' => null]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Delete stale cached config that may have wrong session/DB settings
        $cachedConfig = base_path('bootstrap/cache/config.php');
        if (file_exists($cachedConfig)) {
            @unlink($cachedConfig);
        }

        // Ensure session storage directory exists and is writable
        try {
            $sessionDir = storage_path('framework/sessions');
            if (!is_dir($sessionDir)) {
                mkdir($sessionDir, 0755, true);
            }
        } catch (\Throwable $e) {
            // Can't create directory — sessions won't work but don't crash
        }

        // Share active announcements with the admin layout so the
        // announcement banner and splash modal always have data
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
