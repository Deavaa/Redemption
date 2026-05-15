<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id',
        'employee_id',
        'status',
        'completion_date',
        'score',
        'grade',
        'certificate_number',
        'certificate_issued',
        'feedback',
        'remarks',
        'nominated_by',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'score' => 'decimal:2',
        'certificate_issued' => 'boolean',
    ];

    /* ── Relationships ── */

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function nominator()
    {
        return $this->belongsTo(User::class, 'nominated_by');
    }

    /* ── Accessors ── */

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'invited' => '<span class="badge bg-secondary">Invited</span>',
            'enrolled' => '<span class="badge bg-info">Enrolled</span>',
            'attended' => '<span class="badge bg-warning text-dark">Attended</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'absent' => '<span class="badge bg-danger">Absent</span>',
            'dropped' => '<span class="badge bg-dark">Dropped</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'invited' => 'Invited',
            'enrolled' => 'Enrolled',
            'attended' => 'Attended',
            'completed' => 'Completed',
            'absent' => 'Absent',
            'dropped' => 'Dropped',
            default => ucfirst($this->status),
        };
    }
}
