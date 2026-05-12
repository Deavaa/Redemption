<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_assets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('class_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->integer('quantity')->default(1);
            $t->enum('condition', ['new','good','fair','poor','damaged'])->default('good');
            $t->date('purchase_date')->nullable();
            $t->decimal('purchase_price', 12, 2)->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_assets');
    }
};