<?php
/**
 * HOMEPAGE 500 ERROR DIAGNOSTIC - READ THE ACTUAL ERROR LOG
 * 
 * This script reads storage/logs/laravel.log to find the REAL error
 * causing the homepage 500. All previous fixes were speculative.
 * 
 * Upload to: /htdocs/read-error-log.php
 * Run: https://redemption.byethost4.com/read-error-log.php
 * DELETE after use!
 */

echo "<h1>Laravel Error Log Reader</h1>";
echo "<pre>";

$base = dirname(__FILE__);
$logFile = $base . '/storage/logs/laravel.log';

// ============================================================
// 1. CHECK IF LOG FILE EXISTS AND READ IT
// ============================================================
echo "=== STEP 1: READ LARAVEL ERROR LOG ===\n\n";

if (!file_exists($logFile)) {
    echo "ERROR: Log file not found at: $logFile\n";
    
    // Try alternative locations
    $altLocations = [
        $base . '/storage/logs/laravel-*.log',
        $base . '/logs/laravel.log',
        $base . '/app/storage/logs/laravel.log',
    ];
    
    foreach ($altLocations as $alt) {
        $matches = glob($alt);
        if (!empty($matches)) {
            $logFile = $matches[0];
            echo "Found log at alternative location: $logFile\n";
            break;
        }
    }
    
    if (!file_exists($logFile)) {
        // List what's in storage/logs
        $logDir = $base . '/storage/logs';
        if (is_dir($logDir)) {
            echo "Contents of storage/logs:\n";
            $files = scandir($logDir);
            foreach ($files as $f) {
                if ($f !== '.' && $f !== '..') {
                    $fp = $logDir . '/' . $f;
                    echo "  $f (" . filesize($fp) . " bytes, modified: " . date('Y-m-d H:i:s', filemtime($fp)) . ")\n";
                }
            }
        } else {
            echo "storage/logs directory does not exist!\n";
        }
        
        // Also check for daily log files
        $today = date('Y-m-d');
        $dailyLog = $base . "/storage/logs/laravel-{$today}.log";
        if (file_exists($dailyLog)) {
            $logFile = $dailyLog;
            echo "Found daily log: $logFile\n";
        }
    }
}

if (file_exists($logFile)) {
    $logSize = filesize($logFile);
    echo "Log file: $logFile\n";
    echo "Log size: " . number_format($logSize) . " bytes\n\n";
    
    // Read last 8KB of the log (enough for recent errors)
    $maxRead = 8192;
    $handle = fopen($logFile, 'r');
    
    if ($logSize > $maxRead) {
        fseek($handle, $logSize - $maxRead);
        $content = fread($handle, $maxRead);
        echo "[Showing last {$maxRead} bytes of log]\n\n";
    } else {
        $content = fread($handle, $logSize);
        echo "[Showing full log]\n\n";
    }
    fclose($handle);
    
    // Find all error entries (look for exception/error patterns)
    echo "=== RECENT LOG ENTRIES ===\n\n";
    
    // Split by the Laravel log separator [YYYY-MM-DD HH:MM:SS]
    preg_match_all('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|$)/s', $content, $matches);
    
    if (!empty($matches[0])) {
        // Show last 5 error entries
        $entries = $matches[0];
        $count = min(count($entries), 5);
        
        for ($i = max(0, count($entries) - $count); $i < count($entries); $i++) {
            $entry = $entries[$i];
            // Truncate very long stack traces
            if (strlen($entry) > 2000) {
                $entry = substr($entry, 0, 2000) . "\n... [truncated, full entry is " . strlen($entries[$i]) . " bytes]";
            }
            echo $entry . "\n";
            echo str_repeat('-', 60) . "\n\n";
        }
    } else {
        // Just dump the raw content
        if (strlen($content) > 5000) {
            echo substr($content, -5000);
        } else {
            echo $content;
        }
    }
} else {
    echo "NO LOG FILE FOUND - trying to generate one...\n\n";
}

// ============================================================
// 2. TRY TO CAPTURE THE ACTUAL ERROR BY RUNNING LARAVEL DIRECTLY
// ============================================================
echo "\n=== STEP 2: CAPTURE ERROR DIRECTLY ===\n\n";

// Temporarily enable debug mode
$envFile = $base . '/.env';
$envBackup = null;

