<?php

/**
 * ──────────────────────────────────────────────────────────────
 * ROOT INDEX.PHP — Makes Laravel work WITHOUT /public in the URL
 * ──────────────────────────────────────────────────────────────
 * This file goes in the PROJECT ROOT (same level as artisan, .env, etc.)
 * On shared hosting, this is inside public_html/ or htdocs/.
 *
 * It bootstraps Laravel exactly like public/index.php does,
 * but from the project root instead of the public/ subdirectory.
 *
 * CRITICAL: This file also sets LARAVEL_BASE_PATH so Laravel
 * knows the correct subdirectory for URL generation. Without this,
 * route() and redirect() generate wrong URLs on XAMPP subdirectories
 * (e.g. http://localhost/login instead of http://localhost/Redemption/login).
 * ──────────────────────────────────────────────────────────────
 */

// Session GC override — prevent premature session expiration on shared hosting
@ini_set('session.gc_maxlifetime', 28800);
@ini_set('session.gc_probability', 0);
@ini_set('session.gc_divisor', 1);
@ini_set('session.cookie_lifetime', 28800);

// ── CRITICAL: Set LARAVEL_BASE_PATH for subdirectory detection ──
// __DIR__ is ALWAYS correct (it's a compile-time constant), unlike
// SCRIPT_NAME which Apache can modify during .htaccess rewrites.
// Example: __DIR__ = C:\xampp\htdocs\Redemption
//          DOCUMENT_ROOT = C:\xampp\htdocs
//          LARAVEL_BASE_PATH = /Redemption
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$appRoot = __DIR__;
if ($documentRoot && str_starts_with($appRoot, $documentRoot)) {
    $basePath = substr($appRoot, strlen($documentRoot));
    $basePath = str_replace('\\', '/', $basePath); // Windows backslash fix
    $_SERVER['LARAVEL_BASE_PATH'] = $basePath;
}

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request
$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Http\Request;

$app->handleRequest(Request::capture());
