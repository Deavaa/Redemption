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
              AND TABLE_NAME = 'branches'
              AND COLUMN_NAME = 'is_headquarters'
        ");

        if (empty($exists)) {
            Schema::table('branches', function (Blueprint $table) {
                $table->boolean('is_headquarters')->default(false)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('is_headquarters');
        });
    }
};
