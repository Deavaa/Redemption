<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    {{-- Global Session Expiry Detection --}}
    <script>
    // Intercept ALL fetch() calls to detect session expiry (302 redirect to login)
    (function() {
        var originalFetch = window.fetch;
        var loginUrl = '{{ route("login") }}';
        var sessionExpired = false;

        window.fetch = function() {
            var args = arguments;
            return originalFetch.apply(this, args).then(function(response) {
                // If the response was redirected to the login page, the session has expired
                if (response.redirected && response.url.indexOf('/login') !== -1) {
                    if (!sessionExpired) {
                        sessionExpired = true;
                        alert('Your session has expired. You will be redirected to the login page.');
                        window.location.href = loginUrl;
                    }
                    // Return a rejected promise so the calling code doesn't try to parse HTML as JSON
                    return Promise.reject(new Error('Session expired'));
                }
                return response;
            });
        };
    })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>@yield('title', __('app.dashboard')) - Redemption School</title>

    {{-- PWA & Mobile Integration --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Redemption">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-TileColor" content="#6366f1">
    <meta name="msapplication-navbutton-color" content="#6366f1">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <link href="{{ asset('css/modern-components.css') }}" rel="stylesheet">
    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            $isFinance = Auth::user()->role === 'finance';
            $isHR = Auth::user()->role === 'hr';

            // Menu level determines which sidebar sections are shown
            $menuLevel = 'full'; // default for admin/super_admin
            if ($isTeacher) $menuLevel = 'teacher';
            elseif ($isLibrarian) $menuLevel = 'librarian';
            elseif ($isCashier) $menuLevel = 'cashier';
            elseif ($isRegistrar) $menuLevel = 'registrar';
            elseif ($isFinance) $menuLevel = 'finance';
            elseif ($isHR) $menuLevel = 'hr';
            elseif ($isBranchPrincipal) $menuLevel = 'branch_principal';
            elseif ($isGeneralManager) $menuLevel = 'general_manager';

            // Check if teacher is a homeroom teacher for any class/section
            $isHomeroomTeacher = false;
            $userBranchId = Auth::user()->branch_id;
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

            // Get user's branch for branch_principal dropdown locking
            $userBranch = Auth::user()->branch;

            // Route groups for active state detection
            $academicSetupRoutes = ['admin.academic-years.*','admin.terms.*','admin.subjects.*','admin.subject-assignments.*','admin.exams.*','admin.classrooms.*','admin.sections.*'];
            $academicMarksRoutes = ['admin.mark-entries.*','admin.mark-sheet.*','admin.mark-sheet-full.*','admin.mark-roster.*','admin.attendance.*','admin.attendance-delegation.*','admin.mark-entry-locks.*','admin.mark-entry-permissions.*','admin.promotion.*','admin.lesson-plans.*'];
            $academicReportsRoutes = ['admin.report-card.*','admin.progress-reports.*','admin.performance-reports.*'];
            $documentRoutes = ['admin.id-card-generate.*','admin.certificate-generate.*','admin.id-cards.*','admin.certificates.*','admin.report-exchange.*','admin.transcript.*','admin.leaving-certificate.*','admin.report-card.*','admin.progress-reports.*'];
            $peopleRoutes = ['admin.students.*','admin.teachers.*','admin.staff.*','admin.team-members.*','admin.parents.*','admin.teacher-assignments.*'];
            $financeRoutes = ['admin.fees.*','admin.fee-payments.*','admin.payrolls.*','admin.budgets.*','admin.income-expenses.*','admin.finance-statements.*','admin.budget-comparison.*','admin.financial-comparison.*'];
            $hrRoutes = ['admin.leaves.*','admin.employee-assets.*'];
            $analysisRoutes = ['admin.performance-analysis.*','admin.performance-comparison.*','admin.psychological-analysis.*','admin.performance.*'];
            // documentRoutes moved above to include transcript/leaving-certificate/report-card
            $libraryRoutes = ['admin.library.*','admin.video-library.*'];
            $commRoutes = ['admin.calendar.*','admin.announcements.*','admin.telegram.*','admin.chat.*'];
            $websiteRoutes = ['admin.sliders.*','admin.gallery-*','admin.branches.*','admin.contact-messages.*','admin.web-content.*','admin.news.*'];
            $adminRoutes = ['admin.user-access.*','admin.settings.*','admin.roles.*','admin.backup.*','admin.audits.*','admin.email-inbox.*','admin.email-inbox-settings*','admin.bank-integration.*','admin.club-follow-up-configs.*','admin.graphical-reports.*','admin.exam-questions.*'];

            $isAcademicActive = request()->routeIs([...$academicSetupRoutes, ...$academicMarksRoutes, 'admin.attendance.*', 'admin.attendance-delegation.*']);
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
                        <li><a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}"><i class="fas fa-clipboard-check"></i> Attendance</a></li>
                        <li><a href="{{ route('admin.attendance-delegation.index') }}" class="{{ request()->routeIs('admin.attendance-delegation.*') ? 'active' : '' }}"><i class="fas fa-user-check"></i> Attendance Delegation</a></li>
                        @if($isHomeroomTeacher)
                        <li><a href="{{ route('admin.mark-sheet.index') }}" class="{{ request()->routeIs('admin.mark-sheet.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Mark Sheet</a></li>
                        <li><a href="{{ route('admin.mark-sheet-full.index') }}" class="{{ request()->routeIs('admin.mark-sheet-full.*') ? 'active' : '' }}"><i class="fas fa-table"></i> Full Mark Sheet</a></li>
                        @endif
                        <li><a href="{{ route('admin.mark-roster.index') }}" class="{{ request()->routeIs('admin.mark-roster.*') ? 'active' : '' }}"><i class="fas fa-list-ol"></i> Mark Roster</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Planning</li>
                        <li><a href="{{ route('admin.lesson-plans.index') }}" class="{{ request()->routeIs('admin.lesson-plans.*') ? 'active' : '' }}"><i class="fas fa-chalkboard"></i> Lesson Plans</a></li>
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
                        <li><a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}"><i class="fas fa-clipboard-check"></i> Attendance</a></li>
                        <li><a href="{{ route('admin.attendance-delegation.index') }}" class="{{ request()->routeIs('admin.attendance-delegation.*') ? 'active' : '' }}"><i class="fas fa-user-check"></i> Attendance Delegation</a></li>
                        <li><a href="{{ route('admin.mark-sheet.index') }}" class="{{ request()->routeIs('admin.mark-sheet.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Mark Sheet</a></li>
                        <li><a href="{{ route('admin.mark-sheet-full.index') }}" class="{{ request()->routeIs('admin.mark-sheet-full.*') ? 'active' : '' }}"><i class="fas fa-table"></i> Full Mark Sheet</a></li>
                        <li><a href="{{ route('admin.mark-roster.index') }}" class="{{ request()->routeIs('admin.mark-roster.*') ? 'active' : '' }}"><i class="fas fa-list-ol"></i> Mark Roster</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Promotion & Locks</li>
                        <li><a href="{{ route('admin.promotion.index') }}" class="{{ request()->routeIs('admin.promotion.*') ? 'active' : '' }}"><i class="fas fa-level-up-alt"></i> Promotion & Detention</a></li>
                        <li><a href="{{ route('admin.mark-entry-locks.index') }}" class="{{ request()->routeIs('admin.mark-entry-locks.*') ? 'active' : '' }}"><i class="fas fa-lock"></i> Mark Entry Locks</a></li>
                        <li><a href="{{ route('admin.mark-entry-permissions.index') }}" class="{{ request()->routeIs('admin.mark-entry-permissions.*') ? 'active' : '' }}"><i class="fas fa-key"></i> Mark Edit Permissions</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Planning</li>
                        <li><a href="{{ route('admin.lesson-plans.index') }}" class="{{ request()->routeIs('admin.lesson-plans.*') ? 'active' : '' }}"><i class="fas fa-chalkboard"></i> Lesson Plans</a></li>
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
                        <li><a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}"><i class="fas fa-clipboard-check"></i> Attendance</a></li>
                        <li><a href="{{ route('admin.attendance-delegation.index') }}" class="{{ request()->routeIs('admin.attendance-delegation.*') ? 'active' : '' }}"><i class="fas fa-user-check"></i> Attendance Delegation</a></li>
                        <li><a href="{{ route('admin.mark-sheet.index') }}" class="{{ request()->routeIs('admin.mark-sheet.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Mark Sheet</a></li>
                        <li><a href="{{ route('admin.mark-sheet-full.index') }}" class="{{ request()->routeIs('admin.mark-sheet-full.*') ? 'active' : '' }}"><i class="fas fa-table"></i> Full Mark Sheet</a></li>
                        <li><a href="{{ route('admin.mark-roster.index') }}" class="{{ request()->routeIs('admin.mark-roster.*') ? 'active' : '' }}"><i class="fas fa-list-ol"></i> Mark Roster</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Promotion & Locks</li>
                        <li><a href="{{ route('admin.promotion.index') }}" class="{{ request()->routeIs('admin.promotion.*') ? 'active' : '' }}"><i class="fas fa-level-up-alt"></i> Promotion & Detention</a></li>
                        <li><a href="{{ route('admin.mark-entry-locks.index') }}" class="{{ request()->routeIs('admin.mark-entry-locks.*') ? 'active' : '' }}"><i class="fas fa-lock"></i> Mark Entry Locks</a></li>
                        <li><a href="{{ route('admin.mark-entry-permissions.index') }}" class="{{ request()->routeIs('admin.mark-entry-permissions.*') ? 'active' : '' }}"><i class="fas fa-key"></i> Mark Edit Permissions</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Planning</li>
                        <li><a href="{{ route('admin.lesson-plans.index') }}" class="{{ request()->routeIs('admin.lesson-plans.*') ? 'active' : '' }}"><i class="fas fa-chalkboard"></i> Lesson Plans</a></li>
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
                @elseif($menuLevel === 'finance')
                <li class="menu-header">PEOPLE</li>
                <li>
                    <a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}"><i class="fas fa-user-graduate"></i> Students</a>
                    <a href="{{ route('admin.teachers.index') }}" class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"><i class="fas fa-chalkboard-teacher"></i> Teachers</a>
                </li>
                @elseif($menuLevel === 'hr')
                <li class="menu-header">PEOPLE</li>
                <li>
                    <a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}"><i class="fas fa-user-graduate"></i> Students</a>
                    <a href="{{ route('admin.teachers.index') }}" class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"><i class="fas fa-chalkboard-teacher"></i> Teachers</a>
                    <a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}"><i class="fas fa-id-badge"></i> Staff</a>
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
                        <li><a href="{{ route('admin.bank-integration.index') }}" class="{{ request()->routeIs('admin.bank-integration*') ? 'active' : '' }}"><i class="fas fa-university"></i> Bank Integration</a></li>
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
                @elseif($menuLevel === 'finance')
                <li class="menu-header">FINANCE</li>
                <li class="{{ $isFinanceActive ? 'has-active-child' : '' }}">
                    <a href="#financeSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-wallet"></i><span>Finance</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isFinanceActive ? 'show' : '' }}" id="financeSubmenu">
                        <li><a href="{{ route('admin.fees.index') }}" class="{{ request()->routeIs('admin.fees.*') ? 'active' : '' }}"><i class="fas fa-money-bill-wave"></i> Fee Structure</a></li>
                        <li><a href="{{ route('admin.fee-payments.index') }}" class="{{ request()->routeIs('admin.fee-payments.*') ? 'active' : '' }}"><i class="fas fa-credit-card"></i> Payments</a></li>
                        <li><a href="{{ route('admin.budgets.index') }}" class="{{ request()->routeIs('admin.budgets.*') ? 'active' : '' }}"><i class="fas fa-chart-pie"></i> Budgets</a></li>
                        <li><a href="{{ route('admin.income-expenses.index') }}" class="{{ request()->routeIs('admin.income-expenses.*') ? 'active' : '' }}"><i class="fas fa-exchange-alt"></i> Income / Expense</a></li>
                        <li><a href="{{ route('admin.finance-statements.index') }}" class="{{ request()->routeIs('admin.finance-statements.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> Statements</a></li>
                        <li><a href="{{ route('admin.payrolls.index') }}" class="{{ request()->routeIs('admin.payrolls.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> Payroll</a></li>
                    </ul>
                </li>
                @elseif($menuLevel === 'hr')
                <li class="menu-header">HR</li>
                <li class="{{ $isFinanceActive ? 'has-active-child' : '' }}">
                    <a href="#financeSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-users-cog"></i><span>Human Resources</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isFinanceActive ? 'show' : '' }}" id="financeSubmenu">
                        <li><a href="{{ route('admin.teachers.index') }}" class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                        <li><a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}"><i class="fas fa-id-badge"></i> Staff</a></li>
                        <li><a href="{{ route('admin.leaves.index') }}" class="{{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}"><i class="fas fa-calendar-minus"></i> Leaves</a></li>
                        <li><a href="{{ route('admin.payrolls.index') }}" class="{{ request()->routeIs('admin.payrolls.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> Payroll</a></li>
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
                        <i class="fas fa-folder-open"></i><span>Documents & Reports</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isDocumentActive ? 'show' : '' }}" id="documentSubmenu">
                        <li style="padding:4px 12px 2px;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;">Certificates & Documents</li>
                        <li><a href="{{ route('admin.certificate-generate.index') }}" class="{{ request()->routeIs('admin.certificate-generate.*') ? 'active' : '' }}"><i class="fas fa-award"></i> Certificate Generator</a></li>
                        <li><a href="{{ route('admin.transcript.index') }}" class="{{ request()->routeIs('admin.transcript.*') ? 'active' : '' }}"><i class="fas fa-scroll"></i> Academic Transcript</a></li>
                        <li><a href="{{ route('admin.leaving-certificate.index') }}" class="{{ request()->routeIs('admin.leaving-certificate.*') ? 'active' : '' }}"><i class="fas fa-file-signature"></i> Leaving Certificate</a></li>
                        <li><a href="{{ route('admin.id-card-generate.index') }}" class="{{ request()->routeIs('admin.id-card-generate.*') ? 'active' : '' }}"><i class="fas fa-id-badge"></i> ID Card Generator</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Reports</li>
                        <li><a href="{{ route('admin.report-card.index') }}" class="{{ request()->routeIs('admin.report-card.*') ? 'active' : '' }}"><i class="fas fa-id-card"></i> Report Cards</a></li>
                        <li><a href="{{ route('admin.progress-reports.index') }}" class="{{ request()->routeIs('admin.progress-reports.*') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> Progress Reports</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Exchange</li>
                        <li><a href="{{ route('admin.report-exchange.index') }}" class="{{ request()->routeIs('admin.report-exchange.*') ? 'active' : '' }}"><i class="fas fa-exchange-alt"></i> Report Exchange</a></li>
                    </ul>
                </li>
                @endif
                @if(in_array($menuLevel, ['full', 'general_manager', 'branch_principal', 'teacher', 'librarian']))
                <li class="{{ $isLibraryActive ? 'has-active-child' : '' }}">
                    @if(in_array($menuLevel, ['teacher', 'librarian']))
                    <a href="{{ route('admin.video-library.index') }}" class="{{ request()->routeIs('admin.video-library.*') ? 'active' : '' }}">
                        <i class="fas fa-book-open"></i><span>Digital Library</span>
                    </a>
                    @else
                    <a href="#librarySubmenu" data-bs-toggle="collapse" class="submenu-toggle {{ $isLibraryActive ? 'active' : '' }}">
                        <i class="fas fa-book-open"></i><span>Digital Library</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isLibraryActive ? 'show' : '' }}" id="librarySubmenu">
                        <li><a href="{{ route('admin.library.index') }}" class="{{ request()->routeIs('admin.library.*') ? 'active' : '' }}"><i class="fas fa-book"></i> Book Library</a></li>
                        <li><a href="{{ route('admin.video-library.index') }}" class="{{ request()->routeIs('admin.video-library.*') ? 'active' : '' }}"><i class="fab fa-youtube"></i> Video Library</a></li>
                    </ul>
                    @endif
                </li>
                @endif

                {{-- COMMUNICATION --}}
                <li class="menu-header">COMMUNICATION</li>
                <li class="{{ $isCommActive ? 'has-active-child' : '' }}">
                    @if(in_array($menuLevel, ['teacher', 'librarian', 'cashier', 'finance', 'hr']))
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
                @elseif($menuLevel === 'branch_principal')
                <li class="menu-header">WEBSITE</li>
                <li class="{{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.news.index') }}" class="{{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                        <i class="fas fa-newspaper"></i><span>News</span>
                    </a>
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
                        @if($menuLevel === 'full')
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Data & Backup</li>
                        <li><a href="{{ route('admin.backup.index') }}" class="{{ request()->routeIs('admin.backup.*') ? 'active' : '' }}"><i class="fas fa-database"></i> Database Backup</a></li>
                        <li><a href="{{ route('admin.audits.index') }}" class="{{ request()->routeIs('admin.audits.*') ? 'active' : '' }}"><i class="fas fa-clipboard-list"></i> Audit Log</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Integrations</li>
                        <li><a href="{{ route('admin.email-inbox.index') }}" class="{{ request()->routeIs('admin.email-inbox*') ? 'active' : '' }}"><i class="fas fa-envelope-open-text"></i> Email Inbox</a></li>
                        <li><a href="{{ route('admin.bank-integration.index') }}" class="{{ request()->routeIs('admin.bank-integration*') ? 'active' : '' }}"><i class="fas fa-university"></i> Bank Integration</a></li>
                        <li><a href="{{ route('admin.club-follow-up-configs.index') }}" class="{{ request()->routeIs('admin.club-follow-up-configs*') ? 'active' : '' }}"><i class="fas fa-clipboard-check"></i> Club Follow-up Config</a></li>
                        <li style="margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding-left:12px;">Reports & Analytics</li>
                        <li><a href="{{ route('admin.graphical-reports.index') }}" class="{{ request()->routeIs('admin.graphical-reports*') ? 'active' : '' }}"><i class="fas fa-chart-bar"></i> Graphical Reports</a></li>
                        <li><a href="{{ route('admin.exam-questions.index') }}" class="{{ request()->routeIs('admin.exam-questions*') ? 'active' : '' }}"><i class="fas fa-question-circle"></i> Exam Questions Review</a></li>
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
        {{-- Announcement Banner — $activeAnnouncements is shared via AppServiceProvider view composer --}}
        <div id="adminAnnouncementBar" class="announcement-banner">
            <div class="announcement-banner-inner">
                <a href="{{ route('admin.announcements.index') }}" class="announcement-badge" style="text-decoration:none;"><i class="fas fa-bullhorn"></i>&ensp;Announcements</a>
                @if($activeAnnouncements->count() > 0)
                <div class="announcement-ticker-wrap">
                    <div class="announcement-ticker">
                        @foreach($activeAnnouncements as $ann)
                        <span class="announcement-chip">
                            <strong>{{ $ann->title }}</strong>
                            @if($ann->category)<span class="announcement-cat">{{ ucfirst($ann->category) }}</span>@endif
                            @if($ann->start_date)<span class="announcement-date-inline"><i class="fas fa-calendar-alt"></i> {{ $ann->start_date->format('M d') }}</span>@endif
                            @if($ann->description)<span class="announcement-desc-inline">&mdash; {{ Str::limit(strip_tags($ann->description), 80) }}</span>@endif
                        </span>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="announcement-ticker-wrap">
                    <div class="announcement-ticker">
                        <span class="announcement-chip" style="opacity:0.7;"><i class="fas fa-info-circle"></i>&ensp;No active announcements &mdash; <a href="{{ route('admin.announcements.index') }}" style="color:#93c5fd;text-decoration:underline;">Create one</a></span>
                    </div>
                </div>
                @endif
                <button onclick="document.getElementById('adminAnnouncementBar').style.display='none'" class="announcement-close" title="Dismiss"><i class="fas fa-times"></i></button>
            </div>
        </div>

        {{-- Announcement Splash Modal --}}
        @if($activeAnnouncements->count() > 0)
        <div class="announcement-splash-overlay" id="announcementSplash">
            <div class="announcement-splash-modal">
                <div class="announcement-splash-header">
                    <div class="announcement-splash-icon"><i class="fas fa-bullhorn"></i></div>
                    <h2>Announcements</h2>
                    <span class="announcement-splash-count">{{ $activeAnnouncements->count() }} active</span>
                </div>
                <div class="announcement-splash-body">
                    @foreach($activeAnnouncements as $splashAnn)
                    <div class="announcement-splash-item">
                        <div class="announcement-splash-item-dot" style="background:{{ $splashAnn->color ?? '#4361ee' }}"></div>
                        <div class="announcement-splash-item-content">
                            <div class="announcement-splash-item-title">{{ $splashAnn->title }}</div>
                            @if($splashAnn->category)
                            <span class="announcement-splash-item-cat" style="background:{{ $splashAnn->color ?? '#4361ee' }}20;color:{{ $splashAnn->color ?? '#4361ee' }}">{{ ucfirst($splashAnn->category) }}</span>
                            @endif
                            @if($splashAnn->start_date)
                            <span class="announcement-splash-item-date"><i class="fas fa-calendar-alt"></i> {{ $splashAnn->start_date->format('M d, Y') }}</span>
                            @endif
                            @if($splashAnn->description)
                            <p class="announcement-splash-item-desc">{{ Str::limit(strip_tags($splashAnn->description), 150) }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="announcement-splash-footer">
                    <a href="{{ route('admin.announcements.index') }}" class="announcement-splash-viewall"><i class="fas fa-list"></i> View All Announcements</a>
                    <button onclick="closeAnnouncementSplash()" class="announcement-splash-dismiss"><i class="fas fa-check"></i> Dismiss</button>
                </div>
            </div>
        </div>
        @endif

        <style>
        .announcement-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            position: relative;
            z-index: 50;
            border-bottom: 2px solid #3b82f6;
        }
        .announcement-banner-inner {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0 16px;
            height: 40px;
        }
        .announcement-badge {
            font-weight: 700;
            font-size: .82rem;
            background: #3b82f6;
            padding: 3px 12px;
            border-radius: 20px;
            white-space: nowrap;
            letter-spacing: .5px;
            flex-shrink: 0;
            text-transform: uppercase;
        }
        .announcement-ticker-wrap {
            flex: 1;
            overflow: hidden;
            position: relative;
            mask-image: linear-gradient(90deg, transparent 0%, #000 3%, #000 97%, transparent 100%);
            -webkit-mask-image: linear-gradient(90deg, transparent 0%, #000 3%, #000 97%, transparent 100%);
        }
        .announcement-ticker {
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
        }
        .announcement-ticker.scrolling {
            animation: ticker-scroll var(--ticker-duration, 60s) linear infinite;
        }
        .announcement-ticker.scrolling:hover { animation-play-state: paused; }
        @keyframes ticker-scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .announcement-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: .9rem;
            padding: 0 24px;
            border-right: 1px solid rgba(255,255,255,.15);
        }
        .announcement-chip:last-child { border-right: none; }
        .announcement-chip strong { font-weight: 600; }
        .announcement-cat {
            font-size: .72rem;
            font-weight: 600;
            background: rgba(255,255,255,.25);
            padding: 1px 8px;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .announcement-date-inline {
            font-size: .8rem;
            opacity: .75;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .announcement-desc-inline {
            font-size: .84rem;
            opacity: .8;
        }
        .announcement-close {
            background: none;
            border: none;
            color: rgba(255,255,255,.6);
            cursor: pointer;
            font-size: 14px;
            padding: 4px 6px;
            border-radius: 50%;
            transition: all .2s;
            flex-shrink: 0;
        }
        .announcement-close:hover {
            background: rgba(255,255,255,.2);
            color: #fff;
        }

        /* ===== Announcement Splash Modal ===== */
        .announcement-splash-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            display: none;  /* Hidden by default — JS shows it on page load if not dismissed */
            align-items: center;
            justify-content: center;
            padding: 16px;
            backdrop-filter: blur(4px);
        }
        .announcement-splash-overlay.splash-show {
            display: flex;
            animation: splashFadeIn 0.3s ease;
        }
        @keyframes splashFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .announcement-splash-modal {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: splashSlideUp 0.3s ease;
            overflow: hidden;
        }
        @keyframes splashSlideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .announcement-splash-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .announcement-splash-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #3b82f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .announcement-splash-header h2 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            flex: 1;
        }
        .announcement-splash-count {
            font-size: 12px;
            font-weight: 600;
            background: rgba(255,255,255,0.2);
            padding: 3px 10px;
            border-radius: 20px;
        }
        .announcement-splash-body {
            padding: 16px 20px;
            overflow-y: auto;
            flex: 1;
        }
        .announcement-splash-item {
            display: flex;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .announcement-splash-item:last-child { border-bottom: none; }
        .announcement-splash-item-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }
        .announcement-splash-item-content {
            flex: 1;
            min-width: 0;
        }
        .announcement-splash-item-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
            line-height: 1.3;
        }
        .announcement-splash-item-cat {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 6px;
            margin-right: 8px;
        }
        .announcement-splash-item-date {
            font-size: 11px;
            color: #9ca3af;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .announcement-splash-item-desc {
            font-size: 13px;
            color: #6b7280;
            margin: 8px 0 0;
            line-height: 1.5;
        }
        .announcement-splash-footer {
            padding: 16px 20px;
            border-top: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .announcement-splash-viewall {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #3b82f6;
            text-decoration: none;
        }
        .announcement-splash-viewall:hover { text-decoration: underline; }
        .announcement-splash-dismiss {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 20px;
            border-radius: 10px;
            background: #3b82f6;
            color: #fff;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .announcement-splash-dismiss:hover { background: #2563eb; }

        @media (max-width: 768px) {
            /* Mobile: stack badge on line 1, ticker on line 2 */
            .announcement-banner { max-width: 100vw; overflow: hidden; }
            .announcement-banner-inner {
                max-width: 100vw;
                overflow: hidden;
                flex-wrap: wrap;
                height: auto;
                min-height: 40px;
                padding: 6px 10px;
                gap: 4px 10px;
                align-items: center;
            }
            .announcement-badge { font-size: .72rem; padding: 2px 8px; }
            .announcement-close { order: 2; }
            .announcement-ticker-wrap {
                min-width: 0;
                flex-basis: 100%;
                order: 3;
                overflow: hidden;
            }
            .announcement-chip { font-size: .8rem; white-space: normal; word-break: break-word; line-height: 1.3; }

            .announcement-splash-overlay { padding: 8px; z-index: 10001; }
            .announcement-splash-modal { max-width: 100%; max-height: 90vh; border-radius: 12px; }
            .announcement-splash-header { padding: 16px; }
            .announcement-splash-header h2 { font-size: 16px; }
            .announcement-splash-body { padding: 12px 16px; }
            .announcement-splash-footer { padding: 12px 16px; flex-direction: column; }
            .announcement-splash-viewall, .announcement-splash-dismiss {
                width: 100%; justify-content: center;
                min-height: 44px;  /* Touch-friendly */
            }
            .announcement-splash-dismiss { font-size: 15px; font-weight: 700; }
        }
        @media print { .announcement-banner, .announcement-splash-overlay { display: none !important; } }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.announcement-ticker-wrap').forEach(function(wrap) {
                var ticker = wrap.querySelector('.announcement-ticker');
                if (!ticker) return;
                // Only scroll if content overflows the container
                if (ticker.scrollWidth > wrap.clientWidth + 10) {
                    // Clone the content for seamless infinite scroll
                    var clone = ticker.innerHTML;
                    ticker.insertAdjacentHTML('beforeend', clone);
                    var duration = Math.max(ticker.scrollWidth / 2 / 25, 50);
                    ticker.style.setProperty('--ticker-duration', duration + 's');
                    ticker.classList.add('scrolling');
                }
            });

            // Announcement Splash: show ONCE PER DAY per user.
            // Uses localStorage with today's date key so it reappears the next day.
            var splash = document.getElementById('announcementSplash');
            if (splash) {
                var today = new Date().toISOString().slice(0, 10); // YYYY-MM-DD
                var splashKey = 'announcement_splash_dismissed_' + today;
                var alreadyShownToday = localStorage.getItem(splashKey);
                if (!alreadyShownToday) {
                    splash.classList.add('splash-show');
                }
            }

        });

        function closeAnnouncementSplash() {
            var splash = document.getElementById('announcementSplash');
            if (splash) {
                splash.style.opacity = '0';
                splash.style.transition = 'opacity 0.3s';
                setTimeout(function() {
                    splash.classList.remove('splash-show');
                    splash.style.opacity = '';
                    splash.style.transition = '';
                }, 300);
                var today = new Date().toISOString().slice(0, 10);
                localStorage.setItem('announcement_splash_dismissed_' + today, '1');
            }
        }
        </script>
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

                @if(in_array($menuLevel, ['full', 'general_manager']))
                <a href="{{ route('admin.settings.index') }}" class="topbar-icon-btn topbar-icon-link" title="{{ __('app.settings') ?? 'Settings' }}">
                    <i class="fas fa-cog"></i>
                </a>
                @endif
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
                        <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="fas fa-user-cog me-2"></i>Profile & Password</a></li>
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

