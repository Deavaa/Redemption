<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make email nullable so teachers can be created without an email address.
        // The unique index already exists from the original create_teachers_table migration,
        // so we only change the column — do NOT re-add unique() (causes duplicate key error).
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
