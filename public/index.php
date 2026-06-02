<?php

// ============================================================
// Session GC override — safety net (primary fix is cookie driver)
// ============================================================
@ini_set('session.gc_maxlifetime', 28800);
@ini_set('session.gc_probability', 0);
@ini_set('session.gc_divisor', 1);
@ini_set('session.cookie_lifetime', 28800);

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
