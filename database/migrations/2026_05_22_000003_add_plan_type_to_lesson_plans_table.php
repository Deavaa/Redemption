<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->string('plan_type')->default('daily')->after('teacher_id'); // daily, weekly, yearly
            $table->date('week_start_date')->nullable()->after('week_number');
            $table->date('week_end_date')->nullable()->after('week_start_date');
            $table->json('daily_breakdown')->nullable()->after('week_end_date'); // For weekly plans: daily activities
            $table->text('yearly_overview')->nullable()->after('daily_breakdown'); // For yearly plans
            $table->json('term_goals')->nullable()->after('yearly_overview'); // For yearly plans: per-term goals
            $table->string('department_head_status')->default('pending')->after('status'); // pending, reviewed, approved, revision
            $table->text('department_head_comment')->nullable()->after('department_head_status');
            $table->foreignId('department_head_id')->nullable()->after('department_head_comment');
            $table->timestamp('department_head_reviewed_at')->nullable()->after('department_head_id');
            $table->string('principal_status')->default('pending')->after('department_head_reviewed_at');
            $table->text('principal_comment')->nullable()->after('principal_status');
            $table->foreignId('principal_reviewed_id')->nullable()->after('principal_comment');
            $table->timestamp('principal_reviewed_at')->nullable()->after('principal_reviewed_id');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->dropColumn([
                'plan_type', 'week_start_date', 'week_end_date', 'daily_breakdown',
                'yearly_overview', 'term_goals',
                'department_head_status', 'department_head_comment', 'department_head_id', 'department_head_reviewed_at',
                'principal_status', 'principal_comment', 'principal_reviewed_id', 'principal_reviewed_at',
            ]);
        });
    }
};
