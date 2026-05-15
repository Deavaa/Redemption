<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Find and drop any foreign keys that reference exam_id
        // MySQL won't let us drop the unique index while a foreign key depends on it
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'mark_entries'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");

        Schema::table('mark_entries', function (Blueprint $table) use ($foreignKeys) {
            foreach ($foreignKeys as $fk) {
                $name = $fk->CONSTRAINT_NAME;
                // Drop foreign keys that involve exam_id or student_id
                if (str_contains($name, 'exam_id') || str_contains($name, 'student_id')) {
                    $table->dropForeign($name);
                }
            }
        });

        // Step 2: Now drop the old unique constraint
        Schema::table('mark_entries', function (Blueprint $table) {
            // Check if the unique index exists before dropping
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

        // Step 3: Add new unique constraint + make exam_id nullable
        Schema::table('mark_entries', function (Blueprint $table) {
            // Add the new unique constraint matching the actual upsert key
            $table->unique(['student_id', 'subject_id', 'academic_year_id', 'term_id'], 'mark_entries_student_subject_ay_term_unique');

            // Make exam_id nullable
            $table->unsignedBigInteger('exam_id')->nullable()->change();
        });

        // Step 4: Re-add the foreign keys we dropped
        Schema::table('mark_entries', function (Blueprint $table) {
            $table->foreign('exam_id')->references('id')->on('exams')->nullOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mark_entries', function (Blueprint $table) {
            $table->dropForeign(['exam_id']);
            $table->dropForeign(['student_id']);
            $table->dropUnique('mark_entries_student_subject_ay_term_unique');
            $table->unique(['exam_id', 'student_id']);
            $table->foreign('exam_id')->references('id')->on('exams')->nullOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
        });
    }
};
