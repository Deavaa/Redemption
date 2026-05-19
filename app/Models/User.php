<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'id_number', 'password', 'role', 'branch_id', 'phone', 'address', 'profile_photo', 'is_active', 'security_question', 'security_answer', 'gender', 'qualification'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ── Branch Relationship ────────────────────────────────
    public function branch() { return $this->belongsTo(Branch::class); }

    // ── Existing Relationships ──────────────────────────────
    public function student() { return $this->hasOne(Student::class); }
    public function parent() { return $this->hasOne(ParentModel::class); }
    public function employeeAssets() { return $this->hasMany(EmployeeAsset::class, 'employee_id'); }
    public function leaves() { return $this->hasMany(Leave::class, 'employee_id'); }
    public function payrolls() { return $this->hasMany(Payroll::class, 'employee_id'); }
    public function approvedLeaves() { return $this->hasMany(Leave::class, 'approved_by'); }
    public function teacherProfile() { return $this->hasOne(Teacher::class, 'email', 'email'); }
    public function trainings() { return $this->belongsToMany(Training::class, 'training_participants', 'employee_id', 'training_id')->withPivot(['status','completion_date','score','grade','certificate_number','certificate_issued','feedback','remarks','nominated_by'])->withTimestamps(); }
    public function trainingParticipants() { return $this->hasMany(TrainingParticipant::class, 'employee_id'); }

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
     * Direct user-permission overrides (bypasses roles).
     */
    public function directPermissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    /**
     * Get all permissions for this user (through roles + direct).
     * Admin always has full access.
     */
    public function getAllPermissions()
    {
        // Admin always has full access - no need to query
        if ($this->role === 'admin') {
            return Permission::all();
        }

        try {
            // Instance-level cache (not static — avoids cross-user leak)
            if (isset($this->_permissionsCache)) {
                return $this->_permissionsCache;
            }

            // Permissions from all assigned roles
            $rolePermissions = $this->roles->flatMap(function ($role) {
                return $role->permissions;
            });

            // Direct user-level permission overrides
            $directPermissions = $this->directPermissions ?? collect();

            return $this->_permissionsCache = $rolePermissions
                ->merge($directPermissions)
                ->unique('id');
        } catch (\Throwable $e) {
            // Tables may not exist yet (before migration runs)
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
            unset($this->_permissionsCache);
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
            unset($this->_permissionsCache);
        } catch (\Throwable $e) {
            // Silently fail if roles table doesn't exist yet
        }
    }

    /**
     * Give a direct permission override to the user.
     */
    public function giveDirectPermission(Permission $permission): void
    {
        try {
            $this->directPermissions()->syncWithoutDetaching([$permission->id]);
            unset($this->_permissionsCache);
        } catch (\Throwable $e) {}
    }

    /**
     * Revoke a direct permission override from the user.
     */
    public function revokeDirectPermission(Permission $permission): void
    {
        try {
            $this->directPermissions()->detach($permission->id);
            unset($this->_permissionsCache);
        } catch (\Throwable $e) {}
    }

    /**
     * Sync direct permission overrides (full replace).
     */
    public function syncDirectPermissions(array $permissionIds): void
    {
        try {
            $this->directPermissions()->sync($permissionIds);
            unset($this->_permissionsCache);
        } catch (\Throwable $e) {}
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

    /**
     * Get all role names for this user.
     */
    public function getRoleNames(): array
    {
        try {
            return $this->roles->pluck('name')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
