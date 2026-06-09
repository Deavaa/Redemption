<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add staff sub-roles (janitor, guard, nurse, secretary) to the users role enum.
     * These roles allow branch principals to add specific types of support staff.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'admin',
            'super_admin',
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
            'hr',
            'department_head',
            'janitor',
            'guard',
            'nurse',
            'secretary'
        ) NOT NULL DEFAULT 'staff'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'admin',
            'super_admin',
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
            'hr',
            'department_head'
        ) NOT NULL DEFAULT 'staff'");
    }
};
