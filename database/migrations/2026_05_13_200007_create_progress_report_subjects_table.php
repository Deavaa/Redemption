<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('progress_report_subjects')) {
            Schema::create('progress_report_subjects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('progress_report_id')->constrained()->cascadeOnDelete();
                $table->string('subject_name');
                $table->decimal('marks_obtained', 8, 2)->nullable();
                $table->decimal('max_marks', 8, 2)->nullable();
                $table->string('grade')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_report_subjects');
    }
};
