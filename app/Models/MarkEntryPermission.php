<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkEntryPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'student_id',
        'subject_id',
        'academic_year_id',
        'term_id',
        'granted_by',
        'reason',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
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

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Check if a teacher has special permission to edit a specific student's mark
     */
    public static function hasPermission($teacherId, $studentId, $subjectId, $academicYearId, $termId): bool
    {
        return static::where('teacher_id', $teacherId)
            ->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Get all active permissions for a teacher in a given AY/term
     */
    public static function getForTeacher($teacherId, $academicYearId, $termId)
    {
        return static::with(['student', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get();
    }

    /**
     * Check if a teacher can edit a specific student-subject mark,
     * considering both assignment-based access and special permission.
     */
    public static function canEditMark($teacherId, $studentId, $subjectId, $academicYearId, $termId, $branchId = null): bool
    {
        // First check: is the mark entry locked?
        if ($branchId && MarkEntryLock::isLocked($branchId, $academicYearId, $termId)) {
            // Mark entry is locked - only special permission allows editing
            return static::hasPermission($teacherId, $studentId, $subjectId, $academicYearId, $termId);
        }

        // Mark entry is not locked - teacher with assignment can edit normally
        return true;
    }
}
