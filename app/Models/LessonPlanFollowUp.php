<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPlanFollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_plan_id',
        'followed_up_by',
        'follow_up_date',
        'completion_status',
        'objectives_achieved',
        'challenges',
        'adjustments',
        'student_engagement',
        'remarks',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
    ];

    /* ── Status helpers ── */
    public static function completionStatusOptions(): array
    {
        return [
            'not_started'  => 'Not Started',
            'in_progress'  => 'In Progress',
            'completed'    => 'Completed',
            'skipped'      => 'Skipped',
        ];
    }

    public static function completionBadgeClass(string $status): string
    {
        return match ($status) {
            'not_started' => 'modern-badge-light',
            'in_progress' => 'modern-badge-info',
            'completed'   => 'modern-badge-success',
            'skipped'     => 'modern-badge-warning',
            default       => 'modern-badge-light',
        };
    }

    /* ── Relationships ── */
    public function lessonPlan()
    {
        return $this->belongsTo(LessonPlan::class);
    }

    public function followedUpBy()
    {
        return $this->belongsTo(User::class, 'followed_up_by');
    }
}
