<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
        // SESSION: Use COOKIE driver.
        //
        // This is the DEFINITIVE fix for session expiration on XAMPP.
        //
        // WHY NOTHING ELSE WORKED:
        // - file driver: PHP GC deletes session files regardless of ini_set
        // - database driver: requires sessions table; PHP gc() still interferes
        // - safe_database + NoGarbageSessionHandler: didn't prevent expiry
        //
        // WHY COOKIE DRIVER WORKS:
        // - Session data is stored ENCRYPTED in the browser cookie
        // - There is NO server-side state for PHP GC to delete
        // - PHP garbage collection is completely irrelevant
        // - Session lifetime is controlled by the cookie expiry time
        // - Works perfectly on XAMPP, shared hosting, any PHP config
        // ============================================================
        config(['session.driver' => 'cookie']);
        config(['session.lifetime' => 480]);           // 8 hours
        config(['session.encrypt' => true]);            // Encrypt cookie contents
        config(['session.cookie' => 'redemption_session']);
        config(['session.path' => '/']);
        config(['session.domain' => null]);
        config(['session.secure' => false]);            // false for XAMPP HTTP
        config(['session.http_only' => true]);
        config(['session.same_site' => 'lax']);
        config(['session.expire_on_close' => false]);
        config(['session.lottery' => [0, 1000]]);      // 0% lottery — disabled

        // Disable PHP's native session GC as extra safety net
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
        // Delete stale cached config
        $cachedConfig = base_path('bootstrap/cache/config.php');
        if (file_exists($cachedConfig)) {
            @unlink($cachedConfig);
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
