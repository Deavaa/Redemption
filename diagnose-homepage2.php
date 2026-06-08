<?php
/**
 * Homepage 500 Error Diagnostic — v2
 * 
 * This script properly bootstraps Laravel to find the EXACT error
 * causing the homepage (/) to return 500 while other routes work.
 * 
 * Upload to: public_html/diagnose-homepage2.php
 * Access:    https://redemption.byethost4.com/diagnose-homepage2.php
 * DELETE after fixing!
 */

echo '<html><head><title>Homepage Diagnostic v2</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#e0e0e0}';
echo '.ok{color:#4ade80}.err{color:#f87171}.warn{color:#fbbf24}.info{color:#60a5fa}';
echo 'pre{background:#0d1117;padding:12px;border-radius:8px;overflow-x:auto;border:1px solid #30363d}';
echo 'h2{color:#c9a84c;border-bottom:1px solid #30363d;padding-bottom:8px}';
echo 'h3{color:#60a5fa}</style></head><body>';

echo '<h1>Homepage 500 Error Diagnostic v2</h1>';

// ─── STEP 1: Read Laravel error log ───
echo '<h2>1. Laravel Error Log</h2>';
$logPath = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logPath)) {
    $logContent = file_get_contents($logPath);
    // Get last 3000 chars of the log
    $tail = substr($logContent, max(0, strlen($logContent) - 5000));
    echo '<pre>' . htmlspecialchars($tail) . '</pre>';
} else {
    echo '<p class="warn">No laravel.log found at storage/logs/</p>';
    // Check other possible log locations
    $altPaths = [
        __DIR__ . '/storage/logs/',
    ];
    if (is_dir(__DIR__ . '/storage/logs/')) {
        $logs = glob(__DIR__ . '/storage/logs/*.log');
        if ($logs) {
            echo '<p class="info">Found log files:</p><ul>';
            foreach ($logs as $l) {
                echo '<li>' . basename($l) . ' (' . filesize($l) . ' bytes)</li>';
            }
            echo '</ul>';
            // Read the most recent one
            $latest = end($logs);
            $content = file_get_contents($latest);
            echo '<pre>' . htmlspecialchars(substr($content, max(0, strlen($content) - 5000))) . '</pre>';
        }
    }
}

// ─── STEP 2: Check critical files ───
echo '<h2>2. Critical File Check</h2>';
$criticalFiles = [
    'app/Models/Slider.php',
    'app/Models/TeamMember.php',
    'app/Models/GalleryImage.php',
    'app/Models/GalleryVideo.php',
    'app/Models/VideoLibrary.php',
    'app/Models/Setting.php',
    'app/Models/News.php',
    'app/Http/Controllers/HomeController.php',
    'app/Http/Controllers/AppController.php',
    'resources/views/welcome.blade.php',
    'resources/views/layouts/website.blade.php',
];
foreach ($criticalFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "<p class='ok'>✅ {$file}</p>";
    } else {
        echo "<p class='err'>❌ MISSING: {$file}</p>";
    }
}

// ─── STEP 3: Check for Classroom.php alias (case conflict) ───
echo '<h2>3. Classroom vs ClassRoom Check</h2>';
if (file_exists(__DIR__ . '/app/Models/Classroom.php')) {
    echo "<p class='err'>❌ app/Models/Classroom.php EXISTS — this aliases ClassRoom and may conflict!</p>";
} else {
    echo "<p class='ok'>✅ No Classroom.php alias found</p>";
}
if (file_exists(__DIR__ . '/app/Models/ClassRoom.php')) {
    echo "<p class='ok'>✅ app/Models/ClassRoom.php exists</p>";
} else {
    echo "<p class='err'>❌ app/Models/ClassRoom.php MISSING!</p>";
}

// ─── STEP 4: Check autoloader classmap ───
echo '<h2>4. Autoloader Classmap Check</h2>';
$autoloadPath = __DIR__ . '/vendor/composer/autoload_classmap.php';
if (file_exists($autoloadPath)) {
    $classmap = include $autoloadPath;
    $classroomEntries = [];
    foreach ($classmap as $class => $path) {
        if (stripos($class, 'classroom') !== false || stripos($class, 'classroom') !== false) {
            $classroomEntries[$class] = $path;
        }
    }
    if (empty($classroomEntries)) {
        echo "<p class='info'>No Classroom/ClassRoom entries in classmap</p>";
    } else {
        foreach ($classroomEntries as $class => $path) {
            $shortPath = str_replace(__DIR__ . '/', '', $path);
            echo "<p>Class: <code>{$class}</code> → <code>{$shortPath}</code></p>";
        }
    }
    
    // Check if HomeController is in the classmap
    if (isset($classmap['App\\Http\\Controllers\\HomeController'])) {
        echo "<p class='ok'>✅ HomeController in classmap</p>";
    } else {
        echo "<p class='warn'>⚠️ HomeController NOT in classmap</p>";
    }
}

// ─── STEP 5: Properly bootstrap Laravel and test ───
echo '<h2>5. Laravel Bootstrap Test</h2>';

