<?php

/**
 * ──────────────────────────────────────────────────────────────
 * ROOT INDEX.PHP — Makes Laravel work WITHOUT /public in the URL
 * ──────────────────────────────────────────────────────────────
 * Place this file in the DOCUMENT ROOT (public_html or htdocs)
 * alongside the .htaccess file.
 *
 * IMPORTANT: This is the SAME file as the root index.php.
 * On shared hosting, copy this to your document root.
 * ──────────────────────────────────────────────────────────────
 */

// Session GC override — prevent premature session expiration on shared hosting
@ini_set('session.gc_maxlifetime', 28800);
@ini_set('session.gc_probability', 0);
@ini_set('session.gc_divisor', 1);
@ini_set('session.cookie_lifetime', 28800);

// ── FIX: Correct SCRIPT_NAME for subdirectory installations ──
// Apache's mod_rewrite can change SCRIPT_NAME when routing through
// .htaccess, which causes Laravel to generate incorrect URLs.
// We fix this by calculating the CORRECT SCRIPT_NAME from
// SCRIPT_FILENAME and DOCUMENT_ROOT (not affected by mod_rewrite).
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
