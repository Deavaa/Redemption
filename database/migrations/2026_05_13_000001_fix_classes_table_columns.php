<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the existing foreign key that references 'users' - using raw SQL to check if it exists
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'classes'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");

        foreach ($foreignKeys as $fk) {
            $columns = DB::select("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'classes'
                  AND CONSTRAINT_NAME = ?
            ", [$fk->CONSTRAINT_NAME]);

            foreach ($columns as $col) {
                if ($col->COLUMN_NAME === 'teacher_id') {
                    DB::statement("ALTER TABLE classes DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                    break 2;
                }
            }
        }

        // Add the capacity column if it doesn't exist
        $columns = DB::select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'classes'
              AND COLUMN_NAME = 'capacity'
        ");

        if (empty($columns)) {
            Schema::table('classes', function (Blueprint $table) {
                $table->integer('capacity')->nullable()->after('numeric_name');
            });
        }

        // Re-add foreign key referencing 'teachers' table instead of 'users'
        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            // Drop the teachers foreign key
            $table->dropForeign(['teacher_id']);

            // Remove capacity column
            $table->dropColumn('capacity');
        });

        // Restore original foreign key referencing 'users'
        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
