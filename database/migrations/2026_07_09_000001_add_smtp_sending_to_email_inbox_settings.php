<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add SMTP sending fields + is_default_sender flag to email_inbox_settings.
 *
 * This lets the system use the cPanel email account (already configured for
 * IMAP reading) to also SEND emails — including scheduled database backup
 * emails. cPanel email typically uses the same username/password for both
 * IMAP and SMTP, just different ports (IMAP 993 SSL, SMTP 465 SSL or 587 TLS).
 */
return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('email_inbox_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('email_inbox_settings', 'smtp_host')) {
                    // SMTP host defaults to the same as IMAP host (cPanel convention)
                    $table->string('smtp_host')->nullable()->after('imap_encryption');
                }
                if (!Schema::hasColumn('email_inbox_settings', 'smtp_port')) {
                    $table->integer('smtp_port')->default(465)->after('smtp_host');
                }
                if (!Schema::hasColumn('email_inbox_settings', 'smtp_encryption')) {
                    $table->string('smtp_encryption')->default('ssl')->after('smtp_port');
                }
                if (!Schema::hasColumn('email_inbox_settings', 'is_default_sender')) {
                    // When true, this inbox's SMTP config is used to send system
                    // emails (backup notifications, etc.) instead of the global
                    // config/mail.php settings.
                    $table->boolean('is_default_sender')->default(false)->after('is_active');
                }
            });
        } catch (\Throwable $e) {
            \Log::info('email_inbox_settings SMTP columns already exist or skipped: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        try {
            Schema::table('email_inbox_settings', function (Blueprint $table) {
                $table->dropColumn(['smtp_host', 'smtp_port', 'smtp_encryption', 'is_default_sender']);
            });
        } catch (\Throwable $e) {
            // Silently fail
        }
    }
};
