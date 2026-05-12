<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $t->decimal('basic_salary', 12, 2);
            $t->decimal('allowances', 12, 2)->default(0);
            $t->decimal('deductions', 12, 2)->default(0);
            $t->decimal('tax', 12, 2)->default(0);
            $t->decimal('net_salary', 12, 2);
            $t->string('pay_period');
            $t->date('payment_date')->nullable();
            $t->enum('status', ['paid','pending','processed'])->default('pending');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};