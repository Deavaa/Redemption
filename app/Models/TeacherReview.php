<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'academic_year_id',
        'term_id',
        'teaching_quality',
        'communication',
        'punctuality',
        'subject_knowledge',
        'helpfulness',
        'fairness',
        'overall_score',
        'grade',
        'strengths',
        'areas_for_improvement',
        'additional_comments',
        'is_anonymous',
        'submitted_at',
        'status',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'submitted_at' => 'datetime',
        'overall_score' => 'decimal:2',
        'teaching_quality' => 'integer',
        'communication' => 'integer',
        'punctuality' => 'integer',
        'subject_knowledge' => 'integer',
        'helpfulness' => 'integer',
        'fairness' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeForTerm($query, int $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeForTeacher($query, int $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForAcademicYear($query, int $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    // ─── Score Calculation ───────────────────────────────────────

    /**
     * Calculate overall score from criteria ratings.
     * Average of 6 criteria, scaled to 0-100.
     */
    public function calculateOverallScore(): float
    {
        $criteria = [
            $this->teaching_quality,
            $this->communication,
            $this->punctuality,
            $this->subject_knowledge,
            $this->helpfulness,
            $this->fairness,
        ];

        $validCriteria = array_filter($criteria, fn($v) => $v > 0);
        if (empty($validCriteria)) {
            return 0;
        }

        $average = array_sum($validCriteria) / count($validCriteria);

        // Scale from 1-5 to 0-100
        return round((($average - 1) / 4) * 100, 2);
    }

    /**
     * Get the grade label based on overall score.
     */
    public function calculateGrade(): string
    {
        $score = $this->overall_score;

        if ($score >= 90) return 'excellent';
        if ($score >= 75) return 'good';
        if ($score >= 60) return 'satisfactory';
        if ($score >= 40) return 'needs_improvement';
        return 'unsatisfactory';
    }

    // ─── Static Helpers ──────────────────────────────────────────

    /**
     * Rating criteria with labels for forms.
     */
    public static function criteriaOptions(): array
    {
        return [
            'teaching_quality' => 'Teaching Quality',
            'communication' => 'Communication & Clarity',
            'punctuality' => 'Punctuality & Attendance',
            'subject_knowledge' => 'Subject Knowledge',
            'helpfulness' => 'Helpfulness & Approachability',
            'fairness' => 'Fairness & Impartiality',
        ];
    }

    /**
     * Grade options with labels and colors.
     */
    public static function gradeOptions(): array
    {
        return [
            'excellent' => ['label' => 'Excellent', 'color' => '#10b981', 'icon' => '⭐'],
            'good' => ['label' => 'Good', 'color' => '#3b82f6', 'icon' => '👍'],
            'satisfactory' => ['label' => 'Satisfactory', 'color' => '#f59e0b', 'icon' => '👌'],
            'needs_improvement' => ['label' => 'Needs Improvement', 'color' => '#f97316', 'icon' => '⚠️'],
            'unsatisfactory' => ['label' => 'Unsatisfactory', 'color' => '#ef4444', 'icon' => '❌'],
        ];
    }

    /**
     * Rating scale labels.
     */
    public static function ratingScale(): array
    {
        return [
            1 => 'Poor',
            2 => 'Fair',
            3 => 'Good',
            4 => 'Very Good',
            5 => 'Excellent',
        ];
    }

    /**
     * Check if a student has already reviewed a teacher for a specific term.
     */
    public static function hasReviewed(int $studentId, int $teacherId, int $termId): bool
    {
        return self::where('student_id', $studentId)
            ->where('teacher_id', $teacherId)
            ->where('term_id', $termId)
            ->exists();
    }

    /**
     * Get average ratings for a teacher in a term.
     */
    public static function getTeacherTermAverage(int $teacherId, int $termId): ?array
    {
        $reviews = self::where('teacher_id', $teacherId)
            ->where('term_id', $termId)
            ->where('status', 'submitted')
            ->get();

        if ($reviews->isEmpty()) {
            return null;
        }

        $criteria = ['teaching_quality', 'communication', 'punctuality', 'subject_knowledge', 'helpfulness', 'fairness'];
        $averages = [];

        foreach ($criteria as $criterion) {
            $values = $reviews->pluck($criterion)->filter(fn($v) => $v > 0);
            $averages[$criterion] = $values->count() > 0 ? round($values->avg(), 2) : 0;
        }

        $overallAvg = $reviews->avg('overall_score');

        return [
            'count' => $reviews->count(),
            'averages' => $averages,
            'overall_score' => round($overallAvg, 2),
            'grade' => $reviews->first()->calculateGrade() ?? 'satisfactory',
        ];
    }
}