{{-- Swipe Indicator for Mobile --}}
<div class="swipe-indicator" id="swipeIndicator"></div>

{{-- Mobile Bottom Navigation --}}
<nav class="mobile-bottom-nav" id="mobileBottomNav">
    <div class="mobile-bottom-nav-inner">
        {{-- 1. Home — always first --}}
        <a href="{{ route('admin.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i>
            <span>Home</span>
        </a>
        {{-- 2. Mark Entry — second item for all roles that can access it --}}
        @if(in_array($menuLevel, ['full', 'general_manager', 'branch_principal', 'teacher']))
        <a href="{{ route('admin.mark-entries.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.mark-entries.*') ? 'active' : '' }}">
            <i class="fas fa-pen"></i>
            <span>Marks</span>
        </a>
        @endif
        {{-- 3. Attendance Taking — third item for all roles that can access it --}}
        @if(in_array($menuLevel, ['full', 'general_manager', 'branch_principal', 'teacher']))
        <a href="{{ route('admin.attendance.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
            <i class="fas fa-clipboard-check"></i>
            <span>Attend.</span>
        </a>
        @endif
        {{-- 4. Role-specific item --}}
        @if(in_array($menuLevel, ['full', 'general_manager', 'branch_principal', 'registrar']))
        <a href="{{ route('admin.students.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
            <i class="fas fa-user-graduate"></i>
            <span>Students</span>
        </a>
        @elseif(in_array($menuLevel, ['finance', 'cashier']))
        <a href="{{ route('admin.fee-payments.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.fee-payments.*') ? 'active' : '' }}">
            <i class="fas fa-credit-card"></i>
            <span>Payments</span>
        </a>
        @elseif($menuLevel === 'librarian')
        <a href="{{ route('admin.video-library.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.library.*') || request()->routeIs('admin.video-library.*') ? 'active' : '' }}">
            <i class="fas fa-book-open"></i>
            <span>Library</span>
        </a>
        @elseif($menuLevel === 'hr')
        <a href="{{ route('admin.staff.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
            <i class="fas fa-id-badge"></i>
            <span>Staff</span>
        </a>
        @endif
        {{-- More — always last --}}
        <div class="mobile-nav-item mobile-nav-more" id="mobileNavMore" onclick="toggleMobileMenu()">
            <i class="fas fa-ellipsis-h"></i>
            <span>More</span>
        </div>
    </div>
