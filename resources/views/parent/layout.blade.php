<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Parent Portal</title>

    {{-- PWA & Mobile Integration --}}
    <link rel="manifest" href="{{ route('app.manifest') }}">
    <meta name="theme-color" content="#ea580c">
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
    <style>
    /* ===== PARENT PORTAL OVERRIDES — Amber accent from design-tokens ===== */
    .role-parent {
        --sidebar-width: 260px;
        --sidebar-border-color: rgba(255,255,255,0.08);
        --color-body-bg: #faf7f5;
    }
    .parent-sidebar {
        background: #1c1917;
    }
    .parent-sidebar .sidebar-brand-icon {
        background: linear-gradient(135deg, #ea580c, #f59e0b);
    }
    .parent-sidebar .sidebar-brand-pre {
        color: rgba(255,255,255,0.65);
    }
    .parent-sidebar .sidebar-menu > li > a,
    .parent-sidebar .sidebar-menu > li > .submenu-toggle {
        color: rgba(255,255,255,0.65);
    }
    .parent-sidebar .sidebar-menu > li > a:hover,
    .parent-sidebar .sidebar-menu > li > .submenu-toggle:hover {
        background: rgba(255,255,255,0.06);
        color: rgba(255,255,255,0.95);
    }
    .parent-sidebar .sidebar-menu > li.active > a.active {
        background: rgba(234,88,12,0.25);
        color: #fff;
        border-left-color: #ea580c;
    }
    .parent-sidebar .sidebar-menu .menu-header {
        color: rgba(255,255,255,0.3);
    }
    .parent-sidebar .sidebar-menu .collapse li a {
        color: rgba(255,255,255,0.65);
    }
    .parent-sidebar .sidebar-menu .collapse li a:hover {
        background: rgba(255,255,255,0.06);
    }
    .parent-sidebar .sidebar-menu .collapse li a.active {
        background: rgba(234,88,12,0.25);
        border-left-color: #ea580c;
        color: #fff;
    }
    .parent-sidebar .sidebar-menu .collapse li a.active i {
        color: #ea580c;
    }
    .parent-sidebar .sidebar-footer-avatar {
        background: linear-gradient(135deg, #ea580c, #f59e0b);
    }
    .parent-sidebar .sidebar-menu-wrap {
        scrollbar-color: rgba(255,255,255,0.1) transparent;
    }
    .topbar-avatar {
        background: linear-gradient(135deg, #ea580c, #f59e0b);
    }
    .topbar-avatar:hover { box-shadow: 0 0 0 3px rgba(234,88,12,0.1); }
    .child-avatar {
        background: linear-gradient(135deg, #ea580c, #f59e0b);
    }
    .child-card-footer a {
        color: #ea580c; background: rgba(234,88,12,0.1);
    }
    .child-card-footer a:hover {
        background: #ea580c; color: #fff;
    }
    .modern-table tbody tr:hover td { background: #fefce8; }
    .trend-bar {
        background: linear-gradient(90deg, #ea580c, #f59e0b);
    }
    .stat-icon.orange { background: rgba(234,88,12,0.1); color: #ea580c; }
    /* Parent-specific class aliases */
    .parent-wrapper { display: flex; min-height: 100vh; }
    .parent-main { flex: 1; margin-left: var(--sidebar-width, 260px); min-height: 100vh; display: flex; flex-direction: column; }
    .parent-content { flex: 1; padding: 20px; }
    .parent-topbar { height: 56px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    @media (max-width: 768px) {
        .parent-sidebar { transform: translateX(-100%); }
        .parent-sidebar.show { transform: translateX(0); }
        .parent-main { margin-left: 0; }
    }
    @media (max-width: 480px) {
        .parent-content { padding: 12px; }
    }
    </style>
    @stack('styles')
</head>
<body class="role-parent">
<div class="parent-wrapper">
    <nav class="parent-sidebar portal-sidebar" id="parentSidebar">
        <div class="sidebar-header">
            <a href="{{ route('parent.dashboard') }}" class="sidebar-brand">
                <div class="sidebar-brand-icon"><i class="fas fa-home"></i></div>
                <div class="sidebar-brand-text">
                    <span class="sidebar-brand-pre">PARENT PORTAL</span>
                    <span class="sidebar-brand-name">{{ __('app.brand_name') }}</span>
                </div>
            </a>
        </div>

        @php
            $parentUser = auth()->user();
            $parentProfile = \App\Models\ParentModel::where('user_id', $parentUser->id)->first();
            $children = $parentProfile ? $parentProfile->students()->where('status', 'active')->get() : collect();
        @endphp

        <div class="sidebar-menu-wrap">
            <ul class="sidebar-menu">
                <li class="menu-header">OVERVIEW</li>
                <li class="{{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('parent.dashboard') }}" class="{{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i><span>Dashboard</span>
                    </a>
                </li>

                @if($children->count() > 0)
                <li class="menu-header">MY CHILDREN</li>
                @foreach($children as $child)
                <li class="{{ request()->segment(3) == $child->id ? 'has-active-child' : '' }}">
                    <a href="#childMenu{{ $child->id }}" data-bs-toggle="collapse" class="submenu-toggle" style="{{ request()->segment(3) == $child->id ? 'color:rgba(255,255,255,0.9)' : '' }}">
                        <i class="fas fa-user-graduate"></i><span>{{ $child->full_name }}</span><i class="fas fa-chevron-down sidebar-chevron" style="font-size:10px;transition:transform 0.25s ease;opacity:0.5;margin-left:auto;"></i>
                    </a>
                    <ul class="collapse {{ request()->segment(3) == $child->id ? 'show' : '' }}" id="childMenu{{ $child->id }}">
                        <li><a href="{{ route('parent.child.marks', $child->id) }}" class="{{ request()->routeIs('parent.child.marks') && request()->route('studentId') == $child->id ? 'active' : '' }}"><i class="fas fa-pen"></i> Marks</a></li>
                        <li><a href="{{ route('parent.child.progress', $child->id) }}" class="{{ request()->routeIs('parent.child.progress') && request()->route('studentId') == $child->id ? 'active' : '' }}"><i class="fas fa-chart-line"></i> Progress</a></li>
                        <li><a href="{{ route('parent.child.fees', $child->id) }}" class="{{ request()->routeIs('parent.child.fees') && request()->route('studentId') == $child->id ? 'active' : '' }}"><i class="fas fa-money-bill-wave"></i> Fees</a></li>
                        <li><a href="{{ route('parent.child.profile', $child->id) }}" class="{{ request()->routeIs('parent.child.profile') && request()->route('studentId') == $child->id ? 'active' : '' }}"><i class="fas fa-id-card"></i> Profile</a></li>
                    </ul>
                </li>
                @endforeach
                @endif

                <li class="menu-header">COMMUNICATION</li>
                <li class="{{ request()->routeIs('parent.chat.*') ? 'active' : '' }}">
                    <a href="{{ route('parent.chat.index') }}" class="{{ request()->routeIs('parent.chat.*') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i><span>Messages</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <div class="sidebar-footer-user">
                <div class="sidebar-footer-avatar">{{ strtoupper(substr($parentUser->name, 0, 1)) }}</div>
                <div class="sidebar-footer-info">
                    <span class="sidebar-footer-name">{{ $parentUser->name }}</span>
                    <span class="sidebar-footer-role">Parent</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="sidebar-backdrop d-none" id="sidebarBackdrop"></div>
    <div class="parent-main">
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
        <div id="parentAnnouncementBar" class="announcement-banner">
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
                <button onclick="document.getElementById('parentAnnouncementBar').style.display='none'" class="announcement-close" title="Dismiss"><i class="fas fa-times"></i></button>
            </div>
        </div>

        {{-- Announcement Splash Modal for Parent Portal --}}
        <div class="announcement-splash-overlay" id="parentAnnouncementSplash" style="display:none;">
            <div class="announcement-splash-modal" style="max-width:420px;">
                <div class="announcement-splash-header" style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);">
                    <div class="announcement-splash-icon" style="background:#f59e0b;"><i class="fas fa-bullhorn"></i></div>
                    <h2>Announcements</h2>
                    <span class="announcement-splash-count">{{ $activeAnnouncements->count() }} active</span>
                </div>
                <div class="announcement-splash-body">
                    @foreach($activeAnnouncements as $splashAnn)
                    <div class="announcement-splash-item">
                        <div class="announcement-splash-item-dot" style="background:{{ $splashAnn->color ?? '#f59e0b' }}"></div>
                        <div class="announcement-splash-item-content">
                            <div class="announcement-splash-item-title">{{ $splashAnn->title }}</div>
                            @if($splashAnn->category)
                            <span class="announcement-splash-item-cat" style="background:{{ $splashAnn->color ?? '#f59e0b' }}20;color:{{ $splashAnn->color ?? '#f59e0b' }}">{{ ucfirst($splashAnn->category) }}</span>
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
                    <button onclick="closeParentAnnouncementSplash()" class="announcement-splash-dismiss" style="width:100%;justify-content:center;background:#f59e0b;"><i class="fas fa-check"></i> Dismiss</button>
                </div>
            </div>
        </div>
        {{-- Parent-specific announcement accent color --}}
        <style>
        .announcement-badge { background: #f59e0b; }
        .announcement-banner { border-bottom: 2px solid #f59e0b; }
        .announcement-splash-dismiss { background: #f59e0b; }
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
            var splash = document.getElementById('parentAnnouncementSplash');
            if (splash) {
                var today = new Date().toISOString().slice(0, 10);
                var splashKey = 'parent_announcement_splash_dismissed_' + today;
                var alreadyShownToday = localStorage.getItem(splashKey);
                if (!alreadyShownToday) {
                    splash.style.display = 'flex';
                }
            }
        });

        function closeParentAnnouncementSplash() {
            var splash = document.getElementById('parentAnnouncementSplash');
            if (splash) {
                splash.style.opacity = '0';
                splash.style.transition = 'opacity 0.3s';
                setTimeout(function() { splash.style.display = 'none'; }, 300);
                var today = new Date().toISOString().slice(0, 10);
                localStorage.setItem('parent_announcement_splash_dismissed_' + today, '1');
            }
        }
        </script>
        @endif
        <nav class="parent-topbar portal-topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-breadcrumb">
                    <span>Welcome, </span><strong>{{ $parentUser->name }}</strong>
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ url('/') }}" class="topbar-avatar" style="width:32px;height:32px;border-radius:6px;font-size:12px;" title="View Website">
                    <i class="fas fa-external-link-alt" style="font-size:12px;"></i>
                </a>
                <div class="dropdown">
                    <button class="topbar-avatar" data-bs-toggle="dropdown">
                        {{ strtoupper(substr($parentUser->name, 0, 1)) }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            <div class="dropdown-header-name">{{ $parentUser->name }}</div>
                            <div class="dropdown-header-email">{{ $parentUser->email }}</div>
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
        <div class="parent-content portal-content">
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
    const sidebar = document.getElementById('parentSidebar');
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
@stack('scripts')
@yield('scripts')
</body>
</html>
