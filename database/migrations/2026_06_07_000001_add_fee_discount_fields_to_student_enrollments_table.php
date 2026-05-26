<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->decimal('fee_discount', 12, 2)->default(0)->after('registration_fee');
            $table->enum('discount_type', ['percentage', 'fixed'])->default('fixed')->after('fee_discount');
            $table->string('discount_reason')->nullable()->after('discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropColumn(['fee_discount', 'discount_type', 'discount_reason']);
        });
    }
};
