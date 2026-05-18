<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarEvent\CalendarEventController;

Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'app' => 'School of Redemption ERP']);
});

// Public announcements API (used by welcome.blade.php ticker)
Route::get('/public/announcements', [CalendarEventController::class, 'apiAnnouncements']);