<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only insert if permissions table exists
        if (!DB::getSchemaBuilder()->hasTable('permissions')) {
            return;
        }

        $module = 'trainings';
        $now = now();

        $permissions = [
            ['name' => 'trainings.view',       'display_name' => 'View Trainings',       'module' => $module, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'trainings.create',      'display_name' => 'Create Trainings',     'module' => $module, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'trainings.edit',        'display_name' => 'Edit Trainings',       'module' => $module, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'trainings.delete',      'display_name' => 'Delete Trainings',     'module' => $module, 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $perm['name']],
                $perm
            );
        }

        // Assign to admin role if roles table exists
        if (DB::getSchemaBuilder()->hasTable('roles')) {
            $adminRole = DB::table('roles')->where('name', 'admin')->first();
            if ($adminRole && DB::getSchemaBuilder()->hasTable('permission_role')) {
                $permIds = DB::table('permissions')->where('module', $module)->pluck('id');
                foreach ($permIds as $pid) {
                    DB::table('permission_role')->updateOrInsert(
                        ['permission_id' => $pid, 'role_id' => $adminRole->id],
                        ['permission_id' => $pid, 'role_id' => $adminRole->id]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        if (DB::getSchemaBuilder()->hasTable('permissions')) {
            DB::table('permissions')->where('module', 'trainings')->delete();
        }
    }
};
