<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('id_cards', function (Blueprint $t) {
            $t->foreignId('academic_year_id')->nullable()->after('student_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('id_cards', function (Blueprint $t) {
            $t->dropForeign(['academic_year_id']);
            $t->dropColumn('academic_year_id');
        });
    }
};
