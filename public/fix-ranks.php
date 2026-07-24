<?php
/**
 * FIX RANKS PUBLISHING + RUN MIGRATIONS + CLEAR CACHES
 * Upload to public/, visit https://yourdomain.com/fix-ranks.php
 * ⚠️ DELETE after use!
 */
error_reporting(E_ALL); ini_set('display_errors', 1);
$laravelRoot = dirname(__DIR__);
echo '<pre style="font-family:monospace;padding:20px;">';

// 1. Bootstrap Laravel
require $laravelRoot . '/vendor/autoload.php';
$app = require_once $laravelRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

echo "=== STEP 1: Add ranks_published column to terms ===\n";
try {
    if (!Schema::hasColumn('terms', 'ranks_published')) {
        Schema::table('terms', function ($table) {
            $table->boolean('ranks_published')->default(false)->after('is_active');
        });
        echo "✓ Added ranks_published column\n";
    } else {
        echo "✓ ranks_published already exists\n";
    }
    if (!Schema::hasColumn('terms', 'ranks_published_at')) {
        Schema::table('terms', function ($table) {
            $table->timestamp('ranks_published_at')->nullable()->after('ranks_published');
        });
        echo "✓ Added ranks_published_at column\n";
    } else {
        echo "✓ ranks_published_at already exists\n";
    }
    if (!Schema::hasColumn('terms', 'ranks_published_by')) {
        Schema::table('terms', function ($table) {
            $table->unsignedBigInteger('ranks_published_by')->nullable()->after('ranks_published_at');
        });
        echo "✓ Added ranks_published_by column\n";
    } else {
        echo "✓ ranks_published_by already exists\n";
    }
} catch (\Throwable $e) {
    echo "⚠ " . $e->getMessage() . "\n";
}

echo "\n=== STEP 2: Run all migrations ===\n";
try {
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output() ?: "✓ Nothing to migrate\n";
} catch (\Throwable $e) {
    echo "⚠ " . $e->getMessage() . "\n";
}

echo "\n=== STEP 3: Clear all caches ===\n";
try {
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    echo "✓ All caches cleared\n";
} catch (\Throwable $e) {
    echo "⚠ " . $e->getMessage() . "\n";
}

echo "\n=== STEP 4: Verify ===\n";
$terms = DB::table('terms')->get(['id','name','ranks_published','ranks_published_at']);
foreach ($terms as $t) {
    echo "  Term #{$t->id} ({$t->name}): ranks_published=" . ($t->ranks_published ? 'YES' : 'NO') . "\n";
}

echo "\n✓ DONE! Delete fix-ranks.php now.\n";
echo '</pre>';
