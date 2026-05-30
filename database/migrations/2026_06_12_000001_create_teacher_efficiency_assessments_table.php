<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_efficiency_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('assessor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // 10 criteria scored 1-5
            $table->integer('lesson_delivery')->default(0);
            $table->integer('student_assessment')->default(0);
            $table->integer('curriculum_coverage')->default(0);
            $table->integer('classroom_environment')->default(0);
            $table->integer('student_participation')->default(0);
            $table->integer('professional_development')->default(0);
            $table->integer('communication')->default(0);
            $table->integer('time_management')->default(0);
            $table->integer('collaboration')->default(0);
            $table->integer('result_achievement')->default(0);

            $table->decimal('overall_score', 5, 2)->default(0);
            $table->enum('grade', ['excellent', 'good', 'satisfactory', 'needs_improvement', 'unsatisfactory'])->default('satisfactory');

            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('action_plan')->nullable();
            $table->text('comments')->nullable();

            $table->enum('status', ['draft', 'completed', 'acknowledged'])->default('draft');
            $table->timestamp('acknowledged_at')->nullable();
            $table->boolean('is_locked')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // One assessment per teacher per term per academic year
            $table->unique(['teacher_id', 'term_id', 'academic_year_id'], 'teacher_term_year_unique');

            $table->index('assessor_id');
            $table->index('branch_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_efficiency_assessments');
    }
};
