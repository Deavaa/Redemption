<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('club_follow_up_configs')) {
            return; // Table already exists — skip to avoid duplicate migration error
        }
        Schema::create('club_follow_up_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('follow_up_type')->default('regular');
            $table->text('description')->nullable();
            $table->integer('days_after_activity')->default(7);
            $table->json('checklist_items')->nullable();
            $table->json('rating_criteria')->nullable();
            $table->boolean('is_auto_reminder')->default(true);
            $table->integer('reminder_days_before')->default(1);
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
