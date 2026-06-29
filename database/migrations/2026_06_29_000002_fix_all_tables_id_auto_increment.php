<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fix ALL tables missing AUTO_INCREMENT on their 'id' column.
 *
 * On some MySQL installations (especially XAMPP with strict mode), tables
 * were created without AUTO_INCREMENT on the 'id' column. This causes:
 *   "SQLSTATE[HY000]: General error: 1364 Field 'id' doesn't have a default value"
 * when inserting new records via Eloquent::create().
 *
 * This migration scans ALL tables in the database for an 'id' column
 * that is NOT AUTO_INCREMENT and fixes them all in one pass.
 *
 * Affected tables observed so far:
 *   - migrations
 *   - mark_entries
 *   - settings
 *   - sessions (if using database driver)
 *   - cache (if using database driver)
 *   - and potentially many others
 *
 * This migration is idempotent — safe to run multiple times.
 */
return new class extends Migration
{
    public function up(): void
    {
        $dbName = DB::getDatabaseName();

        // Get ALL tables that have an 'id' column without AUTO_INCREMENT
        $tables = DB::select("
            SELECT TABLE_NAME, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND COLUMN_NAME = 'id'
              AND EXTRA NOT LIKE '%auto_increment%'
            ORDER BY TABLE_NAME
        ", [$dbName]);

        $fixed = 0;
        foreach ($tables as $table) {
            $tableName = $table->TABLE_NAME;
            $columnType = $table->COLUMN_TYPE; // e.g. 'bigint unsigned' or 'int'

            // Determine the appropriate type
            $isBigint = strpos($columnType, 'bigint') !== false;
            $typeSql = $isBigint ? 'BIGINT UNSIGNED' : 'INT UNSIGNED';

            try {
                // Drop existing primary key if present (so we can modify the column)
                try {
                    DB::statement("ALTER TABLE `{$tableName}` DROP PRIMARY KEY");
                } catch (\Throwable $e) {
                    // No primary key to drop — continue
                }

                // Modify the id column to be AUTO_INCREMENT PRIMARY KEY
                DB::statement("ALTER TABLE `{$tableName}` MODIFY COLUMN `id` {$typeSql} NOT NULL AUTO_INCREMENT PRIMARY KEY");
                $fixed++;
                echo "Fixed table: {$tableName} ({$typeSql} AUTO_INCREMENT)\n";
            } catch (\Throwable $e) {
                echo "Could not fix table {$tableName}: " . $e->getMessage() . "\n";
                // Continue to next table — don't abort the whole migration
            }
        }

        if ($fixed > 0) {
            echo "\nTotal tables fixed: {$fixed}\n";
        } else {
            echo "\nAll tables already have AUTO_INCREMENT on 'id' — nothing to fix.\n";
        }
    }

    public function down(): void
    {
        // No down — we don't want to revert this fix
    }
};
