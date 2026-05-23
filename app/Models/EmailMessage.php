<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_inbox_setting_id',
        'message_id',
        'subject',
        'body_html',
        'body_text',
        'from_name',
        'from_email',
        'to_email',
        'cc',
        'attachments',
        'received_at',
        'is_read',
        'is_starred',
        'category',
        'assigned_to',
        'notes',
    ];

    protected $casts = [
        'cc' => 'array',
        'attachments' => 'array',
        'received_at' => 'datetime',
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
    ];

    public function inboxSetting()
    {
        return $this->belongsTo(EmailInboxSetting::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public static function categoryOptions(): array
    {
        return [
            'admission' => 'Admission',
            'fee' => 'Fee Payment',
            'general' => 'General Inquiry',
            'complaint' => 'Complaint',
            'academic' => 'Academic',
            'hr' => 'Human Resources',
            'uncategorized' => 'Uncategorized',
        ];
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categoryOptions()[$this->category] ?? 'Uncategorized';
    }
}
