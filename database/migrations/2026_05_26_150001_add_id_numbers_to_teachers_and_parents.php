<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add teacher_id_number to teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('teacher_id_number')->nullable()->unique()->after('full_name');
        });

        // Add parent_id_number to parents table
        Schema::table('parents', function (Blueprint $table) {
            $table->string('parent_id_number')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('teacher_id_number');
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn('parent_id_number');
        });
    }
};
