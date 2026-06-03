<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceDelegation extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'section_id',
        'delegated_to_teacher_id',
        'delegated_by_user_id',
        'date',
        'reason',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function classRoom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function delegatedTeacher()
    {
        return $this->belongsTo(Teacher::class, 'delegated_to_teacher_id');
    }

    public function delegatedBy()
    {
        return $this->belongsTo(User::class, 'delegated_by_user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    // Helper: Check if a teacher can take attendance for a class on a date
    public static function canTakeAttendance($teacherId, $classId, $sectionId = null, $date = null): bool
    {
        $date = $date ?? now()->toDateString();

        // Check if the teacher is a homeroom teacher for this class
        $isHomeroom = Classroom::where('id', $classId)
            ->where('teacher_id', $teacherId)
            ->exists();

        if ($isHomeroom) {
            return true;
        }

        // Check if the teacher is a homeroom for a section in this class
        if ($sectionId) {
            $isSectionHomeroom = Section::where('id', $sectionId)
                ->where('class_id', $classId)
                ->where('teacher_id', $teacherId)
                ->exists();

            if ($isSectionHomeroom) {
                return true;
            }
        } else {
            // Check if teacher is homeroom for ANY section in this class
            $isSectionHomeroom = Section::where('class_id', $classId)
                ->where('teacher_id', $teacherId)
                ->exists();

            if ($isSectionHomeroom) {
                return true;
            }
        }

        // Check if there's an active delegation for this teacher/class/date
        $hasDelegation = self::where('class_id', $classId)
            ->where('delegated_to_teacher_id', $teacherId)
            ->where('date', $date)
            ->where('is_active', true)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->when(!$sectionId, fn($q) => $q->whereNull('section_id'))
            ->exists();

        return $hasDelegation;
    }

    // Helper: Get classes a teacher can take attendance for on a given date
    public static function getAssignableClasses($teacherId, $date = null): array
    {
        $date = $date ?? now()->toDateString();

        // Classes where teacher is the class homeroom
        $classHomeroom = Classroom::where('teacher_id', $teacherId)->pluck('id')->toArray();

        // Classes where teacher is a section homeroom
        $sectionHomeroom = Section::where('teacher_id', $teacherId)
            ->pluck('class_id')
            ->unique()
            ->toArray();

        // Classes delegated to this teacher for today
        $delegated = self::where('delegated_to_teacher_id', $teacherId)
            ->where('date', $date)
            ->where('is_active', true)
            ->pluck('class_id')
            ->toArray();

        return array_unique(array_merge($classHomeroom, $sectionHomeroom, $delegated));
    }
}
