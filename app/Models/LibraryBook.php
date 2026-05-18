<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class LibraryBook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'author', 'isbn', 'publisher', 'category', 'description',
        'file_path', 'file_name', 'file_type', 'file_size', 'cover_image',
        'branch_id', 'uploaded_by', 'access_level', 'is_active', 'read_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
        'read_count' => 'integer',
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
              ->orWhere('author', 'LIKE', "%{$term}%")
              ->orWhere('isbn', 'LIKE', "%{$term}%")
              ->orWhere('category', 'LIKE', "%{$term}%")
              ->orWhere('publisher', 'LIKE', "%{$term}%");
        });
    }

    public function scopeForUser($query, $user)
    {
        // Admin sees everything
        if ($user->role === 'admin') {
            return $query;
        }

        // Branch principal sees their branch books + unassigned books
        if ($user->hasRole('branch_principal') || $user->hasRole('librarian')) {
            return $query->where(function ($q) use ($user) {
                $q->whereNull('branch_id')
                  ->orWhere('branch_id', $user->teacherProfile?->branch_id);
            });
        }

        // Teachers, students, staff, parents see active books based on access level
        return $query->active()->where(function ($q) use ($user) {
            $q->where('access_level', 'all')
              ->orWhere('access_level', $user->role);
        });
    }

    // Helpers
    public function getFileUrl(): string
    {
        // Book files should always be served through the authenticated serve route
        // to prevent direct download access
        try {
            return route('admin.library.serve', $this->id);
        } catch (\Throwable $e) {}
        return '#';
    }

    public function getCoverUrl(): string
    {
        if (empty($this->cover_image)) {
            return '';
        }
        if (file_exists(public_path('storage/' . $this->cover_image))) {
            return asset('storage/' . $this->cover_image);
        }
        try {
            if (Storage::disk('public')->exists($this->cover_image)) {
                return Storage::disk('public')->url($this->cover_image);
            }
        } catch (\Throwable $e) {}
        return asset('storage/' . $this->cover_image);
    }

    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    public function canUpload($user): bool
    {
        // Admin can always upload
        if ($user->role === 'admin') return true;

        // Users with librarian or branch_principal role can upload
        if ($user->hasRole('librarian')) return true;
        if ($user->hasRole('branch_principal')) return true;

        return false;
    }

    public function canRead($user): bool
    {
        if (!$this->is_active) return false;
        if ($user->role === 'admin') return true;

        // Access level check
        return $this->access_level === 'all' || $this->access_level === $user->role;
    }

    public function incrementReadCount(): void
    {
        $this->increment('read_count');
    }
}
