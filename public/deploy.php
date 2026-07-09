<?php
/**
 * STANDALONE DEPLOYMENT HELPER for cPanel (no Terminal needed)
 * ----------------------------------------------------------------------------
 * Upload this file to your Laravel project's public/ folder.
 * Visit https://yourdomain.com/deploy.php in your browser.
 *
 * This script lets you run common artisan commands without SSH/Terminal:
 * 1. Run migrations (php artisan migrate --force)
 * 2. Generate app key (php artisan key:generate)
 * 3. Create storage symlink (php artisan storage:link)
 * 4. Clear all caches (config, route, view, cache)
 * 5. Cache config + routes (for production performance)
 * 6. Fix all tables missing AUTO_INCREMENT
 * 7. Seed the database (if needed)
 * 8. Check system status (PHP version, extensions, DB connection)
 *
 * ⚠️ DELETE THIS FILE after use — it's a security risk if left accessible!
 */

// ── Bootstrap Laravel ───────────────────────────────────────────────────
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$action = $_GET['action'] ?? 'menu';
$output = '';
$success = false;

// ── Handle actions ──────────────────────────────────────────────────────
if ($action !== 'menu') {
    try {
        switch ($action) {
            case 'migrate':
                Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                $output = Illuminate\Support\Facades\Artisan::output();
                $success = true;
                break;

            case 'migrate_fresh_seed':
                echo '<div style="background:#fef3c7;padding:10px;border-radius:8px;margin:10px 0;">⚠️ This will DELETE all data and re-seed. <a href="?action=menu" style="color:#dc2626;">Cancel</a> | <a href="?action=migrate_fresh_seed_confirm" style="color:#dc2626;">I understand, proceed</a></div>';
                $action = 'menu';
                break;

            case 'migrate_fresh_seed_confirm':
                Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
                $output = Illuminate\Support\Facades\Artisan::output();
                $success = true;
                break;

            case 'key_generate':
                Illuminate\Support\Facades\Artisan::call('key:generate');
                $output = Illuminate\Support\Facades\Artisan::output();
                $success = true;
                break;

            case 'storage_link':
                try {
                    Illuminate\Support\Facades\Artisan::call('storage:link');
                    $output = Illuminate\Support\Facades\Artisan::output();
                } catch (\Throwable $e) {
                    // Manual symlink fallback
                    $target = storage_path('app/public');
                    $link = public_path('storage');
                    if (!file_exists($link) && is_dir($target)) {
                        @symlink($target, $link);
                        $output = "Manual symlink created: {$link} -> {$target}\n";
                    } else {
                        $output = "Storage link already exists or target not found.\n";
                    }
                }
                $success = true;
                break;

            case 'clear_all':
                Illuminate\Support\Facades\Artisan::call('config:clear');
                $output .= Illuminate\Support\Facades\Artisan::output();
                Illuminate\Support\Facades\Artisan::call('route:clear');
                $output .= Illuminate\Support\Facades\Artisan::output();
                Illuminate\Support\Facades\Artisan::call('view:clear');
                $output .= Illuminate\Support\Facades\Artisan::output();
                Illuminate\Support\Facades\Artisan::call('cache:clear');
                $output .= Illuminate\Support\Facades\Artisan::output();
                $output .= "\n✓ All caches cleared.\n";
                $success = true;
                break;

            case 'cache_all':
                Illuminate\Support\Facades\Artisan::call('config:cache');
                $output .= Illuminate\Support\Facades\Artisan::output();
                Illuminate\Support\Facades\Artisan::call('route:cache');
                $output .= Illuminate\Support\Facades\Artisan::output();
                Illuminate\Support\Facades\Artisan::call('view:cache');
                $output .= Illuminate\Support\Facades\Artisan::output();
                $output .= "\n✓ All caches built (production mode).\n";
                $success = true;
                break;

            case 'fix_autoincrement':
                $dbName = config('database.connections.mysql.database');
                $tables = Illuminate\Support\Facades\DB::select("
                    SELECT TABLE_NAME, COLUMN_TYPE
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = ?
                      AND COLUMN_NAME = 'id'
                      AND EXTRA NOT LIKE '%auto_increment%'
                    ORDER BY TABLE_NAME
                ", [$dbName]);

                $output = "Found " . count($tables) . " table(s) missing AUTO_INCREMENT:\n";
                $fixed = 0;
                $failed = 0;
                foreach ($tables as $table) {
                    $tableName = $table->TABLE_NAME;
                    $columnType = $table->COLUMN_TYPE;
                    $isBigint = strpos($columnType, 'bigint') !== false;
                    $typeSql = $isBigint ? 'BIGINT UNSIGNED' : 'INT UNSIGNED';

                    try {
                        try {
                            Illuminate\Support\Facades\DB::statement("ALTER TABLE `{$tableName}` DROP PRIMARY KEY");
                        } catch (\Throwable $e) {}
                        Illuminate\Support\Facades\DB::statement("ALTER TABLE `{$tableName}` MODIFY COLUMN `id` {$typeSql} NOT NULL AUTO_INCREMENT PRIMARY KEY");
                        $output .= "  ✓ Fixed: {$tableName}\n";
                        $fixed++;
                    } catch (\Throwable $e) {
                        $output .= "  ✗ Failed: {$tableName} — " . $e->getMessage() . "\n";
                        $failed++;
                    }
                }
                $output .= "\nFixed: {$fixed}, Failed: {$failed}\n";
                $success = true;
                break;

            case 'seed':
                Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
                $output = Illuminate\Support\Facades\Artisan::output();
                $success = true;
                break;

            case 'status':
                $output = "=== SYSTEM STATUS ===\n\n";
                $output .= "PHP Version: " . PHP_VERSION . "\n";
                $output .= "Laravel Version: " . app()->version() . "\n";
                $output .= "Environment: " . app()->environment() . "\n";
                $output .= "Debug Mode: " . (config('app.debug') ? 'ON' : 'OFF') . "\n";
                $output .= "App Key: " . (config('app.key') ? '✓ Set' : '✗ MISSING') . "\n";
                $output .= "App URL: " . config('app.url') . "\n\n";

                $output .= "=== PHP EXTENSIONS ===\n";
                $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'cURL', 'gd', 'fileinfo', 'zip'];
                foreach ($extensions as $ext) {
                    $loaded = extension_loaded(strtolower($ext)) || extension_loaded($ext);
                    $output .= "  " . ($loaded ? '✓' : '✗') . " {$ext}" . ($loaded ? '' : ' (MISSING)') . "\n";
                }

                $output .= "\n=== DATABASE ===\n";
                try {
                    $pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
                    $output .= "  ✓ Connected to: " . config('database.connections.mysql.database') . "\n";
                    $output .= "  Driver: " . Illuminate\Support\Facades\DB::getDriverName() . "\n";

                    // Count tables
                    $tables = Illuminate\Support\Facades\DB::select("SHOW TABLES");
                    $output .= "  Total tables: " . count($tables) . "\n";

                    // Check migrations table
                    try {
                        $ran = Illuminate\Support\Facades\DB::table('migrations')->count();
                        $output .= "  Migrations run: {$ran}\n";
                    } catch (\Throwable $e) {
                        $output .= "  ✗ migrations table missing\n";
                    }

                    // Check users count
                    try {
                        $users = Illuminate\Support\Facades\DB::table('users')->count();
                        $output .= "  Users: {$users}\n";
                    } catch (\Throwable $e) {
                        $output .= "  ✗ users table error: " . $e->getMessage() . "\n";
                    }
                } catch (\Throwable $e) {
                    $output .= "  ✗ DB Error: " . $e->getMessage() . "\n";
                }

                $output .= "\n=== STORAGE ===\n";
                $output .= "  storage/app: " . (is_writable(storage_path('app')) ? '✓ writable' : '✗ NOT writable') . "\n";
                $output .= "  storage/framework: " . (is_writable(storage_path('framework')) ? '✓ writable' : '✗ NOT writable') . "\n";
                $output .= "  storage/logs: " . (is_writable(storage_path('logs')) ? '✓ writable' : '✗ NOT writable') . "\n";
                $output .= "  bootstrap/cache: " . (is_writable(base_path('bootstrap/cache')) ? '✓ writable' : '✗ NOT writable') . "\n";
                $output .= "  public/storage symlink: " . (is_link(public_path('storage')) ? '✓ exists' : '✗ missing') . "\n";

                $output .= "\n=== MISSING AUTO_INCREMENT CHECK ===\n";
                try {
                    $dbName = config('database.connections.mysql.database');
                    $broken = Illuminate\Support\Facades\DB::select("
                        SELECT TABLE_NAME
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = ?
                          AND COLUMN_NAME = 'id'
                          AND EXTRA NOT LIKE '%auto_increment%'
                        ORDER BY TABLE_NAME
                    ", [$dbName]);
                    if (empty($broken)) {
                        $output .= "  ✓ All tables have AUTO_INCREMENT\n";
                    } else {
                        $output .= "  ✗ " . count($broken) . " table(s) missing AUTO_INCREMENT:\n";
                        foreach ($broken as $b) {
                            $output .= "    - " . $b->TABLE_NAME . "\n";
                        }
                        $output .= "\n  → Run 'Fix AUTO_INCREMENT' to fix all tables.\n";
                    }
                } catch (\Throwable $e) {
                    $output .= "  Could not check: " . $e->getMessage() . "\n";
                }

                $success = true;
                break;

            case 'optimize':
                Illuminate\Support\Facades\Artisan::call('optimize');
                $output = Illuminate\Support\Facades\Artisan::output();
                $output .= "\n✓ Application optimized.\n";
                $success = true;
                break;

            default:
                $output = "Unknown action.";
                break;
        }
    } catch (\Throwable $e) {
        $output = "ERROR: " . $e->getMessage() . "\n\nFile: " . $e->getFile() . ":" . $e->getLine() . "\n\n" . $e->getTraceAsString();
        $success = false;
    }
}

// ── Render page ─────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html>
<head>
    <title>Deployment Helper — <?php echo config('app.name', 'Laravel'); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f0fdf4; color: #0f172a; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #047857; margin-bottom: 8px; }
        .subtitle { color: #6b7280; font-size: 14px; margin-bottom: 24px; }
        .cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px; }
        @media (max-width: 600px) { .cards { grid-template-columns: 1fr; } }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; transition: all 0.2s; text-decoration: none; color: inherit; display: block; }
        .card:hover { border-color: #047857; box-shadow: 0 4px 12px rgba(4,120,87,0.12); transform: translateY(-2px); text-decoration: none; }
        .card-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin-bottom: 8px; }
        .card-title { font-weight: 700; font-size: 14px; margin-bottom: 4px; }
        .card-desc { font-size: 12px; color: #6b7280; line-height: 1.5; }
        .card-blue .card-icon { background: #e0e7ff; color: #4361ee; }
        .card-green .card-icon { background: #d1fae5; color: #059669; }
        .card-gold .card-icon { background: #fef3c7; color: #d97706; }
        .card-red .card-icon { background: #fee2e2; color: #dc2626; }
        .card-purple .card-icon { background: #ede9fe; color: #7c3aed; }
        .card-cyan .card-icon { background: #cffafe; color: #0891b2; }
        .output-box { background: #1e293b; color: #e2e8f0; border-radius: 12px; padding: 16px; font-family: 'Consolas', 'Monaco', monospace; font-size: 12px; line-height: 1.6; white-space: pre-wrap; word-wrap: break-word; max-height: 500px; overflow-y: auto; margin-bottom: 20px; }
        .success-banner { background: #d1fae5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; }
        .error-banner { background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; }
        .warning { background: #fef3c7; border: 1px solid #fde68a; color: #92400e; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 13px; }
        .btn-back { display: inline-block; margin-top: 12px; padding: 8px 16px; background: #047857; color: #fff; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 600; }
        .btn-back:hover { background: #036b4a; }
        .danger-zone { border: 2px solid #fecaca; border-radius: 12px; padding: 16px; margin-top: 20px; background: #fef2f2; }
        .danger-zone h3 { color: #dc2626; margin-bottom: 8px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Deployment Helper</h1>
        <p class="subtitle"><?php echo config('app.name', 'Laravel'); ?> — run artisan commands without SSH/Terminal</p>

        <?php if ($output): ?>
            <?php if ($success): ?>
                <div class="success-banner">✓ Command completed successfully</div>
            <?php else: ?>
                <div class="error-banner">✗ Command failed — see output below</div>
            <?php endif; ?>
            <div class="output-box"><?php echo htmlspecialchars($output); ?></div>
            <a href="?action=menu" class="btn-back">← Back to Menu</a>
        <?php else: ?>

        <div class="cards">
            <a href="?action=status" class="card card-cyan">
                <div class="card-icon">📊</div>
                <div class="card-title">System Status</div>
                <div class="card-desc">Check PHP version, extensions, DB connection, storage permissions, missing AUTO_INCREMENT</div>
            </a>

            <a href="?action=migrate" class="card card-green">
                <div class="card-icon">🔄</div>
                <div class="card-title">Run Migrations</div>
                <div class="card-desc">php artisan migrate --force — adds missing columns and tables</div>
            </a>

            <a href="?action=key_generate" class="card card-gold">
                <div class="card-icon">🔑</div>
                <div class="card-title">Generate App Key</div>
                <div class="card-desc">php artisan key:generate — required for encryption</div>
            </a>

            <a href="?action=storage_link" class="card card-blue">
                <div class="card-icon">🔗</div>
                <div class="card-title">Storage Link</div>
                <div class="card-desc">php artisan storage:link — makes uploaded files web-accessible</div>
            </a>

            <a href="?action=fix_autoincrement" class="card card-purple">
                <div class="card-icon">🛠️</div>
                <div class="card-title">Fix AUTO_INCREMENT</div>
                <div class="card-desc">Scans ALL tables and fixes missing AUTO_INCREMENT on 'id' columns</div>
            </a>

            <a href="?action=clear_all" class="card card-red">
                <div class="card-icon">🧹</div>
                <div class="card-title">Clear All Caches</div>
                <div class="card-desc">Clears config, route, view, and application caches</div>
            </a>

            <a href="?action=cache_all" class="card card-green">
                <div class="card-icon">⚡</div>
                <div class="card-title">Cache for Production</div>
                <div class="card-desc">Builds config + route + view caches for faster performance</div>
            </a>

            <a href="?action=optimize" class="card card-blue">
                <div class="card-icon">🚀</div>
                <div class="card-title">Optimize</div>
                <div class="card-desc">php artisan optimize — caches everything for production</div>
            </a>

            <a href="?action=seed" class="card card-gold">
                <div class="card-icon">🌱</div>
                <div class="card-title">Run Seeders</div>
                <div class="card-desc">php artisan db:seed — inserts default settings and demo data</div>
            </a>
        </div>

        <div class="danger-zone">
            <h3>⚠️ DANGER ZONE — Will DELETE all data</h3>
            <p style="font-size:13px;color:#7f1d1d;margin-bottom:10px;">This will drop all tables and re-create them with seed data. Only use on a fresh database.</p>
            <a href="?action=migrate_fresh_seed" style="display:inline-block;padding:8px 16px;background:#dc2626;color:#fff;text-decoration:none;border-radius:8px;font-size:13px;font-weight:600;">Migrate Fresh + Seed</a>
        </div>

        <div class="warning" style="margin-top:20px;">
            <strong>⚠️ SECURITY:</strong> Delete this file (<code>deploy.php</code>) from your server after setup is complete. Leaving it accessible allows anyone to run database commands.
        </div>

        <?php endif; ?>
    </div>
</body>
</html>
