<?php
 $base = getcwd();
 $changes = [];

// ============================================================
// DIAGNOSTIC: Read current files
// ============================================================

echo "===== DIAGNOSTIC OUTPUT =====\n\n";

// 1. Read StudentController store method
echo "--- StudentController.php (store method area) ---\n";
 $scPath = $base . '/app/Http/Controllers/Student/StudentController.php';
if (file_exists($scPath)) {
    $sc = file_get_contents($scPath);
    // Show lines around store method
    $lines = explode("\n", $sc);
    $inStore = false;
    $braceCount = 0;
    $started = false;
    foreach ($lines as $i => $line) {
        if (strpos($line, 'function store') !== false) {
            $inStore = true;
            $started = false;
        }
        if ($inStore) {
            echo ($i+1) . ": " . $line . "\n";
            if (strpos($line, '{') !== false) $braceCount += substr_count($line, '{');
            if (strpos($line, '}') !== false) $braceCount -= substr_count($line, '}');
            if ($started && $braceCount <= 0) break;
            if ($braceCount > 0) $started = true;
        }
    }
} else {
    echo "FILE NOT FOUND: $scPath\n";
}

// 2. Check student create form for admission_date field
echo "\n--- Student create.blade.php (admission_date field) ---\n";
 $createView = $base . '/resources/views/admin/Student/create.blade.php';
if (file_exists($createView)) {
    $cv = file_get_contents($createView);
    $lines = explode("\n", $cv);
    foreach ($lines as $i => $line) {
        if (stripos($line, 'admission_date') !== false || stripos($line, 'admission') !== false) {
            // Show context
            $start = max(0, $i - 2);
            $end = min(count($lines) - 1, $i + 2);
            for ($j = $start; $j <= $end; $j++) {
                echo ($j+1) . ": " . $lines[$j] . "\n";
            }
            echo "...\n";
        }
    }
} else {
    echo "FILE NOT FOUND\n";
}

// 3. Check routes for subject-assignment
echo "\n--- Routes (subject-assignment related) ---\n";
 $routePath = $base . '/routes/web.php';
if (file_exists($routePath)) {
    $rt = file_get_contents($routePath);
    $lines = explode("\n", $rt);
    foreach ($lines as $i => $line) {
        if (stripos($line, 'subject') !== false) {
            echo ($i+1) . ": " . $line . "\n";
        }
    }
}

// 4. Check SubjectAssignment controller
echo "\n--- SubjectAssignment Controller ---\n";
 $sacPaths = array_merge(
    glob($base . '/app/Http/Controllers/*Subject*'),
    glob($base . '/app/Http/Controllers/*/*Subject*')
);
if (!empty($sacPaths)) {
    foreach ($sacPaths as $p) echo "FOUND: $p\n";
} else {
    echo "NO SubjectAssignment Controller found!\n";
}

// 5. Check SubjectAssignment views
echo "\n--- SubjectAssignment Views ---\n";
 $savPaths = array_merge(
    glob($base . '/resources/views/admin/*Subject*', GLOB_ONLYDIR),
    glob($base . '/resources/views/admin/*subject*', GLOB_ONLYDIR)
);
if (!empty($savPaths)) {
    foreach ($savPaths as $p) echo "FOUND: $p\n";
} else {
    echo "NO SubjectAssignment views found!\n";
}

// 6. Read sidebar menu from admin layout
echo "\n--- Admin Layout Sidebar Menu ---\n";
 $layoutPath = $base . '/resources/views/layouts/admin.blade.php';
if (file_exists($layoutPath)) {
    $layout = file_get_contents($layoutPath);
    $lines = explode("\n", $layout);
    $inSidebar = false;
    foreach ($lines as $i => $line) {
        if (stripos($line, 'sidebar') !== false || stripos($line, 'side-menu') !== false || stripos($line, 'nav-sidebar') !== false || stripos($line, 'menu-open') !== false) {
            $inSidebar = true;
        }
        if ($inSidebar || stripos($line, 'nav-link') !== false || stripos($line, 'menu-header') !== false || stripos($line, 'nav-item') !== false || stripos($line, 'collapse') !== false) {
            echo ($i+1) . ": " . $line . "\n";
        }
        if ($inSidebar && (stripos($line, '</aside>') !== false || stripos($line, '</nav>') !== false || stripos($line, 'content-wrapper') !== false)) {
            $inSidebar = false;
        }
    }
}

// 7. Read current admin.css (last 100 lines to see what's already there)
echo "\n--- admin.css (last 100 lines) ---\n";
 $cssPath = $base . '/public/css/admin.css';
if (file_exists($cssPath)) {
    $css = file_get_contents($cssPath);
    $cssLines = explode("\n", $css);
    $start = max(0, count($cssLines) - 100);
    for ($i = $start; $i < count($cssLines); $i++) {
        echo ($i+1) . ": " . $cssLines[$i] . "\n";
    }
}

echo "\n===== END DIAGNOSTIC =====\n";

// ============================================================
// FIX 1: Student admission_date cannot be null
// ============================================================
echo "\n===== FIX 1: admission_date =====\n";

if (file_exists($scPath)) {
    $sc = file_get_contents($scPath);
    
    // Add default admission_date before Student::create
    if (strpos($sc, "empty(\$validated['admission_date'])") === false) {
        $search = "Student::create(\$validated);";
        $replace = "// Set default admission_date if not provided\n        if (empty(\$validated['admission_date'])) {\n            \$validated['admission_date'] = now()->toDateString();\n        }\n\n        Student::create(\$validated);";
        
        if (strpos($sc, $search) !== false) {
            $sc = str_replace($search, $replace, $sc, $count);
            if ($count > 0) {
                file_put_contents($scPath, $sc);
                $changes[] = "FIXED: admission_date default added in StudentController";
                echo "  ✓ Added default admission_date\n";
            }
        }
    } else {
        echo "  ✓ Already fixed\n";
    }
}

// Also check update method
if (file_exists($scPath)) {
    $sc = file_get_contents($scPath);
    if (strpos($sc, "function update") !== false) {
        // Find update method and add same logic before update
        if (strpos($sc, "empty(\$validated['admission_date'])") === false || 
            substr_count($sc, "empty(\$validated['admission_date'])") < 2) {
            // Find the update method's Student::update or $student->update
            $search2 = "\$student->update(\$validated);";
            $replace2 = "if (empty(\$validated['admission_date'])) {\n            \$validated['admission_date'] = now()->toDateString();\n        }\n\n        \$student->update(\$validated);";
            if (strpos($sc, $search2) !== false) {
                $sc = str_replace($search2, $replace2, $sc, $count);
                if ($count > 0) {
                    file_put_contents($scPath, $sc);
                    $changes[] = "FIXED: admission_date default added in update method too";
                    echo "  ✓ Added default admission_date in update method\n";
                }
            }
        }
    }
}

echo "\n===== DONE =====\n";
echo "Changes:\n";
foreach ($changes as $c) echo "  ✓ $c\n";
echo "\nPlease paste the FULL output above so I can provide the remaining fixes!\n";