</nav>

{{-- Mobile Menu Sheet (slides up from bottom) --}}
<div class="mobile-menu-sheet-backdrop" id="mobileMenuBackdrop" onclick="toggleMobileMenu()"></div>
<div class="mobile-menu-sheet" id="mobileMenuSheet">
    <div class="mobile-menu-sheet-handle"></div>
    <div class="mobile-menu-sheet-title">Quick Access</div>
    <div class="mobile-menu-sheet-links">
        <a href="{{ route('admin.dashboard') }}" class="mobile-menu-link">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>
        @if(in_array($menuLevel, ['full', 'general_manager', 'branch_principal', 'registrar']))
        <a href="{{ route('admin.academic-years.index') }}" class="mobile-menu-link">
            <i class="fas fa-calendar"></i>
            <span>Academic Yr</span>
        </a>
        <a href="{{ route('admin.classrooms.index') }}" class="mobile-menu-link">
            <i class="fas fa-building"></i>
            <span>Classes</span>
        </a>
        <a href="{{ route('admin.subjects.index') }}" class="mobile-menu-link">
            <i class="fas fa-book"></i>
            <span>Subjects</span>
        </a>
        @endif
        @if(in_array($menuLevel, ['full', 'general_manager', 'branch_principal', 'hr']))
        <a href="{{ route('admin.staff.index') }}" class="mobile-menu-link">
            <i class="fas fa-id-badge"></i>
            <span>Staff</span>
        </a>
        @endif
        @if(in_array($menuLevel, ['full', 'general_manager', 'branch_principal', 'registrar']))
        <a href="{{ route('admin.students.index') }}" class="mobile-menu-link">
            <i class="fas fa-user-graduate"></i>
            <span>Students</span>
        </a>
        <a href="{{ route('admin.parents.index') }}" class="mobile-menu-link">
            <i class="fas fa-user-friends"></i>
            <span>Parents</span>
        </a>
        @endif
        @if(in_array($menuLevel, ['full', 'general_manager']))
        <a href="{{ route('admin.fees.index') }}" class="mobile-menu-link">
            <i class="fas fa-money-bill-wave"></i>
            <span>Fees</span>
        </a>
        <a href="{{ route('admin.budgets.index') }}" class="mobile-menu-link">
            <i class="fas fa-chart-pie"></i>
            <span>Budgets</span>
        </a>
        <a href="{{ route('admin.payrolls.index') }}" class="mobile-menu-link">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Payroll</span>
        </a>
        <a href="{{ route('admin.leaves.index') }}" class="mobile-menu-link">
            <i class="fas fa-calendar-minus"></i>
            <span>Leaves</span>
        </a>
        @endif
        @if(in_array($menuLevel, ['finance', 'hr']))
        <a href="{{ route('admin.fees.index') }}" class="mobile-menu-link">
            <i class="fas fa-money-bill-wave"></i>
            <span>Fees</span>
        </a>
        @endif
        @if($menuLevel === 'hr')
        <a href="{{ route('admin.leaves.index') }}" class="mobile-menu-link">
            <i class="fas fa-calendar-minus"></i>
            <span>Leaves</span>
        </a>
        <a href="{{ route('admin.payrolls.index') }}" class="mobile-menu-link">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Payroll</span>
        </a>
        @endif
        @if(in_array($menuLevel, ['full', 'general_manager', 'branch_principal', 'registrar']))
        <a href="{{ route('admin.exams.index') }}" class="mobile-menu-link">
            <i class="fas fa-file-alt"></i>
            <span>Exams</span>
        </a>
        <a href="{{ route('admin.mark-entries.index') }}" class="mobile-menu-link">
            <i class="fas fa-pen"></i>
            <span>Mark Entry</span>
        </a>
        <a href="{{ route('admin.attendance.index') }}" class="mobile-menu-link">
            <i class="fas fa-clipboard-check"></i>
            <span>Attendance</span>
        </a>
        <a href="{{ route('admin.attendance-delegation.index') }}" class="mobile-menu-link">
            <i class="fas fa-user-check"></i>
            <span>Delegate</span>
        </a>
        @endif
        @if($menuLevel === 'teacher')
        <a href="{{ route('admin.mark-entries.index') }}" class="mobile-menu-link">
            <i class="fas fa-pen"></i>
            <span>Mark Entry</span>
        </a>
        <a href="{{ route('admin.attendance.index') }}" class="mobile-menu-link">
            <i class="fas fa-clipboard-check"></i>
            <span>Attendance</span>
        </a>
        <a href="{{ route('admin.attendance-delegation.index') }}" class="mobile-menu-link">
            <i class="fas fa-user-check"></i>
            <span>Delegate</span>
        </a>
        <a href="{{ route('admin.mark-roster.index') }}" class="mobile-menu-link">
            <i class="fas fa-list-ol"></i>
            <span>Mark Roster</span>
        </a>
        <a href="{{ route('admin.report-exchange.index') }}" class="mobile-menu-link">
            <i class="fas fa-exchange-alt"></i>
            <span>Report Exchange</span>
        </a>
        @endif
        @if(in_array($menuLevel, ['full', 'general_manager', 'branch_principal', 'teacher', 'librarian']))
        <a href="{{ route('admin.library.index') }}" class="mobile-menu-link">
            <i class="fas fa-book"></i>
            <span>Books</span>
        </a>
        <a href="{{ route('admin.video-library.index') }}" class="mobile-menu-link">
            <i class="fab fa-youtube"></i>
            <span>Videos</span>
        </a>
        @endif
        <a href="{{ route('admin.calendar.index') }}" class="mobile-menu-link">
            <i class="fas fa-calendar-alt"></i>
            <span>Calendar</span>
        </a>
        <a href="{{ route('admin.announcements.index') }}" class="mobile-menu-link">
            <i class="fas fa-bullhorn"></i>
            <span>Announce</span>
        </a>
        <a href="{{ route('admin.chat.index') }}" class="mobile-menu-link">
            <i class="fas fa-comment-dots"></i>
            <span>Chat</span>
        </a>
        @if(in_array($menuLevel, ['full', 'general_manager', 'branch_principal', 'registrar']))
        <a href="{{ route('admin.report-card.index') }}" class="mobile-menu-link">
            <i class="fas fa-id-card"></i>
            <span>Reports</span>
        </a>
        <a href="{{ route('admin.certificate-generate.index') }}" class="mobile-menu-link">
            <i class="fas fa-award"></i>
            <span>Certs</span>
        </a>
        @endif
        @if(in_array($menuLevel, ['full', 'general_manager']))
        <a href="{{ route('admin.branches.index') }}" class="mobile-menu-link">
            <i class="fas fa-map-marker-alt"></i>
            <span>Branches</span>
        </a>
        <a href="{{ route('admin.settings.index') }}" class="mobile-menu-link">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        @endif
        <a href="{{ route('admin.staff.edit', auth()->id()) }}" class="mobile-menu-link">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
        <a href="#" class="mobile-menu-link" onclick="event.preventDefault();document.getElementById('logoutForm').submit();">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
