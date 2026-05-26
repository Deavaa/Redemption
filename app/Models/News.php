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
        'is_approved',
        'approved_by',
        'approved_at',
        'show_until',
        'priority',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'show_until' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: Get news that should be shown on the website.
     * Active + approved news. If show_until is set, respect it; otherwise always show.
     */
    public function scopeVisibleOnWebsite($query)
    {
        return $query->where('is_active', true)
            ->where('is_approved', true)
            ->where(function ($q) {
                $q->whereNull('show_until')
                  ->orWhere('show_until', '>=', now());
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc');
    }
}
