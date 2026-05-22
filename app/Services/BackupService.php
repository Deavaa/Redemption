<?php

namespace App\Services;

use App\Mail\DatabaseBackupMail;
use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    /**
     * The backup directory relative to storage/app/
     */
    protected string $backupDir = 'backups';

    /**
     * Create a database backup using mysqldump.
     *
     * @param bool $compress Whether to gzip the backup
     * @return array{path: string, filename: string, size: int, size_human: string, compressed: bool}
     * @throws \Exception
     */
    public function createBackup(bool $compress = true): array
    {
        $this->ensureBackupDirectory();

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sql";
        $filepath = storage_path("app/{$this->backupDir}/{$filename}");

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $this->backupWithMysqldump($filepath);
        } else {
            // Fallback to PHP-based export for SQLite etc.
            $this->backupWithPhp($filepath);
        }

        $compressed = false;

        if ($compress) {
            $compressed = $this->compressFile($filepath);
            if ($compressed) {
                // Remove the original .sql file, keep .sql.gz
                File::delete($filepath);
                $filename .= '.gz';
                $filepath .= '.gz';
            }
        }

        $size = File::size($filepath);

        // Clean up old backups
        $this->cleanupOldBackups();

        Log::info('Database backup created', [
            'filename' => $filename,
            'size' => $size,
            'compressed' => $compressed,
        ]);

        return [
            'path' => $filepath,
            'filename' => $filename,
            'size' => $size,
            'size_human' => $this->formatFileSize($size),
            'compressed' => $compressed,
        ];
    }

    /**
     * Send a backup file via email.
     *
     * @param string $filePath Full path to the backup file
     * @param string|null $email Recipient email (defaults to setting)
     * @return bool
     */
    public function sendViaEmail(string $filePath, ?string $email = null): bool
    {
        $email = $email ?? Setting::get('backup_email', config('mail.from.address', 'admin@schoolofredemption.com'));

        // Check if mail is actually configured
        $mailMailer = config('mail.default', env('MAIL_MAILER', 'log'));
        if (in_array($mailMailer, ['log', 'array', 'null'])) {
            Log::warning('Database backup email skipped: mail not configured (driver: ' . $mailMailer . ')');
            return false;
        }

        // Check if SMTP credentials are actually set (not placeholder values)
        $mailHost = config('mail.mailers.smtp.host', env('MAIL_HOST'));
        $mailUser = config('mail.mailers.smtp.username', env('MAIL_USERNAME'));
        if (empty($mailHost) || empty($mailUser) || str_contains($mailUser, 'your-')) {
            Log::warning('Database backup email skipped: SMTP credentials appear to be placeholder values. Please configure MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD in .env');
            return false;
        }

        try {
            Mail::to($email)->send(new DatabaseBackupMail(
                $filePath,
                pathinfo($filePath, PATHINFO_EXTENSION) === 'gz' ? 'sql.gz' : 'sql',
                basename($filePath)
            ));

            Log::info('Database backup email sent', ['email' => $email, 'file' => basename($filePath)]);
            return true;
        } catch (\Exception $e) {
            Log::error('Database backup email failed: ' . $e->getMessage(), [
                'hint' => 'Check that MAIL_USERNAME and MAIL_PASSWORD in .env are valid. For Gmail, use an App Password from https://myaccount.google.com/apppasswords',
            ]);
            return false;
        }
    }

    /**
     * Get list of existing backup files with metadata.
     *
     * @return array<int, array{filename: string, size: int, size_human: string, date: string, timestamp: int}>
     */
    public function listBackups(): array
    {
        $this->ensureBackupDirectory();
        $backupPath = storage_path("app/{$this->backupDir}");

        $files = File::glob("{$backupPath}/backup_*");
        $backups = [];

        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                $size = File::size($file);
                $mtime = File::lastModified($file);

                $backups[] = [
                    'filename' => $filename,
                    'size' => $size,
                    'size_human' => $this->formatFileSize($size),
                    'date' => date('Y-m-d H:i:s', $mtime),
                    'timestamp' => $mtime,
                ];
            }
        }

        // Sort by date descending (newest first)
        usort($backups, fn($a, $b) => $b['timestamp'] - $a['timestamp']);

        return $backups;
    }

    /**
     * Delete a backup file.
     *
     * @param string $filename
     * @return bool
     */
    public function deleteBackup(string $filename): bool
    {
        // Prevent directory traversal
        $filename = basename($filename);
        $filepath = storage_path("app/{$this->backupDir}/{$filename}");

        if (File::exists($filepath) && str_starts_with($filename, 'backup_')) {
            File::delete($filepath);
            Log::info('Database backup deleted', ['filename' => $filename]);
            return true;
        }

        return false;
    }

    /**
     * Send a test email to verify mail configuration.
     *
     * @param string $email
     * @return array{success: bool, message: string}
     */
    public function sendTestEmail(string $email): array
    {
        $mailMailer = config('mail.default', env('MAIL_MAILER', 'log'));

        if (in_array($mailMailer, ['log', 'array', 'null'])) {
            return [
                'success' => false,
                'message' => "Mail is not configured. Current driver: '{$mailMailer}'. Please set MAIL_MAILER=smtp in your .env file.",
            ];
        }

        $mailHost = config('mail.mailers.smtp.host', env('MAIL_HOST'));
        $mailUser = config('mail.mailers.smtp.username', env('MAIL_USERNAME'));
        $mailPass = config('mail.mailers.smtp.password', env('MAIL_PASSWORD'));

        if (empty($mailHost) || empty($mailUser) || str_contains($mailUser, 'your-')) {
            return [
                'success' => false,
                'message' => 'SMTP credentials are not configured. Please set MAIL_HOST, MAIL_USERNAME, and MAIL_PASSWORD in your .env file. For Gmail, use an App Password from https://myaccount.google.com/apppasswords',
            ];
        }

        try {
            Mail::to($email)->send(new \App\Mail\TestMail());
            return [
                'success' => true,
                'message' => "Test email sent successfully to {$email}. Check your inbox (and spam folder).",
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage() . '. For Gmail, make sure you are using an App Password (not your regular password) from https://myaccount.google.com/apppasswords',
            ];
        }
    }

    /**
     * Get the full path for a backup file.
     *
     * @param string $filename
     * @return string|null
     */
    public function getBackupPath(string $filename): ?string
    {
        $filename = basename($filename);
        $filepath = storage_path("app/{$this->backupDir}/{$filename}");

        if (File::exists($filepath) && str_starts_with($filename, 'backup_')) {
            return $filepath;
        }

        return null;
    }

    /**
     * Clean up old backups, keeping only the N most recent.
     *
     * @param int|null $keepCount Number of backups to keep (defaults to setting)
     * @return int Number of backups deleted
     */
    public function cleanupOldBackups(?int $keepCount = null): int
    {
        $keepCount = $keepCount ?? (int) Setting::get('backup_keep_count', 10);
        $backups = $this->listBackups();

        if (count($backups) <= $keepCount) {
            return 0;
        }

        $toDelete = array_slice($backups, $keepCount);
        $deleted = 0;

        foreach ($toDelete as $backup) {
            if ($this->deleteBackup($backup['filename'])) {
                $deleted++;
            }
        }

        if ($deleted > 0) {
            Log::info("Cleaned up {$deleted} old database backup(s), keeping {$keepCount}");
        }

        return $deleted;
    }

    /**
     * Backup using mysqldump command.
     *
     * @param string $filepath
     * @throws \Exception
     */
    protected function backupWithMysqldump(string $filepath): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $database = $config['database'] ?? '';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        if (empty($database)) {
            throw new \Exception('Database name is not configured.');
        }

        // Build mysqldump command
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --port=%s --charset=%s --single-transaction --quick --lock-tables=false %s > %s 2>&1',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($charset),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            // If mysqldump fails, fall back to PHP-based backup
            Log::warning('mysqldump failed (code: ' . $returnCode . '), falling back to PHP-based export');
            $this->backupWithPhp($filepath);
        }

        // Verify the file was created and is not empty
        if (!File::exists($filepath) || File::size($filepath) === 0) {
            Log::warning('mysqldump produced empty file, falling back to PHP-based export');
            $this->backupWithPhp($filepath);
        }
    }

    /**
     * Fallback PHP-based backup (works with any database driver).
     *
     * @param string $filepath
     */
    protected function backupWithPhp(string $filepath): void
    {
        $lines = [];
        $lines[] = '-- Redemption School Management System - Database Backup';
        $lines[] = '-- Generated: ' . now()->toIso8601String();
        $lines[] = '-- Method: PHP-based export';
        $lines[] = '-- ' . str_repeat('-', 60);
        $lines[] = '';

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $lines[] = 'SET FOREIGN_KEY_CHECKS=0;';
            $lines[] = '';
        }

        $tables = $this->getTableList();

        foreach ($tables as $table) {
            $lines[] = "-- Table: {$table}";
            $lines[] = '';

            // Get CREATE TABLE statement
            if ($driver === 'mysql' || $driver === 'mariadb') {
                try {
                    $createResult = \DB::select("SHOW CREATE TABLE `{$table}`");
                    if (!empty($createResult)) {
                        $createKey = 'Create Table';
                        $lines[] = 'DROP TABLE IF EXISTS `' . $table . '`;';
                        $lines[] = $createResult[0]->$createKey . ';';
                        $lines[] = '';
                    }
                } catch (\Exception $e) {
                    $lines[] = "-- Error getting CREATE TABLE for {$table}: " . $e->getMessage();
                }
            } elseif ($driver === 'sqlite') {
                try {
                    $createResult = \DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table]);
                    if (!empty($createResult) && $createResult[0]->sql) {
                        $lines[] = 'DROP TABLE IF EXISTS `' . $table . '`;';
                        $lines[] = $createResult[0]->sql . ';';
                        $lines[] = '';
                    }
                } catch (\Exception $e) {
                    $lines[] = "-- Error getting CREATE TABLE for {$table}: " . $e->getMessage();
                }
            }

            // Get rows
            try {
                $rows = \DB::table($table)->get();
                if ($rows->count() > 0) {
                    $columns = array_keys((array) $rows->first());

                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($columns as $col) {
                            $val = $row->$col;
                            if ($val === null) {
                                $values[] = 'NULL';
                            } elseif (is_numeric($val)) {
                                $values[] = $val;
                            } else {
                                $values[] = "'" . addslashes((string) $val) . "'";
                            }
                        }
                        $colList = implode('`, `', $columns);
                        $valList = implode(', ', $values);
                        $lines[] = "INSERT INTO `{$table}` (`{$colList}`) VALUES ({$valList});";
                    }
                    $lines[] = '';
                }
            } catch (\Exception $e) {
                $lines[] = "-- Error exporting data for {$table}: " . $e->getMessage();
            }
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
        }
        $lines[] = '';
        $lines[] = '-- Backup completed at ' . now()->toIso8601String();

        File::put($filepath, implode("\n", $lines));
    }

    /**
     * Compress a file using gzip.
     *
     * @param string $filepath
     * @return bool Whether compression succeeded
     */
    protected function compressFile(string $filepath): bool
    {
        if (!function_exists('gzencode')) {
            return false;
        }

        try {
            $data = File::get($filepath);
            $compressed = gzencode($data, 9);

            if ($compressed === false) {
                return false;
            }

            File::put($filepath . '.gz', $compressed);
            return true;
        } catch (\Exception $e) {
            Log::warning('Backup compression failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get list of database tables.
     */
    protected function getTableList(): array
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'sqlite') {
            $results = \DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
            return array_map(fn($r) => $r->name, $results);
        }

        // MySQL / MariaDB
        $database = config("database.connections.{$connection}.database");
        $results = \DB::select(
            "SELECT TABLE_NAME as name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME",
            [$database]
        );
        return array_map(fn($r) => $r->name, $results);
    }

    /**
     * Ensure the backup directory exists.
     */
    protected function ensureBackupDirectory(): void
    {
        $path = storage_path("app/{$this->backupDir}");
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    /**
     * Format file size in human-readable format.
     */
    protected function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }
}
