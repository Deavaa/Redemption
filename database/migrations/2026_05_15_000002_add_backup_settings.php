<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['key' => 'backup_enabled', 'value' => '1', 'group' => 'backup', 'type' => 'boolean', 'description' => 'Enable or disable automatic scheduled backups'],
            ['key' => 'backup_frequency', 'value' => 'daily', 'group' => 'backup', 'type' => 'text', 'description' => 'Backup frequency: daily, weekly, or monthly'],
            ['key' => 'backup_time', 'value' => '02:00', 'group' => 'backup', 'type' => 'text', 'description' => 'Time of day to run the backup (HH:mm format, Africa/Addis_Ababa timezone)'],
            ['key' => 'backup_email', 'value' => 'dawitac@gmail.com', 'group' => 'backup', 'type' => 'text', 'description' => 'Email address to send backup files to'],
            ['key' => 'backup_compress', 'value' => '1', 'group' => 'backup', 'type' => 'boolean', 'description' => 'Compress backup files with gzip'],
            ['key' => 'backup_keep_count', 'value' => '10', 'group' => 'backup', 'type' => 'number', 'description' => 'Number of recent backups to keep (older ones are auto-deleted)'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'backup_enabled',
            'backup_frequency',
            'backup_time',
            'backup_email',
            'backup_compress',
            'backup_keep_count',
        ])->delete();
    }
};
