<?php
/**
 * Homepage 500 Error — Comprehensive Fix Script v2
 * 
 * This script:
 * 1. Clears ALL Laravel caches (config, routes, views, services)
 * 2. Checks and fixes common issues
 * 3. Tests the homepage after fixes
 * 
 * Upload to: public_html/fix-homepage2.php
 * Access:    https://redemption.byethost4.com/fix-homepage2.php
 * DELETE after fixing!
 */

echo '<html><head><title>Fix Homepage v2</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#e0e0e0}';
echo '.ok{color:#4ade80}.err{color:#f87171}.warn{color:#fbbf24}.info{color:#60a5fa}';
echo 'pre{background:#0d1117;padding:12px;border-radius:8px;overflow-x:auto;border:1px solid #30363d}';
echo 'h2{color:#c9a84c;border-bottom:1px solid #30363d;padding-bottom:8px}</style></head><body>';

echo '<h1>Homepage 500 Error — Fix Script v2</h1>';

$baseDir = __DIR__;
$fixed = 0;
$errors = 0;

// ═══════════════════════════════════════════════════════
// STEP 1: Clear ALL Laravel Caches
// ═══════════════════════════════════════════════════════
echo '<h2>Step 1: Clear All Laravel Caches</h2>';

$cacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/events.php',
];

foreach ($cacheFiles as $cache) {
    $path = $baseDir . '/' . $cache;
    if (file_exists($path)) {
        if (@unlink($path)) {
            echo "<p class='ok'>✅ Deleted: {$cache}</p>";
            $fixed++;
        } else {
            echo "<p class='err'>❌ Could not delete: {$cache} (permission denied?)</p>";
            $errors++;
        }
    } else {
        echo "<p class='info'>⏭️ Not found: {$cache}</p>";
    }
}

// Clear compiled views
$viewCacheDir = $baseDir . '/storage/framework/views';
if (is_dir($viewCacheDir)) {
    $viewFiles = glob($viewCacheDir . '/*.php');
    $clearedViews = 0;
    foreach ($viewFiles as $vf) {
        if (@unlink($vf)) $clearedViews++;
    }
    echo "<p class='ok'>✅ Cleared {$clearedViews} compiled view files</p>";
    $fixed++;
} else {
    echo "<p class='warn'>⚠️ View cache directory doesn't exist: storage/framework/views/</p>";
    // Try to create it
    if (@mkdir($viewCacheDir, 0755, true)) {
        echo "<p class='ok'>✅ Created storage/framework/views/ directory</p>";
        $fixed++;
    } else {
        echo "<p class='err'>❌ Could not create storage/framework/views/</p>";
        $errors++;
    }
}

// ═══════════════════════════════════════════════════════
// STEP 2: Ensure storage directories exist and are writable
// ═══════════════════════════════════════════════════════
echo '<h2>Step 2: Storage Directories</h2>';

$requiredDirs = [
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
];

foreach ($requiredDirs as $dir) {
    $path = $baseDir . '/' . $dir;
    if (!is_dir($path)) {
        if (@mkdir($path, 0755, true)) {
            echo "<p class='ok'>✅ Created: {$dir}/</p>";
            $fixed++;
        } else {
            echo "<p class='err'>❌ Could not create: {$dir}/</p>";
            $errors++;
        }
    } else {
        if (is_writable($path)) {
            echo "<p class='ok'>✅ Writable: {$dir}/</p>";
        } else {
            echo "<p class='err'>❌ NOT writable: {$dir}/ (try chmod 755 or 777)</p>";
            // Try to fix permissions
            if (@chmod($path, 0755)) {
                echo "<p class='ok'>✅ Fixed permissions: {$dir}/</p>";
                $fixed++;
            } elseif (@chmod($path, 0777)) {
                echo "<p class='warn'>⚠️ Set 777: {$dir}/ (less secure but works)</p>";
                $fixed++;
            } else {
                $errors++;
            }
        }
    }
}

// ═══════════════════════════════════════════════════════
// STEP 3: Check critical model files
// ═══════════════════════════════════════════════════════
echo '<h2>Step 3: Critical Model Files</h2>';

$requiredModels = [
    'app/Models/Slider.php' => 'App\\Models\\Slider',
    'app/Models/TeamMember.php' => 'App\\Models\\TeamMember',
    'app/Models/GalleryImage.php' => 'App\\Models\\GalleryImage',
    'app/Models/GalleryVideo.php' => 'App\\Models\\GalleryVideo',
    'app/Models/VideoLibrary.php' => 'App\\Models\\VideoLibrary',
    'app/Models/Setting.php' => 'App\\Models\\Setting',
    'app/Models/News.php' => 'App\\Models\\News',
    'app/Models/ClassRoom.php' => 'App\\Models\\ClassRoom',
];

