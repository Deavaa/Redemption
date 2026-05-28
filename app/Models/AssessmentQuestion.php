<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentQuestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'teacher_id', 'subject_id', 'class_id',
        'academic_year_id', 'title', 'question_text',
        'question_type', 'hint', 'explanation', 'worked_out_solution',
        'difficulty', 'topic', 'marks', 'is_active',
        // section_id and branch_id are kept in DB for schema compat but always NULL
        // Questions are class-level — apply to ALL branches and ALL sections
        'section_id', 'branch_id',
    ];

    protected $casts = [
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

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function options()
    {
        return $this->hasMany(AssessmentOption::class)->orderBy('sort_order');
    }

    public function answers()
    {
        return $this->hasMany(AssessmentAnswer::class);
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForClass($query, $classId, $sectionId = null)
    {
        // Questions are class-level: section_id is always null (applies to all sections)
        $query->where('class_id', $classId);
        return $query;
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    // ── Helpers ─────────────────────────────────────────────

    public function getCorrectOption()
    {
        return $this->options()->where('is_correct', true)->first();
    }

    public function getCorrectOptionLabel(): string
    {
        $correct = $this->getCorrectOption();
        return $correct ? $correct->option_label : '';
    }

    public function getStudentAnswerStats(): array
    {
        $total = $this->answers()->count();
        $correct = $this->answers()->where('is_correct', true)->count();
        return [
            'total_attempts' => $total,
            'correct_attempts' => $correct,
            'accuracy_rate' => $total > 0 ? round(($correct / $total) * 100, 1) : 0,
        ];
    }
}
