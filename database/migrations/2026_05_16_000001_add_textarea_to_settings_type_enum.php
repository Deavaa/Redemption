<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'textarea' to the settings type ENUM
        DB::statement("ALTER TABLE settings MODIFY COLUMN type ENUM('text','number','boolean','textarea','file','json') NOT NULL DEFAULT 'text'");
    }

    public function down(): void
    {
        // First reset any textarea rows back to text
        DB::statement("UPDATE settings SET type = 'text' WHERE type = 'textarea'");

        DB::statement("ALTER TABLE settings MODIFY COLUMN type ENUM('text','number','boolean','file','json') NOT NULL DEFAULT 'text'");
    }
};