<form id="logoutForm" method="POST" action="{{ route('logout') }}" style="display:none">@csrf</form>
<script>
// Global user context for branch principal locking
window.currentUser = {
    role: '{{ Auth::user()->role }}',
    branchId: {{ Auth::user()->branch_id ?? 'null' }},
    branchName: '{{ $userBranch?->name ?? '' }}'
};

// Auto-lock branch dropdowns for branch_principal
document.addEventListener('DOMContentLoaded', function() {
    if (window.currentUser.role === 'branch_principal' && window.currentUser.branchId) {
        document.querySelectorAll('select[name="branch_id"]').forEach(function(sel) {
            // Set the value to the user's branch
            sel.value = window.currentUser.branchId;
            // Disable the select
            sel.disabled = true;
            // Add a hidden input with the same name and value so it's still submitted
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = sel.name;
            hidden.value = window.currentUser.branchId;
            sel.parentNode.insertBefore(hidden, sel.nextSibling);
            // Add visual indicator
            sel.style.opacity = '0.7';
            sel.style.cursor = 'not-allowed';
        });
    }
});
</script>
<script>
(function() {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggle = document.getElementById('sidebarToggle');
    const isMobile = () => window.innerWidth < 768;

    function showSidebar(show) {
        if (!sidebar) return;
        if (show) {
            sidebar.classList.add('show');
            sidebar.removeAttribute('style');
        } else {
            sidebar.classList.remove('show');
        }
        if (backdrop) {
            if (show) {
                backdrop.classList.remove('d-none');
                // Force reflow before adding show class for transition
                void backdrop.offsetWidth;
                backdrop.classList.add('show');
            } else {
                backdrop.classList.remove('show');
                // Hide after transition
                setTimeout(() => {
                    if (!backdrop.classList.contains('show')) {
                        backdrop.classList.add('d-none');
                    }
                }, 300);
            }
        }
    }

    if (toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showSidebar(!sidebar.classList.contains('show'));
        });
    }
    if (backdrop) backdrop.addEventListener('click', () => showSidebar(false));

    // Close sidebar on non-submenu link click (mobile)
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't close for submenu toggles — let Bootstrap handle them
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

