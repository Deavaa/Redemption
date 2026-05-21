<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Nuclear Session Fix Command
 *
 * This command performs a comprehensive reset of all session-related
 * configuration to fix 419 Page Expired errors, SQLite session errors,
 * MissingAppKeyException, and login/logout issues caused by
 * misconfigured .env files or stale cached config.
 *
 * Usage: php artisan session:nuclear-fix
 */
class NuclearSessionFix extends Command
{
    protected $signature = 'session:nuclear-fix';
    protected $description = 'Nuclear fix for session/cookie/419/key errors — resets everything';

    public function handle(): int
    {
        $this->warn('╔══════════════════════════════════════════════════════════╗');
        $this->warn('║        NUCLEAR SESSION FIX — Starting Reset             ║');
        $this->warn('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();

        // ── Step 0: Check APP_KEY ───────────────────────────────────
        $this->info('[0/7] Checking APP_KEY...');
        $envPath = base_path('.env');
        $appKeyMissing = false;

        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            // Check if APP_KEY is empty or just whitespace after the =
            if (preg_match('/^APP_KEY\s*=\s*$/m', $envContent) || !preg_match('/^APP_KEY\s*=\s*base64:/m', $envContent)) {
                $appKeyMissing = true;
                $this->warn('  ✗ APP_KEY is missing or empty in .env file!');
                $this->info('  → Generating new APP_KEY...');
                $this->call('key:generate', ['--force' => true]);
                $this->info('  ✓ APP_KEY generated successfully');
            } else {
                $this->line('  ✓ APP_KEY is set in .env');
            }
        } else {
            $this->error('  ✗ No .env file found!');
            $this->info('  → Creating .env from .env.example...');
            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), $envPath);
                $this->line('  ✓ Created .env from .env.example');
                $this->call('key:generate', ['--force' => true]);
                $this->info('  ✓ APP_KEY generated');
            }
        }

        // ── Step 1: Delete cached config ────────────────────────────
        $this->info('[1/7] Deleting cached config...');
        $cachedConfig = base_path('bootstrap/cache/config.php');
        if (file_exists($cachedConfig)) {
            @unlink($cachedConfig);
            $this->line('  ✓ Deleted bootstrap/cache/config.php');
        } else {
            $this->line('  ✓ No cached config found (good)');
        }

        // ── Step 2: Delete all session files ────────────────────────
        $this->info('[2/7] Deleting all session files...');
        $sessionDir = storage_path('framework/sessions');
        if (is_dir($sessionDir)) {
            $files = glob($sessionDir . '/*');
            $count = 0;
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== '.gitignore') {
                    @unlink($file);
                    $count++;
                }
            }
            $this->line("  ✓ Deleted {$count} session files");
        } else {
            $this->line('  ✓ Session directory does not exist yet');
        }

        // ── Step 3: Create session directory ────────────────────────
        $this->info('[3/7] Ensuring session directory exists...');
        if (!is_dir($sessionDir)) {
            mkdir($sessionDir, 0755, true);
            $this->line('  ✓ Created storage/framework/sessions/');
        } else {
            $this->line('  ✓ Session directory exists');
        }
        // Check writability
        if (is_writable($sessionDir)) {
            $this->line('  ✓ Session directory is writable');
        } else {
            $this->error('  ✗ Session directory is NOT writable! Run: chmod 755 storage/framework/sessions');
        }

        // ── Step 4: Fix .env file ──────────────────────────────────
        $this->info('[4/7] Fixing .env file...');

        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            $fixes = [];

            // Fix SESSION_DRIVER
            if (preg_match('/^SESSION_DRIVER\s*=/m', $envContent)) {
                $envContent = preg_replace('/^SESSION_DRIVER\s*=.*/m', 'SESSION_DRIVER=file', $envContent);
                $fixes[] = 'SESSION_DRIVER=file';
            } else {
                $envContent .= "\nSESSION_DRIVER=file\n";
                $fixes[] = 'SESSION_DRIVER=file (added)';
            }

            // Fix SESSION_SECURE_COOKIE
            if (preg_match('/^SESSION_SECURE_COOKIE\s*=/m', $envContent)) {
                $envContent = preg_replace('/^SESSION_SECURE_COOKIE\s*=.*/m', 'SESSION_SECURE_COOKIE=false', $envContent);
                $fixes[] = 'SESSION_SECURE_COOKIE=false';
            } else {
                $envContent .= "SESSION_SECURE_COOKIE=false\n";
                $fixes[] = 'SESSION_SECURE_COOKIE=false (added)';
            }

            // Fix SESSION_DOMAIN — must be empty or removed
            if (preg_match('/^SESSION_DOMAIN\s*=\s*null\s*$/m', $envContent)) {
                // Replace literal "null" string with empty
                $envContent = preg_replace('/^SESSION_DOMAIN\s*=\s*null\s*$/m', 'SESSION_DOMAIN=', $envContent);
                $fixes[] = 'SESSION_DOMAIN= (was literal string "null" — now empty)';
            } elseif (preg_match('/^SESSION_DOMAIN\s*=/m', $envContent)) {
                $envContent = preg_replace('/^SESSION_DOMAIN\s*=.*/m', 'SESSION_DOMAIN=', $envContent);
                $fixes[] = 'SESSION_DOMAIN= (cleared)';
            }

            // Fix DB_CONNECTION — must be mysql
            if (preg_match('/^DB_CONNECTION\s*=/m', $envContent)) {
                $envContent = preg_replace('/^DB_CONNECTION\s*=.*/m', 'DB_CONNECTION=mysql', $envContent);
                $fixes[] = 'DB_CONNECTION=mysql';
            } else {
                $envContent .= "DB_CONNECTION=mysql\n";
                $fixes[] = 'DB_CONNECTION=mysql (added)';
            }

            // Fix SESSION_ENCRYPT — must be false
            if (preg_match('/^SESSION_ENCRYPT\s*=/m', $envContent)) {
                $envContent = preg_replace('/^SESSION_ENCRYPT\s*=.*/m', 'SESSION_ENCRYPT=false', $envContent);
                $fixes[] = 'SESSION_ENCRYPT=false';
            } else {
                $envContent .= "SESSION_ENCRYPT=false\n";
                $fixes[] = 'SESSION_ENCRYPT=false (added)';
            }

            // Fix SESSION_EXPIRE_ON_CLOSE — must be false
            if (preg_match('/^SESSION_EXPIRE_ON_CLOSE\s*=/m', $envContent)) {
                $envContent = preg_replace('/^SESSION_EXPIRE_ON_CLOSE\s*=.*/m', 'SESSION_EXPIRE_ON_CLOSE=false', $envContent);
                $fixes[] = 'SESSION_EXPIRE_ON_CLOSE=false';
            } else {
                $envContent .= "SESSION_EXPIRE_ON_CLOSE=false\n";
                $fixes[] = 'SESSION_EXPIRE_ON_CLOSE=false (added)';
            }

            // Fix SESSION_COOKIE — force our fixed name
            if (preg_match('/^SESSION_COOKIE\s*=/m', $envContent)) {
                $envContent = preg_replace('/^SESSION_COOKIE\s*=.*/m', 'SESSION_COOKIE=redemption_session', $envContent);
                $fixes[] = 'SESSION_COOKIE=redemption_session';
            } else {
                $envContent .= "SESSION_COOKIE=redemption_session\n";
                $fixes[] = 'SESSION_COOKIE=redemption_session (added)';
            }

            file_put_contents($envPath, $envContent);

            foreach ($fixes as $fix) {
                $this->line("  ✓ {$fix}");
            }
        } else {
            $this->warn('  ⚠ No .env file found! Copy .env.example to .env and configure it.');
        }

        // ── Step 5: Clear Laravel caches ───────────────────────────
        $this->info('[5/7] Clearing Laravel caches...');
        $this->call('config:clear');
        $this->call('cache:clear');
        $this->call('view:clear');
        $this->call('route:clear');
        $this->line('  ✓ All caches cleared');

        // ── Step 6: Fix stale foreign key references ──────────────
        $this->info('[6/7] Fixing stale teacher references in classes...');
        try {
            $staleClasses = \DB::table('classes')
                ->whereNotNull('teacher_id')
                ->whereNotIn('teacher_id', \DB::table('teachers')->pluck('id'))
                ->count();
            $staleSections = \DB::table('sections')
                ->whereNotNull('teacher_id')
                ->whereNotIn('teacher_id', \DB::table('teachers')->pluck('id'))
                ->count();

            if ($staleClasses > 0) {
                \DB::table('classes')
                    ->whereNotNull('teacher_id')
                    ->whereNotIn('teacher_id', \DB::table('teachers')->pluck('id'))
                    ->update(['teacher_id' => null]);
                $this->line("  ✓ Cleaned {$staleClasses} class(es) with non-existent teacher_id");
            } else {
                $this->line('  ✓ No stale teacher references in classes');
            }

            if ($staleSections > 0) {
                \DB::table('sections')
                    ->whereNotNull('teacher_id')
                    ->whereNotIn('teacher_id', \DB::table('teachers')->pluck('id'))
                    ->update(['teacher_id' => null]);
                $this->line("  ✓ Cleaned {$staleSections} section(s) with non-existent teacher_id");
            } else {
                $this->line('  ✓ No stale teacher references in sections');
            }
        } catch (\Throwable $e) {
            $this->warn('  ⚠ Could not check stale references: ' . $e->getMessage());
        }

        // ── Step 7: Verify config at runtime ───────────────────────
        $this->info('[7/7] Verifying runtime config...');
        $this->newLine();

        $checks = [
            ['app.key', 'set', !empty(config('app.key')) ? 'set' : 'EMPTY!'],
            ['session.driver', 'file', config('session.driver')],
            ['session.cookie', 'redemption_session', config('session.cookie')],
            ['session.path', '/', config('session.path')],
            ['session.domain', 'null (PHP)', config('session.domain') === null ? 'null (PHP)' : config('session.domain')],
            ['session.secure', 'false', config('session.secure') ? 'true' : 'false'],
            ['session.same_site', 'lax', config('session.same_site')],
            ['session.encrypt', 'false', config('session.encrypt') ? 'true' : 'false'],
            ['session.expire_on_close', 'false', config('session.expire_on_close') ? 'true' : 'false'],
            ['database.default', 'mysql', config('database.default')],
        ];

        $allOk = true;
        foreach ($checks as [$key, $expected, $actual]) {
            $ok = ($actual === $expected) || ($expected === 'null (PHP)' && $actual === 'null (PHP)');
            $status = $ok ? '✓' : '✗';
            $color = $ok ? 'info' : 'error';
            $this->$color("  {$status} {$key} = {$actual}" . ($ok ? '' : " (expected: {$expected})"));
            if (!$ok) $allOk = false;
        }

        $this->newLine();

        if ($allOk) {
            $this->info('╔══════════════════════════════════════════════════════════╗');
            $this->info('║  ALL CHECKS PASSED! Session should now work correctly.  ║');
            $this->info('╠══════════════════════════════════════════════════════════╣');
            $this->info('║  IMPORTANT: Clear your browser cookies before testing!  ║');
            $this->info('║  Press Ctrl+Shift+Delete → Clear cookies for localhost  ║');
            $this->info('╚══════════════════════════════════════════════════════════╝');
        } else {
            $this->error('╔══════════════════════════════════════════════════════════╗');
            $this->error('║  SOME CHECKS FAILED! See errors above.                   ║');
            $this->error('║  The .env file or cached config may still override.       ║');
            $this->error('╚══════════════════════════════════════════════════════════╝');
        }

        return $allOk ? self::SUCCESS : self::FAILURE;
    }
}
