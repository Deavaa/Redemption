<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Change the `news.content` column from TEXT (65KB max) to LONGTEXT (4GB max).
 *
 * Reason: Summernote's base64 image fallback embeds images as data: URIs
 * directly in the HTML content. A single phone photo can be 100-500KB as
 * base64, which overflows TEXT and causes the save to fail silently or
 * truncate the content. LONGTEXT (4GB) is more than enough.
 */
return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('news', function (Blueprint $table) {
                // Change content from TEXT to LONGTEXT
                $table->longText('content')->nullable()->change();
            });
        } catch (\Throwable $e) {
            // Skip if the column is already LONGTEXT or the driver doesn't support change()
            \Log::info('news.content column change skipped: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        try {
            Schema::table('news', function (Blueprint $table) {
                $table->text('content')->nullable()->change();
            });
        } catch (\Throwable $e) {
            // Silently fail — downgrade is not critical
        }
    }
};
