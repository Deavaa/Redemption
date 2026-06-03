<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkEntryDisallowal extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'section_id',
        'subject_id',
        'academic_year_id',
        'term_id',
        'disallowed_by',
        'reason',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function disallowedBy()
    {
        return $this->belongsTo(User::class, 'disallowed_by');
    }

    /**
     * Check if a teacher is disallowed from entering marks for a specific class/section/subject/AY/term.
     * Null values in the disallowal record act as wildcards.
     */
    public static function isDisallowed($teacherId, $classId, $sectionId, $subjectId, $academicYearId, $termId): bool
    {
        return static::where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->where(function ($q) use ($classId) {
                $q->whereNull('class_id')->orWhere('class_id', $classId);
            })
            ->where(function ($q) use ($sectionId) {
                $q->whereNull('section_id')->orWhere('section_id', $sectionId);
            })
            ->where(function ($q) use ($subjectId) {
                $q->whereNull('subject_id')->orWhere('subject_id', $subjectId);
            })
            ->where(function ($q) use ($academicYearId) {
                $q->whereNull('academic_year_id')->orWhere('academic_year_id', $academicYearId);
            })
            ->where(function ($q) use ($termId) {
                $q->whereNull('term_id')->orWhere('term_id', $termId);
            })
            ->exists();
    }

    /**
     * Get all active disallowals for a teacher in a given AY/term
     */
    public static function getForTeacher($teacherId, $academicYearId = null, $termId = null)
    {
        return static::with(['classRoom', 'section', 'subject', 'academicYear', 'term', 'disallowedBy'])
            ->where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->when($academicYearId, function ($q) use ($academicYearId) {
                $q->where(function ($subQ) use ($academicYearId) {
                    $subQ->whereNull('academic_year_id')->orWhere('academic_year_id', $academicYearId);
                });
            })
            ->when($termId, function ($q) use ($termId) {
                $q->where(function ($subQ) use ($termId) {
                    $subQ->whereNull('term_id')->orWhere('term_id', $termId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
