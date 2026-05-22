<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_library', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('youtube_url');
            $table->string('youtube_video_id')->nullable();
            $table->string('channel_name')->nullable();
            $table->string('channel_url')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('category')->nullable();
            $table->enum('video_type', ['single', 'channel'])->default('single');
            $table->enum('access_level', ['all', 'teacher', 'student', 'staff', 'admin'])->default('all');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('view_count')->default(0);
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('title');
            $table->index('category');
            $table->index('branch_id');
            $table->index('youtube_video_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_library');
    }
};
