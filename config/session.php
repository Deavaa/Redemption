<?php

/**
 * ──────────────────────────────────────────────────────────────────
 * SESSION CONFIGURATION
 * ──────────────────────────────────────────────────────────────────
 *
 * All values are hard-coded to prevent misconfiguration from .env.
 *
 * KEY POINTS:
 * - Driver is 'database' (immune to PHP's garbage collection)
 * - AppServiceProvider may override to 'file' if sessions table
 *   doesn't exist (with PHP GC disabled in index.php and .user.ini)
 * - Cookie name is 'redemption_session' — MUST be consistent
 * - Lottery is [0, 1000] = 0% chance of session cleanup
 *   (We handle cleanup manually or let sessions naturally expire)
 *
 * ──────────────────────────────────────────────────────────────────
 */

return [

    'driver' => env('SESSION_DRIVER', 'database'),

    'lifetime' => 480,

    'expire_on_close' => false,

    'encrypt' => false,

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION', null),

    'table' => 'sessions',

    'store' => env('SESSION_STORE', null),

    // DISABLE Laravel's session GC entirely.
    // [0, 1000] means 0% chance of cleanup on any request.
    // Session cleanup is handled by the session.lifetime setting
    // and manual cleanup, not by random garbage collection.
    // This prevents any chance of active sessions being cleaned up.
    'lottery' => [0, 1000],

    // Cookie name — MUST match what AppServiceProvider sets.
    // Never change this without updating AppServiceProvider too.
    'cookie' => 'redemption_session',

    'path' => '/',

    'domain' => env('SESSION_DOMAIN', null),

    'secure' => env('SESSION_SECURE_COOKIE', false),

    'http_only' => true,

    'same_site' => 'lax',

    'partitioned' => false,

];
