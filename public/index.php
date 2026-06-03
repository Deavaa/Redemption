<?php

// ============================================================
// Session GC override — safety net (primary fix is database driver)
// These settings are belt-and-suspenders for the database driver.
// File-based sessions on XAMPP get killed by PHP's native GC
// regardless of these settings. The database driver bypasses
// PHP's file session handling entirely.
// ============================================================
@ini_set('session.gc_maxlifetime', 28800);
@ini_set('session.gc_probability', 0);
@ini_set('session.gc_divisor', 1);
@ini_set('session.cookie_lifetime', 28800);

// ── CRITICAL: Set LARAVEL_BASE_PATH for subdirectory detection ──
// When accessed via /public/index.php, we need to go up one level
// to find the project root, then compare with DOCUMENT_ROOT.
// Example: __DIR__ = C:\xampp\htdocs\Redemption\public
//          DOCUMENT_ROOT = C:\xampp\htdocs
//          LARAVEL_BASE_PATH = /Redemption
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$appRoot = dirname(__DIR__); // Go up from public/ to project root
if ($documentRoot && str_starts_with($appRoot, $documentRoot)) {
    $basePath = substr($appRoot, strlen($documentRoot));
    $basePath = str_replace('\\', '/', $basePath); // Windows backslash fix
    $_SERVER['LARAVEL_BASE_PATH'] = $basePath;
}

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
