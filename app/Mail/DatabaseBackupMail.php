<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DatabaseBackupMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $filePath;
    public string $format;
    public string $fileName;

    public function __construct(string $filePath, string $format, string $fileName)
    {
        $this->filePath = $filePath;
        $this->format = $format;
        $this->fileName = $fileName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Scheduled Database Backup — ' . config('app.name'),
        );
    }

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

    public function attachments(): array
    {
        $mime = str_contains($this->fileName, '.gz')
            ? 'application/gzip'
            : 'application/sql';

        return [
            Attachment::fromPath($this->filePath)
                ->as($this->fileName)
                ->withMime($mime),
        ];
    }

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
