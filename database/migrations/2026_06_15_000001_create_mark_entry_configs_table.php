<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mark_entry_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();          // Config key name
            $table->string('label');                     // Human-readable label
            $table->text('value');                        // JSON or scalar value
            $table->string('type')->default('text');     // text, number, json, boolean
            $table->string('category')->default('general'); // grouping
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_entry_configs');
    }
};
