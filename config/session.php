<?php

/**
 * ──────────────────────────────────────────────────────────────────
 * SESSION CONFIGURATION — ALL VALUES HARD-CODED
 * ──────────────────────────────────────────────────────────────────
 *
 * This file contains ZERO env() calls. Every value is hard-coded
 * to prevent misconfiguration from a broken .env file.
 *
 * Previous issues:
 * - SESSION_DRIVER=database in .env caused SQLite errors
 * - SESSION_SECURE_COOKIE=true in .env broke cookies on XAMPP
 * - SESSION_DOMAIN=null (literal string) in .env broke cookies
 * - SESSION_ENCRYPT=true with wrong APP_KEY corrupted sessions
 * - APP_URL with wrong path set incorrect cookie path
 *
 * If you need to change any session setting, edit this file directly.
 * ──────────────────────────────────────────────────────────────────
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | HARD-CODED to 'file'. This app uses file-based sessions.
    | The .env SESSION_DRIVER value is IGNORED.
    |
    */

    'driver' => 'file',

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    |
    | Session lifetime in minutes. 480 = 8 hours.
    | Extended from 120 (2h) because mark entry sessions can be long.
    |
    */

    'lifetime' => 480,

    /*
    |--------------------------------------------------------------------------
    | Expire on Close
    |--------------------------------------------------------------------------
    |
    | HARD-CODED to false. If true, the session would expire when the
    | browser window is closed, causing frequent logouts.
    |
    */

    'expire_on_close' => false,

    /*
    |--------------------------------------------------------------------------
    | Session Encryption
    |--------------------------------------------------------------------------
    |
    | HARD-CODED to false. Encrypting sessions with a missing or wrong
    | APP_KEY would corrupt all session data and cause 419 CSRF errors.
    |
    */

    'encrypt' => false,

    /*
    |--------------------------------------------------------------------------
    | Session File Location
    |--------------------------------------------------------------------------
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Connection (unused — driver is 'file')
    |--------------------------------------------------------------------------
    */

    'connection' => null,

    /*
    |--------------------------------------------------------------------------
    | Session Database Table (unused — driver is 'file')
    |--------------------------------------------------------------------------
    */

    'table' => 'sessions',

    /*
    |--------------------------------------------------------------------------
    | Session Cache Store (unused — driver is 'file')
    |--------------------------------------------------------------------------
    */

    'store' => null,

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
    |
    | HARD-CODED to 'redemption_session'. Using a fixed, known name
    | ensures consistency regardless of APP_NAME or SESSION_COOKIE
    | values in .env. The old cookie name (based on APP_NAME) could
    | conflict with previously set cookies in the browser.
    |
    */

    'cookie' => 'redemption_session',

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Path
    |--------------------------------------------------------------------------
    |
    | HARD-CODED to '/'. This is the most permissive path and ensures
    | the session cookie is sent for ALL requests, including:
    |   - https://localhost/Redemption/public/login
    |   - https://localhost/Redemption/public/admin/dashboard
    |   - Any subdirectory or path under the domain
    |
    | Previously, this was auto-detected from APP_URL, which could
    | result in '/Redemption/public' — but if APP_URL was wrong,
    | the cookie path would be wrong and the browser would never
    | send the cookie back, causing 419 Page Expired errors.
    |
    */

    'path' => '/',

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Domain
    |--------------------------------------------------------------------------
    |
    | HARD-CODED to null (no domain restriction).
    | Setting SESSION_DOMAIN=null (the literal string "null") in .env
    | broke cookies — the browser rejected cookies with domain="null".
    |
    */

    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Cookies
    |--------------------------------------------------------------------------
    |
    | HARD-CODED to false.
    | XAMPP uses self-signed HTTPS certificates. While the browser CAN
    | accept these, some browsers may not send Secure cookies properly
    | with untrusted certificates. Setting secure=false ensures the
    | cookie is always sent, even over plain HTTP.
    |
    */

    'secure' => false,

    /*
    |--------------------------------------------------------------------------
    | HTTP Access Only
    |--------------------------------------------------------------------------
    |
    | When true, JavaScript cannot access the cookie. This is a security
    | measure against XSS attacks. Set to true (the safe default).
    |
    */

    'http_only' => true,

    /*
    |--------------------------------------------------------------------------
    | Same-Site Cookies
    |--------------------------------------------------------------------------
    |
    | HARD-CODED to 'lax'. This provides good security while allowing
    | normal top-level navigation to work. 'strict' would be too
    | restrictive, and 'none' would require the Secure flag.
    |
    */

    'same_site' => 'lax',

    /*
    |--------------------------------------------------------------------------
    | Partitioned Cookies
    |--------------------------------------------------------------------------
    |
    | HARD-CODED to false. Partitioned cookies are a new CHIPS feature
    | that should not be enabled unless explicitly needed.
    |
    */

    'partitioned' => false,

];
