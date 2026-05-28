<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix teacher_assignments.teacher_id values AND populate teachers.user_id.
 *
 * Root cause: teacher_assignments.teacher_id originally stored users.id values,
 * but the FK was changed to reference teachers.id.  The FK migration
 * (2026_05_13_000004) set teacher_id = NULL for values not found in teachers.
 *
 * Additionally, teachers.user_id was never populated after the column was added
 * (migration 2026_05_15_000001), so the user→teacher link is missing.
 *
 * This migration:
 *  1. Populates teachers.user_id by matching on email
 *  2. Remaps teacher_assignments.teacher_id from users.id → teachers.id
 *  3. Recovers NULLed teacher_ids from section homeroom teacher
 *  4. Recovers remaining NULLs from class+subject+year peers
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Step 0: Populate teachers.user_id ──────────────────────────────
        // The user_id column was added but never filled.  Match on email.
        DB::statement("
            UPDATE teachers t
            INNER JOIN users u ON u.email = t.email
            SET t.user_id = u.id
            WHERE t.user_id IS NULL
              AND t.email IS NOT NULL
              AND u.email IS NOT NULL
        ");

        // ── Step 1: Remap existing (non-NULL) teacher_id values ──────────
        // If teacher_id currently holds a users.id value, replace it with the
        // corresponding teachers.id via the teachers.user_id column.
        DB::statement("
            UPDATE teacher_assignments ta
            INNER JOIN teachers t ON t.user_id = ta.teacher_id
            SET ta.teacher_id = t.id
            WHERE ta.teacher_id IS NOT NULL
              AND ta.teacher_id != t.id
        ");

        // ── Step 2: Recover NULL teacher_ids from section homeroom teacher ──
        // When a teacher_assignment has a section_id, the sections.teacher_id
        // (homeroom teacher) often matches the assignment's teacher.
        DB::statement("
            UPDATE teacher_assignments ta
            INNER JOIN sections s ON s.id = ta.section_id
            SET ta.teacher_id = s.teacher_id
            WHERE ta.teacher_id IS NULL
              AND s.teacher_id IS NOT NULL
        ");

        // ── Step 3: Recover remaining NULL teacher_ids from class-based lookup ──
        // If the teacher still couldn't be determined, try looking at the
        // teacher_assignments for the same class + subject + academic_year
        // that DO have a teacher_id and copy it.
        DB::statement("
            UPDATE teacher_assignments ta_null
            INNER JOIN (
                SELECT class_id, subject_id, academic_year_id, MIN(teacher_id) AS tid
                FROM teacher_assignments
                WHERE teacher_id IS NOT NULL
                GROUP BY class_id, subject_id, academic_year_id
            ) ta_known
              ON ta_known.class_id        = ta_null.class_id
             AND ta_known.subject_id      = ta_null.subject_id
             AND ta_known.academic_year_id = ta_null.academic_year_id
            SET ta_null.teacher_id = ta_known.tid
            WHERE ta_null.teacher_id IS NULL
              AND ta_known.tid IS NOT NULL
        ");
    }

    public function down(): void
    {
        // No reversible down — the original users.id values are lost.
        // Leaving as no-op since the up() is a one-time data fix.
    }
};
