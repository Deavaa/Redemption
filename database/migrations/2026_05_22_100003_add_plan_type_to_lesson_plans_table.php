<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lesson_plans') && !Schema::hasColumn('lesson_plans', 'plan_type')) {
            Schema::table('lesson_plans', function (Blueprint $table) {
                $table->string('plan_type')->default('daily')->after('teacher_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lesson_plans') && Schema::hasColumn('lesson_plans', 'plan_type')) {
            Schema::table('lesson_plans', function (Blueprint $table) {
                $table->dropColumn('plan_type');
            });
        }
    }
};
