<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('app.dashboard')) - Redemption School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<div class="admin-wrapper">
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <div class="sidebar-brand-icon"><i class="fas fa-graduation-cap"></i></div>
                <div class="sidebar-brand-text">
                    <span class="sidebar-brand-pre">{{ __('app.brand_pre') }}</span>
                    <span class="sidebar-brand-name">{{ __('app.brand_name') }}</span>
                </div>
            </a>
        </div>
        @php
            $isTeacher = Auth::user()->role === 'teacher';
            $isAdmin = in_array(Auth::user()->role, ['admin', 'super_admin']);
            $isBranchPrincipal = Auth::user()->role === 'branch_principal';
            $isGeneralManager = Auth::user()->role === 'general_manager';
            $isLibrarian = Auth::user()->role === 'librarian';
            $isCashier = Auth::user()->role === 'cashier';
            $isRegistrar = Auth::user()->role === 'registrar';

            // Menu level determines which sidebar sections are shown
            $menuLevel = 'full'; // default for admin/super_admin
            if ($isTeacher) $menuLevel = 'teacher';
            elseif ($isLibrarian) $menuLevel = 'librarian';
            elseif ($isCashier) $menuLevel = 'cashier';
            elseif ($isRegistrar) $menuLevel = 'registrar';
            elseif ($isBranchPrincipal) $menuLevel = 'branch_principal';
            elseif ($isGeneralManager) $menuLevel = 'general_manager';

            // Check if teacher is a homeroom teacher for any class/section
            $isHomeroomTeacher = false;
            if ($isTeacher) {
                $teacherUser = Auth::user();
                $teacherModel = \App\Models\Teacher::where('user_id', $teacherUser->id)->first();
                if (!$teacherModel) {
                    $teacherModel = \App\Models\Teacher::where('email', $teacherUser->email)->first();
                }
                if ($teacherModel) {
                    $isHomeroomTeacher = $teacherModel->classRooms()->exists() || $teacherModel->sections()->exists();
                }
            }

            // Route groups for active state detection
            $academicSetupRoutes = ['admin.academic-years.*','admin.terms.*','admin.subjects.*','admin.subject-assignments.*','admin.exams.*','admin.classrooms.*','admin.sections.*'];
            $academicMarksRoutes = ['admin.mark-entries.*','admin.mark-sheet.*','admin.mark-sheet-full.*','admin.mark-roster.*'];
            $academicReportsRoutes = ['admin.report-card.*','admin.progress-reports.*','admin.performance-reports.*'];
            $peopleRoutes = ['admin.students.*','admin.teachers.*','admin.staff.*','admin.team-members.*','admin.parents.*','admin.teacher-assignments.*'];
            $financeRoutes = ['admin.fees.*','admin.fee-payments.*','admin.payrolls.*','admin.budgets.*','admin.income-expenses.*','admin.finance-statements.*','admin.budget-comparison.*','admin.financial-comparison.*'];
            $hrRoutes = ['admin.leaves.*','admin.employee-assets.*'];
            $analysisRoutes = ['admin.performance-analysis.*','admin.performance-comparison.*','admin.psychological-analysis.*','admin.performance.*'];
            $documentRoutes = ['admin.id-card-generate.*','admin.certificate-generate.*','admin.id-cards.*','admin.certificates.*','admin.report-exchange.*'];
            $libraryRoutes = ['admin.library.*'];
            $commRoutes = ['admin.calendar.*','admin.announcements.*','admin.telegram.*','admin.chat.*'];
            $websiteRoutes = ['admin.sliders.*','admin.gallery-*','admin.branches.*','admin.contact-messages.*','admin.web-content.*','admin.news.*'];
            $adminRoutes = ['admin.user-access.*','admin.settings.*','admin.roles.*','admin.database-backup.*','admin.backup.*','admin.audits.*'];

            $isAcademicActive = request()->routeIs([...$academicSetupRoutes, ...$academicMarksRoutes, ...$academicReportsRoutes]);
            $isPeopleActive = request()->routeIs($peopleRoutes);
            $isFinanceActive = request()->routeIs([...$financeRoutes, ...$hrRoutes]);
            $isAnalysisActive = request()->routeIs($analysisRoutes);
            $isDocumentActive = request()->routeIs($documentRoutes);
            $isLibraryActive = request()->routeIs($libraryRoutes);
            $isCommActive = request()->routeIs($commRoutes);
            $isWebsiteActive = request()->routeIs($websiteRoutes);
            $isAdminActive = request()->routeIs($adminRoutes);
        @endphp
        <div class="sidebar-menu-wrap">
            <ul class="sidebar-menu">
                {{-- DASHBOARD --}}
                <li class="menu-header">MAIN</li>
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fas fa-th-large"></i><span>{{ __('app.dashboard') }}</span></a>
                </li>

                {{-- ACADEMIC MANAGEMENT --}}
                @if($menuLevel === 'teacher')
                <li class="menu-header">ACADEMIC</li>
                <li class="{{ $isAcademicActive ? 'has-active-child' : '' }}">
                    <a href="#academicSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-graduation-cap"></i><span>Marks & Assessment</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isAcademicActive ? 'show' : '' }}" id="academicSubmenu">
                        <li><a href="{{ route('admin.mark-entries.index') }}" class="{{ request()->routeIs('admin.mark-entries.*') ? 'active' : '' }}"><i class="fas fa-pen"></i> Mark Entry</a></li>
                        @if($isHomeroomTeacher)
                        <li><a href="{{ route('admin.mark-sheet.index') }}" class="{{ request()->routeIs('admin.mark-sheet.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Mark Sheet</a></li>
                        <li><a href="{{ route('admin.mark-sheet-full.index') }}" class="{{ request()->routeIs('admin.mark-sheet-full.*') ? 'active' : '' }}"><i class="fas fa-table"></i> Full Mark Sheet</a></li>
                        @endif
                        <li><a href="{{ route('admin.mark-roster.index') }}" class="{{ request()->routeIs('admin.mark-roster.*') ? 'active' : '' }}"><i class="fas fa-list-ol"></i> Mark Roster</a></li>
                    </ul>
                </li>
                @elseif($menuLevel === 'librarian')
                {{-- Librarian: no academic section --}}
                @elseif($menuLevel === 'cashier')
                {{-- Cashier: no academic section --}}
                @elseif($menuLevel === 'registrar')
                <li class="menu-header">ACADEMIC</li>
                <li class="{{ $isAcademicActive ? 'has-active-child' : '' }}">
                    <a href="#academicSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-graduation-cap"></i><span>Academic Setup</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isAcademicActive ? 'show' : '' }}" id="academicSubmenu">
                        <li><a href="{{ route('admin.academic-years.index') }}" class="{{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}"><i class="fas fa-calendar"></i> Academic Years</a></li>
                        <li><a href="{{ route('admin.terms.index') }}" class="{{ request()->routeIs('admin.terms.*') ? 'active' : '' }}"><i class="fas fa-bookmark"></i> Terms</a></li>
                        <li><a href="{{ route('admin.classrooms.index') }}" class="{{ request()->routeIs('admin.classrooms.*') ? 'active' : '' }}"><i class="fas fa-building"></i> Classes & Sections</a></li>
                    </ul>
                </li>
                @elseif($menuLevel === 'branch_principal')
                <li class="menu-header">ACADEMIC</li>
                <li class="{{ $isAcademicActive ? 'has-active-child' : '' }}">
                    <a href="#academicSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-graduation-cap"></i><span>Academic Management</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isAcademicActive ? 'show' : '' }}" id="academicSubmenu">
                        <li style="padding:4px 12px 2px;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;">Setup</li>
                        <li><a href="{{ route('admin.academic-years.index') }}" class="{{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}"><i class="fas fa-calendar"></i> Academic Years</a></li>
                        <li><a href="{{ route('admin.terms.index') }}" class="{{ request()->routeIs('admin.terms.*') ? 'active' : '' }}"><i class="fas fa-bookmark"></i> Terms</a></li>
                        <li><a href="{{ route('admin.subjects.index') }}" class="{{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}"><i class="fas fa-book"></i> Subjects</a></li>
                        <li><a href="{{ route('admin.subject-assignments.index') }}" class="{{ request()->routeIs('admin.subject-assignments.*') ? 'active' : '' }}"><i class="fas fa-link"></i> Assign Subjects</a></li>
                        <li><a href="{{ route('admin.exams.index') }}" class="{{ request()->routeIs('admin.exams.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Exams</a></li>
                        <li><a href="{{ route('admin.classrooms.index') }}" class="{{ request()->routeIs('admin.classrooms.*') ? 'active' : '' }}"><i class="fas fa-building"></i> Classes & Sections</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Marks & Assessment</li>
                        <li><a href="{{ route('admin.mark-entries.index') }}" class="{{ request()->routeIs('admin.mark-entries.*') ? 'active' : '' }}"><i class="fas fa-pen"></i> Mark Entry</a></li>
                        <li><a href="{{ route('admin.mark-sheet.index') }}" class="{{ request()->routeIs('admin.mark-sheet.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Mark Sheet</a></li>
                        <li><a href="{{ route('admin.mark-sheet-full.index') }}" class="{{ request()->routeIs('admin.mark-sheet-full.*') ? 'active' : '' }}"><i class="fas fa-table"></i> Full Mark Sheet</a></li>
                        <li><a href="{{ route('admin.mark-roster.index') }}" class="{{ request()->routeIs('admin.mark-roster.*') ? 'active' : '' }}"><i class="fas fa-list-ol"></i> Mark Roster</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Reports</li>
                        <li><a href="{{ route('admin.report-card.index') }}" class="{{ request()->routeIs('admin.report-card.*') ? 'active' : '' }}"><i class="fas fa-id-card"></i> Report Cards</a></li>
                        <li><a href="{{ route('admin.progress-reports.index') }}" class="{{ request()->routeIs('admin.progress-reports.*') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> Progress Reports</a></li>
                    </ul>
                </li>
                @else
                <li class="menu-header">ACADEMIC</li>
                <li class="{{ $isAcademicActive ? 'has-active-child' : '' }}">
                    <a href="#academicSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-graduation-cap"></i><span>Academic Management</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isAcademicActive ? 'show' : '' }}" id="academicSubmenu">
                        <li style="padding:4px 12px 2px;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;">Setup</li>
                        <li><a href="{{ route('admin.academic-years.index') }}" class="{{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}"><i class="fas fa-calendar"></i> Academic Years</a></li>
                        <li><a href="{{ route('admin.terms.index') }}" class="{{ request()->routeIs('admin.terms.*') ? 'active' : '' }}"><i class="fas fa-bookmark"></i> Terms</a></li>
                        <li><a href="{{ route('admin.subjects.index') }}" class="{{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}"><i class="fas fa-book"></i> Subjects</a></li>
                        <li><a href="{{ route('admin.subject-assignments.index') }}" class="{{ request()->routeIs('admin.subject-assignments.*') ? 'active' : '' }}"><i class="fas fa-link"></i> Assign Subjects</a></li>
                        <li><a href="{{ route('admin.exams.index') }}" class="{{ request()->routeIs('admin.exams.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Exams</a></li>
                        <li><a href="{{ route('admin.classrooms.index') }}" class="{{ request()->routeIs('admin.classrooms.*') ? 'active' : '' }}"><i class="fas fa-building"></i> Classes & Sections</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Marks & Assessment</li>
                        <li><a href="{{ route('admin.mark-entries.index') }}" class="{{ request()->routeIs('admin.mark-entries.*') ? 'active' : '' }}"><i class="fas fa-pen"></i> Mark Entry</a></li>
                        <li><a href="{{ route('admin.mark-sheet.index') }}" class="{{ request()->routeIs('admin.mark-sheet.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Mark Sheet</a></li>
                        <li><a href="{{ route('admin.mark-sheet-full.index') }}" class="{{ request()->routeIs('admin.mark-sheet-full.*') ? 'active' : '' }}"><i class="fas fa-table"></i> Full Mark Sheet</a></li>
                        <li><a href="{{ route('admin.mark-roster.index') }}" class="{{ request()->routeIs('admin.mark-roster.*') ? 'active' : '' }}"><i class="fas fa-list-ol"></i> Mark Roster</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Reports</li>
                        <li><a href="{{ route('admin.report-card.index') }}" class="{{ request()->routeIs('admin.report-card.*') ? 'active' : '' }}"><i class="fas fa-id-card"></i> Report Cards</a></li>
                        <li><a href="{{ route('admin.progress-reports.index') }}" class="{{ request()->routeIs('admin.progress-reports.*') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> Progress Reports</a></li>
                    </ul>
                </li>
                @endif

                {{-- PEOPLE MANAGEMENT --}}
                @if(in_array($menuLevel, ['full', 'general_manager', 'branch_principal']))
                <li class="menu-header">PEOPLE</li>
                <li class="{{ $isPeopleActive ? 'has-active-child' : '' }}">
                    <a href="#peopleSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-users"></i><span>People Management</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isPeopleActive ? 'show' : '' }}" id="peopleSubmenu">
                        <li><a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}"><i class="fas fa-user-graduate"></i> Students</a></li>
                        <li><a href="{{ route('admin.teachers.index') }}" class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                        <li><a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}"><i class="fas fa-id-badge"></i> Staff</a></li>
                        <li><a href="{{ route('admin.parents.index') }}" class="{{ request()->routeIs('admin.parents.*') ? 'active' : '' }}"><i class="fas fa-user-friends"></i> Parents</a></li>
                        <li><a href="{{ route('admin.team-members.index') }}" class="{{ request()->routeIs('admin.team-members.*') ? 'active' : '' }}"><i class="fas fa-users-cog"></i> Team Members</a></li>
                        <li><a href="{{ route('admin.teacher-assignments.index') }}" class="{{ request()->routeIs('admin.teacher-assignments.*') ? 'active' : '' }}"><i class="fas fa-chalkboard"></i> Teacher Assignments</a></li>
                    </ul>
                </li>
                @elseif($menuLevel === 'registrar')
                <li class="menu-header">PEOPLE</li>
                <li class="{{ $isPeopleActive ? 'has-active-child' : '' }}">
                    <a href="#peopleSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-users"></i><span>People Management</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isPeopleActive ? 'show' : '' }}" id="peopleSubmenu">
                        <li><a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}"><i class="fas fa-user-graduate"></i> Students</a></li>
                        <li><a href="{{ route('admin.parents.index') }}" class="{{ request()->routeIs('admin.parents.*') ? 'active' : '' }}"><i class="fas fa-user-friends"></i> Parents</a></li>
                    </ul>
                </li>
                @endif

                {{-- FINANCE & HR --}}
                @if(in_array($menuLevel, ['full', 'general_manager']))
                <li class="menu-header">FINANCE & HR</li>
                <li class="{{ $isFinanceActive ? 'has-active-child' : '' }}">
                    <a href="#financeSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-wallet"></i><span>Finance & HR</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isFinanceActive ? 'show' : '' }}" id="financeSubmenu">
                        <li style="padding:4px 12px 2px;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;">Fee & Payment</li>
                        <li><a href="{{ route('admin.fees.index') }}" class="{{ request()->routeIs('admin.fees.*') ? 'active' : '' }}"><i class="fas fa-money-bill-wave"></i> Fee Structure</a></li>
                        <li><a href="{{ route('admin.fee-payments.index') }}" class="{{ request()->routeIs('admin.fee-payments.*') ? 'active' : '' }}"><i class="fas fa-credit-card"></i> Payments</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Budget & Accounts</li>
                        <li><a href="{{ route('admin.budgets.index') }}" class="{{ request()->routeIs('admin.budgets.*') ? 'active' : '' }}"><i class="fas fa-chart-pie"></i> Budgets</a></li>
                        <li><a href="{{ route('admin.income-expenses.index') }}" class="{{ request()->routeIs('admin.income-expenses.*') ? 'active' : '' }}"><i class="fas fa-exchange-alt"></i> Income / Expense</a></li>
                        <li><a href="{{ route('admin.finance-statements.index') }}" class="{{ request()->routeIs('admin.finance-statements.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> Statements</a></li>
                        <li><a href="{{ route('admin.budget-comparison.index') }}" class="{{ request()->routeIs('admin.budget-comparison.*') ? 'active' : '' }}"><i class="fas fa-balance-scale"></i> Budget Comparison</a></li>
                        <li><a href="{{ route('admin.financial-comparison.index') }}" class="{{ request()->routeIs('admin.financial-comparison.*') ? 'active' : '' }}"><i class="fas fa-chart-bar"></i> Financial Comparison</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Payroll & HR</li>
                        <li><a href="{{ route('admin.payrolls.index') }}" class="{{ request()->routeIs('admin.payrolls.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> Payroll</a></li>
                        <li><a href="{{ route('admin.leaves.index') }}" class="{{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}"><i class="fas fa-calendar-minus"></i> Leaves</a></li>
                        <li><a href="{{ route('admin.employee-assets.index') }}" class="{{ request()->routeIs('admin.employee-assets.*') ? 'active' : '' }}"><i class="fas fa-boxes"></i> Employee Assets</a></li>
                    </ul>
                </li>
                @elseif($menuLevel === 'cashier')
                <li class="menu-header">FINANCE</li>
                <li class="{{ $isFinanceActive ? 'has-active-child' : '' }}">
                    <a href="#financeSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-wallet"></i><span>Finance</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isFinanceActive ? 'show' : '' }}" id="financeSubmenu">
                        <li><a href="{{ route('admin.fees.index') }}" class="{{ request()->routeIs('admin.fees.*') ? 'active' : '' }}"><i class="fas fa-money-bill-wave"></i> Fee Structure</a></li>
                        <li><a href="{{ route('admin.fee-payments.index') }}" class="{{ request()->routeIs('admin.fee-payments.*') ? 'active' : '' }}"><i class="fas fa-credit-card"></i> Payments</a></li>
                    </ul>
                </li>
                @elseif($menuLevel === 'registrar')
                <li class="menu-header">FINANCE</li>
                <li class="{{ $isFinanceActive ? 'has-active-child' : '' }}">
                    <a href="#financeSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-wallet"></i><span>Finance</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isFinanceActive ? 'show' : '' }}" id="financeSubmenu">
                        <li><a href="{{ route('admin.fees.index') }}" class="{{ request()->routeIs('admin.fees.*') ? 'active' : '' }}"><i class="fas fa-money-bill-wave"></i> Fee Structure</a></li>
                        <li><a href="{{ route('admin.fee-payments.index') }}" class="{{ request()->routeIs('admin.fee-payments.*') ? 'active' : '' }}"><i class="fas fa-credit-card"></i> Payments</a></li>
                    </ul>
                </li>
                @endif

                {{-- ANALYSIS & INSIGHTS --}}
                @if(in_array($menuLevel, ['full', 'general_manager', 'branch_principal']))
                <li class="menu-header">ANALYTICS</li>
                <li class="{{ $isAnalysisActive ? 'has-active-child' : '' }}">
                    <a href="#analysisSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-chart-line"></i><span>Analysis & Insights</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isAnalysisActive ? 'show' : '' }}" id="analysisSubmenu">
                        <li><a href="{{ route('admin.performance.index') }}" class="{{ request()->routeIs('admin.performance.index') ? 'active' : '' }}"><i class="fas fa-tachometer-alt"></i> Performance Dashboard</a></li>
                        <li><a href="{{ route('admin.performance.at-risk') }}" class="{{ request()->routeIs('admin.performance.at-risk') ? 'active' : '' }}"><i class="fas fa-exclamation-triangle"></i> At-Risk Students</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Comparisons</li>
                        <li><a href="{{ route('admin.performance.class-comparison') }}" class="{{ request()->routeIs('admin.performance.class-comparison') ? 'active' : '' }}"><i class="fas fa-code-compare"></i> Class Comparison</a></li>
                        <li><a href="{{ route('admin.performance.branch-comparison') }}" class="{{ request()->routeIs('admin.performance.branch-comparison') ? 'active' : '' }}"><i class="fas fa-building"></i> Branch Comparison</a></li>
                        <li><a href="{{ route('admin.performance.gender') }}" class="{{ request()->routeIs('admin.performance.gender') ? 'active' : '' }}"><i class="fas fa-venus-mars"></i> Gender Analysis</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Advanced</li>
                        <li><a href="{{ route('admin.performance-analysis.index') }}" class="{{ request()->routeIs('admin.performance-analysis.*') ? 'active' : '' }}"><i class="fas fa-chart-bar"></i> Performance Analysis</a></li>
                        <li><a href="{{ route('admin.performance-comparison.index') }}" class="{{ request()->routeIs('admin.performance-comparison.*') ? 'active' : '' }}"><i class="fas fa-code-compare"></i> Branch Performance</a></li>
                        <li><a href="{{ route('admin.psychological-analysis.index') }}" class="{{ request()->routeIs('admin.psychological-analysis.*') ? 'active' : '' }}"><i class="fas fa-brain"></i> Psychological Analysis</a></li>
                    </ul>
                </li>
                @endif

                {{-- DOCUMENT CENTER --}}
                @if($menuLevel === 'teacher')
                <li class="menu-header">DOCUMENTS</li>
                <li class="{{ request()->routeIs('admin.report-exchange.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.report-exchange.index') }}" class="{{ request()->routeIs('admin.report-exchange.*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt"></i><span>Report Exchange</span>
                    </a>
                </li>
                @elseif(in_array($menuLevel, ['full', 'general_manager', 'branch_principal', 'registrar']))
                <li class="menu-header">DOCUMENTS</li>
                <li class="{{ $isDocumentActive ? 'has-active-child' : '' }}">
                    <a href="#documentSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-folder-open"></i><span>Document Center</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isDocumentActive ? 'show' : '' }}" id="documentSubmenu">
                        <li><a href="{{ route('admin.id-card-generate.index') }}" class="{{ request()->routeIs('admin.id-card-generate.*') ? 'active' : '' }}"><i class="fas fa-id-badge"></i> ID Card Generator</a></li>
                        <li><a href="{{ route('admin.certificate-generate.index') }}" class="{{ request()->routeIs('admin.certificate-generate.*') ? 'active' : '' }}"><i class="fas fa-award"></i> Certificate Generator</a></li>
                        <li><a href="{{ route('admin.report-exchange.index') }}" class="{{ request()->routeIs('admin.report-exchange.*') ? 'active' : '' }}"><i class="fas fa-exchange-alt"></i> Report Exchange</a></li>
                    </ul>
                </li>
                @endif
                @if(in_array($menuLevel, ['full', 'general_manager', 'branch_principal', 'teacher', 'librarian']))
                <li class="{{ $isLibraryActive ? 'active' : '' }}">
                    <a href="{{ route('admin.library.index') }}" class="{{ $isLibraryActive ? 'active' : '' }}"><i class="fas fa-book-open"></i><span>Digital Library</span></a>
                </li>
                @endif

                {{-- COMMUNICATION --}}
                <li class="menu-header">COMMUNICATION</li>
                <li class="{{ $isCommActive ? 'has-active-child' : '' }}">
                    @if(in_array($menuLevel, ['teacher', 'librarian', 'cashier']))
                    <a href="{{ route('admin.calendar.index') }}" class="{{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt"></i><span>Calendar & Announcements</span>
                    </a>
                    @else
                    <a href="#commSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-broadcast-tower"></i><span>Communication</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isCommActive ? 'show' : '' }}" id="commSubmenu">
                        <li><a href="{{ route('admin.calendar.index') }}" class="{{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}"><i class="fas fa-calendar-alt"></i> Academic Calendar</a></li>
                        <li><a href="{{ route('admin.announcements.index') }}" class="{{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}"><i class="fas fa-bullhorn"></i> Announcements</a></li>
                        <li><a href="{{ route('admin.chat.index') }}" class="{{ request()->routeIs('admin.chat.*') ? 'active' : '' }}"><i class="fas fa-comment-dots"></i> Chat</a></li>
                        <li><a href="{{ route('admin.telegram.index') }}" class="{{ request()->routeIs('admin.telegram.*') ? 'active' : '' }}"><i class="fab fa-telegram"></i> Telegram</a></li>
                    </ul>
                    @endif
                </li>

                {{-- WEBSITE --}}
                @if(in_array($menuLevel, ['full', 'general_manager']))
                <li class="menu-header">WEBSITE</li>
                <li class="{{ $isWebsiteActive ? 'has-active-child' : '' }}">
                    <a href="#websiteSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-globe"></i><span>Website Management</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isWebsiteActive ? 'show' : '' }}" id="websiteSubmenu">
                        <li><a href="{{ route('admin.branches.index') }}" class="{{ request()->routeIs('admin.branches.*') ? 'active' : '' }}"><i class="fas fa-map-marker-alt"></i> Branches</a></li>
                        <li><a href="{{ route('admin.web-content.index') }}" class="{{ request()->routeIs('admin.web-content.*') ? 'active' : '' }}"><i class="fas fa-paint-brush"></i> Web Content</a></li>
                        <li><a href="{{ route('admin.sliders.index') }}" class="{{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}"><i class="fas fa-images"></i> Sliders</a></li>
                        <li><a href="{{ route('admin.gallery-images.index') }}" class="{{ request()->routeIs('admin.gallery-images.*') ? 'active' : '' }}"><i class="fas fa-image"></i> Gallery Images</a></li>
                        <li><a href="{{ route('admin.gallery-videos.index') }}" class="{{ request()->routeIs('admin.gallery-videos.*') ? 'active' : '' }}"><i class="fas fa-video"></i> Gallery Videos</a></li>
                        <li><a href="{{ route('admin.news.index') }}" class="{{ request()->routeIs('admin.news.*') ? 'active' : '' }}"><i class="fas fa-newspaper"></i> News</a></li>
                        <li><a href="{{ route('admin.contact-messages.index') }}" class="{{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}"><i class="fas fa-envelope"></i> Messages</a></li>
                    </ul>
                </li>
                @endif

                {{-- ADMINISTRATION --}}
                @if(in_array($menuLevel, ['full', 'general_manager']))
                <li class="menu-header">ADMINISTRATION</li>
                <li class="{{ $isAdminActive ? 'has-active-child' : '' }}">
                    <a href="#adminSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-cogs"></i><span>System Admin</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isAdminActive ? 'show' : '' }}" id="adminSubmenu">
                        <li style="padding:4px 12px 2px;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;">Access Control</li>
                        <li><a href="{{ route('admin.user-access.teachers') }}" class="{{ request()->routeIs('admin.user-access.teachers*') ? 'active' : '' }}"><i class="fas fa-chalkboard-teacher"></i> Teacher Access</a></li>
                        <li><a href="{{ route('admin.user-access.students') }}" class="{{ request()->routeIs('admin.user-access.students*') ? 'active' : '' }}"><i class="fas fa-user-graduate"></i> Student Access</a></li>
                        <li><a href="{{ route('admin.user-access.parents') }}" class="{{ request()->routeIs('admin.user-access.parents*') ? 'active' : '' }}"><i class="fas fa-user-friends"></i> Parent Access</a></li>
                        @if($menuLevel === 'full')
                        <li><a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"><i class="fas fa-shield-alt"></i> Roles & Permissions</a></li>
                        @endif
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Configuration</li>
                        <li><a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><i class="fas fa-cog"></i> Settings</a></li>
                        @if($menuLevel === 'full')
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Data & Backup</li>
                        <li><a href="{{ route('admin.database-backup.index') }}" class="{{ request()->routeIs('admin.database-backup.*') ? 'active' : '' }}"><i class="fas fa-database"></i> Database Export</a></li>
                        <li><a href="{{ route('admin.backup.index') }}" class="{{ request()->routeIs('admin.backup.*') ? 'active' : '' }}"><i class="fas fa-clock"></i> Scheduled Backup</a></li>
                        <li><a href="{{ route('admin.audits.index') }}" class="{{ request()->routeIs('admin.audits.*') ? 'active' : '' }}"><i class="fas fa-clipboard-list"></i> Audit Log</a></li>
                        @endif
                    </ul>
                </li>
                @endif
            </ul>
        </div>
        <div class="sidebar-footer">
            <div class="sidebar-footer-user">
                <div class="sidebar-footer-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="sidebar-footer-info">
                    <span class="sidebar-footer-name">{{ Auth::user()->name }}</span>
                    <span class="sidebar-footer-role">{{ Auth::user()->display_role }}</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="sidebar-backdrop d-none" id="sidebarBackdrop"></div>
    <div class="admin-main">
        {{-- Announcement Banner --}}
        @php
            $activeAnnouncements = collect();
            try {
                $activeAnnouncements = \App\Models\CalendarEvent::where('is_announcement', true)
                    ->where(function($q) {
                        $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
                    })
                    ->orderBy('start_date', 'desc')
                    ->limit(5)
                    ->get();
            } catch (\Exception $e) {}
        @endphp
        @if($activeAnnouncements->count() > 0)
        <div id="adminAnnouncementBar" class="announcement-banner">
            <div class="announcement-banner-inner">
                <div class="announcement-banner-header">
                    <span class="announcement-badge"><i class="fas fa-bullhorn"></i> ANNOUNCEMENTS</span>
                    <button onclick="document.getElementById('adminAnnouncementBar').style.display='none'" class="announcement-close"><i class="fas fa-times"></i></button>
                </div>
                <div class="announcement-list">
                    @foreach($activeAnnouncements as $ann)
                    <div class="announcement-item">
                        <div class="announcement-item-header">
                            <div class="announcement-title">{{ $ann->title }}</div>
                            @if($ann->category)
                                <span class="announcement-category">{{ ucfirst($ann->category) }}</span>
                            @endif
                        </div>
                        @if($ann->description)
                            <div class="announcement-desc">{{ strip_tags($ann->description) }}</div>
                        @endif
                        <div class="announcement-meta">
                            @if($ann->start_date)
                                <span class="announcement-date"><i class="fas fa-calendar-alt"></i> {{ $ann->start_date->format('M d, Y') }}</span>
                            @endif
                            @if($ann->start_time)
                                <span class="announcement-time"><i class="fas fa-clock"></i> {{ $ann->start_time->format('h:i A') }}</span>
                            @endif
                            @if($ann->end_date)
                                <span class="announcement-end-date"><i class="fas fa-calendar-check"></i> Until {{ $ann->end_date->format('M d, Y') }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <style>
        .announcement-banner {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: #fff;
            position: relative;
            z-index: 60;
            padding: 0;
        }
        .announcement-banner-inner {
            max-width: 100%;
            padding: 16px 24px;
        }
        .announcement-banner-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .announcement-badge {
            font-weight: 700;
            font-size: .85rem;
            background: rgba(255,255,255,.2);
            padding: 4px 14px;
            border-radius: 6px;
            white-space: nowrap;
            letter-spacing: 0.5px;
        }
        .announcement-close {
            background: none;
            border: none;
            color: rgba(255,255,255,.7);
            cursor: pointer;
            font-size: 18px;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all .2s;
        }
        .announcement-close:hover {
            background: rgba(255,255,255,.15);
            color: #fff;
        }
        .announcement-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .announcement-item {
            width: 100%;
            background: rgba(255,255,255,.12);
            border-radius: 12px;
            padding: 16px 20px;
            border: 1px solid rgba(255,255,255,.15);
            transition: all .2s;
        }
        .announcement-item:hover {
            background: rgba(255,255,255,.18);
            border-color: rgba(255,255,255,.3);
        }
        .announcement-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 6px;
        }
        .announcement-title {
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.4;
        }
        .announcement-category {
            font-size: .75rem;
            font-weight: 600;
            background: rgba(255,255,255,.25);
            padding: 2px 10px;
            border-radius: 20px;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .announcement-desc {
            font-size: .92rem;
            font-weight: 400;
            opacity: .92;
            line-height: 1.5;
            margin-bottom: 8px;
        }
        .announcement-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            font-size: .82rem;
            opacity: .75;
        }
        .announcement-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .announcement-date, .announcement-time, .announcement-end-date {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        @media (max-width: 768px) {
            .announcement-banner-inner { padding: 12px 16px; }
            .announcement-item { padding: 12px 14px; }
            .announcement-title { font-size: .95rem; }
            .announcement-desc { font-size: .85rem; }
            .announcement-meta { gap: 10px; font-size: .78rem; }
        }
        @media print { .announcement-banner { display: none !important; } }
        </style>
        @endif
        <nav class="admin-topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-breadcrumb">
                    <span>{{ __('app.hi') }} </span><strong>{{ Auth::user()->name }}</strong>
                </div>
            </div>
            <div class="topbar-right">
                {{-- Language Switcher --}}
                <div class="topbar-icon-btn dropdown">
                    <button class="topbar-icon-toggle" data-bs-toggle="dropdown" title="{{ __('app.language') }}">
                        <i class="fas fa-globe"></i>
                        <span class="topbar-icon-badge lang-code">{{ strtoupper(app()->getLocale()) }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end topbar-icon-dropdown">
                        <li class="dropdown-header"><i class="fas fa-globe me-1"></i> {{ __('app.language') }}</li>
                        <li><hr class="dropdown-divider"></li>
                        @foreach(config('app.available_locales') as $code => $name)
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === $code ? 'active' : '' }}"
                                   href="{{ route('lang.switch', $code) }}">
                                    @if(app()->getLocale() === $code)
                                        <i class="fas fa-check me-2 text-success"></i>
                                    @else
                                        <i class="fas fa-circle me-2 text-muted" style="font-size:8px"></i>
                                    @endif
                                    {{ $name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Chat Icon --}}
                @php
                    $chatUnread = 0;
                    try {
                        $chatUnread = \App\Models\ChatMessage::whereHas('conversation.participants', function ($q) {
                            $q->where('user_id', Auth::id());
                        })->where('sender_id', '!=', Auth::id())->where('is_read', false)->count();
                    } catch (\Exception $e) {}
                @endphp
                <a href="{{ route('admin.chat.index') }}" class="topbar-icon-btn topbar-icon-link" title="{{ __('app.chat') }}">
                    <i class="fas fa-comment-dots"></i>
                    @if($chatUnread > 0)
                        <span class="topbar-icon-badge badge-danger">{{ $chatUnread > 99 ? '99+' : $chatUnread }}</span>
                    @endif
                </a>

                {{-- Notifications Icon --}}
                @php
                    $notifUnread = 0;
                    try {
                        $notifUnread = \App\Models\Notification::where('user_id', Auth::id())
                            ->where('is_read', false)->count();
                    } catch (\Exception $e) {}
                    $latestNotifs = \App\Models\Notification::where('user_id', Auth::id())
                        ->orderByDesc('created_at')->limit(5)->get();
                @endphp
                <div class="topbar-icon-btn dropdown">
                    <button class="topbar-icon-toggle" data-bs-toggle="dropdown" title="{{ __('app.notifications') }}">
                        <i class="fas fa-bell"></i>
                        @if($notifUnread > 0)
                            <span class="topbar-icon-badge badge-danger">{{ $notifUnread > 99 ? '99+' : $notifUnread }}</span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end topbar-notif-dropdown">
                        <li class="dropdown-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-bell me-1"></i> {{ __('app.notifications') }}</span>
                            @if($notifUnread > 0)
                                <a href="{{ route('admin.notifications.markAllRead') }}" class="text-primary" style="font-size:11px;" onclick="event.preventDefault();this.closest('form')?.submit();">
                                    <form method="POST" action="{{ route('admin.notifications.markAllRead') }}" style="display:inline">@csrf
                                        <button type="submit" class="btn btn-link p-0 text-primary" style="font-size:11px;text-decoration:none;">{{ __('app.mark_all_read') }}</button>
                                    </form>
                                </a>
                            @endif
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        @forelse($latestNotifs as $notif)
                            <li>
                                <a class="dropdown-item notif-item {{ $notif->is_read ? '' : 'unread' }}"
                                   href="{{ $notif->link ? route('admin.notifications.read', $notif->id) : route('admin.notifications.index') }}">
                                    <div class="notif-title">{{ Str::limit($notif->title ?? $notif->message ?? __('app.notification'), 50) }}</div>
                                    <div class="notif-time">{{ $notif->created_at->diffForHumans() }}</div>
                                </a>
                            </li>
                        @empty
                            <li class="dropdown-item text-center text-muted" style="font-size:12px;padding:16px;">
                                <i class="fas fa-bell-slash mb-1" style="font-size:18px;opacity:.4;display:block"></i>
                                {{ __('app.no_notifications') }}
                            </li>
                        @endforelse
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-center text-primary fw-semibold" href="{{ route('admin.notifications.index') }}" style="font-size:12px;">
                                {{ __('app.view_all_notifications') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <a href="{{ url('/') }}" class="topbar-icon-btn topbar-icon-link" target="_blank" title="{{ __('app.view_website') }}">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                <div class="topbar-dropdown dropdown">
                    <button class="topbar-avatar" data-bs-toggle="dropdown">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            <div class="dropdown-header-name">{{ Auth::user()->name }}</div>
                            <div class="dropdown-header-email">{{ Auth::user()->email }}</div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ url('/') }}"><i class="fas fa-external-link-alt me-2"></i>{{ __('app.view_website') }}</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>{{ __('app.logout') }}</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="admin-content">
            @if(session('success'))
                <div class="global-alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="global-alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif
            @if(session('warning'))
                <div class="global-alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}</div>
            @endif
            @if(session('info'))
                <div class="global-alert alert-info"><i class="fas fa-info-circle"></i> {{ session('info') }}</div>
            @endif

            @yield('content')
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggle = document.getElementById('sidebarToggle');
    const isMobile = () => window.innerWidth < 768;

    function showSidebar(show) {
        if (!sidebar) return;
        sidebar.classList.toggle('show', show);
        if (backdrop) {
            backdrop.classList.toggle('d-none', !show);
            backdrop.classList.toggle('show', show);
        }
    }

    if (toggle) toggle.addEventListener('click', () => showSidebar(!sidebar.classList.contains('show')));
    if (backdrop) backdrop.addEventListener('click', () => showSidebar(false));

    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.addEventListener('click', () => {
            if (link.hasAttribute('data-bs-toggle')) return;
            if (window.innerWidth < 768) showSidebar(false);
        });
    });

    // Auto-scroll to active menu item
    var activeItem = document.querySelector('.sidebar-menu a.active');
    if (activeItem) {
        setTimeout(function() {
            activeItem.scrollIntoView({behavior:'smooth', block:'nearest'});
        }, 200);
    }

    // Auto-dismiss alerts
    document.querySelectorAll('.global-alert').forEach(a => {
        setTimeout(() => {
            a.style.transition = 'opacity 0.3s';
            a.style.opacity = '0';
            setTimeout(() => a.remove(), 300);
        }, 4000);
    });
})();
</script>
<style>
/* ===== Topbar Icon Buttons (Chat, Notifications, Language) ===== */
.topbar-icon-btn {
    position: relative;
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    transition: var(--transition);
    font-size: 15px;
}
.topbar-icon-btn:hover {
    background: var(--body-bg);
    color: var(--text-dark);
}
.topbar-icon-link {
    text-decoration: none;
}
.topbar-icon-toggle {
    background: none;
    border: none;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: inherit;
    font-size: inherit;
    cursor: pointer;
    border-radius: var(--radius-sm);
    transition: var(--transition);
}
.topbar-icon-toggle:hover {
    color: var(--text-dark);
}

