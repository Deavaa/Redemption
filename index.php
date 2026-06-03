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
 * Works alongside the original public/index.php — both are valid
 * entry points. The root .htaccess routes all traffic here.
 * ──────────────────────────────────────────────────────────────
 */

// Session GC override — prevent premature session expiration on shared hosting
@ini_set('session.gc_maxlifetime', 28800);
@ini_set('session.gc_probability', 0);
@ini_set('session.gc_divisor', 1);
@ini_set('session.cookie_lifetime', 28800);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode
if (file_exists(__DIR__.'/storage/framework/maintenance.php')) {
    require __DIR__.'/storage/framework/maintenance.php';
}

// Register the Composer autoloader
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
