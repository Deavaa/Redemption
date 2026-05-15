<?php

use App\Http\Controllers\AcademicYear\AcademicYearController;
use App\Http\Controllers\Admin\MarkEntryController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\SubjectAssignmentController;
use App\Http\Controllers\Audit\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Branch\BranchController;
use App\Http\Controllers\Budget\BudgetController;
use App\Http\Controllers\Budget\BudgetComparisonController;
use App\Http\Controllers\CalendarEvent\AnnouncementController;
use App\Http\Controllers\CalendarEvent\CalendarEventController;
use App\Http\Controllers\Certificate\CertificateController;
use App\Http\Controllers\Certificate\CertificateGenerateController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\ClassAsset\ClassAssetController;
use App\Http\Controllers\Classroom\ClassroomController;
use App\Http\Controllers\ContactMessage\ContactMessageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeAsset\EmployeeAssetController;
use App\Http\Controllers\Exam\ExamController;
use App\Http\Controllers\Fee\FeeController;
use App\Http\Controllers\FeePayment\FeePaymentController;
use App\Http\Controllers\FinanceStatement\FinancialComparisonController;
use App\Http\Controllers\FinanceStatement\FinanceStatementController;
use App\Http\Controllers\GalleryImage\GalleryImageController;
use App\Http\Controllers\GalleryVideo\GalleryVideoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IdCard\IdCardController;
use App\Http\Controllers\IdCard\IdCardGenerateController;
use App\Http\Controllers\IncomeExpense\IncomeExpenseController;
use App\Http\Controllers\Leave\LeaveController;
use App\Http\Controllers\MarkSheet\MarkRosterController;
use App\Http\Controllers\MarkSheet\MarkSheetController;
use App\Http\Controllers\MarkSheet\MarkSheetFullController;
use App\Http\Controllers\MarkSheet\ReportCardController;
use App\Http\Controllers\Media\MediaController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\ParentModel\ParentModelController;
use App\Http\Controllers\Payroll\PayrollController;
use App\Http\Controllers\Stock\StockController;
use App\Http\Controllers\PerformanceReport\PerformanceComparisonController;
use App\Http\Controllers\Performance\PerformanceAnalysisController;
use App\Http\Controllers\PerformanceReport\PerformanceAnalysisController as PerformanceReportAnalysisController;
use App\Http\Controllers\PerformanceReport\PsychologicalAnalysisController;
use App\Http\Controllers\PerformanceReport\PerformanceReportController;
use App\Http\Controllers\ProgressReport\ProgressReportController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Section\SectionController;
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\Setting\WebContentController;
use App\Http\Controllers\Slider\SliderController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Subject\SubjectController;
use App\Http\Controllers\Teacher\TeacherController;
use App\Http\Controllers\TeacherAssignment\TeacherAssignmentController;
use App\Http\Controllers\TeamMember\TeamMemberController;
use App\Http\Controllers\Telegram\TelegramController;
use App\Http\Controllers\Term\TermController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Library\LibraryBookController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\Backup\DatabaseBackupController as ScheduledBackupController;
use App\Http\Controllers\UserAccess\TeacherAccessController;
use App\Http\Controllers\UserAccess\StudentAccessController;
use App\Http\Controllers\UserAccess\ParentAccessController;
use App\Http\Controllers\ReportExchange\ReportExchangeController;
use App\Http\Controllers\Training\TrainingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Public announcement ticker API (no auth)
Route::get('api/public/announcements', [CalendarEventController::class, 'apiAnnouncements'])->name('api.public.announcements');

