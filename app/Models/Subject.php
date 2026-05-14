<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\TeacherAssignment;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'type', 'priority', 'is_active', 'description'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Valid subject types.
     */
    public static function typeOptions(): array
    {
        return [
            'compulsory' => 'Compulsory',
            'elective'   => 'Elective',
            'optional'   => 'Optional',
        ];
    }

    /**
     * Scope: order by priority (for reports and certificates).
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority')->orderBy('name');
    }

    /**
     * Scope: only active subjects.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function assignments()
    {
        return $this->hasMany(\App\Models\TeacherAssignment::class, 'subject_id');
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }
}
