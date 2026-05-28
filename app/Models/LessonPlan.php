<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'plan_type',
        'week_start_date', 'week_end_date', 'daily_breakdown',
        'yearly_overview', 'term_goals',
        'department_head_status', 'department_head_comment', 'department_head_id', 'department_head_reviewed_at',
        'principal_status', 'principal_comment', 'principal_reviewed_id', 'principal_reviewed_at',
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
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'daily_breakdown' => 'array',
        'term_goals' => 'array',
        'department_head_reviewed_at' => 'datetime',
        'principal_reviewed_at' => 'datetime',
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

    public static function planTypeOptions(): array
    {
        return [
            'daily'   => 'Daily Lesson Plan',
            'weekly'  => 'Weekly Lesson Plan',
            'yearly'  => 'Yearly Lesson Plan',
        ];
    }

    public static function planTypeBadgeClass(string $type): string
    {
        return match ($type) {
            'daily'   => 'modern-badge-info',
            'weekly'  => 'modern-badge-purple',
            'yearly'  => 'modern-badge-warning',
            default   => 'modern-badge-light',
        };
    }

    public static function departmentHeadStatusOptions(): array
    {
        return [
            'pending'  => 'Pending',
            'reviewed' => 'Reviewed',
            'approved' => 'Approved',
            'revision' => 'Needs Revision',
        ];
    }

    public static function principalStatusOptions(): array
    {
        return [
            'pending'  => 'Pending',
            'reviewed' => 'Reviewed',
            'approved' => 'Approved',
            'revision' => 'Needs Revision',
        ];
    }

    public function departmentHead()
    {
        return $this->belongsTo(User::class, 'department_head_id');
    }

    public function principalReviewer()
    {
        return $this->belongsTo(User::class, 'principal_reviewed_id');
    }

    public function getApprovalPipelineStep(): int
    {
        if ($this->status === 'approved') return 3;
        if ($this->principal_status === 'pending' && $this->department_head_status === 'approved') return 2;
        return 1;
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

    public function contentNotes()
    {
        return $this->hasMany(SubjectContentNote::class, 'lesson_plan_id');
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
