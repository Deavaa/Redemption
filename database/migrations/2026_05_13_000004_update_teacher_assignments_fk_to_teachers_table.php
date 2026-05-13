<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Drop existing foreign key on teacher_id (Laravel 12 compatible)
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'teacher_assignments'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");

        foreach ($foreignKeys as $fk) {
            $columns = DB::select("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'teacher_assignments'
                  AND CONSTRAINT_NAME = ?
            ", [$fk->CONSTRAINT_NAME]);

            foreach ($columns as $col) {
                if ($col->COLUMN_NAME === 'teacher_id') {
                    DB::statement("ALTER TABLE teacher_assignments DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                    break 2;
                }
            }
        }

        // Step 2: Clean up orphaned teacher_id values
        // Old FK referenced users table, new FK references teachers table.
        // Any teacher_id that doesn't exist in teachers table must be set to NULL.
        DB::statement("
            UPDATE teacher_assignments
            SET teacher_id = NULL
            WHERE teacher_id IS NOT NULL
              AND teacher_id NOT IN (SELECT id FROM teachers)
        ");

        // Step 3: Re-add foreign key referencing teachers table instead of users
        Schema::table('teacher_assignments', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });

        Schema::table('teacher_assignments', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
