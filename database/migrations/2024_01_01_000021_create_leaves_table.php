<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaves', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $t->enum('leave_type', ['sick','annual','maternity','paternity','unpaid','casual','emergency'])->default('annual');
            $t->date('start_date');
            $t->date('end_date');
            $t->integer('total_days');
            $t->text('reason')->nullable();
            $t->enum('status', ['pending','approved','rejected','cancelled'])->default('pending');
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};