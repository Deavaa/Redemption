<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create a table for per-subject first-term override marks for mid-year entrants.
 *
 * Instead of a single first_term_mark_override on the students table, we need
 * per-subject marks because each subject has its own first-term total.
 *
 * This table stores: student_id + subject_id + academic_year_id + grand_total
 * for students who joined in Term 2 and need their first-term marks entered
 * manually (from their previous school).
 *
 * The mark entry form should NOT require individual CA/exam fields for these
 * students — just the grand_total per subject.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('first_term_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('grand_total', 8, 2)->nullable();
            $table->string('grade', 5)->nullable();
            $table->integer('rank_override')->nullable()->comment('Manual rank for this subject in Term 1');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'academic_year_id'], 'fto_unique');
            $table->index(['academic_year_id', 'class_id', 'section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('first_term_overrides');
    }
};
