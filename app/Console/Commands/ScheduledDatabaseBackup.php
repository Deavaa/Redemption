<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScheduledDatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:database {--force : Force backup even if disabled in settings}';

    /**
     * The console command description.
     */
    protected $description = 'Create a scheduled database backup and optionally send via email';

    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $enabled = Setting::get('backup_enabled', '1');

        if ($enabled !== '1' && !$this->option('force')) {
            $this->info('Scheduled database backup is disabled in settings. Use --force to override.');
            return self::SUCCESS;
        }

        $this->info('Starting scheduled database backup...');

        try {
            $compress = (bool) Setting::get('backup_compress', '1');
            $result = $this->backupService->createBackup($compress);

            $this->info("Backup created: {$result['filename']} ({$result['size_human']})");

            // Send via email if configured
            $email = Setting::get('backup_email', 'dawitac@gmail.com');

            if (!empty($email)) {
                $this->info("Sending backup to {$email}...");
                $sent = $this->backupService->sendViaEmail($result['path'], $email);

                if ($sent) {
                    $this->info('Backup email sent successfully.');
                } else {
                    $mailMailer = config('mail.default', env('MAIL_MAILER', 'log'));
                    $this->warn("Email could not be sent (mail driver: {$mailMailer}). Backup file is saved at: {$result['path']}");
                }
            }

            // Log the backup
            $logFile = storage_path('logs/backup.log');
            file_put_contents($logFile, now()->toISOString() . " | Scheduled backup | {$result['filename']} | {$result['size_human']}" . PHP_EOL, FILE_APPEND);

            $this->info('Scheduled database backup completed successfully.');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Database backup failed: ' . $e->getMessage());
            Log::error('Scheduled database backup failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            // Log the failure
            $logFile = storage_path('logs/backup.log');
            file_put_contents($logFile, now()->toISOString() . " | FAILED | " . $e->getMessage() . PHP_EOL, FILE_APPEND);

            return self::FAILURE;
        }
    }
}
