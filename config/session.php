<?php

/**
 * SESSION CONFIGURATION
 *
 * Uses 'safe_database' driver — a custom driver that wraps Laravel's
 * database handler with NoGarbageSessionHandler which DISABLES gc().
 *
 * This means PHP's garbage collection can NEVER delete sessions,
 * regardless of php.ini settings. Sessions only expire through
 * Laravel's own session.lifetime check.
 */

return [

    // Custom driver that disables garbage collection
    'driver' => 'safe_database',

    'lifetime' => 480,

    'expire_on_close' => false,

    'encrypt' => false,

    'files' => storage_path('framework/sessions'),

    'connection' => null,

    'table' => 'sessions',

    'store' => null,

    // 0% chance of Laravel's own GC running
    'lottery' => [0, 1000],

    // Cookie name — MUST match AppServiceProvider
    'cookie' => 'redemption_session',

    'path' => '/',

    'domain' => null,

    'secure' => false,

    'http_only' => true,

    'same_site' => 'lax',

    'partitioned' => false,

];
