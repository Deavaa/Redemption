<?php
 $base = getcwd();

// Build the layout file in parts to avoid heredoc issues with Blade syntax
 $layout = '';

// Part 1: Head
 $layout .= '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield(\'title\', \'Admin\') - Redemption School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset(\'css/admin.css\') }}" rel="stylesheet">
    @stack(\'styles\')
</head>
<body>
';

// Part 2: Wrapper + Sidebar
 $layout .= '<div class="admin-wrapper">
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="{{ route(\'admin.dashboard\') }}" class="sidebar-brand">
                <div class="sidebar-brand-icon"><i class="fas fa-graduation-cap"></i></div>
                <div class="sidebar-brand-text">
                    <span class="sidebar-brand-pre">School of</span>
                    <span class="sidebar-brand-name">REDEMPTION</span>
                </div>
            </a>
        </div>
';

// Part 3: PHP block for active route detection
 $layout .= "        @php
            \$academicRoutes = ['admin.academic-years.*','admin.terms.*','admin.subjects.*','admin.subject-assignments.*','admin.exams.*','admin.mark-entries.*','admin.classrooms.*','admin.sections.*'];
            \$peopleRoutes = ['admin.students.*','admin.teachers.*','admin.staff.*','admin.team-members.*','admin.parents.*'];
            \$financeRoutes = ['admin.fees.*','admin.fee-payments.*','admin.payrolls.*','admin.budgets.*','admin.income-expenses.*','admin.finance-statements.*','admin.leaves.*','admin.employee-assets.*'];
            \$websiteRoutes = ['admin.sliders.*','admin.gallery-*','admin.branches.*','admin.contact-messages.*'];
            \$isAcademicActive = request()->routeIs(\$academicRoutes);
            \$isPeopleActive = request()->routeIs(\$peopleRoutes);
            \$isFinanceActive = request()->routeIs(\$financeRoutes);
            \$isWebsiteActive = request()->routeIs(\$websiteRoutes);
        @endphp
";

// Part 4: Sidebar menu with scroll wrapper
 $layout .= '        <div class="sidebar-menu-wrap">
            <ul class="sidebar-menu">
                <li class="menu-header">MAIN</li>
                <li class="{{ request()->routeIs(\'admin.dashboard\') ? \'active\' : \'\' }}">
                    <a href="{{ route(\'admin.dashboard\') }}" class="{{ request()->routeIs(\'admin.dashboard\') ? \'active\' : \'\' }}"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
                </li>

                <li class="menu-header">ACADEMIC</li>
                <li class="{{ $isAcademicActive ? \'has-active-child\' : \'\' }}">
                    <a href="#academicSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-graduation-cap"></i><span>Academic</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isAcademicActive ? \'show\' : \'\' }}" id="academicSubmenu">
                        <li><a href="{{ route(\'admin.academic-years.index\') }}" class="{{ request()->routeIs(\'admin.academic-years.*\') ? \'active\' : \'\' }}"><i class="fas fa-calendar"></i> Academic Years</a></li>
                        <li><a href="{{ route(\'admin.terms.index\') }}" class="{{ request()->routeIs(\'admin.terms.*\') ? \'active\' : \'\' }}"><i class="fas fa-bookmark"></i> Terms</a></li>
                        <li><a href="{{ route(\'admin.subjects.index\') }}" class="{{ request()->routeIs(\'admin.subjects.*\') ? \'active\' : \'\' }}"><i class="fas fa-book"></i> Subjects</a></li>
                        <li><a href="{{ route(\'admin.subject-assignments.index\') }}" class="{{ request()->routeIs(\'admin.subject-assignments.*\') ? \'active\' : \'\' }}"><i class="fas fa-link"></i> Assign Subjects</a></li>
                        <li><a href="{{ route(\'admin.exams.index\') }}" class="{{ request()->routeIs(\'admin.exams.*\') ? \'active\' : \'\' }}"><i class="fas fa-file-alt"></i> Exams</a></li>
                        <li><a href="{{ route(\'admin.classrooms.index\') }}" class="{{ request()->routeIs(\'admin.classrooms.*\') ? \'active\' : \'\' }}"><i class="fas fa-building"></i> Classes</a></li>
                        <li><a href="{{ route(\'admin.mark-entries.index\') }}" class="{{ request()->routeIs(\'admin.mark-entries.*\') ? \'active\' : \'\' }}"><i class="fas fa-pen"></i> Mark Entry</a></li>
                    </ul>
                </li>

                <li class="menu-header">PEOPLE</li>
                <li class="{{ $isPeopleActive ? \'has-active-child\' : \'\' }}">
                    <a href="#peopleSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-users"></i><span>People</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isPeopleActive ? \'show\' : \'\' }}" id="peopleSubmenu">
                        <li><a href="{{ route(\'admin.students.index\') }}" class="{{ request()->routeIs(\'admin.students.*\') ? \'active\' : \'\' }}"><i class="fas fa-user-graduate"></i> Students</a></li>
                        <li><a href="{{ route(\'admin.teachers.index\') }}" class="{{ request()->routeIs(\'admin.teachers.*\') ? \'active\' : \'\' }}"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                        <li><a href="{{ route(\'admin.staff.index\') }}" class="{{ request()->routeIs(\'admin.staff.*\') ? \'active\' : \'\' }}"><i class="fas fa-id-badge"></i> Staff</a></li>
                        <li><a href="{{ route(\'admin.team-members.index\') }}" class="{{ request()->routeIs(\'admin.team-members.*\') ? \'active\' : \'\' }}"><i class="fas fa-users"></i> Team Members</a></li>
                    </ul>
                </li>

                <li class="menu-header">FINANCE</li>
                <li class="{{ $isFinanceActive ? \'has-active-child\' : \'\' }}">
                    <a href="#financeSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-wallet"></i><span>Finance</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isFinanceActive ? \'show\' : \'\' }}" id="financeSubmenu">
                        <li><a href="{{ route(\'admin.fees.index\') }}" class="{{ request()->routeIs(\'admin.fees.*\') ? \'active\' : \'\' }}"><i class="fas fa-money-bill-wave"></i> Fees</a></li>
                        <li><a href="{{ route(\'admin.fee-payments.index\') }}" class="{{ request()->routeIs(\'admin.fee-payments.*\') ? \'active\' : \'\' }}"><i class="fas fa-credit-card"></i> Payments</a></li>
                        <li><a href="{{ route(\'admin.payrolls.index\') }}" class="{{ request()->routeIs(\'admin.payrolls.*\') ? \'active\' : \'\' }}"><i class="fas fa-file-invoice-dollar"></i> Payroll</a></li>
                        <li><a href="{{ route(\'admin.budgets.index\') }}" class="{{ request()->routeIs(\'admin.budgets.*\') ? \'active\' : \'\' }}"><i class="fas fa-chart-pie"></i> Budgets</a></li>
                        <li><a href="{{ route(\'admin.income-expenses.index\') }}" class="{{ request()->routeIs(\'admin.income-expenses.*\') ? \'active\' : \'\' }}"><i class="fas fa-exchange-alt"></i> Income/Expense</a></li>
                        <li><a href="{{ route(\'admin.finance-statements.index\') }}" class="{{ request()->routeIs(\'admin.finance-statements.*\') ? \'active\' : \'\' }}"><i class="fas fa-file-alt"></i> Statements</a></li>
                        <li><a href="{{ route(\'admin.leaves.index\') }}" class="{{ request()->routeIs(\'admin.leaves.*\') ? \'active\' : \'\' }}"><i class="fas fa-calendar-minus"></i> Leaves</a></li>
                        <li><a href="{{ route(\'admin.employee-assets.index\') }}" class="{{ request()->routeIs(\'admin.employee-assets.*\') ? \'active\' : \'\' }}"><i class="fas fa-boxes"></i> Employee Assets</a></li>
                    </ul>
                </li>

                <li class="menu-header">WEBSITE</li>
                <li class="{{ $isWebsiteActive ? \'has-active-child\' : \'\' }}">
                    <a href="#websiteSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-globe"></i><span>Website</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isWebsiteActive ? \'show\' : \'\' }}" id="websiteSubmenu">
                        <li><a href="{{ route(\'admin.branches.index\') }}" class="{{ request()->routeIs(\'admin.branches.*\') ? \'active\' : \'\' }}"><i class="fas fa-map-marker-alt"></i> Branches</a></li>
                        <li><a href="{{ route(\'admin.sliders.index\') }}" class="{{ request()->routeIs(\'admin.sliders.*\') ? \'active\' : \'\' }}"><i class="fas fa-images"></i> Sliders</a></li>
                        <li><a href="{{ route(\'admin.gallery-images.index\') }}" class="{{ request()->routeIs(\'admin.gallery-images.*\') ? \'active\' : \'\' }}"><i class="fas fa-image"></i> Gallery Images</a></li>
                        <li><a href="{{ route(\'admin.gallery-videos.index\') }}" class="{{ request()->routeIs(\'admin.gallery-videos.*\') ? \'active\' : \'\' }}"><i class="fas fa-video"></i> Gallery Videos</a></li>
                        <li><a href="{{ route(\'admin.contact-messages.index\') }}" class="{{ request()->routeIs(\'admin.contact-messages.*\') ? \'active\' : \'\' }}"><i class="fas fa-envelope"></i> Messages</a></li>
                    </ul>
                </li>

                <li class="menu-header">SYSTEM</li>
                <li class="{{ request()->routeIs(\'admin.settings.*\') ? \'active\' : \'\' }}">
                    <a href="{{ route(\'admin.settings.index\') }}" class="{{ request()->routeIs(\'admin.settings.*\') ? \'active\' : \'\' }}"><i class="fas fa-cog"></i><span>Settings</span></a>
                </li>
                <li class="{{ request()->routeIs(\'admin.audits.*\') ? \'active\' : \'\' }}">
                    <a href="{{ route(\'admin.audits.index\') }}" class="{{ request()->routeIs(\'admin.audits.*\') ? \'active\' : \'\' }}"><i class="fas fa-clipboard-list"></i><span>Audit Log</span></a>
                </li>
            </ul>
        </div>
';

// Part 5: Sidebar footer
 $layout .= '        <div class="sidebar-footer">
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
';

// Part 6: Main area + topbar
 $layout .= '    <div class="admin-main">
        <nav class="admin-topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-breadcrumb">
                    <span>Hi, </span><strong>{{ Auth::user()->name }}</strong>
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ url(\'/\') }}" class="topbar-link" target="_blank" title="View Website">
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
                        <li><a class="dropdown-item" href="{{ url(\'/\') }}"><i class="fas fa-external-link-alt me-2"></i>View Website</a></li>
                        <li>
                            <form method="POST" action="{{ route(\'logout\') }}">@csrf
                                <button class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
';

// Part 7: Content + alerts + yield
 $layout .= '        <div class="admin-content">
            @if(session(\'success\'))
                <div class="global-alert alert-success"><i class="fas fa-check-circle"></i> {{ session(\'success\') }}</div>
            @endif
            @if(session(\'error\'))
                <div class="global-alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session(\'error\') }}</div>
            @endif
            @if(session(\'warning\'))
                <div class="global-alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{ session(\'warning\') }}</div>
            @endif
            @if(session(\'info\'))
                <div class="global-alert alert-info"><i class="fas fa-info-circle"></i> {{ session(\'info\') }}</div>
            @endif

            @yield(\'content\')
        </div>
    </div>
</div>
';

// Part 8: Scripts
 $layout .= '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    const sidebar = document.getElementById(\'adminSidebar\');
    const backdrop = document.getElementById(\'sidebarBackdrop\');
    const toggle = document.getElementById(\'sidebarToggle\');
    const isMobile = () => window.innerWidth < 768;

    function showSidebar(show) {
        if (!sidebar) return;
        sidebar.classList.toggle(\'show\', show);
        if (backdrop) {
            backdrop.classList.toggle(\'d-none\', !show);
            backdrop.classList.toggle(\'show\', show);
        }
    }

    if (toggle) toggle.addEventListener(\'click\', () => showSidebar(!sidebar.classList.contains(\'show\')));
    if (backdrop) backdrop.addEventListener(\'click\', () => showSidebar(false));

    document.querySelectorAll(\'.sidebar-menu a\').forEach(link => {
        link.addEventListener(\'click\', () => {
            if (link.hasAttribute(\'data-bs-toggle\')) return;
            if (window.innerWidth < 768) showSidebar(false);
        });
    });

    // Auto-scroll to active menu item
    var activeItem = document.querySelector(\'.sidebar-menu a.active\');
    if (activeItem) {
        setTimeout(function() {
            activeItem.scrollIntoView({behavior:\'smooth\', block:\'nearest\'});
        }, 200);
    }

    // Auto-dismiss alerts
    document.querySelectorAll(\'.global-alert\').forEach(a => {
        setTimeout(() => {
            a.style.transition = \'opacity 0.3s\';
            a.style.opacity = \'0\';
            setTimeout(() => a.remove(), 300);
        }, 4000);
    });
})();
</script>
@stack(\'scripts\')
@yield(\'scripts\')
</body>
</html>
';

// Write the file
 $filePath = $base . '/resources/views/layouts/admin.blade.php';
file_put_contents($filePath, $layout);

echo "Layout written successfully!\n";
echo "File size: " . filesize($filePath) . " bytes\n";
