<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add must_change_password column to users table.
 *
 * When true, the user is redirected to a forced password-change page after
 * login instead of going to the dashboard. This flag is set whenever a user
 * account is created or reset with the default password (123456).
 *
 * Also backfills existing users whose password matches the default '123456'
 * hash — sets must_change_password=true for them so they get prompted on
 * next login.
 */
return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'must_change_password')) {
                    $table->boolean('must_change_password')->default(false)->after('is_active');
                }
            });

            // Backfill: mark existing users with default password '123456'
            // We check both bcrypt hashes since different code paths used
            // Hash::make('123456') at different times (different salts = different hashes).
            // The most reliable approach: check Hash::check() in PHP.
            try {
                $users = \DB::table('users')->whereNull('deleted_at')->get(['id', 'password']);
                $updated = 0;
                foreach ($users as $u) {
                    if (\Illuminate\Support\Facades\Hash::check('123456', $u->password)) {
                        \DB::table('users')->where('id', $u->id)->update(['must_change_password' => true]);
                        $updated++;
                    }
                }
                \Log::info("must_change_password backfill: marked {$updated} users with default password");
            } catch (\Throwable $e) {
                \Log::info('must_change_password backfill skipped: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            \Log::info('users.must_change_password column already exists or skipped: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('must_change_password');
            });
        } catch (\Throwable $e) {
            // Silently fail
        }
    }
};
