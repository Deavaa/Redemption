<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make email nullable using raw SQL instead of ->change()
        // ->change() requires doctrine/dbal which is not installed, and it can
        // corrupt ENUM columns when reconstructing the table.
        DB::statement("ALTER TABLE teachers MODIFY COLUMN email VARCHAR(255) NULL DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE teachers MODIFY COLUMN email VARCHAR(255) NOT NULL");
    }
};
