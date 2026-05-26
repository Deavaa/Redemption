<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'user_id',
        'comment_type',
        'visibility',
        'comment',
        'is_report_comment',
    ];

    protected $casts = [
        'is_report_comment' => 'boolean',
    ];

    /**
     * Comment type labels for display.
     */
    public static function commentTypes(): array
    {
        return [
            'general' => 'General Comment',
            'academic' => 'Academic',
            'behavior' => 'Behavior',
            'attendance' => 'Attendance',
            'progress' => 'Progress',
        ];
    }

    /**
     * Visibility labels for display.
     */
    public static function visibilityOptions(): array
    {
        return [
            'private' => 'Private (only author)',
            'staff' => 'Staff Only',
            'public' => 'Public (visible to parents)',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeForReport($query)
    {
        return $query->where('is_report_comment', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('comment_type', $type);
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeStaffVisible($query)
    {
        return $query->whereIn('visibility', ['staff', 'public']);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // ── Accessors ──────────────────────────────────────────────────

    public function getCommentTypeLabelAttribute(): string
    {
        return self::commentTypes()[$this->comment_type] ?? $this->comment_type;
    }

    public function getVisibilityLabelAttribute(): string
    {
        return self::visibilityOptions()[$this->visibility] ?? $this->visibility;
    }

    public function getAuthorNameAttribute(): string
    {
        return $this->user?->name ?? 'Unknown';
    }

    public function getAuthorRoleAttribute(): string
    {
        return $this->user?->role ?? 'unknown';
    }
}
