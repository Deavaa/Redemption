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
        // ──────────────────────────────────────────────────────────────
        // FORCE correct database and session settings at the EARLIEST
        // possible point. This prevents SQLite / session errors caused
        // by wrong .env values or stale cached config.
        //
        // These overrides take effect BEFORE any request processing,
        // BEFORE middleware runs, and BEFORE the session is started.
        // ──────────────────────────────────────────────────────────────

        // Database: always MySQL/MariaDB (never sqlite)
        config(['database.default' => 'mysql']);

        // Session: always file-based (never database — no sessions table)
        config(['session.driver' => 'file']);

        // Cookie path: '/' is the most permissive — ensures the session
        // cookie is always sent, even in subdirectory installs like XAMPP.
        // (e.g., https://localhost/Redemption/public/)
        config(['session.path' => '/']);

        // Cookie domain: null = no domain restriction (correct default)
        config(['session.domain' => null]);

        // Secure cookies: must be false for XAMPP self-signed HTTPS
        config(['session.secure' => false]);

        // SameSite: 'lax' allows normal navigation while preventing CSRF
        config(['session.same_site' => 'lax']);
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
