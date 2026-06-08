<?php
/**
 * Homepage 500 Error Diagnostic
 * Finds the real error causing the 500 on /
 * DELETE this file after debugging!
 */
echo "<h1>Homepage Diagnostic</h1><pre>";

// ── 1. Check Laravel logs ──
echo "=== Recent Laravel Error Logs ===\n";
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    // Get last 5000 chars to find the most recent error
    $tail = substr($logContent, -5000);
    // Find the last error entry
    $entries = explode('[', $tail);
    $lastEntries = array_slice($entries, -5);
    foreach ($lastEntries as $entry) {
        if (strlen($entry) > 50) {
            echo "[" . substr($entry, 0, 500) . "\n\n";
        }
    }
} else {
    echo "No log file found at $logFile\n";
}

// ── 2. Try bootstrapping Laravel and catching the error ──
echo "\n=== Testing Laravel Bootstrap ===\n";
try {
    require __DIR__ . '/vendor/autoload.php';
    echo "✓ Autoloader loaded\n";
    
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "✓ App bootstrapped (" . get_class($app) . ")\n";
    
    // Try to resolve the kernel
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    echo "✓ HTTP Kernel resolved\n";
    
} catch (\Throwable $e) {
    echo "✗ Bootstrap error: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// ── 3. Try the HomeController directly ──
echo "\n=== Testing HomeController ===\n";
try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    
    // Create a fake request to /
    $request = \Illuminate\Http\Request::create('/', 'GET');
    $request->setRuntimeUrl('http://redemption.byethost4.com');
    
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    
    echo "Response status: $status\n";
    
    if ($status >= 400) {
        $body = $response->getContent();
        // If it's an HTML error page, try to extract the error message
        if (preg_match('/<title>(.*?)<\/title>/s', $body, $m)) {
            echo "Error title: " . $m[1] . "\n";
        }
        // Try to find the actual error in the response
        if (preg_match('/exception.*?<.*?>(.*?)</s', $body, $m)) {
            echo "Exception: " . $m[1] . "\n";
        }
        // Show first 2000 chars of response for analysis
        echo "\nResponse preview:\n";
        echo htmlspecialchars(substr($body, 0, 3000)) . "\n";
    } else {
        echo "✓ Homepage loaded successfully!\n";
    }
    
    $kernel->terminate($request, $response);
    
} catch (\Throwable $e) {
    echo "✗ HomeController error: " . get_class($e) . "\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "  Trace:\n";
    foreach ($e->getTrace() as $i => $t) {
        if ($i > 10) break;
        echo "    #$i " . ($t['file'] ?? 'internal') . ":" . ($t['line'] ?? '?') . " in " . ($t['function'] ?? '?') . "()\n";
    }
}

// ── 4. Check if key database tables exist ──
echo "\n=== Database Tables Check ===\n";
try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
    $tableNames = array_map(function($t) { return array_values((array)$t)[0]; }, $tables);
    
    $requiredTables = ['sliders', 'team_members', 'gallery_images', 'gallery_videos', 
                       'settings', 'video_library', 'news', 'branches', 'classes', 
                       'sessions', 'users'];
    
    foreach ($requiredTables as $table) {
        if (in_array($table, $tableNames)) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' MISSING!\n";
        }
    }
} catch (\Throwable $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

echo "\n</pre>";
