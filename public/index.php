<?php

// ============================================================
// CRITICAL: Override PHP's native session garbage collection
// BEFORE Laravel boots. This must be at the TOP of index.php.
//
// On XAMPP, PHP's session.gc_maxlifetime is often set to 300
// seconds (5 minutes) or 1440 seconds (24 minutes). This causes
// session files to be deleted regardless of Laravel's lifetime.
//
// Setting gc_probability=0 DISABLES PHP's garbage collector
// entirely. Laravel handles its own session cleanup via the
// lottery mechanism, which only affects expired sessions.
//
// These ini_set calls must run BEFORE session_start() is called
// by Laravel's StartSession middleware.
// ============================================================
@ini_set('session.gc_maxlifetime', 28800);    // 8 hours in seconds
@ini_set('session.gc_probability', 0);         // Disable PHP GC completely
@ini_set('session.gc_divisor', 1);             // Backup: if probability > 0, chance = 0/1 = 0%
@ini_set('session.cookie_lifetime', 28800);   // Cookie lives for 8 hours
@ini_set('session.use_strict_mode', 0);        // Don't reject uninitialized session IDs

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
