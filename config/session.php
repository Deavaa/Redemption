<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | HARD-CODED to 'file' — this app uses file-based sessions.
    | The .env SESSION_DRIVER value is IGNORED to prevent misconfiguration.
    | If you need a different driver (e.g., redis), change it here.
    |
    */

    'driver' => 'file',

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Session Encryption
    |--------------------------------------------------------------------------
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Session File Location
    |--------------------------------------------------------------------------
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Connection
    |--------------------------------------------------------------------------
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Table
    |--------------------------------------------------------------------------
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Cache Store
    |--------------------------------------------------------------------------
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Session Sweeping Lottery
    |--------------------------------------------------------------------------
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name
    |--------------------------------------------------------------------------
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Path
    |--------------------------------------------------------------------------
    |
    | HARD-CODED auto-detection from APP_URL.
    | The .env SESSION_PATH value is IGNORED to prevent misconfiguration.
    | For subdirectory installs (e.g., XAMPP), this auto-detects the correct
    | path from APP_URL. For example:
    |   APP_URL=https://localhost/Redemption/public → path = /Redemption/public
    |
    */

    'path' => (function () {
        $url = env('APP_URL', 'http://localhost');
        $parsed = parse_url($url);
        return isset($parsed['path']) ? rtrim($parsed['path'], '/') : '/';
    })(),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Domain
    |--------------------------------------------------------------------------
    |
    | HARD-CODED to null (no domain restriction).
    | The .env SESSION_DOMAIN value is IGNORED to prevent misconfiguration.
    | Setting SESSION_DOMAIN=null (the string "null") in .env breaks cookies.
    |
    */

    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Cookies
    |--------------------------------------------------------------------------
    |
    | HARD-CODED to false for XAMPP/local development.
    | The .env SESSION_SECURE_COOKIE value is IGNORED.
    | XAMPP uses self-signed HTTPS certificates — the browser won't send
    | secure cookies because the cert isn't trusted.
    |
    */

    'secure' => false,

    /*
    |--------------------------------------------------------------------------
    | HTTP Access Only
    |--------------------------------------------------------------------------
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Same-Site Cookies
    |--------------------------------------------------------------------------
    |
    | HARD-CODED to 'lax' for XAMPP/local development.
    | The .env SESSION_SAME_SITE value is IGNORED to prevent misconfiguration.
    |
    */

    'same_site' => 'lax',

    /*
    |--------------------------------------------------------------------------
    | Partitioned Cookies
    |--------------------------------------------------------------------------
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
