<?php
 $base = __DIR__;

echo "=== CONTROLLER - show/edit/update methods ===\n";
 $ctrl = file_get_contents($base . '/app/Http/Controllers/Exam/ExamController.php');
preg_match('/public function show.*?public function/s', $ctrl, $m);
echo "show: " . ($m[0] ?? 'NOT FOUND') . "\n---\n";
preg_match('/public function edit.*?public function/s', $ctrl, $m);
echo "edit: " . ($m[0] ?? 'NOT FOUND') . "\n---\n";
preg_match('/public function update.*?public function/s', $ctrl, $m);
echo "update: " . ($m[0] ?? 'NOT FOUND') . "\n---\n";

echo "\n=== EDIT BLADE - line 29 (form action) ===\n";
 $edit = file_get_contents($base . '/resources/views/admin/Exam/edit.blade.php');
 $lines = explode("\n", $edit);
for ($i = 27; $i <= 32; $i++) {
    echo ($i+1) . ': ' . trim($lines[$i]) . "\n";
}

echo "\n=== EDIT BLADE - all \$item references ===\n";
foreach ($lines as $i => $line) {
    if (strpos($line, '$item') !== false) {
        echo ($i+1) . ': ' . trim($line) . "\n";
    }
}

echo "\n=== EDIT BLADE - all \$exam references ===\n";
foreach ($lines as $i => $line) {
    if (strpos($line, '$exam') !== false) {
        echo ($i+1) . ': ' . trim($line) . "\n";
    }
}

echo "\n=== ROUTES - exams ===\n";
 $routes = file_get_contents($base . '/routes/web.php');
foreach (explode("\n", $routes) as $i => $line) {
    if (strpos($line, 'exam') !== false) {
        echo ($i+1) . ': ' . trim($line) . "\n";
    }
}
