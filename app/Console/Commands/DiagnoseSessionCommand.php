<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DiagnoseSessionCommand extends Command
{
    protected $signature = 'session:diagnose';
    protected $description = 'Diagnose session problems and show what\'s wrong';

    public function handle()
    {
        $this->info('=== Session Diagnostic Report ===');
        $this->newLine();

        $problems = 0;

        // 1. Check session driver
        $driver = config('session.driver');
        $this->line("Session driver: <info>{$driver}</info>");
        if ($driver !== 'file') {
            $this->error("  PROBLEM: Session driver should be 'file' but is '{$driver}'");
            $this->warn("  FIX: Run: php artisan config:clear");
            $problems++;
        }

        // 2. Check database connection
        $dbConnection = config('database.default');
        $this->line("Database connection: <info>{$dbConnection}</info>");
        if ($dbConnection !== 'mysql') {
            $this->error("  PROBLEM: Database connection should be 'mysql' but is '{$dbConnection}'");
            $this->warn("  FIX: Run: php artisan config:clear");
            $problems++;
        }

        // 3. Check session cookie secure flag
        $secure = config('session.secure');
        $this->line("Session secure cookie: <info>" . ($secure ? 'true' : 'false') . "</info>");
        if ($secure) {
            $this->error("  PROBLEM: Secure cookie is ON — browser won't send cookies over self-signed HTTPS");
            $this->warn("  FIX: Run: php artisan config:clear");
            $problems++;
        }

        // 4. Check session domain
        $domain = config('session.domain');
        $this->line("Session domain: <info>" . ($domain === null ? 'null (correct)' : "'{$domain}'") . "</info>");
        if ($domain !== null && $domain !== '') {
            if (strtolower($domain) === 'null') {
                $this->error("  PROBLEM: Session domain is the STRING 'null' — this breaks cookies!");
            } else {
                $this->error("  PROBLEM: Session domain is set to '{$domain}' — this may break cookies");
            }
            $this->warn("  FIX: Run: php artisan config:clear");
            $problems++;
        }

        // 5. Check session path
        $path = config('session.path');
        $this->line("Session cookie path: <info>{$path}</info>");

        // 6. Check same_site
        $sameSite = config('session.same_site');
        $this->line("SameSite: <info>{$sameSite}</info>");

        // 7. Check session storage directory
        $sessionDir = storage_path('framework/sessions');
        $this->line("Session storage dir: <info>{$sessionDir}</info>");
        if (!is_dir($sessionDir)) {
            $this->error("  PROBLEM: Session storage directory does NOT exist!");
            $this->warn("  FIX: Run: php artisan env:fix");
            $problems++;
        } elseif (!is_writable($sessionDir)) {
            $this->error("  PROBLEM: Session storage directory is NOT writable!");
            $this->warn("  FIX: Run: chmod 755 {$sessionDir}");
            $problems++;
        } else {
            $files = glob($sessionDir . '/*');
            $this->line("  Session files count: " . count($files));
        }

        // 8. Check for cached config
        $cachedConfig = base_path('bootstrap/cache/config.php');
        if (file_exists($cachedConfig)) {
            $this->error("CACHED CONFIG EXISTS: {$cachedConfig}");
            $this->warn("  PROBLEM: Cached config may have old/wrong values!");
            $this->warn("  FIX: Run: php artisan config:clear");
            $problems++;
        } else {
            $this->line("Cached config: <info>none (good)</info>");
        }

        // 9. Check .env for common problems
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            $badSettings = [
                'DB_CONNECTION=sqlite' => 'DB should be mysql, not sqlite',
                'SESSION_DRIVER=database' => 'Session should be file, not database',
                'SESSION_SECURE_COOKIE=true' => 'Secure cookies break on self-signed HTTPS',
            ];

            foreach ($badSettings as $bad => $reason) {
                if (str_contains($envContent, $bad)) {
                    $this->error(".env has: {$bad} — {$reason}");
                    $this->warn("  FIX: Run: php artisan env:fix");
                    $problems++;
                }
            }

            // Check for SESSION_DOMAIN=null (the string)
            if (preg_match('/^SESSION_DOMAIN=null$/m', $envContent)) {
                $this->error(".env has: SESSION_DOMAIN=null (the STRING 'null') — this breaks cookies!");
                $this->warn("  FIX: Run: php artisan env:fix");
                $problems++;
            }
        }

        $this->newLine();

        if ($problems === 0) {
            $this->info('No problems found! Sessions should be working.');
            $this->info('If still not working, check browser DevTools > Application > Cookies');
            $this->info('to see if the session cookie is being set and sent.');
        } else {
            $this->error("Found {$problems} problem(s)!");
            $this->newLine();
            $this->info('Quick fix — run these commands in order:');
            $this->line('  1. php artisan env:fix');
            $this->line('  2. php artisan config:clear');
            $this->line('  3. php artisan session:diagnose  (verify fixes)');
        }

        return $problems > 0 ? 1 : 0;
    }
}
