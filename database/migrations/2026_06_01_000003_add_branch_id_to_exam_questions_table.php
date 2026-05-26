<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if the table doesn't exist yet (it will be created by a later migration
        // that already includes branch_id). This ALTER only matters for databases where
        // exam_questions was created by an older migration WITHOUT branch_id.
        if (!Schema::hasTable('exam_questions')) {
            return;
        }

        Schema::table('exam_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_questions', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('term_id')->constrained('branches')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('exam_questions')) {
            return;
        }

        Schema::table('exam_questions', function (Blueprint $table) {
            if (Schema::hasColumn('exam_questions', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });
    }
};
