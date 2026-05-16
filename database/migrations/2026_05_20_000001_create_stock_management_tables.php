<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stock Items - Central inventory of fixed & stationary materials
        if (!Schema::hasTable('stock_items')) {
            Schema::create('stock_items', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable()->unique();
                $table->enum('category', ['fixed_asset', 'stationary', 'furniture', 'electronics', 'cleaning', 'other'])->default('stationary');
                $table->text('description')->nullable();
                $table->string('unit')->default('pcs'); // pcs, box, ream, set, etc.
                $table->integer('quantity')->default(0);
                $table->integer('minimum_stock')->default(0); // Reorder level
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('total_value', 12, 2)->default(0);
                $table->string('location')->nullable(); // Storage location
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Stock Transactions - Track all stock in/out movements
        if (!Schema::hasTable('stock_transactions')) {
            Schema::create('stock_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('stock_item_id');
                $table->foreign('stock_item_id')->references('id')->on('stock_items')->onDelete('cascade');
                $table->enum('type', ['in', 'out'])->default('in');
                $table->enum('reason', ['purchase', 'return', 'issue_employee', 'issue_class', 'damaged', 'lost', 'adjustment', 'transfer'])->default('purchase');
                $table->integer('quantity');
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('total_price', 12, 2)->default(0);
                $table->date('transaction_date');
                // Polymorphic: who received/issued the item
                $table->unsignedBigInteger('recipient_id')->nullable();
                $table->string('recipient_type')->nullable(); // App\Models\User, App\Models\Classroom, etc.
                $table->string('reference_no')->nullable(); // Invoice/Receipt number
                $table->string('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
        Schema::dropIfExists('stock_items');
    }
};
