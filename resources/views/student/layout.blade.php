<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @stack('styles')
    <style>
    :root {
        --sidebar-w: 250px;
        --topbar-h: 56px;
        --primary: #0d9488;
        --primary-hover: #0f766e;
        --primary-light: rgba(13,148,136,0.1);
        --accent: #0ea5e9;
        --accent-light: rgba(14,165,233,0.1);
        --success: #10b981;
        --success-light: rgba(16,185,129,0.1);
        --warning: #f59e0b;
        --warning-light: rgba(245,158,11,0.1);
        --danger: #ef4444;
        --danger-light: rgba(239,68,68,0.1);
        --sidebar-bg: #0f766e;
        --sidebar-hover: rgba(255,255,255,0.1);
        --sidebar-active: rgba(255,255,255,0.18);
        --sidebar-text: rgba(255,255,255,0.75);
        --sidebar-text-active: #fff;
        --sidebar-border: rgba(255,255,255,0.1);
        --body-bg: #f0fdfa;
        --card-bg: #fff;
        --text-dark: #134e4a;
        --text: #475569;
        --text-muted: #94a3b8;
        --border: #e2e8f0;
        --radius: 10px;
        --radius-sm: 6px;
        --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        --shadow-md: 0 4px 6px rgba(0,0,0,0.06), 0 2px 4px rgba(0,0,0,0.04);
        --font: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        --transition: all 0.2s ease;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { font-size: 14px; }
    body {
        font-family: var(--font);
        background: var(--body-bg);
        color: var(--text);
        line-height: 1.5;
        min-height: 100vh;
        overflow-x: hidden;
    }
    a { text-decoration: none; color: inherit; }

    /* ===== LAYOUT ===== */
    .student-wrapper { display: flex; min-height: 100vh; }

    /* ===== SIDEBAR ===== */
    .student-sidebar {
        width: var(--sidebar-w);
        min-height: 100vh;
        background: linear-gradient(180deg, #0f766e 0%, #115e59 100%);
        position: fixed;
        left: 0; top: 0; bottom: 0;
        z-index: 100;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
        overflow: hidden;
    }
    .sidebar-header {
        padding: 16px 18px;
        border-bottom: 1px solid var(--sidebar-border);
        flex-shrink: 0;
    }
    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .sidebar-brand-icon {
        width: 36px; height: 36px;
        background: rgba(255,255,255,0.2);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 16px; flex-shrink: 0;
    }
    .sidebar-brand-text { display: flex; flex-direction: column; line-height: 1.2; }
    .sidebar-brand-pre {
        font-size: 9px; color: rgba(255,255,255,0.6);
        text-transform: uppercase; letter-spacing: 1.5px; font-weight: 500;
    }
    .sidebar-brand-name {
        font-size: 15px; font-weight: 800; color: #fff; letter-spacing: 1px;
    }

    /* Sidebar Menu */
    .sidebar-menu-wrap {
        flex: 1; overflow-y: auto; overflow-x: hidden;
        padding: 8px 0;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.15) transparent;
    }
    .sidebar-menu-wrap::-webkit-scrollbar { width: 4px; }
    .sidebar-menu-wrap::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }
    .sidebar-menu-wrap::-webkit-scrollbar-track { background: transparent; }

    .sidebar-menu { list-style: none; padding: 0; margin: 0; }
    .sidebar-menu .menu-header {
        padding: 16px 18px 6px;
        font-size: 10px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1.5px;
        color: rgba(255,255,255,0.35);
    }
    .sidebar-menu > li > a {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 18px;
        color: var(--sidebar-text); font-size: 13px; font-weight: 500;
        transition: var(--transition);
        border-left: 3px solid transparent; margin: 1px 0;
    }
    .sidebar-menu > li > a:hover {
        background: var(--sidebar-hover);
        color: rgba(255,255,255,0.95);
    }
    .sidebar-menu > li > a i:first-child {
        width: 18px; text-align: center; font-size: 13px; flex-shrink: 0;
    }
    .sidebar-menu > li > a span { flex: 1; }
    .sidebar-menu > li.active > a.active {
        background: var(--sidebar-active);
        color: var(--sidebar-text-active);
        border-left-color: #5eead4; font-weight: 600;
    }

    /* Sidebar Footer */
    .sidebar-footer {
        padding: 12px 18px;
        border-top: 1px solid var(--sidebar-border);
        flex-shrink: 0;
    }
    .sidebar-footer-user { display: flex; align-items: center; gap: 10px; }
    .sidebar-footer-avatar {
        width: 32px; height: 32px; border-radius: 8px;
        background: rgba(255,255,255,0.2);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; flex-shrink: 0;
    }
    .sidebar-footer-info { display: flex; flex-direction: column; line-height: 1.3; min-width: 0; }
    .sidebar-footer-name {
        font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.9);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .sidebar-footer-role { font-size: 10px; color: rgba(255,255,255,0.5); }

    /* Sidebar backdrop (mobile) */
    .sidebar-backdrop {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.4); z-index: 99;
        opacity: 0; transition: opacity 0.3s;
    }
    .sidebar-backdrop.show { opacity: 1; }

    /* ===== MAIN AREA ===== */
    .student-main {
        flex: 1; margin-left: var(--sidebar-w);
        min-height: 100vh; display: flex; flex-direction: column;
    }

    /* ===== TOPBAR ===== */
    .student-topbar {
        height: var(--topbar-h);
        background: var(--card-bg);
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 20px;
        position: sticky; top: 0; z-index: 50;
        box-shadow: var(--shadow);
    }
    .topbar-left { display: flex; align-items: center; gap: 12px; }
    .sidebar-toggle {
        background: none; border: none; font-size: 16px;
        color: var(--text); cursor: pointer; padding: 4px;
        border-radius: var(--radius-sm); transition: var(--transition);
    }
    .sidebar-toggle:hover { background: var(--body-bg); }
    .topbar-breadcrumb { font-size: 14px; color: var(--text-muted); }
    .topbar-breadcrumb strong { color: var(--text-dark); font-weight: 600; }
    .topbar-right { display: flex; align-items: center; gap: 8px; }
    .topbar-avatar-btn {
        width: 34px; height: 34px; border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), #0ea5e9);
        color: #fff; border: none; font-size: 13px; font-weight: 700;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        transition: var(--transition);
    }
    .topbar-avatar-btn:hover { box-shadow: 0 0 0 3px var(--primary-light); }

    /* ===== CONTENT ===== */
    .student-content { flex: 1; padding: 20px; }

    /* Global alert */
    .global-alert {
        padding: 10px 16px; border-radius: var(--radius-sm);
        margin-bottom: 16px; font-size: 13px; font-weight: 500;
        display: flex; align-items: center; gap: 8px;
        box-shadow: var(--shadow);
    }
    .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
    .alert-info { background: #f0f9ff; color: #1e40af; border: 1px solid #bfdbfe; }

    /* ===== STUDENT DASHBOARD CARDS ===== */
    .dash-welcome { margin-bottom: 20px; }
    .dash-welcome h2 { font-size: 20px; font-weight: 700; color: var(--text-dark); margin-bottom: 4px; }
    .dash-welcome p { font-size: 13px; color: var(--text-muted); }

    .dash-stats {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 16px; margin-bottom: 20px;
    }
    .dash-stat-card {
        background: var(--card-bg); border-radius: var(--radius);
        padding: 18px; box-shadow: var(--shadow);
        display: flex; align-items: center; gap: 14px;
        transition: var(--transition); border: 1px solid var(--border);
    }
    .dash-stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
    .dash-stat-icon {
        width: 44px; height: 44px; border-radius: var(--radius);
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .dash-stat-icon.teal { background: var(--primary-light); color: var(--primary); }
    .dash-stat-icon.blue { background: var(--accent-light); color: var(--accent); }
    .dash-stat-icon.green { background: var(--success-light); color: var(--success); }
    .dash-stat-icon.gold { background: var(--warning-light); color: var(--warning); }
    .dash-stat-icon.red { background: var(--danger-light); color: var(--danger); }
    .dash-stat-info h3 { font-size: 22px; font-weight: 700; color: var(--text-dark); line-height: 1.2; }
    .dash-stat-info p { font-size: 12px; color: var(--text-muted); font-weight: 500; }

    /* ===== CONTENT CARDS ===== */
    .student-card {
        background: var(--card-bg); border-radius: var(--radius);
        box-shadow: var(--shadow); border: 1px solid var(--border);
        overflow: hidden; margin-bottom: 20px;
    }
    .student-card-header {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .student-card-header h5 {
        font-size: 15px; font-weight: 700; color: var(--text-dark); margin: 0;
    }
    .student-card-body { padding: 18px; }

    /* ===== TABLE ===== */
    .student-table { width: 100%; font-size: 13px; }
    .student-table th {
        text-align: left; padding: 10px 14px;
        color: var(--text-muted); font-weight: 600; font-size: 11px;
        text-transform: uppercase; letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border); background: #f8fafc;
    }
    .student-table td {
        padding: 10px 14px; border-bottom: 1px solid #f3f4f6;
        vertical-align: middle; color: var(--text);
    }
    .student-table tbody tr { transition: var(--transition); }
    .student-table tbody tr:hover td { background: #f0fdfa; }

    /* ===== BADGES ===== */
    .grade-badge {
        display: inline-flex; align-items: center;
        padding: 3px 10px; border-radius: 20px;
        font-size: 11px; font-weight: 700;
    }
    .grade-a { background: #ecfdf5; color: #065f46; }
    .grade-b { background: #eff6ff; color: #1e40af; }
    .grade-c { background: #fffbeb; color: #92400e; }
    .grade-d { background: #fef2f2; color: #991b1b; }
    .grade-f { background: #fef2f2; color: #7f1d1d; }

    /* ===== FILTER BAR ===== */
    .filter-bar {
        display: flex; gap: 12px; flex-wrap: wrap; align-items: center;
    }
    .filter-bar select, .filter-bar input {
        border: 1px solid var(--border); border-radius: var(--radius-sm);
        padding: 7px 12px; font-size: 13px; color: var(--text-dark);
        background: var(--card-bg); font-family: var(--font);
        transition: var(--transition);
    }
    .filter-bar select:focus, .filter-bar input:focus {
        outline: none; border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }
    .filter-bar .btn-filter {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px; border-radius: var(--radius-sm);
        font-weight: 600; font-size: 12px; font-family: var(--font);
        border: none; cursor: pointer; transition: var(--transition);
        background: var(--primary); color: #fff;
    }
    .filter-bar .btn-filter:hover { background: var(--primary-hover); }

    /* ===== PROFILE ===== */
    .profile-header {
        display: flex; align-items: center; gap: 20px;
        padding: 24px; margin-bottom: 20px;
        background: linear-gradient(135deg, #0f766e, #0ea5e9);
        border-radius: var(--radius); color: #fff;
    }
    .profile-avatar {
        width: 72px; height: 72px; border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex; align-items: center; justify-content: center;
        font-size: 28px; font-weight: 700; flex-shrink: 0;
        border: 3px solid rgba(255,255,255,0.3);
    }
    .profile-info h3 { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
    .profile-info p { font-size: 13px; opacity: 0.85; margin-bottom: 2px; }
    .info-grid {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .info-item label {
        display: block; font-size: 11px; font-weight: 600;
        color: var(--text-muted); text-transform: uppercase;
        letter-spacing: 0.5px; margin-bottom: 4px;
    }
    .info-item span {
        font-size: 14px; font-weight: 500; color: var(--text-dark);
    }

    /* ===== PROGRESS BAR CHART ===== */
    .term-bar {
        margin-bottom: 16px;
    }
    .term-bar-label {
        display: flex; justify-content: space-between;
        margin-bottom: 4px;
    }
    .term-bar-label span { font-size: 13px; font-weight: 600; color: var(--text-dark); }
    .term-bar-label small { font-size: 12px; color: var(--text-muted); }
    .term-bar-track {
        height: 10px; background: #e2e8f0; border-radius: 5px; overflow: hidden;
    }
    .term-bar-fill {
        height: 100%; border-radius: 5px;
        background: linear-gradient(90deg, var(--primary), var(--accent));
        transition: width 0.8s ease;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center; padding: 40px 20px; color: var(--text-muted);
    }
    .empty-state i { font-size: 40px; opacity: 0.3; margin-bottom: 12px; display: block; }
    .empty-state p { font-size: 14px; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .student-sidebar { transform: translateX(-100%); }
        .student-sidebar.show { transform: translateX(0); }
        .student-main { margin-left: 0; }
        .dash-stats { grid-template-columns: repeat(2, 1fr); }
        .info-grid { grid-template-columns: 1fr; }
        .profile-header { flex-direction: column; text-align: center; }
    }
    @media (max-width: 480px) {
        .dash-stats { grid-template-columns: 1fr; }
        .student-content { padding: 12px; }
        .filter-bar { flex-direction: column; }
    }

    /* Dropdown overrides */
    .dropdown-menu {
        font-size: 13px; border: 1px solid var(--border);
        border-radius: var(--radius-sm); box-shadow: var(--shadow-md); padding: 4px;
    }
    .dropdown-header { padding: 8px 12px; font-size: 12px; }
    .dropdown-header-name { font-weight: 600; color: var(--text-dark); }
    .dropdown-header-email { font-size: 11px; color: var(--text-muted); }
    .dropdown-item { border-radius: 4px; padding: 6px 12px; font-size: 13px; }

    /* Fee summary cards */
    .fee-summary {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 16px; margin-bottom: 20px;
    }
    @media (max-width: 576px) {
        .fee-summary { grid-template-columns: 1fr; }
    }
    .fee-card {
        padding: 20px; border-radius: var(--radius);
        text-align: center; border: 1px solid var(--border);
        box-shadow: var(--shadow);
    }
    .fee-card.total { background: linear-gradient(135deg, #0f766e, #0ea5e9); color: #fff; border: none; }
    .fee-card.paid { background: #ecfdf5; border-color: #a7f3d0; }
    .fee-card.balance { background: #fef2f2; border-color: #fecaca; }
    .fee-card h6 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 6px; opacity: 0.8; }
    .fee-card .amount { font-size: 24px; font-weight: 800; }
    .fee-card.total h6 { color: rgba(255,255,255,0.8); }
    .fee-card.total .amount { color: #fff; }
    .fee-card.paid h6 { color: #065f46; }
    .fee-card.paid .amount { color: #065f46; }
    .fee-card.balance h6 { color: #991b1b; }
    .fee-card.balance .amount { color: #991b1b; }
    </style>
</head>
<body>
<div class="student-wrapper">
    {{-- SIDEBAR --}}
    <nav class="student-sidebar" id="studentSidebar">
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
        <style>
        .announcement-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            position: relative;
            z-index: 60;
            border-bottom: 2px solid #06b6d4;
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
            background: #06b6d4;
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
        @media (max-width: 768px) {
            .announcement-banner-inner { padding: 0 10px; height: 36px; }
            .announcement-badge { font-size: .72rem; padding: 2px 8px; }
            .announcement-chip { font-size: .8rem; }
        }
        @media print { .announcement-banner, .announcement-splash-overlay { display: none !important; } }

        /* Splash Modal Styles */
        .announcement-splash-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:10000;display:flex;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(4px);animation:splashFadeIn .3s ease; }
        @keyframes splashFadeIn { from{opacity:0}to{opacity:1} }
        .announcement-splash-modal { background:#fff;border-radius:16px;width:100%;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,0.3);animation:splashSlideUp .3s ease;overflow:hidden; }
        @keyframes splashSlideUp { from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1} }
        .announcement-splash-header { color:#fff;padding:20px 24px;display:flex;align-items:center;gap:12px; }
        .announcement-splash-icon { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0; }
        .announcement-splash-header h2 { font-size:18px;font-weight:700;margin:0;flex:1; }
        .announcement-splash-count { font-size:12px;font-weight:600;background:rgba(255,255,255,0.2);padding:3px 10px;border-radius:20px; }
        .announcement-splash-body { padding:16px 20px;overflow-y:auto;flex:1; }
        .announcement-splash-item { display:flex;gap:12px;padding:14px 0;border-bottom:1px solid #f3f4f6; }
        .announcement-splash-item:last-child { border-bottom:none; }
        .announcement-splash-item-dot { width:10px;height:10px;border-radius:50%;margin-top:5px;flex-shrink:0; }
        .announcement-splash-item-content { flex:1;min-width:0; }
        .announcement-splash-item-title { font-size:14px;font-weight:700;color:#1e293b;margin-bottom:6px; }
        .announcement-splash-item-cat { display:inline-block;font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;margin-right:8px; }
        .announcement-splash-item-date { font-size:11px;color:#9ca3af;display:inline-flex;align-items:center;gap:4px; }
        .announcement-splash-item-desc { font-size:13px;color:#6b7280;margin:8px 0 0;line-height:1.5; }
        .announcement-splash-footer { padding:16px 20px;border-top:1px solid #f3f4f6; }
        .announcement-splash-dismiss { display:inline-flex;align-items:center;gap:6px;padding:8px 20px;border-radius:10px;color:#fff;border:none;font-size:13px;font-weight:600;cursor:pointer;transition:background .2s; }
        .announcement-splash-dismiss:hover { filter:brightness(0.9); }
        @media (max-width:768px) {
            .announcement-splash-modal { max-width:100%;max-height:90vh;border-radius:12px; }
            .announcement-splash-header { padding:16px; }
            .announcement-splash-header h2 { font-size:16px; }
            .announcement-splash-body { padding:12px 16px; }
            .announcement-splash-footer { padding:12px 16px; }
        }
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

            // Show splash only on fresh load or browser refresh, NOT on menu navigation
            var splash = document.getElementById('studentAnnouncementSplash');
            if (splash) {
                var navEntry = performance.getEntriesByType('navigation')[0];
                var isReload = navEntry ? navEntry.type === 'reload' : (performance.navigation && performance.navigation.type === 1);
                var alreadyShown = sessionStorage.getItem('student_announcement_splash_dismissed');
                if (isReload || !alreadyShown) {
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
                sessionStorage.setItem('student_announcement_splash_dismissed', '1');
            }
        }
        </script>
        @endif
        <nav class="student-topbar">
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

        <div class="student-content">
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
