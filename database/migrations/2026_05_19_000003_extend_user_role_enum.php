<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter the users.role enum to include librarian and branch_principal
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','teacher','staff','student','parent','librarian','branch_principal') NOT NULL DEFAULT 'staff'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','teacher','staff','student','parent') NOT NULL DEFAULT 'staff'");
    }
};
