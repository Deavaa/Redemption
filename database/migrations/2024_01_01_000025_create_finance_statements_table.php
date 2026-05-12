<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_statements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('statement_type', ['income_statement','balance_sheet','cash_flow','trial_balance'])->default('income_statement');
            $t->date('period_from');
            $t->date('period_to');
            $t->decimal('total_income', 14, 2)->default(0);
            $t->decimal('total_expense', 14, 2)->default(0);
            $t->decimal('net_balance', 14, 2)->default(0);
            $t->text('description')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_statements');
    }
};