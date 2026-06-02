<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\CalendarEvent;
use App\Session\NoGarbageSessionHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ============================================================
        // SAFETY NET: Auto-generate APP_KEY if missing.
        // ============================================================
        if (empty(config('app.key'))) {
            $key = 'base64:' . base64_encode(random_bytes(32));
            config(['app.key' => $key]);
            $envPath = base_path('.env');
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                if (preg_match('/^APP_KEY\s*=/m', $envContent)) {
                    $envContent = preg_replace('/^APP_KEY\s*=.*/m', "APP_KEY={$key}", $envContent);
                } else {
                    $envContent .= "\nAPP_KEY={$key}\n";
                }
                @file_put_contents($envPath, $envContent);
            }
        }

        // ============================================================
        // FORCE correct database: always MySQL (never sqlite)
        // ============================================================
        config(['database.default' => 'mysql']);

        // ============================================================
        // SESSION: Use 'database' driver with NoGarbageSessionHandler.
        //
        // The KEY FIX is a custom session driver called 'safe_database'
        // that wraps Laravel's database handler with NoGarbageSessionHandler.
        // This makes gc() a NO-OP so sessions can NEVER be deleted by
        // PHP's garbage collection, regardless of php.ini settings.
        // ============================================================
        config(['session.driver' => 'safe_database']);  // Our custom driver
        config(['session.table' => 'sessions']);
        config(['session.connection' => null]);
        config(['session.store' => null]);
        config(['session.cookie' => 'redemption_session']);
        config(['session.path' => '/']);
        config(['session.domain' => null]);
        config(['session.secure' => false]);
        config(['session.same_site' => 'lax']);
        config(['session.http_only' => true]);
        config(['session.encrypt' => false]);
        config(['session.expire_on_close' => false]);
        config(['session.partitioned' => false]);
        config(['session.lifetime' => 480]); // 8 hours
        config(['session.lottery' => [0, 1000]]); // 0% chance — disabled

        // Also try to disable PHP GC as extra safety net
        @ini_set('session.gc_maxlifetime', 28800);
        @ini_set('session.gc_probability', 0);
        @ini_set('session.gc_divisor', 1);
        @ini_set('session.cookie_lifetime', 28800);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Delete stale cached config
        $cachedConfig = base_path('bootstrap/cache/config.php');
        if (file_exists($cachedConfig)) {
            @unlink($cachedConfig);
        }

        // ============================================================
        // AUTO-CREATE sessions table using RAW SQL.
        // Schema::create() can fail silently. Raw SQL is more reliable.
        // ============================================================
        try {
            $exists = DB::selectOne("SHOW TABLES LIKE 'sessions'");
            if (!$exists) {
                DB::statement("
                    CREATE TABLE `sessions` (
                        `id` varchar(255) NOT NULL PRIMARY KEY,
                        `user_id` bigint unsigned DEFAULT NULL,
                        `ip_address` varchar(45) DEFAULT NULL,
                        `user_agent` text DEFAULT NULL,
                        `payload` longtext NOT NULL,
                        `last_activity` int NOT NULL,
                        KEY `sessions_user_id_index` (`user_id`),
                        KEY `sessions_last_activity_index` (`last_activity`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }
        } catch (\Throwable $e) {
            // Don't crash — try Schema::create as fallback
            try {
                if (!Schema::hasTable('sessions')) {
                    Schema::create('sessions', function ($table) {
                        $table->string('id')->primary();
                        $table->foreignId('user_id')->nullable()->index();
                        $table->string('ip_address', 45)->nullable();
                        $table->text('user_agent')->nullable();
                        $table->longText('payload');
                        $table->integer('last_activity')->index();
                    });
                }
            } catch (\Throwable $e2) {
                // If both fail, fall back to file driver
                config(['session.driver' => 'file']);
            }
        }

        // Ensure session directory exists (for file driver fallback)
        try {
            $sessionDir = storage_path('framework/sessions');
            if (!is_dir($sessionDir)) {
                mkdir($sessionDir, 0755, true);
            }
        } catch (\Throwable $e) {}

        // ============================================================
        // THE KEY FIX: Register 'safe_database' session driver.
        //
        // This creates a custom session driver that wraps Laravel's
        // default database handler with NoGarbageSessionHandler.
        //
        // The NoGarbageSessionHandler overrides gc() to do NOTHING.
        // When PHP calls gc() (garbage collection), it returns 0
        // and does not delete any sessions. This makes sessions
        // completely immune to PHP's garbage collection regardless
        // of any php.ini, .user.ini, or ini_set() settings.
        //
        // Sessions still expire based on Laravel's session.lifetime
        // check, but NOT through PHP's gc() function.
        // ============================================================
        Session::extend('safe_database', function ($app) {
            $connection = $app['db']->connection($app['config']->get('session.connection'));
            $table = $app['config']->get('session.table', 'sessions');
            $minutes = $app['config']->get('session.lifetime', 480);

            // Create Laravel's default database session handler
            $databaseHandler = new \Illuminate\Session\DatabaseSessionHandler(
                $connection, $table, $minutes, $app
            );

            // Wrap it with our NoGarbageSessionHandler that disables gc()
            return new NoGarbageSessionHandler($databaseHandler);
        });

        // Also register a 'safe_file' driver as fallback
        Session::extend('safe_file', function ($app) {
            $path = $app['config']->get('session.files', storage_path('framework/sessions'));

            // Create Laravel's default file session handler
            $fileHandler = new \Illuminate\Session\FileSessionHandler(
                $app['files'], $path, $app['config']->get('session.lifetime', 480)
            );

            // Wrap it with our NoGarbageSessionHandler that disables gc()
            return new NoGarbageSessionHandler($fileHandler);
        });

        // Share active announcements with admin layout
        View::composer('layouts.admin', function ($view) {
            try {
                $activeAnnouncements = CalendarEvent::where('is_announcement', true)
                    ->where(function ($q) {
                        $q->where('start_date', '>=', now()->subDays(30))
                          ->orWhere('start_date', '>=', now());
                    })
                    ->orderBy('start_date', 'desc')
                    ->limit(10)
                    ->get();
            } catch (\Throwable $e) {
                $activeAnnouncements = collect([]);
            }

            $view->with('activeAnnouncements', $activeAnnouncements);
        });
    }
}