// Mobile Menu Sheet Toggle
function toggleMobileMenu() {
    const sheet = document.getElementById('mobileMenuSheet');
    const backdrop = document.getElementById('mobileMenuBackdrop');
    const isOpen = sheet.classList.contains('show');
    sheet.classList.toggle('show', !isOpen);
    backdrop.classList.toggle('show', !isOpen);
    document.body.style.overflow = isOpen ? '' : 'hidden';
}

// Swipe-to-open sidebar on mobile
(function() {
    var sidebar = document.getElementById('adminSidebar');
    var backdrop = document.getElementById('sidebarBackdrop');
    var swipeIndicator = document.getElementById('swipeIndicator');
    var touchStartX = 0;
    var touchStartY = 0;
    var isSwiping = false;

    document.addEventListener('touchstart', function(e) {
        if (window.innerWidth >= 769) return;
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        isSwiping = false;
    }, { passive: true });

    document.addEventListener('touchmove', function(e) {
        if (window.innerWidth >= 769) return;
        var dx = e.touches[0].clientX - touchStartX;
        var dy = e.touches[0].clientY - touchStartY;

        // Only detect horizontal swipe from the left edge (within 30px)
        if (!isSwiping && touchStartX < 30 && Math.abs(dx) > Math.abs(dy) && dx > 10) {
            isSwiping = true;
        }
    }, { passive: true });

    document.addEventListener('touchend', function(e) {
        if (window.innerWidth >= 769 || !isSwiping) return;
        var dx = e.changedTouches[0].clientX - touchStartX;

        // Swipe right from left edge opens sidebar
        if (dx > 60 && sidebar && !sidebar.classList.contains('show')) {
            sidebar.classList.add('show');
            if (backdrop) {
                backdrop.classList.remove('d-none');
                backdrop.classList.add('show');
            }
        }
        isSwiping = false;
    }, { passive: true });

    // Hide swipe indicator after 3 seconds
    if (swipeIndicator) {
        setTimeout(function() {
            swipeIndicator.style.opacity = '0';
            setTimeout(function() { swipeIndicator.style.display = 'none'; }, 300);
        }, 3000);
    }
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

{{-- PWA Service Worker Registration & Notification Permission --}}
<script>
// Register service worker for PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('{{ asset('sw.js') }}')
            .then(function(registration) {
                console.log('SW registered:', registration.scope);

                // Check for updates periodically
                setInterval(function() {
                    registration.update();
                }, 60 * 60 * 1000); // every hour
            })
            .catch(function(error) {
                console.log('SW registration failed:', error);
            });
    });
}

