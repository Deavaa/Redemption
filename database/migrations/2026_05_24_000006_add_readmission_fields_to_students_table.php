<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'original_admission_date')) {
                $table->date('original_admission_date')->nullable()->after('admission_date');
            }
            if (!Schema::hasColumn('students', 'readmission_count')) {
                $table->integer('readmission_count')->default(0)->after('original_admission_date');
            }
            if (!Schema::hasColumn('students', 'previous_class_id')) {
                $table->foreignId('previous_class_id')->nullable()->constrained('classes')->nullOnDelete()->after('readmission_count');
            }
            if (!Schema::hasColumn('students', 'previous_section_id')) {
                $table->foreignId('previous_section_id')->nullable()->constrained('sections')->nullOnDelete()->after('previous_class_id');
            }
            if (!Schema::hasColumn('students', 'leave_date')) {
                $table->date('leave_date')->nullable()->after('previous_section_id');
            }
            if (!Schema::hasColumn('students', 'leave_reason')) {
                $table->text('leave_reason')->nullable()->after('leave_date');
            }
            if (!Schema::hasColumn('students', 'is_readmitted')) {
                $table->boolean('is_readmitted')->default(false)->after('leave_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $columns = ['original_admission_date', 'readmission_count', 'previous_class_id', 'previous_section_id', 'leave_date', 'leave_reason', 'is_readmitted'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('students', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
