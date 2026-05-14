<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $t) {
            $t->foreignId('branch_id')->nullable()->after('academic_year_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $t) {
            $t->dropForeign(['branch_id']);
            $t->dropColumn('branch_id');
        });
    }
};
