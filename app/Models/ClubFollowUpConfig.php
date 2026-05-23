<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubFollowUpConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'branch_id',
        'name',
        'follow_up_type',
        'description',
        'days_after_activity',
        'checklist_items',
        'rating_criteria',
        'is_auto_reminder',
        'reminder_days_before',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'checklist_items' => 'array',
        'rating_criteria' => 'array',
        'is_auto_reminder' => 'boolean',
        'is_active' => 'boolean',
        'days_after_activity' => 'integer',
        'reminder_days_before' => 'integer',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForClub($query, int $clubId)
    {
        return $query->where('club_id', $clubId);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public static function followUpTypeOptions(): array
    {
        return [
            'regular' => 'Regular Check-in',
            'post_event' => 'Post-Event Review',
            'monthly' => 'Monthly Evaluation',
            'quarterly' => 'Quarterly Assessment',
            'annual' => 'Annual Review',
        ];
    }

    public static function defaultChecklistItems(): array
    {
        return [
            'Was the activity completed as planned?',
            'Were all objectives met?',
            'Were there any challenges?',
            'Were all participants engaged?',
            'Were resources sufficient?',
            'Any recommendations for improvement?',
        ];
    }

    public static function defaultRatingCriteria(): array
    {
        return [
            'participation_rate' => 'Participation Rate (1-5)',
            'objective_achievement' => 'Objective Achievement (1-5)',
            'member_engagement' => 'Member Engagement (1-5)',
            'resource_utilization' => 'Resource Utilization (1-5)',
            'overall_satisfaction' => 'Overall Satisfaction (1-5)',
        ];
    }
}
