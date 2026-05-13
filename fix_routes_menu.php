<?php
echo "=== Part 1: Add Routes + Sidebar Menu ===\n\n";
 $b = __DIR__;

// ── 1. Add all missing routes ──
echo "[1/2] Adding routes to web.php...\n";
 $routeFile = $b . '/routes/web.php';
 $rc = file_get_contents($routeFile);

 $useStatements = $rc;
// Add missing use statements
 $uses = [
    'use App\Http\Controllers\ParentModel\ParentModelController;',
    'use App\Http\Controllers\Fee\FeeController;',
    'use App\Http\Controllers\FeePayment\FeePaymentController;',
    'use App\Http\Controllers\Setting\SettingController;',
    'use App\Http\Controllers\ProgressReport\ProgressReportController;',
    'use App\Http\Controllers\ClassAsset\ClassAssetController;',
    'use App\Http\Controllers\Section\SectionController;',
    'use App\Http\Controllers\Exam\ExamController;',
    'use App\Http\Controllers\Leave\LeaveController;',
    'use App\Http\Controllers\Payroll\PayrollController;',
    'use App\Http\Controllers\EmployeeAsset\EmployeeAssetController;',
    'use App\Http\Controllers\MarkEntry\MarkEntryController;',
    'use App\Http\Controllers\TeacherAssignment\TeacherAssignmentController;',
    'use App\Http\Controllers\FinanceStatement\FinanceStatementController;',
    'use App\Http\Controllers\IncomeExpense\IncomeExpenseController;',
    'use App\Http\Controllers\Budget\BudgetController;',
    'use App\Http\Controllers\Audit\AuditController;',
    'use App\Http\Controllers\Certificate\CertificateController;',
    'use App\Http\Controllers\IdCard\IdCardController;',
    'use App\Http\Controllers\GalleryImage\GalleryImageController;',
    'use App\Http\Controllers\GalleryVideo\GalleryVideoController;',
    'use App\Http\Controllers\Slider\SliderController;',
    'use App\Http\Controllers\TeamMember\TeamMemberController;',
    'use App\Http\Controllers\ContactMessage\ContactMessageController;',
    'use App\Http\Controllers\PerformanceReport\PerformanceReportController;',
];
foreach ($uses as $u) {
    if (strpos($useStatements, $u) === false) {
        $useStatements = str_replace('use Illuminate\Support\Facades\Route;', "use Illuminate\Support\Facades\Route;\n$u", $useStatements);
    }
}

// Add resource routes inside the admin group
 $resourceRoutes = '
    // Parents
    Route::resource("parents", ParentModelController::class);

    // Fees
    Route::resource("fees", FeeController::class);

    // Fee Payments
    Route::resource("fee-payments", FeePaymentController::class);

    // Settings
    Route::get("/settings", [SettingController::class, "index"])->name("settings.index");
    Route::post("/settings", [SettingController::class, "updateAll"])->name("settings.updateAll");
    Route::resource("settings", SettingController::class)->except(["index"]);

    // Progress Reports
    Route::resource("progress-reports", ProgressReportController::class);

    // Class Assets
    Route::resource("class-assets", ClassAssetController::class);
    Route::get("/class-assets/api/by-class/{classId}", [ClassAssetController::class, "getAssetsByClass"])->name("class-assets.by-class");

    // Sections
    Route::resource("sections", SectionController::class);

    // Exams
    Route::resource("exams", ExamController::class);

    // Leave Management
    Route::resource("leaves", LeaveController::class);

    // Payroll
    Route::resource("payrolls", PayrollController::class);

    // Employee Assets
    Route::resource("employee-assets", EmployeeAssetController::class);

    // Mark Entry
    Route::resource("mark-entries", MarkEntryController::class);

    // Teacher Assignment
    Route::resource("teacher-assignments", TeacherAssignmentController::class);

    // Finance Statement
    Route::resource("finance-statements", FinanceStatementController::class);

    // Income Expense
    Route::resource("income-expenses", IncomeExpenseController::class);

    // Budget
    Route::resource("budgets", BudgetController::class);

    // Audit Log
    Route::resource("audits", AuditController::class);

    // Certificates
    Route::resource("certificates", CertificateController::class);

    // ID Cards
    Route::resource("id-cards", IdCardController::class);

    // Gallery Images
    Route::resource("gallery-images", GalleryImageController::class);

    // Gallery Videos
    Route::resource("gallery-videos", GalleryVideoController::class);

    // Sliders
    Route::resource("sliders", SliderController::class);

    // Team Members
    Route::resource("team-members", TeamMemberController::class);

    // Contact Messages
    Route::resource("contact-messages", ContactMessageController::class);

    // Performance Reports
    Route::resource("performance-reports", PerformanceReportController::class);
