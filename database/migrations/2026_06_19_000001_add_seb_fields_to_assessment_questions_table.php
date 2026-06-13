<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_questions', function (Blueprint $table) {
            // SEB (Safe Exam Browser) integration fields
            // seb_mode: 'none' = normal, 'optional' = can use SEB but not required,
            //           'required' = must use SEB to access this question
            $table->string('seb_mode')->default('none')->after('is_active');
            // seb_config_key: The SEB configuration key (SHA-256 hash of the .seb config file).
            // Used to verify that the student is using the correct SEB configuration.
            $table->string('seb_config_key')->nullable()->after('seb_mode');
            // seb_exam_keys: JSON array of allowed SEB exam keys for verification.
            // Each SEB installation generates a unique key we can validate.
            $table->json('seb_exam_keys')->nullable()->after('seb_config_key');
            // seb_allow_quit: Whether SEB can be quit during the exam
            $table->boolean('seb_allow_quit')->default(false)->after('seb_exam_keys');
            // seb_quit_password: Password required to quit SEB (hashed)
            $table->string('seb_quit_password')->nullable()->after('seb_allow_quit');
            // seb_show_taskbar: Show SEB taskbar during exam
            $table->boolean('seb_show_taskbar')->default(true)->after('seb_quit_password');
            // seb_show_time: Show clock in SEB taskbar
            $table->boolean('seb_show_time')->default(true)->after('seb_show_taskbar');
            // seb_allow_spell_check: Allow spell checking in SEB
            $table->boolean('seb_allow_spell_check')->default(false)->after('seb_show_time');
            // seb_browser_view_mode: 0=window, 1=fullscreen, 2=fullscreen+touch
            $table->tinyInteger('seb_browser_view_mode')->default(1)->after('seb_allow_spell_check');
            // seb_allowed_urls: JSON array of URLs allowed during exam (in addition to the assessment)
            $table->json('seb_allowed_urls')->nullable()->after('seb_browser_view_mode');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->dropColumn([
                'seb_mode',
                'seb_config_key',
                'seb_exam_keys',
                'seb_allow_quit',
                'seb_quit_password',
                'seb_show_taskbar',
                'seb_show_time',
                'seb_allow_spell_check',
                'seb_browser_view_mode',
                'seb_allowed_urls',
            ]);
        });
    }
};
