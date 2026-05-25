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
use App\Http\Controllers\Certificate\TranscriptController;
use App\Http\Controllers\Certificate\LeavingCertificateController;
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
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Parent\ParentDashboardController;
use App\Http\Controllers\Subject\SubjectController;
use App\Http\Controllers\Teacher\TeacherController;
use App\Http\Controllers\TeacherAssignment\TeacherAssignmentController;
use App\Http\Controllers\TeamMember\TeamMemberController;
use App\Http\Controllers\Telegram\TelegramController;
use App\Http\Controllers\Term\TermController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Library\LibraryBookController;
use App\Http\Controllers\Library\VideoLibraryController;
use App\Http\Controllers\Stock\StockController;
use App\Http\Controllers\Training\TrainingController;
use App\Http\Controllers\Backup\DatabaseBackupController;
use App\Http\Controllers\UserAccess\TeacherAccessController;
use App\Http\Controllers\UserAccess\StudentAccessController;
use App\Http\Controllers\UserAccess\ParentAccessController;
use App\Http\Controllers\ReportExchange\ReportExchangeController;
use App\Http\Controllers\News\NewsController;
use App\Http\Controllers\Admin\MarkEntryLockController;
use App\Http\Controllers\Admin\MarkEntryPermissionController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Attendance\AttendanceDelegationController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\LessonPlan\LessonPlanController;
use App\Http\Controllers\LessonPlan\LessonPlanFollowUpController;
use App\Http\Controllers\Report\GraphicalReportController;
use App\Http\Controllers\Email\EmailInboxController;
use App\Http\Controllers\Bank\BankIntegrationController;
use App\Http\Controllers\Club\ClubFollowUpConfigController;
use App\Http\Controllers\Exam\ExamQuestionController;
use App\Http\Controllers\Assessment\AssessmentQuestionController;
use App\Http\Controllers\Assessment\StudentAssessmentController;
use App\Http\Controllers\Enrollment\EnrollmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/gallery', [HomeController::class, 'gallery'])->name('gallery');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/team', [HomeController::class, 'team'])->name('team');

// Public Contact Form
Route::post('contact', [ContactMessageController::class, 'store'])->name('contact.store');

