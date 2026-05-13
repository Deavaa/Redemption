<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Convert ENUM to VARCHAR first so we can update existing data without truncation
        DB::statement("ALTER TABLE subjects MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'compulsory'");

        // Step 2: Update existing rows that had old type values
        DB::statement("UPDATE subjects SET type = 'compulsory' WHERE type IN ('theory', 'both', 'core', 'Core', 'Compulsory')");
        DB::statement("UPDATE subjects SET type = 'elective' WHERE type IN ('practical', 'Elective')");
        DB::statement("UPDATE subjects SET type = 'optional' WHERE type NOT IN ('compulsory', 'elective', 'optional')");

        // Step 3: Now safely convert to the new ENUM
        DB::statement("ALTER TABLE subjects MODIFY COLUMN type ENUM('compulsory','elective','optional') NOT NULL DEFAULT 'compulsory'");

        // Step 4: Add priority and is_active columns
        Schema::table('subjects', function (Blueprint $table) {
            $table->integer('priority')->default(0)->after('type')->comment('Display order: lower number = higher priority');
            $table->boolean('is_active')->default(true)->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['priority', 'is_active']);
        });

        DB::statement("ALTER TABLE subjects MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'theory'");
        DB::statement("UPDATE subjects SET type = 'theory' WHERE type IN ('compulsory', 'optional')");
        DB::statement("UPDATE subjects SET type = 'practical' WHERE type = 'elective'");
        DB::statement("ALTER TABLE subjects MODIFY COLUMN type ENUM('theory','practical','both') NOT NULL DEFAULT 'theory'");
    }
};
