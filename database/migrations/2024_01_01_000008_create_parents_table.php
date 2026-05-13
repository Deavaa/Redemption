<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('father_name');
            $t->string('mother_name');
            $t->string('father_occupation')->nullable();
            $t->string('mother_occupation')->nullable();
            $t->string('father_phone')->nullable();
            $t->string('mother_phone')->nullable();
            $t->string('guardian_name')->nullable();
            $t->string('guardian_relation')->nullable();
            $t->string('guardian_phone')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};