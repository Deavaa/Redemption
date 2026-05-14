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
            $academicRoutes = ['admin.academic-years.*','admin.terms.*','admin.subjects.*','admin.subject-assignments.*','admin.exams.*','admin.mark-entries.*','admin.classrooms.*','admin.sections.*','admin.mark-sheet.*','admin.mark-sheet-full.*','admin.mark-roster.*','admin.report-card.*','admin.progress-reports.*'];
            $peopleRoutes = ['admin.students.*','admin.teachers.*','admin.staff.*','admin.team-members.*','admin.parents.*','admin.teacher-assignments.*'];
            $financeRoutes = ['admin.fees.*','admin.fee-payments.*','admin.payrolls.*','admin.budgets.*','admin.income-expenses.*','admin.finance-statements.*','admin.leaves.*','admin.employee-assets.*'];
            $websiteRoutes = ['admin.sliders.*','admin.gallery-*','admin.branches.*','admin.contact-messages.*'];
                $generateRoutes = ['admin.id-card-generate.*','admin.certificate-generate.*','admin.id-cards.*','admin.certificates.*'];
            $isAcademicActive = request()->routeIs($academicRoutes);
            $isPeopleActive = request()->routeIs($peopleRoutes);
            $isFinanceActive = request()->routeIs($financeRoutes);
            $isWebsiteActive = request()->routeIs($websiteRoutes);
                $isGenerateActive = request()->routeIs($generateRoutes);
        @endphp
        <div class="sidebar-menu-wrap">
            <ul class="sidebar-menu">
                <li class="menu-header">{{ __('app.main') }}</li>
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fas fa-th-large"></i><span>{{ __('app.dashboard') }}</span></a>
                </li>

                <li class="menu-header">{{ __('app.academic') }}</li>
                <li class="{{ $isAcademicActive ? 'has-active-child' : '' }}">
                    <a href="#academicSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-graduation-cap"></i><span>{{ __('app.academic') }}</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isAcademicActive ? 'show' : '' }}" id="academicSubmenu">
                        <li><a href="{{ route('admin.academic-years.index') }}" class="{{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}"><i class="fas fa-calendar"></i> {{ __('app.academic_years') }}</a></li>
                        <li><a href="{{ route('admin.terms.index') }}" class="{{ request()->routeIs('admin.terms.*') ? 'active' : '' }}"><i class="fas fa-bookmark"></i> {{ __('app.terms') }}</a></li>
                        <li><a href="{{ route('admin.subjects.index') }}" class="{{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}"><i class="fas fa-book"></i> {{ __('app.subjects') }}</a></li>
                        <li><a href="{{ route('admin.subject-assignments.index') }}" class="{{ request()->routeIs('admin.subject-assignments.*') ? 'active' : '' }}"><i class="fas fa-link"></i> {{ __('app.assign_subjects') }}</a></li>
                        <li><a href="{{ route('admin.exams.index') }}" class="{{ request()->routeIs('admin.exams.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> {{ __('app.exams') }}</a></li>
                        <li><a href="{{ route('admin.classrooms.index') }}" class="{{ request()->routeIs('admin.classrooms.*') ? 'active' : '' }}"><i class="fas fa-building"></i> {{ __('app.classes') }}</a></li>
                        <li><a href="{{ route('admin.mark-entries.index') }}" class="{{ request()->routeIs('admin.mark-entries.*') ? 'active' : '' }}"><i class="fas fa-pen"></i> {{ __('app.mark_entry') }}</a></li>
                        <li><a href="{{ route('admin.mark-sheet.index') }}" class="{{ request()->routeIs('admin.mark-sheet.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> {{ __('app.mark_sheet') }}</a></li>
                        <li><a href="{{ route('admin.mark-sheet-full.index') }}" class="{{ request()->routeIs('admin.mark-sheet-full.*') ? 'active' : '' }}"><i class="fas fa-table"></i> {{ __('app.full_mark_sheet') }}</a></li>
                        <li><a href="{{ route('admin.mark-roster.index') }}" class="{{ request()->routeIs('admin.mark-roster.*') ? 'active' : '' }}"><i class="fas fa-list-ol"></i> {{ __('app.mark_roster') }}</a></li>
                        <li><a href="{{ route('admin.report-card.index') }}" class="{{ request()->routeIs('admin.report-card.*') ? 'active' : '' }}"><i class="fas fa-id-card"></i> {{ __('app.report_cards') }}</a></li>
                        <li><a href="{{ route('admin.progress-reports.index') }}" class="{{ request()->routeIs('admin.progress-reports.*') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> {{ __('app.progress_reports') }}</a></li>
                    </ul>
                </li>

                <li class="menu-header">{{ __('app.people') }}</li>
                <li class="{{ $isPeopleActive ? 'has-active-child' : '' }}">
                    <a href="#peopleSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-users"></i><span>{{ __('app.people') }}</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isPeopleActive ? 'show' : '' }}" id="peopleSubmenu">
                        <li><a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}"><i class="fas fa-user-graduate"></i> {{ __('app.students') }}</a></li>
                        <li><a href="{{ route('admin.teachers.index') }}" class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"><i class="fas fa-chalkboard-teacher"></i> {{ __('app.teachers') }}</a></li>
                        <li><a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}"><i class="fas fa-id-badge"></i> {{ __('app.staff') }}</a></li>
                        <li><a href="{{ route('admin.team-members.index') }}" class="{{ request()->routeIs('admin.team-members.*') ? 'active' : '' }}"><i class="fas fa-users"></i> {{ __('app.team_members') }}</a></li>
                        <li><a href="{{ route('admin.parents.index') }}" class="{{ request()->routeIs('admin.parents.*') ? 'active' : '' }}"><i class="fas fa-user-friends"></i> {{ __('app.parents') }}</a></li>
                        <li><a href="{{ route('admin.teacher-assignments.index') }}" class="{{ request()->routeIs('admin.teacher-assignments.*') ? 'active' : '' }}"><i class="fas fa-chalkboard"></i> {{ __('app.teacher_assignments') }}</a></li>
                    </ul>
                </li>

                <li class="menu-header">{{ __('app.finance') }}</li>
                <li class="{{ $isFinanceActive ? 'has-active-child' : '' }}">
                    <a href="#financeSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-wallet"></i><span>{{ __('app.finance') }}</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isFinanceActive ? 'show' : '' }}" id="financeSubmenu">
                        <li><a href="{{ route('admin.fees.index') }}" class="{{ request()->routeIs('admin.fees.*') ? 'active' : '' }}"><i class="fas fa-money-bill-wave"></i> {{ __('app.fees') }}</a></li>
                        <li><a href="{{ route('admin.fee-payments.index') }}" class="{{ request()->routeIs('admin.fee-payments.*') ? 'active' : '' }}"><i class="fas fa-credit-card"></i> {{ __('app.payments') }}</a></li>
                        <li><a href="{{ route('admin.payrolls.index') }}" class="{{ request()->routeIs('admin.payrolls.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> {{ __('app.payroll') }}</a></li>
                        <li><a href="{{ route('admin.budgets.index') }}" class="{{ request()->routeIs('admin.budgets.*') ? 'active' : '' }}"><i class="fas fa-chart-pie"></i> {{ __('app.budgets') }}</a></li>
                        <li><a href="{{ route('admin.income-expenses.index') }}" class="{{ request()->routeIs('admin.income-expenses.*') ? 'active' : '' }}"><i class="fas fa-exchange-alt"></i> {{ __('app.income_expense') }}</a></li>
                        <li><a href="{{ route('admin.finance-statements.index') }}" class="{{ request()->routeIs('admin.finance-statements.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> {{ __('app.statements') }}</a></li>
                        <li><a href="{{ route('admin.leaves.index') }}" class="{{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}"><i class="fas fa-calendar-minus"></i> {{ __('app.leaves') }}</a></li>
                        <li><a href="{{ route('admin.employee-assets.index') }}" class="{{ request()->routeIs('admin.employee-assets.*') ? 'active' : '' }}"><i class="fas fa-boxes"></i> {{ __('app.employee_assets') }}</a></li>
                    </ul>
                </li>

                <li class="menu-header">{{ __('app.generate') }}</li>
                <li class="{{ $isGenerateActive ? 'has-active-child' : '' }}">
                    <a href="#generateSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-magic"></i><span>{{ __('app.generate') }}</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isGenerateActive ? 'show' : '' }}" id="generateSubmenu">
                        <li><a href="{{ route('admin.id-card-generate.index') }}" class="{{ request()->routeIs('admin.id-card-generate.*') ? 'active' : '' }}"><i class="fas fa-id-badge"></i> {{ __('app.student_id_cards') }}</a></li>
                        <li><a href="{{ route('admin.certificate-generate.index') }}" class="{{ request()->routeIs('admin.certificate-generate.*') ? 'active' : '' }}"><i class="fas fa-award"></i> {{ __('app.certificates') }}</a></li>
                    </ul>
                </li>

                <li class="menu-header">{{ __('app.website') }}</li>
                <li class="{{ $isWebsiteActive ? 'has-active-child' : '' }}">
                    <a href="#websiteSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-globe"></i><span>{{ __('app.website') }}</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isWebsiteActive ? 'show' : '' }}" id="websiteSubmenu">
                        <li><a href="{{ route('admin.branches.index') }}" class="{{ request()->routeIs('admin.branches.*') ? 'active' : '' }}"><i class="fas fa-map-marker-alt"></i> {{ __('app.branches') }}</a></li>
                        <li><a href="{{ route('admin.sliders.index') }}" class="{{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}"><i class="fas fa-images"></i> {{ __('app.sliders') }}</a></li>
                        <li><a href="{{ route('admin.gallery-images.index') }}" class="{{ request()->routeIs('admin.gallery-images.*') ? 'active' : '' }}"><i class="fas fa-image"></i> {{ __('app.gallery_images') }}</a></li>
                        <li><a href="{{ route('admin.gallery-videos.index') }}" class="{{ request()->routeIs('admin.gallery-videos.*') ? 'active' : '' }}"><i class="fas fa-video"></i> {{ __('app.gallery_videos') }}</a></li>
                        <li><a href="{{ route('admin.contact-messages.index') }}" class="{{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}"><i class="fas fa-envelope"></i> {{ __('app.messages') }}</a></li>
                    </ul>
                </li>

                <li class="menu-header">{{ __('app.system') }}</li>

                <li class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><i class="fas fa-cog"></i><span>{{ __('app.settings') }}</span></a>
                </li>
                <li class="{{ request()->routeIs('admin.audits.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.audits.index') }}" class="{{ request()->routeIs('admin.audits.*') ? 'active' : '' }}"><i class="fas fa-clipboard-list"></i><span>{{ __('app.audit_log') }}</span></a>
                </li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <div class="sidebar-footer-user">
                <div class="sidebar-footer-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="sidebar-footer-info">
                    <span class="sidebar-footer-name">{{ Auth::user()->name }}</span>
                    <span class="sidebar-footer-role">{{ __('app.administrator') }}</span>
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
                    <span>{{ __('app.hi') }} </span><strong>{{ Auth::user()->name }}</strong>
                </div>
            </div>
            <div class="topbar-right">
                {{-- Language Switcher --}}
                <div class="topbar-dropdown dropdown">
                    <button class="topbar-lang-btn" data-bs-toggle="dropdown" title="{{ __('app.language') }}">
                        <i class="fas fa-globe"></i>
                        <span class="lang-label">{{ strtoupper(app()->getLocale()) }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end lang-dropdown">
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

                <a href="{{ url('/') }}" class="topbar-link" target="_blank" title="{{ __('app.view_website') }}">
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
/* Language Switcher Styles */
.topbar-lang-btn {
    display: flex;
    align-items: center;
    gap: 5px;
    background: none;
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 6px;
    color: rgba(255,255,255,0.8);
    padding: 6px 10px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}
.topbar-lang-btn:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
    border-color: rgba(255,255,255,0.3);
}
.topbar-lang-btn .lang-label {
    font-weight: 600;
    font-size: 11px;
}
.lang-dropdown .dropdown-item {
    font-size: 14px;
    padding: 8px 16px;
}
.lang-dropdown .dropdown-item.active {
    background-color: #f0f7ff;
    font-weight: 600;
}
.lang-dropdown .dropdown-item:hover {
    background-color: #f5f5f5;
}
</style>
@stack('scripts')
@yield('scripts')
</body>
</html>
