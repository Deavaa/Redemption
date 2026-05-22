<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'student_id',
        'role',
        'join_date',
        'status',
    ];

    protected $casts = [
        'join_date' => 'date',
    ];

    /* ── Relationships ── */

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /* ── Scopes ── */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /* ── Helper Methods ── */

    public static function roleOptions(): array
    {
        return [
            'member' => 'Member',
            'vice_president' => 'Vice President',
            'president' => 'President',
            'secretary' => 'Secretary',
        ];
    }

    public static function roleBadgeClass(string $role): string
    {
        return match ($role) {
            'president' => 'modern-badge-info',
            'vice_president' => 'modern-badge-purple',
            'secretary' => 'modern-badge-warning',
            'member' => 'modern-badge-light',
            default => 'modern-badge-light',
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return self::roleOptions()[$this->role] ?? ucfirst($this->role);
    }
}
