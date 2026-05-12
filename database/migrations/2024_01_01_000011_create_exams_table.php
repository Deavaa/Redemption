<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $t) {
            $t->id();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->foreignId('term_id')->constrained()->cascadeOnDelete();
            $t->foreignId('class_id')->constrained()->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->enum('type', ['exam','quiz','assignment','project','midterm','final'])->default('exam');
            $t->decimal('total_marks', 8, 2)->default(100);
            $t->decimal('passing_marks', 8, 2)->default(50);
            // ...
            $t->date('start_date'); // Changed from exam_date
// ...
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};