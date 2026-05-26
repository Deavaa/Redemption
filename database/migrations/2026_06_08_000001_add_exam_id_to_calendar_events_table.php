<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->foreignId('exam_id')->nullable()->after('branch_id')->constrained('exams')->nullOnDelete();
            $table->string('source_type')->nullable()->after('exam_id'); // 'exam' or null for manual events
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropForeign(['exam_id']);
            $table->dropColumn(['exam_id', 'source_type']);
        });
    }
};
