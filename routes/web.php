<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AcademicYear\AcademicYearController;
use App\Http\Controllers\Term\TermController;
use App\Http\Controllers\Exam\ExamController;
use App\Http\Controllers\Subject\SubjectController;
use App\Http\Controllers\Admin\SubjectAssignmentController;
use App\Http\Controllers\Admin\MarkEntryController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Teacher\TeacherController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\TeamMember\TeamMemberController;
use App\Http\Controllers\Slider\SliderController;
use App\Http\Controllers\GalleryImage\GalleryImageController;
use App\Http\Controllers\GalleryVideo\GalleryVideoController;
use App\Http\Controllers\Branch\BranchController;
use App\Http\Controllers\Classroom\ClassroomController;
use App\Http\Controllers\Section\SectionController;
use App\Http\Controllers\ClassAsset\ClassAssetController;
use App\Http\Controllers\IdCard\IdCardController;
use App\Http\Controllers\Certificate\CertificateController;
use App\Http\Controllers\Audit\AuditController;
use App\Http\Controllers\Budget\BudgetController;
use App\Http\Controllers\IncomeExpense\IncomeExpenseController;
use App\Http\Controllers\FinanceStatement\FinanceStatementController;
use App\Http\Controllers\Fee\FeeController;
use App\Http\Controllers\FeePayment\FeePaymentController;
use App\Http\Controllers\ParentModel\ParentModelController;
use App\Http\Controllers\Payroll\PayrollController;
use App\Http\Controllers\Leave\LeaveController;
use App\Http\Controllers\EmployeeAsset\EmployeeAssetController;
use App\Http\Controllers\PerformanceReport\PerformanceReportController;
use App\Http\Controllers\ProgressReport\ProgressReportController;
use App\Http\Controllers\TeacherAssignment\TeacherAssignmentController;
use App\Http\Controllers\ContactMessage\ContactMessageController;
use App\Http\Controllers\MarkSheet\MarkSheetController;
use App\Http\Controllers\MarkSheet\MarkRosterController;
use App\Http\Controllers\MarkSheet\MarkSheetFullController;
use App\Http\Controllers\PerformanceReport\PerformanceAnalysisController;
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\CalendarEvent\CalendarEventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Role\RoleController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Telegram webhook (public)
Route::post('telegram/webhook', [App\Http\Controllers\Telegram\TelegramController::class, 'webhook']);

Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('academic-years', AcademicYearController::class);
    Route::resource('terms', TermController::class);
    Route::resource('exams', ExamController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('subject-assignments', SubjectAssignmentController::class);
    Route::delete('subject-assignments/bulk-delete', [SubjectAssignmentController::class, 'bulkDelete'])->name('subject-assignments.bulk-delete');
    Route::get('subject-assignments/api/classes', [SubjectAssignmentController::class, 'apiClasses'])->name('subject-assignments.api.classes');
    Route::get('subject-assignments/api/sections', [SubjectAssignmentController::class, 'apiSections'])->name('subject-assignments.api.sections');

    Route::resource('mark-entries', MarkEntryController::class);
    Route::get('mark-entries/api/terms', [MarkEntryController::class, 'apiTerms'])->name('mark-entries.api.terms');
    Route::get('mark-entries/api/sections', [MarkEntryController::class, 'apiSections'])->name('mark-entries.api.sections');
    Route::get('mark-entries/api/subjects', [MarkEntryController::class, 'apiSubjects'])->name('mark-entries.api.subjects');
    Route::get('mark-entries/api/students', [MarkEntryController::class, 'apiStudents'])->name('mark-entries.api.students');
    Route::get('mark-entries/api/load-students', [MarkEntryController::class, 'apiLoadStudents'])->name('mark-entries.api.load-students');
    Route::post('mark-entries/api/save', [MarkEntryController::class, 'apiSave'])->name('mark-entries.api.save');

    // Mark Sheet
    Route::get('mark-sheet', [MarkSheetController::class, 'index'])->name('mark-sheet.index');
    Route::post('mark-sheet/generate', [MarkSheetController::class, 'generate'])->name('mark-sheet.generate');
    Route::get('mark-sheet/api/sections', [MarkSheetController::class, 'getSections'])->name('mark-sheet.sections');
    Route::get('mark-sheet/api/students', [MarkSheetController::class, 'getStudents'])->name('mark-sheet.students');

    // Mark Roster
    Route::get('mark-roster', [MarkRosterController::class, 'index'])->name('mark-roster.index');
    Route::post('mark-roster/generate', [MarkRosterController::class, 'generate'])->name('mark-roster.generate');
    Route::get('mark-roster/api/sections', [MarkRosterController::class, 'getSections'])->name('mark-roster.sections');

    // Full Mark Sheet (Term1 + Term2 + Annual)
    Route::get('mark-sheet-full', [MarkSheetFullController::class, 'index'])->name('mark-sheet-full.index');
    Route::post('mark-sheet-full/generate', [MarkSheetFullController::class, 'generate'])->name('mark-sheet-full.generate');
    Route::get('mark-sheet-full/api/sections', [MarkSheetFullController::class, 'getSections'])->name('mark-sheet-full.sections');

    // Performance Analysis
    Route::get('performance-analysis', [PerformanceAnalysisController::class, 'index'])->name('performance-analysis.index');
    Route::post('performance-analysis/generate', [PerformanceAnalysisController::class, 'generate'])->name('performance-analysis.generate');
    Route::get('performance-analysis/api/sections', [PerformanceAnalysisController::class, 'getSections'])->name('performance-analysis.sections');

    // ID Card Generation
    Route::get('id-card-generate', [App\Http\Controllers\IdCard\IdCardGenerateController::class, 'index'])->name('id-card-generate.index');
    Route::post('id-card-generate', [App\Http\Controllers\IdCard\IdCardGenerateController::class, 'generate'])->name('id-card-generate.generate');
    Route::get('id-card-generate/api/sections', [App\Http\Controllers\IdCard\IdCardGenerateController::class, 'getSections'])->name('id-card-generate.sections');
    Route::get('id-card-generate/api/students', [App\Http\Controllers\IdCard\IdCardGenerateController::class, 'getStudents'])->name('id-card-generate.students');

    // Certificate Generation
    Route::get('certificate-generate', [App\Http\Controllers\Certificate\CertificateGenerateController::class, 'index'])->name('certificate-generate.index');
    Route::post('certificate-generate', [App\Http\Controllers\Certificate\CertificateGenerateController::class, 'generate'])->name('certificate-generate.generate');
    Route::get('certificate-generate/api/students', [App\Http\Controllers\Certificate\CertificateGenerateController::class, 'getStudents'])->name('certificate-generate.students');

    Route::resource('students', StudentController::class);
    Route::get('students/api/admission-preview', [StudentController::class, 'apiAdmissionPreview'])->name('students.api.admission-preview');
    Route::get('students/api/roll-preview', [StudentController::class, 'apiRollPreview'])->name('students.api.roll-preview');
    Route::get('students/api/sections/{classId}', [StudentController::class, 'getSections'])->name('students.api.sections');
    Route::resource('teachers', TeacherController::class);
    Route::resource('staff', StaffController::class);
    Route::resource('team-members', TeamMemberController::class);
    Route::resource('sliders', SliderController::class);

    Route::resource('gallery-images', GalleryImageController::class);
    Route::resource('gallery-videos', GalleryVideoController::class);

    Route::resource('branches', BranchController::class);
    Route::resource('classrooms', ClassroomController::class);
    Route::resource('sections', SectionController::class);
    Route::resource('class-assets', ClassAssetController::class);
    Route::get('class-assets/api/sections', [ClassAssetController::class, 'apiSections'])->name('class-assets.api-sections');
    Route::resource('id-cards', IdCardController::class);
    Route::resource('certificates', CertificateController::class);
    Route::resource('audits', AuditController::class);
    Route::resource('budgets', BudgetController::class);
    Route::resource('income-expenses', IncomeExpenseController::class);
    Route::resource('finance-statements', FinanceStatementController::class);
    Route::resource('fees', FeeController::class);
    Route::resource('fee-payments', FeePaymentController::class);
    Route::resource('parents', ParentModelController::class);
    Route::resource('payrolls', PayrollController::class);
    Route::resource('leaves', LeaveController::class);
    Route::resource('employee-assets', EmployeeAssetController::class);
    Route::resource('performance-reports', PerformanceReportController::class);
    Route::resource('progress-reports', ProgressReportController::class);
    Route::resource('teacher-assignments', TeacherAssignmentController::class);
    Route::resource('contact-messages', ContactMessageController::class);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::get('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('api/notifications/unread', [NotificationController::class, 'apiUnreadCount'])->name('notifications.api.unread');
    Route::get('api/notifications/latest', [NotificationController::class, 'apiLatest'])->name('notifications.api.latest');

    // Academic Calendar
    Route::get('calendar', [CalendarEventController::class, 'index'])->name('calendar.index');
    Route::post('calendar', [CalendarEventController::class, 'store'])->name('calendar.store');
    Route::put('calendar/{calendar_event}', [CalendarEventController::class, 'update'])->name('calendar.update');
    Route::delete('calendar/{calendar_event}', [CalendarEventController::class, 'destroy'])->name('calendar.destroy');
    Route::get('api/calendar/events', [CalendarEventController::class, 'apiEvents'])->name('calendar.api.events');
    Route::get('api/calendar/event/{calendar_event}', [CalendarEventController::class, 'apiEvent'])->name('calendar.api.event');

    // Chat
    Route::get('chat', [App\Http\Controllers\Chat\ChatController::class, 'index'])->name('chat.index');
    Route::post('chat', [App\Http\Controllers\Chat\ChatController::class, 'storeConversation'])->name('chat.store');
    Route::get('chat/{id}', [App\Http\Controllers\Chat\ChatController::class, 'show'])->name('chat.show');
    Route::post('chat/{id}/send', [App\Http\Controllers\Chat\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::delete('chat/{id}', [App\Http\Controllers\Chat\ChatController::class, 'destroyConversation'])->name('chat.destroy');
    Route::get('chat/{id}/messages', [App\Http\Controllers\Chat\ChatController::class, 'getMessages'])->name('chat.messages');

    // Telegram
    Route::get('telegram', [App\Http\Controllers\Telegram\TelegramController::class, 'index'])->name('telegram.index');
    Route::put('telegram/settings', [App\Http\Controllers\Telegram\TelegramController::class, 'updateSettings'])->name('telegram.update-settings');
    Route::post('telegram/branch-settings', [App\Http\Controllers\Telegram\TelegramController::class, 'updateBranchSettings'])->name('telegram.update-branch-settings');
    Route::get('telegram/send', [App\Http\Controllers\Telegram\TelegramController::class, 'send'])->name('telegram.send');
    Route::post('telegram/send', [App\Http\Controllers\Telegram\TelegramController::class, 'sendMessage'])->name('telegram.send-message');
    Route::get('telegram/test', [App\Http\Controllers\Telegram\TelegramController::class, 'testConnection'])->name('telegram.test');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/update-all', [SettingController::class, 'update'])->name('settings.updateAll');

    // Roles & Permissions
    Route::resource('roles', RoleController::class);
    Route::get('roles/{role}/users', [RoleController::class, 'users'])->name('roles.users');
    Route::post('roles/{role}/assign-users', [RoleController::class, 'assignUsers'])->name('roles.assign-users');
    Route::post('roles/{role}/toggle-permission', [RoleController::class, 'togglePermission'])->name('roles.toggle-permission');
});