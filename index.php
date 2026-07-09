<?php

/**
 * ──────────────────────────────────────────────────────────────
 * ROOT INDEX.PHP — Makes Laravel work WITHOUT /public in the URL
 * ──────────────────────────────────────────────────────────────
 *
 * This file sits in the project root (e.g., htdocs/Redemption/).
 * It auto-detects the subdirectory, fixes SCRIPT_NAME so Laravel
 * knows the correct base path, then delegates to public/index.php.
 * ──────────────────────────────────────────────────────────────
 */

// ── Session GC override — prevent premature session expiration ──
@ini_set('session.gc_maxlifetime', 28800);
@ini_set('session.gc_probability', 0);
@ini_set('session.gc_divisor', 1);
@ini_set('session.cookie_lifetime', 28800);

// ── UPLOAD LIMITS — force increase at runtime ──
@ini_set('memory_limit', '256M');
@ini_set('max_execution_time', 300);
@ini_set('max_input_time', 120);
@ini_set('upload_max_filesize', '60M');
@ini_set('post_max_size', '65M');

// ── AUTO-DETECT subdirectory from filesystem ──
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$currentDir = realpath(__DIR__);

if ($documentRoot && $currentDir && str_starts_with($currentDir, $documentRoot)) {
    // Compute the subdirectory path from filesystem
    $basePath = substr($currentDir, strlen($documentRoot));
    $basePath = str_replace('\\', '/', $basePath); // Fix Windows backslashes
    $basePath = rtrim($basePath, '/');              // Remove trailing slash

    // ── CASE FIX: Adjust base path case to match REQUEST_URI ──
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $uriPath = parse_url($requestUri, PHP_URL_PATH) ?: '';

    if ($basePath !== '' && $uriPath !== '') {
        if (stripos($uriPath, $basePath) === 0) {
            $basePath = substr($uriPath, 0, strlen($basePath));
        }
    }

    // Force SCRIPT_NAME and PHP_SELF to the correct value
    $_SERVER['SCRIPT_NAME'] = $basePath . '/index.php';
    $_SERVER['PHP_SELF'] = $basePath . '/index.php';

    // Also set APP_URL in the environment so Laravel's config
    // picks up the correct value regardless of what's in .env
    $httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || ($_SERVER['SERVER_PORT'] ?? '') === '443';
    $scheme = $https ? 'https' : 'http';
    $detectedUrl = $scheme . '://' . $httpHost . $basePath;

    $_ENV['APP_URL'] = $detectedUrl;
    $_SERVER['APP_URL'] = $detectedUrl;
    putenv('APP_URL=' . $detectedUrl);
}

// ── Bootstrap Laravel via public/index.php ──
require __DIR__ . '/public/index.php';
