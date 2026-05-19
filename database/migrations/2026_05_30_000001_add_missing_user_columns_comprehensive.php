<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Comprehensive migration to add all missing columns to the users table.
 * This fixes the "Column not found: id_number" error that breaks login,
 * and also adds security_question/security_answer for the forgot-password flow.
 *
 * Uses IF NOT EXISTS checks so it's safe to run even if some columns already exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Add id_number column ──
        if (!Schema::hasColumn('users', 'id_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('id_number')->nullable()->unique()->after('email');
            });
        }

        // ── Add security_question column ──
        if (!Schema::hasColumn('users', 'security_question')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('security_question')->nullable()->after('is_active');
            });
        }

        // ── Add security_answer column ──
        if (!Schema::hasColumn('users', 'security_answer')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('security_answer')->nullable()->after('security_question');
            });
        }

        // ── Ensure phone column exists (should be in original migration but just in case) ──
        if (!Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone')->nullable()->after('role');
            });
        }

        // ── Ensure branch_id column exists ──
        if (!Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('role')->constrained('branches')->nullOnDelete();
            });
        }

        // ── Ensure gender column exists ──
        if (!Schema::hasColumn('users', 'gender')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('phone');
            });
        }

        // ── Ensure qualification column exists ──
        if (!Schema::hasColumn('users', 'qualification')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('qualification')->nullable()->after('gender');
            });
        }
    }

    public function down(): void
    {
        // Only drop columns that we added (check each one)
        $columnsToCheck = ['id_number', 'security_question', 'security_answer', 'gender', 'qualification'];

        Schema::table('users', function (Blueprint $table) use ($columnsToCheck) {
            foreach ($columnsToCheck as $col) {
                if (Schema::hasColumn('users', $col)) {
                    // Drop unique index first if it exists
                    try {
                        $table->dropUnique(['id_number']);
                    } catch (\Throwable $e) {
                        // Index may not exist
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
