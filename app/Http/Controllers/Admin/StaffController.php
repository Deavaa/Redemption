<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\EmployeeIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Staff roles that can be created through this controller.
     * Excludes: student, parent (created through their own flows).
     */
    protected const STAFF_ROLES = [
        'admin'             => 'Admin',
        'teacher'           => 'Teacher',
        'general_manager'   => 'General Manager',
        'branch_principal'  => 'Branch Principal',
        'registrar'         => 'Registrar',
        'finance'           => 'Finance Officer',
        'hr'                => 'HR Officer',
        'cashier'           => 'Cashier',
        'librarian'         => 'Librarian',
        'staff'             => 'Staff',
    ];

    /**
     * Roles that require a branch assignment.
     */
    protected const BRANCH_ROLES = ['branch_principal', 'finance', 'hr', 'cashier', 'librarian', 'registrar'];

    public function index()
    {
        $query = User::whereIn('role', array_keys(self::STAFF_ROLES))
            ->with('branch');

        // Branch principals can only see staff from their own branch
        if (auth()->user()->role === 'branch_principal') {
            $query->where('branch_id', auth()->user()->branch_id);
        }

        // Apply role filter if provided
        if (request()->filled('role') && in_array(request('role'), array_keys(self::STAFF_ROLES))) {
            $query->where('role', request('role'));
        }

        $staff = $query->orderBy('name', 'asc')->paginate(20);

        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        $roles = self::STAFF_ROLES;
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $branchRoles = self::BRANCH_ROLES;
        $authUser = auth()->user();
        $isBranchPrincipal = $authUser->role === 'branch_principal';
        $authBranchId = $isBranchPrincipal ? $authUser->branch_id : null;

        // If branch principal has no branch assigned, try to load from staff/teacher relationship
        if ($isBranchPrincipal && !$authBranchId) {
            $branch = Branch::where('is_headquarters', true)->first();
            if ($branch) {
                $authBranchId = $branch->id;
            }
        }

        return view('admin.staff.create', compact('roles', 'branches', 'branchRoles', 'isBranchPrincipal', 'authBranchId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'id_number'    => 'nullable|string|max:50|unique:users,id_number',
            'phone'        => 'nullable|string|max:20',
            'role'         => 'required|in:' . implode(',', array_keys(self::STAFF_ROLES)),
            'branch_id'    => 'nullable|exists:branches,id',
            'password'     => 'nullable|string|min:6|confirmed',
            'gender'       => 'nullable|string|max:10',
            'qualification'=> 'nullable|string|max:255',
            'address'      => 'nullable|string|max:500',
        ]);

        // Default password for all new staff members
        $employeeIdService = new EmployeeIdService();
        $defaultPassword = $employeeIdService->getDefaultPassword();
        $validated['password'] = Hash::make($validated['password'] ?? $defaultPassword);

        // Normalize phone number
        if (!empty($validated['phone'])) {
            $validated['phone'] = $this->normalizePhone($validated['phone']);
        }

        // Force branch_principal's own branch — they cannot assign other branches
        // This applies to ALL roles when the logged-in user is a branch principal
        if (auth()->user()->role === 'branch_principal') {
            $validated['branch_id'] = auth()->user()->branch_id;
        }

        // Auto-assign branch for branch-scoped roles if not provided
        if (in_array($validated['role'], self::BRANCH_ROLES) && empty($validated['branch_id'])) {
            // Default to headquarters branch if not specified
            $hq = Branch::where('is_headquarters', true)->first();
            if ($hq) {
                $validated['branch_id'] = $hq->id;
            }
        }

        $user = User::create($validated);

        // Auto-generate employee ID
        $employeeId = $employeeIdService->assignToUser($user, $validated['branch_id'] ?? null);

        // Assign RBAC role if it exists
        $this->syncRbacRole($user, $validated['role']);

        // If role is branch_principal, also add to branch_principals pivot
        if ($validated['role'] === 'branch_principal' && !empty($validated['branch_id'])) {
            try {
                DB::table('branch_principals')->insert([
                    'branch_id' => $validated['branch_id'],
                    'teacher_id' => 0, // Will be updated when teacher profile is linked
                    'is_primary' => true,
                    'assigned_date' => now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {}
        }

        return redirect()->route('admin.staff.index')
            ->with('success', __('app.staff_member_created', ['name' => $user->name]) . " Employee ID: {$employeeId}. Default password: {$defaultPassword}");
    }

    public function edit(User $user)
    {
        $roles = self::STAFF_ROLES;
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $branchRoles = self::BRANCH_ROLES;
        $authUser = auth()->user();
        $isBranchPrincipal = $authUser->role === 'branch_principal';
        $authBranchId = $isBranchPrincipal ? $authUser->branch_id : null;

        // If branch principal has no branch assigned, try to load from staff/teacher relationship
        if ($isBranchPrincipal && !$authBranchId) {
            $branch = Branch::where('is_headquarters', true)->first();
            if ($branch) {
                $authBranchId = $branch->id;
            }
        }

        return view('admin.staff.edit', compact('user', 'roles', 'branches', 'branchRoles', 'isBranchPrincipal', 'authBranchId'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $user->id,
            'id_number'    => 'nullable|string|max:50|unique:users,id_number,' . $user->id,
            'phone'        => 'nullable|string|max:20',
            'role'         => 'required|in:' . implode(',', array_keys(self::STAFF_ROLES)),
            'branch_id'    => 'nullable|exists:branches,id',
            'password'     => 'nullable|string|min:6|confirmed',
            'gender'       => 'nullable|string|max:10',
            'qualification'=> 'nullable|string|max:255',
            'address'      => 'nullable|string|max:500',
            'is_active'    => 'nullable|boolean',
        ]);

        // Handle password
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle is_active checkbox
        $validated['is_active'] = $request->has('is_active');

        // Force branch_principal's own branch — they cannot assign other branches
        // This applies to ALL roles when the logged-in user is a branch principal
        if (auth()->user()->role === 'branch_principal') {
            $validated['branch_id'] = auth()->user()->branch_id;
        }

        // Auto-assign branch for branch-scoped roles if not provided
        if (in_array($validated['role'], self::BRANCH_ROLES) && empty($validated['branch_id'])) {
            $hq = Branch::where('is_headquarters', true)->first();
            if ($hq) {
                $validated['branch_id'] = $hq->id;
            }
        }

        $user->update($validated);

        // Sync RBAC role
        $this->syncRbacRole($user, $validated['role']);

        return redirect()->route('admin.staff.index')
            ->with('success', __('app.staff_member_updated', ['name' => $user->name]));
    }

    public function destroy(User $user)
    {
        // Prevent deleting the last admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('admin.staff.index')
                ->with('error', 'Cannot delete the last admin user.');
        }

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.staff.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member removed.');
    }

    /**
     * Normalize phone number to 0900000000 format.
     */
    private function normalizePhone(string $input): string
    {
        $cleaned = preg_replace('/[\s\-().]/', '', $input);
        if (preg_match('/^(\+251|00251)(\d{9})$/', $cleaned, $m)) {
            return '0' . $m[2];
        }
        if (preg_match('/^251(\d{9})$/', $cleaned, $m)) {
            return '0' . $m[1];
        }
        if (preg_match('/^0\d{9}$/', $cleaned)) {
            return $cleaned;
        }
        return $cleaned;
    }

    /**
     * Sync the RBAC role (role_user pivot) with the legacy role column.
     */
    protected function syncRbacRole(User $user, string $roleName): void
    {
        try {
            $rbacRole = Role::where('name', $roleName)->first();
            if ($rbacRole) {
                // Detach all current roles and attach the new one
                $user->roles()->sync([$rbacRole->id]);
            }
        } catch (\Throwable $e) {
            // RBAC tables may not exist yet — silently skip
        }
    }
}
