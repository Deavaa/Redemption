<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DatabaseExportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The path to the backup file.
     */
    public string $filePath;

    /**
     * The export format (sql or csv).
     */
    public string $format;

    /**
     * The original file name.
     */
    public string $fileName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $filePath, string $format, string $fileName)
    {
        $this->filePath = $filePath;
        $this->format = $format;
        $this->fileName = $fileName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('app.db_export_email_subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.database-export',
            with: [
                'format' => $this->format,
                'fileName' => $this->fileName,
                'fileSize' => $this->formatFileSize(filesize($this->filePath)),
                'generatedAt' => now()->format('F j, Y \a\t g:i A'),
                'appName' => config('app.name'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)
                ->as($this->fileName)
                ->withMime($this->format === 'csv' ? 'application/zip' : 'application/sql'),
        ];
    }

    /**
     * Format file size in human-readable format.
     */
    protected function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }
}
