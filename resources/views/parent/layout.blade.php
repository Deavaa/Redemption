<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Parent Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    :root {
        --sidebar-w: 260px;
        --topbar-h: 56px;
        --primary: #ea580c;
        --primary-hover: #c2410c;
        --primary-light: rgba(234,88,12,0.1);
        --primary-dark: #9a3412;
        --accent: #f59e0b;
        --accent-light: rgba(245,158,11,0.1);
        --success: #10b981;
        --success-light: rgba(16,185,129,0.1);
        --warning: #f59e0b;
        --warning-light: rgba(245,158,11,0.1);
        --danger: #ef4444;
        --danger-light: rgba(239,68,68,0.1);
        --info: #0ea5e9;
        --info-light: rgba(14,165,233,0.1);
        --sidebar-bg: #1c1917;
        --sidebar-hover: rgba(255,255,255,0.06);
        --sidebar-active: rgba(234,88,12,0.25);
        --sidebar-text: rgba(255,255,255,0.65);
        --sidebar-text-active: #fff;
        --sidebar-border: rgba(255,255,255,0.08);
        --body-bg: #faf7f5;
        --card-bg: #fff;
        --text-dark: #292524;
        --text: #57534e;
        --text-muted: #a8a29e;
        --border: #e7e5e4;
        --radius: 10px;
        --radius-sm: 6px;
        --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
        --shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
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

    .parent-wrapper { display: flex; min-height: 100vh; }

    /* ===== SIDEBAR ===== */
    .parent-sidebar {
        width: var(--sidebar-w);
        min-height: 100vh;
        background: var(--sidebar-bg);
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
        background: linear-gradient(135deg, var(--primary), var(--accent));
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 16px; flex-shrink: 0;
    }
    .sidebar-brand-text { display: flex; flex-direction: column; line-height: 1.2; }
    .sidebar-brand-pre {
        font-size: 10px; color: var(--sidebar-text);
        text-transform: uppercase; letter-spacing: 1.5px; font-weight: 500;
    }
    .sidebar-brand-name {
        font-size: 15px; font-weight: 800; color: #fff; letter-spacing: 1px;
    }

    .sidebar-menu-wrap {
        flex: 1; overflow-y: auto; overflow-x: hidden;
        padding: 8px 0;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.1) transparent;
    }
    .sidebar-menu-wrap::-webkit-scrollbar { width: 4px; }
    .sidebar-menu-wrap::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
    .sidebar-menu-wrap::-webkit-scrollbar-track { background: transparent; }

    .sidebar-menu { list-style: none; padding: 0; margin: 0; }
    .sidebar-menu .menu-header {
        padding: 16px 18px 6px;
        font-size: 10px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1.5px;
        color: rgba(255,255,255,0.3);
    }

    .sidebar-menu > li > a {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 18px;
        color: var(--sidebar-text);
        font-size: 13px; font-weight: 500;
        transition: var(--transition);
        border-left: 3px solid transparent;
        margin: 1px 0;
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
        border-left-color: var(--primary);
        font-weight: 600;
    }

    /* Submenu child links */
    .sidebar-menu .collapse, .sidebar-menu .collapsing {
        list-style: none; padding: 0; margin: 0;
    }
    .sidebar-menu .collapse li a {
        display: flex; align-items: center; gap: 8px;
        padding: 7px 18px 7px 46px;
        color: var(--sidebar-text);
        font-size: 12.5px; font-weight: 400;
        transition: var(--transition);
        border-left: 3px solid transparent;
    }
    .sidebar-menu .collapse li a i {
        width: 16px; text-align: center; font-size: 11px; opacity: 0.6;
    }
    .sidebar-menu .collapse li a:hover {
        background: var(--sidebar-hover);
        color: rgba(255,255,255,0.95);
    }
    .sidebar-menu .collapse li a.active {
        background: var(--sidebar-active);
        color: var(--sidebar-text-active);
        border-left-color: var(--primary);
        font-weight: 600;
    }
    .sidebar-menu .collapse li a.active i {
        opacity: 1; color: var(--primary);
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
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; flex-shrink: 0;
    }
    .sidebar-footer-info { display: flex; flex-direction: column; line-height: 1.3; min-width: 0; }
    .sidebar-footer-name {
        font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.9);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .sidebar-footer-role { font-size: 10px; color: rgba(255,255,255,0.4); }

    /* Sidebar backdrop (mobile) */
    .sidebar-backdrop {
        position: fixed; inset: 0; background: rgba(0,0,0,0.4);
        z-index: 99; opacity: 0; transition: opacity 0.3s;
    }
    .sidebar-backdrop.show { opacity: 1; }

    /* ===== MAIN AREA ===== */
    .parent-main {
        flex: 1; margin-left: var(--sidebar-w);
        min-height: 100vh; display: flex; flex-direction: column;
    }

    /* ===== TOPBAR ===== */
    .parent-topbar {
        height: var(--topbar-h);
        background: var(--card-bg);
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 20px;
        position: sticky; top: 0; z-index: 50;
        box-shadow: var(--shadow-sm);
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
    .topbar-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: #fff; border: none; font-size: 13px; font-weight: 700;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        transition: var(--transition);
    }
    .topbar-avatar:hover { box-shadow: 0 0 0 3px var(--primary-light); }

    /* ===== CONTENT ===== */
    .parent-content { flex: 1; padding: 20px; }

    /* Global alert */
    .global-alert {
        padding: 10px 16px; border-radius: var(--radius-sm);
        margin-bottom: 16px; font-size: 13px; font-weight: 500;
        display: flex; align-items: center; gap: 8px;
        box-shadow: var(--shadow-sm);
    }
    .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
    .alert-info { background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; }

    /* ===== DASHBOARD CARDS ===== */
    .dash-welcome { margin-bottom: 20px; }
    .dash-welcome h2 { font-size: 20px; font-weight: 700; color: var(--text-dark); margin-bottom: 4px; }
    .dash-welcome p { font-size: 13px; color: var(--text-muted); }

    .child-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        overflow: hidden;
        transition: var(--transition);
    }
    .child-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    .child-card-header {
        padding: 16px 18px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 12px;
    }
    .child-avatar {
        width: 42px; height: 42px; border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 16px; font-weight: 700; flex-shrink: 0;
    }
    .child-card-header h5 {
        font-size: 15px; font-weight: 700; color: var(--text-dark); margin: 0;
    }
    .child-card-header small { font-size: 12px; color: var(--text-muted); display: block; }
    .child-card-body { padding: 16px 18px; }
    .child-stats {
        display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;
    }
    .child-stat {
        text-align: center; padding: 10px 8px;
        border-radius: var(--radius-sm); background: var(--body-bg);
    }
    .child-stat-value {
        font-size: 18px; font-weight: 700; color: var(--text-dark);
    }
    .child-stat-label {
        font-size: 11px; color: var(--text-muted); font-weight: 500; margin-top: 2px;
    }
    .child-card-footer {
        padding: 12px 18px;
        border-top: 1px solid var(--border);
        display: flex; gap: 8px; flex-wrap: wrap;
    }
    .child-card-footer a {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 10px; border-radius: var(--radius-sm);
        font-size: 11px; font-weight: 600;
        color: var(--primary); background: var(--primary-light);
        transition: var(--transition);
    }
    .child-card-footer a:hover {
        background: var(--primary); color: #fff;
    }

    /* ===== PAGE HEADER ===== */
    .page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 20px; flex-wrap: wrap; gap: 10px;
    }
    .page-header h4 {
        font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0;
    }
    .page-header-sub {
        font-size: 13px; color: var(--text-muted); margin-top: 2px;
    }

    /* ===== INFO CARDS ===== */
    .info-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        overflow: hidden;
    }
    .info-card-header {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .info-card-header h5 {
        font-size: 14px; font-weight: 700; color: var(--text-dark); margin: 0;
    }
    .info-card-body { padding: 18px; }

    /* ===== STAT CARDS ===== */
    .stat-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px; }
    .stat-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        padding: 18px;
        box-shadow: var(--shadow);
        display: flex; align-items: center; gap: 14px;
        transition: var(--transition);
        border: 1px solid var(--border);
    }
    .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
    .stat-icon {
        width: 44px; height: 44px; border-radius: var(--radius);
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .stat-icon.orange { background: var(--primary-light); color: var(--primary); }
    .stat-icon.green { background: var(--success-light); color: var(--success); }
    .stat-icon.red { background: var(--danger-light); color: var(--danger); }
    .stat-icon.amber { background: var(--accent-light); color: var(--accent); }
    .stat-icon.blue { background: var(--info-light); color: var(--info); }
    .stat-info h3 { font-size: 22px; font-weight: 700; color: var(--text-dark); line-height: 1.2; }
    .stat-info p { font-size: 12px; color: var(--text-muted); font-weight: 500; }

    /* ===== TABLE ===== */
    .modern-table { width: 100%; font-size: 13px; }
    .modern-table th {
        text-align: left; padding: 10px 14px;
        color: var(--text-muted); font-weight: 600;
        font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border); background: #fafaf9;
    }
    .modern-table td {
        padding: 10px 14px; border-bottom: 1px solid #f3f4f6;
        vertical-align: middle; color: var(--text);
    }
    .modern-table tbody tr { transition: var(--transition); }
    .modern-table tbody tr:hover td { background: #fefce8; }

    /* ===== BADGES ===== */
    .modern-badge {
        display: inline-flex; align-items: center;
        padding: 3px 8px; border-radius: 20px;
        font-size: 11px; font-weight: 600;
    }
    .modern-badge-orange { background: var(--primary-light); color: var(--primary); }
    .modern-badge-green { background: var(--success-light); color: var(--success); }
    .modern-badge-red { background: var(--danger-light); color: var(--danger); }
    .modern-badge-amber { background: var(--accent-light); color: var(--accent); }
    .modern-badge-blue { background: var(--info-light); color: var(--info); }
    .modern-badge-light { background: #f5f5f4; color: var(--text-muted); }

    /* ===== BUTTONS ===== */
    .btn-modern {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px; border-radius: var(--radius-sm);
        font-weight: 600; font-size: 12px; font-family: var(--font);
        text-decoration: none; border: none; cursor: pointer;
        transition: var(--transition); white-space: nowrap;
    }
    .btn-modern-primary {
        background: var(--primary); color: #fff;
        box-shadow: 0 1px 2px rgba(234,88,12,0.3);
    }
    .btn-modern-primary:hover {
        background: var(--primary-hover); color: #fff;
        box-shadow: 0 2px 6px rgba(234,88,12,0.4);
    }
    .btn-modern-outline {
        background: transparent; color: var(--text-muted);
        border: 1px solid var(--border);
    }
    .btn-modern-outline:hover {
        border-color: var(--primary); color: var(--primary);
        background: var(--primary-light);
    }
    .btn-modern-success { background: var(--success); color: #fff; }
    .btn-modern-success:hover { background: #059669; color: #fff; }

    /* ===== FILTER BAR ===== */
    .filter-bar {
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 16px; flex-wrap: wrap;
    }
    .filter-bar select, .filter-bar input {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 7px 10px; font-size: 13px;
        color: var(--text-dark); background: var(--card-bg);
        font-family: var(--font);
    }
    .filter-bar select:focus, .filter-bar input:focus {
        outline: none; border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    /* ===== PROFILE DETAIL ===== */
    .profile-detail {
        display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
    }
    .profile-item {
        padding: 10px 14px; border-radius: var(--radius-sm);
        background: var(--body-bg);
    }
    .profile-item-label {
        font-size: 11px; font-weight: 600; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;
    }
    .profile-item-value {
        font-size: 14px; font-weight: 500; color: var(--text-dark);
    }

    /* ===== PROGRESS TREND ===== */
    .trend-item {
        display: flex; align-items: center; gap: 16px;
        padding: 14px 18px;
        border-bottom: 1px solid #f3f4f6;
    }
    .trend-item:last-child { border-bottom: none; }
    .trend-term-name {
        font-size: 13px; font-weight: 600; color: var(--text-dark);
        min-width: 100px;
    }
    .trend-bar-wrap { flex: 1; background: #f5f5f4; border-radius: 6px; height: 24px; position: relative; overflow: hidden; }
    .trend-bar {
        height: 100%; border-radius: 6px;
        background: linear-gradient(90deg, var(--primary), var(--accent));
        transition: width 0.5s ease;
        display: flex; align-items: center; justify-content: flex-end;
        padding-right: 8px;
    }
    .trend-bar span {
        font-size: 10px; font-weight: 700; color: #fff;
    }
    .trend-details {
        font-size: 11px; color: var(--text-muted); min-width: 120px; text-align: right;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center; padding: 40px 20px;
    }
    .empty-state i { font-size: 40px; color: var(--text-muted); opacity: 0.3; margin-bottom: 12px; }
    .empty-state h5 { font-size: 16px; font-weight: 600; color: var(--text-dark); margin-bottom: 4px; }
    .empty-state p { font-size: 13px; color: var(--text-muted); }

    /* ===== DROPDOWN OVERRIDES ===== */
    .dropdown-menu {
        font-size: 13px; border: 1px solid var(--border);
        border-radius: var(--radius-sm); box-shadow: var(--shadow-md); padding: 4px;
    }
    .dropdown-header { padding: 8px 12px; font-size: 12px; }
    .dropdown-header-name { font-weight: 600; color: var(--text-dark); }
    .dropdown-header-email { font-size: 11px; color: var(--text-muted); }
    .dropdown-item { border-radius: 4px; padding: 6px 12px; font-size: 13px; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .parent-sidebar { transform: translateX(-100%); }
        .parent-sidebar.show { transform: translateX(0); }
        .parent-main { margin-left: 0; }
        .child-stats { grid-template-columns: 1fr; }
        .stat-cards { grid-template-columns: 1fr; }
        .profile-detail { grid-template-columns: 1fr; }
    }
    @media (max-width: 480px) {
        .stat-cards { grid-template-columns: 1fr; }
        .parent-content { padding: 12px; }
    }
    </style>
    @stack('styles')
</head>
<body>
<div class="parent-wrapper">
    <nav class="parent-sidebar" id="parentSidebar">
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
                        <i class="fas fa-user-graduate"></i><span>{{ $child->first_name }} {{ $child->last_name }}</span><i class="fas fa-chevron-down sidebar-chevron" style="font-size:10px;transition:transform 0.25s ease;opacity:0.5;margin-left:auto;"></i>
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
        <style>
        .announcement-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            position: relative;
            z-index: 60;
            border-bottom: 2px solid #f59e0b;
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
            background: #f59e0b;
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
        @media print { .announcement-banner { display: none !important; } }
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
        });
        </script>
        @endif
        <nav class="parent-topbar">
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
        <div class="parent-content">
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
