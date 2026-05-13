<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mark_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->decimal('marks_obtained', 8, 2);
            $t->string('grade')->nullable();
            $t->text('remarks')->nullable();
            $t->timestamps();
            $t->unique(['exam_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_entries');
    }
};