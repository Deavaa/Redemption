<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('report_documents')) {
            Schema::create('report_documents', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('document_type')->default('report'); // report, memo, proposal, financial, academic, inspection
                $table->string('priority')->default('normal'); // low, normal, high, urgent
                $table->string('status')->default('draft'); // draft, submitted, reviewed, approved, rejected, archived
                $table->string('file_path')->nullable();
                $table->string('file_name')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->foreignId('from_branch_id')->nullable()->constrained('branches')->onDelete('set null');
                $table->foreignId('to_branch_id')->nullable()->constrained('branches')->onDelete('set null'); // null = headquarters
                $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
                $table->foreignId('term_id')->nullable()->constrained('terms')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('report_document_comments')) {
            Schema::create('report_document_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_document_id')->constrained('report_documents')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('comment');
                $table->string('action')->default('comment'); // comment, approve, reject, request_revision
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('report_document_recipients')) {
            Schema::create('report_document_recipients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_document_id')->constrained('report_documents')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->unique(['report_document_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('report_document_recipients');
        Schema::dropIfExists('report_document_comments');
        Schema::dropIfExists('report_documents');
    }
};
