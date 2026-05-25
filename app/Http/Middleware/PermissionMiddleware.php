<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('permission:academic_years.view')
     * Or multiple (ANY match): ->middleware('permission:academic_years.view,academic_years.create')
     * Or ALL match: ->middleware('permission:academic_years.view&academic_years.create')
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Authentication required.'], 401);
            }
            abort(401);
        }

        // Admins and super_admin always pass all permission checks
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return $next($request);
        }

        // ── Role-based route restrictions ──────────────────────
        // Teachers only have access to certain sections.
        // If the route has a permission middleware, check if this role
        // is allowed to access that permission category.

        if ($user->role === 'teacher') {
            $teacherAllowedPermissions = [
                'dashboard.view',
                // Marks & Assessment
                'mark_entries.view', 'mark_entries.create', 'mark_entries.edit',
                'mark_sheets.view', 'mark_sheets.generate',
                // Students (view only — restricted by controller to assigned students)
                'students.view',
                // Teacher assignments
                'subject_assignments.view',
                // Lesson Plans
                'lesson_plans.view', 'lesson_plans.create', 'lesson_plans.edit', 'lesson_plans.follow_up',
                // Exam Questions
                'exam_questions.view', 'exam_questions.create', 'exam_questions.edit',
                // Teacher Evaluation
                'teacher_evaluations.view', 'teacher_evaluations.create', 'teacher_evaluations.edit',
                // Documents
                'settings.view', // report exchange uses settings.view
                // Library
                'library.view',
                // Attendance
                'attendance.view', 'attendance.manage',
                // Communication
                'calendar.view', 'calendar.manage',
                'chat.access',
                'notifications.view',
                // ID Cards and Certificates (view/generate only)
                'id_cards.generate',
                'certificates.generate',
            ];

            if (!empty($permissions)) {
                $hasAccess = false;
                foreach ($permissions as $perm) {
                    if (in_array($perm, $teacherAllowedPermissions)) {
                        $hasAccess = true;
                        break;
                    }
                }
                if (!$hasAccess) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Teachers do not have access to this section.',
                            'required_any' => $permissions,
                        ], 403);
                    }
                    // Redirect to dashboard with error message instead of 403 page
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'You do not have permission to access this section.');
                }
            }

            return $next($request);
        }

        // Branch principal — manages staff, students, parents in own branch only
        // Does NOT control academic years, terms, exams (those are admin-level)
        // Does NOT have access to team members, teacher assignments, website
        if ($user->role === 'branch_principal') {
            $branchPrincipalAllowedPermissions = [
                'dashboard.view',
                // NO academic_years or terms access — managed by admin/GM only
                // Academic setup (view only for subjects, classrooms, exams)
                'subjects.view', 'subject_assignments.view',
                'exams.view', 'classrooms.view', 'sections.view',
                // Marks & Assessment
                'mark_entries.view', 'mark_entries.create', 'mark_entries.edit',
                'mark_sheets.view', 'mark_sheets.generate',
                // People (full management within own branch only)
                'students.view', 'students.create', 'students.edit', 'students.manage',
                'teachers.view', 'teachers.create', 'teachers.edit',
                'parents.view', 'parents.create', 'parents.edit',
                // Staff management within branch
                'staff.view', 'staff.manage',
                // Attendance
                'attendance.view', 'attendance.manage',
                // Lesson Plans
                'lesson_plans.view', 'lesson_plans.create', 'lesson_plans.edit', 'lesson_plans.review', 'lesson_plans.follow_up',
                // Exam Questions (principal review)
                'exam_questions.view', 'exam_questions.principal_review',
                // Teacher Evaluation
                'teacher_evaluations.view', 'teacher_evaluations.create', 'teacher_evaluations.edit',
                // Documents
                'settings.view', 'id_cards.generate', 'certificates.generate',
                // Library
                'library.view', 'library.manage',
                // Communication (calendar view only, cannot add school-wide events)
                'calendar.view', 'calendar.manage', 'chat.access', 'notifications.view',
                'announcements.view',
                // Departments (view only)
                'departments.view',
                // User access management
                'user_access.view',
                // Enrollment
                'students.manage',
                // Fees (view only for own branch)
                'fees.view', 'fee_payments.view',
            ];

            if (!empty($permissions)) {
                $hasAccess = false;
                foreach ($permissions as $perm) {
                    if (in_array($perm, $branchPrincipalAllowedPermissions)) {
                        $hasAccess = true;
                        break;
                    }
                }
                if (!$hasAccess) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'You do not have access to this section.',
                            'required_any' => $permissions,
                        ], 403);
                    }
                    // Redirect to dashboard with error message instead of 403 page
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'You do not have permission to access this section.');
                }
            }

            return $next($request);
        }

        // Department Head — reviews exam questions from teachers in their department
        if ($user->role === 'department_head') {
            $departmentHeadAllowedPermissions = [
                'dashboard.view',
                // Academic
                'subjects.view', 'subject_assignments.view',
                'exams.view', 'classrooms.view', 'sections.view',
                // Marks (view)
                'mark_entries.view',
                'mark_sheets.view',
                // People
                'students.view', 'teachers.view',
                // Lesson Plans
                'lesson_plans.view', 'lesson_plans.review',
                // Exam Questions (full dept review access)
                'exam_questions.view', 'exam_questions.create', 'exam_questions.edit', 'exam_questions.dept_review',
                // Teacher Evaluation
                'teacher_evaluations.view', 'teacher_evaluations.create', 'teacher_evaluations.edit',
                // Departments
                'departments.view',
                // Documents
                'settings.view',
                // Library
                'library.view',
                // Communication
                'calendar.view', 'chat.access', 'notifications.view',
            ];

            if (!empty($permissions)) {
                $hasAccess = false;
                foreach ($permissions as $perm) {
                    if (in_array($perm, $departmentHeadAllowedPermissions)) {
                        $hasAccess = true;
                        break;
                    }
                }
                if (!$hasAccess) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'You do not have access to this section.',
                            'required_any' => $permissions,
                        ], 403);
                    }
                    // Redirect to dashboard with error message instead of 403 page
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'You do not have permission to access this section.');
                }
            }

            return $next($request);
        }

        // General manager — broad access except system admin
        if ($user->role === 'general_manager') {
            $gmBlockedPermissions = [
                'settings.edit', 'roles.view', 'roles.edit',
                'database_backup', 'backup', 'audits.view',
            ];

            if (!empty($permissions)) {
                foreach ($permissions as $perm) {
                    if (in_array($perm, $gmBlockedPermissions)) {
                        if ($request->expectsJson()) {
                            return response()->json([
                                'message' => 'You do not have access to this section.',
                            ], 403);
                        }
                        // Redirect to dashboard with error message instead of 403 page
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'You do not have permission to access this section.');
                    }
                }
            }

            return $next($request);
        }

        // Librarian — only library access
        if ($user->role === 'librarian') {
            $librarianAllowedPermissions = [
                'dashboard.view', 'library.view', 'library.manage', 'chat.access', 'notifications.view', 'calendar.view',
            ];

            if (!empty($permissions)) {
                $hasAccess = false;
                foreach ($permissions as $perm) {
                    if (in_array($perm, $librarianAllowedPermissions)) {
                        $hasAccess = true;
                        break;
                    }
                }
                if (!$hasAccess) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Librarians do not have access to this section.'], 403);
                    }
                    // Redirect to dashboard with error message instead of 403 page
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'You do not have permission to access this section.');
                }
            }

            return $next($request);
        }

        // Cashier — finance access only
        if ($user->role === 'cashier') {
            $cashierAllowedPermissions = [
                'dashboard.view', 'fees.view', 'fee_payments.view', 'fee_payments.create',
                'students.view', 'chat.access', 'notifications.view', 'calendar.view',
            ];

            if (!empty($permissions)) {
                $hasAccess = false;
                foreach ($permissions as $perm) {
                    if (in_array($perm, $cashierAllowedPermissions)) {
                        $hasAccess = true;
                        break;
                    }
                }
                if (!$hasAccess) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Cashiers do not have access to this section.'], 403);
                    }
                    // Redirect to dashboard with error message instead of 403 page
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'You do not have permission to access this section.');
                }
            }

            return $next($request);
        }

        // Registrar — student and parent management
        if ($user->role === 'registrar') {
            $registrarAllowedPermissions = [
                'dashboard.view', 'students.view', 'students.create', 'students.edit',
                'parents.view', 'parents.create', 'parents.edit',
                'academic_years.view', 'terms.view', 'classrooms.view', 'sections.view',
                'fees.view', 'fee_payments.view', 'fee_payments.create',
                'id_cards.generate', 'certificates.generate',
                'chat.access', 'notifications.view', 'calendar.view',
            ];

            if (!empty($permissions)) {
                $hasAccess = false;
                foreach ($permissions as $perm) {
                    if (in_array($perm, $registrarAllowedPermissions)) {
                        $hasAccess = true;
                        break;
                    }
                }
                if (!$hasAccess) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Registrars do not have access to this section.'], 403);
                    }
                    // Redirect to dashboard with error message instead of 403 page
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'You do not have permission to access this section.');
                }
            }

            return $next($request);
        }

        // Finance — full finance & budgeting access, limited other access
        if ($user->role === 'finance') {
            $financeAllowedPermissions = [
                'dashboard.view',
                // Finance
                'fees.view', 'fee_payments.view', 'fee_payments.create',
                'budgets.view', 'budgets.manage',
                'income_expenses.view', 'income_expenses.manage',
                'finance_statements.view',
                'payrolls.view',
                // People (view only)
                'students.view', 'teachers.view',
                // Documents
                'settings.view',
                // Communication
                'chat.access', 'notifications.view', 'calendar.view',
            ];

            if (!empty($permissions)) {
                $hasAccess = false;
                foreach ($permissions as $perm) {
                    if (in_array($perm, $financeAllowedPermissions)) {
                        $hasAccess = true;
                        break;
                    }
                }
                if (!$hasAccess) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Finance officers do not have access to this section.'], 403);
                    }
                    // Redirect to dashboard with error message instead of 403 page
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'You do not have permission to access this section.');
                }
            }

            return $next($request);
        }

        // HR — employee management, leaves, payroll, trainings
        if ($user->role === 'hr') {
            $hrAllowedPermissions = [
                'dashboard.view',
                // People
                'students.view', 'teachers.view', 'staff.view',
                // HR
                'leaves.view', 'leaves.manage',
                'payrolls.view', 'payrolls.manage',
                'trainings.view', 'trainings.manage',
                'employee_assets.view',
                // Communication
                'chat.access', 'notifications.view', 'calendar.view',
            ];

            if (!empty($permissions)) {
                $hasAccess = false;
                foreach ($permissions as $perm) {
                    if (in_array($perm, $hrAllowedPermissions)) {
                        $hasAccess = true;
                        break;
                    }
                }
                if (!$hasAccess) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'HR officers do not have access to this section.'], 403);
                    }
                    // Redirect to dashboard with error message instead of 403 page
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'You do not have permission to access this section.');
                }
            }

            return $next($request);
        }

        // Staff role — similar restricted access
        if ($user->role === 'staff') {
            // Staff gets broader access than teachers but not full admin
            // For now, let them through to all permission-protected routes
            return $next($request);
        }

        // Student role — only access student portal routes (which don't use this middleware)
        if ($user->role === 'student') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Students do not have access to this section.',
                ], 403);
            }
            return redirect()->route('student.dashboard')
                ->with('error', 'You do not have permission to access this section.');
        }

        // Parent role — only access parent portal routes (which don't use this middleware)
        if ($user->role === 'parent') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Parents do not have access to this section.',
                ], 403);
            }
            return redirect()->route('parent.dashboard')
                ->with('error', 'You do not have permission to access this section.');
        }

        // Default: no permissions set, no recognized role — fall through to permission check
        if (empty($permissions)) {
            return $next($request);
        }

        // Check if any permission uses & (ALL match mode)
        $firstPerm = $permissions[0] ?? '';
        if (str_contains($firstPerm, '&')) {
            $allPerms = explode('&', $firstPerm);
            if (!$user->hasAllPermissions($allPerms)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'You do not have all required permissions.',
                        'required' => $allPerms,
                    ], 403);
                }
                return redirect()->route('admin.dashboard')
                    ->with('error', 'You do not have all required permissions to access this section.');
            }
        } else {
            // ANY match mode (default)
            if (!$user->hasAnyPermission($permissions)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'You do not have permission to perform this action.',
                        'required_any' => $permissions,
                    ], 403);
                }
                return redirect()->route('admin.dashboard')
                    ->with('error', 'You do not have permission to access this section.');
            }
        }

        return $next($request);
    }
}
