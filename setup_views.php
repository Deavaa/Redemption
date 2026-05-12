<?php
echo "=== Creating Views ===\n\n";
require __DIR__ . '/vendor/autoload.php';
 $app = require_once __DIR__ . '/bootstrap/app.php';
 $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
 $kernel->bootstrap();

 $v = resource_path('views');
foreach (["$v/layouts","$v/auth","$v/admin/AcademicYear","$v/admin/Term","$v/admin/Classroom","$v/admin/Section","$v/admin/Subject","$v/admin/Student","$v/admin/ParentModel","$v/admin/TeacherAssignment","$v/admin/Exam","$v/admin/MarkEntry","$v/admin/Certificate","$v/admin/IdCard","$v/admin/ProgressReport","$v/admin/PerformanceReport","$v/admin/ClassAsset","$v/admin/EmployeeAsset","$v/admin/Fee","$v/admin/FeePayment","$v/admin/Leave","$v/admin/Payroll","$v/admin/Budget","$v/admin/IncomeExpense","$v/admin/FinanceStatement","$v/admin/Audit","$v/admin/Branch","$v/admin/TeamMember","$v/admin/GalleryImage","$v/admin/GalleryVideo","$v/admin/Slider","$v/admin/Setting","$v/admin/ContactMessage"] as $d) {
    if (!is_dir($d)) mkdir($d, 0755, true);
}

function vw($path, $content) {
    global $v;
    file_put_contents("$v/$path", $content);
    echo "  [OK] $path\n";
}

vw('layouts/app.blade.php', file_get_contents('https://raw.githubusercontent.com/z-dev-lab/sor-views/main/layouts/app.blade.php') ?: 'FALLBACK');
