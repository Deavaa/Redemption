<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('fee_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->decimal('amount_paid', 12, 2);
            $t->date('payment_date');
            $t->enum('payment_method', ['cash','bank','mobile','cheque','online'])->default('cash');
            $t->string('transaction_id')->nullable();
            $t->string('receipt_number')->unique();
            $t->enum('status', ['paid','partial','pending','overdue'])->default('pending');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};