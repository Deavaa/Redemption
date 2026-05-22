<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make user_id and mother_name nullable on parents table.
 *
 * - user_id: Made nullable so parents can be created without a user account
 *   (the controller auto-creates a user, but this provides a safety net).
 * - mother_name: Was NOT NULL but the form doesn't always require it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parents', function (Blueprint $t) {
            $t->foreignId('user_id')->nullable()->change();
            $t->string('mother_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('parents', function (Blueprint $t) {
            $t->foreignId('user_id')->nullable(false)->change();
            $t->string('mother_name')->nullable(false)->change();
        });
    }
};
