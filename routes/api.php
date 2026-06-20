<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarEvent\CalendarEventController;
use App\Http\Controllers\ReportExportController;

Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'app' => 'School of Redemption ERP']);
});

// Public announcements API (used by welcome.blade.php ticker)
Route::get('/public/announcements', [CalendarEventController::class, 'apiAnnouncements']);

// ── Report Export API (used by the mobile app and web admin) ──────────
// Returns PDF (print-friendly HTML that auto-triggers window.print()) or
// CSV (opens natively in Excel). Authenticated via session cookie (web
// middleware) so the mobile app's webview can call these directly.
Route::middleware(['web', 'auth'])->prefix('export')->group(function () {
    Route::get('/marks', [ReportExportController::class, 'exportMarks'])->name('api.export.marks');
    Route::get('/attendance', [ReportExportController::class, 'exportAttendance'])->name('api.export.attendance');
    Route::get('/fees', [ReportExportController::class, 'exportFees'])->name('api.export.fees');
    Route::get('/students', [ReportExportController::class, 'exportStudents'])->name('api.export.students');
});