<?php

namespace App\Http\Controllers\Backup;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DatabaseBackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Show the backup management page with list of past backups and schedule settings.
     */
    public function index()
    {
        $backups = $this->backupService->listBackups();

        // Schedule settings
        $scheduleSettings = [
            'backup_enabled' => Setting::get('backup_enabled', '1'),
            'backup_frequency' => Setting::get('backup_frequency', 'daily'),
            'backup_time' => Setting::get('backup_time', '02:00'),
            'backup_email' => Setting::get('backup_email', config('mail.from.address', 'admin@schoolofredemption.com')),
            'backup_compress' => Setting::get('backup_compress', '1'),
            'backup_keep_count' => Setting::get('backup_keep_count', '10'),
        ];

        // Database info
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver", 'unknown');
        $database = config("database.connections.{$connection}.database", 'N/A');

        // Mail status
        $mailMailer = config('mail.default', env('MAIL_MAILER', 'log'));
        $mailConfigured = !in_array($mailMailer, ['log', 'array', 'null']);

        return view('admin.backup.index', compact(
            'backups', 'scheduleSettings', 'driver', 'database', 'mailMailer', 'mailConfigured'
        ));
    }

    /**
     * Create an immediate backup, optionally send via email.
     */
    public function backupNow(Request $request)
    {
        $request->validate([
            'send_email' => 'nullable|boolean',
            'email' => 'nullable|email',
        ]);

        try {
            $compress = (bool) Setting::get('backup_compress', '1');
            $result = $this->backupService->createBackup($compress);

            $sendEmail = $request->boolean('send_email', true);
            $emailSent = false;

            if ($sendEmail) {
                $email = $request->input('email', Setting::get('backup_email', config('mail.from.address', 'admin@schoolofredemption.com')));
                $emailSent = $this->backupService->sendViaEmail($result['path'], $email);

                if (!$emailSent) {
                    $mailMailer = config('mail.default', env('MAIL_MAILER', 'log'));
                    if (in_array($mailMailer, ['log', 'array', 'null'])) {
                        return redirect()->back()->with('warning',
                            'Backup created successfully (' . $result['size_human'] . '), but email was not sent because mail is not configured (driver: ' . $mailMailer . '). ' .
                            'Please configure SMTP in your .env file to enable email delivery.'
                        );
                    }
                }
            }

            $message = 'Database backup created successfully! File: ' . $result['filename'] . ' (' . $result['size_human'] . ')';
            if ($sendEmail && $emailSent) {
                $message .= ' — Email sent to ' . ($request->input('email') ?? Setting::get('backup_email', config('mail.from.address', '')));
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Manual database backup failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Download a specific backup file.
     */
    public function download($filename)
    {
        $path = $this->backupService->getBackupPath($filename);

        if (!$path) {
            return redirect()->back()->with('error', 'Backup file not found.');
        }

        return response()->download($path);
    }

    /**
     * Delete a backup file.
     */
    public function delete($filename)
    {
        if ($this->backupService->deleteBackup($filename)) {
            return redirect()->back()->with('success', 'Backup file deleted successfully.');
        }

        return redirect()->back()->with('error', 'Backup file not found or could not be deleted.');
    }

    /**
     * Quick export: one-click backup and send to default email.
     */
    public function quickExport()
    {
        try {
            $compress = (bool) Setting::get('backup_compress', '1');
            $result = $this->backupService->createBackup($compress);

            $email = Setting::get('backup_email', config('mail.from.address', 'admin@schoolofredemption.com'));
            $emailSent = $this->backupService->sendViaEmail($result['path'], $email);

            if (!$emailSent) {
                $mailMailer = config('mail.default', env('MAIL_MAILER', 'log'));
                if (in_array($mailMailer, ['log', 'array', 'null'])) {
                    return redirect()->back()->with('warning',
                        'Backup created (' . $result['size_human'] . '), but email was not sent (driver: ' . $mailMailer . ').'
                    );
                }
            }

            $message = 'Quick backup sent to ' . $email . ' (' . $result['size_human'] . ')';
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Quick database export failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Quick export failed: ' . $e->getMessage());
        }
    }

    /**
     * Export database and send via email (with table selection).
     */
    public function exportAndSend(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
        ]);

        // Reuse backupNow with forced email
        $request->merge(['send_email' => true]);
        return $this->backupNow($request);
    }

    /**
     * Send a test email to verify mail configuration.
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $result = $this->backupService->sendTestEmail($request->input('email'));

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Update the backup schedule settings.
     */
    public function updateSchedule(Request $request)
    {
        $request->validate([
            'backup_enabled' => 'required|in:0,1',
            'backup_frequency' => 'required|in:daily,weekly,monthly',
            'backup_time' => 'required|date_format:H:i',
            'backup_email' => 'required|email',
            'backup_compress' => 'required|in:0,1',
            'backup_keep_count' => 'required|integer|min:1|max:100',
        ]);

        try {
            Setting::set('backup_enabled', $request->input('backup_enabled'));
            Setting::set('backup_frequency', $request->input('backup_frequency'));
            Setting::set('backup_time', $request->input('backup_time'));
            Setting::set('backup_email', $request->input('backup_email'));
            Setting::set('backup_compress', $request->input('backup_compress'));
            Setting::set('backup_keep_count', $request->input('backup_keep_count'));

            Log::info('Backup schedule settings updated', $request->only([
                'backup_enabled', 'backup_frequency', 'backup_time', 'backup_email', 'backup_compress', 'backup_keep_count'
            ]));

            return redirect()->back()->with('success', 'Backup schedule settings updated successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to update backup settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
