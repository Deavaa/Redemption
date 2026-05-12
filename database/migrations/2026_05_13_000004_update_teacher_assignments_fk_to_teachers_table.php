<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {
            // Drop the old foreign key if it exists
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $foreignKeys = $sm->listTableForeignKeys('teacher_assignments');
            foreach ($foreignKeys as $fk) {
                if (in_array('teacher_id', $fk->getLocalColumns())) {
                    $table->dropForeign($fk->getName());
                    break;
                }
            }
        });

        Schema::table('teacher_assignments', function (Blueprint $table) {
            // Re-add foreign key referencing teachers table instead of users
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });

        Schema::table('teacher_assignments', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
