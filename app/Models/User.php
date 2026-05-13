<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'address', 'profile_photo', 'is_active'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ── Existing Relationships ──────────────────────────────
    public function student() { return $this->hasOne(Student::class); }
    public function parent() { return $this->hasOne(ParentModel::class); }
    public function employeeAssets() { return $this->hasMany(EmployeeAsset::class, 'employee_id'); }
    public function leaves() { return $this->hasMany(Leave::class, 'employee_id'); }
    public function payrolls() { return $this->hasMany(Payroll::class, 'employee_id'); }
    public function approvedLeaves() { return $this->hasMany(Leave::class, 'approved_by'); }
    public function teacherProfile() { return $this->hasOne(Teacher::class, 'email', 'email'); }

    public function chatParticipants()
    {
        return $this->hasMany(ChatParticipant::class);
    }

    public function conversations()
    {
        return $this->belongsToMany(ChatConversation::class, 'chat_participants', 'user_id', 'conversation_id')
            ->withPivot('role', 'joined_at', 'last_read_at', 'is_muted')
            ->withTimestamps();
    }

    // ── Role & Permission Relationships ─────────────────────
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Get all permissions for this user (through roles).
     */
    public function getAllPermissions()
    {
        // Admin always has full access - no need to query
        if ($this->role === 'admin') {
            return collect();
        }

        try {
            static $cached = null;
            if ($cached !== null) {
                return $cached;
            }

            return $cached = $this->roles->flatMap(function ($role) {
                return $role->permissions;
            })->unique('id');
        } catch (\Throwable $e) {
            // Roles/permissions tables may not exist yet (before migration runs)
            return collect();
        }
    }

    // ── Legacy Role Helpers ─────────────────────────────────
    public function isAdmin() { return $this->role === 'admin'; }
    public function isTeacher() { return $this->role === 'teacher'; }

    // ── New Permission Helpers ──────────────────────────────
    /**
     * Check if user has a specific role (by name).
     */
    public function hasRole(string $role): bool
    {
        // Super admin bypass — always has access
        if ($this->role === 'admin') {
            return true;
        }
        try {
            return $this->roles()->where('name', $role)->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if user has a specific permission (by name).
     * Admins always pass.
     */
    public function hasPermission(string $permission): bool
    {
        // Super admin bypass — always has access
        if ($this->role === 'admin') {
            return true;
        }

        try {
            return $this->getAllPermissions()->contains('name', $permission);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        try {
            return $this->getAllPermissions()
                ->whereIn('name', $permissions)
                ->isNotEmpty();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        try {
            $userPerms = $this->getAllPermissions()->pluck('name')->toArray();
            return empty(array_diff($permissions, $userPerms));
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Role $role): void
    {
        try {
            $this->roles()->syncWithoutDetaching([$role->id]);
        } catch (\Throwable $e) {
            // Silently fail if roles table doesn't exist yet
        }
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(Role $role): void
    {
        try {
            $this->roles()->detach($role->id);
        } catch (\Throwable $e) {
            // Silently fail if roles table doesn't exist yet
        }
    }

    /**
     * Check if user can access a specific module.
     */
    public function canAccessModule(string $module): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        try {
            return $this->getAllPermissions()
                ->where('module', $module)
                ->isNotEmpty();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get the display role name (for sidebar).
     */
    public function getDisplayRoleAttribute(): string
    {
        try {
            $role = $this->roles()->first();
            if ($role) {
                return $role->display_name;
            }
        } catch (\Throwable $e) {
            // Roles table may not exist yet (before migration runs)
        }
        return ucfirst($this->role);
    }
}
