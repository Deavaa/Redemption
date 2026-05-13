<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('code')->unique();
            $t->enum('type', ['theory','practical','both'])->default('theory');
            $t->text('description')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};