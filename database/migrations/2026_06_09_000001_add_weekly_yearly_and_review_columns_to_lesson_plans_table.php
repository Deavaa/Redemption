<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lesson_plans')) {
            return;
        }

        Schema::table('lesson_plans', function (Blueprint $table) {
            // Weekly plan columns
            if (!Schema::hasColumn('lesson_plans', 'week_start_date')) {
                $table->date('week_start_date')->nullable()->after('plan_type');
            }
            if (!Schema::hasColumn('lesson_plans', 'week_end_date')) {
                $table->date('week_end_date')->nullable()->after('week_start_date');
            }
            if (!Schema::hasColumn('lesson_plans', 'daily_breakdown')) {
                $table->json('daily_breakdown')->nullable()->after('week_end_date');
            }

            // Yearly plan columns
            if (!Schema::hasColumn('lesson_plans', 'yearly_overview')) {
                $table->text('yearly_overview')->nullable()->after('daily_breakdown');
            }
            if (!Schema::hasColumn('lesson_plans', 'term_goals')) {
                $table->json('term_goals')->nullable()->after('yearly_overview');
            }

            // Department head review pipeline
            if (!Schema::hasColumn('lesson_plans', 'department_head_status')) {
                $table->string('department_head_status')->default('pending')->after('term_goals');
            }
            if (!Schema::hasColumn('lesson_plans', 'department_head_comment')) {
                $table->text('department_head_comment')->nullable()->after('department_head_status');
            }
            if (!Schema::hasColumn('lesson_plans', 'department_head_id')) {
                $table->foreignId('department_head_id')->nullable()->constrained('users')->nullOnDelete()->after('department_head_comment');
            }
            if (!Schema::hasColumn('lesson_plans', 'department_head_reviewed_at')) {
                $table->timestamp('department_head_reviewed_at')->nullable()->after('department_head_id');
            }

            // Principal review pipeline
            if (!Schema::hasColumn('lesson_plans', 'principal_status')) {
                $table->string('principal_status')->default('pending')->after('department_head_reviewed_at');
            }
            if (!Schema::hasColumn('lesson_plans', 'principal_comment')) {
                $table->text('principal_comment')->nullable()->after('principal_status');
            }
            if (!Schema::hasColumn('lesson_plans', 'principal_reviewed_id')) {
                $table->foreignId('principal_reviewed_id')->nullable()->constrained('users')->nullOnDelete()->after('principal_comment');
            }
            if (!Schema::hasColumn('lesson_plans', 'principal_reviewed_at')) {
                $table->timestamp('principal_reviewed_at')->nullable()->after('principal_reviewed_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('lesson_plans')) {
            return;
        }

        Schema::table('lesson_plans', function (Blueprint $table) {
            $columns = [
                'week_start_date', 'week_end_date', 'daily_breakdown',
                'yearly_overview', 'term_goals',
                'department_head_status', 'department_head_comment',
                'department_head_id', 'department_head_reviewed_at',
                'principal_status', 'principal_comment',
                'principal_reviewed_id', 'principal_reviewed_at',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('lesson_plans', $col)) {
                    // Drop foreign key first if it's a foreignId column
                    if (in_array($col, ['department_head_id', 'principal_reviewed_id'])) {
                        $table->dropForeign([$col]);
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
