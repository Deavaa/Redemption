<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->string('category');
            $t->decimal('allocated_amount', 14, 2);
            $t->decimal('spent_amount', 14, 2)->default(0);
            $t->text('description')->nullable();
            $t->enum('status', ['draft','approved','active','closed'])->default('draft');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};