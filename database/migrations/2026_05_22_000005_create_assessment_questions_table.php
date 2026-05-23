<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Assessment Questions — created by teachers for self-assessment
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title')->nullable();           // Optional short title
            $table->text('question_text');                  // The actual question
            $table->enum('question_type', ['multiple_choice', 'true_false', 'short_answer'])->default('multiple_choice');
            $table->text('hint')->nullable();              // Optional hint before answering
            $table->text('explanation')->nullable();        // Detailed explanation shown AFTER answering
            $table->text('worked_out_solution')->nullable(); // Step-by-step worked out solution
            $table->string('difficulty', 20)->default('medium'); // easy, medium, hard
            $table->string('topic')->nullable();           // Topic/chapter tag
            $table->integer('marks')->default(1);          // Points for this question
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['class_id', 'subject_id']);
            $table->index(['teacher_id', 'is_active']);
            $table->index('topic');
        });

        // Multiple choice options for each question
        Schema::create('assessment_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_question_id')->constrained()->cascadeOnDelete();
            $table->text('option_text');
            $table->string('option_label', 1)->default('A'); // A, B, C, D
            $table->boolean('is_correct')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Student answers — records each attempt
        Schema::create('assessment_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_option_id')->nullable()->constrained()->nullOnDelete(); // for MC/TF
            $table->text('student_answer')->nullable();     // for short_answer type
            $table->boolean('is_correct')->default(false);
            $table->integer('attempt_number')->default(1);
            $table->integer('time_spent_seconds')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'assessment_question_id', 'attempt_number'], 'unique_student_attempt');
            $table->index(['student_id', 'is_correct']);
            $table->index('assessment_question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_answers');
        Schema::dropIfExists('assessment_options');
        Schema::dropIfExists('assessment_questions');
    }
};
