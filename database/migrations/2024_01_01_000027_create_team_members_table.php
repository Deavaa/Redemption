<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('designation');
            $t->string('department')->nullable();
            $t->string('qualification')->nullable();
            $t->string('experience')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('photo')->nullable();
            $t->text('bio')->nullable();
            $t->integer('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};