// Request notification permission for mobile integration
function requestNotificationPermission() {
    if (!('Notification' in window)) {
        console.log('This browser does not support notifications');
        return;
    }

    if (Notification.permission === 'default') {
        // Don't auto-request — let user trigger it
        window._redemptionCanRequestNotifications = true;
    }
}

// Call on first user interaction (click/tap) to comply with browser policies
function setupNotificationOnInteraction() {
    if (!window._redemptionNotificationSetupDone) {
        window._redemptionNotificationSetupDone = true;
        document.addEventListener('click', function requestOnFirstClick() {
            if (Notification.permission === 'default') {
                Notification.requestPermission().then(function(permission) {
                    if (permission === 'granted') {
                        console.log('Notification permission granted');
                        // Subscribe to push notifications if available
                        subscribeToPushNotifications();
                    }
                });
            }
            document.removeEventListener('click', requestOnFirstClick);
        }, { once: false });
    }
}

// Subscribe to push notifications via service worker
function subscribeToPushNotifications() {
    if (!('PushManager' in window)) return;

    navigator.serviceWorker.ready.then(function(registration) {
        registration.pushManager.getSubscription().then(function(subscription) {
            if (!subscription) {
                // Create a new subscription
                // The public key needs to be generated on server and set in settings
                // For now, we prepare the infrastructure
                console.log('Push subscription not yet configured on server');
            }
        });
    });
}

