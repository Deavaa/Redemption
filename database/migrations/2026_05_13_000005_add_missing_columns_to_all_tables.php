<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add missing columns to exams table
        Schema::table('exams', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('start_date');
            $table->time('start_time')->nullable()->after('end_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->text('description')->nullable()->after('passing_marks');
        });

        // 2. Add missing columns to mark_entries table
        Schema::table('mark_entries', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->after('student_id')->constrained()->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->after('subject_id')->constrained()->nullOnDelete();
            $table->foreignId('term_id')->nullable()->after('academic_year_id')->constrained()->nullOnDelete();
            $table->foreignId('class_id')->nullable()->after('term_id')->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->after('class_id')->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->after('section_id')->constrained('teachers')->nullOnDelete();
            $table->string('class_grade')->nullable()->after('grade');
            $table->decimal('ca1', 5, 2)->nullable()->after('class_grade');
            $table->decimal('ca2', 5, 2)->nullable()->after('ca1');
            $table->decimal('ca3', 5, 2)->nullable()->after('ca2');
            $table->decimal('ca4', 5, 2)->nullable()->after('ca3');
            $table->decimal('ca5', 5, 2)->nullable()->after('ca4');
            $table->decimal('ca6', 5, 2)->nullable()->after('ca5');
            $table->decimal('ca7', 5, 2)->nullable()->after('ca6');
            $table->decimal('ca8', 5, 2)->nullable()->after('ca7');
            $table->decimal('ca9', 5, 2)->nullable()->after('ca8');
            $table->decimal('ca10', 5, 2)->nullable()->after('ca9');
            $table->decimal('conduct', 5, 2)->nullable()->after('ca10');
            $table->decimal('handwriting', 5, 2)->nullable()->after('conduct');
            $table->decimal('creativity', 5, 2)->nullable()->after('handwriting');
            $table->decimal('test1', 5, 2)->nullable()->after('creativity');
            $table->decimal('test2', 5, 2)->nullable()->after('test1');
            $table->decimal('mid_term', 5, 2)->nullable()->after('test2');
            $table->decimal('final_exam', 5, 2)->nullable()->after('mid_term');
            $table->decimal('ca_total', 5, 2)->nullable()->after('final_exam');
            $table->decimal('exam_total', 5, 2)->nullable()->after('ca_total');
            $table->decimal('grand_total', 5, 2)->nullable()->after('exam_total');
        });

        // 3. Add missing columns to progress_reports table
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->decimal('max_marks', 8, 2)->nullable()->after('total_marks');
            $table->string('overall_grade')->nullable()->after('percentage');
            $table->integer('class_rank')->nullable()->after('rank');
            $table->string('status')->default('draft')->after('teacher_comment');
        });

        // 4. Add section_id to class_assets table
        Schema::table('class_assets', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('class_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        // 1. Remove added columns from exams
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['end_date', 'start_time', 'end_time', 'description']);
        });

        // 2. Remove added columns from mark_entries
        Schema::table('mark_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subject_id');
            $table->dropConstrainedForeignId('academic_year_id');
            $table->dropConstrainedForeignId('term_id');
            $table->dropConstrainedForeignId('class_id');
            $table->dropConstrainedForeignId('section_id');
            $table->dropConstrainedForeignId('teacher_id');
            $table->dropColumn([
                'class_grade', 'ca1', 'ca2', 'ca3', 'ca4', 'ca5', 'ca6', 'ca7', 'ca8', 'ca9', 'ca10',
                'conduct', 'handwriting', 'creativity', 'test1', 'test2', 'mid_term', 'final_exam',
                'ca_total', 'exam_total', 'grand_total'
            ]);
        });

        // 3. Remove added columns from progress_reports
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->dropColumn(['max_marks', 'overall_grade', 'class_rank', 'status']);
        });

        // 4. Remove section_id from class_assets
        Schema::table('class_assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('section_id');
        });
    }
};
