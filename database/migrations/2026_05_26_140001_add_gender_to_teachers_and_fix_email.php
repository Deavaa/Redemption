<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add gender column to teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])->nullable()->after('full_name');
        });

        // Fix existing empty string emails to NULL so the unique constraint works with multiple NULLs
        // MySQL allows multiple NULL values in a UNIQUE column, but only one empty string ''
        DB::statement("UPDATE teachers SET email = NULL WHERE email = ''");
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }
};
