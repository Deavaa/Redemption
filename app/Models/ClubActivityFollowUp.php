<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubActivityFollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_activity_id',
        'followed_up_by',
        'follow_up_date',
        'completion_status',
        'observations',
        'achievements',
        'issues',
        'action_items',
        'actual_participants',
        'photos',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'actual_participants' => 'integer',
    ];

    /* ── Relationships ── */

    public function activity()
    {
        return $this->belongsTo(ClubActivity::class, 'club_activity_id');
    }

    public function followedUpBy()
    {
        return $this->belongsTo(User::class, 'followed_up_by');
    }

    /* ── Status helpers ── */

    public static function completionStatusOptions(): array
    {
        return [
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'postponed' => 'Postponed',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function completionBadgeClass(string $status): string
    {
        return match ($status) {
            'not_started' => 'modern-badge-light',
            'in_progress' => 'modern-badge-info',
            'completed' => 'modern-badge-success',
            'postponed' => 'modern-badge-warning',
            'cancelled' => 'modern-badge-danger',
            default => 'modern-badge-light',
        };
    }

    public function getCompletionStatusLabelAttribute(): string
    {
        return self::completionStatusOptions()[$this->completion_status] ?? ucfirst($this->completion_status);
    }

    /* ── Photo helpers ── */

    public function getPhotoUrlsAttribute(): array
    {
        if (!$this->photos) return [];
        $paths = json_decode($this->photos, true) ?? [];
        return array_map(fn($p) => asset('storage/' . $p), $paths);
    }
}