/* Badge on icon */
.topbar-icon-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    font-size: 9px;
    font-weight: 700;
    line-height: 1;
    min-width: 16px;
    height: 16px;
    padding: 2px 4px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}
.topbar-icon-badge.lang-code {
    position: static;
    background: var(--primary-light);
    color: var(--primary);
    min-width: auto;
    height: auto;
    padding: 1px 4px;
    font-size: 9px;
    border-radius: 3px;
    margin-left: -2px;
    margin-top: -6px;
}
.topbar-icon-badge.badge-danger {
    background: var(--danger);
    color: #fff;
    top: 0;
    right: 0;
    animation: badge-pulse 2s infinite;
}
@keyframes badge-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Notification Dropdown */
.topbar-icon-dropdown {
    min-width: 180px;
}
.topbar-icon-dropdown .dropdown-item {
    font-size: 13px;
    padding: 8px 14px;
}
.topbar-icon-dropdown .dropdown-item.active {
    background-color: #f0f7ff;
    font-weight: 600;
}

.topbar-notif-dropdown {
    width: 320px;
    max-width: 90vw;
    max-height: 400px;
    overflow-y: auto;
}
.notif-item {
    padding: 10px 14px !important;
    border-bottom: 1px solid #f3f4f6;
}
.notif-item.unread {
    background: #f0f7ff;
}
.notif-item .notif-title {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.notif-item .notif-time {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
}

/* ===== Print Styles ===== */
@media print {
    .admin-sidebar, .sidebar-backdrop, .admin-topbar, .sidebar-footer, .sidebar-toggle,
    .no-print, .mr-filter-card, .mr-header, .mr-actions, .me-filter-card, .me-header,
    .me-keyboard-hint, .fms-card:first-of-type, .global-alert {
        display: none !important;
    }
    .admin-wrapper, .admin-main {
        margin: 0 !important;
        padding: 0 !important;
    }
    .admin-content {
        padding: 0 !important;
    }
    .admin-sidebar {
        display: none !important;
    }
    body {
        background: #fff !important;
    }
}
</style>
@stack('scripts')
@yield('scripts')
</body>
</html>
