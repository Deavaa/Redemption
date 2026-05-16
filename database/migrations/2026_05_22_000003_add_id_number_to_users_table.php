<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'id_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('id_number')->nullable()->unique()->after('email');
            });
        }
        
        // Add index for faster lookups
        try {
            DB::statement('CREATE INDEX idx_users_id_number ON users (id_number)');
        } catch (\Exception $e) {}
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'id_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('id_number');
            });
        }
    }
};
