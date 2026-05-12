<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_settings', function (Blueprint $table) {
            $table->id();
            $table->string('bot_token')->nullable();
            $table->string('chat_id')->nullable();
            $table->string('webhook_url')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->text('welcome_message')->nullable();
            $table->timestamps();
        });

        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id');
            $table->string('from_id')->nullable();
            $table->string('from_name')->nullable();
            $table->text('message')->nullable();
            $table->enum('direction', ['incoming', 'outgoing'])->default('outgoing');
            $table->enum('status', ['sent', 'delivered', 'failed'])->default('sent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
        Schema::dropIfExists('telegram_settings');
    }
};
