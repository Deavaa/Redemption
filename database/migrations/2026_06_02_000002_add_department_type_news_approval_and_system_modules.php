<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add department_type to departments
        Schema::table('departments', function (Blueprint $table) {
            $table->string('type')->default('academic')->after('name'); // academic, administrative, support
            $table->string('code')->nullable()->after('name'); // short code like SCI, MATH, HUM
        });

        // Add is_approved to news for admin activation/deactivation
        Schema::table('news', function (Blueprint $table) {
            $table->boolean('is_approved')->default(true)->after('is_active');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('is_approved');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        // Create system_modules table for admin function toggles
        Schema::create('system_modules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. 'news', 'lesson_plans', 'exam_questions'
            $table->string('name'); // Human readable
            $table->string('description')->nullable();
            $table->string('group')->default('general'); // academic, finance, communication, etc.
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['type', 'code']);
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['is_approved', 'approved_by', 'approved_at']);
        });

        Schema::dropIfExists('system_modules');
    }
};
