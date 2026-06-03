<?php

/**
 * ──────────────────────────────────────────────────────────────
 * ROOT INDEX.PHP for cPanel / Shared Hosting
 * ──────────────────────────────────────────────────────────────
 * Place this file in the DOCUMENT ROOT (public_html or htdocs)
 * alongside the .htaccess file.
 * ──────────────────────────────────────────────────────────────
 */

// Session GC override — prevent premature session expiration on shared hosting
@ini_set('session.gc_maxlifetime', 28800);
@ini_set('session.gc_probability', 0);
@ini_set('session.gc_divisor', 1);
@ini_set('session.cookie_lifetime', 28800);

// ── CRITICAL: Set LARAVEL_BASE_PATH for subdirectory detection ──
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$appRoot = __DIR__;
if ($documentRoot && str_starts_with($appRoot, $documentRoot)) {
    $basePath = substr($appRoot, strlen($documentRoot));
    $basePath = str_replace('\\', '/', $basePath); // Windows backslash fix

    // Case-fix: match the case from REQUEST_URI
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
    if ($basePath !== '' && $uriPath !== '' && stripos($uriPath, $basePath) === 0) {
        $basePath = substr($uriPath, 0, strlen($basePath));
    }

    $_SERVER['SCRIPT_NAME'] = $basePath . '/index.php';
    $_SERVER['PHP_SELF'] = $basePath . '/index.php';
    $_SERVER['LARAVEL_BASE_PATH'] = $basePath;

    // Auto-detect APP_URL and set before Laravel boots
    $httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || ($_SERVER['SERVER_PORT'] ?? '') === '443';
    $scheme = $isHttps ? 'https' : 'http';
    $detectedUrl = $scheme . '://' . $httpHost . $basePath;

    $_ENV['APP_URL'] = $detectedUrl;
    $_SERVER['APP_URL'] = $detectedUrl;
    putenv('APP_URL=' . $detectedUrl);
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
