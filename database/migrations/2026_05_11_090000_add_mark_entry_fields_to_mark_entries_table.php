<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mark_entries', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('ca1', 5, 2)->nullable();
            $table->decimal('ca2', 5, 2)->nullable();
            $table->decimal('ca3', 5, 2)->nullable();
            $table->decimal('ca4', 5, 2)->nullable();
            $table->decimal('ca5', 5, 2)->nullable();
            $table->decimal('ca6', 5, 2)->nullable();
            $table->decimal('ca7', 5, 2)->nullable();
            $table->decimal('ca8', 5, 2)->nullable();
            $table->decimal('ca9', 5, 2)->nullable();
            $table->decimal('ca10', 5, 2)->nullable();
            $table->decimal('conduct', 5, 2)->nullable();
            $table->decimal('handwriting', 5, 2)->nullable();
            $table->decimal('creativity', 5, 2)->nullable();
            $table->decimal('test1', 5, 2)->nullable();
            $table->decimal('test2', 5, 2)->nullable();
            $table->decimal('mid_term', 5, 2)->nullable();
            $table->decimal('final_exam', 5, 2)->nullable();
            $table->decimal('ca_total', 8, 2)->nullable();
            $table->decimal('exam_total', 8, 2)->nullable();
            $table->decimal('grand_total', 8, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mark_entries', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['term_id']);
            $table->dropForeign(['class_id']);
            $table->dropForeign(['section_id']);
            $table->dropForeign(['teacher_id']);

            $table->dropColumn([
                'subject_id',
                'academic_year_id',
                'term_id',
                'class_id',
                'section_id',
                'teacher_id',
                'ca1',
                'ca2',
                'ca3',
                'ca4',
                'ca5',
                'ca6',
                'ca7',
                'ca8',
                'ca9',
                'ca10',
                'conduct',
                'handwriting',
                'creativity',
                'test1',
                'test2',
                'mid_term',
                'final_exam',
                'ca_total',
                'exam_total',
                'grand_total',
            ]);
        });
    }
};
