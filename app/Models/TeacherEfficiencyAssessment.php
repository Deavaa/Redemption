<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherEfficiencyAssessment extends Model
{
    use HasFactory, SoftDeletes;

    // ── Criteria Constants ─────────────────────────────────────
    public const CRITERIA = [
        'lesson_delivery'         => 'Lesson Delivery & Presentation',
        'student_assessment'      => 'Student Assessment & Feedback',
        'curriculum_coverage'     => 'Curriculum Coverage & Planning',
        'classroom_environment'   => 'Classroom Environment & Management',
        'student_participation'   => 'Student Participation & Engagement',
        'professional_development'=> 'Professional Development & Growth',
        'communication'           => 'Communication with Students & Parents',
        'time_management'         => 'Time Management & Punctuality',
        'collaboration'           => 'Collaboration with Colleagues',
        'result_achievement'      => 'Student Result Achievement',
    ];

    protected $fillable = [
        'teacher_id',
        'assessor_id',
        'academic_year_id',
        'term_id',
        'branch_id',
        'lesson_delivery',
        'student_assessment',
        'curriculum_coverage',
        'classroom_environment',
        'student_participation',
        'professional_development',
        'communication',
        'time_management',
        'collaboration',
        'result_achievement',
        'overall_score',
        'grade',
        'strengths',
        'areas_for_improvement',
        'action_plan',
        'comments',
        'status',
        'acknowledged_at',
        'is_locked',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'is_locked' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
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

    // ── Scopes ───────────────────────────────────────────────

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeByTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Calculate overall score from 10 criteria.
     * Average of 1-5 scores, scaled to 0-100.
     */
    public static function calculateOverallScore(array $attrs): float
    {
        $criteria = array_keys(self::CRITERIA);
        $total = 0;
        $count = 0;

        foreach ($criteria as $field) {
            if (isset($attrs[$field]) && $attrs[$field] > 0) {
                $total += $attrs[$field];
                $count++;
            }
        }

        return $count > 0 ? round(($total / $count) * 20, 2) : 0;
    }

    /**
     * Convert a 0-100 score to a grade.
     */
    public static function scoreToGrade(float $score): string
    {
        if ($score >= 90) return 'excellent';
        if ($score >= 75) return 'good';
        if ($score >= 60) return 'satisfactory';
        if ($score >= 40) return 'needs_improvement';
        return 'unsatisfactory';
    }

    // ── Accessors ────────────────────────────────────────────

    public function getGradeLabelAttribute(): string
    {
        return match ($this->grade) {
            'excellent'        => 'Excellent',
            'good'             => 'Good',
            'satisfactory'     => 'Satisfactory',
            'needs_improvement'=> 'Needs Improvement',
            'unsatisfactory'   => 'Unsatisfactory',
            default            => ucfirst($this->grade ?? ''),
        };
    }

    public function getGradeBadgeClassAttribute(): string
    {
        return match ($this->grade) {
            'excellent'        => 'modern-badge-success',
            'good'             => 'modern-badge-info',
            'satisfactory'     => 'modern-badge-light',
            'needs_improvement'=> 'modern-badge-warning',
            'unsatisfactory'   => 'modern-badge-danger',
            default            => 'modern-badge-light',
        };
    }

    /**
     * Get the criteria scores as an array for charts/breakdown.
     */
    public function getCriteriaScores(): array
    {
        $scores = [];
        foreach (self::CRITERIA as $field => $label) {
            $scores[$field] = [
                'label' => $label,
                'score' => $this->$field,
                'percentage' => ($this->$field / 5) * 100,
            ];
        }
        return $scores;
    }
}
