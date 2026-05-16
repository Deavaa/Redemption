<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'image_path',
        'is_active',
        'show_until',
        'priority',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_until' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Get news that should be shown on the website.
     * Active news that are either:
     * - Less than 2 days old, OR
     * - Have show_until that hasn't expired
     */
    public function scopeVisibleOnWebsite($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->where('created_at', '>=', now()->subDays(2))
                  ->orWhereNull('show_until')
                  ->orWhere('show_until', '>=', now());
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc');
    }
}
