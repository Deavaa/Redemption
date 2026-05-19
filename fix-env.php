<?php
/**
 * EMERGENCY FIX SCRIPT - Run this directly with PHP
 * This fixes the .env file WITHOUT using Laravel (which is broken)
 * 
 * Usage:  php fix-env.php
 */

$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    echo "ERROR: .env file not found at $envPath\n";
    echo "Run:  copy .env.example .env\n";
    echo "Then: php artisan key:generate\n";
    exit(1);
}

$content = file_get_contents($envPath);
$fixes = [];

// 1. DB_CONNECTION=mysql (NOT sqlite)
if (preg_match('/^DB_CONNECTION=(.*)$/m', $content, $m)) {
    if (trim($m[1]) !== 'mysql') {
        $content = preg_replace('/^DB_CONNECTION=.*$/m', 'DB_CONNECTION=mysql', $content);
        $fixes[] = "DB_CONNECTION=mysql (was: " . trim($m[1]) . ")";
    }
}

// 2. SESSION_DRIVER=file (NOT database)
if (preg_match('/^SESSION_DRIVER=(.*)$/m', $content, $m)) {
    if (trim($m[1]) !== 'file') {
        $content = preg_replace('/^SESSION_DRIVER=.*$/m', 'SESSION_DRIVER=file', $content);
        $fixes[] = "SESSION_DRIVER=file (was: " . trim($m[1]) . ")";
    }
}

// 3. SESSION_DOMAIN= empty (NOT "null" or "localhost")
if (preg_match('/^SESSION_DOMAIN=(.*)$/m', $content, $m)) {
    if (trim($m[1]) !== '') {
        $content = preg_replace('/^SESSION_DOMAIN=.*$/m', 'SESSION_DOMAIN=', $content);
        $fixes[] = "SESSION_DOMAIN= (was: " . trim($m[1]) . ")";
    }
}

// 4. Add SESSION_SECURE_COOKIE=false if missing
if (!preg_match('/^SESSION_SECURE_COOKIE=/m', $content)) {
    $content .= "\nSESSION_SECURE_COOKIE=false";
    $fixes[] = "SESSION_SECURE_COOKIE=false (added)";
} else {
    $content = preg_replace('/^SESSION_SECURE_COOKIE=.*$/m', 'SESSION_SECURE_COOKIE=false', $content);
    $fixes[] = "SESSION_SECURE_COOKIE=false";
}

// 5. Add SESSION_SAME_SITE=lax if missing
if (!preg_match('/^SESSION_SAME_SITE=/m', $content)) {
    $content .= "\nSESSION_SAME_SITE=lax";
    $fixes[] = "SESSION_SAME_SITE=lax (added)";
} else {
    $content = preg_replace('/^SESSION_SAME_SITE=.*$/m', 'SESSION_SAME_SITE=lax', $content);
    $fixes[] = "SESSION_SAME_SITE=lax";
}

// Save .env
file_put_contents($envPath, $content);

// Delete cached config (this is CRITICAL - cached config overrides .env)
$cachedConfig = __DIR__ . '/bootstrap/cache/config.php';
if (file_exists($cachedConfig)) {
    unlink($cachedConfig);
    $fixes[] = "DELETED bootstrap/cache/config.php (cached config)";
}

// Clear session files
$sessionDir = __DIR__ . '/storage/framework/sessions';
if (is_dir($sessionDir)) {
    $files = glob($sessionDir . '/*');
    foreach ($files as $file) {
        if (is_file($file) && basename($file) !== '.gitignore') {
            unlink($file);
        }
    }
    $fixes[] = "Cleared session files";
}

echo "========================================\n";
echo "  EMERGENCY .env FIX\n";
echo "========================================\n\n";

if (empty($fixes)) {
    echo "  .env is already correct!\n";
} else {
    echo "  Applied " . count($fixes) . " fix(es):\n\n";
    foreach ($fixes as $fix) {
        echo "  ✓ $fix\n";
    }
}

echo "\n========================================\n";
echo "  Now restart Apache in XAMPP\n";
echo "  Then try logging in again.\n";
echo "========================================\n";
