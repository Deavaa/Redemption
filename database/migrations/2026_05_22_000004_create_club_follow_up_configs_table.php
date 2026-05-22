<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_follow_up_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->nullable()->constrained()->nullOnDelete(); // null = applies to all clubs
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete(); // null = applies to all branches
            $table->string('name'); // e.g. "Weekly Check-in", "Post-Event Review"
            $table->string('follow_up_type')->default('regular'); // regular, post_event, monthly, quarterly, annual
            $table->text('description')->nullable();
            $table->integer('days_after_activity')->default(7); // How many days after activity to follow up
            $table->json('checklist_items')->nullable(); // Structured checklist for follow-ups
            $table->json('rating_criteria')->nullable(); // Rating criteria for evaluation
            $table->boolean('is_auto_reminder')->default(true); // Auto-send reminders
            $table->integer('reminder_days_before')->default(1); // Days before deadline to send reminder
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['club_id', 'is_active']);
            $table->index(['branch_id', 'follow_up_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_follow_up_configs');
    }
};
