<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add department_head to user role enum
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','super_admin','teacher','staff','student','parent','librarian','branch_principal','general_manager','cashier','registrar','finance','hr','department_head') NOT NULL DEFAULT 'staff'");
        }

        // Add department_id to teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('user_id')->constrained('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','super_admin','teacher','staff','student','parent','librarian','branch_principal','general_manager','cashier','registrar','finance','hr') NOT NULL DEFAULT 'staff'");
        }
    }
};
