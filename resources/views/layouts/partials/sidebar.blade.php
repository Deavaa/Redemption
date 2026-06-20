{{--
    ============================================================================
    SIDEBAR PARTIAL — School of Redemption Admin Layout
    ============================================================================

    This is the consolidated, modernized sidebar. Key improvements over the
    legacy inline sidebar:

    1. Each section header (MAIN, ACADEMIC, PEOPLE, etc.) appears AT MOST
       ONCE regardless of role. Previously, role-specific blocks each had
       their own header, so an admin would see "ACADEMIC" 4 times.

    2. Role-specific items are conditionally included INSIDE each section
       rather than rendering entirely separate sections per role.

    3. Menu search input at the top — filters items live by label text.
       Hidden on mobile (sidebar is already a drawer there).

    4. Active state detection uses the same $is*Active flags the old
       sidebar used — no behavioral changes.

    The PHP @php block above the <ul> sets up the same $menuLevel /
       $is*Active / $isHomeroomTeacher variables the legacy sidebar used,
       so any child partial that references them still works.
    ----------------------------------------------------------------------------
--}}

@php
    // ── STEP 1: Set ALL defaults FIRST, unconditionally.
    // This guarantees $menuLevel and every $is* flag is ALWAYS defined,
    // even if Auth::user() throws, the DB is down, or anything else
    // goes wrong below. No if/else branching — just straight defaults.
    $menuLevel = 'full';
    $isTeacher = false;
    $isAdmin = false;
    $isBranchPrincipal = false;
    $isGeneralManager = false;
    $isLibrarian = false;
    $isCashier = false;
    $isRegistrar = false;
    $isFinance = false;
    $isHR = false;
    $isHomeroomTeacher = false;
    $isAcademicActive = false;
    $isPeopleActive = false;
    $isFinanceActive = false;
    $isAnalysisActive = false;
    $isDocumentActive = false;
    $isLibraryActive = false;
    $isCommActive = false;
    $isWebsiteActive = false;
    $isAdminActive = false;
    $authUser = null;

    // ── STEP 2: Try to load the authenticated user.
    // Wrapped in try/catch so a session/DB error doesn't kill the sidebar.
    try {
        $authUser = \Illuminate\Support\Facades\Auth::user();
    } catch (\Throwable $e) {
        $authUser = null;
    }

    // ── STEP 3: If we have a user, override the defaults.
    if ($authUser) {
        try {
            $role = $authUser->role ?? '';
            $isTeacher         = $role === 'teacher';
            $isAdmin           = in_array($role, ['admin', 'super_admin']);
            $isBranchPrincipal = $role === 'branch_principal';
            $isGeneralManager  = $role === 'general_manager';
            $isLibrarian       = $role === 'librarian';
            $isCashier         = $role === 'cashier';
            $isRegistrar       = $role === 'registrar';
            $isFinance         = $role === 'finance';
            $isHR              = $role === 'hr';

            if ($isTeacher)              $menuLevel = 'teacher';
            elseif ($isLibrarian)        $menuLevel = 'librarian';
            elseif ($isCashier)          $menuLevel = 'cashier';
            elseif ($isRegistrar)        $menuLevel = 'registrar';
            elseif ($isFinance)          $menuLevel = 'finance';
            elseif ($isHR)               $menuLevel = 'hr';
            elseif ($isBranchPrincipal)  $menuLevel = 'branch_principal';
            elseif ($isGeneralManager)   $menuLevel = 'general_manager';
        } catch (\Throwable $e) {
            // Defaults stay — $menuLevel is already 'full'
        }

        // Homeroom teacher check
        if ($isTeacher) {
            try {
                $teacherModel = \App\Models\Teacher::where('user_id', $authUser->id)->first()
                             ?: \App\Models\Teacher::where('email', $authUser->email)->first();
                if ($teacherModel) {
                    $isHomeroomTeacher = $teacherModel->classRooms()->exists()
                                      || $teacherModel->sections()->exists();
                }
            } catch (\Throwable $e) {}
        }

        // Active-state detection
        try {
            $academicSetupRoutes = ['admin.academic-years.*','admin.terms.*','admin.subjects.*','admin.subject-assignments.*','admin.exams.*','admin.classrooms.*','admin.sections.*','admin.class-assets.*'];
            $academicMarksRoutes = ['admin.mark-entries.*','admin.mark-sheet.*','admin.mark-sheet-full.*','admin.mark-roster.*','admin.attendance.*','admin.attendance-delegation.*','admin.mark-entry-locks.*','admin.mark-entry-permissions.*','admin.mark-entry-disallowals.*','admin.mark-entry-configs.*','admin.promotion.*','admin.lesson-plans.*','admin.content-notes.*'];
            $documentRoutes = ['admin.id-card-generate.*','admin.certificate-generate.*','admin.certificate-print.*','admin.id-cards.*','admin.certificates.*','admin.report-exchange.*','admin.transcript.*','admin.leaving-certificate.*','admin.report-card.*','admin.progress-reports.*'];
            $peopleRoutes   = ['admin.students.*','admin.teachers.*','admin.staff.*','admin.team-members.*','admin.parents.*','admin.teacher-assignments.*','admin.enrollments.*','admin.teacher-reviews.*'];
            $financeRoutes  = ['admin.fees.*','admin.fee-payments.*','admin.payrolls.*','admin.budgets.*','admin.income-expenses.*','admin.finance-statements.*','admin.budget-comparison.*','admin.financial-comparison.*'];
            $hrRoutes       = ['admin.leaves.*','admin.employee-assets.*'];
            $analysisRoutes = ['admin.performance-analysis.*','admin.performance-comparison.*','admin.psychological-analysis.*','admin.performance.*'];
            $libraryRoutes  = ['admin.library.*','admin.video-library.*'];
            $commRoutes     = ['admin.calendar.*','admin.announcements.*','admin.telegram.*','admin.chat.*'];
            $websiteRoutes  = ['admin.sliders.*','admin.gallery-*','admin.branches.*','admin.contact-messages.*','admin.web-content.*','admin.news.*'];
            $adminRoutes    = ['admin.user-access.*','admin.settings.*','admin.roles.*','admin.backup.*','admin.audits.*','admin.email-inbox.*','admin.email-inbox-settings*','admin.bank-integration.*','admin.club-follow-up-configs.*','admin.graphical-reports.*','admin.exam-questions.*'];

            $isAcademicActive = request()->routeIs([...$academicSetupRoutes, ...$academicMarksRoutes, 'admin.attendance.*', 'admin.attendance-delegation.*']);
            $isPeopleActive   = request()->routeIs($peopleRoutes);
            $isFinanceActive  = request()->routeIs([...$financeRoutes, ...$hrRoutes]);
            $isAnalysisActive = request()->routeIs($analysisRoutes);
            $isDocumentActive = request()->routeIs($documentRoutes);
            $isLibraryActive  = request()->routeIs($libraryRoutes);
            $isCommActive     = request()->routeIs($commRoutes);
            $isWebsiteActive  = request()->routeIs($websiteRoutes);
            $isAdminActive    = request()->routeIs($adminRoutes);
        } catch (\Throwable $e) {}
    }
