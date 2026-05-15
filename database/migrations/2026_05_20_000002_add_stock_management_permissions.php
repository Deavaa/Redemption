<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Create stock management permissions (idempotent)
        $stockPermissions = [
            ['name' => 'stock.view', 'display_name' => 'View Stock', 'module' => 'finance', 'description' => 'View stock items and inventory', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'stock.create', 'display_name' => 'Create Stock Items', 'module' => 'finance', 'description' => 'Add new stock items to inventory', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'stock.edit', 'display_name' => 'Edit Stock Items', 'module' => 'finance', 'description' => 'Edit stock item details', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'stock.delete', 'display_name' => 'Delete Stock Items', 'module' => 'finance', 'description' => 'Remove stock items from inventory', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'stock.stock-in', 'display_name' => 'Stock In', 'module' => 'finance', 'description' => 'Add stock to inventory (stock in)', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'stock.stock-out', 'display_name' => 'Stock Out / Issue', 'module' => 'finance', 'description' => 'Issue stock to employees or classes (stock out)', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'stock.report', 'display_name' => 'Stock Reports', 'module' => 'finance', 'description' => 'View and generate stock reports', 'created_at' => $now, 'updated_at' => $now],
        ];

        $permissionIds = [];
        foreach ($stockPermissions as $perm) {
            $existing = DB::table('permissions')->where('name', $perm['name'])->first();
            if (!$existing) {
                $permissionIds[] = DB::table('permissions')->insertGetId($perm);
            } else {
                $permissionIds[] = $existing->id;
            }
        }

        // Assign all stock permissions to admin role (idempotent)
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        if ($adminRole) {
            foreach ($permissionIds as $pid) {
                $exists = DB::table('permission_role')
                    ->where('permission_id', $pid)
                    ->where('role_id', $adminRole->id)
                    ->exists();
                if (!$exists) {
                    DB::table('permission_role')->insert([
                        'permission_id' => $pid,
                        'role_id' => $adminRole->id,
                    ]);
                }
            }
        }

        // Assign stock.view to staff role (idempotent)
        $staffRole = DB::table('roles')->where('name', 'staff')->first();
        if ($staffRole && isset($permissionIds[0])) {
            $exists = DB::table('permission_role')
                ->where('permission_id', $permissionIds[0])
                ->where('role_id', $staffRole->id)
                ->exists();
            if (!$exists) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permissionIds[0],
                    'role_id' => $staffRole->id,
                ]);
            }
        }
    }

    public function down(): void
    {
        $stockPermIds = DB::table('permissions')->where('name', 'like', 'stock.%')->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $stockPermIds)->delete();
        DB::table('permission_user')->whereIn('permission_id', $stockPermIds)->delete();
        DB::table('permissions')->where('name', 'like', 'stock.%')->delete();
    }
};
