<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'head_user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function headUser()
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'department_id');
    }

    public function examQuestions()
    {
        return $this->hasManyThrough(ExamQuestion::class, Teacher::class, 'department_id', 'teacher_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTypeLabelAttribute()
    {
        return match ($this->type) {
            'academic' => 'Academic',
            'administrative' => 'Administrative',
            'support' => 'Support Staff',
            default => ucfirst($this->type ?? ''),
        };
    }

    public function evaluations()
    {
        return $this->hasManyThrough(TeacherEvaluation::class, Teacher::class, 'department_id', 'teacher_id');
    }
}
