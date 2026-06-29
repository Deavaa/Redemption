<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fix mark_entries.id column to be AUTO_INCREMENT.
 *
 * Some databases (especially on XAMPP / MySQL strict mode) have the
 * mark_entries.id column without AUTO_INCREMENT, which causes:
 *   "Field 'id' doesn't have a default value"
 * when inserting new records via MarkEntry::create().
 *
 * This migration ensures the id column is:
 *   - BIGINT UNSIGNED
 *   - NOT NULL
 *   - AUTO_INCREMENT
 *   - PRIMARY KEY
 */
return new class extends Migration
{
    public function up(): void
    {
        // Check if the column is already AUTO_INCREMENT
        $colInfo = DB::selectOne("SHOW COLUMNS FROM mark_entries WHERE Field = 'id'");
        if (!$colInfo) {
            // Table doesn't exist or column missing — nothing to do
            return;
        }

        $extra = $colInfo->Extra ?? '';
        if (stripos($extra, 'auto_increment') !== false) {
            // Already auto_increment — nothing to do
            return;
        }

        // Drop the existing primary key if it exists (so we can modify the column)
        try {
            DB::statement('ALTER TABLE mark_entries DROP PRIMARY KEY');
        } catch (\Throwable $e) {
            // No primary key to drop — continue
        }

        // Modify the id column to be BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
        DB::statement('ALTER TABLE mark_entries MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
    }

    public function down(): void
    {
        // No down — we don't want to revert this fix
    }
};
