<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, update any existing rows with invalid type values to 'compulsory'
        DB::statement("UPDATE subjects SET type = 'compulsory' WHERE type NOT IN ('compulsory','elective','optional') OR type IS NULL");

        // Now safely alter the ENUM column
        DB::statement("ALTER TABLE subjects MODIFY COLUMN type ENUM('compulsory','elective','optional') NOT NULL DEFAULT 'compulsory'");

        // Add priority column if it doesn't exist
        if (!\Illuminate\Support\Facades\Schema::hasColumn('subjects', 'priority')) {
            DB::statement("ALTER TABLE subjects ADD COLUMN priority INT DEFAULT 0 AFTER type");
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE subjects MODIFY COLUMN type ENUM('theory','practical','both') NOT NULL DEFAULT 'theory'");

        if (\Illuminate\Support\Facades\Schema::hasColumn('subjects', 'priority')) {
            DB::statement("ALTER TABLE subjects DROP COLUMN priority");
        }
    }
};
