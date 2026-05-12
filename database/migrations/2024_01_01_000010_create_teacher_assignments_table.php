<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('class_id')->constrained()->cascadeOnDelete();
            $t->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['teacher_id','class_id','section_id','subject_id','academic_year_id'], 'ta_unique');
            $t->dropForeign('teacher_assignments_teacher_id_foreign');
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
    }
};