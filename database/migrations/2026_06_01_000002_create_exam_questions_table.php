<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('title');
            $table->enum('question_type', ['multiple_choice', 'true_false', 'short_answer', 'essay', 'fill_blank', 'mixed'])->default('mixed');
            $table->longText('content')->nullable();
            $table->integer('total_marks')->default(0);
            $table->integer('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->enum('status', ['draft', 'submitted', 'dept_approved', 'dept_rejected', 'principal_approved', 'principal_rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('dept_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dept_reviewed_at')->nullable();
            $table->text('dept_comments')->nullable();
            $table->foreignId('principal_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('principal_reviewed_at')->nullable();
            $table->text('principal_comments')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('teacher_id');
            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
