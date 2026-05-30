<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_score',
        'max_score',
        'grade',
        'grade_point',
        'description',
        'is_passing',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_passing' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the grade for a given score
     */
    public static function getGrade(float $score): ?self
    {
        return static::where('is_active', true)
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Seed default grade scales if none exist
     */
    public static function seedDefaults(): void
    {
        if (static::count() > 0) return;

        $scales = [
            ['min_score' => 80, 'max_score' => 100, 'grade' => 'A', 'grade_point' => 4.00, 'description' => 'Excellent', 'is_passing' => true, 'sort_order' => 1],
            ['min_score' => 60, 'max_score' => 79.99, 'grade' => 'B', 'grade_point' => 3.00, 'description' => 'Good', 'is_passing' => true, 'sort_order' => 2],
            ['min_score' => 50, 'max_score' => 59.99, 'grade' => 'C', 'grade_point' => 2.00, 'description' => 'Average', 'is_passing' => true, 'sort_order' => 3],
            ['min_score' => 40, 'max_score' => 49.99, 'grade' => 'D', 'grade_point' => 1.00, 'description' => 'Below Average', 'is_passing' => false, 'sort_order' => 4],
            ['min_score' => 0.01, 'max_score' => 39.99, 'grade' => 'F', 'grade_point' => 0.00, 'description' => 'Fail', 'is_passing' => false, 'sort_order' => 5],
            ['min_score' => 0, 'max_score' => 0, 'grade' => 'I', 'grade_point' => 0.00, 'description' => 'Incomplete', 'is_passing' => false, 'sort_order' => 6],
        ];

        foreach ($scales as $scale) {
            static::create($scale);
        }
    }
}
