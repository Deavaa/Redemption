<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $t) {
            $t->id();
            $t->foreignId('class_id')->constrained()->cascadeOnDelete();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->string('fee_type');
            $t->decimal('amount', 12, 2);
            $t->date('due_date')->nullable();
            $t->text('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};