<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->boolean('is_exam')->default(false)->after('is_active');
            $table->integer('exam_duration_minutes')->nullable()->after('is_exam');
            $table->integer('max_attempts')->default(0)->after('exam_duration_minutes')->comment('0 = unlimited');
            $table->timestamp('exam_opens_at')->nullable()->after('max_attempts');
            $table->timestamp('exam_closes_at')->nullable()->after('exam_opens_at');
            $table->boolean('show_results_immediately')->default(true)->after('exam_closes_at')->comment('false = teacher reviews before showing');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->dropColumn(['is_exam', 'exam_duration_minutes', 'max_attempts', 'exam_opens_at', 'exam_closes_at', 'show_results_immediately']);
        });
    }
};
