<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
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
        // AND cached config.
        // ============================================================

        // Database: always MySQL/MariaDB (never sqlite)
        config(['database.default' => 'mysql']);

        // Session driver: use 'database' driver if sessions table exists,
        // otherwise fall back to 'file'. The database driver is immune to
        // PHP's native session garbage collection, which was deleting session
        // files in <5 minutes on XAMPP. However, if the sessions table doesn't
        // exist yet (migration not run), the database driver will crash ALL pages.
        // So we check for the table and fall back gracefully.
        $useDatabaseDriver = false;
        try {
            // Try to connect and check if sessions table exists.
            // We do this in register() so the driver is set before the session starts.
            $connection = $this->app->make('db')->connection();
            $useDatabaseDriver = $connection->getSchemaBuilder()->hasTable('sessions');
        } catch (\Throwable $e) {
            // Database not available yet — fall back to file driver
        }

        if ($useDatabaseDriver) {
            config(['session.driver' => 'database']);
            config(['session.connection' => null]);
            config(['session.store' => null]);
            config(['session.table' => 'sessions']);
        } else {
            config(['session.driver' => 'file']);
            config(['session.connection' => null]);
            config(['session.store' => null]);
        }

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

        // Session lifetime: 480 minutes (8 hours) — extended for long mark entry sessions
        config(['session.lifetime' => 480]);
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

        // Ensure session storage directory exists and is writable (for file driver)
        try {
            $sessionDir = storage_path('framework/sessions');
            if (!is_dir($sessionDir)) {
                mkdir($sessionDir, 0755, true);
            }
        } catch (\Throwable $e) {
            // Can't create directory — sessions won't work but don't crash
        }

        // Try to create the sessions table if it doesn't exist.
        // This allows the database driver to work without running artisan migrate.
        // If the table already exists, Schema::create will throw an error which we catch.
        if (config('session.driver') !== 'database') {
            try {
                Schema::create('sessions', function ($table) {
                    $table->string('id')->primary();
                    $table->foreignId('user_id')->nullable()->index();
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->longText('payload');
                    $table->integer('last_activity')->index();
                });
                // Table created successfully — switch to database driver for NEXT request
                // (Can't switch mid-request as session already started with file driver)
                config(['session.driver' => 'database']);
                config(['session.table' => 'sessions']);
            } catch (\Throwable $e) {
                // Table already exists or creation failed — keep current driver
            }
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
