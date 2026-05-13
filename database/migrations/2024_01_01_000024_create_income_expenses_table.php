<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_expenses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['income','expense']);
            $t->string('category');
            $t->decimal('amount', 14, 2);
            $t->date('date');
            $t->text('description')->nullable();
            $t->string('reference')->nullable();
            $t->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_expenses');
    }
};