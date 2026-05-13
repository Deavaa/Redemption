<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make email nullable on teachers table using raw SQL (Laravel 12 compatible - no ->change())
        DB::statement("ALTER TABLE teachers MODIFY COLUMN email VARCHAR(255) NULL DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE teachers MODIFY COLUMN email VARCHAR(255) NOT NULL");
    }
};