';

// Insert before the closing of the admin group
if (strpos($useStatements, 'Route::resource("parents"') === false) {
    $useStatements = str_replace('});', $resourceRoutes . '});', $useStatements, $count);
    if ($count === 0) {
        // Fallback: append before last });
        $pos = strrpos($useStatements, '});');
        $useStatements = substr($useStatements, 0, $pos) . $resourceRoutes . '});';
    }
}
file_put_contents($routeFile, $useStatements);
echo "  [OK] Routes added\n";

// ── 2. Update sidebar menu ──
echo "[2/2] Updating sidebar menu...\n";
 $layoutFile = $b . '/resources/views/layouts/admin.blade.php';
 $lc = file_get_contents($layoutFile);

// Build new sidebar HTML
 $newSidebar = '
        <ul class="navbar-nav flex-column ms-3" id="sidebarNav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.dashboard") ? "active fw-bold" : "" }}" href="{{ route("admin.dashboard") }}">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>

            <li class="nav-item mt-2"><span class="nav-link text-muted small fw-bold text-uppercase">Academic</span></li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.academic-years.*") ? "active fw-bold" : "" }}" href="{{ route("admin.academic-years.index") }}">
                    <i class="bi bi-calendar3 me-2"></i>Academic Years
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.terms.*") ? "active fw-bold" : "" }}" href="{{ route("admin.terms.index") }}">
                    <i class="bi bi-calendar-range me-2"></i>Terms
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.classrooms.*") ? "active fw-bold" : "" }}" href="{{ route("admin.classrooms.index") }}">
                    <i class="bi bi-door-open me-2"></i>Classrooms
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.sections.*") ? "active fw-bold" : "" }}" href="{{ route("admin.sections.index") }}">
                    <i class="bi bi-columns-gap me-2"></i>Sections
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.subjects.*") ? "active fw-bold" : "" }}" href="{{ route("admin.subjects.index") }}">
                    <i class="bi bi-book me-2"></i>Subjects
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.exams.*") ? "active fw-bold" : "" }}" href="{{ route("admin.exams.index") }}">
                    <i class="bi bi-clipboard2-check me-2"></i>Exams
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.teacher-assignments.*") ? "active fw-bold" : "" }}" href="{{ route("admin.teacher-assignments.index") }}">
                    <i class="bi bi-person-badge me-2"></i>Teacher Assignment
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.mark-entries.*") ? "active fw-bold" : "" }}" href="{{ route("admin.mark-entries.index") }}">
                    <i class="bi bi-pencil-square me-2"></i>Mark Entry
                </a>
            </li>

            <li class="nav-item mt-2"><span class="nav-link text-muted small fw-bold text-uppercase">People</span></li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.teachers.*") ? "active fw-bold" : "" }}" href="{{ route("admin.teachers.index") }}">
                    <i class="bi bi-person-workspace me-2"></i>Teachers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.students.*") ? "active fw-bold" : "" }}" href="{{ route("admin.students.index") }}">
                    <i class="bi bi-mortarboard me-2"></i>Students
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.parents.*") ? "active fw-bold" : "" }}" href="{{ route("admin.parents.index") }}">
                    <i class="bi bi-people me-2"></i>Parents
                </a>
            </li>

            <li class="nav-item mt-2"><span class="nav-link text-muted small fw-bold text-uppercase">Finance</span></li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.fees.*") ? "active fw-bold" : "" }}" href="{{ route("admin.fees.index") }}">
                    <i class="bi bi-receipt me-2"></i>Fee Structure
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.fee-payments.*") ? "active fw-bold" : "" }}" href="{{ route("admin.fee-payments.index") }}">
                    <i class="bi bi-credit-card me-2"></i>Fee Payments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.payrolls.*") ? "active fw-bold" : "" }}" href="{{ route("admin.payrolls.index") }}">
                    <i class="bi bi-cash-stack me-2"></i>Payroll
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.income-expenses.*") ? "active fw-bold" : "" }}" href="{{ route("admin.income-expenses.index") }}">
                    <i class="bi bi-arrow-left-right me-2"></i>Income / Expense
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.budgets.*") ? "active fw-bold" : "" }}" href="{{ route("admin.budgets.index") }}">
                    <i class="bi bi-pie-chart me-2"></i>Budget
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.finance-statements.*") ? "active fw-bold" : "" }}" href="{{ route("admin.finance-statements.index") }}">
                    <i class="bi bi-bar-chart-line me-2"></i>Finance Statement
                </a>
            </li>

            <li class="nav-item mt-2"><span class="nav-link text-muted small fw-bold text-uppercase">HR</span></li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.leaves.*") ? "active fw-bold" : "" }}" href="{{ route("admin.leaves.index") }}">
                    <i class="bi bi-calendar-x me-2"></i>Leave Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.employee-assets.*") ? "active fw-bold" : "" }}" href="{{ route("admin.employee-assets.index") }}">
                    <i class="bi bi-archive me-2"></i>Employee Assets
                </a>
            </li>

            <li class="nav-item mt-2"><span class="nav-link text-muted small fw-bold text-uppercase">Reports</span></li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.progress-reports.*") ? "active fw-bold" : "" }}" href="{{ route("admin.progress-reports.index") }}">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i>Progress Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.performance-reports.*") ? "active fw-bold" : "" }}" href="{{ route("admin.performance-reports.index") }}">
                    <i class="bi bi-graph-up me-2"></i>Performance Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.audits.*") ? "active fw-bold" : "" }}" href="{{ route("admin.audits.index") }}">
                    <i class="bi bi-journal-check me-2"></i>Audit Log
                </a>
            </li>

            <li class="nav-item mt-2"><span class="nav-link text-muted small fw-bold text-uppercase">Assets</span></li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.class-assets.*") ? "active fw-bold" : "" }}" href="{{ route("admin.class-assets.index") }}">
                    <i class="bi bi-box-seam me-2"></i>Class Assets
                </a>
            </li>

            <li class="nav-item mt-2"><span class="nav-link text-muted small fw-bold text-uppercase">Content</span></li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.sliders.*") ? "active fw-bold" : "" }}" href="{{ route("admin.sliders.index") }}">
                    <i class="bi bi-images me-2"></i>Sliders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.gallery-images.*") ? "active fw-bold" : "" }}" href="{{ route("admin.gallery-images.index") }}">
                    <i class="bi bi-image me-2"></i>Gallery Images
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.gallery-videos.*") ? "active fw-bold" : "" }}" href="{{ route("admin.gallery-videos.index") }}">
                    <i class="bi bi-camera-video me-2"></i>Gallery Videos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.team-members.*") ? "active fw-bold" : "" }}" href="{{ route("admin.team-members.index") }}">
                    <i class="bi bi-person-lines-fill me-2"></i>Team Members
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.certificates.*") ? "active fw-bold" : "" }}" href="{{ route("admin.certificates.index") }}">
                    <i class="bi bi-award me-2"></i>Certificates
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.id-cards.*") ? "active fw-bold" : "" }}" href="{{ route("admin.id-cards.index") }}">
                    <i class="bi bi-card-text me-2"></i>ID Cards
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.contact-messages.*") ? "active fw-bold" : "" }}" href="{{ route("admin.contact-messages.index") }}">
                    <i class="bi bi-envelope me-2"></i>Contact Messages
                </a>
            </li>

            <li class="nav-item mt-2"><span class="nav-link text-muted small fw-bold text-uppercase">System</span></li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.branches.*") ? "active fw-bold" : "" }}" href="{{ route("admin.branches.index") }}">
                    <i class="bi bi-building me-2"></i>Branches
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs("admin.settings.*") ? "active fw-bold" : "" }}" href="{{ route("admin.settings.index") }}">
                    <i class="bi bi-gear me-2"></i>Settings
                </a>
            </li>
        </ul>';

