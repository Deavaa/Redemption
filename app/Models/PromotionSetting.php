<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'minimum_average_for_promotion',
        'maximum_subjects_to_fail',
        'minimum_subject_pass_mark',
        'consider_attendance',
        'minimum_attendance_percentage',
        'consider_behavior',
        'consider_conduct',
        'minimum_conduct_score',
        'auto_promote_if_pass_all',
        'allow_conditional_promotion',
        'conditional_promotion_min_average',
        'conditional_promotion_max_failures',
        'is_active',
        'description',
    ];

    protected $casts = [
        'consider_attendance' => 'boolean',
        'consider_behavior' => 'boolean',
        'consider_conduct' => 'boolean',
        'auto_promote_if_pass_all' => 'boolean',
        'allow_conditional_promotion' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the currently active promotion setting
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
