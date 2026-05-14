<?php
 $css = <<<'CSS_CONTENT'
/* ===== REDEMPTION SCHOOL MANAGEMENT - MODERN TEMPLATE ===== */
:root {
    --sidebar-w: 260px;
    --topbar-h: 56px;
    --primary: #6366f1;
    --primary-hover: #4f46e5;
    --primary-light: rgba(99,102,241,0.1);
    --success: #10b981;
    --success-light: rgba(16,185,129,0.1);
    --warning: #f59e0b;
    --warning-light: rgba(245,158,11,0.1);
    --danger: #ef4444;
    --danger-light: rgba(239,68,68,0.1);
    --info: #3b82f6;
    --info-light: rgba(59,130,246,0.1);
    --sidebar-bg: #0f172a;
    --sidebar-hover: rgba(255,255,255,0.06);
    --sidebar-active: rgba(99,102,241,0.2);
    --sidebar-text: rgba(255,255,255,0.7);
    --sidebar-text-active: #fff;
    --sidebar-border: rgba(255,255,255,0.06);
    --body-bg: #f1f5f9;
    --card-bg: #fff;
    --text-dark: #1e293b;
    --text: #475569;
    --text-muted: #94a3b8;
    --border: #e2e8f0;
    --radius: 10px;
    --radius-sm: 6px;
    --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
    --shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.06), 0 2px 4px rgba(0,0,0,0.04);
    --font: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
    --transition: all 0.2s ease;
}

/* ===== RESET & BASE ===== */
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
.admin-wrapper {
    display: flex;
    min-height: 100vh;
}

/* ===== SIDEBAR ===== */
.admin-sidebar {
    width: var(--sidebar-w);
    min-height: 100vh;
    background: var(--sidebar-bg);
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
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
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, var(--primary), #8b5cf6);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
    flex-shrink: 0;
}

.sidebar-brand-text {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.sidebar-brand-pre {
    font-size: 10px;
    color: var(--sidebar-text);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 500;
}

.sidebar-brand-name {
    font-size: 16px;
    font-weight: 800;
    color: #fff;
    letter-spacing: 2px;
}

/* Sidebar Menu */
.sidebar-menu-wrap {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 8px 0;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.1) transparent;
}
.sidebar-menu-wrap::-webkit-scrollbar { width: 4px; }
.sidebar-menu-wrap::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
.sidebar-menu-wrap::-webkit-scrollbar-track { background: transparent; }

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu .menu-header {
    padding: 16px 18px 6px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: rgba(255,255,255,0.3);
}

.sidebar-menu > li > a,
.sidebar-menu > li > .submenu-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 18px;
    color: var(--sidebar-text);
    font-size: 13px;
    font-weight: 500;
    transition: var(--transition);
    border-left: 3px solid transparent;
    margin: 1px 0;
}

.sidebar-menu > li > a:hover,
.sidebar-menu > li > .submenu-toggle:hover {
    background: var(--sidebar-hover);
    color: rgba(255,255,255,0.95);
}

.sidebar-menu > li > a i:first-child,
.sidebar-menu > li > .submenu-toggle i:first-child {
    width: 18px;
    text-align: center;
    font-size: 13px;
    flex-shrink: 0;
}

.sidebar-menu > li > a span,
.sidebar-menu > li > .submenu-toggle span {
    flex: 1;
}

.sidebar-chevron {
    font-size: 10px;
    transition: transform 0.25s ease;
    opacity: 0.5;
}

.sidebar-menu > li.has-active-child > .submenu-toggle .sidebar-chevron,
.sidebar-menu > li > .submenu-toggle[aria-expanded="true"] .sidebar-chevron {
    transform: rotate(180deg);
    opacity: 0.8;
}

/* Active top-level item (Dashboard, Settings) */
.sidebar-menu > li.active > a.active {
    background: var(--sidebar-active);
    color: var(--sidebar-text-active);
    border-left-color: var(--primary);
    font-weight: 600;
}

/* Parent group with active child - NOT highlighted, just shows chevron */
.sidebar-menu > li.has-active-child > .submenu-toggle {
    color: rgba(255,255,255,0.9);
}

/* Submenu */
.sidebar-menu .collapse,
.sidebar-menu .collapsing {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu .collapse li a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 18px 7px 46px;
    color: var(--sidebar-text);
    font-size: 12.5px;
    font-weight: 400;
    transition: var(--transition);
    border-left: 3px solid transparent;
}

.sidebar-menu .collapse li a i {
    width: 16px;
    text-align: center;
    font-size: 11px;
    opacity: 0.6;
}

.sidebar-menu .collapse li a:hover {
    background: var(--sidebar-hover);
    color: rgba(255,255,255,0.95);
}

/* ACTIVE CHILD - the key highlight */
.sidebar-menu .collapse li a.active {
    background: var(--sidebar-active);
    color: var(--sidebar-text-active);
    border-left-color: var(--primary);
    font-weight: 600;
}

