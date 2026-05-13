<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing foreign key first
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

        // Make teacher_id nullable via raw SQL (Laravel 12 compatible)
        DB::statement("ALTER TABLE teacher_assignments MODIFY COLUMN teacher_id BIGINT UNSIGNED NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE teacher_assignments MODIFY COLUMN teacher_id BIGINT UNSIGNED NOT NULL");
    }
};
