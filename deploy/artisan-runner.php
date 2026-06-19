<?php

/**
 * ================================================================
 * ARTISAN WEB RUNNER — DISABLED FOR SECURITY
 * ================================================================
 *
 * This file used to allow running `php artisan migrate:fresh`, `db:seed`,
 * `key:generate`, and other destructive commands via a simple URL with a
 * hardcoded secret (`CHANGE_THIS_SECRET_12345`). If the secret was not
 * changed (very common), anyone with the URL could wipe the database,
 * invalidate all encrypted data, or take the site offline.
 *
 * The file is now disabled. To re-enable temporarily on a trusted host:
 *   1. Rename this file to something unique (e.g. `deploy/run-XYZ.php`).
 *   2. Move the secret to an environment variable or external file that
 *      is NOT checked into git.
 *   3. Wrap the runner in `auth` + `admin` middleware (route it through
 *      Laravel instead of being a standalone PHP file).
 *   4. Remove `migrate:fresh`, `key:generate`, and `db:seed` from the
 *      allow-list unless absolutely necessary.
 *
 * Recommended alternatives:
 *   - Use `php artisan` over SSH when possible.
 *   - Use Laravel Forge / Envoyer / Ploi deployment hooks.
 *   - For cPanel shared hosting: use the cron-job runner with a one-time
 *     trigger and a randomly-named flag file.
 * ================================================================
 */

http_response_code(404);
echo '<h1>404 Not Found</h1>';
exit;
