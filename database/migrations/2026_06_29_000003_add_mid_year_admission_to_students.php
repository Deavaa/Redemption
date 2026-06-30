<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add mid-year admission support for students.
 *
 * Students who join in Term 2 need:
 * - joined_term: which term they joined (1 = start of year, 2 = mid-year)
 * - first_term_mark_override: manually entered total mark for term 1
 * - first_term_rank_override: manually entered rank for term 1
 *
 * These students:
 * - Do NOT participate in Term 1 ranking (their rank is manual)
 * - Their annual total = Term 2 total only (no Term 1 data to average)
 * - They DO participate in Term 2 and Annual ranking (based on Term 2 only)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'joined_term')) {
                $table->tinyInteger('joined_term')->default(1)->after('admission_date')
                    ->comment('1 = joined at start of year, 2 = joined in term 2 (mid-year)');
            }
            if (!Schema::hasColumn('students', 'first_term_mark_override')) {
                $table->decimal('first_term_mark_override', 8, 2)->nullable()->after('joined_term')
                    ->comment('Manually entered total mark for term 1 (mid-year entrants)');
            }
            if (!Schema::hasColumn('students', 'first_term_rank_override')) {
                $table->integer('first_term_rank_override')->nullable()->after('first_term_mark_override')
                    ->comment('Manually entered rank for term 1 (mid-year entrants)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['joined_term', 'first_term_mark_override', 'first_term_rank_override']);
        });
    }
};
