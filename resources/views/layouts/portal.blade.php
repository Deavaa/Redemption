<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal') - Redemption School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --sidebar-bg: #1a1a2e;
            --sidebar-text: #cbd5e1;
            --sidebar-active: #4361ee;
            --text-dark: #1a1a2e;
            --text-muted: #6c757d;
            --body-bg: #f3f4f6;
            --card-bg: #ffffff;
            --border: #e5e7eb;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--body-bg); color: var(--text-dark); min-height: 100vh; }

        .portal-wrapper { display: flex; min-height: 100vh; }

        /* Sidebar */
        .portal-sidebar { width: 250px; background: var(--sidebar-bg); color: var(--sidebar-text); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 100; transition: transform 0.3s; }
        .portal-sidebar-brand { display: flex; align-items: center; gap: 0.75rem; padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08); text-decoration: none; }
        .portal-sidebar-brand i { font-size: 1.5rem; color: var(--primary); }
        .portal-sidebar-brand span { font-weight: 800; font-size: 1.1rem; color: #fff; }
        .portal-sidebar-menu { flex: 1; padding: 1rem 0; overflow-y: auto; }
        .portal-sidebar-menu a { display: flex; align-items: center; gap: 0.75rem; padding: 0.7rem 1.5rem; color: var(--sidebar-text); text-decoration: none; font-size: 0.88rem; font-weight: 500; transition: all 0.2s; border-left: 3px solid transparent; }
        .portal-sidebar-menu a:hover { background: rgba(255,255,255,0.06); color: #fff; }
        .portal-sidebar-menu a.active { background: rgba(67,97,238,0.15); color: #fff; border-left-color: var(--primary); }
        .portal-sidebar-menu a i { width: 20px; text-align: center; font-size: 0.95rem; }
        .portal-sidebar-footer { padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.08); }
        .portal-sidebar-footer form { display: inline; }
        .portal-sidebar-footer button { background: none; border: none; color: var(--sidebar-text); font-size: 0.85rem; cursor: pointer; padding: 0; font-family: inherit; }
        .portal-sidebar-footer button:hover { color: #f87171; }

        /* Main */
        .portal-main { margin-left: 250px; flex: 1; min-height: 100vh; display: flex; flex-direction: column; }
        .portal-topbar { background: var(--card-bg); border-bottom: 1px solid var(--border); padding: 0.75rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .portal-topbar-title { font-weight: 700; font-size: 1rem; }
        .portal-topbar-user { display: flex; align-items: center; gap: 0.75rem; font-size: 0.88rem; }
        .portal-topbar-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; }
        .portal-content { padding: 1.5rem; flex: 1; }

        /* Cards */
        .portal-card { background: var(--card-bg); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid var(--border); margin-bottom: 1.25rem; overflow: hidden; }
        .portal-card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); background: #fafbfc; font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem; }
        .portal-card-body { padding: 1.25rem; }

        /* Stat cards */
        .stat-card { background: var(--card-bg); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid var(--border); padding: 1.25rem; text-align: center; }
        .stat-card .stat-value { font-size: 1.75rem; font-weight: 800; color: var(--primary); }
        .stat-card .stat-label { font-size: 0.82rem; color: var(--text-muted); font-weight: 600; margin-top: 0.25rem; }

        /* Table */
        .portal-table { width: 100%; font-size: 0.88rem; }
        .portal-table th { background: #f8fafc; font-weight: 700; padding: 0.7rem 0.75rem; border-bottom: 2px solid var(--border); text-align: left; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; color: var(--text-muted); }
        .portal-table td { padding: 0.6rem 0.75rem; border-bottom: 1px solid #f3f4f6; }
        .portal-table tbody tr:hover { background: #f8fafc; }

        /* Grade badges */
        .grade-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 6px; font-size: 0.78rem; font-weight: 700; }
        .grade-A { background: #d1fae5; color: #065f46; }
        .grade-B { background: #dbeafe; color: #1e40af; }
        .grade-C { background: #fef3c7; color: #92400e; }
        .grade-D { background: #fed7aa; color: #9a3412; }
        .grade-F { background: #fecaca; color: #991b1b; }

        /* Alerts */
        .portal-alert { padding: 0.75rem 1.25rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.88rem; font-weight: 500; }
        .portal-alert.success { background: #d1fae5; color: #065f46; }
        .portal-alert.info { background: #dbeafe; color: #1e40af; }
        .portal-alert.warning { background: #fef3c7; color: #92400e; }

        /* Mobile */
        .portal-sidebar-toggle { display: none; background: none; border: none; font-size: 1.2rem; color: var(--text-dark); cursor: pointer; }
        @media (max-width: 768px) {
            .portal-sidebar { transform: translateX(-100%); }
            .portal-sidebar.show { transform: translateX(0); }
            .portal-main { margin-left: 0; }
            .portal-sidebar-toggle { display: inline-flex; }
        }

        /* Child selector tabs for parent */
        .child-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
        .child-tab { padding: 0.5rem 1rem; border-radius: 8px; border: 1.5px solid var(--border); background: #fff; font-weight: 600; font-size: 0.85rem; color: var(--text-dark); cursor: pointer; text-decoration: none; transition: all 0.2s; }
        .child-tab:hover { border-color: var(--primary); color: var(--primary); }
        .child-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    </style>
</head>
<body>
<div class="portal-wrapper">
    <nav class="portal-sidebar" id="portalSidebar">
        <a href="@yield('home_route', route('portal.dashboard'))" class="portal-sidebar-brand">
            <i class="fas fa-graduation-cap"></i>
            <span>Redemption</span>
        </a>
        <div class="portal-sidebar-menu">
            @yield('sidebar_menu')
        </div>
        <div class="portal-sidebar-footer">
            <span style="font-size:0.82rem;opacity:0.7;">{{ Auth::user()->name }}</span>
            <span style="float:right;">
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </span>
        </div>
    </nav>
    <div class="portal-main">
        <div class="portal-topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="portal-sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <span class="portal-topbar-title">@yield('topbar_title', 'Dashboard')</span>
            </div>
            <div class="portal-topbar-user">
                <a href="{{ url('/') }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fas fa-external-link-alt me-1"></i>Website</a>
                <div class="portal-topbar-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            </div>
        </div>
        <div class="portal-content">
            @if(session('success'))
                <div class="portal-alert success"><i class="fas fa-check-circle me-1"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="portal-alert warning"><i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    const sidebar = document.getElementById('portalSidebar');
    const toggle = document.getElementById('sidebarToggle');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => sidebar.classList.toggle('show'));
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 768 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
})();
</script>
</body>
</html>
