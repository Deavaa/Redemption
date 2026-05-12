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
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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

    Route::resource('students', StudentController::class);
    Route::get('students/roll-number-preview', [StudentController::class, 'getRollNumberPreview'])->name('students.roll-number-preview');
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

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/update-all', [SettingController::class, 'update'])->name('settings.updateAll');
});