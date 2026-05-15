<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: First ALTER the ENUM to include ALL values (old + new)
        // This way existing 'theory','practical','both' values are still valid
        DB::statement("ALTER TABLE subjects MODIFY COLUMN type ENUM('theory','practical','both','compulsory','elective','optional') NOT NULL DEFAULT 'compulsory'");

        // Step 2: NOW update old values to new ones
        DB::statement("UPDATE subjects SET type = 'compulsory' WHERE type IN ('theory','both') OR type IS NULL");
        DB::statement("UPDATE subjects SET type = 'elective' WHERE type = 'practical'");

        // Step 3: Finally narrow the ENUM to only the new values
        DB::statement("ALTER TABLE subjects MODIFY COLUMN type ENUM('compulsory','elective','optional') NOT NULL DEFAULT 'compulsory'");

        // Add priority column if it doesn't exist
        if (!Schema::hasColumn('subjects', 'priority')) {
            DB::statement("ALTER TABLE subjects ADD COLUMN priority INT DEFAULT 0 AFTER type");
        }
    }

    public function down(): void
    {
        // Widen ENUM to include old values
        DB::statement("ALTER TABLE subjects MODIFY COLUMN type ENUM('theory','practical','both','compulsory','elective','optional') NOT NULL DEFAULT 'theory'");

        // Map back
        DB::statement("UPDATE subjects SET type = 'theory' WHERE type IN ('compulsory','both') OR type IS NULL");
        DB::statement("UPDATE subjects SET type = 'practical' WHERE type = 'elective'");

        // Narrow to old values only
        DB::statement("ALTER TABLE subjects MODIFY COLUMN type ENUM('theory','practical','both') NOT NULL DEFAULT 'theory'");

        if (Schema::hasColumn('subjects', 'priority')) {
            DB::statement("ALTER TABLE subjects DROP COLUMN priority");
        }
    }
};
