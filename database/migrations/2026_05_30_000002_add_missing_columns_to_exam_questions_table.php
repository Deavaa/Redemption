<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            // Add columns that the controller expects but may not exist
            if (!Schema::hasColumn('exam_questions', 'attachment')) {
                $table->string('attachment')->nullable();
            }
            if (!Schema::hasColumn('exam_questions', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            if (!Schema::hasColumn('exam_questions', 'dept_reviewed_by')) {
                $table->unsignedBigInteger('dept_reviewed_by')->nullable();
            }
            if (!Schema::hasColumn('exam_questions', 'dept_reviewed_at')) {
                $table->timestamp('dept_reviewed_at')->nullable();
            }
            if (!Schema::hasColumn('exam_questions', 'dept_comments')) {
                $table->text('dept_comments')->nullable();
            }
            if (!Schema::hasColumn('exam_questions', 'principal_reviewed_by')) {
                $table->unsignedBigInteger('principal_reviewed_by')->nullable();
            }
            if (!Schema::hasColumn('exam_questions', 'principal_reviewed_at')) {
                $table->timestamp('principal_reviewed_at')->nullable();
            }
            if (!Schema::hasColumn('exam_questions', 'principal_comments')) {
                $table->text('principal_comments')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropColumn([
                'attachment', 'submitted_at',
                'dept_reviewed_by', 'dept_reviewed_at', 'dept_comments',
                'principal_reviewed_by', 'principal_reviewed_at', 'principal_comments',
            ]);
        });
    }
};
