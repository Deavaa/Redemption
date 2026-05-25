<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('students')) {
            return;
        }
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'previous_branch_id')) {
                $table->foreignId('previous_branch_id')->nullable()->after('previous_section_id')->constrained('branches')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('students') && Schema::hasColumn('students', 'previous_branch_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropForeign(['previous_branch_id']);
                $table->dropColumn('previous_branch_id');
            });
        }
    }
};
