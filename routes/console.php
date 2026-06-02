<?php

use App\Console\Commands\ScheduledDatabaseBackup;
use App\Models\Setting;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Helper: safely read a setting value — returns $default if the settings
// table does not exist yet (e.g. during php artisan migrate --seed).
if (!function_exists('safeSetting')) {
    function safeSetting(string $key, $default = null)
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

// Scheduled database backup — reads frequency and time from settings
Schedule::command('backup:database')
    ->dailyAt(safeSetting('backup_time', '02:00'))
    ->timezone('Africa/Addis_Ababa')
    ->when(function () {
        $enabled = safeSetting('backup_enabled', '1');
        if ($enabled !== '1') {
            return false;
        }

        $frequency = safeSetting('backup_frequency', 'daily');
        $now = now('Africa/Addis_Ababa');

        switch ($frequency) {
            case 'weekly':
                return $now->isMonday(); // Run on Mondays for weekly
            case 'monthly':
                return $now->day === 1; // Run on 1st of month
            case 'daily':
            default:
                return true;
        }
    })
    ->evenInMaintenanceMode()
    ->onOneServer()
    ->emailOutputOnFailure(safeSetting('backup_email', config('mail.from.address', 'admin@schoolofredemption.com')));

// Clean up old file-based session files (leftover from previous safe_file driver)
// These are no longer needed since we switched to the database driver.
Schedule::call(function () {
    $sessionPath = storage_path('framework/sessions');
    if (is_dir($sessionPath)) {
        $files = glob($sessionPath . '/sess_*');
        $deleted = 0;
        foreach ($files as $file) {
            // Delete session files older than 24 hours
            if (filemtime($file) < time() - 86400) {
                @unlink($file);
                $deleted++;
            }
        }
        if ($deleted > 0) {
            \Log::info("Cleaned up {$deleted} old session files from file-based driver");
        }
    }
})->daily()->at('03:00')->timezone('Africa/Addis_Ababa');
