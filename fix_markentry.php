<?php
 $base = getcwd();

echo "===== DIAGNOSING MARK ENTRY =====\n\n";

// 1. Read MarkEntryController
 $ctrlPath = $base . '/app/Http/Controllers/MarkEntry/MarkEntryController.php';
if (!file_exists($ctrlPath)) {
    $ctrlPath = $base . '/app/Http/Controllers/MarkEntryController.php';
}
if (!file_exists($ctrlPath)) {
    $altPaths = array_merge(
        glob($base . '/app/Http/Controllers/*/*MarkEntry*'),
        glob($base . '/app/Http/Controllers/*MarkEntry*')
    );
    if (!empty($altPaths)) $ctrlPath = $altPaths[0];
}

if (file_exists($ctrlPath)) {
    echo "Controller: $ctrlPath\n";
    $ctrl = file_get_contents($ctrlPath);
    $lines = explode("\n", $ctrl);
    foreach ($lines as $i => $line) {
        // Show lines mentioning student, user, name
        if (stripos($line, 'student') !== false || stripos($line, 'user') !== false || stripos($line, 'name') !== false || stripos($line, 'with(') !== false || stripos($line, 'load') !== false) {
            echo ($i+1) . ": " . $line . "\n";
        }
    }
} else {
    echo "Controller NOT FOUND\n";
}

// 2. Read MarkEntry Model
 $modelPath = $base . '/app/Models/MarkEntry.php';
if (!file_exists($modelPath)) {
    $altModels = array_merge(
        glob($base . '/app/Models/*MarkEntry*'),
        glob($base . '/app/Models/*/*MarkEntry*')
    );
    if (!empty($altModels)) $modelPath = $altModels[0];
}

if (file_exists($modelPath)) {
    echo "\nModel: $modelPath\n";
    $model = file_get_contents($modelPath);
    echo $model . "\n";
} else {
    echo "\nModel NOT FOUND\n";
}

// 3. Read Mark Entry views
echo "\n===== MARK ENTRY VIEWS =====\n";
 $viewDirs = array_merge(
    glob($base . '/resources/views/admin/mark-entries', GLOB_ONLYDIR),
    glob($base . '/resources/views/admin/*Mark*', GLOB_ONLYDIR),
    glob($base . '/resources/views/admin/mark*', GLOB_ONLYDIR)
);
if (!empty($viewDirs)) {
    foreach ($viewDirs as $dir) {
        echo "Directory: $dir\n";
        $files = glob($dir . '/*.blade.php');
        foreach ($files as $f) {
            echo "\n--- " . basename($f) . " ---\n";
            $content = file_get_contents($f);
            $lines = explode("\n", $content);
            foreach ($lines as $i => $line) {
                if (stripos($line, 'student') !== false || stripos($line, 'user') !== false || stripos($line, 'name') !== false || stripos($line, '->name') !== false) {
                    echo ($i+1) . ": " . $line . "\n";
                }
            }
        }
    }
} else {
    echo "No mark entry view directories found\n";
}

// 4. Check Student model relationships
echo "\n===== STUDENT MODEL =====\n";
 $studentModel = $base . '/app/Models/Student.php';
if (file_exists($studentModel)) {
    $sm = file_get_contents($studentModel);
    // Show relationship methods and fillable
    $lines = explode("\n", $sm);
    foreach ($lines as $i => $line) {
        if (stripos($line, 'function') !== false || stripos($line, 'fillable') !== false || stripos($line, 'name') !== false || stripos($line, 'user') !== false || stripos($line, 'return') !== false) {
            echo ($i+1) . ": " . $line . "\n";
        }
    }
}

// 5. Check MarkEntry migration for columns
echo "\n===== MARK ENTRY MIGRATION =====\n";
 $migrations = glob($base . '/database/migrations/*mark*');
if (!empty($migrations)) {
    foreach ($migrations as $m) {
        echo basename($m) . "\n";
        $content = file_get_contents($m);
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (stripos($line, 'student') !== false || stripos($line, 'user') !== false || stripos($line, 'name') !== false || stripos($line, 'column') !== false || stripos($line, 'foreign') !== false) {
                echo ($i+1) . ": " . $line . "\n";
            }
        }
    }
} else {
    echo "No mark entry migrations found\n";
}

echo "\n===== END DIAGNOSIS =====\n";
