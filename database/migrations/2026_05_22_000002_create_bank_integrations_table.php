<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bank_name'); // CBE, Awash, Dashen, Abyssinia, etc.
            $table->string('bank_code')->nullable(); // Short code
            $table->string('account_number');
            $table->string('account_name');
            $table->string('integration_type')->default('csv_upload'); // csv_upload, api, manual
            $table->string('api_url')->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('merchant_id')->nullable();
            $table->string('currency')->default('ETB');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_integration_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_reference')->unique();
            $table->string('bank_transaction_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('ETB');
            $table->date('transaction_date');
            $table->string('sender_name')->nullable();
            $table->string('sender_account')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, matched, unmatched, rejected
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fee_payment_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('matched_amount', 12, 2)->nullable();
            $table->text('match_notes')->nullable();
            $table->foreignId('matched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('matched_at')->nullable();
            $table->string('source_file')->nullable(); // CSV filename
            $table->timestamps();

            $table->index(['bank_integration_id', 'transaction_date']);
            $table->index(['status', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_integrations');
    }
};
