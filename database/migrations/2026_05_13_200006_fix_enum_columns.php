<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix certificates type enum to include all types used in controllers
        DB::statement("ALTER TABLE certificates MODIFY COLUMN type ENUM('character','transfer','experience','achievement','completion','bonafide','academic','foldable','other') NOT NULL DEFAULT 'character'");

        // Fix performance_reports rating columns from enum to decimal (0-10 scale as controller expects)
        DB::statement("ALTER TABLE performance_reports MODIFY COLUMN behavior_rating DECIMAL(5,2) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE performance_reports MODIFY COLUMN sports_rating DECIMAL(5,2) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE performance_reports MODIFY COLUMN extracurricular_rating DECIMAL(5,2) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE performance_reports MODIFY COLUMN overall_rating DECIMAL(5,2) NULL DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE certificates MODIFY COLUMN type ENUM('character','transfer','experience','achievement','completion') NOT NULL DEFAULT 'character'");

        DB::statement("ALTER TABLE performance_reports MODIFY COLUMN behavior_rating ENUM('excellent','good','average','poor') NULL DEFAULT NULL");
        DB::statement("ALTER TABLE performance_reports MODIFY COLUMN sports_rating ENUM('excellent','good','average','poor') NULL DEFAULT NULL");
        DB::statement("ALTER TABLE performance_reports MODIFY COLUMN extracurricular_rating ENUM('excellent','good','average','poor') NULL DEFAULT NULL");
        DB::statement("ALTER TABLE performance_reports MODIFY COLUMN overall_rating ENUM('excellent','good','average','poor') NULL DEFAULT NULL");
    }
};
