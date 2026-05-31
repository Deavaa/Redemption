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
 * - File-based sessions expired in <5 min on XAMPP due to PHP's
 *   native session.gc_maxlifetime overriding Laravel's lifetime
 *
 * KEY CHANGE: Switched from 'file' to 'database' driver.
 * The database driver is immune to PHP's native garbage collection,
 * which was deleting session files prematurely on XAMPP.
 * ──────────────────────────────────────────────────────────────────
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | CHANGED to 'database' from 'file'.
    |
    | WHY: On XAMPP, PHP's native session garbage collection
    | (session.gc_maxlifetime) was deleting Laravel's session files
    | in under 5 minutes, causing constant 419 Page Expired errors
    | during mark entry. The database driver stores sessions in MySQL,
    | which is immune to PHP's file-based garbage collection.
    |
    | The sessions table already exists in the database (migration
    | 2026_05_13_200001_create_sessions_table.php).
    |
    */

    'driver' => 'database',

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
    | Session File Location (unused — driver is 'database')
    |--------------------------------------------------------------------------
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Connection
    |--------------------------------------------------------------------------
    |
    | null = use the default MySQL connection from config/database.php.
    | The sessions table uses the same MySQL database as the rest of the app.
    |
    */

    'connection' => null,

    /*
    |--------------------------------------------------------------------------
    | Session Database Table
    |--------------------------------------------------------------------------
    |
    | The MySQL table where sessions are stored.
    | Created by migration 2026_05_13_200001_create_sessions_table.php.
    |
    */

    'table' => 'sessions',

    /*
    |--------------------------------------------------------------------------
    | Session Cache Store (unused — driver is 'database')
    |--------------------------------------------------------------------------
    */

    'store' => null,

    /*
    |--------------------------------------------------------------------------
    | Session Sweeping Lottery
    |--------------------------------------------------------------------------
    |
    | The lottery determines the chance of expired sessions being cleaned up.
    | [2, 100] means 2% chance on each request. With database driver, this
    | only deletes sessions older than 'lifetime' minutes from the DB table,
    | completely bypassing PHP's native session.gc_maxlifetime.
    |
    | Reduced from [2, 100] to [1, 1000] to further reduce the chance of
    | aggressive cleanup that could affect long sessions.
    |
    */

    'lottery' => [1, 1000],

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
    | IMPORTANT: After switching from file to database driver, the
    | cookie name stays the same, so existing sessions will be
    | transparently migrated when users log in again.
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
