<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Definitive fix for the teacher status column.
 *
 * ROOT CAUSE: The merge_first_last_name migration used ->change() which requires
 * doctrine/dbal (not installed in this project). This caused the ENUM status column
 * to be corrupted — either left as VARCHAR with wrong defaults, or the migration
 * failed entirely leaving the column in an inconsistent state.
 *
 * FIX: Convert status from ENUM to VARCHAR(20) NOT NULL DEFAULT 'active' using
 * raw SQL only. VARCHAR is immune to Doctrine DBAL corruption and Laravel's
 * validation already enforces the allowed values (active, inactive, on_leave).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Convert status to VARCHAR(20) — this works regardless of current type
        DB::statement("ALTER TABLE teachers MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'");

        // Step 2: Fix any corrupted data — reset empty/null/invalid values to 'active'
        DB::statement("UPDATE teachers SET status = 'active' WHERE status IS NULL OR status = '' OR status NOT IN ('active','inactive','on_leave')");

        // Step 3: Also fix the email column to be nullable (in case the ->change() migration failed)
        try {
            DB::statement("ALTER TABLE teachers MODIFY COLUMN email VARCHAR(255) NULL DEFAULT NULL");
        } catch (\Exception $e) {
            // Column might already be nullable — ignore
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE teachers MODIFY COLUMN status ENUM('active','inactive','on_leave') NOT NULL DEFAULT 'active'");
    }
};
