<?php
 $base = getcwd();
 $changes = 0;

// ============================================================
// FIX 1: Add teacherAssignments() relationship to Subject model
// ============================================================
echo "===== FIX 1: Subject::teacherAssignments() missing =====\n\n";

 $subjectModel = $base . '/app/Models/Subject.php';
if (file_exists($subjectModel)) {
    $model = file_get_contents($subjectModel);
    
    echo "Current Subject model:\n";
    echo $model . "\n";
    
    // Check if teacherAssignments relationship already exists
    if (strpos($model, 'teacherAssignments') === false) {
        // Add the relationship before the closing }
        $relationship = "\n    public function teacherAssignments()\n    {\n        return \$this->hasMany(TeacherAssignment::class);\n    }\n";
        
        // Find the last } and insert before it
        $lastBrace = strrpos($model, '}');
        $model = substr($model, 0, $lastBrace) . $relationship . substr($model, $lastBrace);
        
        // Make sure TeacherAssignment is imported
        if (strpos($model, 'use App\\Models\\TeacherAssignment') === false) {
            // Add after namespace line
            $model = preg_replace(
                '/(use [^\n]+;\n)/',
                "$1use App\\Models\\TeacherAssignment;\n",
                $model,
                1
            );
        }
        
        file_put_contents($subjectModel, $model);
        echo "OK: Added teacherAssignments() relationship to Subject model\n";
        $changes++;
    } else {
        echo "OK: teacherAssignments() already exists\n";
    }
} else {
    echo "Subject model NOT FOUND\n";
}

// ============================================================
// FIX 2: Check ID Card routes and fix if needed
// ============================================================
echo "\n===== FIX 2: ID Card routes =====\n\n";

 $routePath = $base . '/routes/web.php';
 $routes = file_get_contents($routePath);

echo "Current ID card routes:\n";
 $lines = explode("\n", $routes);
foreach ($lines as $i => $line) {
    if (stripos($line, 'id-card') !== false) {
        echo "  " . ($i+1) . ": " . trim($line) . "\n";
    }
}

// Check what the sidebar references
 $layoutPath = $base . '/resources/views/layouts/admin.blade.php';
 $layout = file_get_contents($layoutPath);

echo "\nCurrent sidebar ID card reference:\n";
 $layoutLines = explode("\n", $layout);
foreach ($layoutLines as $i => $line) {
    if (stripos($line, 'id-card') !== false) {
        echo "  " . ($i+1) . ": " . trim($line) . "\n";
    }
}

// Verify the id-card-generate route name matches
echo "\nChecking route names...\n";
exec('php artisan route:list --path=id-card 2>&1', $routeOutput);
echo implode("\n", $routeOutput) . "\n";

// ============================================================
// FIX 3: Verify the sidebar link matches actual route name
// ============================================================
echo "\n===== FIX 3: Verify sidebar ID card link =====\n\n";

// The routes show:
// admin.id-card-generate.index
// admin.id-cards.index (resource)
// 
// The sidebar should link to id-card-generate for the generate page

// Check if the sidebar has the correct route name
if (strpos($layout, "admin.id-card-generate.index") !== false) {
    echo "OK: Sidebar already links to admin.id-card-generate.index\n";
} elseif (strpos($layout, "admin.id-cards.index") !== false) {
    echo "Sidebar links to admin.id-cards.index (resource route)\n";
} else {
    echo "WARNING: No ID card link found in sidebar!\n";
}

// ============================================================
// FIX 4: Check IdCardGenerateController exists and works
// ============================================================
echo "\n===== FIX 4: IdCardGenerateController =====\n\n";

 $idCtrlPath = $base . '/app/Http/Controllers/IdCard/IdCardGenerateController.php';
if (file_exists($idCtrlPath)) {
    echo "OK: Controller exists\n";
    $idCtrl = file_get_contents($idCtrlPath);
    
    // Check index method
    if (strpos($idCtrl, 'function index') !== false) {
        echo "OK: index method exists\n";
    } else {
        echo "MISSING: index method\n";
    }
    
    // Check generate method
    if (strpos($idCtrl, 'function generate') !== false) {
        echo "OK: generate method exists\n";
    } else {
        echo "MISSING: generate method\n";
    }
    
    // Check getSections method
    if (strpos($idCtrl, 'function getSections') !== false) {
        echo "OK: getSections method exists\n";
    } else {
        echo "MISSING: getSections method\n";
    }
    
    // Check getStudents method
    if (strpos($idCtrl, 'function getStudents') !== false) {
        echo "OK: getStudents method exists\n";
    } else {
        echo "MISSING: getStudents method\n";
    }
} else {
    echo "MISSING: IdCardGenerateController not found at $idCtrlPath\n";
}

// Check the view
 $idViewPath = $base . '/resources/views/admin/id-card-generate/index.blade.php';
if (file_exists($idViewPath)) {
    echo "OK: id-card-generate/index.blade.php exists\n";
} else {
    echo "MISSING: id-card-generate/index.blade.php view\n";
    
    // Check alternate locations
    $altViews = glob($base . '/resources/views/admin/IdCard/*.blade.php');
    $altViews2 = glob($base . '/resources/views/admin/id-card*/**/*.blade.php');
    $allAltViews = array_merge($altViews ?: [], $altViews2 ?: []);
    if (!empty($allAltViews)) {
        echo "  Found views at:\n";
        foreach ($allAltViews as $v) echo "    $v\n";
    }
}

echo "\n===== SUMMARY: $changes changes made =====\n";
echo "Run: php artisan cache:clear && php artisan view:clear\n";
