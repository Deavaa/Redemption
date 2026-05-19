<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mark_entries')) {
            return;
        }

        // Step 1: Find and drop any foreign keys that reference exam_id
        try {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'mark_entries'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");

            Schema::table('mark_entries', function (Blueprint $table) use ($foreignKeys) {
                foreach ($foreignKeys as $fk) {
                    $name = is_object($fk) ? ($fk->CONSTRAINT_NAME ?? '') : '';
                    if ($name && (str_contains($name, 'exam_id') || str_contains($name, 'student_id'))) {
                        try {
                            $table->dropForeign($name);
                        } catch (\Throwable $e) {}
                    }
                }
            });
        } catch (\Throwable $e) {}

        // Step 2: Drop the old unique constraint
        try {
            Schema::table('mark_entries', function (Blueprint $table) {
                $indexes = DB::select("
                    SELECT INDEX_NAME
                    FROM INFORMATION_SCHEMA.STATISTICS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'mark_entries'
                      AND INDEX_NAME = 'mark_entries_exam_id_student_id_unique'
                    GROUP BY INDEX_NAME
                ");
                if (!empty($indexes)) {
                    $table->dropUnique(['exam_id', 'student_id']);
                }
            });
        } catch (\Throwable $e) {}

        // Step 3: Add new unique constraint + make exam_id nullable
        try {
            Schema::table('mark_entries', function (Blueprint $table) {
                // Check if unique already exists
                $existing = collect(DB::select("
                    SELECT INDEX_NAME
                    FROM INFORMATION_SCHEMA.STATISTICS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'mark_entries'
                      AND INDEX_NAME = 'mark_entries_student_subject_ay_term_unique'
                    GROUP BY INDEX_NAME
                "))->isEmpty();

                if ($existing) {
                    $table->unique(['student_id', 'subject_id', 'academic_year_id', 'term_id'], 'mark_entries_student_subject_ay_term_unique');
                }
            });
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasColumn('mark_entries', 'exam_id')) {
                Schema::table('mark_entries', function (Blueprint $table) {
                    $table->unsignedBigInteger('exam_id')->nullable()->change();
                });
            }
        } catch (\Throwable $e) {}

        // Step 4: Re-add the foreign keys we dropped
        try {
            Schema::table('mark_entries', function (Blueprint $table) {
                if (Schema::hasColumn('mark_entries', 'exam_id') && Schema::hasTable('exams')) {
                    $table->foreign('exam_id')->references('id')->on('exams')->nullOnDelete();
                }
                if (Schema::hasColumn('mark_entries', 'student_id') && Schema::hasTable('students')) {
                    $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
                }
            });
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        // No down migration — too risky to reverse
    }
};
