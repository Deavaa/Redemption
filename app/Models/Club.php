<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'branch_id',
        'leader_id',
        'created_by',
        'meeting_schedule',
        'meeting_location',
        'max_members',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_members' => 'integer',
    ];

    /* ── Relationships ── */

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function leader()
    {
        return $this->belongsTo(Teacher::class, 'leader_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->hasMany(ClubMember::class);
    }

    public function activeMembers()
    {
        return $this->hasMany(ClubMember::class)->where('status', 'active');
    }

    public function activities()
    {
        return $this->hasMany(ClubActivity::class);
    }

    /* ── Scopes ── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /* ── Helper Methods ── */

    public static function categoryOptions(): array
    {
        return [
            'academic' => 'Academic',
            'sports' => 'Sports',
            'arts' => 'Arts',
            'community_service' => 'Community Service',
            'technology' => 'Technology',
            'cultural' => 'Cultural',
            'debate' => 'Debate & Public Speaking',
            'science' => 'Science',
            'music' => 'Music',
            'other' => 'Other',
        ];
    }

    public static function categoryBadgeClass(string $category): string
    {
        return match ($category) {
            'academic' => 'modern-badge-info',
            'sports' => 'modern-badge-success',
            'arts' => 'modern-badge-purple',
            'community_service' => 'modern-badge-orange',
            'technology' => 'modern-badge-cyan',
            'cultural' => 'modern-badge-warning',
            'debate' => 'modern-badge-danger',
            'science' => 'modern-badge-info',
            'music' => 'modern-badge-purple',
            default => 'modern-badge-light',
        };
    }

    public function getMemberCountAttribute(): int
    {
        return $this->activeMembers()->count();
    }

    public function getActivityCountAttribute(): int
    {
        return $this->activities()->count();
    }

    public function getIsFullAttribute(): bool
    {
        return $this->max_members > 0 && $this->member_count >= $this->max_members;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categoryOptions()[$this->category] ?? ucfirst($this->category ?? '');
    }
}
