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
        // Exam mode fields
        'is_exam', 'exam_duration_minutes', 'max_attempts',
        'exam_opens_at', 'exam_closes_at', 'show_results_immediately',
        // section_id and branch_id are kept in DB for schema compat but always NULL
        'section_id', 'branch_id',
        // Safe Exam Browser integration
        'seb_mode', 'seb_config_key', 'seb_exam_keys',
        'seb_allow_quit', 'seb_quit_password',
        'seb_show_taskbar', 'seb_show_time',
        'seb_allow_spell_check', 'seb_browser_view_mode',
        'seb_allowed_urls',
    ];

    // SEB secrets — never leak to JSON/array responses.
    protected $hidden = [
        'seb_config_key',
        'seb_quit_password',
        'seb_exam_keys',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_exam' => 'boolean',
        'show_results_immediately' => 'boolean',
        'exam_opens_at' => 'datetime',
        'exam_closes_at' => 'datetime',
        'seb_allow_quit' => 'boolean',
        'seb_show_taskbar' => 'boolean',
        'seb_show_time' => 'boolean',
        'seb_allow_spell_check' => 'boolean',
        'seb_exam_keys' => 'array',
        'seb_allowed_urls' => 'array',
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

    public function scopeExams($query)
    {
        return $query->where('is_exam', true);
    }

    public function scopePractice($query)
    {
        return $query->where('is_exam', false);
    }

    public function scopeExamOpen($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('exam_opens_at')->orWhere('exam_opens_at', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('exam_closes_at')->orWhere('exam_closes_at', '>=', $now);
        });
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

    // ── Safe Exam Browser Helpers ────────────────────────────

    public function isSebRequired(): bool
    {
        return $this->seb_mode === 'required';
    }

    public function isSebOptional(): bool
    {
        return $this->seb_mode === 'optional';
    }

    public function isSebEnabled(): bool
    {
        return in_array($this->seb_mode, ['required', 'optional']);
    }

    public function getSebModeLabel(): string
    {
        return match ($this->seb_mode) {
            'required' => 'SEB Required',
            'optional' => 'SEB Optional',
            default => 'No SEB',
        };
    }

    public static function sebModeOptions(): array
    {
        return [
            'none' => 'No SEB (Normal Browser)',
            'optional' => 'SEB Optional (Browser or SEB)',
            'required' => 'SEB Required (Must use Safe Exam Browser)',
        ];
    }

    public static function sebBrowserViewModeOptions(): array
    {
        return [
            0 => 'Window Mode',
            1 => 'Full Screen',
            2 => 'Full Screen with Touch Optimization',
        ];
    }

    // ── Exam Helpers ─────────────────────────────────────────

    public function isExam(): bool
    {
        return (bool) $this->is_exam;
    }

    public function isPractice(): bool
    {
        return !$this->is_exam;
    }

    public function isExamOpen(): bool
    {
        $now = now();
        if ($this->exam_opens_at && $now < $this->exam_opens_at) return false;
        if ($this->exam_closes_at && $now > $this->exam_closes_at) return false;
        return true;
    }

    public function isExamClosed(): bool
    {
        return $this->exam_closes_at && now() > $this->exam_closes_at;
    }

    public function isExamNotYetOpen(): bool
    {
        return $this->exam_opens_at && now() < $this->exam_opens_at;
    }

    public function hasTimeLimit(): bool
    {
        return $this->is_exam && $this->exam_duration_minutes > 0;
    }

    public function hasAttemptLimit(): bool
    {
        return $this->is_exam && $this->max_attempts > 0;
    }

    public function getRemainingAttemptsAttribute($studentId): int
    {
        if (!$this->hasAttemptLimit()) return PHP_INT_MAX;
        $used = \App\Models\AssessmentAnswer::where('student_id', $studentId)
            ->where('assessment_question_id', $this->id)
            ->distinct('attempt_number')
            ->count('attempt_number');
        return max(0, $this->max_attempts - $used);
    }

    public function getExamStatusLabel(): string
    {
        if (!$this->is_exam) return 'Practice';
        if ($this->isExamNotYetOpen()) return 'Scheduled';
        if ($this->isExamClosed()) return 'Closed';
        return 'Open';
    }
}