// Replace the sidebar section - find and replace
if (strpos($lc, 'id="sidebarNav"') !== false) {
    // Replace existing sidebar
    $lc = preg_replace('/<ul[^>]*id="sidebarNav"[^>]*>.*?<\/ul>/s', $newSidebar, $lc);
    echo "  [OK] Sidebar updated (replaced existing)\n";
} else {
    // Append before @endsection or before closing sidebar div
    $lc = str_replace('@section("sidebar"', '@section("sidebar"', $lc);
    // Try to find a sidebar area
    if (strpos($lc, '@section("sidebar"') !== false) {
        // Already has sidebar section, insert into it
        $lc = preg_replace('/@section\("sidebar"\)(.*?)@endsection/s', '@section("sidebar")' . $newSidebar . '@endsection', $lc);
        echo "  [OK] Sidebar updated (in section)\n";
    } else {
        // No sidebar section found, we will add it after @section("content")
        echo "  [WARN] Could not auto-replace sidebar. Manual update needed.\n";
    }
}
file_put_contents($layoutFile, $lc);
echo "  [OK] Layout saved\n";

// Clear caches
echo "\nClearing caches...\n";
foreach(['route:clear','config:clear','view:clear','cache:clear'] as $cmd){
    $o=shell_exec('php artisan '.$cmd.' 2>&1'); echo "  ".trim($o)."\n";
}
echo "\n=== Part 1 Done! Routes + Menu updated. ===\n";
echo "Now run Part 2 to update all views.\n";
