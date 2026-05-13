<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up() {
        // Make email nullable on teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->change();
        });
    }

    public function down() {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('email')->unique()->nullable(false)->change();
        });
    }
};
