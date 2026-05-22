<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make students.user_id nullable so demo students can exist without a user account.
        // The StudentDataSeeder (real students) always creates a User first,
        // but SchoolDataSeeder (demo students) creates students without user accounts.
        DB::statement("ALTER TABLE students MODIFY COLUMN user_id BIGINT UNSIGNED NULL DEFAULT NULL");
    }

    public function down(): void
    {
        // Clean up nulls before reverting
        DB::statement("UPDATE students SET user_id = 0 WHERE user_id IS NULL");
        DB::statement("ALTER TABLE students MODIFY COLUMN user_id BIGINT UNSIGNED NOT NULL");
    }
};
