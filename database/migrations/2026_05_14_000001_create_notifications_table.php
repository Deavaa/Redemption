<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type'); // info, warning, success, danger
                $table->string('title');
                $table->text('message')->nullable();
                $table->string('icon')->default('fas fa-bell');
                $table->string('link')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();

                $table->index(['user_id', 'is_read']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