if (file_exists($envFile)) {
    $envBackup = file_get_contents($envFile);
    $envContent = $envBackup;
    
    // Force debug mode ON
    $envContent = preg_replace('/APP_DEBUG=(true|false)/i', 'APP_DEBUG=true', $envContent);
    if (!preg_match('/APP_DEBUG=/', $envContent)) {
        $envContent .= "\nAPP_DEBUG=true\n";
    }
    
    file_put_contents($envFile, $envContent);
    echo "APP_DEBUG set to true\n";
}

// Clear config cache
$configCache = $base . '/bootstrap/cache/config.php';
if (file_exists($configCache)) {
    unlink($configCache);
    echo "Cleared config cache\n";
}

// Now try to boot Laravel and capture the error
try {
    // Suppress output
    ob_start();
    
    $app = require $base . '/bootstrap/app.php';
    echo "App bootstrap: OK\n";
    
    // Try to resolve the kernel
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    echo "HTTP Kernel: OK\n";
    
    // Create a request for the homepage
    $request = \Illuminate\Http\Request::create('/', 'GET');
    $request->setTrustedProxies(['127.0.0.1', request()->ip()], 
        \Illuminate\Http\Request::HEADER_X_FORWARDED_ALL);
    echo "Request created: OK\n";
    
    // Handle the request
    $response = $kernel->handle($request);
    echo "Response status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() !== 200) {
        echo "ERROR RESPONSE BODY:\n";
        $body = $response->getContent();
        if (strlen($body) > 5000) {
            echo substr($body, 0, 5000) . "\n... [truncated]";
        } else {
            echo $body;
        }
        echo "\n";
    }
    
    $kernel->terminate($request, $response);
    
} catch (\Throwable $e) {
    echo "EXCEPTION CAUGHT:\n";
    echo "Class: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (line " . $e->getLine() . ")\n";
    echo "\nStack Trace:\n";
    
    $trace = $e->getTraceAsString();
    if (strlen($trace) > 3000) {
        echo substr($trace, 0, 3000) . "\n... [truncated]";
    } else {
        echo $trace;
    }
    echo "\n";
    
    // Also show previous exception if any
    if ($e->getPrevious()) {
        $prev = $e->getPrevious();
        echo "\nPREVIOUS EXCEPTION:\n";
        echo "Class: " . get_class($prev) . "\n";
        echo "Message: " . $prev->getMessage() . "\n";
        echo "File: " . $prev->getFile() . " (line " . $prev->getLine() . ")\n";
    }
}

// ============================================================
// 3. CHECK KEY FILES FOR ISSUES
// ============================================================
echo "\n\n=== STEP 3: CHECK KEY FILES ===\n\n";

// Check routes/web.php for the homepage route
$routesFile = $base . '/routes/web.php';
if (file_exists($routesFile)) {
    $routes = file_get_contents($routesFile);
    echo "routes/web.php size: " . strlen($routes) . " bytes\n";
    
    // Find the homepage route
    if (preg_match('/Route::[^\n]*[\'"]\/[\'"][^\n]*;/s', $routes, $match)) {
        echo "Homepage route: " . trim($match[0]) . "\n";
    } else {
        echo "WARNING: No explicit '/' route found\n";
        // Look for any root route patterns
        if (preg_match('/Route::[^\n]*\/[^\n]*;/s', $routes, $match)) {
            echo "Possible root routes:\n";
            preg_match_all('/Route::[^\n]*\/[^\n]*;/s', $routes, $allMatches);
            foreach ($allMatches[0] as $m) {
                echo "  " . trim($m) . "\n";
            }
        }
    }
}

// Check HomeController
$homeController = $base . '/app/Http/Controllers/HomeController.php';
if (file_exists($homeController)) {
    $content = file_get_contents($homeController);
    echo "\nHomeController.php size: " . strlen($content) . " bytes\n";
    
    // Show the index method
    if (preg_match('/public\s+function\s+index\s*\([^)]*\)\s*\{[^}]*\}/s', $content, $match)) {
        echo "Index method:\n";
        $method = $match[0];
        if (strlen($method) > 1000) {
            echo substr($method, 0, 1000) . "\n... [truncated]";
        } else {
            echo $method;
        }
        echo "\n";
    }
} else {
    echo "\nWARNING: HomeController.php NOT FOUND!\n";
}

