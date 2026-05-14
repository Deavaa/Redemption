<?php

namespace App\Http\Controllers;

use App\Mail\DatabaseExportMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class DatabaseBackupController extends Controller
{
    /**
     * Recipient email for database exports.
     */
    protected string $recipientEmail = 'dawitac@gmail.com';

    /**
     * Show the database backup page.
     */
    public function index()
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver", 'unknown');
        $mailMailer = config('mail.default', env('MAIL_MAILER', 'log'));
        $mailConfigured = !in_array($mailMailer, ['log', 'array', 'null']);

        // Gather database stats
        $tables = $this->getTableList();
        $tableCounts = [];
        $totalRecords = 0;

        foreach ($tables as $table) {
            try {
                $count = DB::table($table)->count();
                $tableCounts[$table] = $count;
                $totalRecords += $count;
            } catch (\Exception $e) {
                $tableCounts[$table] = 'error';
            }
        }

        // Check last backup time from settings or log
        $lastBackup = null;
        $logFile = storage_path('logs/backup.log');
        if (file_exists($logFile)) {
            $lastBackup = filemtime($logFile);
        }

        return view('admin.database-backup.index', compact(
            'connection', 'driver', 'tables', 'tableCounts', 'totalRecords', 'lastBackup',
            'mailMailer', 'mailConfigured'
        ));
    }

    /**
     * Export the database and send via email.
     */
    public function exportAndSend(Request $request)
    {
        $request->validate([
            'format' => 'nullable|in:sql,csv',
            'tables' => 'nullable|array',
            'tables.*' => 'string',
            'email' => 'nullable|email',
        ]);

        // Check if mail is actually configured
        $mailMailer = config('mail.default', env('MAIL_MAILER', 'log'));
        if (in_array($mailMailer, ['log', 'array', 'null'])) {
            return redirect()->back()->with('error',
                'Email is not configured! The current mail driver is "' . $mailMailer . '" which only writes to log files. ' .
                'Please configure SMTP in your .env file (MAIL_MAILER=smtp, MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD) to actually send emails. ' .
                'For Gmail, use an App Password from https://myaccount.google.com/apppasswords'
            );
        }

        try {
            $format = $request->input('format', 'sql');
            $selectedTables = $request->input('tables', []);
            $email = $request->input('email', $this->recipientEmail);

            // Generate the export
            if ($format === 'csv') {
                $files = $this->exportAsCsv($selectedTables);
                $archivePath = $this->createZipArchive($files, 'csv');
            } else {
                $sqlContent = $this->exportAsSql($selectedTables);
                $timestamp = now()->format('Y-m-d_His');
                $fileName = "redemption_backup_{$timestamp}.sql";
                $archivePath = storage_path("app/backups/{$fileName}");

                // Ensure backups directory exists
                $backupDir = storage_path('app/backups');
                if (!File::isDirectory($backupDir)) {
                    File::makeDirectory($backupDir, 0755, true);
                }

                File::put($archivePath, $sqlContent);
            }

            // Send via email
            Mail::to($email)->send(new DatabaseExportMail(
                $archivePath,
                $format,
                basename($archivePath)
            ));

            // Log the backup
            Log::channel('single')->info("Database backup sent to {$email}", [
                'format' => $format,
                'file' => basename($archivePath),
                'size' => File::size($archivePath),
                'tables' => count($selectedTables) > 0 ? implode(',', $selectedTables) : 'all',
            ]);

            // Also store backup info
            $logFile = storage_path('logs/backup.log');
            File::append($logFile, now()->toISOString() . " | Backup sent to {$email} | " . basename($archivePath) . "\n");

            return redirect()->back()->with('success',
                __('app.db_export_success', ['email' => $email])
            );

        } catch (\Exception $e) {
            Log::error('Database export failed: ' . $e->getMessage());
            return redirect()->back()->with('error',
                __('app.db_export_error', ['message' => $e->getMessage()])
            );
        }
    }

    /**
     * Quick export: one-click backup and send to default email.
     */
    public function quickExport()
    {
        // Check if mail is actually configured
        $mailMailer = config('mail.default', env('MAIL_MAILER', 'log'));
        if (in_array($mailMailer, ['log', 'array', 'null'])) {
            return redirect()->back()->with('error',
                'Email is not configured! The current mail driver is "' . $mailMailer . '" which only writes to log files. ' .
                'Please configure SMTP in your .env file (MAIL_MAILER=smtp, MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD) to actually send emails. ' .
                'For Gmail, use an App Password from https://myaccount.google.com/apppasswords'
            );
        }

        try {
            $sqlContent = $this->exportAsSql([]);
            $timestamp = now()->format('Y-m-d_His');
            $fileName = "redemption_backup_{$timestamp}.sql";
            $archivePath = storage_path("app/backups/{$fileName}");

            $backupDir = storage_path('app/backups');
            if (!File::isDirectory($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            File::put($archivePath, $sqlContent);

            Mail::to($this->recipientEmail)->send(new DatabaseExportMail(
                $archivePath,
                'sql',
                basename($archivePath)
            ));

            $logFile = storage_path('logs/backup.log');
            File::append($logFile, now()->toISOString() . " | Quick backup sent to {$this->recipientEmail} | " . basename($archivePath) . "\n");

            return redirect()->back()->with('success',
                __('app.db_export_success', ['email' => $this->recipientEmail])
            );

        } catch (\Exception $e) {
            Log::error('Quick database export failed: ' . $e->getMessage());
            return redirect()->back()->with('error',
                __('app.db_export_error', ['message' => $e->getMessage()])
            );
        }
    }

    /**
     * Export database as SQL dump.
     */
    protected function exportAsSql(array $selectedTables): string
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'sqlite') {
            return $this->exportSqliteAsSql($selectedTables);
        }

        return $this->exportMysqlAsSql($selectedTables);
    }

    /**
     * Export SQLite database as SQL.
     */
    protected function exportSqliteAsSql(array $selectedTables): string
    {
        $lines = [];
        $lines[] = '-- Redemption School Management System - Database Backup';
        $lines[] = '-- Generated: ' . now()->toIso8601String();
        $lines[] = '-- Driver: SQLite';
        $lines[] = '-- ' . str_repeat('-', 60);
        $lines[] = '';

        $tables = count($selectedTables) > 0 ? $selectedTables : $this->getTableList();

        foreach ($tables as $table) {
            $lines[] = "-- Table: {$table}";
            $lines[] = '';

            // Get CREATE TABLE statement
            $createResult = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table]);
            if (!empty($createResult) && $createResult[0]->sql) {
                $lines[] = 'DROP TABLE IF EXISTS `' . $table . '`;';
                $lines[] = $createResult[0]->sql . ';';
                $lines[] = '';
            }

            // Get rows
            $rows = DB::table($table)->get();
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
                            $values[] = "'" . str_replace("'", "''", (string) $val) . "'";
                        }
                    }
                    $colList = implode('`, `', $columns);
                    $valList = implode(', ', $values);
                    $lines[] = "INSERT INTO `{$table}` (`{$colList}`) VALUES ({$valList});";
                }
                $lines[] = '';
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Export MySQL database as SQL.
     */
    protected function exportMysqlAsSql(array $selectedTables): string
    {
        $lines = [];
        $lines[] = '-- Redemption School Management System - Database Backup';
        $lines[] = '-- Generated: ' . now()->toIso8601String();
        $lines[] = '-- Driver: MySQL';
        $lines[] = '-- ' . str_repeat('-', 60);
        $lines[] = '';
        $lines[] = 'SET FOREIGN_KEY_CHECKS=0;';
        $lines[] = '';

        $tables = count($selectedTables) > 0 ? $selectedTables : $this->getTableList();

        foreach ($tables as $table) {
            $lines[] = "-- Table: {$table}";
            $lines[] = '';

            // Get CREATE TABLE statement
            $createResult = DB::select("SHOW CREATE TABLE `{$table}`");
            if (!empty($createResult)) {
                $createKey = 'Create Table';
                $lines[] = 'DROP TABLE IF EXISTS `' . $table . '`;';
                $lines[] = $createResult[0]->$createKey . ';';
                $lines[] = '';
            }

            // Get rows
            $rows = DB::table($table)->get();
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
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
        $lines[] = '';
        $lines[] = '-- Backup completed at ' . now()->toIso8601String();

        return implode("\n", $lines);
    }

    /**
     * Export selected tables as CSV files.
     */
    protected function exportAsCsv(array $selectedTables): array
    {
        $tables = count($selectedTables) > 0 ? $selectedTables : $this->getTableList();
        $files = [];
        $backupDir = storage_path('app/backups/csv_' . now()->format('Y-m-d_His'));

        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        foreach ($tables as $table) {
            $rows = DB::table($table)->get();
            $filePath = "{$backupDir}/{$table}.csv";

            $handle = fopen($filePath, 'w');
            if ($rows->count() > 0) {
                // Header
                $columns = array_keys((array) $rows->first());
                fputcsv($handle, $columns);

                // Data
                foreach ($rows as $row) {
                    fputcsv($handle, (array) $row);
                }
            }

            fclose($handle);
            $files[] = $filePath;
        }

        return $files;
    }

    /**
     * Create a ZIP archive from files.
     */
    protected function createZipArchive(array $files, string $format): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $archiveName = "redemption_backup_{$timestamp}.zip";
        $archivePath = storage_path("app/backups/{$archiveName}");

        $backupDir = storage_path('app/backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $zip = new \ZipArchive();
        $zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();

        // Clean up temporary CSV directory
        if (isset($files[0])) {
            $csvDir = dirname($files[0]);
            if (File::isDirectory($csvDir)) {
                File::deleteDirectory($csvDir);
            }
        }

        return $archivePath;
    }

    /**
     * Get list of database tables.
     */
    protected function getTableList(): array
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'sqlite') {
            $results = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
            return array_map(fn($r) => $r->name, $results);
        }

        // MySQL
        $database = config("database.connections.{$connection}.database");
        $results = DB::select("SELECT TABLE_NAME as name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME", [$database]);
        return array_map(fn($r) => $r->name, $results);
    }

    /**
     * Download the backup file directly.
     */
    public function download(Request $request)
    {
        $request->validate([
            'format' => 'nullable|in:sql,csv',
            'tables' => 'nullable|array',
            'tables.*' => 'string',
        ]);

        try {
            $format = $request->input('format', 'sql');
            $selectedTables = $request->input('tables', []);

            if ($format === 'csv') {
                $files = $this->exportAsCsv($selectedTables);
                $archivePath = $this->createZipArchive($files, 'csv');
            } else {
                $sqlContent = $this->exportAsSql($selectedTables);
                $timestamp = now()->format('Y-m-d_His');
                $fileName = "redemption_backup_{$timestamp}.sql";
                $archivePath = storage_path("app/backups/{$fileName}");

                $backupDir = storage_path('app/backups');
                if (!File::isDirectory($backupDir)) {
                    File::makeDirectory($backupDir, 0755, true);
                }

                File::put($archivePath, $sqlContent);
            }

            return response()->download($archivePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return redirect()->back()->with('error',
                __('app.db_export_error', ['message' => $e->getMessage()])
            );
        }
    }
}
