<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promotion_settings')) {
            return;
        }
        Schema::create('promotion_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->default('Default Promotion Policy');
            $table->decimal('minimum_average_for_promotion', 5, 2)->default(50.00);
            $table->integer('maximum_subjects_to_fail')->default(0);
            $table->decimal('minimum_subject_pass_mark', 5, 2)->default(50.00);
            $table->boolean('consider_attendance')->default(false);
            $table->decimal('minimum_attendance_percentage', 5, 2)->default(75.00);
            $table->boolean('consider_behavior')->default(false);
            $table->boolean('consider_conduct')->default(false);
            $table->decimal('minimum_conduct_score', 5, 2)->default(3.00);
            $table->boolean('auto_promote_if_pass_all')->default(true);
            $table->boolean('allow_conditional_promotion')->default(true);
            $table->decimal('conditional_promotion_min_average', 5, 2)->default(40.00);
            $table->integer('conditional_promotion_max_failures')->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_settings');
    }
};
