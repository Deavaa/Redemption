<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // Academic, Sports, Arts, Community Service, Technology, etc.
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('leader_id')->nullable()->constrained('teachers')->nullOnDelete(); // Club leader/coordinator
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('meeting_schedule')->nullable(); // e.g., "Every Tuesday 3:00 PM"
            $table->string('meeting_location')->nullable();
            $table->integer('max_members')->default(50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('branch_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