try {
    // Use the SAME bootstrap as index.php
    require __DIR__ . '/vendor/autoload.php';
    
    // Boot the Laravel application
    $app = require_once __DIR__ . '/bootstrap/app.php';
    
    // Handle the request via the kernel
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    // Create a request for the homepage
    $request = Illuminate\Http\Request::create('/', 'GET');
    
    // Set the necessary server variables
    $request->server->set('HTTP_HOST', $_SERVER['HTTP_HOST'] ?? 'redemption.byethost4.com');
    $request->server->set('SCRIPT_NAME', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $request->server->set('REQUEST_URI', '/');
    
    echo '<p class="info">Attempting to handle / request...</p>';
    
    $response = $kernel->handle($request);
    
    $status = $response->getStatusCode();
    if ($status === 200) {
        echo "<p class='ok'>✅ Homepage returned status 200!</p>";
    } else {
        echo "<p class='err'>❌ Homepage returned status {$status}</p>";
    }
    
    // If not 200, show the response body (which contains the error)
    if ($status !== 200) {
        $body = $response->getContent();
        // Try to extract the error message
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/s', $body, $m)) {
            echo "<p class='err'>Error title: " . htmlspecialchars($m[1]) . "</p>";
        }
        if (preg_match('/<p class=".*?message.*?">(.*?)<\/p>/s', $body, $m)) {
            echo "<p class='err'>Error message: " . htmlspecialchars($m[1]) . "</p>";
        }
        // Show first 3000 chars of response
        echo '<pre>' . htmlspecialchars(substr($body, 0, 3000)) . '</pre>';
    }
    
    $kernel->terminate($request, $response);
    
} catch (\Throwable $e) {
    echo '<p class="err">❌ Exception during bootstrap:</p>';
    echo '<pre class="err">';
    echo 'Class: ' . get_class($e) . "\n";
    echo 'Message: ' . $e->getMessage() . "\n";
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n";
    echo 'Trace:\n';
    echo $e->getTraceAsString();
    echo '</pre>';
    
    // Show previous exception if any
    if ($e->getPrevious()) {
        $prev = $e->getPrevious();
        echo '<h3>Previous Exception</h3>';
        echo '<pre class="err">';
        echo 'Class: ' . get_class($prev) . "\n";
        echo 'Message: ' . $prev->getMessage() . "\n";
        echo 'File: ' . $prev->getFile() . ':' . $prev->getLine() . "\n";
        echo '</pre>';
    }
}

// ─── STEP 6: Test /login route for comparison ───
echo '<h2>6. /login Route Test (for comparison)</h2>';
try {
    $app2 = require __DIR__ . '/bootstrap/app.php';
    $kernel2 = $app2->make(Illuminate\Contracts\Http\Kernel::class);
    $request2 = Illuminate\Http\Request::create('/login', 'GET');
    $request2->server->set('HTTP_HOST', $_SERVER['HTTP_HOST'] ?? 'redemption.byethost4.com');
    $request2->server->set('SCRIPT_NAME', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $response2 = $kernel2->handle($request2);
    $status2 = $response2->getStatusCode();
    echo "<p class='" . ($status2 === 200 ? 'ok' : 'err') . "'>/login status: {$status2}</p>";
    $kernel2->terminate($request2, $response2);
} catch (\Throwable $e) {
    echo "<p class='err'>❌ /login test failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// ─── STEP 7: Check view cache ───
echo '<h2>7. View Cache</h2>';
$viewCacheDir = __DIR__ . '/storage/framework/views';
if (is_dir($viewCacheDir)) {
    $cachedViews = glob($viewCacheDir . '/*.php');
    echo '<p class="info">Found ' . count($cachedViews) . ' cached views</p>';
    // Check if any cached view references ClassRoom/Classroom
    foreach ($cachedViews as $cv) {
        $content = file_get_contents($cv);
        if (stripos($content, 'classroom') !== false || stripos($content, 'ClassRoom') !== false) {
            echo "<p class='warn'>⚠️ Cached view " . basename($cv) . " references Classroom/ClassRoom</p>";
        }
    }
    echo '<p class="info"><a href="?clear_views=1">Click here to clear view cache</a></p>';
} else {
    echo '<p class="warn">View cache directory does not exist</p>';
}

// Clear view cache if requested
if (isset($_GET['clear_views'])) {
    $cachedViews = glob($viewCacheDir . '/*.php');
    $cleared = 0;
    foreach ($cachedViews as $cv) {
        if (unlink($cv)) $cleared++;
    }
    echo "<p class='ok'>✅ Cleared {$cleared} cached views</p>";
}

// ─── STEP 8: Check AppServiceProvider detectAppUrl status ───
echo '<h2>8. AppServiceProvider Status</h2>';
$aspPath = __DIR__ . '/app/Providers/AppServiceProvider.php';
if (file_exists($aspPath)) {
    $aspContent = file_get_contents($aspPath);
    if (strpos($aspContent, '//$this->detectAppUrl') !== false || strpos($aspContent, '// $this->detectAppUrl') !== false) {
        echo "<p class='warn'>⚠️ detectAppUrl() is COMMENTED OUT</p>";
    } elseif (strpos($aspContent, '$this->detectAppUrl()') !== false) {
        echo "<p class='info'>detectAppUrl() is ACTIVE</p>";
    } else {
        echo "<p class='info'>detectAppUrl() call not found (may have been removed)</p>";
    }
}

echo '<hr>';
echo '<p><strong>DELETE THIS FILE after fixing the issue!</strong></p>';
echo '</body></html>';
