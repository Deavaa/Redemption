<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Define all module permissions and system roles.
     */
    private function getPermissions(): array
    {
        return [
            // ── Academic Module ──────────────────────────────
            ['name' => 'academic_years.view',       'display_name' => 'View Academic Years',       'module' => 'academic'],
            ['name' => 'academic_years.create',     'display_name' => 'Create Academic Years',     'module' => 'academic'],
            ['name' => 'academic_years.edit',       'display_name' => 'Edit Academic Years',       'module' => 'academic'],
            ['name' => 'academic_years.delete',     'display_name' => 'Delete Academic Years',     'module' => 'academic'],

            ['name' => 'terms.view',                'display_name' => 'View Terms',                'module' => 'academic'],
            ['name' => 'terms.create',              'display_name' => 'Create Terms',              'module' => 'academic'],
            ['name' => 'terms.edit',                'display_name' => 'Edit Terms',                'module' => 'academic'],
            ['name' => 'terms.delete',              'display_name' => 'Delete Terms',              'module' => 'academic'],

            ['name' => 'subjects.view',             'display_name' => 'View Subjects',             'module' => 'academic'],
            ['name' => 'subjects.create',           'display_name' => 'Create Subjects',           'module' => 'academic'],
            ['name' => 'subjects.edit',             'display_name' => 'Edit Subjects',             'module' => 'academic'],
            ['name' => 'subjects.delete',           'display_name' => 'Delete Subjects',           'module' => 'academic'],

            ['name' => 'subject_assignments.view',  'display_name' => 'View Subject Assignments',  'module' => 'academic'],
            ['name' => 'subject_assignments.create','display_name' => 'Create Subject Assignments','module' => 'academic'],
            ['name' => 'subject_assignments.edit',  'display_name' => 'Edit Subject Assignments',  'module' => 'academic'],
            ['name' => 'subject_assignments.delete','display_name' => 'Delete Subject Assignments','module' => 'academic'],

            ['name' => 'exams.view',                'display_name' => 'View Exams',                'module' => 'academic'],
            ['name' => 'exams.create',              'display_name' => 'Create Exams',              'module' => 'academic'],
            ['name' => 'exams.edit',                'display_name' => 'Edit Exams',                'module' => 'academic'],
            ['name' => 'exams.delete',              'display_name' => 'Delete Exams',              'module' => 'academic'],

            ['name' => 'classrooms.view',           'display_name' => 'View Classes',              'module' => 'academic'],
            ['name' => 'classrooms.create',         'display_name' => 'Create Classes',            'module' => 'academic'],
            ['name' => 'classrooms.edit',           'display_name' => 'Edit Classes',              'module' => 'academic'],
            ['name' => 'classrooms.delete',         'display_name' => 'Delete Classes',            'module' => 'academic'],

            ['name' => 'sections.view',             'display_name' => 'View Sections',             'module' => 'academic'],
            ['name' => 'sections.create',           'display_name' => 'Create Sections',           'module' => 'academic'],
            ['name' => 'sections.edit',             'display_name' => 'Edit Sections',             'module' => 'academic'],
            ['name' => 'sections.delete',           'display_name' => 'Delete Sections',           'module' => 'academic'],

            ['name' => 'class_assets.view',         'display_name' => 'View Section Assets',       'module' => 'academic'],
            ['name' => 'class_assets.create',       'display_name' => 'Create Section Assets',     'module' => 'academic'],
            ['name' => 'class_assets.edit',         'display_name' => 'Edit Section Assets',       'module' => 'academic'],
            ['name' => 'class_assets.delete',       'display_name' => 'Delete Section Assets',     'module' => 'academic'],

            ['name' => 'mark_entries.view',         'display_name' => 'View Mark Entries',         'module' => 'academic'],
            ['name' => 'mark_entries.create',       'display_name' => 'Enter Marks',               'module' => 'academic'],
            ['name' => 'mark_entries.edit',         'display_name' => 'Edit Mark Entries',         'module' => 'academic'],
            ['name' => 'mark_entries.delete',       'display_name' => 'Delete Mark Entries',       'module' => 'academic'],

            ['name' => 'mark_sheets.view',          'display_name' => 'View Mark Sheets',          'module' => 'academic'],
            ['name' => 'mark_sheets.generate',      'display_name' => 'Generate Mark Sheets',      'module' => 'academic'],

            ['name' => 'attendance.view',           'display_name' => 'View Attendance',            'module' => 'academic'],
            ['name' => 'attendance.manage',          'display_name' => 'Record & Edit Attendance',   'module' => 'academic'],

            ['name' => 'id_cards.generate',         'display_name' => 'Generate ID Cards',         'module' => 'academic'],
            ['name' => 'certificates.generate',     'display_name' => 'Generate Certificates',     'module' => 'academic'],

            ['name' => 'calendar.view',             'display_name' => 'View Academic Calendar',    'module' => 'academic'],
            ['name' => 'calendar.manage',           'display_name' => 'Manage Academic Calendar',  'module' => 'academic'],

            // ── People Module ────────────────────────────────
            ['name' => 'students.view',             'display_name' => 'View Students',             'module' => 'people'],
            ['name' => 'students.create',           'display_name' => 'Create Students',           'module' => 'people'],
            ['name' => 'students.edit',             'display_name' => 'Edit Students',             'module' => 'people'],
            ['name' => 'students.delete',           'display_name' => 'Delete Students',           'module' => 'people'],

            ['name' => 'enrollments.view',          'display_name' => 'View Enrollments',          'module' => 'people'],
            ['name' => 'enrollments.manage',        'display_name' => 'Manage Enrollments',        'module' => 'people'],

            ['name' => 'teachers.view',             'display_name' => 'View Teachers',             'module' => 'people'],
            ['name' => 'teachers.create',           'display_name' => 'Create Teachers',           'module' => 'people'],
            ['name' => 'teachers.edit',             'display_name' => 'Edit Teachers',             'module' => 'people'],
            ['name' => 'teachers.delete',           'display_name' => 'Delete Teachers',           'module' => 'people'],

            ['name' => 'staff.view',                'display_name' => 'View Staff',                'module' => 'people'],
            ['name' => 'staff.create',              'display_name' => 'Create Staff',              'module' => 'people'],
            ['name' => 'staff.edit',                'display_name' => 'Edit Staff',                'module' => 'people'],
            ['name' => 'staff.delete',              'display_name' => 'Delete Staff',              'module' => 'people'],

            ['name' => 'team_members.view',         'display_name' => 'View Team Members',         'module' => 'people'],
            ['name' => 'team_members.create',       'display_name' => 'Create Team Members',       'module' => 'people'],
            ['name' => 'team_members.edit',         'display_name' => 'Edit Team Members',         'module' => 'people'],
            ['name' => 'team_members.delete',       'display_name' => 'Delete Team Members',       'module' => 'people'],

            ['name' => 'parents.view',              'display_name' => 'View Parents',              'module' => 'people'],
            ['name' => 'parents.create',            'display_name' => 'Create Parents',            'module' => 'people'],
            ['name' => 'parents.edit',              'display_name' => 'Edit Parents',              'module' => 'people'],
            ['name' => 'parents.delete',            'display_name' => 'Delete Parents',            'module' => 'people'],

            // ── Finance Module ───────────────────────────────
            ['name' => 'fees.view',                 'display_name' => 'View Fees',                 'module' => 'finance'],
            ['name' => 'fees.create',               'display_name' => 'Create Fees',               'module' => 'finance'],
            ['name' => 'fees.edit',                 'display_name' => 'Edit Fees',                 'module' => 'finance'],
            ['name' => 'fees.delete',               'display_name' => 'Delete Fees',               'module' => 'finance'],

            ['name' => 'fee_payments.view',         'display_name' => 'View Fee Payments',         'module' => 'finance'],
            ['name' => 'fee_payments.create',       'display_name' => 'Record Fee Payments',       'module' => 'finance'],
            ['name' => 'fee_payments.edit',         'display_name' => 'Edit Fee Payments',         'module' => 'finance'],
            ['name' => 'fee_payments.delete',       'display_name' => 'Delete Fee Payments',       'module' => 'finance'],

            ['name' => 'payrolls.view',             'display_name' => 'View Payroll',              'module' => 'finance'],
            ['name' => 'payrolls.create',           'display_name' => 'Create Payroll',            'module' => 'finance'],
            ['name' => 'payrolls.edit',             'display_name' => 'Edit Payroll',              'module' => 'finance'],
            ['name' => 'payrolls.delete',           'display_name' => 'Delete Payroll',            'module' => 'finance'],

            ['name' => 'budgets.view',              'display_name' => 'View Budgets',              'module' => 'finance'],
            ['name' => 'budgets.create',            'display_name' => 'Create Budgets',            'module' => 'finance'],
            ['name' => 'budgets.edit',              'display_name' => 'Edit Budgets',              'module' => 'finance'],
            ['name' => 'budgets.delete',            'display_name' => 'Delete Budgets',            'module' => 'finance'],

            ['name' => 'income_expenses.view',      'display_name' => 'View Income/Expenses',      'module' => 'finance'],
            ['name' => 'income_expenses.create',    'display_name' => 'Create Income/Expenses',    'module' => 'finance'],
            ['name' => 'income_expenses.edit',      'display_name' => 'Edit Income/Expenses',      'module' => 'finance'],
            ['name' => 'income_expenses.delete',    'display_name' => 'Delete Income/Expenses',    'module' => 'finance'],

            ['name' => 'finance_statements.view',   'display_name' => 'View Finance Statements',   'module' => 'finance'],
            ['name' => 'finance_statements.create', 'display_name' => 'Create Finance Statements', 'module' => 'finance'],
            ['name' => 'finance_statements.edit',   'display_name' => 'Edit Finance Statements',   'module' => 'finance'],
            ['name' => 'finance_statements.delete', 'display_name' => 'Delete Finance Statements', 'module' => 'finance'],

            ['name' => 'leaves.view',               'display_name' => 'View Leaves',               'module' => 'finance'],
            ['name' => 'leaves.create',             'display_name' => 'Apply Leaves',              'module' => 'finance'],
            ['name' => 'leaves.edit',               'display_name' => 'Edit Leaves',               'module' => 'finance'],
            ['name' => 'leaves.delete',             'display_name' => 'Delete Leaves',             'module' => 'finance'],
            ['name' => 'leaves.approve',            'display_name' => 'Approve Leaves',            'module' => 'finance'],

            ['name' => 'employee_assets.view',      'display_name' => 'View Employee Assets',      'module' => 'finance'],
            ['name' => 'employee_assets.create',    'display_name' => 'Assign Employee Assets',    'module' => 'finance'],
            ['name' => 'employee_assets.edit',      'display_name' => 'Edit Employee Assets',      'module' => 'finance'],
            ['name' => 'employee_assets.delete',    'display_name' => 'Delete Employee Assets',    'module' => 'finance'],

            // ── Website Module ───────────────────────────────
            ['name' => 'branches.view',             'display_name' => 'View Branches',             'module' => 'website'],
            ['name' => 'branches.create',           'display_name' => 'Create Branches',           'module' => 'website'],
            ['name' => 'branches.edit',             'display_name' => 'Edit Branches',             'module' => 'website'],
            ['name' => 'branches.delete',           'display_name' => 'Delete Branches',           'module' => 'website'],

            ['name' => 'sliders.view',              'display_name' => 'View Sliders',              'module' => 'website'],
            ['name' => 'sliders.create',            'display_name' => 'Create Sliders',            'module' => 'website'],
            ['name' => 'sliders.edit',              'display_name' => 'Edit Sliders',              'module' => 'website'],
            ['name' => 'sliders.delete',            'display_name' => 'Delete Sliders',            'module' => 'website'],

            ['name' => 'gallery.view',              'display_name' => 'View Gallery',              'module' => 'website'],
            ['name' => 'gallery.create',            'display_name' => 'Upload Gallery Items',      'module' => 'website'],
            ['name' => 'gallery.edit',              'display_name' => 'Edit Gallery Items',        'module' => 'website'],
            ['name' => 'gallery.delete',            'display_name' => 'Delete Gallery Items',      'module' => 'website'],

            ['name' => 'contact_messages.view',     'display_name' => 'View Contact Messages',     'module' => 'website'],
            ['name' => 'contact_messages.delete',   'display_name' => 'Delete Contact Messages',   'module' => 'website'],

            // ── Library Module ────────────────────────────────
            ['name' => 'library.view',              'display_name' => 'View Library',              'module' => 'library'],
            ['name' => 'library.create',            'display_name' => 'Add Library Books',         'module' => 'library'],
            ['name' => 'library.edit',              'display_name' => 'Edit Library Books',        'module' => 'library'],
            ['name' => 'library.delete',            'display_name' => 'Delete Library Books',      'module' => 'library'],

            // ── Communication Module ─────────────────────────
            ['name' => 'chat.access',               'display_name' => 'Access Chat',               'module' => 'communication'],
            ['name' => 'telegram.manage',           'display_name' => 'Manage Telegram',           'module' => 'communication'],
            ['name' => 'notifications.view',        'display_name' => 'View Notifications',        'module' => 'communication'],

            // ── System Module ────────────────────────────────
            ['name' => 'settings.view',             'display_name' => 'View Settings',             'module' => 'system'],
            ['name' => 'settings.edit',             'display_name' => 'Edit Settings',             'module' => 'system'],
            ['name' => 'roles.view',                'display_name' => 'View Roles & Permissions',  'module' => 'system'],
            ['name' => 'roles.create',              'display_name' => 'Create Roles',              'module' => 'system'],
            ['name' => 'roles.edit',                'display_name' => 'Edit Roles',                'module' => 'system'],
            ['name' => 'roles.delete',              'display_name' => 'Delete Roles',              'module' => 'system'],
            ['name' => 'audits.view',               'display_name' => 'View Audit Log',            'module' => 'system'],
            ['name' => 'dashboard.view',            'display_name' => 'View Dashboard',            'module' => 'system'],
        ];
    }

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        // Create all permissions
        foreach ($this->getPermissions() as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm['name']],
                $perm
            );
        }

        // Create system roles
        $adminRole = Role::updateOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator', 'description' => 'Full system access — super admin', 'is_system' => true]
        );

        $teacherRole = Role::updateOrCreate(
            ['name' => 'teacher'],
            ['display_name' => 'Teacher', 'description' => 'Can view and manage assigned academic records', 'is_system' => true]
        );

        $staffRole = Role::updateOrCreate(
            ['name' => 'staff'],
            ['display_name' => 'Staff', 'description' => 'Limited access based on assigned duties', 'is_system' => true]
        );

        $parentRole = Role::updateOrCreate(
            ['name' => 'parent'],
            ['display_name' => 'Parent', 'description' => 'Can view own child records', 'is_system' => true]
        );

        $studentRole = Role::updateOrCreate(
            ['name' => 'student'],
            ['display_name' => 'Student', 'description' => 'Can view own academic records', 'is_system' => true]
        );

        // ── New Staff Roles ──────────────────────────────────
        $generalManagerRole = Role::updateOrCreate(
            ['name' => 'general_manager'],
            ['display_name' => 'General Manager', 'description' => 'Oversees all branches and operations', 'is_system' => true]
        );

        $branchPrincipalRole = Role::updateOrCreate(
            ['name' => 'branch_principal'],
            ['display_name' => 'Branch Principal', 'description' => 'Manages their assigned branch only', 'is_system' => true]
        );

        $registrarRole = Role::updateOrCreate(
            ['name' => 'registrar'],
            ['display_name' => 'Registrar', 'description' => 'Manages student enrollment and academic records', 'is_system' => true]
        );

        $financeRole = Role::updateOrCreate(
            ['name' => 'finance'],
            ['display_name' => 'Finance Officer', 'description' => 'Manages financial operations, budgets, and payroll', 'is_system' => true]
        );

        $hrRole = Role::updateOrCreate(
            ['name' => 'hr'],
            ['display_name' => 'HR Officer', 'description' => 'Manages human resources, leaves, and employee assets', 'is_system' => true]
        );

        $cashierRole = Role::updateOrCreate(
            ['name' => 'cashier'],
            ['display_name' => 'Cashier', 'description' => 'Processes fee payments', 'is_system' => true]
        );

        $librarianRole = Role::updateOrCreate(
            ['name' => 'librarian'],
            ['display_name' => 'Librarian', 'description' => 'Manages the digital library', 'is_system' => true]
        );

        // Assign all permissions to admin role
        $adminRole->syncPermissions(Permission::pluck('id')->toArray());

        // Assign teacher-specific permissions
        $teacherPerms = [
            'dashboard.view',
            'academic_years.view', 'terms.view', 'subjects.view', 'subject_assignments.view',
            'exams.view', 'classrooms.view', 'sections.view',
            'mark_entries.view', 'mark_entries.create', 'mark_entries.edit',
            'mark_sheets.view', 'mark_sheets.generate',
            'attendance.view', 'attendance.manage',
            'students.view', 'teachers.view',
            'id_cards.generate', 'certificates.generate',
            'calendar.view',
            'chat.access', 'notifications.view',
        ];
        $teacherRole->syncPermissions(
            Permission::whereIn('name', $teacherPerms)->pluck('id')->toArray()
        );

        // Assign staff-specific permissions
        $staffPerms = [
            'dashboard.view',
            'students.view', 'teachers.view', 'staff.view', 'parents.view',
            'fees.view', 'fee_payments.view', 'fee_payments.create',
            'leaves.view', 'leaves.create',
            'calendar.view', 'chat.access', 'notifications.view',
        ];
        $staffRole->syncPermissions(
            Permission::whereIn('name', $staffPerms)->pluck('id')->toArray()
        );

        // Assign parent-specific permissions
        $parentPerms = [
            'dashboard.view',
            'students.view', 'fees.view', 'fee_payments.view',
            'mark_sheets.view',
            'calendar.view', 'chat.access', 'notifications.view',
        ];
        $parentRole->syncPermissions(
            Permission::whereIn('name', $parentPerms)->pluck('id')->toArray()
        );

        // Assign student-specific permissions
        $studentPerms = [
            'dashboard.view',
            'mark_sheets.view',
            'calendar.view', 'chat.access', 'notifications.view',
        ];
        $studentRole->syncPermissions(
            Permission::whereIn('name', $studentPerms)->pluck('id')->toArray()
        );

        // ── New Staff Role Permissions ───────────────────────

        // General Manager: broad access (like admin but without system settings)
        $gmPerms = [
            'dashboard.view',
            // Academic
            'academic_years.view', 'terms.view', 'subjects.view', 'subject_assignments.view',
            'exams.view', 'classrooms.view', 'sections.view',
            'mark_entries.view', 'mark_sheets.view', 'mark_sheets.generate',
            'attendance.view',
            // People
            'students.view', 'teachers.view', 'staff.view', 'parents.view', 'team_members.view',
            // Finance & HR
            'fees.view', 'fee_payments.view', 'payrolls.view',
            'budgets.view', 'income_expenses.view', 'finance_statements.view',
            'leaves.view', 'employee_assets.view',
            // Documents
            'id_cards.generate', 'certificates.generate',
            // Website
            'branches.view',
            // Communication
            'calendar.view', 'calendar.manage', 'chat.access', 'notifications.view', 'telegram.manage',
            // Analysis
            'mark_sheets.generate',
        ];
        $generalManagerRole->syncPermissions(
            Permission::whereIn('name', $gmPerms)->pluck('id')->toArray()
        );

        // Branch Principal: academic + people + documents for own branch
        $bpPerms = [
            'dashboard.view',
            // Academic
            'academic_years.view', 'terms.view', 'subjects.view', 'subject_assignments.view',
            'exams.view', 'classrooms.view', 'sections.view',
            'mark_entries.view', 'mark_entries.create', 'mark_entries.edit',
            'mark_sheets.view', 'mark_sheets.generate',
            'attendance.view', 'attendance.manage',
            // People
            'students.view', 'teachers.view', 'staff.view', 'parents.view',
            // Enrollment
            'enrollments.view', 'enrollments.manage',
            // Documents
            'id_cards.generate', 'certificates.generate',
            // Finance (view only)
            'fees.view', 'fee_payments.view',
            // Communication
            'calendar.view', 'calendar.manage', 'chat.access', 'notifications.view',
        ];
        $branchPrincipalRole->syncPermissions(
            Permission::whereIn('name', $bpPerms)->pluck('id')->toArray()
        );

        // Registrar: enrollment + academic records
        $registrarPerms = [
            'dashboard.view',
            // Academic Setup
            'academic_years.view', 'terms.view', 'classrooms.view', 'sections.view',
            'subjects.view', 'subject_assignments.view', 'exams.view',
            'attendance.view', 'attendance.manage',
            // People
            'students.view', 'students.create', 'students.edit',
            'parents.view', 'parents.create', 'parents.edit',
            // Enrollment
            'enrollments.view', 'enrollments.manage',
            // Finance (view fees)
            'fees.view', 'fee_payments.view', 'fee_payments.create',
            // Documents
            'id_cards.generate', 'certificates.generate',
            // Communication
            'calendar.view', 'chat.access', 'notifications.view',
        ];
        $registrarRole->syncPermissions(
            Permission::whereIn('name', $registrarPerms)->pluck('id')->toArray()
        );

        // Finance Officer: full finance access
        $financePerms = [
            'dashboard.view',
            // People (view only)
            'students.view', 'teachers.view', 'staff.view',
            // Enrollment (view + fee management)
            'enrollments.view',
            // Finance
            'fees.view', 'fees.create', 'fees.edit',
            'fee_payments.view', 'fee_payments.create', 'fee_payments.edit',
            'payrolls.view', 'payrolls.create', 'payrolls.edit',
            'budgets.view', 'budgets.create', 'budgets.edit',
            'income_expenses.view', 'income_expenses.create', 'income_expenses.edit',
            'finance_statements.view',
            // Communication
            'calendar.view', 'chat.access', 'notifications.view',
        ];
        $financeRole->syncPermissions(
            Permission::whereIn('name', $financePerms)->pluck('id')->toArray()
        );

        // HR Officer: HR-focused access
        $hrPerms = [
            'dashboard.view',
            // People
            'students.view', 'teachers.view', 'staff.view', 'staff.create', 'staff.edit',
            'parents.view',
            // HR
            'leaves.view', 'leaves.create', 'leaves.edit', 'leaves.approve',
            'employee_assets.view', 'employee_assets.create', 'employee_assets.edit',
            'payrolls.view', 'payrolls.create', 'payrolls.edit',
            // Communication
            'calendar.view', 'chat.access', 'notifications.view',
        ];
        $hrRole->syncPermissions(
            Permission::whereIn('name', $hrPerms)->pluck('id')->toArray()
        );

        // Cashier: payment processing
        $cashierPerms = [
            'dashboard.view',
            // People (view only)
            'students.view', 'teachers.view',
            // Enrollment (view + fee management)
            'enrollments.view',
            // Finance
            'fees.view', 'fee_payments.view', 'fee_payments.create', 'fee_payments.edit',
            // Communication
            'calendar.view', 'chat.access', 'notifications.view',
        ];
        $cashierRole->syncPermissions(
            Permission::whereIn('name', $cashierPerms)->pluck('id')->toArray()
        );

        // Librarian: library access
        $librarianPerms = [
            'dashboard.view',
            // Library
            'library.view', 'library.create', 'library.edit', 'library.delete',
            // People (view only)
            'students.view', 'teachers.view',
            // Communication
            'calendar.view', 'chat.access', 'notifications.view',
        ];
        $librarianRole->syncPermissions(
            Permission::whereIn('name', $librarianPerms)->pluck('id')->toArray()
        );
    }
}
