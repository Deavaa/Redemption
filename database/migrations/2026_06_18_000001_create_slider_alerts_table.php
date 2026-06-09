<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slider_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('message');
            $table->string('icon')->default('fa-bullhorn'); // FontAwesome icon class
            $table->string('type')->default('info'); // info, success, warning, danger
            $table->string('bg_color')->default('#059669'); // Custom background color
            $table->string('text_color')->default('#ffffff'); // Custom text color
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slider_alerts');
    }
};
