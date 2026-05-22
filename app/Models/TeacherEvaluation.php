<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'evaluator_id',
        'academic_year_id',
        'term_id',
        'evaluation_type',
        'evaluation_date',
        'teaching_quality',
        'student_engagement',
        'classroom_management',
        'lesson_preparation',
        'professional_conduct',
        'communication_skills',
        'punctuality',
        'student_results',
        'overall_score',
        'grade',
        'strengths',
        'areas_for_improvement',
        'recommendations',
        'comments',
        'status',
        'acknowledged_at',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'acknowledged_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('evaluation_date', 'desc');
    }

    // ── Helpers ────────────────────────────────────────────

    public static function calculateOverallScore($attrs)
    {
        $criteria = [
            'teaching_quality', 'student_engagement', 'classroom_management',
            'lesson_preparation', 'professional_conduct', 'communication_skills',
            'punctuality', 'student_results'
        ];

        $total = 0;
        $count = 0;
        foreach ($criteria as $field) {
            if (isset($attrs[$field]) && $attrs[$field] > 0) {
                $total += $attrs[$field];
                $count++;
            }
        }

        return $count > 0 ? round(($total / $count) * 20, 2) : 0; // Scale 1-5 to 0-100
    }

    public static function scoreToGrade($score)
    {
        if ($score >= 90) return 'excellent';
        if ($score >= 75) return 'good';
        if ($score >= 60) return 'satisfactory';
        if ($score >= 40) return 'needs_improvement';
        return 'unsatisfactory';
    }

    public function getGradeLabelAttribute()
    {
        return match ($this->grade) {
            'excellent' => 'Excellent',
            'good' => 'Good',
            'satisfactory' => 'Satisfactory',
            'needs_improvement' => 'Needs Improvement',
            'unsatisfactory' => 'Unsatisfactory',
            default => ucfirst($this->grade ?? ''),
        };
    }

    public function getGradeBadgeAttribute()
    {
        return match ($this->grade) {
            'excellent' => 'modern-badge-success',
            'good' => 'modern-badge-info',
            'satisfactory' => 'modern-badge-light',
            'needs_improvement' => 'modern-badge-warning',
            'unsatisfactory' => 'modern-badge-danger',
            default => 'modern-badge-light',
        };
    }

    public function getEvaluationTypeLabelAttribute()
    {
        return match ($this->evaluation_type) {
            'periodic' => 'Periodic Review',
            'annual' => 'Annual Evaluation',
            'observation' => 'Classroom Observation',
            'peer_review' => 'Peer Review',
            default => ucfirst(str_replace('_', ' ', $this->evaluation_type ?? '')),
        };
    }
}
