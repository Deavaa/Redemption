<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Create librarian role (idempotent)
        $librarianRole = DB::table('roles')->where('name', 'librarian')->first();
        if (!$librarianRole) {
            $librarianRoleId = DB::table('roles')->insertGetId([
                'name' => 'librarian',
                'display_name' => 'Librarian',
                'description' => 'Can manage the digital library - upload, edit, and organize books',
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $librarianRoleId = $librarianRole->id;
        }

        // Create branch_principal role (idempotent)
        $branchPrincipalRole = DB::table('roles')->where('name', 'branch_principal')->first();
        if (!$branchPrincipalRole) {
            $branchPrincipalRoleId = DB::table('roles')->insertGetId([
                'name' => 'branch_principal',
                'display_name' => 'Branch Principal',
                'description' => 'Can manage library books for their branch and access branch-specific features',
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $branchPrincipalRoleId = $branchPrincipalRole->id;
        }

        // Create library permissions (idempotent)
        $libraryPermissions = [
            ['name' => 'library.view', 'display_name' => 'View Library', 'module' => 'academic', 'description' => 'View and browse library books', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'library.upload', 'display_name' => 'Upload Books', 'module' => 'academic', 'description' => 'Upload new books to the library', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'library.edit', 'display_name' => 'Edit Books', 'module' => 'academic', 'description' => 'Edit book details and files', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'library.delete', 'display_name' => 'Delete Books', 'module' => 'academic', 'description' => 'Remove books from the library', 'created_at' => $now, 'updated_at' => $now],
        ];

        $permissionIds = [];
        foreach ($libraryPermissions as $perm) {
            $existing = DB::table('permissions')->where('name', $perm['name'])->first();
            if (!$existing) {
                $permissionIds[] = DB::table('permissions')->insertGetId($perm);
            } else {
                $permissionIds[] = $existing->id;
            }
        }

        // Assign all library permissions to librarian role (idempotent - permission_role has no timestamps)
        foreach ($permissionIds as $pid) {
            $exists = DB::table('permission_role')
                ->where('permission_id', $pid)
                ->where('role_id', $librarianRoleId)
                ->exists();
            if (!$exists) {
                DB::table('permission_role')->insert([
                    'permission_id' => $pid,
                    'role_id' => $librarianRoleId,
                ]);
            }
        }

        // Assign all library permissions to branch_principal role (idempotent)
        foreach ($permissionIds as $pid) {
            $exists = DB::table('permission_role')
                ->where('permission_id', $pid)
                ->where('role_id', $branchPrincipalRoleId)
                ->exists();
            if (!$exists) {
                DB::table('permission_role')->insert([
                    'permission_id' => $pid,
                    'role_id' => $branchPrincipalRoleId,
                ]);
            }
        }

        // Assign library.view to existing roles (teacher, student, staff, parent)
        $viewOnlyRoles = DB::table('roles')->whereIn('name', ['teacher', 'student', 'staff', 'parent'])->pluck('id');
        $viewPermissionId = $permissionIds[0]; // library.view
        foreach ($viewOnlyRoles as $roleId) {
            $exists = DB::table('permission_role')
                ->where('permission_id', $viewPermissionId)
                ->where('role_id', $roleId)
                ->exists();
            if (!$exists) {
                DB::table('permission_role')->insert([
                    'permission_id' => $viewPermissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Remove library permissions
        $libraryPermIds = DB::table('permissions')->where('name', 'like', 'library.%')->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $libraryPermIds)->delete();
        DB::table('permission_user')->whereIn('permission_id', $libraryPermIds)->delete();
        DB::table('permissions')->where('name', 'like', 'library.%')->delete();

        // Remove librarian and branch_principal roles
        $roleIds = DB::table('roles')->whereIn('name', ['librarian', 'branch_principal'])->pluck('id');
        DB::table('permission_role')->whereIn('role_id', $roleIds)->delete();
        DB::table('role_user')->whereIn('role_id', $roleIds)->delete();
        DB::table('roles')->whereIn('name', ['librarian', 'branch_principal'])->delete();
    }
};
