<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lesson_plans')) {
            Schema::create('lesson_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
                $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
                $table->foreignId('term_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('objectives')->nullable();
                $table->text('materials')->nullable();
                $table->text('activities')->nullable();
                $table->text('assessment')->nullable();
                $table->text('homework')->nullable();
                $table->text('notes')->nullable();
                $table->integer('week_number')->default(1);
                $table->date('lesson_date')->nullable();
                $table->integer('duration_minutes')->default(45);
                $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved', 'revision'])->default('draft');
                $table->text('reviewer_comment')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['teacher_id', 'academic_year_id', 'term_id']);
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_plans');
    }
};
