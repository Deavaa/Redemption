<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->string('roll_number')->nullable();
            $table->date('enrollment_date')->nullable();
            $table->enum('status', ['enrolled', 'pending', 'withdrawn', 'graduated', 'transferred'])->default('enrolled');
            $table->enum('enrollment_type', ['new', 'returning', 'transferred_in'])->default('new');

            // Registration Fee Tracking
            $table->decimal('registration_fee', 12, 2)->default(0);
            $table->decimal('registration_fee_paid', 12, 2)->default(0);
            $table->date('registration_fee_date')->nullable();
            $table->enum('registration_fee_status', ['unpaid', 'partial', 'paid', 'waived'])->default('unpaid');
            $table->string('registration_fee_payment_method')->nullable();
            $table->string('registration_fee_receipt_number')->nullable();
            $table->text('registration_fee_notes')->nullable();

            // Withdrawal / Transfer info
            $table->date('withdrawal_date')->nullable();
            $table->string('withdrawal_reason')->nullable();
            $table->foreignId('transferred_to_branch_id')->nullable()->constrained('branches')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // A student can only have one enrollment per academic year
            $table->unique(['student_id', 'academic_year_id'], 'student_ay_unique');
            // Indexes for fast lookups
            $table->index(['academic_year_id', 'status']);
            $table->index(['class_id', 'section_id', 'academic_year_id']);
            $table->index(['branch_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
