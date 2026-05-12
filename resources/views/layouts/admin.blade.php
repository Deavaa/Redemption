<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') | School of Redemption</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <link href="{{ asset('css/modern-components.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body class="@yield('body-class')">
    <div class="admin-wrapper">
        <nav class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                    <div class="sidebar-brand-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="sidebar-brand-text">
                        <span class="sidebar-brand-pre">School of</span>
                        <span class="sidebar-brand-name">REDEMPTION</span>
                    </div>
                </a>
            </div>
            @php
                $academicRoutes = [
                    'admin.academic-years.*',
                    'admin.terms.*',
                    'admin.subjects.*',
                    'admin.subject-assignments.*',
                    'admin.exams.*',
                    'admin.mark-entries.*',
                    'admin.mark-sheet.*',
                    'admin.classrooms.*',
                    'admin.sections.*',
                    'admin.class-assets.*',
                    'admin.id-card-generate.*',
                    'admin.certificate-generate.*',
                ];
                $peopleRoutes = ['admin.students.*', 'admin.teachers.*', 'admin.staff.*', 'admin.team-members.*', 'admin.parents.*'];
                $financeRoutes = ['admin.fees.*', 'admin.fee-payments.*', 'admin.payrolls.*', 'admin.budgets.*', 'admin.income-expenses.*', 'admin.finance-statements.*', 'admin.leaves.*', 'admin.employee-assets.*'];
                $websiteRoutes = ['admin.sliders.*', 'admin.gallery-*', 'admin.branches.*', 'admin.contact-messages.*'];
                $isAcademicOpen = request()->routeIs($academicRoutes);
                $isPeopleOpen = request()->routeIs($peopleRoutes);
                $isFinanceOpen = request()->routeIs($financeRoutes);
                $isWebsiteOpen = request()->routeIs($websiteRoutes);
            @endphp
            <ul class="sidebar-menu">
                <li class="menu-header">MAIN</li>
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-th-large"></i><span>Dashboard</span>
                    </a>
                </li>

                <li class="menu-header">ACADEMIC</li>
                <li class="{{ $isAcademicOpen ? 'menu-open' : '' }}">
                    <a href="#academicSubmenu" data-bs-toggle="collapse">
                        <i class="bi bi-mortarboard"></i><span>Academic</span><i class="fas fa-chevron-down ms-auto sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isAcademicOpen ? 'show' : '' }}" id="academicSubmenu">
                        <li class="{{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}"><a href="{{ route('admin.academic-years.index') }}"><i class="bi bi-calendar-range"></i> Academic Years</a></li>
                        <li class="{{ request()->routeIs('admin.terms.*') ? 'active' : '' }}"><a href="{{ route('admin.terms.index') }}"><i class="bi bi-bookmark"></i> Terms</a></li>
                        <li class="{{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}"><a href="{{ route('admin.subjects.index') }}"><i class="bi bi-book"></i> Subjects</a></li>
                        <li class="{{ request()->routeIs('admin.subject-assignments.*') ? 'active' : '' }}"><a href="{{ route('admin.subject-assignments.index') }}"><i class="bi bi-link-45deg"></i> Assign Subjects</a></li>
                        <li class="{{ request()->routeIs('admin.exams.*') ? 'active' : '' }}"><a href="{{ route('admin.exams.index') }}"><i class="bi bi-journal-text"></i> Exams</a></li>
                        <li class="{{ request()->routeIs('admin.classrooms.*') ? 'active' : '' }}"><a href="{{ route('admin.classrooms.index') }}"><i class="bi bi-building"></i> Classes</a></li>
                        <li class="{{ request()->routeIs('admin.class-assets.*') ? 'active' : '' }}"><a href="{{ route('admin.class-assets.index') }}"><i class="fas fa-boxes"></i> Section Assets</a></li>
                        <li class="{{ request()->routeIs('admin.mark-entries.*') ? 'active' : '' }}"><a href="{{ route('admin.mark-entries.index') }}"><i class="bi bi-pencil-square"></i> Mark Entry</a></li>
                        <li class="{{ request()->routeIs('admin.mark-sheet.*') ? 'active' : '' }}"><a href="{{ route('admin.mark-sheet.index') }}"><i class="bi bi-file-earmark-text"></i> Mark Sheet</a></li>
                        <li class="{{ request()->routeIs('admin.id-card-generate.*') ? 'active' : '' }}"><a href="{{ route('admin.id-card-generate.index') }}"><i class="fas fa-id-card"></i> Generate ID Cards</a></li>
                        <li class="{{ request()->routeIs('admin.certificate-generate.*') ? 'active' : '' }}"><a href="{{ route('admin.certificate-generate.index') }}"><i class="fas fa-certificate"></i> Generate Certificates</a></li>
                    </ul>
                </li>

                <li class="menu-header">PEOPLE</li>
                <li class="{{ $isPeopleOpen ? 'menu-open' : '' }}">
                    <a href="#peopleSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-users"></i><span>People</span><i class="fas fa-chevron-down ms-auto sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isPeopleOpen ? 'show' : '' }}" id="peopleSubmenu">
                        <li class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}"><a href="{{ route('admin.students.index') }}"><i class="fas fa-user-graduate"></i> Students</a></li>
                        <li class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"><a href="{{ route('admin.teachers.index') }}"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                        <li class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}"><a href="{{ route('admin.staff.index') }}"><i class="bi bi-person-badge"></i> Staff</a></li>
                        <li class="{{ request()->routeIs('admin.team-members.*') ? 'active' : '' }}"><a href="{{ route('admin.team-members.index') }}"><i class="fas fa-users"></i> Team Members</a></li>
                    </ul>
                </li>

                <li class="menu-header">FINANCE</li>
                <li class="{{ $isFinanceOpen ? 'menu-open' : '' }}">
                    <a href="#financeSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-wallet"></i><span>Finance</span><i class="fas fa-chevron-down ms-auto sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isFinanceOpen ? 'show' : '' }}" id="financeSubmenu">
                        <li class="{{ request()->routeIs('admin.fees.*') ? 'active' : '' }}"><a href="{{ route('admin.fees.index') }}"><i class="fas fa-money-bill-wave"></i> Fees</a></li>
                        <li class="{{ request()->routeIs('admin.fee-payments.*') ? 'active' : '' }}"><a href="{{ route('admin.fee-payments.index') }}"><i class="fas fa-credit-card"></i> Payments</a></li>
                        <li class="{{ request()->routeIs('admin.payrolls.*') ? 'active' : '' }}"><a href="{{ route('admin.payrolls.index') }}"><i class="fas fa-file-invoice-dollar"></i> Payroll</a></li>
                        <li class="{{ request()->routeIs('admin.budgets.*') ? 'active' : '' }}"><a href="{{ route('admin.budgets.index') }}"><i class="fas fa-chart-pie"></i> Budgets</a></li>
                        <li class="{{ request()->routeIs('admin.income-expenses.*') ? 'active' : '' }}"><a href="{{ route('admin.income-expenses.index') }}"><i class="fas fa-exchange-alt"></i> Income/Expense</a></li>
                        <li class="{{ request()->routeIs('admin.finance-statements.*') ? 'active' : '' }}"><a href="{{ route('admin.finance-statements.index') }}"><i class="fas fa-file-alt"></i> Statements</a></li>
                        <li class="{{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}"><a href="{{ route('admin.leaves.index') }}"><i class="fas fa-calendar-minus"></i> Leaves</a></li>
                        <li class="{{ request()->routeIs('admin.employee-assets.*') ? 'active' : '' }}"><a href="{{ route('admin.employee-assets.index') }}"><i class="fas fa-boxes"></i> Employee Assets</a></li>
                    </ul>
                </li>

                <li class="menu-header">WEBSITE</li>
                <li class="{{ $isWebsiteOpen ? 'menu-open' : '' }}">
                    <a href="#websiteSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-globe"></i><span>Website</span><i class="fas fa-chevron-down ms-auto sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isWebsiteOpen ? 'show' : '' }}" id="websiteSubmenu">
                        <li class="{{ request()->routeIs('admin.branches.*') ? 'active' : '' }}"><a href="{{ route('admin.branches.index') }}"><i class="fas fa-map-marker-alt"></i> Branches</a></li>
                        <li class="{{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}"><a href="{{ route('admin.sliders.index') }}"><i class="fas fa-images"></i> Sliders</a></li>
                        <li class="{{ request()->routeIs('admin.gallery-images.*') ? 'active' : '' }}"><a href="{{ route('admin.gallery-images.index') }}"><i class="fas fa-image"></i> Gallery Images</a></li>
                        <li class="{{ request()->routeIs('admin.gallery-videos.*') ? 'active' : '' }}"><a href="{{ route('admin.gallery-videos.index') }}"><i class="fas fa-video"></i> Gallery Videos</a></li>
                        <li class="{{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}"><a href="{{ route('admin.contact-messages.index') }}"><i class="fas fa-envelope"></i> Messages</a></li>
                    </ul>
                </li>

                <li class="menu-header">COMMUNICATION</li>
                <li class="{{ request()->routeIs('admin.chat.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.chat.index') }}">
                        <i class="fas fa-comments"></i><span>Chat</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.telegram.*') ? 'active' : '' }}">
                    <a href="#telegramSubmenu" data-bs-toggle="collapse">
                        <i class="fab fa-telegram"></i><span>Telegram</span><i class="fas fa-chevron-down ms-auto sidebar-chevron"></i>
                    </a>
                    <ul class="collapse" id="telegramSubmenu">
                        <li><a href="{{ route('admin.telegram.index') }}"><i class="fas fa-cog"></i> Settings</a></li>
                        <li><a href="{{ route('admin.telegram.send') }}"><i class="fas fa-paper-plane"></i> Send Message</a></li>
                    </ul>
                </li>

                <li class="menu-header">SYSTEM</li>
                <li class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}">
                        <i class="fas fa-cog"></i><span>Settings</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.audits.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.audits.index') }}">
                        <i class="fas fa-clipboard-list"></i><span>Audit Log</span>
                    </a>
                </li>
            </ul>

            {{-- Sidebar Footer --}}
            <div class="sidebar-footer">
                <div class="sidebar-footer-user">
                    <div class="sidebar-footer-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
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
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="topbar-breadcrumb">
                        <span class="topbar-greeting">Welcome back,</span>
                        <strong>{{ Auth::user()->name }}</strong>
                    </div>
                </div>
                <div class="topbar-right">
                    <a href="{{ url('/') }}" class="topbar-link" target="_blank" title="View Website">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    <div class="topbar-dropdown">
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

        {{-- Mobile Bottom Navigation --}}
        <nav class="mobile-bottom-nav" id="mobileBottomNav">
            <a href="{{ route('admin.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Home</span>
            </a>
            <a href="{{ route('admin.chat.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.chat.*') ? 'active' : '' }}">
                <i class="fas fa-comments"></i>
                <span>Chat</span>
            </a>
            <a href="{{ route('admin.students.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                <i class="fas fa-user-graduate"></i>
                <span>Students</span>
            </a>
            <a href="{{ route('admin.teachers.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Teachers</span>
            </a>
            <a href="#" class="mobile-nav-item" id="mobileMenuToggle">
                <i class="fas fa-ellipsis-h"></i>
                <span>More</span>
            </a>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('adminSidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        const sidebarToggle = document.getElementById('sidebarToggle');

        function setSidebarVisibility(show) {
            if (!sidebar) return;
            sidebar.classList.toggle('show', show);
            if (sidebarBackdrop) {
                sidebarBackdrop.classList.toggle('d-none', !show);
            }
        }

        sidebarToggle?.addEventListener('click', () => {
            setSidebarVisibility(!sidebar.classList.contains('show'));
        });

        sidebarBackdrop?.addEventListener('click', () => {
            setSidebarVisibility(false);
        });

        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', () => {
                const isCollapseToggle = link.hasAttribute('data-bs-toggle') || (link.getAttribute('href') || '').startsWith('#');
                if (window.innerWidth < 768 && !isCollapseToggle) {
                    setSidebarVisibility(false);
                }
            });
        });

        if (window.innerWidth >= 768) {
            setSidebarVisibility(true);
        }

        // Auto-dismiss alerts
        document.querySelectorAll('.global-alert').forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'all 0.4s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 400);
            }, 4000);
        });

        // Mobile "More" menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                setSidebarVisibility(true);
            });
        }
    </script>
    @stack('scripts')
    @yield('scripts')
</body>

</html>