// Check welcome.blade.php
$welcome = $base . '/resources/views/welcome.blade.php';
if (file_exists($welcome)) {
    echo "\nwelcome.blade.php size: " . filesize($welcome) . " bytes\n";
    // Check first and last few lines
    $wc = file_get_contents($welcome);
    $lines = explode("\n", $wc);
    echo "Lines: " . count($lines) . "\n";
    echo "First 3 lines:\n";
    for ($i = 0; $i < min(3, count($lines)); $i++) {
        echo "  " . $lines[$i] . "\n";
    }
    echo "Last 3 lines:\n";
    for ($i = max(0, count($lines) - 3); $i < count($lines); $i++) {
        echo "  " . $lines[$i] . "\n";
    }
}

// Check layouts/website.blade.php
$layout = $base . '/resources/views/layouts/website.blade.php';
if (file_exists($layout)) {
    echo "\nlayouts/website.blade.php size: " . filesize($layout) . " bytes\n";
    $lc = file_get_contents($layout);
    $lines = explode("\n", $lc);
    echo "Lines: " . count($lines) . "\n";
    echo "First 5 lines:\n";
    for ($i = 0; $i < min(5, count($lines)); $i++) {
        echo "  " . $lines[$i] . "\n";
    }
}

// Check if website.css exists (from previous fix attempt)
$websiteCss = $base . '/public/css/website.css';
if (file_exists($websiteCss)) {
    echo "\npublic/css/website.css EXISTS: " . filesize($websiteCss) . " bytes\n";
}

// ============================================================
// 4. CHECK FOR SYNTAX ERRORS IN KEY FILES
// ============================================================
echo "\n\n=== STEP 4: PHP SYNTAX CHECK ===\n\n";

$filesToCheck = [
    'app/Http/Controllers/HomeController.php',
    'app/Providers/AppServiceProvider.php',
    'app/Providers/RouteServiceProvider.php',
    'routes/web.php',
    'app/Http/Kernel.php',
];

foreach ($filesToCheck as $file) {
    $fullPath = $base . '/' . $file;
    if (file_exists($fullPath)) {
        $output = [];
        $returnVar = 0;
        exec("php -l " . escapeshellarg($fullPath) . " 2>&1", $output, $returnVar);
        $status = $returnVar === 0 ? "OK" : "ERROR";
        echo "$file: $status\n";
        if ($returnVar !== 0) {
            echo "  " . implode("\n  ", $output) . "\n";
        }
    } else {
        echo "$file: NOT FOUND\n";
    }
}

// ============================================================
// 5. REAL HTTP TEST WITH ERROR CAPTURE
// ============================================================
echo "\n\n=== STEP 5: REAL HTTP TEST ===\n\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://redemption.byethost4.com/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HEADER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($response, $headerSize);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response body length: " . strlen($body) . " bytes\n";

if ($error) {
    echo "cURL error: $error\n";
}

if ($httpCode !== 200) {
    echo "\nERROR RESPONSE BODY:\n";
    if (strlen($body) > 5000) {
        echo substr($body, 0, 5000) . "\n... [truncated]";
    } else {
        echo $body ?: "(empty response body)";
    }
}

// ============================================================
// 6. CHECK COMPILE ERROR IN BLADE VIEWS
// ============================================================
echo "\n\n=== STEP 6: BLADE COMPILE CHECK ===\n\n";

// Check if compiled views have errors
$compiledDir = $base . '/storage/framework/views';
if (is_dir($compiledDir)) {
    $compiledFiles = glob($compiledDir . '/*.php');
    echo "Compiled views: " . count($compiledFiles) . " files\n";
    
    // Clear compiled views (forces recompile)
    foreach ($compiledFiles as $cf) {
        unlink($cf);
    }
    echo "Cleared all compiled views\n";
} else {
    echo "Compiled views directory not found\n";
}

// Restore .env
if ($envBackup !== null) {
    file_put_contents($envFile, $envBackup);
    echo "\nRestored original .env file\n";
}

echo "\n\n=== DONE ===\n";
echo "Please share the FULL output above, especially any EXCEPTION or ERROR messages.\n";
echo "Then DELETE this file from your server!\n";

echo "</pre>";
