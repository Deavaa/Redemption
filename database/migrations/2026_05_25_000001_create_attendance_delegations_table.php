<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('delegated_to_teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('delegated_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date'); // The specific date this delegation is valid for
            $table->text('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // A teacher can only be delegated once per class per date
            $table->unique(['class_id', 'section_id', 'delegated_to_teacher_id', 'date'], 'delegation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_delegations');
    }
};
