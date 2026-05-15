<?php
 $base = getcwd();

echo "===== FINDING MISSING FEATURES =====\n\n";

// 1. Find all controllers
echo "--- Controllers ---\n";
 $controllers = array_merge(
    glob($base . '/app/Http/Controllers/Admin/*.php'),
    glob($base . '/app/Http/Controllers/*/*.php')
);
foreach ($controllers as $c) {
    echo "  " . basename($c) . "\n";
}

// 2. Find all admin view directories
echo "\n--- Admin View Directories ---\n";
 $viewDirs = glob($base . '/resources/views/admin/*', GLOB_ONLYDIR);
foreach ($viewDirs as $d) {
    $files = glob($d . '/*.blade.php');
    echo "  " . basename($d) . "/ (" . count($files) . " files)\n";
}

// 3. Check routes for mark-roster, mark-sheet, certificates, id-cards
echo "\n--- Routes for missing features ---\n";
 $routePath = $base . '/routes/web.php';
 $routes = file_get_contents($routePath);
 $lines = explode("\n", $routes);
foreach ($lines as $i => $line) {
    if (stripos($line, 'mark-roster') !== false || stripos($line, 'mark-sheet') !== false || stripos($line, 'certificate') !== false || stripos($line, 'id-card') !== false || stripos($line, 'IdCard') !== false || stripos($line, 'Certificate') !== false) {
        echo "  " . ($i+1) . ": " . trim($line) . "\n";
    }
}

// 4. Check if specific controllers exist
echo "\n--- Key Controller Files ---\n";
 $checkControllers = [
    'MarkRosterController',
    'MarkSheetController', 
    'CertificateController',
    'IdCardController',
    'ReportCardController',
    'ProgressReportController',
];
foreach ($checkControllers as $cc) {
    $found = false;
    foreach ($controllers as $c) {
        if (stripos(basename($c), $cc) !== false) {
            echo "  FOUND: " . basename($c) . " at " . dirname($c) . "\n";
            $found = true;
        }
    }
    if (!$found) echo "  MISSING: $cc\n";
}

echo "\n===== DONE =====\n";
