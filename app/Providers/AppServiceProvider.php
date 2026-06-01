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
        // SESSION: Force database driver UNCONDITIONALLY.
        //
        // The file driver is broken on XAMPP because PHP's native
        // session.gc_maxlifetime (often 300s/5min on XAMPP) deletes
        // session files regardless of Laravel's session.lifetime.
        //
        // The database driver stores sessions in MySQL and is
        // completely immune to PHP's garbage collection.
        //
        // The sessions table is auto-created in boot() if missing.
        // ============================================================
        config(['session.driver' => 'database']);
        config(['session.table' => 'sessions']);
        config(['session.connection' => null]);
        config(['session.store' => null]);

        // Session cookie settings
        config(['session.cookie' => 'redemption_session_v3']); // New name = forces fresh cookies
        config(['session.path' => '/']);
        config(['session.domain' => null]);
        config(['session.secure' => false]);
        config(['session.same_site' => 'lax']);
        config(['session.http_only' => true]);
        config(['session.encrypt' => false]);
        config(['session.expire_on_close' => false]);
        config(['session.partitioned' => false]);
        config(['session.lifetime' => 480]); // 8 hours
        config(['session.lottery' => [1, 1000]]); // Very low GC chance
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
            // Don't crash if table creation fails
        }

        // Ensure session directory exists (for any fallback)
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
