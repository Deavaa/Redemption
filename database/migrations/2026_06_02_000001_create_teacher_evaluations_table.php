<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->string('evaluation_type')->default('periodic'); // periodic, annual, observation, peer_review
            $table->date('evaluation_date');
            // Scoring criteria (1-5 scale)
            $table->integer('teaching_quality')->default(0); // 1-5
            $table->integer('student_engagement')->default(0);
            $table->integer('classroom_management')->default(0);
            $table->integer('lesson_preparation')->default(0);
            $table->integer('professional_conduct')->default(0);
            $table->integer('communication_skills')->default(0);
            $table->integer('punctuality')->default(0);
            $table->integer('student_results')->default(0);
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->enum('grade', ['excellent', 'good', 'satisfactory', 'needs_improvement', 'unsatisfactory'])->default('satisfactory');
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('comments')->nullable();
            $table->enum('status', ['draft', 'completed', 'acknowledged'])->default('completed');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index('teacher_id');
            $table->index('evaluator_id');
            $table->index('evaluation_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_evaluations');
    }
};
