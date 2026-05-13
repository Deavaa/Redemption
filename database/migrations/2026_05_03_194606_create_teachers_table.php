<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create("teachers", function(Blueprint $table) {
            $table->id();
            $table->string("first_name");
            $table->string("last_name");
            $table->string("email")->unique();
            $table->string("phone")->nullable();
            $table->string("qualification")->nullable();
            $table->string("department")->nullable();
            $table->date("hire_date")->nullable();
            $table->decimal("salary",12,2)->default(0);
            $table->enum("status",["active","inactive"])->default("active");
            $table->string("address")->nullable();
            $table->string("photo")->nullable();
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists("teachers");
    }
};