@endphp

<nav class="admin-sidebar" id="adminSidebar">
    {{-- Brand --}}
    <div class="sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand-icon"><i class="fas fa-graduation-cap"></i></div>
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-pre">{{ __('app.brand_pre') }}</span>
                <span class="sidebar-brand-name">{{ __('app.brand_name') }}</span>
            </div>
        </a>
    </div>

    {{-- Menu search (desktop only — hidden via CSS on small screens) --}}
    <div class="sidebar-search d-none d-lg-block">
        <div class="sidebar-search-wrap">
            <i class="fas fa-search"></i>
            <input type="text"
                   class="sidebar-search-input"
                   id="sidebarSearchInput"
                   placeholder="Search menu…"
                   autocomplete="off"
                   aria-label="Search navigation menu">
        </div>
    </div>

    {{-- Menu --}}
    <div class="sidebar-menu-wrap">
        <ul class="sidebar-menu" id="sidebarMenu">

            {{-- ════════════════════════════════════════════════════════
                 MAIN
            ════════════════════════════════════════════════════════ --}}
            <li class="menu-header" data-section="main">MAIN</li>
            <li data-menu-item="dashboard">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i><span>Dashboard</span>
                </a>
            </li>

            @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'registrar', 'finance', 'cashier']))
            <li data-menu-item="enrollments">
                <a href="{{ route('admin.enrollments.index') }}" class="{{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i><span>Enrollments</span>
                </a>
            </li>
            @endif

            {{-- ════════════════════════════════════════════════════════
                 ACADEMIC  (single header, role-aware contents)
            ════════════════════════════════════════════════════════ --}}
            @if(!in_array(($menuLevel ?? 'full'), ['librarian', 'cashier']))
            <li class="menu-header" data-section="academic">ACADEMIC</li>

            @if(($menuLevel ?? 'full') === 'teacher')
                {{-- Teacher: just marks & planning, no setup --}}
                <li class="{{ ($isAcademicActive ?? false) ? 'has-active-child' : '' }}" data-menu-item="academic">
                    <a href="#academicSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-graduation-cap"></i><span>Marks &amp; Assessment</span>
                        <i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ ($isAcademicActive ?? false) ? 'show' : '' }}" id="academicSubmenu">
                        <li><a href="{{ route('admin.mark-entries.index') }}" class="{{ request()->routeIs('admin.mark-entries.*') ? 'active' : '' }}"><i class="fas fa-pen"></i> Mark Entry</a></li>
                        <li><a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}"><i class="fas fa-clipboard-check"></i> Attendance</a></li>
                        <li><a href="{{ route('admin.attendance-delegation.index') }}" class="{{ request()->routeIs('admin.attendance-delegation.*') ? 'active' : '' }}"><i class="fas fa-user-check"></i> Attendance Delegation</a></li>
                        @if($isHomeroomTeacher ?? false)
                        <li><a href="{{ route('admin.mark-sheet.index') }}" class="{{ request()->routeIs('admin.mark-sheet.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Mark Sheet</a></li>
                        <li><a href="{{ route('admin.mark-sheet-full.index') }}" class="{{ request()->routeIs('admin.mark-sheet-full.*') ? 'active' : '' }}"><i class="fas fa-table"></i> Full Mark Sheet</a></li>
                        @endif
                        <li><a href="{{ route('admin.mark-roster.index') }}" class="{{ request()->routeIs('admin.mark-roster.*') ? 'active' : '' }}"><i class="fas fa-list-ol"></i> Mark Roster</a></li>
                        <li class="sidebar-subsection-label">Planning</li>
                        <li><a href="{{ route('admin.lesson-plans.index') }}" class="{{ request()->routeIs('admin.lesson-plans.*') ? 'active' : '' }}"><i class="fas fa-chalkboard"></i> Lesson Plans</a></li>
                        <li><a href="{{ route('admin.content-notes.index') }}" class="{{ request()->routeIs('admin.content-notes.*') ? 'active' : '' }}"><i class="fas fa-sticky-note"></i> Note Bank</a></li>
                        <li><a href="{{ route('admin.assessment-questions.index') }}" class="{{ request()->routeIs('admin.assessment-questions.*') ? 'active' : '' }}"><i class="fas fa-brain"></i> Self-Assessment</a></li>
                    </ul>
                </li>
            @elseif(($menuLevel ?? 'full') === 'registrar')
                {{-- Registrar: only academic setup --}}
                <li class="{{ ($isAcademicActive ?? false) ? 'has-active-child' : '' }}" data-menu-item="academic">
                    <a href="#academicSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-graduation-cap"></i><span>Academic Setup</span>
                        <i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ ($isAcademicActive ?? false) ? 'show' : '' }}" id="academicSubmenu">
                        <li><a href="{{ route('admin.academic-years.index') }}" class="{{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}"><i class="fas fa-calendar"></i> Academic Years</a></li>
                        <li><a href="{{ route('admin.terms.index') }}" class="{{ request()->routeIs('admin.terms.*') ? 'active' : '' }}"><i class="fas fa-bookmark"></i> Terms</a></li>
                        <li><a href="{{ route('admin.classrooms.index') }}" class="{{ request()->routeIs('admin.classrooms.*') ? 'active' : '' }}"><i class="fas fa-building"></i> Classes &amp; Sections</a></li>
                        <li><a href="{{ route('admin.class-assets.index') }}" class="{{ request()->routeIs('admin.class-assets.*') ? 'active' : '' }}"><i class="fas fa-boxes"></i> Class Assets</a></li>
                    </ul>
                </li>
            @else
                {{-- Full / general_manager / branch_principal / finance / hr: full academic menu --}}
                <li class="{{ ($isAcademicActive ?? false) ? 'has-active-child' : '' }}" data-menu-item="academic">
                    <a href="#academicSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-graduation-cap"></i><span>Academic Management</span>
                        <i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ ($isAcademicActive ?? false) ? 'show' : '' }}" id="academicSubmenu">
                        @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal']))
                        <li class="sidebar-subsection-label">Setup</li>
                        <li><a href="{{ route('admin.academic-years.index') }}" class="{{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}"><i class="fas fa-calendar"></i> Academic Years</a></li>
                        <li><a href="{{ route('admin.terms.index') }}" class="{{ request()->routeIs('admin.terms.*') ? 'active' : '' }}"><i class="fas fa-bookmark"></i> Terms</a></li>
                        <li><a href="{{ route('admin.subjects.index') }}" class="{{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}"><i class="fas fa-book"></i> Subjects</a></li>
                        <li><a href="{{ route('admin.subject-assignments.index') }}" class="{{ request()->routeIs('admin.subject-assignments.*') ? 'active' : '' }}"><i class="fas fa-link"></i> Assign Subjects</a></li>
                        <li><a href="{{ route('admin.classrooms.index') }}" class="{{ request()->routeIs('admin.classrooms.*') ? 'active' : '' }}"><i class="fas fa-building"></i> Classes &amp; Sections</a></li>
                        <li><a href="{{ route('admin.class-assets.index') }}" class="{{ request()->routeIs('admin.class-assets.*') ? 'active' : '' }}"><i class="fas fa-boxes"></i> Class Assets</a></li>
                        <li><a href="{{ route('admin.exams.index') }}" class="{{ request()->routeIs('admin.exams.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Exams</a></li>
                        @endif

                        <li class="sidebar-subsection-label">Marks &amp; Assessment</li>
                        <li><a href="{{ route('admin.mark-entries.index') }}" class="{{ request()->routeIs('admin.mark-entries.*') ? 'active' : '' }}"><i class="fas fa-pen"></i> Mark Entry</a></li>
                        <li><a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}"><i class="fas fa-clipboard-check"></i> Attendance</a></li>
                        <li><a href="{{ route('admin.attendance-delegation.index') }}" class="{{ request()->routeIs('admin.attendance-delegation.*') ? 'active' : '' }}"><i class="fas fa-user-check"></i> Attendance Delegation</a></li>
                        <li><a href="{{ route('admin.mark-sheet.index') }}" class="{{ request()->routeIs('admin.mark-sheet.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Mark Sheet</a></li>
                        <li><a href="{{ route('admin.mark-sheet-full.index') }}" class="{{ request()->routeIs('admin.mark-sheet-full.*') ? 'active' : '' }}"><i class="fas fa-table"></i> Full Mark Sheet</a></li>
                        <li><a href="{{ route('admin.mark-roster.index') }}" class="{{ request()->routeIs('admin.mark-roster.*') ? 'active' : '' }}"><i class="fas fa-list-ol"></i> Mark Roster</a></li>

                        @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal']))
                        <li class="sidebar-subsection-label">Promotion &amp; Locks</li>
                        <li><a href="{{ route('admin.promotion.index') }}" class="{{ request()->routeIs('admin.promotion.*') ? 'active' : '' }}"><i class="fas fa-level-up-alt"></i> Promotion &amp; Detention</a></li>
                        <li><a href="{{ route('admin.mark-entry-locks.index') }}" class="{{ request()->routeIs('admin.mark-entry-locks.*') ? 'active' : '' }}"><i class="fas fa-lock"></i> Mark Entry Locks</a></li>
                        <li><a href="{{ route('admin.mark-entry-permissions.index') }}" class="{{ request()->routeIs('admin.mark-entry-permissions.*') ? 'active' : '' }}"><i class="fas fa-key"></i> Mark Edit Permissions</a></li>
                        <li><a href="{{ route('admin.mark-entry-disallowals.index') }}" class="{{ request()->routeIs('admin.mark-entry-disallowals.*') ? 'active' : '' }}"><i class="fas fa-ban"></i> Mark Entry Disallowals</a></li>
                        <li><a href="{{ route('admin.mark-entry-configs.index') }}" class="{{ request()->routeIs('admin.mark-entry-configs.*') ? 'active' : '' }}"><i class="fas fa-cog"></i> Mark Entry Config</a></li>
                        @endif

                        <li class="sidebar-subsection-label">Planning</li>
                        <li><a href="{{ route('admin.lesson-plans.index') }}" class="{{ request()->routeIs('admin.lesson-plans.*') ? 'active' : '' }}"><i class="fas fa-chalkboard"></i> Lesson Plans</a></li>
                        <li><a href="{{ route('admin.content-notes.index') }}" class="{{ request()->routeIs('admin.content-notes.*') ? 'active' : '' }}"><i class="fas fa-sticky-note"></i> Note Bank</a></li>
                        <li><a href="{{ route('admin.assessment-questions.index') }}" class="{{ request()->routeIs('admin.assessment-questions.*') ? 'active' : '' }}"><i class="fas fa-brain"></i> Self-Assessment</a></li>
                    </ul>
                </li>
            @endif
            @endif

            {{-- ════════════════════════════════════════════════════════
                 PEOPLE
            ════════════════════════════════════════════════════════ --}}
            @if(!in_array(($menuLevel ?? 'full'), ['librarian', 'cashier']))
            <li class="menu-header" data-section="people">PEOPLE</li>

            @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'registrar', 'finance']))
            <li class="{{ ($isPeopleActive ?? false) ? 'has-active-child' : '' }}" data-menu-item="people">
                <a href="#peopleSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                    <i class="fas fa-users"></i><span>Students &amp; Staff</span>
                    <i class="fas fa-chevron-down sidebar-chevron"></i>
                </a>
                <ul class="collapse {{ ($isPeopleActive ?? false) ? 'show' : '' }}" id="peopleSubmenu">
                    <li><a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}"><i class="fas fa-user-graduate"></i> Students</a></li>
                    <li><a href="{{ route('admin.teachers.index') }}" class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                    @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal']))
                    <li><a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}"><i class="fas fa-user-tie"></i> Staff</a></li>
                    <li><a href="{{ route('admin.parents.index') }}" class="{{ request()->routeIs('admin.parents.*') ? 'active' : '' }}"><i class="fas fa-user-friends"></i> Parents</a></li>
                    <li><a href="{{ route('admin.team-members.index') }}" class="{{ request()->routeIs('admin.team-members.*') ? 'active' : '' }}"><i class="fas fa-people-arrows"></i> Team Members</a></li>
                    <li><a href="{{ route('admin.teacher-assignments.index') }}" class="{{ request()->routeIs('admin.teacher-assignments.*') ? 'active' : '' }}"><i class="fas fa-link"></i> Teacher Assignments</a></li>
                    <li><a href="{{ route('admin.teacher-reviews.index') }}" class="{{ request()->routeIs('admin.teacher-reviews.*') ? 'active' : '' }}"><i class="fas fa-star"></i> Teacher Reviews</a></li>
                    @endif
                </ul>
            </li>
            @elseif(($menuLevel ?? 'full') === 'teacher')
            <li data-menu-item="teacher-reviews">
                <a href="{{ route('admin.teacher-reviews.index') }}" class="{{ request()->routeIs('admin.teacher-reviews.*') ? 'active' : '' }}">
                    <i class="fas fa-star"></i><span>My Reviews</span>
                </a>
            </li>
            @endif
            @endif

            {{-- ════════════════════════════════════════════════════════
                 FINANCE & HR
            ════════════════════════════════════════════════════════ --}}
            @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'finance', 'cashier', 'hr']))
            <li class="menu-header" data-section="finance">FINANCE &amp; HR</li>

            @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'finance', 'cashier']))
            <li class="{{ ($isFinanceActive ?? false) ? 'has-active-child' : '' }}" data-menu-item="finance">
                <a href="#financeSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                    <i class="fas fa-money-bill-wave"></i><span>Finance</span>
                    <i class="fas fa-chevron-down sidebar-chevron"></i>
                </a>
                <ul class="collapse {{ ($isFinanceActive ?? false) ? 'show' : '' }}" id="financeSubmenu">
                    <li><a href="{{ route('admin.fees.index') }}" class="{{ request()->routeIs('admin.fees.*') ? 'active' : '' }}"><i class="fas fa-hand-holding-usd"></i> Fees</a></li>
                    <li><a href="{{ route('admin.fee-payments.index') }}" class="{{ request()->routeIs('admin.fee-payments.*') ? 'active' : '' }}"><i class="fas fa-receipt"></i> Fee Payments</a></li>
                    <li><a href="{{ route('admin.payrolls.index') }}" class="{{ request()->routeIs('admin.payrolls.*') ? 'active' : '' }}"><i class="fas fa-money-check-alt"></i> Payrolls</a></li>
                    <li><a href="{{ route('admin.budgets.index') }}" class="{{ request()->routeIs('admin.budgets.*') ? 'active' : '' }}"><i class="fas fa-coins"></i> Budgets</a></li>
                    <li><a href="{{ route('admin.income-expenses.index') }}" class="{{ request()->routeIs('admin.income-expenses.*') ? 'active' : '' }}"><i class="fas fa-exchange-alt"></i> Income / Expense</a></li>
                    <li><a href="{{ route('admin.finance-statements.index') }}" class="{{ request()->routeIs('admin.finance-statements.*') ? 'active' : '' }}"><i class="fas fa-balance-sheet"></i> Finance Statements</a></li>
                    <li class="sidebar-subsection-label">Comparisons</li>
                    <li><a href="{{ route('admin.budget-comparison.index') }}" class="{{ request()->routeIs('admin.budget-comparison.*') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> Budget Comparison</a></li>
                    <li><a href="{{ route('admin.financial-comparison.index') }}" class="{{ request()->routeIs('admin.financial-comparison.*') ? 'active' : '' }}"><i class="fas fa-balance-scale"></i> Financial Comparison</a></li>
                </ul>
            </li>
            @endif

            @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'hr']))
            <li data-menu-item="hr">
                <a href="#hrSubmenu" data-bs-toggle="collapse" class="submenu-toggle {{ request()->routeIs(['admin.leaves.*', 'admin.employee-assets.*']) ? 'active' : '' }}">
                    <i class="fas fa-id-card-alt"></i><span>HR</span>
                    <i class="fas fa-chevron-down sidebar-chevron"></i>
                </a>
                <ul class="collapse {{ request()->routeIs(['admin.leaves.*', 'admin.employee-assets.*']) ? 'show' : '' }}" id="hrSubmenu">
                    <li><a href="{{ route('admin.leaves.index') }}" class="{{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}"><i class="fas fa-calendar-times"></i> Leaves</a></li>
                    <li><a href="{{ route('admin.employee-assets.index') }}" class="{{ request()->routeIs('admin.employee-assets.*') ? 'active' : '' }}"><i class="fas fa-briefcase"></i> Employee Assets</a></li>
                </ul>
            </li>
            @endif
            @endif

            {{-- ════════════════════════════════════════════════════════
                 ANALYSIS
            ════════════════════════════════════════════════════════ --}}
            @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal']))
            <li class="menu-header" data-section="analysis">ANALYSIS</li>
            <li class="{{ ($isAnalysisActive ?? false) ? 'has-active-child' : '' }}" data-menu-item="analysis">
                <a href="#analysisSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                    <i class="fas fa-chart-pie"></i><span>Performance Analysis</span>
                    <i class="fas fa-chevron-down sidebar-chevron"></i>
                </a>
                <ul class="collapse {{ ($isAnalysisActive ?? false) ? 'show' : '' }}" id="analysisSubmenu">
                    <li><a href="{{ route('admin.performance.index') }}" class="{{ request()->routeIs('admin.performance.*') ? 'active' : '' }}"><i class="fas fa-chart-bar"></i> Overview</a></li>
                    <li><a href="{{ route('admin.performance.class-comparison') }}" class="{{ request()->routeIs('admin.performance.class-comparison') ? 'active' : '' }}"><i class="fas fa-graduation-cap"></i> Class Comparison</a></li>
                    <li><a href="{{ route('admin.performance.branch-comparison') }}" class="{{ request()->routeIs('admin.performance.branch-comparison') ? 'active' : '' }}"><i class="fas fa-code-branch"></i> Branch Comparison</a></li>
                    <li><a href="{{ route('admin.performance.gender') }}" class="{{ request()->routeIs('admin.performance.gender') ? 'active' : '' }}"><i class="fas fa-venus-mars"></i> Gender Analysis</a></li>
                    <li><a href="{{ route('admin.performance.at-risk') }}" class="{{ request()->routeIs('admin.performance.at-risk') ? 'active' : '' }}"><i class="fas fa-exclamation-triangle"></i> At-Risk Students</a></li>
                    <li><a href="{{ route('admin.performance-comparison.index') }}" class="{{ request()->routeIs('admin.performance-comparison.*') ? 'active' : '' }}"><i class="fas fa-balance-scale"></i> Branch Performance</a></li>
                    <li><a href="{{ route('admin.psychological-analysis.index') }}" class="{{ request()->routeIs('admin.psychological-analysis.*') ? 'active' : '' }}"><i class="fas fa-brain"></i> Psychological Analysis</a></li>
                </ul>
            </li>
            @endif

            {{-- ════════════════════════════════════════════════════════
                 DOCUMENTS & REPORTS
            ════════════════════════════════════════════════════════ --}}
            @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'registrar']))
            <li class="menu-header" data-section="documents">DOCUMENTS &amp; REPORTS</li>
            <li class="{{ ($isDocumentActive ?? false) ? 'has-active-child' : '' }}" data-menu-item="documents">
                <a href="#documentSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                    <i class="fas fa-folder-open"></i><span>Documents &amp; Reports</span>
                    <i class="fas fa-chevron-down sidebar-chevron"></i>
                </a>
                <ul class="collapse {{ ($isDocumentActive ?? false) ? 'show' : '' }}" id="documentSubmenu">
                    <li class="sidebar-subsection-label">Certificates &amp; Documents</li>
                    <li><a href="{{ route('admin.certificate-generate.index') }}" class="{{ request()->routeIs('admin.certificate-generate.*') ? 'active' : '' }}"><i class="fas fa-award"></i> Certificate Generator</a></li>
                    <li><a href="{{ route('admin.certificate-print.index') }}" class="{{ request()->routeIs('admin.certificate-print.*') ? 'active' : '' }}"><i class="fas fa-print"></i> Print on Certificate</a></li>
                    <li><a href="{{ route('admin.transcript.index') }}" class="{{ request()->routeIs('admin.transcript.*') ? 'active' : '' }}"><i class="fas fa-scroll"></i> Academic Transcript</a></li>
                    <li><a href="{{ route('admin.leaving-certificate.index') }}" class="{{ request()->routeIs('admin.leaving-certificate.*') ? 'active' : '' }}"><i class="fas fa-file-signature"></i> Leaving Certificate</a></li>
                    <li><a href="{{ route('admin.id-card-generate.index') }}" class="{{ request()->routeIs('admin.id-card-generate.*') ? 'active' : '' }}"><i class="fas fa-id-badge"></i> ID Card Generator</a></li>
                    <li class="sidebar-subsection-label">Reports</li>
                    <li><a href="{{ route('admin.report-card.index') }}" class="{{ request()->routeIs('admin.report-card.*') ? 'active' : '' }}"><i class="fas fa-id-card"></i> Report Cards</a></li>
                    <li><a href="{{ route('admin.progress-reports.index') }}" class="{{ request()->routeIs('admin.progress-reports.*') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> Progress Reports</a></li>
                    <li class="sidebar-subsection-label">Exchange</li>
                    <li><a href="{{ route('admin.report-exchange.index') }}" class="{{ request()->routeIs('admin.report-exchange.*') ? 'active' : '' }}"><i class="fas fa-exchange-alt"></i> Report Exchange</a></li>
                </ul>
            </li>
            @endif

            {{-- ════════════════════════════════════════════════════════
                 RESOURCES
            ════════════════════════════════════════════════════════ --}}
            @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'teacher', 'librarian']))
            <li class="menu-header" data-section="resources">RESOURCES</li>
            <li class="{{ ($isLibraryActive ?? false) ? 'has-active-child' : '' }}" data-menu-item="library">
                @if(in_array(($menuLevel ?? 'full'), ['teacher', 'librarian']))
                <a href="{{ route('admin.video-library.index') }}" class="{{ request()->routeIs('admin.video-library.*') ? 'active' : '' }}">
                    <i class="fas fa-book-open"></i><span>Digital Library</span>
                </a>
                @else
                <a href="#librarySubmenu" data-bs-toggle="collapse" class="submenu-toggle {{ ($isLibraryActive ?? false) ? 'active' : '' }}">
                    <i class="fas fa-book-open"></i><span>Digital Library</span>
                    <i class="fas fa-chevron-down sidebar-chevron"></i>
                </a>
                <ul class="collapse {{ ($isLibraryActive ?? false) ? 'show' : '' }}" id="librarySubmenu">
                    <li><a href="{{ route('admin.library.index') }}" class="{{ request()->routeIs('admin.library.*') ? 'active' : '' }}"><i class="fas fa-book"></i> Book Library</a></li>
                    <li><a href="{{ route('admin.video-library.index') }}" class="{{ request()->routeIs('admin.video-library.*') ? 'active' : '' }}"><i class="fab fa-youtube"></i> Video Library</a></li>
                </ul>
                @endif
            </li>
            @endif

            {{-- ════════════════════════════════════════════════════════
                 COMMUNICATION
            ════════════════════════════════════════════════════════ --}}
            <li class="menu-header" data-section="communication">COMMUNICATION</li>
            <li class="{{ ($isCommActive ?? false) ? 'has-active-child' : '' }}" data-menu-item="communication">
                @if(in_array(($menuLevel ?? 'full'), ['teacher', 'librarian', 'cashier', 'finance', 'hr']))
                <a href="{{ route('admin.calendar.index') }}" class="{{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i><span>Calendar &amp; Announcements</span>
                </a>
                @else
                <a href="#commSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                    <i class="fas fa-broadcast-tower"></i><span>Communication</span>
                    <i class="fas fa-chevron-down sidebar-chevron"></i>
                </a>
                <ul class="collapse {{ ($isCommActive ?? false) ? 'show' : '' }}" id="commSubmenu">
                    <li><a href="{{ route('admin.calendar.index') }}" class="{{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}"><i class="fas fa-calendar-alt"></i> Academic Calendar</a></li>
                    <li><a href="{{ route('admin.announcements.index') }}" class="{{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}"><i class="fas fa-bullhorn"></i> Announcements</a></li>
                    <li><a href="{{ route('admin.chat.index') }}" class="{{ request()->routeIs('admin.chat.*') ? 'active' : '' }}"><i class="fas fa-comment-dots"></i> Chat</a></li>
                    <li><a href="{{ route('admin.telegram.index') }}" class="{{ request()->routeIs('admin.telegram.*') ? 'active' : '' }}"><i class="fab fa-telegram"></i> Telegram</a></li>
                </ul>
                @endif
            </li>

            {{-- ════════════════════════════════════════════════════════
                 WEBSITE
            ════════════════════════════════════════════════════════ --}}
            @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager']))
            <li class="menu-header" data-section="website">WEBSITE</li>
            <li class="{{ ($isWebsiteActive ?? false) ? 'has-active-child' : '' }}" data-menu-item="website">
                <a href="#websiteSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                    <i class="fas fa-globe"></i><span>Website Management</span>
                    <i class="fas fa-chevron-down sidebar-chevron"></i>
                </a>
                <ul class="collapse {{ ($isWebsiteActive ?? false) ? 'show' : '' }}" id="websiteSubmenu">
                    <li><a href="{{ route('admin.branches.index') }}" class="{{ request()->routeIs('admin.branches.*') ? 'active' : '' }}"><i class="fas fa-map-marker-alt"></i> Branches</a></li>
                    <li><a href="{{ route('admin.web-content.index') }}" class="{{ request()->routeIs('admin.web-content.*') ? 'active' : '' }}"><i class="fas fa-paint-brush"></i> Web Content</a></li>
                    <li><a href="{{ route('admin.sliders.index') }}" class="{{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}"><i class="fas fa-images"></i> Sliders</a></li>
                    <li><a href="{{ route('admin.gallery-images.index') }}" class="{{ request()->routeIs('admin.gallery-images.*') ? 'active' : '' }}"><i class="fas fa-image"></i> Gallery Images</a></li>
                    <li><a href="{{ route('admin.gallery-videos.index') }}" class="{{ request()->routeIs('admin.gallery-videos.*') ? 'active' : '' }}"><i class="fas fa-video"></i> Gallery Videos</a></li>
                    <li><a href="{{ route('admin.news.index') }}" class="{{ request()->routeIs('admin.news.*') ? 'active' : '' }}"><i class="fas fa-newspaper"></i> News</a></li>
                    <li><a href="{{ route('admin.contact-messages.index') }}" class="{{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}"><i class="fas fa-envelope"></i> Messages</a></li>
                </ul>
            </li>
            @elseif(($menuLevel ?? 'full') === 'branch_principal')
            <li class="menu-header" data-section="transfers">TRANSFERS</li>
            <li data-menu-item="transfer">
                <a href="{{ route('admin.students.index') }}?filter=transfer" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                    <i class="fas fa-exchange-alt"></i><span>Student Transfer</span>
                </a>
            </li>
            @endif

            {{-- ════════════════════════════════════════════════════════
                 ADMINISTRATION
            ════════════════════════════════════════════════════════ --}}
            @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager']))
            <li class="menu-header" data-section="administration">ADMINISTRATION</li>
            <li class="{{ ($isAdminActive ?? false) ? 'has-active-child' : '' }}" data-menu-item="admin">
                <a href="#adminSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                    <i class="fas fa-cogs"></i><span>System Admin</span>
                    <i class="fas fa-chevron-down sidebar-chevron"></i>
                </a>
                <ul class="collapse {{ ($isAdminActive ?? false) ? 'show' : '' }}" id="adminSubmenu">
                    <li class="sidebar-subsection-label">Access Control</li>
                    <li><a href="{{ route('admin.user-access.teachers') }}" class="{{ request()->routeIs('admin.user-access.teachers*') ? 'active' : '' }}"><i class="fas fa-chalkboard-teacher"></i> Teacher Access</a></li>
                    <li><a href="{{ route('admin.user-access.students') }}" class="{{ request()->routeIs('admin.user-access.students*') ? 'active' : '' }}"><i class="fas fa-user-graduate"></i> Student Access</a></li>
                    <li><a href="{{ route('admin.user-access.parents') }}" class="{{ request()->routeIs('admin.user-access.parents*') ? 'active' : '' }}"><i class="fas fa-user-friends"></i> Parent Access</a></li>
                    @if(($menuLevel ?? 'full') === 'full')
                    <li><a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"><i class="fas fa-shield-alt"></i> Roles &amp; Permissions</a></li>
                    @endif

                    <li class="sidebar-subsection-label">Configuration</li>
                    <li><a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><i class="fas fa-cog"></i> Settings</a></li>

                    <li class="sidebar-subsection-label">Data &amp; Backup</li>
                    <li><a href="{{ route('admin.backup.index') }}" class="{{ request()->routeIs('admin.backup.*') ? 'active' : '' }}"><i class="fas fa-database"></i> Database Backup</a></li>
                    <li><a href="{{ route('admin.audits.index') }}" class="{{ request()->routeIs('admin.audits.*') ? 'active' : '' }}"><i class="fas fa-clipboard-list"></i> Audit Log</a></li>

                    <li class="sidebar-subsection-label">Integrations</li>
                    <li><a href="{{ route('admin.email-inbox.index') }}" class="{{ request()->routeIs('admin.email-inbox*') ? 'active' : '' }}"><i class="fas fa-envelope-open-text"></i> Email Inbox</a></li>
                    <li><a href="{{ route('admin.bank-integration.index') }}" class="{{ request()->routeIs('admin.bank-integration*') ? 'active' : '' }}"><i class="fas fa-university"></i> Bank Integration</a></li>
                    <li><a href="{{ route('admin.club-follow-up-configs.index') }}" class="{{ request()->routeIs('admin.club-follow-up-configs*') ? 'active' : '' }}"><i class="fas fa-clipboard-check"></i> Club Follow-up Config</a></li>

                    <li class="sidebar-subsection-label">Reports &amp; Analytics</li>
                    <li><a href="{{ route('admin.graphical-reports.index') }}" class="{{ request()->routeIs('admin.graphical-reports*') ? 'active' : '' }}"><i class="fas fa-chart-bar"></i> Graphical Reports</a></li>
                    <li><a href="{{ route('admin.exam-questions.index') }}" class="{{ request()->routeIs('admin.exam-questions*') ? 'active' : '' }}"><i class="fas fa-question-circle"></i> Exam Questions Review</a></li>
                </ul>
            </li>
            @endif

            {{-- Search "no results" message --}}
            <li class="search-no-results">
                <i class="fas fa-search" style="opacity:0.5;display:block;margin-bottom:6px;"></i>
                No matching pages
            </li>

        </ul>
    </div>

    {{-- Footer: user info + mobile app link --}}
    <div class="sidebar-footer">
        <a href="{{ route('app.download') }}"
           class="d-flex align-items-center gap-2 px-2 py-2 mb-2 rounded-3 text-decoration-none"
           style="background:rgba(99,102,241,0.1);color:#a5b4fc;font-size:12px;font-weight:600;transition:all .2s;"
           onmouseover="this.style.background='rgba(99,102,241,0.2)'"
           onmouseout="this.style.background='rgba(99,102,241,0.1)'">
            <i class="fas fa-mobile-alt" style="font-size:14px;"></i>
            <span>Download Mobile App</span>
            <i class="fas fa-external-link-alt ms-auto" style="font-size:9px;opacity:0.5;"></i>
        </a>
        <div class="sidebar-footer-user" data-bs-toggle="dropdown" role="button" tabindex="0"
             aria-haspopup="true" aria-expanded="false" id="sidebarUserDropdown">
            <div class="sidebar-footer-avatar">{{ ($authUser ?? null) ? strtoupper(substr($authUser->name, 0, 1)) : '?' }}</div>
            <div class="sidebar-footer-info">
                <span class="sidebar-footer-name">{{ ($authUser ?? null)?->name ?? 'Guest' }}</span>
                <span class="sidebar-footer-role">{{ ($authUser ?? null)?->display_role ?? '' }}</span>
            </div>
            <i class="fas fa-ellipsis-v" style="color:rgba(255,255,255,0.4);font-size:12px;"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="sidebarUserDropdown" style="min-width:200px;">
            <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="fas fa-user me-2"></i>My Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}" id="sidebarLogoutForm">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                </form>
            </li>
        </ul>
    </div>
