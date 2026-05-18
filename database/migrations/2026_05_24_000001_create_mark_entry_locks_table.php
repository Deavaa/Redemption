<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mark_entry_locks')) {
            return;
        }
        Schema::create('mark_entry_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_locked')->default(false);
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('unlocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('unlocked_at')->nullable();
            $table->text('lock_reason')->nullable();
            $table->text('unlock_reason')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'academic_year_id', 'term_id'], 'mark_lock_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_entry_locks');
    }
};
