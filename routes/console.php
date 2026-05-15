<?php

use App\Console\Commands\ScheduledDatabaseBackup;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled database backup — runs daily at 02:00
Schedule::command('backup:database')->dailyAt('02:00')->timezone('Africa/Addis_Ababa')
    ->evenInMaintenanceMode()
    ->onOneServer()
    ->emailOutputOnFailure(config('mail.from.address', 'dawitac@gmail.com'));
