<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if a column exists in a table.
     */
    private function columnExists(string $table, string $column): bool
    {
        $result = DB::select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ", [$table, $column]);

        return !empty($result);
    }

    public function up(): void
    {
        // 1. Add missing columns to exams table
        Schema::table('exams', function (Blueprint $table) {
            if (!$this->columnExists('exams', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (!$this->columnExists('exams', 'start_time')) {
                $table->time('start_time')->nullable()->after('end_date');
            }
            if (!$this->columnExists('exams', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
            if (!$this->columnExists('exams', 'description')) {
                $table->text('description')->nullable()->after('passing_marks');
            }
        });

        // 2. Add missing columns to mark_entries table
        Schema::table('mark_entries', function (Blueprint $table) {
            if (!$this->columnExists('mark_entries', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->after('student_id')->constrained()->nullOnDelete();
            }
            if (!$this->columnExists('mark_entries', 'academic_year_id')) {
                $table->foreignId('academic_year_id')->nullable()->after('subject_id')->constrained()->nullOnDelete();
            }
            if (!$this->columnExists('mark_entries', 'term_id')) {
                $table->foreignId('term_id')->nullable()->after('academic_year_id')->constrained()->nullOnDelete();
            }
            if (!$this->columnExists('mark_entries', 'class_id')) {
                $table->foreignId('class_id')->nullable()->after('term_id')->constrained()->nullOnDelete();
            }
            if (!$this->columnExists('mark_entries', 'section_id')) {
                $table->foreignId('section_id')->nullable()->after('class_id')->constrained()->nullOnDelete();
            }
            if (!$this->columnExists('mark_entries', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('section_id')->constrained('teachers')->nullOnDelete();
            }
            if (!$this->columnExists('mark_entries', 'class_grade')) {
                $table->string('class_grade')->nullable()->after('grade');
            }

            $caColumns = ['ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10',
                          'conduct','handwriting','creativity','test1','test2',
                          'mid_term','final_exam','ca_total','exam_total','grand_total'];
            $prevCol = 'class_grade';
            foreach ($caColumns as $col) {
                if (!$this->columnExists('mark_entries', $col)) {
                    $table->decimal($col, 5, 2)->nullable()->after($prevCol);
                }
                $prevCol = $col;
            }
        });

        // 3. Add missing columns to progress_reports table
        Schema::table('progress_reports', function (Blueprint $table) {
            if (!$this->columnExists('progress_reports', 'max_marks')) {
                $table->decimal('max_marks', 8, 2)->nullable()->after('total_marks');
            }
            if (!$this->columnExists('progress_reports', 'overall_grade')) {
                $table->string('overall_grade')->nullable()->after('percentage');
            }
            if (!$this->columnExists('progress_reports', 'class_rank')) {
                $table->integer('class_rank')->nullable()->after('rank');
            }
            if (!$this->columnExists('progress_reports', 'status')) {
                $table->string('status')->default('draft')->after('teacher_comment');
            }
        });

        // 4. Add section_id to class_assets table
        Schema::table('class_assets', function (Blueprint $table) {
            if (!$this->columnExists('class_assets', 'section_id')) {
                $table->foreignId('section_id')->nullable()->after('class_id')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // 1. Remove added columns from exams
        Schema::table('exams', function (Blueprint $table) {
            $cols = ['end_date', 'start_time', 'end_time', 'description'];
            $existing = array_filter($cols, fn($c) => $this->columnExists('exams', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });

        // 2. Remove added columns from mark_entries
        Schema::table('mark_entries', function (Blueprint $table) {
            $fks = ['subject_id','academic_year_id','term_id','class_id','section_id','teacher_id'];
            foreach ($fks as $fk) {
                if ($this->columnExists('mark_entries', $fk)) {
                    try { $table->dropConstrainedForeignId($fk); } catch (\Throwable $e) {}
                }
            }
            $cols = ['class_grade','ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10',
                     'conduct','handwriting','creativity','test1','test2','mid_term','final_exam',
                     'ca_total','exam_total','grand_total'];
            $existing = array_filter($cols, fn($c) => $this->columnExists('mark_entries', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });

        // 3. Remove added columns from progress_reports
        Schema::table('progress_reports', function (Blueprint $table) {
            $cols = ['max_marks','overall_grade','class_rank','status'];
            $existing = array_filter($cols, fn($c) => $this->columnExists('progress_reports', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });

        // 4. Remove section_id from class_assets
        Schema::table('class_assets', function (Blueprint $table) {
            if ($this->columnExists('class_assets', 'section_id')) {
                try { $table->dropConstrainedForeignId('section_id'); } catch (\Throwable $e) {}
            }
        });
    }
};