// Language Switcher
Route::get('lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Telegram webhook (public)
Route::post('telegram/webhook', [TelegramController::class, 'webhook']);

// Media fallback route - serves storage files when symlink doesn't exist (e.g., XAMPP)
Route::get('storage/{path}', [MediaController::class, 'serve'])->where('path', '.*');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard.view');

    // ── Academic ──────────────────────────────────────────
    Route::resource('academic-years', AcademicYearController::class)->middleware('permission:academic_years.view');
    Route::resource('terms', TermController::class)->middleware('permission:terms.view');
    Route::resource('exams', ExamController::class)->middleware('permission:exams.view');
    Route::resource('subjects', SubjectController::class)->middleware('permission:subjects.view');
    Route::resource('subject-assignments', SubjectAssignmentController::class)->middleware('permission:subject_assignments.view');
    Route::delete('subject-assignments/bulk-delete', [SubjectAssignmentController::class, 'bulkDelete'])->name('subject-assignments.bulk-delete')->middleware('permission:subject_assignments.delete');
    Route::get('subject-assignments/api/classes', [SubjectAssignmentController::class, 'apiClasses'])->name('subject-assignments.api.classes');
    Route::get('subject-assignments/api/sections', [SubjectAssignmentController::class, 'apiSections'])->name('subject-assignments.api.sections');

    Route::resource('mark-entries', MarkEntryController::class)->middleware('permission:mark_entries.view');
    Route::get('mark-entries/api/terms', [MarkEntryController::class, 'apiTerms'])->name('mark-entries.api.terms');
    Route::get('mark-entries/api/sections', [MarkEntryController::class, 'apiSections'])->name('mark-entries.api.sections');
    Route::get('mark-entries/api/subjects', [MarkEntryController::class, 'apiSubjects'])->name('mark-entries.api.subjects');
    Route::get('mark-entries/api/students', [MarkEntryController::class, 'apiStudents'])->name('mark-entries.api.students');
    Route::get('mark-entries/api/load-students', [MarkEntryController::class, 'apiLoadStudents'])->name('mark-entries.api.load-students');
    Route::post('mark-entries/api/save', [MarkEntryController::class, 'apiSave'])->name('mark-entries.api.save');

    // Mark Sheet
    Route::get('mark-sheet', [MarkSheetController::class, 'index'])->name('mark-sheet.index')->middleware('permission:mark_sheets.view');
    Route::post('mark-sheet/generate', [MarkSheetController::class, 'generate'])->name('mark-sheet.generate')->middleware('permission:mark_sheets.generate');
    Route::get('mark-sheet/api/sections', [MarkSheetController::class, 'getSections'])->name('mark-sheet.sections');
    Route::get('mark-sheet/api/students', [MarkSheetController::class, 'getStudents'])->name('mark-sheet.students');

    // Full Mark Sheet
    Route::get('mark-sheet-full', [MarkSheetFullController::class, 'index'])->name('mark-sheet-full.index')->middleware('permission:mark_sheets.view');
    Route::post('mark-sheet-full/generate', [MarkSheetFullController::class, 'generate'])->name('mark-sheet-full.generate')->middleware('permission:mark_sheets.generate');
    Route::get('mark-sheet-full/api/sections', [MarkSheetFullController::class, 'getSections'])->name('mark-sheet-full.sections');

    // Mark Roster
    Route::get('mark-roster', [MarkRosterController::class, 'index'])->name('mark-roster.index')->middleware('permission:mark_sheets.view');
    Route::post('mark-roster/generate', [MarkRosterController::class, 'generate'])->name('mark-roster.generate')->middleware('permission:mark_sheets.generate');
    Route::get('mark-roster/api/sections', [MarkRosterController::class, 'getSections'])->name('mark-roster.sections');

    // Report Card
    Route::get('report-card', [ReportCardController::class, 'index'])->name('report-card.index')->middleware('permission:mark_sheets.view');
    Route::post('report-card/generate', [ReportCardController::class, 'generate'])->name('report-card.generate')->middleware('permission:mark_sheets.generate');
    Route::get('report-card/api/sections', [ReportCardController::class, 'getSections'])->name('report-card.sections');
    Route::get('report-card/api/students', [ReportCardController::class, 'getStudents'])->name('report-card.students');

    // Performance Analysis (Legacy - Class-based)
    Route::get('performance-analysis', [PerformanceReportAnalysisController::class, 'index'])->name('performance-analysis.index')->middleware('permission:mark_sheets.view');
    Route::post('performance-analysis/generate', [PerformanceReportAnalysisController::class, 'generate'])->name('performance-analysis.generate')->middleware('permission:mark_sheets.generate');
    Route::get('performance-analysis/api/sections', [PerformanceReportAnalysisController::class, 'getSections'])->name('performance-analysis.sections');

    // ID Card Generation
    Route::get('id-card-generate', [IdCardGenerateController::class, 'index'])->name('id-card-generate.index')->middleware('permission:id_cards.generate');
    Route::post('id-card-generate', [IdCardGenerateController::class, 'generate'])->name('id-card-generate.generate')->middleware('permission:id_cards.generate');
    Route::get('id-card-generate/api/sections', [IdCardGenerateController::class, 'getSections'])->name('id-card-generate.sections');
    Route::get('id-card-generate/api/students', [IdCardGenerateController::class, 'getStudents'])->name('id-card-generate.students');

    // Certificate Generation
    Route::get('certificate-generate', [CertificateGenerateController::class, 'index'])->name('certificate-generate.index')->middleware('permission:certificates.generate');
    Route::post('certificate-generate', [CertificateGenerateController::class, 'generate'])->name('certificate-generate.generate')->middleware('permission:certificates.generate');
    Route::get('certificate-generate/api/students', [CertificateGenerateController::class, 'getStudents'])->name('certificate-generate.students');

    // ── People ────────────────────────────────────────────
    Route::resource('students', StudentController::class)->middleware('permission:students.view');
    Route::get('students/api/admission-preview', [StudentController::class, 'apiAdmissionPreview'])->name('students.api.admission-preview');
    Route::get('students/api/roll-preview', [StudentController::class, 'apiRollPreview'])->name('students.api.roll-preview');
    Route::get('students/roll-number-preview', [StudentController::class, 'getRollNumberPreview'])->name('students.roll-number-preview');
    Route::get('students/api/sections/{classId}', [StudentController::class, 'getSections'])->name('students.api.sections');
    Route::resource('teachers', TeacherController::class)->middleware('permission:teachers.view');
    Route::resource('staff', StaffController::class)->middleware('permission:staff.view');
    Route::resource('team-members', TeamMemberController::class)->middleware('permission:team_members.view');
    Route::resource('parents', ParentModelController::class)->middleware('permission:parents.view');

    // ── Website ───────────────────────────────────────────
    Route::resource('sliders', SliderController::class)->middleware('permission:sliders.view');
    Route::resource('gallery-images', GalleryImageController::class)->middleware('permission:gallery.view');
    Route::resource('gallery-videos', GalleryVideoController::class)->middleware('permission:gallery.view');
    Route::resource('branches', BranchController::class)->middleware('permission:branches.view');
    Route::resource('contact-messages', ContactMessageController::class)->middleware('permission:contact_messages.view');

    // ── Classes & Sections ─────────────────────────────────
    Route::resource('classrooms', ClassroomController::class)->middleware('permission:classrooms.view');
    Route::resource('sections', SectionController::class)->middleware('permission:sections.view');
    Route::resource('class-assets', ClassAssetController::class)->middleware('permission:class_assets.view');
    Route::get('class-assets/api/sections', [ClassAssetController::class, 'apiSections'])->name('class-assets.api-sections');
    Route::resource('teacher-assignments', TeacherAssignmentController::class)->middleware('permission:subject_assignments.view');

    // ── Documents ─────────────────────────────────────────
    Route::resource('id-cards', IdCardController::class)->middleware('permission:id_cards.generate');
    Route::resource('certificates', CertificateController::class)->middleware('permission:certificates.generate');

    // ── Library ───────────────────────────────────────────
    Route::resource('library', LibraryBookController::class)->middleware('permission:library.view');
    Route::get('library/{library}/read', [LibraryBookController::class, 'read'])->name('library.read')->middleware('permission:library.view');
    Route::get('library/{library}/serve', [LibraryBookController::class, 'serveBook'])->name('library.serve')->middleware('permission:library.view');

    // ── Finance ───────────────────────────────────────────
    Route::resource('budgets', BudgetController::class)->middleware('permission:budgets.view');
    Route::resource('income-expenses', IncomeExpenseController::class)->middleware('permission:income_expenses.view');
    Route::resource('finance-statements', FinanceStatementController::class)->middleware('permission:finance_statements.view');
    Route::resource('fees', FeeController::class)->middleware('permission:fees.view');
    Route::resource('fee-payments', FeePaymentController::class)->middleware('permission:fee_payments.view');
    Route::resource('payrolls', PayrollController::class)->middleware('permission:payrolls.view');
    Route::resource('leaves', LeaveController::class)->middleware('permission:leaves.view');
    Route::resource('employee-assets', EmployeeAssetController::class)->middleware('permission:employee_assets.view');

    // Training & Capacity Building
    Route::resource('trainings', TrainingController::class)->middleware('permission:trainings.view');
    Route::post('trainings/{training}/participants', [TrainingController::class, 'addParticipant'])->name('trainings.participants.add')->middleware('permission:trainings.edit');
    Route::post('trainings/{training}/participants/bulk', [TrainingController::class, 'addBulkParticipants'])->name('trainings.participants.add-bulk')->middleware('permission:trainings.edit');
    Route::put('trainings/{training}/participants/{participantId}', [TrainingController::class, 'updateParticipant'])->name('trainings.participants.update')->middleware('permission:trainings.edit');
    Route::delete('trainings/{training}/participants/{participantId}', [TrainingController::class, 'removeParticipant'])->name('trainings.participants.remove')->middleware('permission:trainings.edit');

    // Stock Management
    Route::resource('stock', StockController::class)->middleware('permission:stock.view');
    Route::get('stock-in', [StockController::class, 'stockIn'])->name('stock.stock-in')->middleware('permission:stock.stock-in');
    Route::post('stock-in', [StockController::class, 'storeStockIn'])->name('stock.store-stock-in')->middleware('permission:stock.stock-in');
    Route::get('stock-out', [StockController::class, 'stockOut'])->name('stock.stock-out')->middleware('permission:stock.stock-out');
    Route::post('stock-out', [StockController::class, 'storeStockOut'])->name('stock.store-stock-out')->middleware('permission:stock.stock-out');
    Route::get('stock-transactions', [StockController::class, 'transactions'])->name('stock.transactions')->middleware('permission:stock.view');
    Route::get('stock-report', [StockController::class, 'report'])->name('stock.report')->middleware('permission:stock.report');
    Route::resource('performance-reports', PerformanceReportController::class)->middleware('permission:mark_sheets.view');
    Route::resource('progress-reports', ProgressReportController::class)->middleware('permission:mark_sheets.view');
    Route::resource('audits', AuditController::class)->middleware('permission:audits.view');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware('permission:notifications.view');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead')->middleware('permission:notifications.view');
    Route::get('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read')->middleware('permission:notifications.view');
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy')->middleware('permission:notifications.view');
    Route::get('api/notifications/unread', [NotificationController::class, 'apiUnreadCount'])->name('notifications.api.unread');
    Route::get('api/notifications/latest', [NotificationController::class, 'apiLatest'])->name('notifications.api.latest');

    // Academic Calendar
    Route::get('calendar', [CalendarEventController::class, 'index'])->name('calendar.index')->middleware('permission:calendar.view');
    Route::post('calendar', [CalendarEventController::class, 'store'])->name('calendar.store')->middleware('permission:calendar.manage');
    Route::put('calendar/{calendar_event}', [CalendarEventController::class, 'update'])->name('calendar.update')->middleware('permission:calendar.manage');
    Route::delete('calendar/{calendar_event}', [CalendarEventController::class, 'destroy'])->name('calendar.destroy')->middleware('permission:calendar.manage');
    Route::get('api/calendar/events', [CalendarEventController::class, 'apiEvents'])->name('calendar.api.events');
    Route::get('api/calendar/event/{calendar_event}', [CalendarEventController::class, 'apiEvent'])->name('calendar.api.event');
    Route::get('api/announcements', [CalendarEventController::class, 'apiAnnouncements'])->name('api.announcements');

    // Chat
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index')->middleware('permission:chat.access');
    Route::post('chat', [ChatController::class, 'storeConversation'])->name('chat.store')->middleware('permission:chat.access');
    Route::get('chat/{id}', [ChatController::class, 'show'])->name('chat.show')->middleware('permission:chat.access');
    Route::post('chat/{id}/send', [ChatController::class, 'sendMessage'])->name('chat.send')->middleware('permission:chat.access');
    Route::delete('chat/{id}', [ChatController::class, 'destroyConversation'])->name('chat.destroy')->middleware('permission:chat.access');
    Route::get('chat/{id}/messages', [ChatController::class, 'getMessages'])->name('chat.messages')->middleware('permission:chat.access');

    // Telegram
    Route::get('telegram', [TelegramController::class, 'index'])->name('telegram.index')->middleware('permission:telegram.manage');
    Route::put('telegram/settings', [TelegramController::class, 'updateSettings'])->name('telegram.update-settings')->middleware('permission:telegram.manage');
    Route::post('telegram/branch-settings', [TelegramController::class, 'updateBranchSettings'])->name('telegram.update-branch-settings')->middleware('permission:telegram.manage');
    Route::get('telegram/send', [TelegramController::class, 'send'])->name('telegram.send')->middleware('permission:telegram.manage');
    Route::post('telegram/send', [TelegramController::class, 'sendMessage'])->name('telegram.send-message')->middleware('permission:telegram.manage');
    Route::get('telegram/test', [TelegramController::class, 'testConnection'])->name('telegram.test')->middleware('permission:telegram.manage');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index')->middleware('permission:settings.view');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store')->middleware('permission:settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update')->middleware('permission:settings.edit');
    Route::post('/settings/update-all', [SettingController::class, 'update'])->name('settings.updateAll')->middleware('permission:settings.edit');
    Route::post('/settings/upload-logo', [SettingController::class, 'uploadLogo'])->name('settings.uploadLogo')->middleware('permission:settings.edit');
    Route::post('/settings/upload-favicon', [SettingController::class, 'uploadFavicon'])->name('settings.uploadFavicon')->middleware('permission:settings.edit');
    Route::delete('/settings/{id}', [SettingController::class, 'destroy'])->name('settings.destroy')->middleware('permission:settings.edit');

    // Roles & Permissions
    Route::resource('roles', RoleController::class)->middleware('permission:roles.view');
    Route::get('roles/{role}/users', [RoleController::class, 'users'])->name('roles.users')->middleware('permission:roles.view');
    Route::post('roles/{role}/assign-users', [RoleController::class, 'assignUsers'])->name('roles.assign-users')->middleware('permission:roles.edit');
    Route::post('roles/{role}/toggle-permission', [RoleController::class, 'togglePermission'])->name('roles.toggle-permission')->middleware('permission:roles.edit');

    // Database Backup & Export (Legacy)
    Route::get('database-backup', [DatabaseBackupController::class, 'index'])->name('database-backup.index')->middleware('permission:settings.view');
    Route::post('database-backup/export-send', [DatabaseBackupController::class, 'exportAndSend'])->name('database-backup.export-send')->middleware('permission:settings.edit');
    Route::post('database-backup/quick-export', [DatabaseBackupController::class, 'quickExport'])->name('database-backup.quick-export')->middleware('permission:settings.edit');
    Route::post('database-backup/download', [DatabaseBackupController::class, 'download'])->name('database-backup.download')->middleware('permission:settings.edit');

    // Scheduled Database Backup
    Route::get('backup', [ScheduledBackupController::class, 'index'])->name('backup.index')->middleware('permission:settings.view');
    Route::post('backup/now', [ScheduledBackupController::class, 'backupNow'])->name('backup.now')->middleware('permission:settings.edit');
    Route::get('backup/download/{filename}', [ScheduledBackupController::class, 'download'])->name('backup.download')->middleware('permission:settings.edit')->where('filename', '[^/]+');
    Route::delete('backup/{filename}', [ScheduledBackupController::class, 'delete'])->name('backup.delete')->middleware('permission:settings.edit')->where('filename', '[^/]+');
    Route::put('backup/schedule', [ScheduledBackupController::class, 'updateSchedule'])->name('backup.schedule')->middleware('permission:settings.edit');

    // Branch Budget Comparison
    Route::get('budget-comparison', [BudgetComparisonController::class, 'index'])->name('budget-comparison.index')->middleware('permission:budgets.view');

    // Financial Comparison
    Route::get('financial-comparison', [FinancialComparisonController::class, 'index'])->name('financial-comparison.index')->middleware('permission:finance_statements.view');

    // Performance Branch Comparison
    Route::get('performance-comparison', [PerformanceComparisonController::class, 'index'])->name('performance-comparison.index')->middleware('permission:mark_sheets.view');

    // Psychological Analysis
    Route::get('psychological-analysis', [PsychologicalAnalysisController::class, 'index'])->name('psychological-analysis.index')->middleware('permission:mark_sheets.view');
    Route::post('psychological-analysis/generate', [PsychologicalAnalysisController::class, 'generate'])->name('psychological-analysis.generate')->middleware('permission:mark_sheets.generate');

    // Performance Analysis & Suggestions
    Route::get('performance', [PerformanceAnalysisController::class, 'index'])->name('performance.index')->middleware('permission:mark_sheets.view');
    Route::get('performance/student/{id}', [PerformanceAnalysisController::class, 'studentAnalysis'])->name('performance.student')->middleware('permission:mark_sheets.view');
    Route::get('performance/class-comparison', [PerformanceAnalysisController::class, 'classComparison'])->name('performance.class-comparison')->middleware('permission:mark_sheets.view');
    Route::get('performance/branch-comparison', [PerformanceAnalysisController::class, 'branchComparison'])->name('performance.branch-comparison')->middleware('permission:mark_sheets.view');
    Route::get('performance/gender', [PerformanceAnalysisController::class, 'genderAnalysis'])->name('performance.gender')->middleware('permission:mark_sheets.view');
    Route::get('performance/at-risk', [PerformanceAnalysisController::class, 'atRiskStudents'])->name('performance.at-risk')->middleware('permission:mark_sheets.view');
    Route::get('performance/suggestions/{id}', [PerformanceAnalysisController::class, 'suggestions'])->name('performance.suggestions')->middleware('permission:mark_sheets.view');

    // Web Content Management
    Route::get('web-content', [WebContentController::class, 'index'])->name('web-content.index')->middleware('permission:settings.view');
    Route::match(['post', 'put'], 'web-content', [WebContentController::class, 'update'])->name('web-content.update')->middleware('permission:settings.edit');
    Route::post('web-content/upload', [WebContentController::class, 'upload'])->name('web-content.upload')->middleware('permission:settings.edit');

    // Announcements
    Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index')->middleware('permission:calendar.view');
    Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store')->middleware('permission:calendar.manage');
    Route::post('announcements/{id}/send-telegram', [AnnouncementController::class, 'sendToTelegram'])->name('announcements.send-telegram')->middleware('permission:telegram.manage');
    Route::delete('announcements/{id}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy')->middleware('permission:calendar.manage');

    // Media Upload (admin)
    Route::post('media/upload', [MediaController::class, 'upload'])->name('media.upload');

    // Report Document Exchange
    Route::get('report-exchange', [ReportExchangeController::class, 'index'])->name('report-exchange.index')->middleware('permission:settings.view');
    Route::get('report-exchange/create', [ReportExchangeController::class, 'create'])->name('report-exchange.create')->middleware('permission:settings.edit');
    Route::post('report-exchange', [ReportExchangeController::class, 'store'])->name('report-exchange.store')->middleware('permission:settings.edit');
    Route::get('report-exchange/{report_exchange}', [ReportExchangeController::class, 'show'])->name('report-exchange.show')->middleware('permission:settings.view');
    Route::get('report-exchange/{report_exchange}/edit', [ReportExchangeController::class, 'edit'])->name('report-exchange.edit')->middleware('permission:settings.edit');
    Route::put('report-exchange/{report_exchange}', [ReportExchangeController::class, 'update'])->name('report-exchange.update')->middleware('permission:settings.edit');
    Route::delete('report-exchange/{report_exchange}', [ReportExchangeController::class, 'destroy'])->name('report-exchange.destroy')->middleware('permission:settings.edit');
    Route::get('report-exchange/{report_exchange}/download', [ReportExchangeController::class, 'download'])->name('report-exchange.download')->middleware('permission:settings.view');
    Route::post('report-exchange/{report_exchange}/comment', [ReportExchangeController::class, 'addComment'])->name('report-exchange.comment')->middleware('permission:settings.edit');
    Route::get('report-exchange-api/terms', [ReportExchangeController::class, 'getTerms'])->name('report-exchange.terms');

    // User Access Management
    Route::get('user-access/teachers', [TeacherAccessController::class, 'index'])->name('user-access.teachers');
    Route::post('user-access/teachers/create', [TeacherAccessController::class, 'createAccount'])->name('user-access.teachers.create');
    Route::post('user-access/teachers/permissions', [TeacherAccessController::class, 'assignPermissions'])->name('user-access.teachers.permissions');
    Route::get('user-access/students', [StudentAccessController::class, 'index'])->name('user-access.students');
    Route::post('user-access/students/create', [StudentAccessController::class, 'createAccount'])->name('user-access.students.create');
    Route::post('user-access/students/bulk', [StudentAccessController::class, 'bulkCreate'])->name('user-access.students.bulk');
    Route::get('user-access/parents', [ParentAccessController::class, 'index'])->name('user-access.parents');
    Route::post('user-access/parents/create', [ParentAccessController::class, 'createAccount'])->name('user-access.parents.create');
});
