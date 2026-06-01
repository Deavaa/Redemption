<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\CalendarEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        // SESSION CONFIGURATION
        // ============================================================
        // We set the session driver to 'database' which stores sessions
        // in MySQL and is immune to PHP's native garbage collection.
        //
        // As a safety net, we also override PHP's native GC settings
        // here. These are also set in public/index.php and .user.ini
        // for maximum coverage.
        //
        // CRITICAL: Cookie name must be CONSISTENT everywhere.
        // We use 'redemption_session' — matching config/session.php.
        // ============================================================
        @ini_set('session.gc_maxlifetime', 28800);  // 8 hours
        @ini_set('session.gc_probability', 0);       // Disable PHP GC
        @ini_set('session.gc_divisor', 1);
        @ini_set('session.cookie_lifetime', 28800);

        // Session driver — use 'database' if sessions table exists,
        // otherwise fall back to 'file' (with PHP GC disabled above)
        try {
            $canUseDatabase = Schema::hasTable('sessions');
        } catch (\Throwable $e) {
            $canUseDatabase = false;
        }

        if ($canUseDatabase) {
            config(['session.driver' => 'database']);
        } else {
            // Fall back to file driver — PHP GC is disabled above so
            // file sessions won't be prematurely deleted
            config(['session.driver' => 'file']);
        }

        config(['session.table' => 'sessions']);
        config(['session.connection' => null]);
        config(['session.store' => null]);

        // Session cookie settings — MUST match config/session.php
        config(['session.cookie' => 'redemption_session']);
        config(['session.path' => '/']);
        config(['session.domain' => null]);
        config(['session.secure' => false]);
        config(['session.same_site' => 'lax']);
        config(['session.http_only' => true]);
        config(['session.encrypt' => false]);
        config(['session.expire_on_close' => false]);
        config(['session.partitioned' => false]);
        config(['session.lifetime' => 480]); // 8 hours
        config(['session.lottery' => [0, 1000]]); // DISABLE Laravel GC too (0% chance)
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Delete stale cached config
        $cachedConfig = base_path('bootstrap/cache/config.php');
        if (file_exists($cachedConfig)) {
            @unlink($cachedConfig);
        }

        // ============================================================
        // AUTO-CREATE sessions table if it doesn't exist.
        // This ensures the database session driver works immediately.
        // ============================================================
        try {
            if (!Schema::hasTable('sessions')) {
                Schema::create('sessions', function ($table) {
                    $table->string('id')->primary();
                    $table->foreignId('user_id')->nullable()->index();
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->longText('payload');
                    $table->integer('last_activity')->index();
                });
            }
        } catch (\Throwable $e) {
            // Don't crash if table creation fails — file driver will be used
        }

        // Ensure session directory exists (for file driver fallback)
        try {
            $sessionDir = storage_path('framework/sessions');
            if (!is_dir($sessionDir)) {
                mkdir($sessionDir, 0755, true);
            }
        } catch (\Throwable $e) {}

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
