<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            // Drop the existing foreign key that references 'users'
            // Laravel names it: classes_teacher_id_foreign
            $table->dropForeign(['teacher_id']);

            // Add the capacity column
            $table->integer('capacity')->nullable()->after('numeric_name');
        });

        // Re-add foreign key referencing 'teachers' table instead of 'users'
        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            // Drop the teachers foreign key
            $table->dropForeign(['teacher_id']);

            // Remove capacity column
            $table->dropColumn('capacity');
        });

        // Restore original foreign key referencing 'users'
        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
