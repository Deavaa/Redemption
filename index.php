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

// ── FIX: Correct SCRIPT_NAME for subdirectory installations ──
// Apache's mod_rewrite can change SCRIPT_NAME when routing through
// .htaccess, which causes Laravel to generate incorrect URLs
// (e.g. http://localhost/login instead of http://localhost/Redemption/login).
//
// We fix this by calculating the CORRECT SCRIPT_NAME from SCRIPT_FILENAME
// and DOCUMENT_ROOT, which are NOT affected by mod_rewrite.
//
// Example on XAMPP:
//   SCRIPT_FILENAME = C:/xampp/htdocs/Redemption/index.php
//   DOCUMENT_ROOT   = C:/xampp/htdocs
//   → Correct SCRIPT_NAME = /Redemption/index.php
//   → Laravel detects base path = /Redemption ✅
//
// Example on live hosting (domain root):
//   SCRIPT_FILENAME = /home/user/public_html/index.php
//   DOCUMENT_ROOT   = /home/user/public_html
//   → Correct SCRIPT_NAME = /index.php
//   → Laravel detects base path = / ✅
if (isset($_SERVER['SCRIPT_FILENAME']) && isset($_SERVER['DOCUMENT_ROOT'])) {
    $correctScriptName = str_replace(
        str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),
        '',
        str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'])
    );
    if ($correctScriptName && $correctScriptName !== $_SERVER['SCRIPT_NAME']) {
        $_SERVER['SCRIPT_NAME'] = $correctScriptName;
    }
}

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
