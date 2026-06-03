<?php

/**
 * ──────────────────────────────────────────────────────────────
 * ROOT INDEX.PHP — Makes Laravel work WITHOUT /public in the URL
 * ──────────────────────────────────────────────────────────────
 * Place this file in the DOCUMENT ROOT (public_html or htdocs)
 * alongside the .htaccess file.
 *
 * CRITICAL: This file sets LARAVEL_BASE_PATH for subdirectory detection.
 * ──────────────────────────────────────────────────────────────
 */

// Session GC override — prevent premature session expiration on shared hosting
@ini_set('session.gc_maxlifetime', 28800);
@ini_set('session.gc_probability', 0);
@ini_set('session.gc_divisor', 1);
@ini_set('session.cookie_lifetime', 28800);

// NOTE: ClassRoom/Classroom compatibility is handled by app/Models/ClassRoom.php
// which uses require_once + class_alias(). No manual alias needed here.

// ── CRITICAL: Set LARAVEL_BASE_PATH for subdirectory detection ──
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
