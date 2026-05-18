<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Students table: add full_name, migrate data, drop first_name/last_name
        Schema::table('students', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('user_id');
        });

        // Migrate existing data
        DB::statement("UPDATE students SET full_name = TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')))");

        // Make full_name NOT NULL after data migration (allow empty string for rows with no names)
        Schema::table('students', function (Blueprint $table) {
            $table->string('full_name')->nullable(false)->default('')->change();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });

        // 2. Teachers table: add full_name, migrate data, drop first_name/last_name
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('user_id');
        });

        // Migrate existing data
        DB::statement("UPDATE teachers SET full_name = TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')))");

        Schema::table('teachers', function (Blueprint $table) {
            $table->string('full_name')->nullable(false)->default('')->change();
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }

    public function down(): void
    {
        // Students: restore first_name/last_name
        Schema::table('students', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('user_id');
            $table->string('last_name')->nullable()->after('first_name');
        });

        // Split full_name back (first word = first_name, rest = last_name)
        DB::statement("UPDATE students SET first_name = SUBSTRING_INDEX(full_name, ' ', 1), last_name = TRIM(SUBSTRING(full_name, LOCATE(' ', full_name)))");

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('full_name');
        });

        // Teachers: restore first_name/last_name
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('user_id');
            $table->string('last_name')->nullable()->after('first_name');
        });

        DB::statement("UPDATE teachers SET first_name = SUBSTRING_INDEX(full_name, ' ', 1), last_name = TRIM(SUBSTRING(full_name, LOCATE(' ', full_name)))");

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('full_name');
        });
    }
};
