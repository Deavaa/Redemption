<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('subjects', 'is_active')) {
            DB::statement("ALTER TABLE subjects ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER priority");
        }

        // Also make code nullable if it isn't already (some subjects may not have a code)
        $col = DB::selectOne("SHOW COLUMNS FROM subjects WHERE Field = 'code'");
        if ($col && $col->Null === 'NO') {
            DB::statement("ALTER TABLE subjects MODIFY COLUMN code VARCHAR(50) NULL DEFAULT NULL");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('subjects', 'is_active')) {
            DB::statement("ALTER TABLE subjects DROP COLUMN is_active");
        }
    }
};
