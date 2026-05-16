<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ensure the teacher role has the correct permissions assigned.
     * This is needed because teachers may have been created before the
     * PermissionSeeder was run, leaving them with no permissions in
     * permission_role — causing 403 errors on every admin route.
     */
    public function up(): void
    {
        $now = now();

        // ── 1. Ensure all required permissions exist ──────────────
        $permissions = [
            ['name' => 'dashboard.view',            'display_name' => 'View Dashboard',            'module' => 'system'],
            ['name' => 'academic_years.view',       'display_name' => 'View Academic Years',       'module' => 'academic'],
            ['name' => 'terms.view',                'display_name' => 'View Terms',                'module' => 'academic'],
            ['name' => 'subjects.view',             'display_name' => 'View Subjects',             'module' => 'academic'],
            ['name' => 'subject_assignments.view',  'display_name' => 'View Subject Assignments',  'module' => 'academic'],
            ['name' => 'exams.view',                'display_name' => 'View Exams',                'module' => 'academic'],
            ['name' => 'classrooms.view',           'display_name' => 'View Classes',              'module' => 'academic'],
            ['name' => 'sections.view',             'display_name' => 'View Sections',             'module' => 'academic'],
            ['name' => 'mark_entries.view',         'display_name' => 'View Mark Entries',         'module' => 'academic'],
            ['name' => 'mark_entries.create',       'display_name' => 'Enter Marks',               'module' => 'academic'],
            ['name' => 'mark_entries.edit',         'display_name' => 'Edit Mark Entries',         'module' => 'academic'],
            ['name' => 'mark_sheets.view',          'display_name' => 'View Mark Sheets',          'module' => 'academic'],
            ['name' => 'mark_sheets.generate',      'display_name' => 'Generate Mark Sheets',      'module' => 'academic'],
            ['name' => 'students.view',             'display_name' => 'View Students',             'module' => 'people'],
            ['name' => 'teachers.view',             'display_name' => 'View Teachers',             'module' => 'people'],
            ['name' => 'id_cards.generate',         'display_name' => 'Generate ID Cards',         'module' => 'academic'],
            ['name' => 'certificates.generate',     'display_name' => 'Generate Certificates',     'module' => 'academic'],
            ['name' => 'calendar.view',             'display_name' => 'View Academic Calendar',    'module' => 'academic'],
            ['name' => 'chat.access',               'display_name' => 'Access Chat',               'module' => 'communication'],
            ['name' => 'notifications.view',        'display_name' => 'View Notifications',        'module' => 'communication'],
            ['name' => 'library.view',              'display_name' => 'View Library',              'module' => 'academic'],
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $perm['name']],
                array_merge($perm, ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, \''.$now->format('Y-m-d H:i:s').'\')')])
            );
        }

        // ── 2. Ensure the teacher role exists ────────────────────
        $teacherRoleId = DB::table('roles')->where('name', 'teacher')->value('id');
        if (!$teacherRoleId) {
            $teacherRoleId = DB::table('roles')->insertGetId([
                'name' => 'teacher',
                'display_name' => 'Teacher',
                'description' => 'Can view and manage assigned academic records',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ── 3. Assign permissions to teacher role ────────────────
        $teacherPermNames = [
            'dashboard.view',
            'academic_years.view', 'terms.view', 'subjects.view', 'subject_assignments.view',
            'exams.view', 'classrooms.view', 'sections.view',
            'mark_entries.view', 'mark_entries.create', 'mark_entries.edit',
            'mark_sheets.view', 'mark_sheets.generate',
            'students.view', 'teachers.view',
            'id_cards.generate', 'certificates.generate',
            'calendar.view',
            'chat.access', 'notifications.view',
            'library.view',
        ];

        $permIds = DB::table('permissions')->whereIn('name', $teacherPermNames)->pluck('id');

        foreach ($permIds as $pid) {
            DB::table('permission_role')->updateOrInsert(
                ['permission_id' => $pid, 'role_id' => $teacherRoleId],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        // ── 4. Ensure all existing teacher users have the teacher role in role_user ──
        $teacherUserIds = DB::table('users')->where('role', 'teacher')->pluck('id');
        foreach ($teacherUserIds as $uid) {
            DB::table('role_user')->updateOrInsert(
                ['role_id' => $teacherRoleId, 'user_id' => $uid],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        // Remove teacher role permissions (but don't remove the permissions themselves)
        $teacherRoleId = DB::table('roles')->where('name', 'teacher')->value('id');
        if ($teacherRoleId) {
            DB::table('permission_role')->where('role_id', $teacherRoleId)->delete();
        }
    }
};
