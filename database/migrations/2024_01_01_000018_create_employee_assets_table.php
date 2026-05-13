<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_assets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $t->string('name');
            $t->integer('quantity')->default(1);
            $t->enum('condition', ['new','good','fair','poor','damaged'])->default('good');
            $t->date('issue_date');
            $t->date('return_date')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_assets');
    }
};