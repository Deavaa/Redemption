<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->foreignId('term_id')->constrained()->cascadeOnDelete();
            $t->decimal('attendance_percentage', 5, 2)->default(0);
            $t->enum('behavior_rating', ['excellent','good','average','poor'])->default('good');
            $t->enum('sports_rating', ['excellent','good','average','poor'])->default('good');
            $t->enum('extracurricular_rating', ['excellent','good','average','poor'])->default('good');
            $t->enum('overall_rating', ['excellent','good','average','poor'])->default('good');
            $t->text('remarks')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reports');
    }
};