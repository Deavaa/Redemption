<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lesson_plans')) {
            return; // lesson_plans table doesn't exist yet
        }
        Schema::table('lesson_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('lesson_plans', 'plan_type')) {
                $table->string('plan_type')->default('daily')->after('teacher_id'); // daily, weekly, yearly
            }
            if (!Schema::hasColumn('lesson_plans', 'week_start_date')) {
                $table->date('week_start_date')->nullable()->after('week_number');
            }
            if (!Schema::hasColumn('lesson_plans', 'week_end_date')) {
                $table->date('week_end_date')->nullable()->after('week_start_date');
            }
            if (!Schema::hasColumn('lesson_plans', 'daily_breakdown')) {
                $table->json('daily_breakdown')->nullable()->after('week_end_date'); // For weekly plans: daily activities
            }
            if (!Schema::hasColumn('lesson_plans', 'yearly_overview')) {
                $table->text('yearly_overview')->nullable()->after('daily_breakdown'); // For yearly plans
            }
            if (!Schema::hasColumn('lesson_plans', 'term_goals')) {
                $table->json('term_goals')->nullable()->after('yearly_overview'); // For yearly plans: per-term goals
            }
            if (!Schema::hasColumn('lesson_plans', 'department_head_status')) {
                $table->string('department_head_status')->default('pending')->after('status'); // pending, reviewed, approved, revision
            }
            if (!Schema::hasColumn('lesson_plans', 'department_head_comment')) {
                $table->text('department_head_comment')->nullable()->after('department_head_status');
            }
            if (!Schema::hasColumn('lesson_plans', 'department_head_id')) {
                $table->foreignId('department_head_id')->nullable()->after('department_head_comment');
            }
            if (!Schema::hasColumn('lesson_plans', 'department_head_reviewed_at')) {
                $table->timestamp('department_head_reviewed_at')->nullable()->after('department_head_id');
            }
            if (!Schema::hasColumn('lesson_plans', 'principal_status')) {
                $table->string('principal_status')->default('pending')->after('department_head_reviewed_at');
            }
            if (!Schema::hasColumn('lesson_plans', 'principal_comment')) {
                $table->text('principal_comment')->nullable()->after('principal_status');
            }
            if (!Schema::hasColumn('lesson_plans', 'principal_reviewed_id')) {
                $table->foreignId('principal_reviewed_id')->nullable()->after('principal_comment');
            }
            if (!Schema::hasColumn('lesson_plans', 'principal_reviewed_at')) {
                $table->timestamp('principal_reviewed_at')->nullable()->after('principal_reviewed_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lesson_plans', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['plan_type', 'week_start_date', 'week_end_date', 'daily_breakdown',
                'yearly_overview', 'term_goals', 'department_head_status', 'department_head_comment',
                'department_head_id', 'department_head_reviewed_at', 'principal_status',
                'principal_comment', 'principal_reviewed_id', 'principal_reviewed_at'] as $col) {
                if (Schema::hasColumn('lesson_plans', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
