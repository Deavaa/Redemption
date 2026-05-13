<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') | School of Redemption</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <link href="{{ asset('css/modern-components.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="admin-wrapper">
        <nav class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                    <div class="sidebar-brand-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div class="sidebar-brand-text">
                        <span class="sidebar-brand-pre">School of</span>
                        <span class="sidebar-brand-name">REDEMPTION</span>
                    </div>
                </a>
                <button class="sidebar-toggle-btn" id="sidebarCollapseBtn" title="Toggle sidebar">
                    <i class="fas fa-angles-left"></i>
                </button>
            </div>
            @php
                $academicRoutes = ['admin.academic-years.*','admin.terms.*','admin.subjects.*','admin.subject-assignments.*','admin.exams.*','admin.mark-entries.*','admin.classrooms.*','admin.sections.*'];
                $peopleRoutes = ['admin.students.*','admin.teachers.*','admin.staff.*','admin.team-members.*','admin.parents.*'];
                $financeRoutes = ['admin.fees.*','admin.fee-payments.*','admin.payrolls.*','admin.budgets.*','admin.income-expenses.*','admin.finance-statements.*','admin.leaves.*','admin.employee-assets.*'];
                $websiteRoutes = ['admin.sliders.*','admin.gallery-*','admin.branches.*','admin.contact-messages.*'];
                $isAcademicActive = request()->routeIs($academicRoutes);
                $isPeopleActive = request()->routeIs($peopleRoutes);
                $isFinanceActive = request()->routeIs($financeRoutes);
                $isWebsiteActive = request()->routeIs($websiteRoutes);
            @endphp
            <ul class="sidebar-menu">
                <li class="menu-header">MAIN</li>
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
                </li>

                <li class="menu-header">ACADEMIC</li>
                <li class="{{ $isAcademicActive ? 'active' : '' }}">
                    <a href="#academicSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="bi bi-mortarboard"></i><span>Academic</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isAcademicActive ? 'show' : '' }}" id="academicSubmenu">
                        <li><a href="{{ route('admin.academic-years.index') }}"><i class="bi bi-calendar-range"></i> Academic Years</a></li>
                        <li><a href="{{ route('admin.terms.index') }}"><i class="bi bi-bookmark"></i> Terms</a></li>
                        <li><a href="{{ route('admin.subjects.index') }}"><i class="bi bi-book"></i> Subjects</a></li>
                        <li><a href="{{ route('admin.subject-assignments.index') }}"><i class="bi bi-link-45deg"></i> Assign Subjects</a></li>
                        <li><a href="{{ route('admin.exams.index') }}"><i class="bi bi-journal-text"></i> Exams</a></li>
                        <li><a href="{{ route('admin.classrooms.index') }}"><i class="bi bi-building"></i> Classes</a></li>
                        <li><a href="{{ route('admin.mark-entries.index') }}"><i class="bi bi-pencil-square"></i> Mark Entry</a></li>
                    </ul>
                </li>

                <li class="menu-header">PEOPLE</li>
                <li class="{{ $isPeopleActive ? 'active' : '' }}">
                    <a href="#peopleSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-users"></i><span>People</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isPeopleActive ? 'show' : '' }}" id="peopleSubmenu">
                        <li><a href="{{ route('admin.students.index') }}"><i class="fas fa-user-graduate"></i> Students</a></li>
                        <li><a href="{{ route('admin.teachers.index') }}"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                        <li><a href="{{ route('admin.staff.index') }}"><i class="bi bi-person-badge"></i> Staff</a></li>
                        <li><a href="{{ route('admin.team-members.index') }}"><i class="fas fa-users"></i> Team Members</a></li>
                    </ul>
                </li>

                <li class="menu-header">FINANCE</li>
                <li class="{{ $isFinanceActive ? 'active' : '' }}">
                    <a href="#financeSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-wallet"></i><span>Finance</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isFinanceActive ? 'show' : '' }}" id="financeSubmenu">
                        <li><a href="{{ route('admin.fees.index') }}"><i class="fas fa-money-bill-wave"></i> Fees</a></li>
                        <li><a href="{{ route('admin.fee-payments.index') }}"><i class="fas fa-credit-card"></i> Payments</a></li>
                        <li><a href="{{ route('admin.payrolls.index') }}"><i class="fas fa-file-invoice-dollar"></i> Payroll</a></li>
                        <li><a href="{{ route('admin.budgets.index') }}"><i class="fas fa-chart-pie"></i> Budgets</a></li>
                        <li><a href="{{ route('admin.income-expenses.index') }}"><i class="fas fa-exchange-alt"></i> Income/Expense</a></li>
                        <li><a href="{{ route('admin.finance-statements.index') }}"><i class="fas fa-file-alt"></i> Statements</a></li>
                        <li><a href="{{ route('admin.leaves.index') }}"><i class="fas fa-calendar-minus"></i> Leaves</a></li>
                        <li><a href="{{ route('admin.employee-assets.index') }}"><i class="fas fa-boxes"></i> Employee Assets</a></li>
                    </ul>
                </li>

                <li class="menu-header">WEBSITE</li>
                <li class="{{ $isWebsiteActive ? 'active' : '' }}">
                    <a href="#websiteSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-globe"></i><span>Website</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isWebsiteActive ? 'show' : '' }}" id="websiteSubmenu">
                        <li><a href="{{ route('admin.branches.index') }}"><i class="fas fa-map-marker-alt"></i> Branches</a></li>
                        <li><a href="{{ route('admin.sliders.index') }}"><i class="fas fa-images"></i> Sliders</a></li>
                        <li><a href="{{ route('admin.gallery-images.index') }}"><i class="fas fa-image"></i> Gallery Images</a></li>
                        <li><a href="{{ route('admin.gallery-videos.index') }}"><i class="fas fa-video"></i> Gallery Videos</a></li>
                        <li><a href="{{ route('admin.contact-messages.index') }}"><i class="fas fa-envelope"></i> Messages</a></li>
                    </ul>
                </li>

                <li class="menu-header">SYSTEM</li>
                <li class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}"><i class="fas fa-cog"></i><span>Settings</span></a>
                </li>
                <li class="{{ request()->routeIs('admin.audits.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.audits.index') }}"><i class="fas fa-clipboard-list"></i><span>Audit Log</span></a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <div class="sidebar-footer-user">
                    <div class="sidebar-footer-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    <div class="sidebar-footer-info">
                        <span class="sidebar-footer-name">{{ Auth::user()->name }}</span>
                        <span class="sidebar-footer-role">Administrator</span>
                    </div>
                </div>
            </div>
        </nav>

        <div class="sidebar-backdrop d-none" id="sidebarBackdrop"></div>

        <div class="admin-main">
            <nav class="admin-topbar">
                <div class="topbar-left">
                    <button class="topbar-link d-md-none" id="sidebarToggle" style="display:none;">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="topbar-greeting">Hi, <strong>{{ Auth::user()->name }}</strong></span>
                </div>
                <div class="topbar-right">
                    <a href="{{ url('/') }}" class="topbar-link" target="_blank" title="View Website">
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
                            <li><a class="dropdown-item" href="{{ url('/') }}"><i class="fas fa-external-link-alt me-2"></i>View Website</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">@csrf
                                    <button class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="admin-content">
                @if (session('success'))
                    <div class="global-alert global-alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                        <button type="button" class="global-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="global-alert global-alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                        <button type="button" class="global-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function(){
        const sidebar = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const collapseBtn = document.getElementById('sidebarCollapseBtn');
        const mobileToggle = document.getElementById('sidebarToggle');
        const isMobile = () => window.innerWidth < 768;

        // Collapse toggle
        collapseBtn?.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            const icon = collapseBtn.querySelector('i');
            icon.className = sidebar.classList.contains('collapsed')
                ? 'fas fa-angles-right' : 'fas fa-angles-left';
        });

        // Mobile toggle
        function showSidebar(show) {
            if (!sidebar) return;
            sidebar.classList.toggle('show', show);
            backdrop?.classList.toggle('d-none', !show);
        }

        if (mobileToggle) mobileToggle.style.display = isMobile() ? 'flex' : 'none';
        mobileToggle?.addEventListener('click', () => showSidebar(!sidebar.classList.contains('show')));
        backdrop?.addEventListener('click', () => showSidebar(false));

        window.addEventListener('resize', () => {
            if (mobileToggle) mobileToggle.style.display = isMobile() ? 'flex' : 'none';
            if (!isMobile()) showSidebar(false);
        });

        // Auto-dismiss alerts
        document.querySelectorAll('.global-alert').forEach(a => {
            setTimeout(() => { a.style.transition='opacity 0.3s'; a.style.opacity='0'; setTimeout(()=>a.remove(),300); }, 4000);
        });
    })();
    </script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>