foreach ($requiredModels as $file => $class) {
    $path = $baseDir . '/' . $file;
    if (file_exists($path)) {
        echo "<p class='ok'>✅ {$file}</p>";
        // Check the file content has the right class name
        $content = file_get_contents($path);
        if (preg_match('/class\s+(\w+)/', $content, $m)) {
            $className = $m[1];
            $expectedClass = substr($class, strrpos($class, '\\') + 1);
            if ($className !== $expectedClass) {
                echo "<p class='err'>❌ Class name mismatch in {$file}: found '{$className}', expected '{$expectedClass}'</p>";
                $errors++;
            }
        }
    } else {
        echo "<p class='err'>❌ MISSING: {$file}</p>";
        $errors++;
    }
}

// Check for Classroom.php (should NOT exist - causes autoloader conflict)
if (file_exists($baseDir . '/app/Models/Classroom.php')) {
    echo "<p class='err'>❌ app/Models/Classroom.php EXISTS — will conflict with ClassRoom! Deleting...</p>";
    if (@unlink($baseDir . '/app/Models/Classroom.php')) {
        echo "<p class='ok'>✅ Deleted app/Models/Classroom.php</p>";
        $fixed++;
    } else {
        echo "<p class='err'>❌ Could not delete app/Models/Classroom.php — delete manually!</p>";
        $errors++;
    }
}

// ═══════════════════════════════════════════════════════
// STEP 4: Check critical view files
// ═══════════════════════════════════════════════════════
echo '<h2>Step 4: Critical View Files</h2>';

$requiredViews = [
    'resources/views/welcome.blade.php',
    'resources/views/layouts/website.blade.php',
    'resources/views/auth/login.blade.php',
];

foreach ($requiredViews as $view) {
    $path = $baseDir . '/' . $view;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "<p class='ok'>✅ {$view} ({$size} bytes)</p>";
    } else {
        echo "<p class='err'>❌ MISSING: {$view}</p>";
        $errors++;
    }
}

// ═══════════════════════════════════════════════════════
// STEP 5: Check and fix AppServiceProvider
// ═══════════════════════════════════════════════════════
echo '<h2>Step 5: AppServiceProvider Check</h2>';

$aspPath = $baseDir . '/app/Providers/AppServiceProvider.php';
if (file_exists($aspPath)) {
    $aspContent = file_get_contents($aspPath);
    
    // Check if detectAppUrl is commented or active
    if (strpos($aspContent, '//$this->detectAppUrl') !== false || strpos($aspContent, '// $this->detectAppUrl') !== false) {
        echo "<p class='warn'>⚠️ detectAppUrl() is commented out — this may cause redirect-to-localhost issues but shouldn't cause 500</p>";
    } elseif (strpos($aspContent, '$this->detectAppUrl()') !== false) {
        echo "<p class='info'>detectAppUrl() is active</p>";
    }
    
    // Check if Schema::hasTable is called in boot() - this can cause issues if DB is down
    if (strpos($aspContent, "Schema::hasTable('sessions')") !== false || strpos($aspContent, "\\Schema::hasTable('sessions')") !== false) {
        echo "<p class='info'>Schema::hasTable sessions check found in boot()</p>";
    }
} else {
    echo "<p class='err'>❌ AppServiceProvider.php MISSING!</p>";
    $errors++;
}

// ═══════════════════════════════════════════════════════
// STEP 6: Fix autoloader classmap
// ═══════════════════════════════════════════════════════
echo '<h2>Step 6: Autoloader Classmap Fix</h2>';

$classmapPath = $baseDir . '/vendor/composer/autoload_classmap.php';
if (file_exists($classmapPath)) {
    $classmap = include $classmapPath;
    $fixes = 0;
    
    // Fix Classroom → ClassRoom
    foreach ($classmap as $class => $path) {
        if (stripos($class, 'classroom') !== false) {
            $shortPath = str_replace($baseDir . '/', '', $path);
            $basename = basename($path);
            
            if ($class === 'App\\Models\\Classroom' && $basename === 'Classroom.php') {
                // This is the old alias — it should point to ClassRoom.php instead
                echo "<p class='warn'>⚠️ Found alias '{$class}' → {$shortPath}</p>";
                
                // Fix: update classmap to point to ClassRoom.php
                $classmap[$class] = dirname($path) . '/ClassRoom.php';
                if (file_exists($classmap[$class])) {
                    echo "<p class='ok'>✅ Redirected to ClassRoom.php</p>";
                    $fixes++;
                }
            }
            
            if ($class === 'App\\Models\\ClassRoom' && $basename !== 'ClassRoom.php') {
                echo "<p class='err'>❌ ClassRoom maps to wrong file: {$shortPath}</p>";
                // Fix it
                $correctPath = dirname($path) . '/ClassRoom.php';
                if (file_exists($correctPath)) {
                    $classmap[$class] = $correctPath;
                    echo "<p class='ok'>✅ Fixed to point to ClassRoom.php</p>";
                    $fixes++;
                }
            }
        }
    }
    
    if ($fixes > 0) {
        // Write the fixed classmap
        $output = "<?php\n\n// Auto-fixed by fix-homepage2.php\nreturn " . var_export($classmap, true) . ";\n";
        if (@file_put_contents($classmapPath, $output)) {
            echo "<p class='ok'>✅ Updated autoload_classmap.php with {$fixes} fixes</p>";
            $fixed += $fixes;
        } else {
            echo "<p class='err'>❌ Could not write autoload_classmap.php</p>";
            $errors++;
        }
    } else {
        echo "<p class='ok'>✅ Classmap looks correct</p>";
    }
}

