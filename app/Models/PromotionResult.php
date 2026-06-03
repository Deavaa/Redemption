<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'from_class_id',
        'to_class_id',
        'academic_year_id',
        'term_id',
        'status',
        'average_score',
        'overall_percentage',
        'overall_grade',
        'grade_point_average',
        'total_subjects',
        'subjects_passed',
        'subjects_failed',
        'class_rank',
        'total_students',
        'attendance_percentage',
        'failure_reasons',
        'processed_by',
        'processed_at',
        'is_final',
        'remarks',
    ];

    protected $casts = [
        'failure_reasons' => 'array',
        'processed_at' => 'datetime',
        'is_final' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fromClass()
    {
        return $this->belongsTo(ClassRoom::class, 'from_class_id');
    }

    public function toClass()
    {
        return $this->belongsTo(ClassRoom::class, 'to_class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
