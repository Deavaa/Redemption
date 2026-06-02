<?php

/**
 * SESSION CONFIGURATION — safe_file driver with NoGarbageSessionHandler
 *
 * Uses 'safe_file' driver that wraps Laravel's file handler with
 * NoGarbageSessionHandler. This makes PHP's gc() a NO-OP so sessions
 * can NEVER be deleted by garbage collection.
 *
 * Cookie name changed to 'redemption_session_v4' to avoid conflicts
 * with old session cookies from previous driver attempts.
 */

return [

    'driver' => env('SESSION_DRIVER', 'safe_file'),

    'lifetime' => env('SESSION_LIFETIME', 480),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    'encrypt' => env('SESSION_ENCRYPT', false),

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => 'sessions',

    'store' => env('SESSION_STORE'),

    // 0% chance of Laravel's own GC running
    'lottery' => [0, 1000],

    // NEW cookie name to avoid conflicts with old driver cookies
    'cookie' => env('SESSION_COOKIE', 'redemption_session_v4'),

    'path' => env('SESSION_PATH', '/'),

    'domain' => env('SESSION_DOMAIN'),

    'secure' => env('SESSION_SECURE_COOKIE', false),

    'http_only' => env('SESSION_HTTP_ONLY', true),

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
