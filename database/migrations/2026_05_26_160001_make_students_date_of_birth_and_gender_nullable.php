<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Make date_of_birth and gender nullable on students table.
 *
 * The bulk enrollment and single student creation forms allow these fields
 * to be omitted, but the original migration defined them as NOT NULL.
 * This caused silent transaction rollbacks when students were created
 * without providing date_of_birth or gender.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Make date_of_birth nullable
        DB::statement("ALTER TABLE students MODIFY COLUMN date_of_birth DATE NULL DEFAULT NULL");

        // Make gender nullable (enum with empty string default to handle existing NOT NULL constraint)
        DB::statement("ALTER TABLE students MODIFY COLUMN gender ENUM('male', 'female', 'other') NULL DEFAULT NULL");
    }

    public function down(): void
    {
        // Revert: set defaults for null values before making NOT NULL
        DB::statement("UPDATE students SET date_of_birth = '2000-01-01' WHERE date_of_birth IS NULL");
        DB::statement("UPDATE students SET gender = 'male' WHERE gender IS NULL");

        DB::statement("ALTER TABLE students MODIFY COLUMN date_of_birth DATE NOT NULL");
        DB::statement("ALTER TABLE students MODIFY COLUMN gender ENUM('male', 'female', 'other') NOT NULL");
    }
};
