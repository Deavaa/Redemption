<?php

/**
 * SESSION CONFIGURATION — Cookie Driver
 *
 * Uses the COOKIE driver — the DEFINITIVE fix for session expiration on XAMPP.
 *
 * Session data is stored encrypted in the browser cookie. There is NO
 * server-side state, so PHP's garbage collection CANNOT delete sessions.
 * This makes it 100% immune to php.ini gc_maxlifetime/gc_probability
 * settings that were causing sessions to expire in 5 minutes.
 */

return [

    'driver' => env('SESSION_DRIVER', 'cookie'),

    'lifetime' => env('SESSION_LIFETIME', 480),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    'encrypt' => env('SESSION_ENCRYPT', true),

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => 'sessions',

    'store' => env('SESSION_STORE'),

    'lottery' => [0, 1000],

    'cookie' => env('SESSION_COOKIE', 'redemption_session'),

    'path' => env('SESSION_PATH', '/'),

    'domain' => env('SESSION_DOMAIN'),

    'secure' => env('SESSION_SECURE_COOKIE', false),

    'http_only' => env('SESSION_HTTP_ONLY', true),

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
