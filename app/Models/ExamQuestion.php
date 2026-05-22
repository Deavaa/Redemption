<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'class_id',
        'section_id',
        'exam_id',
        'academic_year_id',
        'term_id',
        'branch_id',
        'title',
        'description',
        'questions',
        'question_type',
        'total_marks',
        'duration_minutes',
        'status',
        'department_head_comment',
        'department_head_id',
        'department_head_reviewed_at',
        'principal_comment',
        'principal_id',
        'principal_reviewed_at',
    ];

    protected $casts = [
        'department_head_reviewed_at' => 'datetime',
        'principal_reviewed_at' => 'datetime',
    ];

    /* ── Status helpers ── */
    public static function statusOptions(): array
    {
        return [
            'pending_department'    => 'Pending Department Review',
            'pending_principal'    => 'Pending Principal Review',
            'approved'             => 'Approved',
            'rejected_by_department' => 'Rejected by Department',
            'rejected_by_principal'  => 'Rejected by Principal',
            'revision'             => 'Needs Revision',
        ];
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'pending_department'      => 'modern-badge-warning',
            'pending_principal'       => 'modern-badge-info',
            'approved'                => 'modern-badge-success',
            'rejected_by_department'  => 'modern-badge-danger',
            'rejected_by_principal'   => 'modern-badge-danger',
            'revision'                => 'modern-badge-orange',
            default                   => 'modern-badge-light',
        };
    }

    public static function questionTypeOptions(): array
    {
        return [
            'multiple_choice' => 'Multiple Choice',
            'essay'           => 'Essay',
            'short_answer'    => 'Short Answer',
            'mixed'           => 'Mixed',
        ];
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

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function departmentHead()
    {
        return $this->belongsTo(User::class, 'department_head_id');
    }

    public function principal()
    {
        return $this->belongsTo(User::class, 'principal_id');
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

    public function scopePendingDepartmentReview($query)
    {
        return $query->where('status', 'pending_department');
    }

    public function scopePendingPrincipalReview($query)
    {
        return $query->where('status', 'pending_principal');
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /* ── Helpers ── */
    public function getQuestionsArray(): array
    {
        if (is_array($this->questions)) {
            return $this->questions;
        }

        $decoded = json_decode($this->questions, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['pending_department', 'revision', 'rejected_by_department', 'rejected_by_principal']);
    }

    public function isDeletable(): bool
    {
        return in_array($this->status, ['pending_department']);
    }

    public function getPipelineStep(): int
    {
        return match ($this->status) {
            'pending_department', 'revision', 'rejected_by_department' => 1,
            'pending_principal', 'rejected_by_principal' => 2,
            'approved' => 3,
            default => 1,
        };
    }
}
