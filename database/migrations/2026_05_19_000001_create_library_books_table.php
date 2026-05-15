<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('library_books')) {
            return;
        }
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('isbn')->nullable();
            $table->string('publisher')->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->string('file_path'); // Path to the softcopy file (PDF)
            $table->string('file_name')->nullable(); // Original file name
            $table->string('file_type')->nullable(); // MIME type
            $table->unsignedBigInteger('file_size')->nullable(); // File size in bytes
            $table->string('cover_image')->nullable(); // Book cover image path
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete(); // Who uploaded
            $table->enum('access_level', ['all', 'teacher', 'student', 'staff', 'admin'])->default('all'); // Who can read
            $table->boolean('is_active')->default(true);
            $table->integer('read_count')->default(0); // Track how many times read
            $table->timestamps();
            $table->softDeletes();

            // Indexes for search
            $table->index('title');
            $table->index('author');
            $table->index('category');
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_books');
    }
};
