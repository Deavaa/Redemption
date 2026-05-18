<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The full_name merge migration used ->change() which can corrupt ENUM columns
        // (Doctrine DBAL reconstructs the table and may lose ENUM definitions).
        // Re-apply the correct ENUM to ensure status works properly.
        DB::statement("ALTER TABLE teachers MODIFY COLUMN status ENUM('active','inactive','on_leave') NOT NULL DEFAULT 'active'");

        // Fix any rows that may have been corrupted — reset empty/null status to 'active'
        DB::statement("UPDATE teachers SET status = 'active' WHERE status IS NULL OR status = '' OR status NOT IN ('active','inactive','on_leave')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE teachers MODIFY COLUMN status ENUM('active','inactive','on_leave') NOT NULL DEFAULT 'active'");
    }
};