</nav>

{{-- Menu search script — filters items live by label text --}}
<script>
(function () {
    var input = document.getElementById('sidebarSearchInput');
    var menu = document.getElementById('sidebarMenu');
    if (!input || !menu) return;

    input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase();
        var items = menu.querySelectorAll('li[data-menu-item], li[data-section]');

        if (!q) {
            // Reset: show everything
            items.forEach(function (el) { el.classList.remove('search-hidden'); });
            menu.classList.remove('searching');
            return;
        }

        menu.classList.add('searching');
        var anyVisible = false;

        items.forEach(function (el) {
            // For top-level data-menu-item li's, check the link text + nested submenu links
            var text = '';
            var link = el.querySelector('a:not([data-bs-toggle])') || el.querySelector('a');
            if (link) text = link.textContent.toLowerCase();
            // Also check nested submenu items
            el.querySelectorAll('ul.collapse li a').forEach(function (a) {
                text += ' ' + a.textContent.toLowerCase();
            });

            var matches = text.indexOf(q) !== -1;
            el.classList.toggle('search-hidden', !matches);
            if (matches) anyVisible = true;

            // If this item matches, expand its submenu so the matching child is visible
            if (matches) {
                var submenu = el.querySelector('ul.collapse');
                if (submenu) submenu.classList.add('show');
            }
        });

        // Also hide section headers whose entire section has no matches
        var sections = menu.querySelectorAll('li[data-section], li.menu-header');
        sections.forEach(function (hdr) {
            var next = hdr.nextElementSibling;
            var hasVisibleChild = false;
            while (next && !next.matches('li.menu-header') && !next.matches('li[data-section]')) {
                if (!next.classList.contains('search-hidden')) {
                    hasVisibleChild = true;
                    break;
                }
                next = next.nextElementSibling;
            }
            hdr.classList.toggle('search-hidden', !hasVisibleChild);
        });

        // Show "no results" message if nothing matches
        var noResults = menu.querySelector('.search-no-results');
        if (noResults) noResults.classList.toggle('search-hidden', anyVisible);
    });

    // Keyboard shortcut: "/" focuses the search input (desktop only)
    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' &&
            document.activeElement.tagName !== 'TEXTAREA' && document.activeElement.tagName !== 'SELECT') {
            e.preventDefault();
            input.focus();
        }
        // Esc clears the search
        if (e.key === 'Escape' && document.activeElement === input) {
            input.value = '';
            input.dispatchEvent(new Event('input'));
            input.blur();
        }
    });
})();
</script>