// Mobile-specific enhancements
function initMobileIntegration() {
    // Vibration API support
    window.redemptionVibrate = function(pattern) {
        if ('vibrate' in navigator) {
            navigator.vibrate(pattern || [100]);
        }
    };

    // Share API
    window.redemptionShare = function(data) {
        if (navigator.share) {
            navigator.share(data).catch(function(err) {
                console.log('Share failed:', err);
            });
        }
    };

    // Network status
    window.addEventListener('online', function() {
        // Show online toast
        showToast('Back online', 'success');
        // Trigger background sync
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            navigator.serviceWorker.ready.then(function(reg) {
                return reg.sync.register('data-sync');
            });
        }
    });

    window.addEventListener('offline', function() {
        showToast('You are offline. Changes will sync when you reconnect.', 'warning');
    });

    // Request notification permission on interaction
    setupNotificationOnInteraction();
    requestNotificationPermission();
}

// Toast notification helper
function showToast(message, type) {
    type = type || 'info';
    var colors = {
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#3b82f6'
    };
    var icons = {
        success: 'fa-check-circle',
        warning: 'fa-exclamation-triangle',
        danger: 'fa-times-circle',
        info: 'fa-info-circle'
    };

    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:16px;right:16px;z-index:9999;background:' + (colors[type] || colors.info) + ';color:#fff;padding:12px 20px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.15);font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;max-width:90vw;opacity:0;transform:translateY(-10px);transition:all 0.3s;';
    toast.innerHTML = '<i class="fas ' + (icons[type] || icons.info) + '"></i><span>' + message + '</span>';
    document.body.appendChild(toast);

    requestAnimationFrame(function() {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

// Initialize mobile integration on DOM ready
document.addEventListener('DOMContentLoaded', initMobileIntegration);

// Make functions globally available for other scripts
window.showToast = showToast;
</script>
</body>
</html>
