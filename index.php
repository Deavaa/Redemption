<?php

/**
 * ──────────────────────────────────────────────────────────────
 * ROOT INDEX.PHP — Makes Laravel work WITHOUT /public in the URL
 * ──────────────────────────────────────────────────────────────
 *
 * HOW THIS WORKS:
 * This file sits in the project root (e.g., htdocs/Redemption/).
 * It auto-detects the subdirectory, fixes SCRIPT_NAME so Laravel
 * knows the correct base path, then delegates to public/index.php.
 *
 * KEY FIX: Case sensitivity!
 * On Windows/XAMPP, the directory might be "Redemption" but the user
 * accesses "http://localhost/redemption/" (lowercase). Windows is
 * case-insensitive for files, but Laravel/Symfony does CASE-SENSITIVE
 * comparison when stripping the base path from the request URI.
 * If SCRIPT_NAME says /Redemption but REQUEST_URI says /redemption,
 * Symfony can't strip the base path → Laravel sees /redemption/login
 * as the route instead of /login → 404!
 *
 * Solution: We compute the base path from the filesystem, then adjust
 * its case to match REQUEST_URI. This way SCRIPT_NAME always matches
 * the URL case the user actually used.
 * ──────────────────────────────────────────────────────────────
 */

// ── Session GC override — prevent premature session expiration ──
@ini_set('session.gc_maxlifetime', 28800);
@ini_set('session.gc_probability', 0);
@ini_set('session.gc_divisor', 1);
@ini_set('session.cookie_lifetime', 28800);

// ── Class alias: ClassRoom → Classroom (autoloader safety net) ──
// The Composer optimized autoloader classmap may not have "ClassRoom"
// (only "Classroom"). This alias ensures both names work regardless
// of autoloader state. We load the Classroom.php file manually first,
// then register the alias. This is needed on shared hosting where we
// can't run "composer dump-autoload" after renaming classes.
$classroomFile = __DIR__ . '/app/Models/Classroom.php';
if (file_exists($classroomFile)) {
    require_once $classroomFile;
    if (class_exists('App\Models\Classroom', false) && !class_exists('App\Models\ClassRoom', false)) {
        class_alias('App\Models\Classroom', 'App\Models\ClassRoom');
    }
}
// Also check ClassRoom.php in case only that file exists (older deploy)
$classRoomFile = __DIR__ . '/app/Models/ClassRoom.php';
if (file_exists($classRoomFile) && !file_exists($classroomFile)) {
    require_once $classRoomFile;
    if (class_exists('App\Models\ClassRoom', false) && !class_exists('App\Models\Classroom', false)) {
        class_alias('App\Models\ClassRoom', 'App\Models\Classroom');
    }
}

// ── AUTO-DETECT subdirectory from filesystem ──
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$currentDir = realpath(__DIR__);

if ($documentRoot && $currentDir && str_starts_with($currentDir, $documentRoot)) {
    // Compute the subdirectory path from filesystem
    $basePath = substr($currentDir, strlen($documentRoot));
    $basePath = str_replace('\\', '/', $basePath); // Fix Windows backslashes
    $basePath = rtrim($basePath, '/');              // Remove trailing slash

    // ── CASE FIX: Adjust base path case to match REQUEST_URI ──
    // The filesystem might give us /Redemption (capital R) but the
    // user accessed /redemption/ (lowercase). Symfony does case-
    // sensitive comparison, so we must match the URL's case.
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $uriPath = parse_url($requestUri, PHP_URL_PATH) ?: '';

    if ($basePath !== '' && $uriPath !== '') {
        // Case-insensitive match: does the URI start with our base path?
        if (stripos($uriPath, $basePath) === 0) {
            // YES — replace our base path with the case from the URI
            $basePath = substr($uriPath, 0, strlen($basePath));
        }
    }

    // Force SCRIPT_NAME and PHP_SELF to the correct value
    // (with the correct case matching the URL)
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
