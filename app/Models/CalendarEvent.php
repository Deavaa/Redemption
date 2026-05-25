<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'title', 'description', 'category', 'color',
        'start_date', 'end_date', 'start_time', 'end_time',
        'is_all_day', 'is_announcement', 'is_approved', 'approved_by', 'approved_at',
        'academic_year_id', 'branch_id', 'scope', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_all_day' => 'boolean',
        'is_announcement' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: only approved events (for public/student/parent visibility).
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope: school-wide events.
     */
    public function scopeSchoolWide($query)
    {
        return $query->where('scope', 'school');
    }

    /**
     * Scope: branch-specific events.
     */
    public function scopeBranchScoped($query)
    {
        return $query->where('scope', 'branch');
    }

    /**
     * Scope: events visible to a specific branch (school-wide + branch-specific).
     */
    public function scopeVisibleToBranch($query, $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('scope', 'school')
              ->orWhere(function ($q2) use ($branchId) {
                  $q2->where('scope', 'branch')->where('branch_id', $branchId);
              });
        });
    }

    public static function categoryColors()
    {
        return [
            'holiday'  => '#ef4444',
            'exam'     => '#f59e0b',
            'event'    => '#10b981',
            'meeting'  => '#6366f1',
            'deadline' => '#ec4899',
            'other'    => '#6b7280',
        ];
    }

    public static function categoryList()
    {
        return [
            'holiday'  => 'Holiday',
            'exam'     => 'Exam',
            'event'    => 'Event',
            'meeting'  => 'Meeting',
            'deadline' => 'Deadline',
            'other'    => 'Other',
        ];
    }
}
