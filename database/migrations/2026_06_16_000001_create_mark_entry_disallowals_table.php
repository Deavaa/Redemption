<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mark_entry_disallowals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('disallowed_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint: one active disallowal per teacher+class+section+subject+AY+term
            $table->unique(
                ['teacher_id', 'class_id', 'section_id', 'subject_id', 'academic_year_id', 'term_id'],
                'disallowal_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_entry_disallowals');
    }
};
