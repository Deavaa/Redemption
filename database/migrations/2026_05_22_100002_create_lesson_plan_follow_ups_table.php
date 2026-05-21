<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lesson_plan_follow_ups')) {
            Schema::create('lesson_plan_follow_ups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lesson_plan_id')->constrained()->cascadeOnDelete();
                $table->foreignId('followed_up_by')->constrained('users')->cascadeOnDelete();
                $table->date('follow_up_date');
                $table->enum('completion_status', ['not_started', 'in_progress', 'completed', 'skipped'])->default('not_started');
                $table->text('objectives_achieved')->nullable();
                $table->text('challenges')->nullable();
                $table->text('adjustments')->nullable();
                $table->text('student_engagement')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['lesson_plan_id', 'follow_up_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_plan_follow_ups');
    }
};
