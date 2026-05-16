<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add librarian and branch_principal roles
        $now = now();

        // Create librarian role
        $librarianRoleId = DB::table('roles')->insertGetId([
            'name' => 'librarian',
            'display_name' => 'Librarian',
            'description' => 'Can manage the digital library - upload, edit, and organize books',
            'is_system' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Create branch_principal role
        $branchPrincipalRoleId = DB::table('roles')->insertGetId([
            'name' => 'branch_principal',
            'display_name' => 'Branch Principal',
            'description' => 'Can manage library books for their branch and access branch-specific features',
            'is_system' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Create library permissions
        $libraryPermissions = [
            ['name' => 'library.view', 'display_name' => 'View Library', 'module' => 'academic', 'description' => 'View and browse library books', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'library.upload', 'display_name' => 'Upload Books', 'module' => 'academic', 'description' => 'Upload new books to the library', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'library.edit', 'display_name' => 'Edit Books', 'module' => 'academic', 'description' => 'Edit book details and files', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'library.delete', 'display_name' => 'Delete Books', 'module' => 'academic', 'description' => 'Remove books from the library', 'created_at' => $now, 'updated_at' => $now],
        ];

        $permissionIds = [];
        foreach ($libraryPermissions as $perm) {
            $permissionIds[] = DB::table('permissions')->insertGetId($perm);
        }

        // Assign all library permissions to librarian role
        foreach ($permissionIds as $pid) {
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $pid,
                'role_id' => $librarianRoleId,
            ]);
        }

        // Assign all library permissions to branch_principal role
        foreach ($permissionIds as $pid) {
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $pid,
                'role_id' => $branchPrincipalRoleId,
            ]);
        }

        // Assign library.view to existing roles (teacher, student, staff, parent)
        $viewOnlyRoles = DB::table('roles')->whereIn('name', ['teacher', 'student', 'staff', 'parent'])->pluck('id');
        $viewPermissionId = $permissionIds[0]; // library.view
        foreach ($viewOnlyRoles as $roleId) {
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $viewPermissionId,
                'role_id' => $roleId,
            ]);
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
