<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exam_questions')) {
            return; // Table already exists — skip to avoid duplicate migration error
        }
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('exam_id')->nullable()->constrained('exams')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('questions'); // JSON array of questions
            $table->string('question_type')->default('mixed');
            $table->integer('total_marks')->default(0);
            $table->integer('duration_minutes')->nullable();
            $table->enum('status', ['pending_department','pending_principal','approved','rejected_by_department','rejected_by_principal','revision'])->default('pending_department');
            $table->text('department_head_comment')->nullable();
            $table->foreignId('department_head_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('department_head_reviewed_at')->nullable();
            $table->text('principal_comment')->nullable();
            $table->foreignId('principal_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('principal_reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('teacher_id');
            $table->index('status');
            $table->index('subject_id');
            $table->index('exam_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
