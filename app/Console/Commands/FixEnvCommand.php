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

        // Fix DB_CONNECTION
        if (preg_match('/^DB_CONNECTION=(.*)$/m', $content, $m)) {
            if (trim($m[1]) !== 'mysql') {
                $content = preg_replace('/^DB_CONNECTION=.*$/m', 'DB_CONNECTION=mysql', $content);
                $changes[] = 'DB_CONNECTION=mysql (was: ' . trim($m[1]) . ')';
            }
        } else {
            $content = preg_replace('/^(DB_HOST=)/m', "DB_CONNECTION=mysql\n$1", $content);
            $changes[] = 'DB_CONNECTION=mysql (added)';
        }

        // Fix SESSION_DRIVER
        if (preg_match('/^SESSION_DRIVER=(.*)$/m', $content, $m)) {
            if (trim($m[1]) !== 'file') {
                $content = preg_replace('/^SESSION_DRIVER=.*$/m', 'SESSION_DRIVER=file', $content);
                $changes[] = 'SESSION_DRIVER=file (was: ' . trim($m[1]) . ')';
            }
        } else {
            $content .= "\nSESSION_DRIVER=file";
            $changes[] = 'SESSION_DRIVER=file (added)';
        }

        // Fix SESSION_DOMAIN - must be empty, not "null"
        if (preg_match('/^SESSION_DOMAIN=(.*)$/m', $content, $m)) {
            $val = trim($m[1]);
            if ($val !== '' && $val !== '""') {
                $content = preg_replace('/^SESSION_DOMAIN=.*$/m', 'SESSION_DOMAIN=', $content);
                $changes[] = 'SESSION_DOMAIN= (was: ' . $val . ')';
            }
        } else {
            $content .= "\nSESSION_DOMAIN=";
            $changes[] = 'SESSION_DOMAIN= (added)';
        }

        // Fix SESSION_SECURE_COOKIE
        if (preg_match('/^SESSION_SECURE_COOKIE=(.*)$/m', $content, $m)) {
            if (trim($m[1]) !== 'false') {
                $content = preg_replace('/^SESSION_SECURE_COOKIE=.*$/m', 'SESSION_SECURE_COOKIE=false', $content);
                $changes[] = 'SESSION_SECURE_COOKIE=false (was: ' . trim($m[1]) . ')';
            }
        } else {
            $content .= "\nSESSION_SECURE_COOKIE=false";
            $changes[] = 'SESSION_SECURE_COOKIE=false (added)';
        }

        // Fix SESSION_SAME_SITE
        if (preg_match('/^SESSION_SAME_SITE=(.*)$/m', $content, $m)) {
            if (trim($m[1]) !== 'lax') {
                $content = preg_replace('/^SESSION_SAME_SITE=.*$/m', 'SESSION_SAME_SITE=lax', $content);
                $changes[] = 'SESSION_SAME_SITE=lax (was: ' . trim($m[1]) . ')';
            }
        } else {
            $content .= "\nSESSION_SAME_SITE=lax";
            $changes[] = 'SESSION_SAME_SITE=lax (added)';
        }

        // Fix SESSION_PATH based on APP_URL
        $path = '/';
        if (preg_match('/^APP_URL=(.*)$/m', $content, $m)) {
            $url = trim($m[1]);
            $parsed = parse_url($url);
            if (isset($parsed['path'])) {
                $path = rtrim($parsed['path'], '/');
            }
        }
        if (preg_match('/^SESSION_PATH=(.*)$/m', $content, $m)) {
            if (trim($m[1]) !== $path) {
                $content = preg_replace('/^SESSION_PATH=.*$/m', 'SESSION_PATH=' . $path, $content);
                $changes[] = 'SESSION_PATH=' . $path . ' (was: ' . trim($m[1]) . ')';
            }
        } else {
            $content .= "\nSESSION_PATH=" . $path;
            $changes[] = 'SESSION_PATH=' . $path . ' (added)';
        }

        if (empty($changes)) {
            $this->info('✓ .env file is already correctly configured!');
        } else {
            file_put_contents($envPath, $content);
            $this->info('Fixed ' . count($changes) . ' setting(s) in .env:');
            foreach ($changes as $change) {
                $this->line('  • ' . $change);
            }
        }

        $this->newLine();
        $this->info('Clearing config cache...');
        $this->call('config:clear');
        $this->call('cache:clear');

        $this->newLine();
        $this->info('Done! Try logging in again.');

        return 0;
    }
}
