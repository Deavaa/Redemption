<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();

            // Rating criteria (1-5 scale each)
            $table->unsignedTinyInteger('teaching_quality')->default(0)->comment('1=Poor, 5=Excellent');
            $table->unsignedTinyInteger('communication')->default(0)->comment('1=Poor, 5=Excellent');
            $table->unsignedTinyInteger('punctuality')->default(0)->comment('1=Poor, 5=Excellent');
            $table->unsignedTinyInteger('subject_knowledge')->default(0)->comment('1=Poor, 5=Excellent');
            $table->unsignedTinyInteger('helpfulness')->default(0)->comment('1=Poor, 5=Excellent');
            $table->unsignedTinyInteger('fairness')->default(0)->comment('1=Poor, 5=Excellent');

            // Overall
            $table->decimal('overall_score', 5, 2)->default(0)->comment('Average of all criteria, scaled 0-100');
            $table->string('grade', 20)->nullable()->comment('excellent, good, satisfactory, needs_improvement, unsatisfactory');

            // Text feedback
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('additional_comments')->nullable();

            // Privacy
            $table->boolean('is_anonymous')->default(true)->comment('If true, student identity hidden from teacher');

            // Status
            $table->timestamp('submitted_at')->nullable();
            $table->string('status', 20)->default('submitted')->comment('submitted, flagged');

            // Unique constraint: one review per student per teacher per term
            $table->unique(['student_id', 'teacher_id', 'term_id'], 'unique_student_teacher_term_review');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_reviews');
    }
};
