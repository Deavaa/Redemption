<?php

/**
 * ──────────────────────────────────────────────────────────────
 * PUBLIC INDEX.PHP — Standard Laravel entry point
 * ──────────────────────────────────────────────────────────────
 * This is the standard Laravel entry point when accessing via
 * the /public URL (e.g. http://localhost/Redemption/public/).
 *
 * For subdirectory hosting WITHOUT /public, the root index.php
 * is used instead (see ../index.php).
 *
 * This file is still needed for:
 * - Direct /public access (legacy/fallback)
 * - artisan serve (development server)
 * ──────────────────────────────────────────────────────────────
 */

// Session GC override — safety net
@ini_set('session.gc_maxlifetime', 28800);
@ini_set('session.gc_probability', 0);
@ini_set('session.gc_divisor', 1);
@ini_set('session.cookie_lifetime', 28800);

// ── UPLOAD LIMITS — force increase at runtime ──
// These ini_set calls run BEFORE Laravel boots, so the ValidatePostSize
// middleware sees the new limits. Note: upload_max_filesize and
// post_max_size are PHP_INI_PERDIR — they can only be set in php.ini,
// .user.ini, or .htaccess, NOT at runtime. But we try anyway as some
// hosts allow it. The .htaccess and .user.ini are the primary fix.
@ini_set('memory_limit', '256M');
@ini_set('max_execution_time', 300);
@ini_set('max_input_time', 120);
// These two may not work at runtime (PHP_INI_PERDIR) but we try
@ini_set('upload_max_filesize', '60M');
@ini_set('post_max_size', '65M');

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