// Language Switcher
Route::get('lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset (self-service)
Route::get('/password/forgot', [AuthController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/password/forgot', [AuthController::class, 'submitForgotPassword'])->name('password.forgot.submit');
Route::post('/password/verify-security', [AuthController::class, 'verifySecurityAnswer'])->name('password.verify.security');
Route::post('/password/reset', [AuthController::class, 'submitResetPassword'])->name('password.reset.submit');

// Telegram webhook (public)
Route::post('telegram/webhook', [TelegramController::class, 'webhook']);

// Media fallback route - serves storage files when symlink doesn't exist (e.g., XAMPP)
Route::get('storage/{path}', [MediaController::class, 'serve'])->where('path', '.*');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'branch-scope'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard.view');

    // Admin Profile & Password Change
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/reset-user-password', [ProfileController::class, 'resetUserPassword'])->name('profile.reset-user-password');

    // ── Academic ──────────────────────────────────────────
    Route::resource('academic-years', AcademicYearController::class)->middleware('permission:academic_years.view');
    Route::resource('terms', TermController::class)->middleware('permission:terms.view');
    Route::resource('exams', ExamController::class)->middleware('permission:exams.view');
    Route::resource('subjects', SubjectController::class)->middleware('permission:subjects.view');
    Route::resource('subject-assignments', SubjectAssignmentController::class)->middleware('permission:subject_assignments.view');
    Route::delete('subject-assignments/bulk-delete', [SubjectAssignmentController::class, 'bulkDelete'])->name('subject-assignments.bulk-delete')->middleware('permission:subject_assignments.delete');
    Route::get('subject-assignments/api/classes', [SubjectAssignmentController::class, 'apiClasses'])->name('subject-assignments.api.classes');
    Route::get('subject-assignments/api/sections', [SubjectAssignmentController::class, 'apiSections'])->name('subject-assignments.api.sections');

    // Mark Entry API routes (MUST be defined BEFORE the resource route to avoid conflicts)
    Route::get('mark-entries/api/terms', [MarkEntryController::class, 'apiTerms'])->name('mark-entries.api.terms');
    Route::get('mark-entries/api/classes', [MarkEntryController::class, 'apiClasses'])->name('mark-entries.api.classes');
    Route::get('mark-entries/api/sections', [MarkEntryController::class, 'apiSections'])->name('mark-entries.api.sections');
    Route::get('mark-entries/api/subjects', [MarkEntryController::class, 'apiSubjects'])->name('mark-entries.api.subjects');
    Route::get('mark-entries/api/students', [MarkEntryController::class, 'apiStudents'])->name('mark-entries.api.students');
    Route::get('mark-entries/api/load-students', [MarkEntryController::class, 'apiLoadStudents'])->name('mark-entries.api.load-students');
    Route::post('mark-entries/api/save', [MarkEntryController::class, 'apiSave'])->name('mark-entries.api.save');
    Route::get('mark-entries/api/check-lock', [MarkEntryLockController::class, 'apiCheckLock'])->name('mark-entries.api.check-lock');
    Route::resource('mark-entries', MarkEntryController::class)->middleware('permission:mark_entries.view');

    // Mark Entry Lock Management (Branch Principal / Admin)
    Route::get('mark-entry-locks', [MarkEntryLockController::class, 'index'])->name('mark-entry-locks.index')->middleware('permission:mark_entries.view');
    Route::post('mark-entry-locks/lock', [MarkEntryLockController::class, 'lock'])->name('mark-entry-locks.lock')->middleware('permission:mark_entries.manage');
    Route::post('mark-entry-locks/unlock', [MarkEntryLockController::class, 'unlock'])->name('mark-entry-locks.unlock')->middleware('permission:mark_entries.manage');

    // Mark Entry Permission Management (Branch Principal / Admin)
    Route::get('mark-entry-permissions', [MarkEntryPermissionController::class, 'index'])->name('mark-entry-permissions.index')->middleware('permission:mark_entries.view');
    Route::get('mark-entry-permissions/create', [MarkEntryPermissionController::class, 'create'])->name('mark-entry-permissions.create')->middleware('permission:mark_entries.manage');
    Route::post('mark-entry-permissions', [MarkEntryPermissionController::class, 'store'])->name('mark-entry-permissions.store')->middleware('permission:mark_entries.manage');
    Route::post('mark-entry-permissions/batch', [MarkEntryPermissionController::class, 'batchStore'])->name('mark-entry-permissions.batch')->middleware('permission:mark_entries.manage');
    Route::delete('mark-entry-permissions/{id}/revoke', [MarkEntryPermissionController::class, 'revoke'])->name('mark-entry-permissions.revoke')->middleware('permission:mark_entries.manage');
    Route::get('mark-entry-permissions/api/students', [MarkEntryPermissionController::class, 'apiStudents'])->name('mark-entry-permissions.api.students');
    Route::get('mark-entry-permissions/api/teacher-subjects', [MarkEntryPermissionController::class, 'apiTeacherSubjects'])->name('mark-entry-permissions.api.teacher-subjects');

    // Promotion & Detention Management
    Route::get('promotion', [PromotionController::class, 'index'])->name('promotion.index')->middleware('permission:mark_entries.view');
    Route::get('promotion/preview', [PromotionController::class, 'preview'])->name('promotion.preview')->middleware('permission:mark_entries.view');
    Route::post('promotion/process', [PromotionController::class, 'processClass'])->name('promotion.process')->middleware('permission:mark_entries.manage');
    Route::post('promotion/process-student', [PromotionController::class, 'processStudent'])->name('promotion.process-student')->middleware('permission:mark_entries.manage');
    Route::patch('promotion/override', [PromotionController::class, 'processStudent'])->name('promotion.override')->middleware('permission:mark_entries.manage');
    Route::get('promotion/{id}', [PromotionController::class, 'show'])->name('promotion.detail')->middleware('permission:mark_entries.view');
    Route::get('promotion/{id}/edit', [PromotionController::class, 'show'])->name('promotion.edit')->middleware('permission:mark_entries.manage');
    Route::get('promotion/settings/index', [PromotionController::class, 'settings'])->name('promotion.settings.index')->middleware('permission:mark_entries.manage');
    Route::post('promotion/settings', [PromotionController::class, 'storeSettings'])->name('promotion.settings.store')->middleware('permission:mark_entries.manage');
    Route::get('promotion/grade-scales/index', [PromotionController::class, 'gradeScales'])->name('promotion.grade-scales.index')->middleware('permission:mark_entries.manage');
    Route::post('promotion/grade-scales', [PromotionController::class, 'storeGradeScale'])->name('promotion.grade-scales.store')->middleware('permission:mark_entries.manage');
    Route::get('promotion/print', [PromotionController::class, 'print'])->name('promotion.print')->middleware('permission:mark_entries.view');

    // Mark Sheet
    Route::get('mark-sheet', [MarkSheetController::class, 'index'])->name('mark-sheet.index')->middleware('permission:mark_sheets.view');
    Route::post('mark-sheet/generate', [MarkSheetController::class, 'generate'])->name('mark-sheet.generate')->middleware('permission:mark_sheets.generate');
    Route::get('mark-sheet/api/sections', [MarkSheetController::class, 'getSections'])->name('mark-sheet.sections');
    Route::get('mark-sheet/api/students', [MarkSheetController::class, 'getStudents'])->name('mark-sheet.students');

    // Full Mark Sheet
    Route::get('mark-sheet-full', [MarkSheetFullController::class, 'index'])->name('mark-sheet-full.index')->middleware('permission:mark_sheets.view');
    Route::match(['get', 'post'], 'mark-sheet-full/generate', [MarkSheetFullController::class, 'generate'])->name('mark-sheet-full.generate')->middleware('permission:mark_sheets.generate');
    Route::get('mark-sheet-full/api/sections', [MarkSheetFullController::class, 'getSections'])->name('mark-sheet-full.sections');

    // Mark Roster
    Route::get('mark-roster', [MarkRosterController::class, 'index'])->name('mark-roster.index')->middleware('permission:mark_sheets.view');
    Route::match(['get', 'post'], 'mark-roster/generate', [MarkRosterController::class, 'generate'])->name('mark-roster.generate')->middleware('permission:mark_sheets.generate');
    Route::get('mark-roster/api/sections', [MarkRosterController::class, 'getSections'])->name('mark-roster.sections');

    // Report Card
    Route::get('report-card', [ReportCardController::class, 'index'])->name('report-card.index')->middleware('permission:mark_sheets.view');
    Route::match(['get', 'post'], 'report-card/generate', [ReportCardController::class, 'generate'])->name('report-card.generate')->middleware('permission:mark_sheets.generate');
    Route::get('report-card/api/sections', [ReportCardController::class, 'getSections'])->name('report-card.sections');
    Route::get('report-card/api/students', [ReportCardController::class, 'getStudents'])->name('report-card.students');

    // Performance Analysis (Legacy - Class-based)
    Route::get('performance-analysis', [PerformanceReportAnalysisController::class, 'index'])->name('performance-analysis.index')->middleware('permission:mark_sheets.view');
    Route::match(['get', 'post'], 'performance-analysis/generate', [PerformanceReportAnalysisController::class, 'generate'])->name('performance-analysis.generate')->middleware('permission:mark_sheets.generate');
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

    // Transcript Generation
    Route::get('transcript', [TranscriptController::class, 'index'])->name('transcript.index')->middleware('permission:certificates.generate');
    Route::get('transcript/generate', [TranscriptController::class, 'generateForm'])->name('transcript.generate-form')->middleware('permission:certificates.generate');
    Route::post('transcript/generate', [TranscriptController::class, 'generate'])->name('transcript.generate')->middleware('permission:certificates.generate');
    Route::get('transcript/api/students', [TranscriptController::class, 'getStudents'])->name('transcript.students');

    // Leaving Certificate Generation
    Route::get('leaving-certificate', [LeavingCertificateController::class, 'index'])->name('leaving-certificate.index')->middleware('permission:certificates.generate');
    Route::post('leaving-certificate/generate', [LeavingCertificateController::class, 'generate'])->name('leaving-certificate.generate')->middleware('permission:certificates.generate');
    Route::get('leaving-certificate/api/students', [LeavingCertificateController::class, 'getStudents'])->name('leaving-certificate.students');

    // ── People ────────────────────────────────────────────
    Route::get('students/generate-ids', [StudentController::class, 'generateIds'])->name('students.generateIds');
    Route::resource('students', StudentController::class)->middleware('permission:students.view');
    Route::get('students/api/admission-preview', [StudentController::class, 'apiAdmissionPreview'])->name('students.api.admission-preview');
    Route::get('students/api/roll-preview', [StudentController::class, 'apiRollPreview'])->name('students.api.roll-preview');
    Route::get('students/roll-number-preview', [StudentController::class, 'getRollNumberPreview'])->name('students.roll-number-preview');
    Route::get('students/api/sections/{classId}', [StudentController::class, 'getSections'])->name('students.api.sections');
    Route::get('students/api/sections-by-branch', [StudentController::class, 'apiSectionsByBranch'])->name('students.api.sections-by-branch');
    Route::get('students/inactive/list', [StudentController::class, 'inactive'])->name('students.inactive')->middleware('permission:students.view');
    Route::get('students/{student}/readmit', [StudentController::class, 'readmit'])->name('students.readmit')->middleware('permission:students.manage');
    Route::post('students/{student}/readmit', [StudentController::class, 'readmitStore'])->name('students.readmit-store')->middleware('permission:students.manage');
    Route::post('students/{student}/mark-as-left', [StudentController::class, 'markAsLeft'])->name('students.mark-as-left')->middleware('permission:students.manage');
    Route::get('students/{student}/transfer', [StudentController::class, 'transferForm'])->name('students.transfer')->middleware('permission:students.manage');
    Route::post('students/{student}/transfer', [StudentController::class, 'transfer'])->name('students.transfer-store')->middleware('permission:students.manage');
    Route::post('students/bulk-transfer', [StudentController::class, 'bulkTransfer'])->name('students.bulk-transfer')->middleware('permission:students.manage');
    Route::resource('teachers', TeacherController::class)->middleware('permission:teachers.view');
    Route::resource('staff', StaffController::class)->middleware('permission:staff.view');
    Route::resource('team-members', TeamMemberController::class)->middleware('permission:team_members.view');
    Route::resource('parents', ParentModelController::class)->middleware('permission:parents.view');

    // ── Enrollment ────────────────────────────────────────
    Route::get('enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index')->middleware('permission:students.view');
    Route::get('enrollments/create', [EnrollmentController::class, 'create'])->name('enrollments.create')->middleware('permission:students.manage');
    Route::post('enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store')->middleware('permission:students.manage');
    Route::get('enrollments/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollments.show')->middleware('permission:students.view');
    Route::get('enrollments/{enrollment}/edit', [EnrollmentController::class, 'edit'])->name('enrollments.edit')->middleware('permission:students.manage');
    Route::put('enrollments/{enrollment}', [EnrollmentController::class, 'update'])->name('enrollments.update')->middleware('permission:students.manage');
    Route::delete('enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy')->middleware('permission:students.manage');
    Route::get('enrollments/bulk-enroll', [EnrollmentController::class, 'bulkEnroll'])->name('enrollments.bulk-enroll')->middleware('permission:students.manage');
    Route::post('enrollments/bulk-enroll', [EnrollmentController::class, 'processBulkEnroll'])->name('enrollments.process-bulk-enroll')->middleware('permission:students.manage');
    Route::get('enrollments/{enrollment}/pay-registration-fee', [EnrollmentController::class, 'payRegistrationFee'])->name('enrollments.pay-registration-fee')->middleware('permission:fee_payments.manage');
    Route::post('enrollments/{enrollment}/pay-registration-fee', [EnrollmentController::class, 'processPayRegistrationFee'])->name('enrollments.process-pay-registration-fee')->middleware('permission:fee_payments.manage');
    Route::post('enrollments/{enrollment}/waive-registration-fee', [EnrollmentController::class, 'waiveRegistrationFee'])->name('enrollments.waive-registration-fee')->middleware('permission:fee_payments.manage');
    Route::get('enrollments/{enrollment}/withdraw', [EnrollmentController::class, 'withdraw'])->name('enrollments.withdraw')->middleware('permission:students.manage');
    Route::post('enrollments/{enrollment}/withdraw', [EnrollmentController::class, 'processWithdraw'])->name('enrollments.process-withdraw')->middleware('permission:students.manage');
    Route::get('enrollments/api/sections', [EnrollmentController::class, 'apiSections'])->name('enrollments.api.sections');
    Route::get('enrollments/api/classes', [EnrollmentController::class, 'apiClasses'])->name('enrollments.api.classes');
    Route::get('enrollments/api/unenrolled-students', [EnrollmentController::class, 'apiUnenrolledStudents'])->name('enrollments.api.unenrolled-students');
    Route::get('enrollments/api/stats', [EnrollmentController::class, 'apiStats'])->name('enrollments.api.stats');

    // ── Website ───────────────────────────────────────────
    Route::resource('sliders', SliderController::class)->middleware('permission:sliders.view');
    Route::resource('gallery-images', GalleryImageController::class)->middleware('permission:gallery.view');
    Route::resource('gallery-videos', GalleryVideoController::class)->middleware('permission:gallery.view');
    Route::resource('branches', BranchController::class)->middleware('permission:branches.view');
    Route::resource('contact-messages', ContactMessageController::class)->middleware('permission:contact_messages.view');
    Route::resource('news', NewsController::class)->middleware('permission:news.manage');

    // ── Classes & Sections ─────────────────────────────────
    Route::resource('classrooms', ClassroomController::class)->middleware('permission:classrooms.view');
    Route::resource('sections', SectionController::class)->middleware('permission:sections.view');
    Route::resource('class-assets', ClassAssetController::class)->middleware('permission:class_assets.view');
    Route::get('class-assets/api/sections', [ClassAssetController::class, 'apiSections'])->name('class-assets.api-sections');
    Route::resource('teacher-assignments', TeacherAssignmentController::class)->middleware('permission:subject_assignments.view');
    Route::get('teacher-assignments/api/sections', [TeacherAssignmentController::class, 'apiSections'])->name('teacher-assignments.api.sections');
    Route::get('teacher-assignments/api/subjects', [TeacherAssignmentController::class, 'apiSubjects'])->name('teacher-assignments.api.subjects');

    // ── Lesson Plans ─────────────────────────────────────────
    Route::resource('lesson-plans', LessonPlanController::class)->middleware('permission:lesson_plans.view');
    Route::post('lesson-plans/{lessonPlan}/review', [LessonPlanController::class, 'review'])->name('lesson-plans.review')->middleware('permission:lesson_plans.review');
    Route::post('lesson-plans/{lessonPlan}/department-review', [LessonPlanController::class, 'departmentReview'])->name('lesson-plans.department-review')->middleware('permission:lesson_plans.review');
    Route::post('lesson-plans/{lessonPlan}/principal-review', [LessonPlanController::class, 'principalReview'])->name('lesson-plans.principal-review')->middleware('permission:lesson_plans.review');
    Route::post('lesson-plans/{lessonPlan}/follow-ups', [LessonPlanFollowUpController::class, 'store'])->name('lesson-plans.follow-ups.store')->middleware('permission:lesson_plans.follow_up');
    Route::put('lesson-plans/{lessonPlan}/follow-ups/{followUp}', [LessonPlanFollowUpController::class, 'update'])->name('lesson-plans.follow-ups.update')->middleware('permission:lesson_plans.follow_up');
    Route::delete('lesson-plans/{lessonPlan}/follow-ups/{followUp}', [LessonPlanFollowUpController::class, 'destroy'])->name('lesson-plans.follow-ups.destroy')->middleware('permission:lesson_plans.follow_up');

    // ── Exam Questions (Department → Principal Pipeline) ──────
    Route::resource('exam-questions', ExamQuestionController::class)->middleware('permission:exams.view');
    Route::post('exam-questions/{exam_question}/department-review', [ExamQuestionController::class, 'reviewByDepartment'])->name('exam-questions.department-review')->middleware('permission:exams.manage');
    Route::post('exam-questions/{exam_question}/principal-review', [ExamQuestionController::class, 'reviewByPrincipal'])->name('exam-questions.principal-review')->middleware('permission:exams.manage');
    Route::post('exam-questions/{exam_question}/request-revision', [ExamQuestionController::class, 'requestRevision'])->name('exam-questions.request-revision')->middleware('permission:exams.manage');

    // ── Self-Assessment Questions (Teacher creates, Student answers) ──
    Route::resource('assessment-questions', AssessmentQuestionController::class)->middleware('permission:lesson_plans.view');
    Route::post('assessment-questions/{assessment_question}/toggle-active', [AssessmentQuestionController::class, 'toggleActive'])->name('assessment-questions.toggle-active')->middleware('permission:lesson_plans.create');
    Route::get('assessment-questions/bulk/create', [AssessmentQuestionController::class, 'bulkCreate'])->name('assessment-questions.bulk-create')->middleware('permission:lesson_plans.create');
    Route::post('assessment-questions/bulk', [AssessmentQuestionController::class, 'bulkStore'])->name('assessment-questions.bulk-store')->middleware('permission:lesson_plans.create');
    Route::get('assessment-questions/report', [AssessmentQuestionController::class, 'report'])->name('assessment-questions.report')->middleware('permission:lesson_plans.view');
    Route::get('assessment-questions/api/sections/{classId}', [AssessmentQuestionController::class, 'apiSections'])->name('assessment-questions.api-sections');

    // ── Documents ─────────────────────────────────────────
    Route::resource('id-cards', IdCardController::class)->middleware('permission:id_cards.generate');
    Route::resource('certificates', CertificateController::class)->middleware('permission:certificates.generate');

    // ── Attendance ────────────────────────────────────────
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index')->middleware('permission:attendance.view');
    Route::get('attendance/create', [AttendanceController::class, 'create'])->name('attendance.create')->middleware('permission:attendance.manage');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store')->middleware('permission:attendance.manage');
    Route::get('attendance/{date}', [AttendanceController::class, 'show'])->name('attendance.show')->middleware('permission:attendance.view');
    Route::get('attendance/{date}/{classId}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit')->middleware('permission:attendance.manage');
    Route::put('attendance', [AttendanceController::class, 'update'])->name('attendance.update')->middleware('permission:attendance.manage');
    Route::get('attendance-report', [AttendanceController::class, 'report'])->name('attendance.report')->middleware('permission:attendance.view');
    Route::get('attendance-api/students', [AttendanceController::class, 'apiStudents'])->name('attendance.api.students');

    // ── Attendance Delegation ──────────────────────────────
    Route::get('attendance-delegation', [AttendanceDelegationController::class, 'index'])->name('attendance-delegation.index')->middleware('permission:attendance.manage');
    Route::post('attendance-delegation', [AttendanceDelegationController::class, 'store'])->name('attendance-delegation.store')->middleware('permission:attendance.manage');
    Route::post('attendance-delegation/{delegation}/revoke', [AttendanceDelegationController::class, 'revoke'])->name('attendance-delegation.revoke')->middleware('permission:attendance.manage');
    Route::get('attendance-delegation-api/sections/{class}', [AttendanceDelegationController::class, 'apiSections'])->name('attendance-delegation.api.sections');

    // ── Library ───────────────────────────────────────────
    Route::resource('library', LibraryBookController::class)->middleware('permission:library.view');
    Route::get('library/{library}/read', [LibraryBookController::class, 'read'])->name('library.read')->middleware('permission:library.view');
    Route::get('library/{library}/serve', [LibraryBookController::class, 'serveBook'])->name('library.serve')->middleware('permission:library.view');

    // ── Video Library ────────────────────────────────────
    Route::resource('video-library', VideoLibraryController::class)->middleware('permission:library.view');
    Route::get('video-library/{video_library}/embed', [VideoLibraryController::class, 'show'])->name('video-library.embed')->middleware('permission:library.view');

    // ── Stock Management ─────────────────────────────────
    Route::resource('stock', StockController::class)->middleware('permission:settings.view');
    Route::get('stock/stock-in', [StockController::class, 'stockIn'])->name('stock.stock-in')->middleware('permission:settings.edit');
    Route::post('stock/stock-in', [StockController::class, 'storeStockIn'])->name('stock.store-stock-in')->middleware('permission:settings.edit');
    Route::get('stock/stock-out', [StockController::class, 'stockOut'])->name('stock.stock-out')->middleware('permission:settings.edit');
    Route::post('stock/stock-out', [StockController::class, 'storeStockOut'])->name('stock.store-stock-out')->middleware('permission:settings.edit');
    Route::get('stock/transactions', [StockController::class, 'transactions'])->name('stock.transactions')->middleware('permission:settings.view');
    Route::get('stock/report', [StockController::class, 'report'])->name('stock.report')->middleware('permission:settings.view');

    // ── Training ─────────────────────────────────────────
    Route::resource('trainings', TrainingController::class)->middleware('permission:settings.view');
    Route::post('trainings/{training}/participants', [TrainingController::class, 'addParticipant'])->name('trainings.add-participant')->middleware('permission:settings.edit');
    Route::post('trainings/{training}/participants/bulk', [TrainingController::class, 'addBulkParticipants'])->name('trainings.add-bulk-participants')->middleware('permission:settings.edit');
    Route::put('trainings/{training}/participants/{participantId}', [TrainingController::class, 'updateParticipant'])->name('trainings.update-participant')->middleware('permission:settings.edit');
    Route::delete('trainings/{training}/participants/{participantId}', [TrainingController::class, 'removeParticipant'])->name('trainings.remove-participant')->middleware('permission:settings.edit');

    // ── Finance ───────────────────────────────────────────
    Route::resource('budgets', BudgetController::class)->middleware('permission:budgets.view');
    Route::resource('income-expenses', IncomeExpenseController::class)->middleware('permission:income_expenses.view');
    Route::resource('finance-statements', FinanceStatementController::class)->middleware('permission:finance_statements.view');
    Route::resource('fees', FeeController::class)->middleware('permission:fees.view');
    Route::resource('fee-payments', FeePaymentController::class)->middleware('permission:fee_payments.view');
    Route::resource('payrolls', PayrollController::class)->middleware('permission:payrolls.view');
    Route::resource('leaves', LeaveController::class)->middleware('permission:leaves.view');
    Route::resource('employee-assets', EmployeeAssetController::class)->middleware('permission:employee_assets.view');
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

    // Database Backup & Export (redirects to unified backup page)
    Route::get('database-backup', fn() => redirect()->route('admin.backup.index'))->name('database-backup.index');
    Route::post('database-backup/export-send', [DatabaseBackupController::class, 'exportAndSend'])->name('database-backup.export-send')->middleware('permission:settings.edit');
    Route::post('database-backup/quick-export', [DatabaseBackupController::class, 'quickExport'])->name('database-backup.quick-export')->middleware('permission:settings.edit');
    Route::post('database-backup/download', [DatabaseBackupController::class, 'backupNow'])->name('database-backup.download')->middleware('permission:settings.edit');

    // Scheduled Database Backup
    Route::get('backup', [DatabaseBackupController::class, 'index'])->name('backup.index')->middleware('permission:settings.view');
    Route::post('backup/now', [DatabaseBackupController::class, 'backupNow'])->name('backup.now')->middleware('permission:settings.edit');
    Route::post('backup/test-email', [DatabaseBackupController::class, 'testEmail'])->name('backup.test-email')->middleware('permission:settings.edit');
    Route::get('backup/download/{filename}', [DatabaseBackupController::class, 'download'])->name('backup.download')->middleware('permission:settings.edit')->where('filename', '[^/]+');
    Route::delete('backup/{filename}', [DatabaseBackupController::class, 'delete'])->name('backup.delete')->middleware('permission:settings.edit')->where('filename', '[^/]+');
    Route::put('backup/schedule', [DatabaseBackupController::class, 'updateSchedule'])->name('backup.schedule')->middleware('permission:settings.edit');

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
    Route::post('user-access/teachers/reset-password', [TeacherAccessController::class, 'resetPassword'])->name('user-access.teachers.reset-password');
    Route::get('user-access/students', [StudentAccessController::class, 'index'])->name('user-access.students');
    Route::post('user-access/students/create', [StudentAccessController::class, 'createAccount'])->name('user-access.students.create');
    Route::post('user-access/students/bulk', [StudentAccessController::class, 'bulkCreate'])->name('user-access.students.bulk');
    Route::post('user-access/students/reset-password', [StudentAccessController::class, 'resetPassword'])->name('user-access.students.reset-password');
    Route::get('user-access/parents', [ParentAccessController::class, 'index'])->name('user-access.parents');
    Route::post('user-access/parents/create', [ParentAccessController::class, 'createAccount'])->name('user-access.parents.create');
    Route::post('user-access/parents/reset-password', [ParentAccessController::class, 'resetPassword'])->name('user-access.parents.reset-password');

    // ── Graphical Reports ──────────────────────────────────
    Route::get('graphical-reports', [GraphicalReportController::class, 'index'])->name('graphical-reports.index')->middleware('permission:dashboard.view');

    // ── Email Inbox (Gmail IMAP Integration) ────────────────
    Route::get('email-inbox', [EmailInboxController::class, 'index'])->name('email-inbox.index')->middleware('permission:settings.view');
    Route::get('email-inbox/{email_message}', [EmailInboxController::class, 'show'])->name('email-inbox.show')->middleware('permission:settings.view');
    Route::post('email-inbox/{email_message}/category', [EmailInboxController::class, 'updateCategory'])->name('email-inbox.update-category')->middleware('permission:settings.edit');
    Route::post('email-inbox/{email_message}/star', [EmailInboxController::class, 'toggleStar'])->name('email-inbox.toggle-star')->middleware('permission:settings.edit');
    Route::post('email-inbox/{email_message}/assign', [EmailInboxController::class, 'assign'])->name('email-inbox.assign')->middleware('permission:settings.edit');
    Route::get('email-inbox-settings', [EmailInboxController::class, 'settings'])->name('email-inbox.settings')->middleware('permission:settings.view');
    Route::post('email-inbox-settings', [EmailInboxController::class, 'storeSettings'])->name('email-inbox.settings.store')->middleware('permission:settings.edit');
    Route::put('email-inbox-settings/{inboxSetting}', [EmailInboxController::class, 'updateSettings'])->name('email-inbox.settings.update')->middleware('permission:settings.edit');
    Route::delete('email-inbox-settings/{inboxSetting}', [EmailInboxController::class, 'destroySettings'])->name('email-inbox.settings.destroy')->middleware('permission:settings.edit');
    Route::post('email-inbox-sync/{inboxSetting}', [EmailInboxController::class, 'syncInbox'])->name('email-inbox.sync')->middleware('permission:settings.edit');

    // ── Bank Integration (Ethiopian Banks) ───────────────────
    Route::get('bank-integration', [BankIntegrationController::class, 'index'])->name('bank-integration.index')->middleware('permission:fee_payments.view');
    Route::get('bank-integration-settings', [BankIntegrationController::class, 'settings'])->name('bank-integration.settings')->middleware('permission:fees.view');
    Route::post('bank-integration-settings', [BankIntegrationController::class, 'storeSettings'])->name('bank-integration.settings.store')->middleware('permission:fees.manage');
    Route::delete('bank-integration-settings/{bankIntegration}', [BankIntegrationController::class, 'destroySettings'])->name('bank-integration.settings.destroy')->middleware('permission:fees.manage');
    Route::post('bank-integration/upload-csv', [BankIntegrationController::class, 'uploadCsv'])->name('bank-integration.upload-csv')->middleware('permission:fee_payments.manage');
    Route::post('bank-integration/{bankTransaction}/match', [BankIntegrationController::class, 'manualMatch'])->name('bank-integration.match')->middleware('permission:fee_payments.manage');
    Route::post('bank-integration/{bankTransaction}/reject', [BankIntegrationController::class, 'rejectTransaction'])->name('bank-integration.reject')->middleware('permission:fee_payments.manage');
    Route::post('bank-integration/{bankTransaction}/unmatched', [BankIntegrationController::class, 'markUnmatched'])->name('bank-integration.unmatched')->middleware('permission:fee_payments.manage');
    Route::get('bank-integration-search-students', [BankIntegrationController::class, 'searchStudents'])->name('bank-integration.search-students')->middleware('permission:fee_payments.view');

    // ── Club Follow-up Configuration ────────────────────────
    Route::resource('club-follow-up-configs', ClubFollowUpConfigController::class)->middleware('permission:settings.view');
});

// ── Student Portal ──────────────────────────────────────────
Route::prefix('student')->name('student.')->middleware(['auth', 'student'])->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/marks', [StudentDashboardController::class, 'marks'])->name('marks');
    Route::get('/progress', [StudentDashboardController::class, 'progress'])->name('progress');
    Route::get('/fees', [StudentDashboardController::class, 'fees'])->name('fees');
    Route::get('/profile', [StudentDashboardController::class, 'profile'])->name('profile');

    // Student Chat
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('chat', [ChatController::class, 'storeConversation'])->name('chat.store');
    Route::get('chat/{id}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('chat/{id}/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::delete('chat/{id}', [ChatController::class, 'destroyConversation'])->name('chat.destroy');
    Route::get('chat/{id}/messages', [ChatController::class, 'getMessages'])->name('chat.messages');

    // Self-Assessment
    Route::get('assessment', [StudentAssessmentController::class, 'index'])->name('assessment.index');
    Route::get('assessment/subject/{subjectId}', [StudentAssessmentController::class, 'subjectQuestions'])->name('assessment.subject');
    Route::get('assessment/question/{questionId}', [StudentAssessmentController::class, 'showQuestion'])->name('assessment.show');
    Route::post('assessment/question/{questionId}', [StudentAssessmentController::class, 'submitAnswer'])->name('assessment.submit');
    Route::get('assessment/question/{questionId}/retake', [StudentAssessmentController::class, 'retakeQuestion'])->name('assessment.retake');
    Route::get('assessment/progress', [StudentAssessmentController::class, 'progress'])->name('assessment.progress');
});

// ── Parent Portal ───────────────────────────────────────────
Route::prefix('parent')->name('parent.')->middleware(['auth', 'parent'])->group(function () {
    Route::get('/dashboard', [ParentDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/child/{studentId}/marks', [ParentDashboardController::class, 'childMarks'])->name('child.marks');
    Route::get('/child/{studentId}/progress', [ParentDashboardController::class, 'childProgress'])->name('child.progress');
    Route::get('/child/{studentId}/fees', [ParentDashboardController::class, 'childFees'])->name('child.fees');
    Route::get('/child/{studentId}/profile', [ParentDashboardController::class, 'childProfile'])->name('child.profile');

    // Parent Chat
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('chat', [ChatController::class, 'storeConversation'])->name('chat.store');
    Route::get('chat/{id}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('chat/{id}/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::delete('chat/{id}', [ChatController::class, 'destroyConversation'])->name('chat.destroy');
    Route::get('chat/{id}/messages', [ChatController::class, 'getMessages'])->name('chat.messages');
});
