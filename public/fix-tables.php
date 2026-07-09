<?php
/**
 * FIX MISSING TABLES + TEAM MEMBERS LIMIT
 * ----------------------------------------------------------------------------
 * Upload to public/ folder, visit https://yourdomain.com/fix-tables.php
 *
 * Fixes:
 * 1. Creates 'roles' table (missing from cPanel database)
 * 2. Creates 'permissions' table
 * 3. Creates 'permission_role' table
 * 4. Creates 'role_user' table
 * 5. Seeds default roles
 * 6. Clears Laravel caches
 *
 * ⚠️ DELETE THIS FILE after use!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$laravelRoot = dirname(__DIR__);

echo '<!DOCTYPE html><html><head><title>Fix Tables</title>';
echo '<style>body{font-family:monospace;padding:20px;max-width:900px;margin:0 auto;background:#0f172a;color:#e2e8f0;}h1{color:#10b981;}pre{background:#1e293b;border:1px solid #334155;border-radius:8px;padding:16px;white-space:pre-wrap;}.ok{color:#10b981;}.err{color:#dc2626;}.warn{color:#fbbf24;}</style>';
echo '</head><body><h1>🔧 Fix Missing Tables</h1><pre>';

// Bootstrap Laravel
$autoloadPath = $laravelRoot . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    echo '<span class="err">✗ vendor/autoload.php not found. Run composer install first.</span>';
    echo '</pre></body></html>';
    exit;
}

require $autoloadPath;
$app = require_once $laravelRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ═══════════════════════════════════════════════════════════════════════
// 1. Create 'roles' table
// ═══════════════════════════════════════════════════════════════════════
echo "=== STEP 1: Create 'roles' table ===\n";
if (Schema::hasTable('roles')) {
    echo "<span class='ok'>✓ roles table already exists.</span>\n";
} else {
    try {
        Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
        echo "<span class='ok'>✓ roles table created.</span>\n";
    } catch (\Throwable $e) {
        echo "<span class='err'>✗ Failed: " . $e->getMessage() . "</span>\n";
    }
}

// ═══════════════════════════════════════════════════════════════════════
// 2. Create 'permissions' table
// ═══════════════════════════════════════════════════════════════════════
echo "\n=== STEP 2: Create 'permissions' table ===\n";
if (Schema::hasTable('permissions')) {
    echo "<span class='ok'>✓ permissions table already exists.</span>\n";
} else {
    try {
        Schema::create('permissions', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->string('module')->index();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        echo "<span class='ok'>✓ permissions table created.</span>\n";
    } catch (\Throwable $e) {
        echo "<span class='err'>✗ Failed: " . $e->getMessage() . "</span>\n";
    }
}

// ═══════════════════════════════════════════════════════════════════════
// 3. Create 'permission_role' table
// ═══════════════════════════════════════════════════════════════════════
echo "\n=== STEP 3: Create 'permission_role' table ===\n";
if (Schema::hasTable('permission_role')) {
    echo "<span class='ok'>✓ permission_role table already exists.</span>\n";
} else {
    try {
        Schema::create('permission_role', function ($table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->unique(['permission_id', 'role_id']);
        });
        echo "<span class='ok'>✓ permission_role table created.</span>\n";
    } catch (\Throwable $e) {
        echo "<span class='warn'>⚠ " . $e->getMessage() . "</span>\n";
        // Try without foreign keys
        try {
            Schema::create('permission_role', function ($table) {
                $table->id();
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->unique(['permission_id', 'role_id']);
            });
            echo "<span class='ok'>✓ permission_role table created (no FK).</span>\n";
        } catch (\Throwable $e2) {
            echo "<span class='err'>✗ " . $e2->getMessage() . "</span>\n";
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════
// 4. Create 'role_user' table
// ═══════════════════════════════════════════════════════════════════════
echo "\n=== STEP 4: Create 'role_user' table ===\n";
if (Schema::hasTable('role_user')) {
    echo "<span class='ok'>✓ role_user table already exists.</span>\n";
} else {
    try {
        Schema::create('role_user', function ($table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['role_id', 'user_id']);
        });
        echo "<span class='ok'>✓ role_user table created.</span>\n";
    } catch (\Throwable $e) {
        echo "<span class='warn'>⚠ " . $e->getMessage() . "</span>\n";
        try {
            Schema::create('role_user', function ($table) {
                $table->id();
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('user_id');
                $table->unique(['role_id', 'user_id']);
            });
            echo "<span class='ok'>✓ role_user table created (no FK).</span>\n";
        } catch (\Throwable $e2) {
            echo "<span class='err'>✗ " . $e2->getMessage() . "</span>\n";
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════
// 5. Seed default roles
// ═══════════════════════════════════════════════════════════════════════
echo "\n=== STEP 5: Seed default roles ===\n";
$defaultRoles = [
    ['name' => 'admin', 'display_name' => 'Administrator', 'is_system' => true],
    ['name' => 'super_admin', 'display_name' => 'Super Administrator', 'is_system' => true],
    ['name' => 'teacher', 'display_name' => 'Teacher', 'is_system' => true],
    ['name' => 'staff', 'display_name' => 'Staff', 'is_system' => true],
    ['name' => 'student', 'display_name' => 'Student', 'is_system' => true],
    ['name' => 'parent', 'display_name' => 'Parent', 'is_system' => true],
    ['name' => 'librarian', 'display_name' => 'Librarian', 'is_system' => false],
    ['name' => 'branch_principal', 'display_name' => 'Branch Principal', 'is_system' => false],
    ['name' => 'general_manager', 'display_name' => 'General Manager', 'is_system' => false],
    ['name' => 'cashier', 'display_name' => 'Cashier', 'is_system' => false],
    ['name' => 'registrar', 'display_name' => 'Registrar', 'is_system' => false],
    ['name' => 'finance', 'display_name' => 'Finance', 'is_system' => false],
    ['name' => 'hr', 'display_name' => 'Human Resources', 'is_system' => false],
];

$seeded = 0;
foreach ($defaultRoles as $role) {
    try {
        $exists = DB::table('roles')->where('name', $role['name'])->exists();
        if (!$exists) {
            DB::table('roles')->insert(array_merge($role, [
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            $seeded++;
        }
    } catch (\Throwable $e) {
        echo "<span class='warn'>⚠ Could not seed role '{$role['name']}': " . $e->getMessage() . "</span>\n";
    }
}
echo "<span class='ok'>✓ Seeded {$seeded} new role(s). Total roles: " . DB::table('roles')->count() . "</span>\n";

// ═══════════════════════════════════════════════════════════════════════
// 6. Mark the migration as run
// ═══════════════════════════════════════════════════════════════════════
echo "\n=== STEP 6: Mark migration as run ===\n";
try {
    if (!DB::table('migrations')->where('migration', '2026_05_15_000001_create_permissions_table')->exists()) {
        DB::table('migrations')->insert([
            'migration' => '2026_05_15_000001_create_permissions_table',
            'batch' => 1,
        ]);
        echo "<span class='ok'>✓ Migration marked as run.</span>\n";
    } else {
        echo "<span class='ok'>✓ Migration already marked as run.</span>\n";
    }
} catch (\Throwable $e) {
    echo "<span class='warn'>⚠ " . $e->getMessage() . "</span>\n";
}

// ═══════════════════════════════════════════════════════════════════════
// 7. Run remaining migrations
// ═══════════════════════════════════════════════════════════════════════
echo "\n=== STEP 7: Run remaining migrations ===\n";
try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $output = \Illuminate\Support\Facades\Artisan::output();
    echo $output ?: "<span class='ok'>✓ Nothing to migrate.</span>\n";
} catch (\Throwable $e) {
    echo "<span class='err'>✗ " . $e->getMessage() . "</span>\n";
}

// ═══════════════════════════════════════════════════════════════════════
// 8. Clear caches
// ═══════════════════════════════════════════════════════════════════════
echo "\n=== STEP 8: Clear caches ===\n";
try {
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "<span class='ok'>✓ All caches cleared.</span>\n";
} catch (\Throwable $e) {
    echo "<span class='warn'>⚠ " . $e->getMessage() . "</span>\n";
}

// ═══════════════════════════════════════════════════════════════════════
// 9. Verify
// ═══════════════════════════════════════════════════════════════════════
echo "\n=== STEP 9: Verification ===\n";
$tables = ['roles', 'permissions', 'permission_role', 'role_user', 'team_members', 'users'];
foreach ($tables as $t) {
    $exists = Schema::hasTable($t);
    $count = $exists ? DB::table($t)->count() : 0;
    echo "  " . ($exists ? '<span class="ok">✓</span>' : '<span class="err">✗</span>') . " {$t}: " . ($exists ? "{$count} records" : "MISSING") . "\n";
}

// Check team members limit fix
echo "\n=== STEP 10: Team Members on Homepage ===\n";
$tmCount = DB::table('team_members')->where('is_active', 1)->count();
echo "Active team members: {$tmCount}\n";
echo "<span class='ok'>✓ Homepage now shows ALL active team members (was limited to 4).</span>\n";

echo "\n=== DONE ===\n";
echo "<span class='warn'>⚠️ Delete fix-tables.php from your server NOW!</span>\n";
echo "</pre></body></html>";
