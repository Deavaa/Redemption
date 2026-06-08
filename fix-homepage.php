<?php
/**
 * Fix Homepage 500 Error
 * Clears Laravel caches and ensures storage directories exist.
 * DELETE this file after fixing!
 */
echo "<h1>Laravel Cache Fix</h1><pre>";

$root = __DIR__;
$fixed = 0;
$errors = [];

// ── 1. Ensure storage directories exist ──
echo "=== Checking Storage Directories ===\n";
$dirs = [
    '/storage/framework',
    '/storage/framework/cache',
    '/storage/framework/cache/data',
    '/storage/framework/sessions',
    '/storage/framework/views',
    '/storage/logs',
    '/bootstrap/cache',
];

foreach ($dirs as $dir) {
    $fullPath = $root . $dir;
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0755, true)) {
            echo "✓ Created: $dir\n";
            $fixed++;
        } else {
            $errors[] = "Failed to create: $dir";
        }
    } else {
        echo "✓ Exists: $dir\n";
    }
    // Ensure writable
    if (is_dir($fullPath) && !is_writable($fullPath)) {
        if (chmod($fullPath, 0755)) {
            echo "  → Fixed permissions\n";
        } else {
            $errors[] = "Not writable: $dir";
        }
    }
}

// ── 2. Clear all cached files ──
echo "\n=== Clearing Laravel Caches ===\n";

// Clear compiled views
$viewsDir = $root . '/storage/framework/views';
if (is_dir($viewsDir)) {
    $files = glob($viewsDir . '/*');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file) && basename($file) !== '.gitignore') {
            if (unlink($file)) $count++;
        }
    }
    echo "✓ Cleared $count compiled view files\n";
    $fixed++;
}

// Clear cache files
$cacheDir = $root . '/storage/framework/cache';
if (is_dir($cacheDir)) {
    $count = 0;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($items as $item) {
        if ($item->isFile() && basename($item->getFilename()) !== '.gitignore') {
            if (unlink($item->getRealPath())) $count++;
        }
    }
    echo "✓ Cleared $count cache files\n";
    $fixed++;
}

// Clear session files
$sessionsDir = $root . '/storage/framework/sessions';
if (is_dir($sessionsDir)) {
    $files = glob($sessionsDir . '/*');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file) && basename($file) !== '.gitignore') {
            if (unlink($file)) $count++;
        }
    }
    echo "✓ Cleared $count session files\n";
}

// ── 3. Delete bootstrap cache files (will be regenerated) ──
echo "\n=== Clearing Bootstrap Cache ===\n";
$bootstrapCache = $root . '/bootstrap/cache';
$cacheFiles = glob($bootstrapCache . '/*.php');
$bcCount = 0;
foreach ($cacheFiles as $file) {
    if (is_file($file) && basename($file) !== '.gitignore') {
        $name = basename($file);
        if (unlink($file)) {
            echo "✓ Deleted: bootstrap/cache/$name\n";
            $bcCount++;
        }
    }
}
if ($bcCount === 0) {
    echo "  No bootstrap cache files to clear\n";
}

// ── 4. Test if Laravel can boot now ──
echo "\n=== Testing Laravel After Fix ===\n";
try {
    // Use include instead of require_once so it always returns the app
    $app = null;
    $appFile = $root . '/bootstrap/app.php';
    
    // Clear any opcache for this file
    if (function_exists('opcache_invalidate')) {
        opcache_invalidate($appFile, true);
        opcache_invalidate($root . '/vendor/autoload.php', true);
    }
    
    // Boot Laravel fresh
    $app = require $appFile;
    
    if ($app instanceof \Illuminate\Foundation\Application) {
        echo "✓ Laravel app bootstrapped successfully\n";
        
        // Try to resolve the view service
        try {
            $viewFactory = $app->make('view');
            echo "✓ View service resolved: " . get_class($viewFactory) . "\n";
        } catch (\Throwable $e) {
            echo "✗ View service failed: " . $e->getMessage() . "\n";
            $errors[] = "View service: " . $e->getMessage();
        }
        
        // Try to render a simple view
        try {
            $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
            $request = \Illuminate\Http\Request::create('/', 'GET');
            $response = $kernel->handle($request);
            echo "✓ Homepage response status: " . $response->getStatusCode() . "\n";
            $kernel->terminate($request, $response);
        } catch (\Throwable $e) {
            echo "✗ Homepage request failed: " . get_class($e) . "\n";
            echo "  Message: " . $e->getMessage() . "\n";
            echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
            
            // Get the previous exception (the real cause)
            $prev = $e->getPrevious();
            if ($prev) {
                echo "  Caused by: " . get_class($prev) . "\n";
                echo "  Message: " . $prev->getMessage() . "\n";
                echo "  File: " . $prev->getFile() . ":" . $prev->getLine() . "\n";
            }
        }
    } else {
        echo "✗ Bootstrap returned: " . gettype($app) . "\n";
    }
} catch (\Throwable $e) {
    echo "✗ Bootstrap failed: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// ── 5. Check .env file ──
echo "\n=== Environment Check ===\n";
$envFile = $root . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    echo "✓ .env file exists\n";
    
    // Check APP_KEY
    if (preg_match('/APP_KEY=(.+)/', $envContent, $m)) {
        $key = trim($m[1]);
        if (str_starts_with($key, 'base64:')) {
            echo "✓ APP_KEY is set\n";
        } else {
            echo "✗ APP_KEY looks invalid: $key\n";
            $errors[] = "APP_KEY may be invalid";
        }
    } else {
        echo "✗ APP_KEY not found in .env\n";
        $errors[] = "APP_KEY missing from .env";
    }
    
    // Check APP_URL
    if (preg_match('/APP_URL=(.+)/', $envContent, $m)) {
        $url = trim($m[1]);
        echo "  APP_URL: $url\n";
        if (str_contains($url, 'localhost')) {
            echo "  ⚠ APP_URL is set to localhost — this causes redirect issues on cPanel\n";
        }
    }
    
    // Check DB connection
    if (preg_match('/DB_DATABASE=(.+)/', $envContent, $m)) {
        echo "  DB_DATABASE: " . trim($m[1]) . "\n";
    }
    if (preg_match('/DB_HOST=(.+)/', $envContent, $m)) {
        echo "  DB_HOST: " . trim($m[1]) . "\n";
    }
} else {
    echo "✗ .env file NOT found!\n";
    $errors[] = ".env file is missing";
}

// ── Summary ──
echo "\n═══════════════════════════════════════\n";
echo "Fixed: $fixed item(s)\n";
if ($errors) {
    echo "Errors:\n";
    foreach ($errors as $err) {
        echo "  ✗ $err\n";
    }
}
echo "═══════════════════════════════════════\n\n";

echo "⚠️ DELETE this file after fixing!\n";
echo "</pre>";
