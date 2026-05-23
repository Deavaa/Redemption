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
        'folder',
        'is_active',
        'sync_interval_minutes',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'imap_port' => 'integer',
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
}
