<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->integer('max_students')->nullable()->after('name');
            $table->foreignId('teacher_id')->nullable()->after('max_students')->constrained('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_id');
            $table->dropColumn('max_students');
        });
    }
};
