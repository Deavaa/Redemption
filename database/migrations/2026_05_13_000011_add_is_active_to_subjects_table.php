<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE subjects ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER priority");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE subjects DROP COLUMN is_active");
    }
};
