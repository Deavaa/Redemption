<?php

/**
 * ──────────────────────────────────────────────────────────────
 * ROOT INDEX.PHP — Makes Laravel work WITHOUT /public in the URL
 * ──────────────────────────────────────────────────────────────
 *
 * HOW THIS WORKS:
 * This file sits in the project root (e.g., htdocs/Redemption/).
 * It delegates to public/index.php BUT first fixes $_SERVER['SCRIPT_NAME']
 * so that Laravel detects the correct base path.
 *
 * WHY THE FIX IS NEEDED:
 * When .htaccess rewrites to this file, Apache sets:
 *   SCRIPT_NAME = /Redemption/index.php  ← CORRECT (no /public)
 *   Laravel detects base path = /Redemption ← CORRECT
 *
 * But in some Apache configurations, if the request gets internally
 * forwarded to public/index.php instead, Apache would set:
 *   SCRIPT_NAME = /Redemption/public/index.php  ← WRONG
 *   Laravel detects base path = /Redemption/public  ← WRONG (404s)
 *
 * The fix below ensures SCRIPT_NAME NEVER includes /public/,
 * regardless of how Apache routes the request.
 * ──────────────────────────────────────────────────────────────
 */

// ── FIX SCRIPT_NAME: Remove /public from the path ──
// This is the SINGLE MOST IMPORTANT line for subdirectory hosting.
// It ensures Laravel's URL generator and route resolver both
// calculate the base path as /Redemption (not /Redemption/public).
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
if (str_contains($scriptName, '/public/')) {
    $_SERVER['SCRIPT_NAME'] = str_replace('/public/', '/', $scriptName);
}
// Also fix PHP_SELF (Symfony uses it as a fallback)
$phpSelf = $_SERVER['PHP_SELF'] ?? '';
if (str_contains($phpSelf, '/public/')) {
    $_SERVER['PHP_SELF'] = str_replace('/public/', '/', $phpSelf);
}

// ── Session GC override — prevent premature session expiration ──
@ini_set('session.gc_maxlifetime', 28800);
@ini_set('session.gc_probability', 0);
@ini_set('session.gc_divisor', 1);
@ini_set('session.cookie_lifetime', 28800);

// ── Bootstrap Laravel via public/index.php ──
// This is the standard Laravel entry point. We delegate to it
// after fixing the server variables above.
require __DIR__ . '/public/index.php';
