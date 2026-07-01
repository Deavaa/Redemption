<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add special_needs column to students table.
 * Special needs students are excluded from ranking (they get their own
 * mark but don't affect other students' ranks).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'special_needs')) {
                $table->boolean('special_needs')->default(false)->after('status')
                    ->comment('If true, student is excluded from ranking');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('special_needs');
        });
    }
};
