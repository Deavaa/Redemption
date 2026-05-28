<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Self-Assessment questions are class-level — they apply to ALL branches
     * and ALL sections within a class. This migration:
     * 1. Makes teacher_id and academic_year_id nullable (admin can create questions)
     * 2. Adds soft_deletes column if missing
     * 3. Sets section_id = NULL and branch_id = NULL on all existing questions
     * 4. Populates teachers.user_id where missing (needed for teacher assignment lookups)
     * 5. Remaps teacher_assignments.teacher_id from users.id → teachers.id
     */
    public function up()
    {
        // ── 1. Schema fixes using raw SQL (no doctrine/dbal required) ──

        // Drop foreign keys first
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'assessment_questions'
              AND COLUMN_NAME = 'teacher_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE assessment_questions DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        $ayForeignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'assessment_questions'
              AND COLUMN_NAME = 'academic_year_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        foreach ($ayForeignKeys as $fk) {
            DB::statement("ALTER TABLE assessment_questions DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // Make teacher_id nullable
        DB::statement("ALTER TABLE assessment_questions MODIFY COLUMN teacher_id BIGINT UNSIGNED NULL");

        // Make academic_year_id nullable
        DB::statement("ALTER TABLE assessment_questions MODIFY COLUMN academic_year_id BIGINT UNSIGNED NULL");

        // Re-add foreign keys with nullOnDelete behavior
        DB::statement("ALTER TABLE assessment_questions ADD CONSTRAINT assessment_questions_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL");
        DB::statement("ALTER TABLE assessment_questions ADD CONSTRAINT assessment_questions_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE SET NULL");

        // Add soft_deletes if missing
        if (!Schema::hasColumn('assessment_questions', 'deleted_at')) {
            DB::statement("ALTER TABLE assessment_questions ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        }

        // ── 2. Data fixes ──────────────────────────────────────

        // Make all existing assessment questions class-level
        DB::table('assessment_questions')->update([
            'section_id' => null,
            'branch_id' => null,
        ]);

        // Populate teachers.user_id by matching email with users table
        $teachers = DB::table('teachers')
            ->whereNull('user_id')
            ->orWhere('user_id', 0)
            ->get();

        foreach ($teachers as $teacher) {
            if (empty($teacher->email)) continue;

            $user = DB::table('users')
                ->where('email', $teacher->email)
                ->first();

            if ($user) {
                DB::table('teachers')
                    ->where('id', $teacher->id)
                    ->update(['user_id' => $user->id]);
            }
        }

        // Remap teacher_assignments.teacher_id from users.id → teachers.id
        // This handles the case where teacher_assignments.teacher_id stores
        // users.id values instead of teachers.id values
        $assignments = DB::table('teacher_assignments')->get();

        foreach ($assignments as $assignment) {
            $isUser = DB::table('users')->where('id', $assignment->teacher_id)->exists();
            $isTeacher = DB::table('teachers')->where('id', $assignment->teacher_id)->exists();

            if ($isUser && !$isTeacher) {
                // This teacher_id references a users.id — find the corresponding teacher
                $user = DB::table('users')->where('id', $assignment->teacher_id)->first();
                if ($user) {
                    $teacher = DB::table('teachers')->where('email', $user->email)->first();
                    if ($teacher) {
                        DB::table('teacher_assignments')
                            ->where('id', $assignment->id)
                            ->update(['teacher_id' => $teacher->id]);
                    }
                }
            }
        }
    }

    public function down()
    {
        // No down migration — the data fix is intentional
    }
};
