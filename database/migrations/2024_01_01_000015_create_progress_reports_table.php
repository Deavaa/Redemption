<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->foreignId('term_id')->constrained()->cascadeOnDelete();
            $t->foreignId('class_id')->constrained()->cascadeOnDelete();
            $t->decimal('total_marks', 8, 2)->nullable();
            $t->decimal('percentage', 5, 2)->nullable();
            $t->string('grade')->nullable();
            $t->integer('rank')->nullable();
            $t->text('remarks')->nullable();
            $t->text('teacher_comment')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_reports');
    }
};