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

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | DATABASE driver is the ONLY reliable option on XAMPP.
    | File-based sessions are killed by PHP's native garbage collection
    | despite all ini_set overrides. Database sessions give Laravel
    | full control over session lifecycle — no PHP GC interference.
    |
    | Supported: "file", "cookie", "database", "apc",
    |            "memcached", "redis", "dynamodb", "array"
    |
    | DO NOT use "safe_file" or "file" — they WILL expire on XAMPP.
    */
    'driver' => env('SESSION_DRIVER', 'database'),

    // On local/dev environments, boost session lifetime to 24 hours so the
    // user never gets logged out mid-work due to slow keepalives or
    // transient DB hiccups that are common on XAMPP / php artisan serve.
    'lifetime' => env('SESSION_LIFETIME', app()->environment('local', 'testing') ? 1440 : 480),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    'encrypt' => env('SESSION_ENCRYPT', false),

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => 'sessions',

    'store' => env('SESSION_STORE'),

    // 2% lottery for Laravel's own GC (cleans expired DB session rows)
    // This is SAFE because it only deletes sessions where last_activity
    // is older than session.lifetime — it does NOT kill active sessions.
    'lottery' => [2, 100],

    // New cookie name v5 — avoids conflicts with old file-driver cookies
    'cookie' => env('SESSION_COOKIE', 'redemption_session_v5'),

    'path' => env('SESSION_PATH', '/'),

    'domain' => env('SESSION_DOMAIN'),

    'secure' => env('SESSION_SECURE_COOKIE', false),

    'http_only' => env('SESSION_HTTP_ONLY', true),

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
