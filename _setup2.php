<?php
 $base = __DIR__;
function wf($p,$c){$d=dirname($p);if(!is_dir($d))mkdir($d,0755,true);file_put_contents($p,$c);echo "  OK: $p\n";}

// Delete old broken teacher-assignments views
foreach(glob("$base/resources/views/admin/teacher-assignments/*.blade.php") as $f) {unlink($f);echo "  Deleted old: ".basename($f)."\n";}

// ===== CONTROLLERS =====
echo "[1] Controllers...\n";
wf("$base/app/Http/Controllers/Admin/MarkEntryController.php", file_get_contents("/home/z/my-project/download/school-of-redemption/app/Http/Controllers/Admin/MarkEntryController.php"));
wf("$base/app/Http/Controllers/Admin/ExamController.php", file_get_contents("/home/z/my-project/download/school-of-redemption/app/Http/Controllers/Admin/ExamController.php"));
wf("$base/app/Http/Controllers/Admin/SubjectController.php", file_get_contents("/home/z/my-project/download/school-of-redemption/app/Http/Controllers/Admin/SubjectController.php"));
wf("$base/app/Http/Controllers/Admin/SubjectAssignmentController.php", file_get_contents("/home/z/my-project/download/school-of-redemption/app/Http/Controllers/Admin/SubjectAssignmentController.php"));
wf("$base/app/Http/Controllers/Admin/StaffController.php", file_get_contents("/home/z/my-project/download/school-of-redemption/app/Http/Controllers/Admin/StaffController.php"));

// ===== VIEWS =====
echo "[2] Views...\n";
 $views = [
    "resources/views/admin/mark-entries/index.blade.php",
    "resources/views/admin/exams/index.blade.php",
    "resources/views/admin/exams/create.blade.php",
    "resources/views/admin/exams/edit.blade.php",
    "resources/views/admin/subjects/index.blade.php",
    "resources/views/admin/subjects/create.blade.php",
    "resources/views/admin/subjects/edit.blade.php",
    "resources/views/admin/subject-assignments/index.blade.php",
    "resources/views/admin/subject-assignments/create.blade.php",
    "resources/views/admin/subject-assignments/edit.blade.php",
    "resources/views/admin/staff/index.blade.php",
    "resources/views/admin/staff/create.blade.php",
    "resources/views/admin/staff/edit.blade.php",
];
foreach ($views as $v) {
    $src = "/home/z/my-project/download/school-of-redemption/$v";
    if (file_exists($src)) {
        wf("$base/$v", file_get_contents($src));
    } else {
        echo "  SKIP: $v (not found on server)\n";
    }
}

// ===== ROUTES =====
echo "[3] Routes...\n";
wf("$base/routes/web.php", file_get_contents("/home/z/my-project/download/school-of-redemption/routes/web.php"));

// ===== LAYOUT =====
echo "[4] Layout...\n";
wf("$base/resources/views/layouts/admin.blade.php", file_get_contents("/home/z/my-project/download/school-of-redemption/resources/views/layouts/admin.blade.php"));

echo "\n[DONE] All files written. Now clearing cache...\n";
passthru("php artisan route:clear 2>&1");
passthru("php artisan config:clear 2>&1");
passthru("php artisan view:clear 2>&1");

echo "\n=== SETUP COMPLETE ===\n";
echo "Test these pages:\n";
echo "  http://localhost/school-of-redemption/public/admin/mark-entries\n";
echo "  http://localhost/school-of-redemption/public/admin/staff\n";
echo "  http://localhost/school-of-redemption/public/admin/subject-assignments\n";
echo "  http://localhost/school-of-redemption/public/admin/exams\n";
