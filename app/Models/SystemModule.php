<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'group',
        'is_enabled',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    public static function isEnabled($key)
    {
        $module = static::where('key', $key)->first();
        // If module not in table, default to enabled
        return $module ? $module->is_enabled : true;
    }

    public static function getGroups()
    {
        return [
            'academic' => 'Academic',
            'assessment' => 'Assessment & Exams',
            'people' => 'People Management',
            'finance' => 'Finance',
            'communication' => 'Communication',
            'documents' => 'Documents & Reports',
            'library' => 'Library',
            'website' => 'Website & Content',
            'admin' => 'Administration',
        ];
    }

    // Default modules to seed
    public static function defaultModules()
    {
        return [
            ['key' => 'lesson_plans', 'name' => 'Lesson Plans', 'group' => 'academic', 'sort_order' => 1],
            ['key' => 'exam_questions', 'name' => 'Exam Question Review', 'group' => 'assessment', 'sort_order' => 2],
            ['key' => 'teacher_evaluation', 'name' => 'Teacher Evaluation', 'group' => 'assessment', 'sort_order' => 3],
            ['key' => 'attendance', 'name' => 'Attendance', 'group' => 'academic', 'sort_order' => 4],
            ['key' => 'promotion', 'name' => 'Promotion & Detention', 'group' => 'assessment', 'sort_order' => 5],
            ['key' => 'mark_entry', 'name' => 'Mark Entry', 'group' => 'assessment', 'sort_order' => 6],
            ['key' => 'news', 'name' => 'News & Announcements', 'group' => 'website', 'sort_order' => 7],
            ['key' => 'chat', 'name' => 'Internal Chat', 'group' => 'communication', 'sort_order' => 8],
            ['key' => 'telegram', 'name' => 'Telegram Integration', 'group' => 'communication', 'sort_order' => 9],
            ['key' => 'library', 'name' => 'Library Management', 'group' => 'library', 'sort_order' => 10],
            ['key' => 'stock', 'name' => 'Stock Management', 'group' => 'admin', 'sort_order' => 11],
            ['key' => 'training', 'name' => 'Staff Training', 'group' => 'admin', 'sort_order' => 12],
            ['key' => 'report_exchange', 'name' => 'Report Exchange', 'group' => 'documents', 'sort_order' => 13],
            ['key' => 'certificates', 'name' => 'Certificates & ID Cards', 'group' => 'documents', 'sort_order' => 14],
            ['key' => 'performance_analysis', 'name' => 'Performance Analysis', 'group' => 'assessment', 'sort_order' => 15],
        ];
    }
}
