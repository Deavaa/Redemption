<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VideoLibrary extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'video_library';

    protected $fillable = [
        'title', 'description', 'youtube_url', 'youtube_video_id',
        'channel_name', 'channel_url', 'thumbnail', 'category',
        'video_type', 'access_level', 'branch_id', 'uploaded_by',
        'is_active', 'show_on_website', 'view_count', 'duration_seconds',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_on_website' => 'boolean',
        'view_count' => 'integer',
        'duration_seconds' => 'integer',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $term)
    {
        if (empty($term)) return $query;
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('channel_name', 'LIKE', "%{$term}%")
              ->orWhere('category', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    public function scopeForUser($query, $user)
    {
        if ($user->role === 'admin') {
            return $query;
        }

        if ($user->hasRole('branch_principal') || $user->hasRole('librarian')) {
            return $query->where(function ($q) use ($user) {
                $q->whereNull('branch_id')
                  ->orWhere('branch_id', $user->teacherProfile?->branch_id);
            });
        }

        return $query->active()->where(function ($q) use ($user) {
            $q->where('access_level', 'all')
              ->orWhere('access_level', $user->role);
        });
    }

    // Helpers
    public static function extractYoutubeVideoId(string $url): ?string
    {
        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/v\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/',
            '/^([a-zA-Z0-9_-]{11})$/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public static function extractChannelId(string $url): ?string
    {
        // Extract channel ID from URL like https://www.youtube.com/channel/UCxxxxxx
        if (preg_match('/youtube\.com\/channel\/(UC[a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        // Extract custom handle like @channelname
        if (preg_match('/youtube\.com\/(@[\w.-]+)/', $url, $matches)) {
            return $matches[1];
        }
        // Extract /c/channelname format
        if (preg_match('/youtube\.com\/c\/([\w.-]+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function getEmbedUrl(): string
    {
        if ($this->youtube_video_id) {
            return "https://www.youtube.com/embed/{$this->youtube_video_id}";
        }
        return '';
    }

    public function getThumbnailUrl(): string
    {
        if ($this->thumbnail) {
            return $this->thumbnail;
        }
        if ($this->youtube_video_id) {
            return "https://img.youtube.com/vi/{$this->youtube_video_id}/hqdefault.jpg";
        }
        return '';
    }

    public function getFormattedDuration(): string
    {
        $seconds = $this->duration_seconds ?? 0;
        if ($seconds <= 0) return '';
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }
        return sprintf('%d:%02d', $minutes, $secs);
    }

    public function canManage($user): bool
    {
        if ($user->role === 'admin') return true;
        if ($user->hasRole('librarian')) return true;
        if ($user->hasRole('branch_principal')) return true;
        if ($user->hasRole('general_manager')) return true;
        if ($user->hasRole('teacher')) return true;
        return false;
    }

    public function canView($user): bool
    {
        if (!$this->is_active && $user->role !== 'admin') return false;
        if ($user->role === 'admin') return true;
        return $this->access_level === 'all' || $this->access_level === $user->role;
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    // Scope for website-visible videos
    public function scopeForWebsite($query)
    {
        return $query->where('is_active', true)
                     ->where('show_on_website', true)
                     ->where('access_level', 'all');
    }
}
