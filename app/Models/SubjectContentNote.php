<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectContentNote extends Model
{
    use SoftDeletes;

    protected $table = 'subject_content_notes';

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'class_id',
        'branch_id',
        'academic_year_id',
        'lesson_plan_id',
        'title',
        'description',
        'content',
        'topic',
        'chapter',
        'note_type',
        'difficulty',
        'is_shared',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function lessonPlan()
    {
        return $this->belongsTo(LessonPlan::class);
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'content_note_section')
            ->withTimestamps();
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    // ── Helpers ─────────────────────────────────────────────

    public static function noteTypeOptions(): array
    {
        return [
            'general'        => 'General',
            'summary'        => 'Summary',
            'formula'        => 'Formulas & Equations',
            'definition'     => 'Definitions & Key Terms',
            'worked_example' => 'Worked Examples',
            'reference'      => 'Reference Material',
        ];
    }

    public static function noteTypeBadgeClass(): array
    {
        return [
            'general'        => 'modern-badge-info',
            'summary'        => 'modern-badge-success',
            'formula'        => 'modern-badge-warning',
            'definition'     => 'modern-badge-info',
            'worked_example' => 'modern-badge-danger',
            'reference'      => 'modern-badge-light',
        ];
    }

    public static function difficultyOptions(): array
    {
        return [
            'easy'   => 'Easy',
            'medium' => 'Medium',
            'hard'   => 'Hard',
        ];
    }
}
