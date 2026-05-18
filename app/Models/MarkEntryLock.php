<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkEntryLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'academic_year_id',
        'term_id',
        'is_locked',
        'locked_by',
        'locked_at',
        'unlocked_by',
        'unlocked_at',
        'lock_reason',
        'unlock_reason',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'unlocked_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function unlockedBy()
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }

    /**
     * Check if mark entry is locked for a given branch/AY/term combination
     */
    public static function isLocked($branchId, $academicYearId, $termId): bool
    {
        $lock = static::where('branch_id', $branchId)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->first();

        return $lock ? $lock->is_locked : false;
    }

    /**
     * Get or create a lock record for a branch/AY/term
     */
    public static function getOrCreate($branchId, $academicYearId, $termId): self
    {
        return static::firstOrCreate(
            [
                'branch_id' => $branchId,
                'academic_year_id' => $academicYearId,
                'term_id' => $termId,
            ],
            ['is_locked' => false]
        );
    }
}
