<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_telegram_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('bot_token')->nullable();
            $table->string('chat_id')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->string('welcome_message')->nullable();
            $table->timestamps();

            $table->unique('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_telegram_settings');
    }
};
