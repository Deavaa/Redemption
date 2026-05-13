<?php

echo "=== Creating Routes ===\n\n";

require __DIR__ . '/vendor/autoload.php';
 $app = require_once __DIR__ . '/bootstrap/app.php';
 $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
 $kernel->bootstrap();

// Backup original routes
if (file_exists(base_path('routes/web.php'))) {
    copy(base_path('routes/web.php'), base_path('routes/web.php.bak'));
}

 $routes = <<<'ROUTE'
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AcademicYear\AcademicYearController;
use App\Http\Controllers\Term\TermController;
use App\Http\Controllers\Classroom\ClassroomController;
use App\Http\Controllers\Section\SectionController;
use App\Http\Controllers\Subject\SubjectController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\ParentModel\ParentModelController;
use App\Http\Controllers\TeacherAssignment\TeacherAssignmentController;
use App\Http\Controllers\Exam\ExamController;
use App\Http\Controllers\MarkEntry\MarkEntryController;
use App\Http\Controllers\Certificate\CertificateController;
use App\Http\Controllers\IdCard\IdCardController;
use App\Http\Controllers\ProgressReport\ProgressReportController;
use App\Http\Controllers\PerformanceReport\PerformanceReportController;
use App\Http\Controllers\ClassAsset\ClassAssetController;
use App\Http\Controllers\EmployeeAsset\EmployeeAssetController;
use App\Http\Controllers\Fee\FeeController;
use App\Http\Controllers\FeePayment\FeePaymentController;
use App\Http\Controllers\Leave\LeaveController;
use App\Http\Controllers\Payroll\PayrollController;
use App\Http\Controllers\Budget\BudgetController;
use App\Http\Controllers\IncomeExpense\IncomeExpenseController;
use App\Http\Controllers\FinanceStatement\FinanceStatementController;
use App\Http\Controllers\Audit\AuditController;
use App\Http\Controllers\Branch\BranchController;
use App\Http\Controllers\TeamMember\TeamMemberController;
use App\Http\Controllers\GalleryImage\GalleryImageController;
use App\Http\Controllers\GalleryVideo\GalleryVideoController;
use App\Http\Controllers\Slider\SliderController;
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\ContactMessage\ContactMessageController;

// ==========================================
// PUBLIC ROUTES
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/gallery', [HomeController::class, 'gallery'])->name('gallery');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');
Route::get('/team', [HomeController::class, 'team'])->name('team');

// ==========================================
// AUTH ROUTES
// ==========================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================
// ADMIN ROUTES (Protected)
// ==========================================
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Academic
    Route::resource('academic-years', AcademicYearController::class);
    Route::resource('terms', TermController::class);
    Route::resource('classes', ClassroomController::class);
    Route::resource('sections', SectionController::class);
    Route::resource('subjects', SubjectController::class);

    // People
    Route::resource('students', StudentController::class);
    Route::resource('parents', ParentModelController::class);
    Route::resource('teacher-assignments', TeacherAssignmentController::class);
    Route::resource('branches', BranchController::class);
    Route::resource('team-members', TeamMemberController::class);

    // Academics
    Route::resource('exams', ExamController::class);
    Route::resource('mark-entries', MarkEntryController::class);
    Route::resource('progress-reports', ProgressReportController::class);
    Route::resource('performance-reports', PerformanceReportController::class);
    Route::resource('certificates', CertificateController::class);
    Route::resource('id-cards', IdCardController::class);

    // Assets
    Route::resource('class-assets', ClassAssetController::class);
    Route::resource('employee-assets', EmployeeAssetController::class);

    // Finance
    Route::resource('fees', FeeController::class);
    Route::resource('fee-payments', FeePaymentController::class);
    Route::resource('budgets', BudgetController::class);
    Route::resource('income-expenses', IncomeExpenseController::class);
    Route::resource('finance-statements', FinanceStatementController::class);

    // HR
    Route::resource('leaves', LeaveController::class);
    Route::resource('payrolls', PayrollController::class);

    // Audit
    Route::resource('audits', AuditController::class);

    // CMS
    Route::resource('sliders', SliderController::class);
    Route::resource('gallery-images', GalleryImageController::class);
    Route::resource('gallery-videos', GalleryVideoController::class);
    Route::resource('settings', SettingController::class);
    Route::resource('contact-messages', ContactMessageController::class);
});
ROUTE;

file_put_contents(base_path('routes/web.php'), $routes);
echo "  [OK] routes/web.php\n";

// Also create api.php
 $api = <<<'API'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'app' => 'School of Redemption ERP']);
});
API;

file_put_contents(base_path('routes/api.php'), $api);
echo "  [OK] routes/api.php\n";

echo "\n=== DONE! Routes created ===\n";
