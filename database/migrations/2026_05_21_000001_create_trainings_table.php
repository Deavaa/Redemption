<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('trainings')) {
            Schema::create('trainings', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('type', ['workshop', 'seminar', 'online_course', 'on_the_job', 'certification', 'conference', 'mentorship', 'induction'])->default('workshop');
                $table->enum('category', ['pedagogical', 'administrative', 'technical', 'leadership', 'safety', 'curriculum', 'pastoral', 'general'])->default('general');
                $table->string('provider')->nullable();
                $table->string('facilitator')->nullable();
                $table->string('venue')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->integer('duration_hours')->default(0);
                $table->enum('target_audience', ['all', 'teachers', 'admins', 'staff', 'specific'])->default('all');
                $table->decimal('cost', 12, 2)->default(0);
                $table->string('budget_source')->nullable();
                $table->integer('max_participants')->default(0);
                $table->enum('status', ['planned', 'ongoing', 'completed', 'cancelled'])->default('planned');
                $table->text('objectives')->nullable();
                $table->text('outcomes')->nullable();
                $table->string('certificate_template')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
