<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Student Portal</title>

    {{-- PWA & Mobile Integration --}}
    <link rel="manifest" href="{{ route('app.manifest') }}">
    <meta name="theme-color" content="#0D9488">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Redemption">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/design-tokens.css') }}" rel="stylesheet">
    <link href="{{ asset('css/portal.css') }}" rel="stylesheet">
    {{-- Student-specific overrides using role-student accent from design-tokens --}}
    <style>
    /* ===== STUDENT PORTAL OVERRIDES — Teal accent from design-tokens ===== */
    .role-student {
        --sidebar-width: 250px;
        --sidebar-border-color: rgba(255,255,255,0.1);
        --color-body-bg: #f0fdfa;
    }
    .student-sidebar {
        background: linear-gradient(180deg, #0f766e 0%, #115e59 100%);
    }
    .student-sidebar .sidebar-brand-icon {
        background: rgba(255,255,255,0.2);
    }
    .student-sidebar .sidebar-brand-pre {
        color: rgba(255,255,255,0.6);
    }
    .student-sidebar .sidebar-menu > li > a,
    .student-sidebar .sidebar-menu > li > .submenu-toggle {
        color: rgba(255,255,255,0.75);
    }
    .student-sidebar .sidebar-menu > li > a:hover,
    .student-sidebar .sidebar-menu > li > .submenu-toggle:hover {
        background: rgba(255,255,255,0.1);
    }
    .student-sidebar .sidebar-menu > li.active > a.active {
        background: rgba(255,255,255,0.18);
        color: #fff;
        border-left-color: #5eead4;
    }
    .student-sidebar .sidebar-menu .menu-header {
        color: rgba(255,255,255,0.35);
    }
    .student-sidebar .sidebar-menu .collapse li a {
        color: rgba(255,255,255,0.75);
    }
    .student-sidebar .sidebar-menu .collapse li a:hover {
        background: rgba(255,255,255,0.1);
    }
    .student-sidebar .sidebar-menu .collapse li a.active {
        background: rgba(255,255,255,0.18);
        border-left-color: #5eead4;
        color: #fff;
    }
    .student-sidebar .sidebar-footer-avatar {
        background: rgba(255,255,255,0.2);
    }
    .student-sidebar .sidebar-menu-wrap {
        scrollbar-color: rgba(255,255,255,0.15) transparent;
    }
    .topbar-avatar-btn {
        background: linear-gradient(135deg, #0d9488, #0ea5e9);
    }
    .student-table tbody tr:hover td { background: #f0fdfa; }
    .profile-header {
        background: linear-gradient(135deg, #0f766e, #0ea5e9);
    }
    .fee-card.total { background: linear-gradient(135deg, #0f766e, #0ea5e9); }
    .term-bar-fill {
        background: linear-gradient(90deg, #0d9488, #0ea5e9);
    }
    /* Student-specific class aliases */
    .student-wrapper { display: flex; min-height: 100vh; }
    .student-main { flex: 1; margin-left: var(--sidebar-width, 250px); min-height: 100vh; display: flex; flex-direction: column; }
    .student-content { flex: 1; padding: 20px; }
    .student-topbar { height: 56px; }
    .student-card { background: var(--color-card-bg); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--color-border); overflow: hidden; margin-bottom: 20px; }
    .student-card-header { padding: 14px 18px; border-bottom: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
    .student-card-header h5 { font-size: 15px; font-weight: 700; color: var(--color-text-dark); margin: 0; }
    .student-card-body { padding: 18px; }
    .student-table { width: 100%; font-size: 13px; }
    .student-table th { text-align: left; padding: 10px 14px; color: var(--color-text-muted); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid var(--color-border); background: rgba(248,250,252,0.5); }
    .student-table td { padding: 10px 14px; border-bottom: 1px solid var(--color-border-light); vertical-align: middle; color: var(--color-text); }
    @media (max-width: 768px) {
        .student-sidebar { transform: translateX(-100%); }
        .student-sidebar.show { transform: translateX(0); }
        .student-main { margin-left: 0; }
    }
    @media (max-width: 480px) {
        .student-content { padding: 12px; }
    }
    </style>
    @stack('styles')
</head>
<body class="role-student">
<div class="student-wrapper">
    {{-- SIDEBAR --}}
    <nav class="student-sidebar portal-sidebar" id="studentSidebar">
        <div class="sidebar-header">
            <a href="{{ route('student.dashboard') }}" class="sidebar-brand">
                <div class="sidebar-brand-icon"><i class="fas fa-graduation-cap"></i></div>
                <div class="sidebar-brand-text">
                    <span class="sidebar-brand-pre">Student Portal</span>
                    <span class="sidebar-brand-name">{{ \App\Models\Setting::get('school_name', 'Redemption') }}</span>
                </div>
            </a>
        </div>
        <div class="sidebar-menu-wrap">
            <ul class="sidebar-menu">
                <li class="menu-header">MENU</li>
                <li class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i><span>Dashboard</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('student.marks') ? 'active' : '' }}">
                    <a href="{{ route('student.marks') }}" class="{{ request()->routeIs('student.marks') ? 'active' : '' }}">
                        <i class="fas fa-pen-alt"></i><span>My Marks</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('student.progress') ? 'active' : '' }}">
                    <a href="{{ route('student.progress') }}" class="{{ request()->routeIs('student.progress') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i><span>My Progress</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('student.fees') ? 'active' : '' }}">
                    <a href="{{ route('student.fees') }}" class="{{ request()->routeIs('student.fees') ? 'active' : '' }}">
                        <i class="fas fa-wallet"></i><span>My Fees</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('student.profile') ? 'active' : '' }}">
                    <a href="{{ route('student.profile') }}" class="{{ request()->routeIs('student.profile') ? 'active' : '' }}">
                        <i class="fas fa-user-circle"></i><span>My Profile</span>
                    </a>
                </li>

                <li class="menu-header">SELF-ASSESSMENT</li>
                <li class="{{ request()->routeIs('student.assessment.*') ? 'active' : '' }}">
                    <a href="{{ route('student.assessment.index') }}" class="{{ request()->routeIs('student.assessment.*') ? 'active' : '' }}">
                        <i class="fas fa-brain"></i><span>Self-Assessment</span>
                    </a>
                </li>

                <li class="menu-header">TEACHER REVIEW</li>
                <li class="{{ request()->routeIs('student.teacher-review.*') ? 'active' : '' }}">
                    <a href="{{ route('student.teacher-review.index') }}" class="{{ request()->routeIs('student.teacher-review.*') ? 'active' : '' }}">
                        <i class="fas fa-chalkboard-teacher"></i><span>Review Teachers</span>
                    </a>
                </li>

                <li class="menu-header">COMMUNICATION</li>
                <li class="{{ request()->routeIs('student.chat.*') ? 'active' : '' }}">
                    <a href="{{ route('student.chat.index') }}" class="{{ request()->routeIs('student.chat.*') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i><span>Messages</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <div class="sidebar-footer-user">
                <div class="sidebar-footer-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="sidebar-footer-info">
                    <span class="sidebar-footer-name">{{ auth()->user()->name }}</span>
                    <span class="sidebar-footer-role">Student</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="sidebar-backdrop d-none" id="sidebarBackdrop"></div>


    {{-- MAIN --}}
    <div class="student-main">
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
        <div id="studentAnnouncementBar" class="announcement-banner">
            <div class="announcement-banner-inner">
                <div class="announcement-badge"><i class="fas fa-bullhorn"></i>&ensp;Announcements</div>
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
                <button onclick="document.getElementById('studentAnnouncementBar').style.display='none'" class="announcement-close" title="Dismiss"><i class="fas fa-times"></i></button>
            </div>
        </div>

        {{-- Announcement Splash Modal for Student Portal --}}
        <div class="announcement-splash-overlay" id="studentAnnouncementSplash" style="display:none;">
            <div class="announcement-splash-modal" style="max-width:420px;">
                <div class="announcement-splash-header" style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);">
                    <div class="announcement-splash-icon" style="background:#06b6d4;"><i class="fas fa-bullhorn"></i></div>
                    <h2>Announcements</h2>
                    <span class="announcement-splash-count">{{ $activeAnnouncements->count() }} active</span>
                </div>
                <div class="announcement-splash-body">
                    @foreach($activeAnnouncements as $splashAnn)
                    <div class="announcement-splash-item">
                        <div class="announcement-splash-item-dot" style="background:{{ $splashAnn->color ?? '#06b6d4' }}"></div>
                        <div class="announcement-splash-item-content">
                            <div class="announcement-splash-item-title">{{ $splashAnn->title }}</div>
                            @if($splashAnn->category)
                            <span class="announcement-splash-item-cat" style="background:{{ $splashAnn->color ?? '#06b6d4' }}20;color:{{ $splashAnn->color ?? '#06b6d4' }}">{{ ucfirst($splashAnn->category) }}</span>
                            @endif
                            @if($splashAnn->start_date)
                            <span class="announcement-splash-item-date"><i class="fas fa-calendar-alt"></i> {{ $splashAnn->start_date->format('M d, Y') }}</span>
                            @endif
                            @if($splashAnn->description)
                            <p class="announcement-splash-item-desc">{{ Str::limit(strip_tags($splashAnn->description), 120) }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="announcement-splash-footer">
                    <button onclick="closeStudentAnnouncementSplash()" class="announcement-splash-dismiss" style="width:100%;justify-content:center;background:#06b6d4;"><i class="fas fa-check"></i> Dismiss</button>
                </div>
            </div>
        </div>
        {{-- Student-specific announcement accent color --}}
        <style>
        .announcement-badge { background: #06b6d4; }
        .announcement-banner { border-bottom: 2px solid #06b6d4; }
        .announcement-splash-dismiss { background: #06b6d4; }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.announcement-ticker-wrap').forEach(function(wrap) {
                var ticker = wrap.querySelector('.announcement-ticker');
                if (!ticker) return;
                if (ticker.scrollWidth > wrap.clientWidth + 10) {
                    var clone = ticker.innerHTML;
                    ticker.insertAdjacentHTML('beforeend', clone);
                    var duration = Math.max(ticker.scrollWidth / 2 / 25, 50);
                    ticker.style.setProperty('--ticker-duration', duration + 's');
                    ticker.classList.add('scrolling');
                }
            });

            // Announcement Splash: show ONCE PER DAY per user.
            var splash = document.getElementById('studentAnnouncementSplash');
            if (splash) {
                var today = new Date().toISOString().slice(0, 10);
                var splashKey = 'student_announcement_splash_dismissed_' + today;
                var alreadyShownToday = localStorage.getItem(splashKey);
                if (!alreadyShownToday) {
                    splash.style.display = 'flex';
                }
            }
        });

        function closeStudentAnnouncementSplash() {
            var splash = document.getElementById('studentAnnouncementSplash');
            if (splash) {
                splash.style.opacity = '0';
                splash.style.transition = 'opacity 0.3s';
                setTimeout(function() { splash.style.display = 'none'; }, 300);
                var today = new Date().toISOString().slice(0, 10);
                localStorage.setItem('student_announcement_splash_dismissed_' + today, '1');
            }
        }
        </script>
        @endif
        <nav class="student-topbar portal-topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-breadcrumb">
                    <span>Hi, </span><strong>{{ auth()->user()->name }}</strong>
                </div>
            </div>
            <div class="topbar-right">
                <div class="dropdown">
                    <button class="topbar-avatar-btn" data-bs-toggle="dropdown">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            <div class="dropdown-header-name">{{ auth()->user()->name }}</div>
                            <div class="dropdown-header-email">{{ auth()->user()->email }}</div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('student.profile') }}"><i class="fas fa-user-circle me-2"></i>My Profile</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="student-content portal-content">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    const sidebar = document.getElementById('studentSidebar');
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
@stack('scripts')
@yield('scripts')
</body>
</html>
