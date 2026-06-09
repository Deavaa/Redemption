<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SliderAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'icon',
        'type',
        'bg_color',
        'text_color',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get active alerts ordered by sort_order
     */
    public static function getActive()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get predefined alert type styles
     */
    public static function getTypeStyles(): array
    {
        return [
            'info'    => ['bg' => '#059669', 'text' => '#ffffff', 'icon' => 'fa-info-circle'],
            'success' => ['bg' => '#10b981', 'text' => '#ffffff', 'icon' => 'fa-check-circle'],
            'warning' => ['bg' => '#f59e0b', 'text' => '#ffffff', 'icon' => 'fa-exclamation-triangle'],
            'danger'  => ['bg' => '#ef4444', 'text' => '#ffffff', 'icon' => 'fa-exclamation-circle'],
            'primary' => ['bg' => '#4361ee', 'text' => '#ffffff', 'icon' => 'fa-bullhorn'],
        ];
    }
}
