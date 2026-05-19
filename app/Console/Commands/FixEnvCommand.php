<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixEnvCommand extends Command
{
    protected $signature = 'env:fix';
    protected $description = 'Fix .env file for XAMPP/local development (session, database, security)';

    public function handle()
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $this->error('.env file not found! Copy .env.example to .env first:');
            $this->info('  copy .env.example .env');
            $this->info('  php artisan key:generate');
            return 1;
        }

        $content = file_get_contents($envPath);
        $changes = [];

        // === CRITICAL FIXES ===

        // 1. DB_CONNECTION must be mysql (not sqlite)
        if (preg_match('/^DB_CONNECTION=/m', $content)) {
            $content = preg_replace('/^DB_CONNECTION=.*$/m', 'DB_CONNECTION=mysql', $content, -1, $count);
            if ($count) $changes[] = 'DB_CONNECTION=mysql';
        } else {
            $content .= "\nDB_CONNECTION=mysql";
            $changes[] = 'DB_CONNECTION=mysql (added)';
        }

        // 2. SESSION_DRIVER must be file (not database - no sessions table)
        if (preg_match('/^SESSION_DRIVER=/m', $content)) {
            $content = preg_replace('/^SESSION_DRIVER=.*$/m', 'SESSION_DRIVER=file', $content, -1, $count);
            if ($count) $changes[] = 'SESSION_DRIVER=file';
        } else {
            $content .= "\nSESSION_DRIVER=file";
            $changes[] = 'SESSION_DRIVER=file (added)';
        }

        // 3. SESSION_DOMAIN must be empty (not "null" or "localhost")
        if (preg_match('/^SESSION_DOMAIN=/m', $content)) {
            $content = preg_replace('/^SESSION_DOMAIN=.*$/m', 'SESSION_DOMAIN=', $content, -1, $count);
            if ($count) $changes[] = 'SESSION_DOMAIN= (empty)';
        }

        // 4. SESSION_SECURE_COOKIE must be false for XAMPP
        if (preg_match('/^SESSION_SECURE_COOKIE=/m', $content)) {
            $content = preg_replace('/^SESSION_SECURE_COOKIE=.*$/m', 'SESSION_SECURE_COOKIE=false', $content, -1, $count);
            if ($count) $changes[] = 'SESSION_SECURE_COOKIE=false';
        } else {
            $content .= "\nSESSION_SECURE_COOKIE=false";
            $changes[] = 'SESSION_SECURE_COOKIE=false (added)';
        }

        // 5. SESSION_SAME_SITE must be lax
        if (preg_match('/^SESSION_SAME_SITE=/m', $content)) {
            $content = preg_replace('/^SESSION_SAME_SITE=.*$/m', 'SESSION_SAME_SITE=lax', $content, -1, $count);
            if ($count) $changes[] = 'SESSION_SAME_SITE=lax';
        } else {
            $content .= "\nSESSION_SAME_SITE=lax";
            $changes[] = 'SESSION_SAME_SITE=lax (added)';
        }

        // 6. SESSION_PATH - auto-detect from APP_URL
        $path = '/';
        if (preg_match('/^APP_URL=(.*)$/m', $content, $m)) {
            $url = trim($m[1]);
            $parsed = parse_url($url);
            if (isset($parsed['path'])) {
                $path = rtrim($parsed['path'], '/');
            }
        }
        if (preg_match('/^SESSION_PATH=/m', $content)) {
            $content = preg_replace('/^SESSION_PATH=.*$/m', 'SESSION_PATH=' . $path, $content);
            $changes[] = 'SESSION_PATH=' . $path;
        } else {
            $content .= "\nSESSION_PATH=" . $path;
            $changes[] = 'SESSION_PATH=' . $path . ' (added)';
        }

        // Write updated .env
        file_put_contents($envPath, $content);

        // Ensure session storage directory exists and is writable
        $sessionDir = storage_path('framework/sessions');
        if (!is_dir($sessionDir)) {
            mkdir($sessionDir, 0755, true);
            $changes[] = 'Created sessions directory';
        }
        if (!is_writable($sessionDir)) {
            chmod($sessionDir, 0755);
            $changes[] = 'Fixed sessions directory permissions';
        }

        // Display results
        if (empty($changes)) {
            $this->info('.env is already correct - no changes needed.');
        } else {
            $this->info('Applied ' . count($changes) . ' fix(es):');
            foreach ($changes as $change) {
                $this->line('  - ' . $change);
            }
        }

        // Delete cached config if it exists
        $cachedConfig = base_path('bootstrap/cache/config.php');
        if (file_exists($cachedConfig)) {
            unlink($cachedConfig);
            $this->warn('Deleted cached config.php');
        }

        $this->newLine();
        $this->call('config:clear');
        $this->call('cache:clear');

        $this->newLine();
        $this->info('Done! Sessions now use FILE storage (not database).');
        $this->info('Database connection is now MYSQL (not sqlite).');
        $this->info('Try logging in again now.');

        return 0;
    }
}
