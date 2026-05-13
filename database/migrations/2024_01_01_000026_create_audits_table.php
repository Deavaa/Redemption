<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $t->string('auditor_name');
            $t->date('audit_date');
            $t->text('findings')->nullable();
            $t->text('recommendations')->nullable();
            $t->enum('status', ['open','in_progress','closed','resolved'])->default('open');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};