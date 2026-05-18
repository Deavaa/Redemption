<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
                'admin',
                'teacher',
                'staff',
                'student',
                'parent',
                'librarian',
                'branch_principal',
                'general_manager',
                'cashier',
                'registrar',
                'finance',
                'hr'
            ) NOT NULL DEFAULT 'staff'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
                'admin',
                'teacher',
                'staff',
                'student',
                'parent',
                'librarian',
                'branch_principal',
                'general_manager',
                'cashier',
                'registrar'
            ) NOT NULL DEFAULT 'staff'");
        }
    }
};
