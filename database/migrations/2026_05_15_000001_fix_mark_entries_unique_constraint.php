<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mark_entries', function (Blueprint $table) {
            // Drop the old unique constraint that uses exam_id + student_id
            // This doesn't work for the new auto-save flow which uses subject_id + term_id instead
            $table->dropUnique(['exam_id', 'student_id']);
        });

        Schema::table('mark_entries', function (Blueprint $table) {
            // Add the new unique constraint matching the actual upsert key
            // student_id + subject_id + academic_year_id + term_id
            $table->unique(['student_id', 'subject_id', 'academic_year_id', 'term_id'], 'mark_entries_student_subject_ay_term_unique');

            // Make exam_id nullable (it's not used in the new auto-save flow)
            $table->unsignedBigInteger('exam_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mark_entries', function (Blueprint $table) {
            $table->dropUnique('mark_entries_student_subject_ay_term_unique');
            $table->unique(['exam_id', 'student_id']);
        });
    }
};
