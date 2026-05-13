<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display all roles with their permissions and user counts.
     */
    public function index()
    {
        $roles = Role::withCount('users')->with('permissions')->get();
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $modules = $permissions->keys();

        return view('admin.roles.index', compact('roles', 'permissions', 'modules'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $modules = $permissions->keys();

        return view('admin.roles.create', compact('permissions', 'modules'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description'  => 'nullable|string|max:500',
            'permissions'  => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name'         => $validated['name'],
            'display_name' => $validated['display_name'],
            'description'  => $validated['description'] ?? null,
            'is_system'    => false,
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$role->display_name}\" created successfully.");
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $modules = $permissions->keys();
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'modules', 'rolePermissionIds'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'description'  => 'nullable|string|max:500',
            'permissions'  => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'display_name' => $validated['display_name'],
            'description'  => $validated['description'] ?? null,
        ]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$role->display_name}\" updated successfully.");
    }

    /**
     * Delete a non-system role.
     */
    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'System roles cannot be deleted.');
        }

        $role->permissions()->detach();
        $role->users()->detach();
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$role->display_name}\" deleted successfully.");
    }

    /**
     * Show users assigned to a role, and form to assign users.
     */
    public function users(Role $role)
    {
        $roleUsers = $role->users()->paginate(25);
        $allUsers = User::orderBy('name')->get(['id', 'name', 'email', 'role']);
        $assignedUserIds = $role->users->pluck('id')->toArray();

        return view('admin.roles.users', compact('role', 'roleUsers', 'allUsers', 'assignedUserIds'));
    }

    /**
     * Assign users to a role.
     */
    public function assignUsers(Request $request, Role $role)
    {
        $validated = $request->validate([
            'user_ids'   => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $role->users()->sync($validated['user_ids'] ?? []);

        return redirect()->route('admin.roles.users', $role)
            ->with('success', "Users assigned to \"{$role->display_name}\" successfully.");
    }

    /**
     * Quick permission toggle for a specific role + permission.
     */
    public function togglePermission(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permission_id' => 'required|exists:permissions,id',
            'enabled'       => 'required|boolean',
        ]);

        if ($validated['enabled']) {
            $role->permissions()->syncWithoutDetaching([$validated['permission_id']]);
        } else {
            $role->permissions()->detach($validated['permission_id']);
        }

        return response()->json(['success' => true]);
    }
}
