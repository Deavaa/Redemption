<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use App\Models\CalendarEvent;
use App\Session\NoGarbageSessionHandler;

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
        // SESSION: Use 'safe_file' driver with NoGarbageSessionHandler.
        //
        // This uses the standard file driver but wraps it so that
        // PHP's garbage collection gc() is a NO-OP. Sessions are
        // stored as files on disk but can NEVER be deleted by PHP GC.
        //
        // The cookie driver was tried but broke login because old
        // session cookies from the previous driver couldn't be
        // decrypted by the cookie driver, causing 419 errors.
        //
        // Key settings:
        // - session.driver = safe_file (NoGarbageSessionHandler wrapper)
        // - session.cookie = redemption_session_v4 (NEW name to avoid
        //   conflicts with old session cookies from previous drivers)
        // - session.lottery = [0, 1000] (Laravel's own GC disabled)
        // - session.lifetime = 480 (8 hours)
        // ============================================================
        config(['session.driver' => 'safe_file']);
        config(['session.lifetime' => 480]);           // 8 hours
        config(['session.encrypt' => false]);
        config(['session.cookie' => 'redemption_session_v4']);  // NEW cookie name!
        config(['session.path' => '/']);
        config(['session.domain' => null]);
        config(['session.secure' => false]);            // false for XAMPP
        config(['session.http_only' => true]);
        config(['session.same_site' => 'lax']);
        config(['session.expire_on_close' => false]);
        config(['session.lottery' => [0, 1000]]);      // 0% lottery

        // Disable PHP's native session GC
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
        // Delete stale cached config EVERY request (ensures fresh config)
        $cachedConfig = base_path('bootstrap/cache/config.php');
        if (file_exists($cachedConfig)) {
            @unlink($cachedConfig);
        }

        // Ensure session directory exists
        try {
            $sessionDir = storage_path('framework/sessions');
            if (!is_dir($sessionDir)) {
                mkdir($sessionDir, 0755, true);
            }
        } catch (\Throwable $e) {}

        // ============================================================
        // Register 'safe_file' session driver.
        // Wraps Laravel's file handler with NoGarbageSessionHandler
        // that makes gc() a NO-OP — sessions can NEVER be deleted
        // by PHP's garbage collection.
        // ============================================================
        Session::extend('safe_file', function ($app) {
            $path = $app['config']->get('session.files', storage_path('framework/sessions'));
            $lifetime = $app['config']->get('session.lifetime', 480);

            // Create Laravel's default file session handler
            $fileHandler = new \Illuminate\Session\FileSessionHandler(
                $app['files'], $path, $lifetime
            );

            // Wrap it with NoGarbageSessionHandler that disables gc()
            return new NoGarbageSessionHandler($fileHandler);
        });

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
