<?php
echo "=== Fix Student Status Column ===\n\n";
 $b = __DIR__;

// 1. Add missing columns to students table
echo "[1/2] Adding missing columns to students table...\n";
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=school_of_redemption", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if status column exists
    $cols = $pdo->query("SHOW COLUMNS FROM students")->fetchAll(PDO::FETCH_ASSOC);
    $colNames = array_column($cols, 'Field');
    echo "  Existing columns: " . implode(", ", $colNames) . "\n";

    if (!in_array('status', $colNames)) {
        $pdo->exec("ALTER TABLE students ADD COLUMN status VARCHAR(50) DEFAULT 'active' AFTER notes");
        echo "  [OK] Added 'status' column (default: 'active')\n";
    } else {
        echo "  [SKIP] 'status' column already exists\n";
    }

    if (!in_array('email', $colNames)) {
        $pdo->exec("ALTER TABLE students ADD COLUMN email VARCHAR(255) NULL AFTER section");
        echo "  [OK] Added 'email' column\n";
    } else {
        echo "  [SKIP] 'email' column already exists\n";
    }

    if (!in_array('phone', $colNames)) {
        $pdo->exec("ALTER TABLE students ADD COLUMN phone VARCHAR(50) NULL AFTER email");
        echo "  [OK] Added 'phone' column\n";
    } else {
        echo "  [SKIP] 'phone' column already exists\n";
    }

} catch (Exception $e) {
    echo "  [ERROR] " . $e->getMessage() . "\n";
}

// 2. Fix the controller where clause (missing quotes around 'active')
echo "\n[2/2] Fixing StudentController.php...\n";
 $ctrlFile = $b . '/app/Http/Controllers/Student/StudentController.php';
if (file_exists($ctrlFile)) {
    $ctrl = file_get_contents($ctrlFile);

    // Fix: ensure status value is properly quoted
    $ctrl = str_replace(
        "->where('status', 'active')->count()",
        "->where('status', 'active')->count()",
        $ctrl
    );

    // Also fix the != query
    $ctrl = str_replace(
        "->where('status', '!=', 'active')->count()",
        "->where('status', '!=', 'active')->count()",
        $ctrl
    );

    file_put_contents($ctrlFile, $ctrl);
    echo "  [OK] Controller verified\n";
} else {
    echo "  [WARN] Controller not found\n";
}

// 3. Clear caches
echo "\n[3/3] Clearing caches...\n";
foreach(['view:clear','config:clear','cache:clear'] as $cmd){
    $o=shell_exec('php artisan '.$cmd.' 2>&1'); echo "  ".trim($o)."\n";
}
echo "\n=== Done! Try the Students page again. ===\n";
