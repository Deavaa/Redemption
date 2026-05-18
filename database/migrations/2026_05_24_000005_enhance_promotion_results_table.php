<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('promotion_results')) {
            Schema::create('promotion_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('from_class_id')->nullable()->constrained('classes')->nullOnDelete();
                $table->foreignId('to_class_id')->nullable()->constrained('classes')->nullOnDelete();
                $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
                $table->enum('status', ['promoted', 'detained', 'conditional'])->default('promoted');
                $table->decimal('average_score', 5, 2)->nullable();
                $table->decimal('overall_percentage', 5, 2)->nullable();
                $table->string('overall_grade', 10)->nullable();
                $table->decimal('grade_point_average', 3, 2)->nullable();
                $table->integer('total_subjects')->default(0);
                $table->integer('subjects_passed')->default(0);
                $table->integer('subjects_failed')->default(0);
                $table->integer('class_rank')->nullable();
                $table->integer('total_students')->nullable();
                $table->decimal('attendance_percentage', 5, 2)->nullable();
                $table->json('failure_reasons')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('processed_at')->nullable();
                $table->boolean('is_final')->default(false);
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->unique(['student_id', 'academic_year_id', 'term_id'], 'promotion_result_unique');
            });
            return;
        }

        // Add missing columns to existing table
        Schema::table('promotion_results', function (Blueprint $table) {
            if (!Schema::hasColumn('promotion_results', 'term_id')) {
                $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete()->after('academic_year_id');
            }
            if (!Schema::hasColumn('promotion_results', 'overall_percentage')) {
                $table->decimal('overall_percentage', 5, 2)->nullable()->after('average_score');
            }
            if (!Schema::hasColumn('promotion_results', 'overall_grade')) {
                $table->string('overall_grade', 10)->nullable()->after('overall_percentage');
            }
            if (!Schema::hasColumn('promotion_results', 'grade_point_average')) {
                $table->decimal('grade_point_average', 3, 2)->nullable()->after('overall_grade');
            }
            if (!Schema::hasColumn('promotion_results', 'total_subjects')) {
                $table->integer('total_subjects')->default(0)->after('grade_point_average');
            }
            if (!Schema::hasColumn('promotion_results', 'subjects_passed')) {
                $table->integer('subjects_passed')->default(0)->after('total_subjects');
            }
            if (!Schema::hasColumn('promotion_results', 'subjects_failed')) {
                $table->integer('subjects_failed')->default(0)->after('subjects_passed');
            }
            if (!Schema::hasColumn('promotion_results', 'class_rank')) {
                $table->integer('class_rank')->nullable()->after('subjects_failed');
            }
            if (!Schema::hasColumn('promotion_results', 'total_students')) {
                $table->integer('total_students')->nullable()->after('class_rank');
            }
            if (!Schema::hasColumn('promotion_results', 'attendance_percentage')) {
                $table->decimal('attendance_percentage', 5, 2)->nullable()->after('total_students');
            }
            if (!Schema::hasColumn('promotion_results', 'failure_reasons')) {
                $table->json('failure_reasons')->nullable()->after('attendance_percentage');
            }
            if (!Schema::hasColumn('promotion_results', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete()->after('failure_reasons');
            }
            if (!Schema::hasColumn('promotion_results', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('processed_by');
            }
            if (!Schema::hasColumn('promotion_results', 'is_final')) {
                $table->boolean('is_final')->default(false)->after('processed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_results');
    }
};
