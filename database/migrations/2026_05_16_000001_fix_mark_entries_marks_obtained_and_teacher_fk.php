<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make marks_obtained nullable since the new mark entry system
        // uses grand_total as the primary total field
        try {
            if (Schema::hasColumn('mark_entries', 'marks_obtained')) {
                Schema::table('mark_entries', function (Blueprint $table) {
                    $table->decimal('marks_obtained', 8, 2)->nullable()->change();
                });
            }
        } catch (\Throwable $e) {
            // Column may already be nullable or doctrine/dbal not installed
        }

        // Fix teacher_id FK: drop old constraint (may point to users) and re-add pointing to teachers
        try {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'mark_entries'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");
        } catch (\Throwable $e) {
            $foreignKeys = [];
        }

        $teacherFkDropped = false;
        if (!empty($foreignKeys)) {
            Schema::table('mark_entries', function (Blueprint $table) use ($foreignKeys, &$teacherFkDropped) {
                foreach ($foreignKeys as $fk) {
                    $name = is_object($fk) ? ($fk->CONSTRAINT_NAME ?? '') : '';
                    if ($name && str_contains($name, 'teacher_id')) {
                        try {
                            $table->dropForeign($name);
                            $teacherFkDropped = true;
                        } catch (\Throwable $e) {
                            // FK may already be dropped
                        }
                    }
                }
            });
        }

        // Clean up orphaned teacher_id values before adding the new FK.
        // Old FK may have pointed to users.id, so some values won't exist in teachers.
        try {
            DB::statement("
                UPDATE mark_entries
                SET teacher_id = NULL
                WHERE teacher_id IS NOT NULL
                  AND teacher_id NOT IN (SELECT id FROM teachers)
            ");
        } catch (\Throwable $e) {
            // teachers table may not exist yet
        }

        // Check if a teacher_id FK pointing to teachers already exists
        $hasTeacherFk = false;
        try {
            $hasTeacherFk = collect(DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'mark_entries'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND CONSTRAINT_NAME LIKE '%teacher_id%'
            "))->isEmpty() === false;
        } catch (\Throwable $e) {
            // Ignore errors
        }

        // Add FK pointing to teachers table (if not already present)
        if (!$hasTeacherFk && Schema::hasTable('teachers')) {
            Schema::table('mark_entries', function (Blueprint $table) {
                $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        try {
            if (Schema::hasColumn('mark_entries', 'marks_obtained')) {
                Schema::table('mark_entries', function (Blueprint $table) {
                    $table->decimal('marks_obtained', 8, 2)->default(0)->change();
                });
            }
        } catch (\Throwable $e) {}

        try {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'mark_entries'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");
        } catch (\Throwable $e) {
            $foreignKeys = [];
        }

        $teacherFkDropped = false;
        if (!empty($foreignKeys)) {
            Schema::table('mark_entries', function (Blueprint $table) use ($foreignKeys, &$teacherFkDropped) {
                foreach ($foreignKeys as $fk) {
                    $name = is_object($fk) ? ($fk->CONSTRAINT_NAME ?? '') : '';
                    if ($name && str_contains($name, 'teacher_id')) {
                        try {
                            $table->dropForeign($name);
                            $teacherFkDropped = true;
                        } catch (\Throwable $e) {}
                    }
                }
            });
        }

        if ($teacherFkDropped) {
            Schema::table('mark_entries', function (Blueprint $table) {
                $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }
};
