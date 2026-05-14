<?php
 $base = getcwd();
 $ctrlPath = $base . '/app/Http/Controllers/Admin/MarkEntryController.php';

if (!file_exists($ctrlPath)) {
    echo "Controller NOT FOUND at $ctrlPath\n";
    // Try alternate locations
    $altPaths = array_merge(
        glob($base . '/app/Http/Controllers/*/*MarkEntry*'),
        glob($base . '/app/Http/Controllers/*MarkEntry*')
    );
    if (!empty($altPaths)) {
        $ctrlPath = $altPaths[0];
        echo "Found at: $ctrlPath\n";
    } else {
        die("Cannot find MarkEntryController\n");
    }
}

 $ctrl = file_get_contents($ctrlPath);
 $changes = 0;

// ============================================================
// FIX 1: apiLoadStudents - use student first_name/last_name instead of users.name
// ============================================================
echo "===== FIX: apiLoadStudents using users.name instead of student name =====\n\n";

// Old code joins users table and uses users.name
 $old1 = "DB::table('students')->leftJoin('users','students.user_id','=','users.id')";
 $new1 = "DB::table('students')";

 $ctrl = str_replace($old1, $new1, $ctrl, $c);
if ($c > 0) { echo "OK: Removed leftJoin with users table\n"; $changes += $c; }

// Old: ->orderBy('users.name','asc')
 $old2 = "->orderBy('users.name','asc')";
 $new2 = "->orderBy('students.last_name','asc')->orderBy('students.first_name','asc')";

 $ctrl = str_replace($old2, $new2, $ctrl, $c);
if ($c > 0) { echo "OK: Changed orderBy to use students.last_name, students.first_name\n"; $changes += $c; }

// Old: 'users.name as student_name'
 $old3 = "'users.name as student_name'";
 $new3 = "DB::raw(\"CONCAT(students.first_name, ' ', students.last_name) as student_name\")";

 $ctrl = str_replace($old3, $new3, $ctrl, $c);
if ($c > 0) { echo "OK: Changed select to CONCAT first_name + last_name\n"; $changes += $c; }

// Also check for double-quoted version
 $old3b = '"users.name as student_name"';
 $new3b = 'DB::raw("CONCAT(students.first_name, \' \', students.last_name) as student_name")';

 $ctrl = str_replace($old3b, $new3b, $ctrl, $c);
if ($c > 0) { echo "OK: Changed select (double-quoted variant)\n"; $changes += $c; }

// ============================================================
// FIX 2: Make sure apiStudents also returns proper name
// ============================================================
echo "\n===== Checking apiStudents method =====\n";

// The apiStudents method already returns first_name and last_name separately
// The create.blade.php JS already handles: [s.first_name || s.student_name || s.name, s.last_name].filter(Boolean).join(' ')
// So that's fine. Let's just verify.

if (strpos($ctrl, "'first_name' => \$student->first_name") !== false) {
    echo "OK: apiStudents already returns first_name and last_name properly\n";
} else {
    echo "WARNING: apiStudents may need updating too\n";
}

// ============================================================
// FIX 3: Also check the mark-sheet and mark-roster controllers
// for any similar issues with user vs student name
// ============================================================
echo "\n===== Checking other mark controllers =====\n";

 $markSheetCtrl = $base . '/app/Http/Controllers/Admin/MarkSheetController.php';
if (file_exists($markSheetCtrl)) {
    $msc = file_get_contents($markSheetCtrl);
    if (strpos($msc, "users.name") !== false) {
        echo "WARNING: MarkSheetController also uses users.name - needs fix\n";
        // Fix it
        $msc = str_replace(
            ["DB::table('students')->leftJoin('users','students.user_id','=','users.id')", "'users.name as student_name'", "->orderBy('users.name','asc')"],
            ["DB::table('students')", "DB::raw(\"CONCAT(students.first_name, ' ', students.last_name) as student_name\")", "->orderBy('students.last_name','asc')->orderBy('students.first_name','asc')"],
            $msc, $c
        );
        if ($c > 0) {
            file_put_contents($markSheetCtrl, $msc);
            echo "OK: Fixed MarkSheetController too\n";
            $changes += $c;
        }
    } else {
        echo "OK: MarkSheetController doesn't use users.name\n";
    }
} else {
    echo "MarkSheetController not found (may not exist)\n";
}

 $markRosterCtrl = $base . '/app/Http/Controllers/Admin/MarkRosterController.php';
if (file_exists($markRosterCtrl)) {
    $mrc = file_get_contents($markRosterCtrl);
    if (strpos($mrc, "users.name") !== false) {
        echo "WARNING: MarkRosterController also uses users.name - needs fix\n";
        $mrc = str_replace(
            ["DB::table('students')->leftJoin('users','students.user_id','=','users.id')", "'users.name as student_name'", "->orderBy('users.name','asc')"],
            ["DB::table('students')", "DB::raw(\"CONCAT(students.first_name, ' ', students.last_name) as student_name\")", "->orderBy('students.last_name','asc')->orderBy('students.first_name','asc')"],
            $mrc, $c
        );
        if ($c > 0) {
            file_put_contents($markRosterCtrl, $mrc);
            echo "OK: Fixed MarkRosterController too\n";
            $changes += $c;
        }
    } else {
        echo "OK: MarkRosterController doesn't use users.name\n";
    }
} else {
    echo "MarkRosterController not found (may not exist)\n";
}

// ============================================================
// Save the main controller if changes were made
// ============================================================
if ($changes > 0) {
    file_put_contents($ctrlPath, $ctrl);
    echo "\n===== SAVED: $changes changes made to MarkEntryController =====\n";
} else {
    echo "\n===== NO CHANGES NEEDED =====\n";
    // Let's dump the apiLoadStudents method for inspection
    echo "\nCurrent apiLoadStudents method:\n";
    $lines = explode("\n", $ctrl);
    $inMethod = false;
    $braceCount = 0;
    foreach ($lines as $i => $line) {
        if (strpos($line, 'function apiLoadStudents') !== false) {
            $inMethod = true;
        }
        if ($inMethod) {
            echo ($i+1) . ": " . $line . "\n";
            $braceCount += substr_count($line, '{');
            $braceCount -= substr_count($line, '}');
            if ($braceCount <= 0 && strpos($line, '}') !== false) break;
        }
    }
}

echo "\nDone! Run: php artisan cache:clear\n";
