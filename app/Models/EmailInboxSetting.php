<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EmailInboxSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'email_address',
        'imap_host',
        'imap_port',
        'imap_username',
        'imap_password',
        'imap_protocol',
        'imap_encryption',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'is_default_sender',
        'folder',
        'is_active',
        'sync_interval_minutes',
        'last_synced_at',
    ];

    // Sensitive credentials — never expose in JSON/array responses.
    protected $hidden = [
        'imap_password',
        'imap_username',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default_sender' => 'boolean',
        'imap_port' => 'integer',
        'smtp_port' => 'integer',
        'sync_interval_minutes' => 'integer',
        'last_synced_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function messages()
    {
        return $this->hasMany(EmailMessage::class);
    }

    public function getDecryptedPassword(): string
    {
        try {
            return Crypt::decryptString($this->imap_password);
        } catch (\Exception $e) {
            return $this->imap_password;
        }
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Get the inbox marked as the default sender (used for sending system
     * emails like backup notifications). Returns null if none is configured.
     */
    public static function getDefaultSender(): ?self
    {
        return static::where('is_default_sender', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get the SMTP host — falls back to the IMAP host if smtp_host is empty
     * (cPanel email typically uses the same host for both).
     */
    public function getSmtpHost(): string
    {
        return $this->smtp_host ?: $this->imap_host;
    }

    /**
     * Get the SMTP username — always the same as imap_username (cPanel
     * email uses the full email address as both IMAP and SMTP username).
     */
    public function getSmtpUsername(): string
    {
        return $this->imap_username ?: $this->email_address;
    }

    /**
     * Get the decrypted SMTP password — same as IMAP password.
     */
    public function getSmtpPassword(): string
    {
        return $this->getDecryptedPassword();
    }
}
