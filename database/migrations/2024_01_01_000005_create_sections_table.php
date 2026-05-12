<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $t) {
            $t->id();
            $t->foreignId('class_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->integer('capacity')->default(40);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};