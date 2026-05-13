<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if column already exists before adding
        $exists = DB::select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'terms'
              AND COLUMN_NAME = 'term_number'
        ");

        if (empty($exists)) {
            Schema::table('terms', function (Blueprint $table) {
                $table->integer('term_number')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('terms', function (Blueprint $table) {
            $table->dropColumn('term_number');
        });
    }
};
