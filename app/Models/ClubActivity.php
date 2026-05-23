<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClubActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'club_id',
        'title',
        'description',
        'activity_type',
        'start_datetime',
        'end_datetime',
        'location',
        'participants_count',
        'objectives',
        'outcomes',
        'challenges',
        'recommendations',
        'status',
        'organized_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'approved_at' => 'datetime',
        'participants_count' => 'integer',
    ];

    /* ── Relationships ── */

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function organizedBy()
    {
        return $this->belongsTo(User::class, 'organized_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function followUps()
    {
        return $this->hasMany(ClubActivityFollowUp::class);
    }

    /* ── Scopes ── */

    public function scopeForStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>=', now())->orderBy('start_datetime');
    }

    public function scopeForClub($query, int $clubId)
    {
        return $query->where('club_id', $clubId);
    }

    /* ── Status helpers ── */

    public static function statusOptions(): array
    {
        return [
            'planned' => 'Planned',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'planned' => 'modern-badge-info',
            'in_progress' => 'modern-badge-warning',
            'completed' => 'modern-badge-success',
            'cancelled' => 'modern-badge-danger',
            default => 'modern-badge-light',
        };
    }

    public static function activityTypeOptions(): array
    {
        return [
            'meeting' => 'Meeting',
            'event' => 'Event',
            'competition' => 'Competition',
            'workshop' => 'Workshop',
            'community_service' => 'Community Service',
            'field_trip' => 'Field Trip',
            'project' => 'Project',
        ];
    }

    public static function activityTypeBadgeClass(string $type): string
    {
        return match ($type) {
            'meeting' => 'modern-badge-info',
            'event' => 'modern-badge-purple',
            'competition' => 'modern-badge-warning',
            'workshop' => 'modern-badge-cyan',
            'community_service' => 'modern-badge-success',
            'field_trip' => 'modern-badge-orange',
            'project' => 'modern-badge-danger',
            default => 'modern-badge-light',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst($this->status);
    }

    public function getActivityTypeLabelAttribute(): string
    {
        return self::activityTypeOptions()[$this->activity_type] ?? ucfirst($this->activity_type);
    }

    public function isApproved(): bool
    {
        return $this->approved_by !== null;
    }
}