.sidebar-menu .collapse li a.active i {
    opacity: 1;
    color: var(--primary);
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 12px 18px;
    border-top: 1px solid var(--sidebar-border);
    flex-shrink: 0;
}

.sidebar-footer-user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-footer-avatar {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, var(--primary), #8b5cf6);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    flex-shrink: 0;
}

.sidebar-footer-info {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
    min-width: 0;
}

.sidebar-footer-name {
    font-size: 12px;
    font-weight: 600;
    color: rgba(255,255,255,0.9);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-footer-role {
    font-size: 10px;
    color: rgba(255,255,255,0.4);
}

/* Sidebar backdrop (mobile) */
.sidebar-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 99;
    opacity: 0;
    transition: opacity 0.3s;
}
.sidebar-backdrop.show { opacity: 1; }

/* ===== MAIN AREA ===== */
.admin-main {
    flex: 1;
    margin-left: var(--sidebar-w);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ===== TOPBAR ===== */
.admin-topbar {
    height: var(--topbar-h);
    background: var(--card-bg);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    position: sticky;
    top: 0;
    z-index: 50;
    box-shadow: var(--shadow-sm);
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.sidebar-toggle {
    background: none;
    border: none;
    font-size: 16px;
    color: var(--text);
    cursor: pointer;
    padding: 4px;
    border-radius: var(--radius-sm);
    transition: var(--transition);
}
.sidebar-toggle:hover { background: var(--body-bg); }

.topbar-breadcrumb {
    font-size: 14px;
    color: var(--text-muted);
}
.topbar-breadcrumb strong {
    color: var(--text-dark);
    font-weight: 600;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
}

.topbar-link {
    width: 34px;
    height: 34px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    transition: var(--transition);
    font-size: 14px;
}
.topbar-link:hover {
    background: var(--body-bg);
    color: var(--text);
}

.topbar-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), #8b5cf6);
    color: #fff;
    border: none;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}
.topbar-avatar:hover {
    box-shadow: 0 0 0 3px var(--primary-light);
}

/* ===== CONTENT ===== */
.admin-content {
    flex: 1;
    padding: 20px;
}

/* Global alert */
.global-alert {
    padding: 10px 16px;
    border-radius: var(--radius-sm);
    margin-bottom: 16px;
    font-size: 13px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: var(--shadow-sm);
}
.alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.alert-info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }

/* ===== DASHBOARD ===== */
.dash-welcome {
    margin-bottom: 20px;
}
.dash-welcome h2 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 4px;
}
.dash-welcome p {
    font-size: 13px;
    color: var(--text-muted);
}

.dash-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.dash-stat-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 18px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 14px;
    transition: var(--transition);
    border: 1px solid var(--border);
}
.dash-stat-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
}

.dash-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.dash-stat-icon.blue { background: var(--primary-light); color: var(--primary); }
.dash-stat-icon.green { background: var(--success-light); color: var(--success); }
.dash-stat-icon.gold { background: var(--warning-light); color: var(--warning); }
.dash-stat-icon.red { background: var(--danger-light); color: var(--danger); }
.dash-stat-icon.info { background: var(--info-light); color: var(--info); }

.dash-stat-info h3 {
    font-size: 22px;
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1.2;
}
.dash-stat-info p {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
}

.dash-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 16px;
}

.dash-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
}

.dash-card-header {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.dash-card-header h5 {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0;
}

.dash-card-body {
    padding: 18px;
}

.dash-card-body p {
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.6;
}

/* Quick action links in dashboard */
.dash-quick-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.dash-quick-action {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 500;
    color: var(--text);
    transition: var(--transition);
    border: 1px solid var(--border);
}
.dash-quick-action:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: var(--primary-light);
}
.dash-quick-action i {
    font-size: 14px;
    opacity: 0.7;
}

/* ===== MODERN CARD (for CRUD pages) ===== */
.modern-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
}

.modern-card-header {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}

.modern-index-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0;
}

.modern-card-body {
    padding: 0;
}

.modern-card-footer {
    padding: 14px 18px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    background: #fafbfc;
}

