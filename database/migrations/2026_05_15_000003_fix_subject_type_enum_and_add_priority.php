<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change the enum type column from ['theory','practical','both'] to ['compulsory','elective','optional']
        // Also add priority column for ordering in reports and certificates
        DB::statement("ALTER TABLE subjects MODIFY COLUMN type ENUM('compulsory','elective','optional') NOT NULL DEFAULT 'compulsory'");

        Schema::table('subjects', function (Blueprint $table) {
            $table->integer('priority')->default(0)->after('type')->comment('Display order: lower number = higher priority');
            $table->boolean('is_active')->default(true)->after('priority');
        });

        // Update existing rows that had old type values
        DB::statement("UPDATE subjects SET type = 'compulsory' WHERE type IN ('theory', 'both', 'core', 'Core', 'Compulsory')");
        DB::statement("UPDATE subjects SET type = 'elective' WHERE type IN ('practical', 'Elective')");
        DB::statement("UPDATE subjects SET type = 'optional' WHERE type NOT IN ('compulsory', 'elective', 'optional')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE subjects MODIFY COLUMN type ENUM('theory','practical','both') NOT NULL DEFAULT 'theory'");

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['priority', 'is_active']);
        });
    }
};
