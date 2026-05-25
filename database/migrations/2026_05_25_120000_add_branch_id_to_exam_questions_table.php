<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            // Add branch_id if it doesn't already exist
            if (!Schema::hasColumn('exam_questions', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('term_id')->constrained('branches')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            if (Schema::hasColumn('exam_questions', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });
    }
};
