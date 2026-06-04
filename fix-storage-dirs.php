<?php
/**
 * Fix Homepage 500 Error
 * Creates missing storage directories and sets permissions
 * DELETE this file after fixing!
 */
echo "<h1>Storage Directory Fix</h1><pre>";

$dirs = [
    __DIR__ . '/storage',
    __DIR__ . '/storage/framework',
    __DIR__ . '/storage/framework/views',
    __DIR__ . '/storage/framework/cache',
    __DIR__ . '/storage/framework/sessions',
    __DIR__ . '/storage/framework/testing',
    __DIR__ . '/storage/logs',
    __DIR__ . '/storage/app',
    __DIR__ . '/storage/app/public',
    __DIR__ . '/storage/app/private',
    __DIR__ . '/storage/app/public/downloads',
    __DIR__ . '/bootstrap',
    __DIR__ . '/bootstrap/cache',
];

$fixed = 0;
$errors = [];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✓ Created directory: $dir\n";
            $fixed++;
        } else {
            $errors[] = "Failed to create: $dir";
            echo "✗ Failed to create: $dir\n";
        }
    } else {
        echo "✓ Directory exists: $dir\n";
    }
    
    // Check writability
    if (is_dir($dir) && !is_writable($dir)) {
        if (chmod($dir, 0755)) {
            echo "  → Fixed permissions (0755)\n";
            $fixed++;
        } else {
            $errors[] = "Not writable and chmod failed: $dir";
            echo "  ✗ Not writable! chmod failed.\n";
        }
    }
}

// Write test file to verify views directory works
$testFile = __DIR__ . '/storage/framework/views/.write-test';
if (@file_put_contents($testFile, 'test')) {
    echo "\n✓ storage/framework/views is writable\n";
    unlink($testFile);
} else {
    $errors[] = "storage/framework/views is NOT writable — this causes the 500 error!";
    echo "\n✗ storage/framework/views is NOT writable!\n";
}

// Write test to bootstrap/cache
$testFile2 = __DIR__ . '/bootstrap/cache/.write-test';
if (@file_put_contents($testFile2, 'test')) {
    echo "✓ bootstrap/cache is writable\n";
    unlink($testFile2);
} else {
    $errors[] = "bootstrap/cache is NOT writable!";
    echo "✗ bootstrap/cache is NOT writable!\n";
}

// Check if the compiled view cache needs clearing
$viewCache = glob(__DIR__ . '/storage/framework/views/*.php');
echo "\nCompiled views in cache: " . count($viewCache) . "\n";
if (count($viewCache) > 100) {
    echo "Clearing stale view cache...\n";
    foreach ($viewCache as $file) {
        @unlink($file);
    }
    echo "✓ Cleared view cache\n";
    $fixed++;
}

echo "\n═══════════════════════════════════════\n";
echo "Fixed: $fixed item(s)\n";
if ($errors) {
    echo "\nErrors:\n";
    foreach ($errors as $err) {
        echo "  ✗ $err\n";
    }
}
echo "═══════════════════════════════════════\n\n";

if (empty($errors)) {
    echo "✅ All storage directories are ready!\n";
    echo "Try visiting https://redemption.byethost4.com/ now\n";
} else {
    echo "⚠️ Some directories couldn't be created or made writable.\n";
    echo "You may need to create them manually in cPanel File Manager.\n";
}

echo "\n⚠️ DELETE this file after fixing!\n";
echo "</pre>";
