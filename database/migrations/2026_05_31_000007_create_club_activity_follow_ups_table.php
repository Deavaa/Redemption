<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_activity_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_activity_id')->constrained('club_activities')->cascadeOnDelete();
            $table->foreignId('followed_up_by')->constrained('users')->cascadeOnDelete();
            $table->date('follow_up_date');
            $table->enum('completion_status', ['not_started', 'in_progress', 'completed', 'postponed', 'cancelled'])->default('not_started');
            $table->text('observations')->nullable();
            $table->text('achievements')->nullable();
            $table->text('issues')->nullable();
            $table->text('action_items')->nullable();
            $table->integer('actual_participants')->default(0);
            $table->text('photos')->nullable(); // JSON array of photo paths
            $table->timestamps();

            $table->index('club_activity_id');
            $table->index('follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_activity_follow_ups');
    }
};