// ═══════════════════════════════════════════════════════
// STEP 7: Check .env file
// ═══════════════════════════════════════════════════════
echo '<h2>Step 7: .env File Check</h2>';

$envPath = $baseDir . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    // Check APP_KEY
    if (preg_match('/^APP_KEY=(.*)$/m', $envContent, $m)) {
        $appKey = trim($m[1]);
        if (empty($appKey) || $appKey === '') {
            echo "<p class='err'>❌ APP_KEY is empty! This WILL cause 500 errors</p>";
            // Generate a new key
            $newKey = 'base64:' . base64_encode(random_bytes(32));
            $envContent = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$newKey}", $envContent);
            @file_put_contents($envPath, $envContent);
            echo "<p class='ok'>✅ Generated and set APP_KEY</p>";
            $fixed++;
        } else {
            echo "<p class='ok'>✅ APP_KEY is set</p>";
        }
    } else {
        echo "<p class='err'>❌ APP_KEY not found in .env</p>";
        $errors++;
    }
    
    // Check APP_URL (don't show the full URL for security)
    if (preg_match('/^APP_URL=(.*)$/m', $envContent, $m)) {
        $appUrl = trim($m[1]);
        if (strpos($appUrl, 'localhost') !== false) {
            echo "<p class='warn'>⚠️ APP_URL points to localhost: {$appUrl}</p>";
            echo "<p class='info'>This is OK if detectAppUrl() is active (it auto-detects the real URL)</p>";
        } else {
            echo "<p class='ok'>✅ APP_URL is set (not localhost)</p>";
        }
    }
    
    // Check DB_CONNECTION
    if (preg_match('/^DB_CONNECTION=(.*)$/m', $envContent, $m)) {
        $dbConn = trim($m[1]);
        echo "<p class='info'>DB_CONNECTION = {$dbConn}</p>";
    }
    if (preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $m)) {
        $dbName = trim($m[1]);
        echo "<p class='info'>DB_DATABASE = {$dbName}</p>";
    }
} else {
    echo "<p class='err'>❌ .env file MISSING!</p>";
    $errors++;
}

// ═══════════════════════════════════════════════════════
// STEP 8: Re-enable detectAppUrl if it was commented out
// ═══════════════════════════════════════════════════════
echo '<h2>Step 8: Re-enable detectAppUrl()</h2>';

if (file_exists($aspPath)) {
    $aspContent = file_get_contents($aspPath);
    
    // Re-enable detectAppUrl if it was commented out
    if (strpos($aspContent, '//$this->detectAppUrl') !== false) {
        $aspContent = str_replace('//$this->detectAppUrl', '$this->detectAppUrl', $aspContent);
        // Also handle the case with a space after //
        $aspContent = str_replace('// $this->detectAppUrl', '$this->detectAppUrl', $aspContent);
        // Remove any "Temporarily disabled" comment
        $aspContent = str_replace(' // Temporarily disabled to fix homepage', '', $aspContent);
        if (@file_put_contents($aspPath, $aspContent)) {
            echo "<p class='ok'>✅ Re-enabled detectAppUrl() in AppServiceProvider</p>";
            $fixed++;
        } else {
            echo "<p class='err'>❌ Could not update AppServiceProvider</p>";
            $errors++;
        }
    } else {
        echo "<p class='ok'>✅ detectAppUrl() is already active</p>";
    }
}

// ═══════════════════════════════════════════════════════
// STEP 9: Test the homepage using Laravel's own kernel
// ═══════════════════════════════════════════════════════
echo '<h2>Step 9: Test Homepage</h2>';

