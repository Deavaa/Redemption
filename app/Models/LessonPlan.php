<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'class_id',
        'section_id',
        'academic_year_id',
        'term_id',
        'title',
        'objectives',
        'materials',
        'activities',
        'assessment',
        'homework',
        'notes',
        'week_number',
        'lesson_date',
        'duration_minutes',
        'status',
        'reviewer_comment',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'lesson_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    /* ── Status helpers ── */
    public static function statusOptions(): array
    {
        return [
            'draft'     => 'Draft',
            'submitted' => 'Submitted',
            'reviewed'  => 'Reviewed',
            'approved'  => 'Approved',
            'revision'  => 'Needs Revision',
        ];
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'draft'     => 'modern-badge-light',
            'submitted' => 'modern-badge-info',
            'reviewed'  => 'modern-badge-cyan',
            'approved'  => 'modern-badge-success',
            'revision'  => 'modern-badge-danger',
            default     => 'modern-badge-light',
        };
    }

    /* ── Relationships ── */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function followUps()
    {
        return $this->hasMany(LessonPlanFollowUp::class);
    }

    /* ── Scopes ── */
    public function scopeForTeacher($query, int $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForTerm($query, int $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('lesson_date', 'desc')->orderBy('created_at', 'desc');
    }
}