/* ===== TABLE ===== */
.modern-table { width: 100%; font-size: 13px; }
.modern-table th {
    text-align: left;
    padding: 10px 14px;
    color: var(--text-muted);
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--border);
    background: #fafbfc;
}
.modern-table td {
    padding: 10px 14px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    color: var(--text);
}
.modern-table tbody tr { transition: var(--transition); }
.modern-table tbody tr:hover td { background: #f8fafc; }

/* ===== BUTTONS ===== */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 12px;
    font-family: var(--font);
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    white-space: nowrap;
}
.btn-modern-primary {
    background: var(--primary);
    color: #fff;
    box-shadow: 0 1px 2px rgba(99,102,241,0.3);
}
.btn-modern-primary:hover {
    background: var(--primary-hover);
    box-shadow: 0 2px 6px rgba(99,102,241,0.4);
    color: #fff;
}
.btn-modern-outline {
    background: transparent;
    color: var(--text-muted);
    border: 1px solid var(--border);
}
.btn-modern-outline:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: var(--primary-light);
}
.btn-modern-ghost { background: transparent; color: var(--text-muted); padding: 7px 10px; }
.btn-modern-ghost:hover { color: var(--text-dark); background: #f3f4f6; }
.btn-modern-success { background: var(--success); color: #fff; }
.btn-modern-success:hover { background: #059669; color: #fff; }
.btn-modern-danger { background: var(--danger); color: #fff; }
.btn-modern-danger:hover { background: #dc2626; color: #fff; }
.btn-modern-sm { padding: 4px 8px; font-size: 11px; }
.btn-modern-warning { background: var(--warning); color: #fff; }
.btn-modern-warning:hover { background: #d97706; color: #fff; }

/* Action icon buttons */
.modern-btn-icon {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-sm);
    border: none;
    cursor: pointer;
    font-size: 12px;
    transition: var(--transition);
}
.modern-btn-view { background: var(--primary-light); color: var(--primary); }
.modern-btn-view:hover { background: var(--primary); color: #fff; }
.modern-btn-edit { background: var(--warning-light); color: var(--warning); }
.modern-btn-edit:hover { background: var(--warning); color: #fff; }
.modern-btn-delete { background: var(--danger-light); color: var(--danger); }
.modern-btn-delete:hover { background: var(--danger); color: #fff; }

/* ===== BADGES ===== */
.modern-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.modern-badge-blue { background: var(--primary-light); color: var(--primary); }
.modern-badge-green { background: var(--success-light); color: var(--success); }
.modern-badge-gold { background: var(--warning-light); color: var(--warning); }
.modern-badge-red { background: var(--danger-light); color: var(--danger); }
.modern-badge-light { background: #f3f4f6; color: var(--text-muted); }

/* ===== FORMS ===== */
.modern-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    padding: 18px;
}
.modern-form-span-2 { grid-column: span 2; }
.modern-form-group { display: flex; flex-direction: column; }
.modern-form-label {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 4px;
    font-size: 12px;
}
.modern-form-label small { font-weight: 400; color: var(--text-muted); font-size: 11px; }
.modern-required { color: var(--danger); font-weight: 700; }

.modern-input-wrapper { position: relative; }
.modern-input-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 12px;
    pointer-events: none;
    z-index: 1;
}
.modern-input-icon-textarea { top: 10px; transform: none; }

.modern-input {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 8px 10px 8px 32px;
    font-size: 13px;
    color: var(--text-dark);
    background: var(--card-bg);
    font-family: var(--font);
    transition: var(--transition);
}
.modern-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-light);
}
.modern-input::placeholder { color: #cbd5e1; }
.modern-input.is-invalid {
    border-color: var(--danger);
    box-shadow: 0 0 0 3px var(--danger-light);
}

.modern-textarea { resize: vertical; min-height: 72px; padding-left: 32px; }

.modern-select {
    appearance: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 8px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding-right: 28px;
}

.modern-form-error {
    display: block;
    color: var(--danger);
    font-size: 11px;
    margin-top: 3px;
    font-weight: 500;
}

.modern-input-hint {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 3px;
}

.modern-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 14px 18px;
    border-top: 1px solid var(--border);
    background: #fafbfc;
}

/* Auto-gen field */
.auto-gen-field { display: flex; flex-direction: column; gap: 4px; }
.auto-gen-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    background: #f0f0ff;
    border: 1px dashed var(--primary);
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-weight: 600;
    color: var(--primary);
}

/* ===== PAGINATION ===== */
.pagination { gap: 4px; }
.page-link {
    border-radius: var(--radius-sm) !important;
    font-size: 12px;
    padding: 6px 10px;
    border-color: var(--border);
    color: var(--text);
}
.page-item.active .page-link {
    background: var(--primary);
    border-color: var(--primary);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
    }
    .admin-sidebar.show {
        transform: translateX(0);
    }
    .admin-main { margin-left: 0; }
    .dash-stats { grid-template-columns: repeat(2, 1fr); }
    .dash-grid { grid-template-columns: 1fr; }
    .modern-form-grid { grid-template-columns: 1fr; }
    .modern-form-span-2 { grid-column: span 1; }
}

@media (max-width: 480px) {
    .dash-stats { grid-template-columns: 1fr; }
    .admin-content { padding: 12px; }
}

/* ===== DROPDOWN OVERRIDES ===== */
.dropdown-menu {
    font-size: 13px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-md);
    padding: 4px;
}
.dropdown-header {
    padding: 8px 12px;
    font-size: 12px;
}
.dropdown-header-name { font-weight: 600; color: var(--text-dark); }
.dropdown-header-email { font-size: 11px; color: var(--text-muted); }
.dropdown-item { border-radius: 4px; padding: 6px 12px; font-size: 13px; }
CSS_CONTENT;

file_put_contents(getcwd() . '/public/css/admin.css', $css);
echo "CSS written successfully!\n";
echo "File size: " . filesize(getcwd() . '/public/css/admin.css') . " bytes\n";
