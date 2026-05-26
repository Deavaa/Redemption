<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // who wrote the comment
            $table->enum('comment_type', ['general', 'academic', 'behavior', 'attendance', 'progress'])->default('general');
            $table->enum('visibility', ['private', 'staff', 'public'])->default('staff'); // who can see
            $table->text('comment');
            $table->boolean('is_report_comment')->default(false); // shows on report card
            $table->timestamps();

            $table->index(['student_id', 'academic_year_id']);
            $table->index(['student_id', 'is_report_comment']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_comments');
    }
};
