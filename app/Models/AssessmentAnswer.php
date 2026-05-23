<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentAnswer extends Model
{
    protected $fillable = [
        'student_id', 'assessment_question_id', 'assessment_option_id',
        'student_answer', 'is_correct', 'attempt_number', 'time_spent_seconds',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function question()
    {
        return $this->belongsTo(AssessmentQuestion::class);
    }

    public function option()
    {
        return $this->belongsTo(AssessmentOption::class);
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    // ── Helpers ─────────────────────────────────────────────

    public static function getStudentStats($studentId): array
    {
        $total = static::byStudent($studentId)->count();
        $correct = static::byStudent($studentId)->correct()->count();
        $uniqueQuestions = static::byStudent($studentId)
            ->distinct('assessment_question_id')
            ->count('assessment_question_id');

        return [
            'total_answers' => $total,
            'correct_answers' => $correct,
            'incorrect_answers' => $total - $correct,
            'accuracy_rate' => $total > 0 ? round(($correct / $total) * 100, 1) : 0,
            'unique_questions_attempted' => $uniqueQuestions,
        ];
    }

    public static function getStudentSubjectStats($studentId, $subjectId): array
    {
        $total = static::byStudent($studentId)
            ->whereHas('question', fn($q) => $q->where('subject_id', $subjectId))
            ->count();
        $correct = static::byStudent($studentId)
            ->correct()
            ->whereHas('question', fn($q) => $q->where('subject_id', $subjectId))
            ->count();

        return [
            'total_answers' => $total,
            'correct_answers' => $correct,
            'accuracy_rate' => $total > 0 ? round(($correct / $total) * 100, 1) : 0,
        ];
    }
}
