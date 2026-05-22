<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_inbox_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email_address');
            $table->string('imap_host')->default('imap.gmail.com');
            $table->integer('imap_port')->default(993);
            $table->string('imap_username');
            $table->string('imap_password'); // encrypted
            $table->string('imap_protocol')->default('imap');
            $table->string('imap_encryption')->default('ssl');
            $table->string('folder')->default('INBOX');
            $table->boolean('is_active')->default(true);
            $table->integer('sync_interval_minutes')->default(15);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_inbox_setting_id')->constrained()->cascadeOnDelete();
            $table->string('message_id')->unique();
            $table->string('subject')->nullable();
            $table->text('body_html')->nullable();
            $table->text('body_text')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('to_email')->nullable();
            $table->json('cc')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->string('category')->nullable(); // admission, fee, general, complaint, etc.
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['email_inbox_setting_id', 'received_at']);
            $table->index(['is_read', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_messages');
        Schema::dropIfExists('email_inbox_settings');
    }
};
