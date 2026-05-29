<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Subject Content Note Bank — reusable teaching notes linked to lesson plans.
     * A note is owned by a teacher, belongs to a subject + class,
     * and can be shared across multiple sections (many-to-many pivot).
     * It can optionally be linked to a lesson plan.
     */
    public function up()
    {
        // ── Main notes table ────────────────────────────────────
        Schema::create('subject_content_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('lesson_plan_id')->nullable()->constrained('lesson_plans')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();          // brief summary
            $table->longText('content');                       // rich text / HTML content
            $table->string('topic')->nullable();              // e.g. "Chapter 3 - Algebra"
            $table->string('chapter')->nullable();            // e.g. "Chapter 3"
            $table->string('note_type')->default('general');  // general, summary, formula, definition, worked_example, reference
            $table->string('difficulty')->default('medium');   // easy, medium, hard
            $table->boolean('is_shared')->default(false);     // shared across branches/sections
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['teacher_id', 'academic_year_id']);
            $table->index(['subject_id', 'class_id']);
            $table->index('lesson_plan_id');
            $table->index('is_shared');
        });

        // ── Pivot: note ↔ sections (many-to-many) ──────────────
        // A single note can be used for multiple sections
        Schema::create('content_note_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_content_note_id')->constrained('subject_content_notes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['subject_content_note_id', 'section_id'], 'note_section_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_note_section');
        Schema::dropIfExists('subject_content_notes');
    }
};
