<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('phone')->nullable();
            $t->string('subject');
            $t->text('message');
            $t->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $t->boolean('is_read')->default(false);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};