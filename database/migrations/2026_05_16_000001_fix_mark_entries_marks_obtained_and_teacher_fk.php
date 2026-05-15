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
        Schema::table('mark_entries', function (Blueprint $table) {
            $table->decimal('marks_obtained', 8, 2)->nullable()->change();
        });

        // Also fix teacher_id FK: drop old constraint (may point to users) and re-add pointing to teachers
        // First check if there are existing FK constraints on teacher_id
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
                if (str_contains($name, 'teacher_id')) {
                    $table->dropForeign($name);
                }
            }
        });

        // Re-add with correct reference to teachers table
        Schema::table('mark_entries', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mark_entries', function (Blueprint $table) {
            $table->decimal('marks_obtained', 8, 2)->default(0)->change();
        });

        // Revert teacher_id FK back
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
                if (str_contains($name, 'teacher_id')) {
                    $table->dropForeign($name);
                }
            }
        });

        Schema::table('mark_entries', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
