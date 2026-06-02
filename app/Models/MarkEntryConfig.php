<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MarkEntryConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'label', 'value', 'type', 'category',
        'description', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Cache key for all config
     */
    public static function cacheKey(): string
    {
        return 'mark_entry_config_all';
    }

    /**
     * Get a config value by name
     */
    public static function getValue(string $name, $default = null)
    {
        $all = static::getAllCached();
        $item = $all->firstWhere('name', $name);
        if (!$item) return $default;

        return static::castValue($item->value, $item->type);
    }

    /**
     * Get all config cached
     */
    public static function getAllCached()
    {
        return Cache::remember(static::cacheKey(), 3600, function () {
            return static::where('is_active', true)->orderBy('sort_order')->get();
        });
    }

    /**
     * Cast a value based on its type
     */
    public static function castValue(string $value, string $type)
    {
        return match ($type) {
            'number'  => is_float(strpos($value, '.')) ? (float) $value : (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }

    /**
     * Clear the config cache
     */
    public static function clearCache(): void
    {
        Cache::forget(static::cacheKey());
    }

    /**
     * Get mark fields configuration from DB (with hardcoded fallback)
     */
    public static function getMarkFields(): array
    {
        return static::getValue('mark_fields', static::defaultMarkFields());
    }

    /**
     * Get CA weight (out of 100)
     */
    public static function getCaWeight(): float
    {
        return (float) static::getValue('ca_weight', 30);
    }

    /**
     * Get exam weight (out of 100)
     */
    public static function getExamWeight(): float
    {
        return (float) static::getValue('exam_weight', 70);
    }

    /**
     * Get rounding precision
     */
    public static function getRoundingPrecision(): int
    {
        return (int) static::getValue('rounding_precision', 2);
    }

    /**
     * Get grade scale as array from DB
     */
    public static function getGradeScale(): array
    {
        return static::getValue('grade_scale', static::defaultGradeScale());
    }

    /**
     * Default mark fields when no config exists in DB
     */
    public static function defaultMarkFields(): array
    {
        return [
            ['col' => 'ca1', 'max' => 5, 'label' => 'CA1', 'category' => 'ca'],
            ['col' => 'ca2', 'max' => 5, 'label' => 'CA2', 'category' => 'ca'],
            ['col' => 'ca3', 'max' => 5, 'label' => 'CA3', 'category' => 'ca'],
            ['col' => 'ca4', 'max' => 5, 'label' => 'CA4', 'category' => 'ca'],
            ['col' => 'ca5', 'max' => 5, 'label' => 'CA5', 'category' => 'ca'],
            ['col' => 'ca6', 'max' => 5, 'label' => 'CA6', 'category' => 'ca'],
            ['col' => 'ca7', 'max' => 5, 'label' => 'CA7', 'category' => 'ca'],
            ['col' => 'ca8', 'max' => 5, 'label' => 'CA8', 'category' => 'ca'],
            ['col' => 'ca9', 'max' => 5, 'label' => 'CA9', 'category' => 'ca'],
            ['col' => 'ca10', 'max' => 5, 'label' => 'CA10', 'category' => 'ca'],
            ['col' => 'conduct', 'max' => 5, 'label' => 'Conduct', 'category' => 'extra_ca'],
            ['col' => 'handwriting', 'max' => 5, 'label' => 'Handwriting', 'category' => 'extra_ca'],
            ['col' => 'creativity', 'max' => 10, 'label' => 'Creativity', 'category' => 'extra_ca'],
            ['col' => 'test1', 'max' => 10, 'label' => 'Test 1', 'category' => 'exam'],
            ['col' => 'test2', 'max' => 10, 'label' => 'Test 2', 'category' => 'exam'],
            ['col' => 'mid_term', 'max' => 20, 'label' => 'Mid-Term', 'category' => 'exam'],
            ['col' => 'final_exam', 'max' => 30, 'label' => 'Final Exam', 'category' => 'exam'],
        ];
    }

    /**
     * Default grade scale when no config exists in DB
     */
    public static function defaultGradeScale(): array
    {
        return [
            ['min' => 80, 'grade' => 'A', 'point' => 4.00, 'label' => 'Excellent', 'is_passing' => true],
            ['min' => 60, 'grade' => 'B', 'point' => 3.00, 'label' => 'Good', 'is_passing' => true],
            ['min' => 50, 'grade' => 'C', 'point' => 2.00, 'label' => 'Average', 'is_passing' => true],
            ['min' => 40, 'grade' => 'D', 'point' => 1.00, 'label' => 'Below Average', 'is_passing' => false],
            ['min' => 0.01, 'grade' => 'F', 'point' => 0.00, 'label' => 'Fail', 'is_passing' => false],
            ['min' => 0, 'grade' => 'I', 'point' => 0.00, 'label' => 'Incomplete', 'is_passing' => false],
        ];
    }

    /**
     * Seed default config into DB if not exists
     */
    public static function seedDefaults(): void
    {
        $defaults = [
            // General settings
            ['name' => 'ca_weight', 'label' => 'CA Weight (%)', 'value' => '30', 'type' => 'number', 'category' => 'general', 'description' => 'Percentage weight of CA marks in grand total (out of 100)', 'sort_order' => 1],
            ['name' => 'exam_weight', 'label' => 'Exam Weight (%)', 'value' => '70', 'type' => 'number', 'category' => 'general', 'description' => 'Percentage weight of Exam marks in grand total (out of 100)', 'sort_order' => 2],
            ['name' => 'rounding_precision', 'label' => 'Rounding Precision (decimal places)', 'value' => '2', 'type' => 'number', 'category' => 'general', 'description' => 'Number of decimal places for mark calculations', 'sort_order' => 3],
            ['name' => 'input_precision', 'label' => 'Input Precision (decimal places)', 'value' => '1', 'type' => 'number', 'category' => 'general', 'description' => 'Number of decimal places allowed in mark input fields', 'sort_order' => 4],
            ['name' => 'pass_mark', 'label' => 'Pass Mark', 'value' => '50', 'type' => 'number', 'category' => 'general', 'description' => 'Minimum mark to pass (C and above)', 'sort_order' => 5],

            // Mark fields configuration (JSON)
            ['name' => 'mark_fields', 'label' => 'Mark Fields', 'value' => json_encode(static::defaultMarkFields()), 'type' => 'json', 'category' => 'fields', 'description' => 'Define each mark entry field: column name, max value, label, and category (ca, extra_ca, exam)', 'sort_order' => 10],

            // Grade scale (JSON)
            ['name' => 'grade_scale', 'label' => 'Grade Scale', 'value' => json_encode(static::defaultGradeScale()), 'type' => 'json', 'category' => 'grading', 'description' => 'Define grade thresholds: minimum score, grade letter, grade point, and pass/fail status', 'sort_order' => 20],
        ];

        foreach ($defaults as $config) {
            static::updateOrCreate(
                ['name' => $config['name']],
                $config
            );
        }

        static::clearCache();
    }

    /**
     * Get all config as a key-value array for frontend
     */
    public static function getFrontendConfig(): array
    {
        $markFields = static::getMarkFields();
        $gradeScale = static::getGradeScale();
        $caWeight = static::getCaWeight();
        $examWeight = static::getExamWeight();
        $roundingPrecision = static::getRoundingPrecision();
        $inputPrecision = (int) static::getValue('input_precision', 1);
        $passMark = (float) static::getValue('pass_mark', 50);

        // Calculate CA raw total from mark fields
        $caRawTotal = 0;
        $caKeys = [];
        $extraCaKeys = [];
        $examKeys = [];

        foreach ($markFields as $field) {
            $cat = $field['category'] ?? 'ca';
            if ($cat === 'ca') {
                $caRawTotal += $field['max'];
                $caKeys[] = $field['col'];
            } elseif ($cat === 'extra_ca') {
                $caRawTotal += $field['max'];
                $extraCaKeys[] = $field['col'];
            } elseif ($cat === 'exam') {
                $examKeys[] = $field['col'];
            }
        }

        // Sort grade scale descending by min score for calcGrade function
        usort($gradeScale, fn($a, $b) => $b['min'] <=> $a['min']);

        return [
            'mark_fields' => $markFields,
            'grade_scale' => $gradeScale,
            'ca_weight' => $caWeight,
            'exam_weight' => $examWeight,
            'ca_raw_total' => $caRawTotal,
            'rounding_precision' => $roundingPrecision,
            'input_precision' => $inputPrecision,
            'pass_mark' => $passMark,
            'ca_keys' => $caKeys,
            'extra_ca_keys' => $extraCaKeys,
            'exam_keys' => $examKeys,
        ];
    }

    protected static function booted()
    {
        // Clear cache on any change
        static::saved(function () { static::clearCache(); });
        static::deleted(function () { static::clearCache(); });
    }
}
