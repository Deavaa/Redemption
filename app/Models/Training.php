<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'category',
        'provider',
        'facilitator',
        'venue',
        'start_date',
        'end_date',
        'duration_hours',
        'target_audience',
        'cost',
        'budget_source',
        'max_participants',
        'status',
        'objectives',
        'outcomes',
        'certificate_template',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'cost' => 'decimal:2',
    ];

    /* ── Relationships ── */

    public function participants()
    {
        return $this->hasMany(TrainingParticipant::class);
    }

    public function employees()
    {
        return $this->belongsToMany(User::class, 'training_participants', 'training_id', 'employee_id')
            ->withPivot([
                'status', 'completion_date', 'score', 'grade',
                'certificate_number', 'certificate_issued', 'feedback',
                'remarks', 'nominated_by',
            ])
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ── Scopes ── */

    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /* ── Accessors ── */

    public function getTypeLabelAttribute()
    {
        return match ($this->type) {
            'workshop' => 'Workshop',
            'seminar' => 'Seminar',
            'online_course' => 'Online Course',
            'on_the_job' => 'On-the-Job Training',
            'certification' => 'Certification',
            'conference' => 'Conference',
            'mentorship' => 'Mentorship',
            'induction' => 'Induction',
            default => ucfirst($this->type),
        };
    }

    public function getCategoryLabelAttribute()
    {
        return match ($this->category) {
            'pedagogical' => 'Pedagogical',
            'administrative' => 'Administrative',
            'technical' => 'Technical / ICT',
            'leadership' => 'Leadership',
            'safety' => 'Safety & Compliance',
            'curriculum' => 'Curriculum',
            'pastoral' => 'Pastoral Care',
            'general' => 'General',
            default => ucfirst($this->category),
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'planned' => '<span class="badge bg-info">Planned</span>',
            'ongoing' => '<span class="badge bg-warning text-dark">Ongoing</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getEnrolledCountAttribute()
    {
        return $this->participants()->whereIn('status', ['invited', 'enrolled', 'attended', 'completed'])->count();
    }

    public function getCompletedCountAttribute()
    {
        return $this->participants()->where('status', 'completed')->count();
    }

    public function getIsFullAttribute()
    {
        return $this->max_participants > 0 && $this->enrolled_count >= $this->max_participants;
    }
}
