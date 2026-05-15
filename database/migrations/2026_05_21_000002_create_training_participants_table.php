<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('training_participants')) {
            Schema::create('training_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
                $table->enum('status', ['invited', 'enrolled', 'attended', 'completed', 'absent', 'dropped'])->default('invited');
                $table->date('completion_date')->nullable();
                $table->decimal('score', 5, 2)->nullable();
                $table->string('grade')->nullable();
                $table->string('certificate_number')->nullable();
                $table->boolean('certificate_issued')->default(false);
                $table->text('feedback')->nullable();
                $table->text('remarks')->nullable();
                $table->foreignId('nominated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['training_id', 'employee_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('training_participants');
    }
};
