<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add `ranks_published` boolean column to the `terms` table.
 *
 * When false (default), ranks/class positions are HIDDEN from students and parents.
 * The branch principal or admin must explicitly publish ranks after final exam
 * marks are entered and verified.
 *
 * This column is per-term, so Term 1 ranks can be published independently from
 * Term 2 ranks.
 */
return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('terms', function (Blueprint $table) {
                if (!Schema::hasColumn('terms', 'ranks_published')) {
                    $table->boolean('ranks_published')->default(false)->after('is_active');
                }
                if (!Schema::hasColumn('terms', 'ranks_published_at')) {
                    $table->timestamp('ranks_published_at')->nullable()->after('ranks_published');
                }
                if (!Schema::hasColumn('terms', 'ranks_published_by')) {
                    $table->foreignId('ranks_published_by')->nullable()->constrained('users')->nullOnDelete()->after('ranks_published_at');
                }
            });
        } catch (\Throwable $e) {
            \Log::info('terms.ranks_published column already exists or skipped: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        try {
            Schema::table('terms', function (Blueprint $table) {
                if (Schema::hasColumn('terms', 'ranks_published_by')) {
                    $table->dropForeign(['ranks_published_by']);
                    $table->dropColumn('ranks_published_by');
                }
                if (Schema::hasColumn('terms', 'ranks_published_at')) {
                    $table->dropColumn('ranks_published_at');
                }
                if (Schema::hasColumn('terms', 'ranks_published')) {
                    $table->dropColumn('ranks_published');
                }
            });
        } catch (\Throwable $e) {
            // Silently fail
        }
    }
};