try {
    require $baseDir . '/vendor/autoload.php';
    $app = require_once $baseDir . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    // Test homepage
    $request = Illuminate\Http\Request::create('/', 'GET');
    $request->server->set('HTTP_HOST', $_SERVER['HTTP_HOST'] ?? 'redemption.byethost4.com');
    $request->server->set('SCRIPT_NAME', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $request->server->set('REQUEST_URI', '/');
    $request->server->set('HTTPS', 'on');
    
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    
    if ($status === 200) {
        echo "<p class='ok'>✅✅✅ HOMEPAGE WORKS! Status: 200</p>";
    } else {
        echo "<p class='err'>❌ Homepage returned status: {$status}</p>";
        
        $body = $response->getContent();
        
        // Extract error details from the response
        // Try to find the actual error message
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/si', $body, $m)) {
            echo "<p class='err'>Error: " . htmlspecialchars(strip_tags($m[1])) . "</p>";
        }
        if (preg_match('/class="exception_message"(.*?)<\/p>/si', $body, $m)) {
            echo "<p class='err'>Exception: " . htmlspecialchars(strip_tags($m[1])) . "</p>";
        }
        if (preg_match('/<span class="exception_message">(.*?)<\/span>/si', $body, $m)) {
            echo "<p class='err'>Exception: " . htmlspecialchars(strip_tags($m[1])) . "</p>";
        }
        
        // Look for specific error patterns
        if (strpos($body, 'ClassRoom') !== false || strpos($body, 'Classroom') !== false) {
            echo "<p class='err'>❌ Error involves ClassRoom/Classroom class</p>";
        }
        if (strpos($body, 'Class ') !== false && strpos($body, ' not found') !== false) {
            if (preg_match('/Class\s+[\'"]?(\S+)[\'"]?\s+not found/', $body, $m)) {
                echo "<p class='err'>❌ Missing class: {$m[1]}</p>";
            }
        }
        if (strpos($body, 'BindingResolutionException') !== false) {
            echo "<p class='err'>❌ BindingResolutionException — a service can\'t be resolved from the container</p>";
        }
        
        // Show a snippet of the response
        echo '<details><summary>Response body (first 2000 chars)</summary>';
        echo '<pre>' . htmlspecialchars(substr($body, 0, 2000)) . '</pre>';
        echo '</details>';
    }
    
    $kernel->terminate($request, $response);
    
} catch (\Throwable $e) {
    echo '<p class="err">❌ Exception during test:</p>';
    echo '<pre>';
    echo 'Class: ' . get_class($e) . "\n";
    echo 'Message: ' . $e->getMessage() . "\n";
    echo 'File: ' . str_replace($baseDir . '/', '', $e->getFile()) . ':' . $e->getLine() . "\n\n";
    
    // Show a limited trace
    $trace = $e->getTraceAsString();
    $traceLines = explode("\n", $trace);
    echo "Trace (first 15 frames):\n";
    for ($i = 0; $i < min(15, count($traceLines)); $i++) {
        echo $traceLines[$i] . "\n";
    }
    echo '</pre>';
    
    if ($e->getPrevious()) {
        $prev = $e->getPrevious();
        echo '<h3>Previous Exception (root cause)</h3>';
        echo '<pre>';
        echo 'Class: ' . get_class($prev) . "\n";
        echo 'Message: ' . $prev->getMessage() . "\n";
        echo 'File: ' . str_replace($baseDir . '/', '', $prev->getFile()) . ':' . $prev->getLine() . "\n";
        echo '</pre>';
    }
}

// ═══════════════════════════════════════════════════════
// STEP 10: Read the latest Laravel error log
// ═══════════════════════════════════════════════════════
echo '<h2>Step 10: Latest Laravel Error Log</h2>';

$logDir = $baseDir . '/storage/logs';
if (is_dir($logDir)) {
    $logFiles = glob($logDir . '/laravel*.log');
    if ($logFiles) {
        // Sort by modification time, newest first
        usort($logFiles, function($a, $b) { return filemtime($b) - filemtime($a); });
        $latestLog = $logFiles[0];
        
        $logContent = file_get_contents($latestLog);
        $tail = substr($logContent, max(0, strlen($logContent) - 6000));
        
        echo '<details><summary>Click to see last log entries (' . basename($latestLog) . ')</summary>';
        echo '<pre>' . htmlspecialchars($tail) . '</pre>';
        echo '</details>';
    } else {
        echo '<p class="info">No Laravel log files found</p>';
    }
} else {
    echo '<p class="warn">storage/logs/ directory does not exist</p>';
}

// ═══════════════════════════════════════════════════════
// Summary
// ═══════════════════════════════════════════════════════
echo '<hr>';
echo "<h2>Summary: {$fixed} fixes applied, {$errors} errors</h2>";
echo '<p><a href="/">Test Homepage Now</a> | <a href="/login">Test Login</a></p>';
echo '<p><strong style="color:#f87171">DELETE THIS FILE after fixing: fix-homepage2.php</strong></p>';
echo '</body></html>';
