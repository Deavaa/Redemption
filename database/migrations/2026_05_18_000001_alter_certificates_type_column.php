<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change the enum column to a string to support all certificate types
        // MySQL doesn't support ALTER ENUM directly in all versions, so we change to varchar
        DB::statement("ALTER TABLE certificates MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'character'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE certificates MODIFY COLUMN type ENUM('character','transfer','experience','achievement','completion') NOT NULL DEFAULT 'character'");
    }

};
