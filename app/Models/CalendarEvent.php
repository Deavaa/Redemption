<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'title', 'description', 'category', 'color',
        'start_date', 'end_date', 'start_time', 'end_time',
        'is_all_day', 'is_announcement', 'academic_year_id', 'branch_id', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_all_day' => 'boolean',
        'is_announcement' => 'boolean',
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
