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
        // Force correct database and session settings at the earliest possible point.
        // This prevents SQLite/session errors caused by wrong .env values or cached config.
        // These overrides take effect BEFORE any request processing.
        config(['database.default' => 'mysql']);
        config(['session.driver' => 'file']);
        config(['session.secure' => false]);
        config(['session.domain' => null]);
        config(['session.same_site' => 'lax']);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure session storage directory exists and is writable
        try {
            $sessionDir = storage_path('framework/sessions');
            if (!is_dir($sessionDir)) {
                mkdir($sessionDir, 0755, true);
            }
        } catch (\Throwable $e) {
            // Can't create directory - sessions won't work but don't crash the app
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
