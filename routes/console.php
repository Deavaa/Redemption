<?php

use App\Console\Commands\ScheduledDatabaseBackup;
use App\Models\Setting;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled database backup — reads frequency and time from settings
Schedule::command('backup:database')->when(function () {
    $enabled = Setting::get('backup_enabled', '1');
    if ($enabled !== '1') {
        return false;
    }

    $frequency = Setting::get('backup_frequency', 'daily');
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
})->dailyAt(function () {
    return Setting::get('backup_time', '02:00');
})->timezone('Africa/Addis_Ababa')
    ->evenInMaintenanceMode()
    ->onOneServer()
    ->emailOutputOnFailure(function () {
        return Setting::get('backup_email', config('mail.from.address', 'admin@schoolofredemption.com'));
    });
