<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'textarea' to the settings type ENUM — safe to re-run
        try {
            DB::statement("ALTER TABLE settings MODIFY COLUMN type ENUM('text','number','boolean','textarea','file','json') NOT NULL DEFAULT 'text'");
        } catch (\Throwable $e) {
            // Column may already have this ENUM value — ignore
        }
    }

    public function down(): void
    {
        DB::statement("UPDATE settings SET type = 'text' WHERE type = 'textarea'");
        DB::statement("ALTER TABLE settings MODIFY COLUMN type ENUM('text','number','boolean','file','json') NOT NULL DEFAULT 'text'");
    }
};
