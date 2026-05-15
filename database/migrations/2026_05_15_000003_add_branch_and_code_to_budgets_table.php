<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            if (!Schema::hasColumn('budgets', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            }
            if (!Schema::hasColumn('budgets', 'budget_code')) {
                $table->string('budget_code')->nullable()->unique()->after('branch_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            if (Schema::hasColumn('budgets', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
            if (Schema::hasColumn('budgets', 'budget_code')) {
                $table->dropColumn('budget_code');
            }
        });
    }
};
