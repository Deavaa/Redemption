<?php

/**
 * ──────────────────────────────────────────────────────────────
 * ROOT INDEX.PHP — Makes Laravel work WITHOUT /public in the URL
 * ──────────────────────────────────────────────────────────────
 *
 * HOW THIS WORKS:
 * This file sits in the project root (e.g., htdocs/Redemption/).
 * It auto-detects the subdirectory from the filesystem, fixes
 * $_SERVER['SCRIPT_NAME'] so Laravel knows the correct base path,
 * then delegates to public/index.php.
 *
 * WHY SCRIPT_NAME MATTERS:
 * Laravel uses Symfony's Request which reads SCRIPT_NAME to detect
 * the base path. If SCRIPT_NAME = /Redemption/index.php, then
 * base path = /Redemption, and ALL generated URLs include /Redemption.
 *
 * PROBLEM THIS FIXES:
 * On some Apache/XAMPP configs, SCRIPT_NAME might be wrong — e.g.,
 *   - /index.php (missing subdirectory)
 *   - /Redemption/public/index.php (includes /public)
 *   - /redemption/index.php (wrong case on Windows)
 *
 * We fix it by computing the CORRECT path from __DIR__ (which is
 * always the directory of this file) relative to DOCUMENT_ROOT.
 *
 * This approach does NOT depend on APP_URL or .env at all!
 * ──────────────────────────────────────────────────────────────
 */

// ── Session GC override — prevent premature session expiration ──
@ini_set('session.gc_maxlifetime', 28800);
@ini_set('session.gc_probability', 0);
@ini_set('session.gc_divisor', 1);
@ini_set('session.cookie_lifetime', 28800);

// ── AUTO-DETECT subdirectory from filesystem ──
// This is the MOST RELIABLE way to determine the base path.
// __DIR__ is always the directory containing this file.
// DOCUMENT_ROOT is the Apache document root (e.g., C:/xampp/htdocs).
// The difference is the subdirectory path (e.g., /Redemption).
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$currentDir = realpath(__DIR__);

if ($documentRoot && $currentDir && str_starts_with($currentDir, $documentRoot)) {
    // Compute the subdirectory path
    $basePath = substr($currentDir, strlen($documentRoot));
    $basePath = str_replace('\\', '/', $basePath); // Fix Windows backslashes

    // Remove trailing slash (keep leading slash)
    $basePath = rtrim($basePath, '/');

    // Force SCRIPT_NAME and PHP_SELF to the correct value
    // This is what Laravel/Symfony reads to detect the base path
    $_SERVER['SCRIPT_NAME'] = $basePath . '/index.php';
    $_SERVER['PHP_SELF'] = $basePath . '/index.php';

    // Also set APP_URL in the environment so Laravel's config
    // picks up the correct value regardless of what's in .env
    $httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || ($_SERVER['SERVER_PORT'] ?? '') === '443';
    $scheme = $https ? 'https' : 'http';
    $detectedUrl = $scheme . '://' . $httpHost . $basePath;

    // Set in all places Laravel might read from
    $_ENV['APP_URL'] = $detectedUrl;
    $_SERVER['APP_URL'] = $detectedUrl;
    putenv('APP_URL=' . $detectedUrl);
}

// ── Bootstrap Laravel via public/index.php ──
require __DIR__ . '/public/index.php';
