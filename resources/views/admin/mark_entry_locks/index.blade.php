@extends('layouts.admin')
@section('title', 'Mark Entry Lock Management')

@push('styles')
<style>
/* ===== MARK ENTRY LOCKS - MODERN DESIGN SYSTEM ===== */
.mlock-page { animation: mlockFadeIn 0.4s ease-out; }
@keyframes mlockFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

/* ── Page Header ── */
.mlock-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem; }
.mlock-header-left { flex: 1; }
.mlock-title { font-size: 1.5rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.mlock-subtitle { font-size: 0.88rem; color: #6c757d; margin: 0.25rem 0 0; }

/* ── Breadcrumb ── */
.mlock-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.mlock-breadcrumb li { color: #adb5bd; }
.mlock-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.mlock-breadcrumb li a:hover { color: #4361ee; }
.mlock-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.mlock-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* ── Card Base (modern-card pattern) ── */
.mlock-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.25rem; }
.mlock-card-head { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: #fafbfc; }
.mlock-card-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.mlock-card-icon.blue { background: #eef2ff; color: #4361ee; }
.mlock-card-icon.green { background: #ecfdf5; color: #10b981; }
.mlock-card-icon.red { background: #fef2f2; color: #ef4444; }
.mlock-card-icon.amber { background: #fffbeb; color: #f59e0b; }
.mlock-card-icon.purple { background: #f5f3ff; color: #8b5cf6; }
.mlock-card-icon.teal { background: #f0fdfa; color: #14b8a6; }
.mlock-card-title { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.mlock-card-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.1rem 0 0; }
.mlock-card-body { padding: 1.25rem 1.5rem; }

/* ── Filter Panel (promo-filter-card pattern) ── */
.mlock-filter-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.25rem; }
.mlock-filter-header { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: #fafbfc; }
.mlock-filter-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; background: #eef2ff; color: #4361ee; }
.mlock-filter-title { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.mlock-filter-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.1rem 0 0; }
.mlock-filter-body { padding: 1.25rem 1.5rem; }
.mlock-filter-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.mlock-filter-group { display: flex; flex-direction: column; }
.mlock-filter-label { font-weight: 600; color: #374151; margin-bottom: 0.4rem; font-size: 0.85rem; }
.mlock-filter-select { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.6rem 2.2rem 0.6rem 0.8rem; font-size: 0.88rem; color: #1a1a2e; background: #fff; appearance: none; cursor: pointer; transition: all 0.2s; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.6rem center; background-repeat: no-repeat; background-size: 1.15rem; }
.mlock-filter-select:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.mlock-filter-select:disabled { opacity: 0.65; cursor: not-allowed; background-color: #f9fafb; }

/* ── Stat Cards (promo-stat-card pattern) ── */
.mlock-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 1.25rem; }
.mlock-stat-card { display: flex; flex-direction: column; padding: 16px 18px; background: #fff; border-radius: 14px; border: 1px solid #e5e7eb; transition: all 0.2s ease; cursor: default; position: relative; overflow: hidden; }
.mlock-stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-color: transparent; }
.mlock-stat-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.mlock-stat-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 15px; }
.mlock-stat-value { font-size: 1.5rem; font-weight: 800; line-height: 1.2; }
.mlock-stat-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }

.mlock-stat-green .mlock-stat-icon { background: rgba(16,185,129,0.12); color: #10b981; }
.mlock-stat-green .mlock-stat-value { color: #10b981; }
.mlock-stat-green .mlock-stat-label { color: #34d399; }
.mlock-stat-green:hover { background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(16,185,129,0.03)); }

.mlock-stat-red .mlock-stat-icon { background: rgba(239,68,68,0.12); color: #ef4444; }
.mlock-stat-red .mlock-stat-value { color: #ef4444; }
.mlock-stat-red .mlock-stat-label { color: #f87171; }
.mlock-stat-red:hover { background: linear-gradient(135deg, rgba(239,68,68,0.08), rgba(239,68,68,0.03)); }

.mlock-stat-amber .mlock-stat-icon { background: rgba(245,158,11,0.12); color: #f59e0b; }
.mlock-stat-amber .mlock-stat-value { color: #f59e0b; }
.mlock-stat-amber .mlock-stat-label { color: #fbbf24; }
.mlock-stat-amber:hover { background: linear-gradient(135deg, rgba(245,158,11,0.08), rgba(245,158,11,0.03)); }

.mlock-stat-teal .mlock-stat-icon { background: rgba(20,184,166,0.12); color: #14b8a6; }
.mlock-stat-teal .mlock-stat-value { color: #14b8a6; }
.mlock-stat-teal .mlock-stat-label { color: #2dd4bf; }
.mlock-stat-teal:hover { background: linear-gradient(135deg, rgba(20,184,166,0.08), rgba(20,184,166,0.03)); }

/* ── Info Alert ── */
.mlock-alert { border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.25rem; display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.88rem; line-height: 1.55; }
.mlock-alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
.mlock-alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; }
.mlock-alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
.mlock-alert i { font-size: 1.15rem; margin-top: 0.1rem; flex-shrink: 0; }
.mlock-alert-close { margin-left: auto; background: none; border: none; cursor: pointer; color: inherit; opacity: 0.5; transition: opacity 0.2s; flex-shrink: 0; font-size: 0.85rem; }
.mlock-alert-close:hover { opacity: 1; }

/* ── Lock Status Grid ── */
.mlock-status-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(370px, 1fr)); gap: 1.25rem; }

/* ── Status Card ── */
.mlock-status-card { background: #fff; border-radius: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden; transition: box-shadow 0.25s, transform 0.25s; position: relative; }
.mlock-status-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.08); transform: translateY(-2px); }

/* Current term highlight */
.mlock-status-card.mlock-current { border: 2px solid #4361ee; box-shadow: 0 4px 16px rgba(67,97,238,0.15); }
.mlock-status-card.mlock-current::before { content: 'CURRENT TERM'; position: absolute; top: 12px; right: -28px; background: #4361ee; color: #fff; font-size: 0.6rem; font-weight: 800; letter-spacing: 0.8px; padding: 3px 32px; transform: rotate(45deg); z-index: 2; }

/* Status card header */
.mlock-status-head { padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; }
.mlock-status-head.locked { background: linear-gradient(135deg, #fef2f2, #fee2e2); border-bottom: 1px solid #fecaca; }
.mlock-status-head.unlocked { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-bottom: 1px solid #a7f3d0; }
.mlock-status-head.current-locked { background: linear-gradient(135deg, #fef2f2, #fee2e2); border-bottom: 2px solid #ef4444; }
.mlock-status-head.current-unlocked { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-bottom: 2px solid #10b981; }

.mlock-status-name { font-size: 1.05rem; font-weight: 700; color: #1a1a2e; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.mlock-status-dates { font-size: 0.78rem; color: #6b7280; margin-top: 0.15rem; }
.mlock-status-branch { font-size: 0.75rem; color: #8b5cf6; font-weight: 600; margin-top: 0.1rem; display: inline-flex; align-items: center; gap: 0.25rem; }

/* Lock Badge */
.mlock-badge { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.3rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; white-space: nowrap; }
.mlock-badge.locked { background: #fee2e2; color: #dc2626; }
.mlock-badge.unlocked { background: #d1fae5; color: #059669; }
.mlock-badge i { font-size: 0.85rem; }

/* Status card body */
.mlock-status-body { padding: 1rem 1.25rem; }
.mlock-detail { display: flex; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.6rem; font-size: 0.85rem; color: #4b5563; }
.mlock-detail:last-child { margin-bottom: 0; }
.mlock-detail-label { font-weight: 600; color: #374151; min-width: 90px; flex-shrink: 0; }
.mlock-detail-value { color: #6b7280; }
.mlock-detail-value.empty { font-style: italic; color: #9ca3af; }
.mlock-detail-value.reason { background: #f9fafb; padding: 0.3rem 0.6rem; border-radius: 6px; border: 1px solid #f0f0f0; font-size: 0.82rem; line-height: 1.4; }

/* Status card action footer */
.mlock-status-action { padding: 0.85rem 1.25rem; border-top: 1px solid #f0f0f0; background: #fafbfc; display: flex; justify-content: flex-end; }

/* ── Buttons ── */
.mlock-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.55rem 1.15rem; border-radius: 10px; font-weight: 600; font-size: 0.85rem; border: none; cursor: pointer; transition: all 0.25s; text-decoration: none; }
.mlock-btn-lock { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; box-shadow: 0 2px 8px rgba(239,68,68,0.3); }
.mlock-btn-lock:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(239,68,68,0.4); color: #fff; }
.mlock-btn-unlock { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 2px 8px rgba(16,185,129,0.3); }
.mlock-btn-unlock:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(16,185,129,0.4); color: #fff; }
.mlock-btn-outline { background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb; box-shadow: none; }
.mlock-btn-outline:hover { border-color: #4361ee; color: #4361ee; background: #f8f9ff; }
.mlock-btn-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.3); }
.mlock-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.4); color: #fff; }
.mlock-btn-ghost { background: transparent; color: #6b7280; padding: 0.55rem 0.9rem; }
.mlock-btn-ghost:hover { color: #1a1a2e; background: #f3f4f6; }

/* ── History Table ── */
.mlock-table-wrap { overflow-x: auto; }
.mlock-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.mlock-table thead th { padding: 0.75rem 1rem; background: #f9fafb; border-bottom: 2px solid #e5e7eb; font-weight: 700; color: #374151; text-align: left; white-space: nowrap; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; }
.mlock-table tbody td { padding: 0.7rem 1rem; border-bottom: 1px solid #f0f0f0; color: #4b5563; vertical-align: middle; }
.mlock-table tbody tr:hover { background: #f8f9ff; }
.mlock-table tbody tr:nth-child(even) { background: #fafbfc; }
.mlock-table tbody tr:nth-child(even):hover { background: #f0f4ff; }
.mlock-table .action-locked { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.6rem; border-radius: 6px; background: #fee2e2; color: #dc2626; font-weight: 600; font-size: 0.78rem; }
.mlock-table .action-unlocked { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.6rem; border-radius: 6px; background: #d1fae5; color: #059669; font-weight: 600; font-size: 0.78rem; }

/* ── Empty State ── */
.mlock-empty { text-align: center; padding: 3rem 1.5rem; }
.mlock-empty i { font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
.mlock-empty p { color: #9ca3af; font-size: 0.95rem; margin: 0; }
.mlock-empty .sub { font-size: 0.82rem; margin-top: 0.5rem; color: #b0b8c4; }

/* ── Modal Enhancements ── */
.mlock-modal-icon { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1rem; }
.mlock-modal-icon.lock { background: #fee2e2; color: #dc2626; }
.mlock-modal-icon.unlock { background: #d1fae5; color: #059669; }
.mlock-modal-title { font-size: 1.15rem; font-weight: 700; color: #1a1a2e; text-align: center; margin-bottom: 0.5rem; }
.mlock-modal-desc { font-size: 0.9rem; color: #6b7280; text-align: center; margin-bottom: 1.25rem; }
.mlock-modal-desc strong { color: #1a1a2e; }
.mlock-textarea { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1a1a2e; resize: vertical; min-height: 80px; transition: all 0.2s; }
.mlock-textarea:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.mlock-textarea::placeholder { color: #9ca3af; }
.mlock-modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.25rem; }

/* ── Branch Principal Badge ── */
.mlock-role-badge { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.7rem; border-radius: 8px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.3px; text-transform: uppercase; background: #f5f3ff; color: #8b5cf6; border: 1px solid #e9e5ff; }

/* ── Responsive ── */
@media (max-width: 992px) {
    .mlock-status-grid { grid-template-columns: 1fr; }
    .mlock-filter-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .mlock-header { flex-direction: column; align-items: stretch; }
    .mlock-title { font-size: 1.25rem; }
    .mlock-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .mlock-filter-grid { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
    .mlock-stats-grid { grid-template-columns: 1fr 1fr; }
    .mlock-status-head { flex-direction: column; align-items: flex-start; }
    .mlock-status-card.mlock-current::before { display: none; }
}
</style>
@endpush

@section('content')
<div class="mlock-page">
    {{-- ════════════════ PAGE HEADER ════════════════ --}}
    <div class="mlock-header">
        <div class="mlock-header-left">
            <nav aria-label="breadcrumb" class="mlock-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Academics</a></li>
                    <li class="active">Mark Entry Locks</li>
                </ol>
            </nav>
            <h1 class="mlock-title">Mark Entry Lock Management</h1>
            <p class="mlock-subtitle">Control mark entry access for each term across branches and academic years</p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            @if(isset($userBranch) && $userBranch)
                <span class="mlock-role-badge">
                    <i class="fas fa-building"></i> {{ $userBranch->name }}
                </span>
            @endif
        </div>
    </div>

    {{-- ════════════════ SESSION FLASH MESSAGES ════════════════ --}}
    @if(session('success'))
        <div class="mlock-alert mlock-alert-success">
            <i class="fas fa-check-circle"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="mlock-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>
    @endif
    @if(session('error'))
        <div class="mlock-alert mlock-alert-warning" style="background:#fef2f2;border-color:#fecaca;color:#991b1b;">
            <i class="fas fa-exclamation-circle"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="mlock-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>
    @endif
    @if(session('info'))
        <div class="mlock-alert mlock-alert-info">
            <i class="fas fa-info-circle"></i>
            <div>{{ session('info') }}</div>
            <button type="button" class="mlock-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>
    @endif

    {{-- ════════════════ INFO ALERT ════════════════ --}}
    <div class="mlock-alert mlock-alert-info">
        <i class="bi bi-shield-lock"></i>
        <div>
            <strong>How it works:</strong> Lock mark entry to prevent teachers from editing marks for a specific term. When locked, only teachers with special <a href="{{ route('admin.mark-entry-permissions.index') }}" style="color:#1e40af;font-weight:600;">edit permissions</a> can modify marks. You must provide a reason for each lock/unlock action.
        </div>
    </div>

    {{-- ════════════════ FILTER PANEL ════════════════ --}}
    <div class="mlock-filter-card" id="filterPanel">
        <div class="mlock-filter-header">
            <div class="mlock-filter-icon"><i class="fas fa-filter"></i></div>
            <div>
                <h3 class="mlock-filter-title">Select Branch, Year & Term</h3>
                <p class="mlock-filter-desc">Choose a branch, academic year, and term to view or manage lock statuses</p>
            </div>
            <div style="margin-left:auto;">
                <a href="{{ route('admin.mark-entry-locks.index') }}" class="mlock-btn mlock-btn-ghost" style="font-size:0.82rem;padding:0.4rem 0.85rem;">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
        </div>
        <div class="mlock-filter-body">
            <form method="GET" action="{{ route('admin.mark-entry-locks.index') }}" id="mlockFilterForm">
                <div class="mlock-filter-grid">
                    <div class="mlock-filter-group">
                        <label class="mlock-filter-label" for="filterBranch">
                            <i class="fas fa-building" style="margin-right:4px;color:#8b5cf6;font-size:0.78rem;"></i> Branch
                        </label>
                        @if(isset($userBranch) && $userBranch)
                            <select name="branch_id" id="filterBranch" class="mlock-filter-select" disabled>
                                <option value="{{ $userBranch->id }}" selected>{{ $userBranch->name }}</option>
                            </select>
                            <input type="hidden" name="branch_id" value="{{ $userBranch->id }}">
                        @else
                            <select name="branch_id" id="filterBranch" class="mlock-filter-select">
                                <option value="">-- All Branches --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="mlock-filter-group">
                        <label class="mlock-filter-label" for="filterAy">
                            <i class="fas fa-calendar-alt" style="margin-right:4px;color:#4361ee;font-size:0.78rem;"></i> Academic Year
                        </label>
                        <select name="academic_year_id" id="filterAy" class="mlock-filter-select">
                            <option value="">-- Select Academic Year --</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ request('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mlock-filter-group">
                        <label class="mlock-filter-label" for="filterTerm">
                            <i class="fas fa-list-ol" style="margin-right:4px;color:#10b981;font-size:0.78rem;"></i> Term
                        </label>
                        <select name="term_id" id="filterTerm" class="mlock-filter-select">
                            <option value="">-- All Terms --</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="margin-top:1rem;display:flex;gap:0.75rem;">
                    <button type="submit" class="mlock-btn mlock-btn-primary" style="font-size:0.85rem;padding:0.55rem 1.25rem;">
                        <i class="fas fa-search"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ════════════════ STAT SUMMARY ════════════════ --}}
    @php
        $totalTerms = $locks->count();
        $lockedCount = $locks->where('is_locked', true)->count();
        $unlockedCount = $totalTerms - $lockedCount;
        $currentTermId = optional($terms->firstWhere('is_current', true))->id ?? null;
        $currentTermLocked = $locks->firstWhere('term_id', $currentTermId) && $locks->firstWhere('term_id', $currentTermId)->is_locked;
    @endphp
    <div class="mlock-stats-grid">
        <div class="mlock-stat-card mlock-stat-teal">
            <div class="mlock-stat-top">
                <div class="mlock-stat-icon"><i class="fas fa-list-alt"></i></div>
            </div>
            <div class="mlock-stat-value">{{ $totalTerms }}</div>
            <div class="mlock-stat-label">Total Term Locks</div>
        </div>
        <div class="mlock-stat-card mlock-stat-red">
            <div class="mlock-stat-top">
                <div class="mlock-stat-icon"><i class="fas fa-lock"></i></div>
            </div>
            <div class="mlock-stat-value">{{ $lockedCount }}</div>
            <div class="mlock-stat-label">Locked</div>
        </div>
        <div class="mlock-stat-card mlock-stat-green">
            <div class="mlock-stat-top">
                <div class="mlock-stat-icon"><i class="fas fa-lock-open"></i></div>
            </div>
            <div class="mlock-stat-value">{{ $unlockedCount }}</div>
            <div class="mlock-stat-label">Unlocked</div>
        </div>
        <div class="mlock-stat-card {{ $currentTermLocked ? 'mlock-stat-red' : 'mlock-stat-green' }}">
            <div class="mlock-stat-top">
                <div class="mlock-stat-icon"><i class="fas fa-star"></i></div>
            </div>
            <div class="mlock-stat-value">{{ $currentTermLocked ? 'Locked' : 'Open' }}</div>
            <div class="mlock-stat-label">Current Term</div>
        </div>
    </div>

    {{-- ════════════════ LOCK STATUS GRID ════════════════ --}}
    <div class="mlock-card">
        <div class="mlock-card-head">
            <div class="mlock-card-icon purple"><i class="bi bi-shield-lock"></i></div>
            <div>
                <h3 class="mlock-card-title">Lock Status by Term</h3>
                <p class="mlock-card-desc">Overview of lock status for each term, academic year, and branch combination</p>
            </div>
        </div>
        <div class="mlock-card-body">
            @if($locks->count() > 0)
                <div class="mlock-status-grid">
                    @foreach($locks as $lock)
                        @php
                            $isLocked = $lock->is_locked;
                            $isCurrentTerm = $lock->term_id == $currentTermId;
                            $termModel = $lock->term;
                            $branchModel = $lock->branch;
                            $ayModel = $lock->academicYear;
                        @endphp
                        <div class="mlock-status-card {{ $isCurrentTerm ? 'mlock-current' : '' }}">
                            <div class="mlock-status-head {{ $isLocked ? ($isCurrentTerm ? 'current-locked' : 'locked') : ($isCurrentTerm ? 'current-unlocked' : 'unlocked') }}">
                                <div>
                                    <h4 class="mlock-status-name">
                                        <i class="bi {{ $isLocked ? 'bi-lock-fill' : 'bi-unlock-fill' }}" style="color: {{ $isLocked ? '#dc2626' : '#059669' }}"></i>
                                        {{ $termModel->name ?? 'Unknown Term' }}
                                        @if($isCurrentTerm)
                                            <span style="background:#4361ee;color:#fff;font-size:0.6rem;padding:2px 6px;border-radius:4px;font-weight:800;letter-spacing:0.5px;vertical-align:middle;">CURRENT</span>
                                        @endif
                                    </h4>
                                    <div class="mlock-status-dates">
                                        @if($termModel && $termModel->start_date && $termModel->end_date)
                                            {{ \Carbon\Carbon::parse($termModel->start_date)->format('M d, Y') }} &mdash; {{ \Carbon\Carbon::parse($termModel->end_date)->format('M d, Y') }}
                                        @else
                                            Dates not set
                                        @endif
                                    </div>
                                    @if($branchModel)
                                        <div class="mlock-status-branch">
                                            <i class="fas fa-building" style="font-size:0.65rem;"></i> {{ $branchModel->name }}
                                        </div>
                                    @endif
                                </div>
                                <span class="mlock-badge {{ $isLocked ? 'locked' : 'unlocked' }}">
                                    <i class="bi {{ $isLocked ? 'bi-lock-fill' : 'bi-unlock-fill' }}"></i>
                                    {{ $isLocked ? 'LOCKED' : 'UNLOCKED' }}
                                </span>
                            </div>
                            <div class="mlock-status-body">
                                @if($isLocked)
                                    <div class="mlock-detail">
                                        <span class="mlock-detail-label">Academic Year:</span>
                                        <span class="mlock-detail-value">{{ $ayModel->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="mlock-detail">
                                        <span class="mlock-detail-label">Locked By:</span>
                                        <span class="mlock-detail-value">{{ $lock->lockedBy->name ?? ($lock->locked_by_name ?? 'N/A') }}</span>
                                    </div>
                                    <div class="mlock-detail">
                                        <span class="mlock-detail-label">Locked At:</span>
                                        <span class="mlock-detail-value">{{ $lock->locked_at ? $lock->locked_at->format('M d, Y h:i A') : 'N/A' }}</span>
                                    </div>
                                    <div class="mlock-detail">
                                        <span class="mlock-detail-label">Reason:</span>
                                        <span class="mlock-detail-value reason {{ !$lock->lock_reason ? 'empty' : '' }}">{{ $lock->lock_reason ?? 'No reason provided' }}</span>
                                    </div>
                                @else
                                    <div class="mlock-detail">
                                        <span class="mlock-detail-label">Academic Year:</span>
                                        <span class="mlock-detail-value">{{ $ayModel->name ?? 'N/A' }}</span>
                                    </div>
                                    @if($lock->unlocked_by)
                                        <div class="mlock-detail">
                                            <span class="mlock-detail-label">Unlocked By:</span>
                                            <span class="mlock-detail-value">{{ $lock->unlockedBy->name ?? ($lock->unlocked_by_name ?? 'N/A') }}</span>
                                        </div>
                                        <div class="mlock-detail">
                                            <span class="mlock-detail-label">Unlocked At:</span>
                                            <span class="mlock-detail-value">{{ $lock->unlocked_at ? $lock->unlocked_at->format('M d, Y h:i A') : 'N/A' }}</span>
                                        </div>
                                        @if($lock->unlock_reason)
                                            <div class="mlock-detail">
                                                <span class="mlock-detail-label">Reason:</span>
                                                <span class="mlock-detail-value reason">{{ $lock->unlock_reason }}</span>
                                            </div>
                                        @endif
                                    @elseif($lock->exists)
                                        <div class="mlock-detail">
                                            <span class="mlock-detail-label">Status:</span>
                                            <span class="mlock-detail-value empty">No lock record &mdash; marks are open for entry</span>
                                        </div>
                                    @else
                                        <div class="mlock-detail">
                                            <span class="mlock-detail-label">Status:</span>
                                            <span class="mlock-detail-value empty">No lock record found &mdash; marks are open for entry</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <div class="mlock-status-action">
                                @if($isLocked)
                                    <button type="button" class="mlock-btn mlock-btn-unlock"
                                            onclick="openUnlockModal({{ $lock->term_id }}, '{{ addslashes($termModel->name ?? '') }}', {{ $lock->branch_id }}, {{ $lock->academic_year_id }})">
                                        <i class="bi bi-unlock-fill"></i> Unlock Mark Entry
                                    </button>
                                @else
                                    <button type="button" class="mlock-btn mlock-btn-lock"
                                            onclick="openLockModal({{ $lock->term_id }}, '{{ addslashes($termModel->name ?? '') }}', {{ $lock->branch_id }}, {{ $lock->academic_year_id }})">
                                        <i class="bi bi-lock-fill"></i> Lock Mark Entry
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mlock-empty">
                    <i class="bi bi-shield-lock"></i>
                    <p>No lock records found for the selected filters.</p>
                    <p class="sub">Please select a branch, academic year, and term above to view lock statuses.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ════════════════ LOCK HISTORY TABLE ════════════════ --}}
    <div class="mlock-card">
        <div class="mlock-card-head">
            <div class="mlock-card-icon amber"><i class="bi bi-clock-history"></i></div>
            <div>
                <h3 class="mlock-card-title">Lock History</h3>
                <p class="mlock-card-desc">Chronological record of all lock and unlock actions for the selected filters</p>
            </div>
        </div>
        <div class="mlock-card-body" style="padding: 0;">
            @if($locks->count() > 0)
                <div class="mlock-table-wrap">
                    <table class="mlock-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Branch</th>
                                <th>Academic Year</th>
                                <th>Term</th>
                                <th>Action</th>
                                <th>By Whom</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locks->sortByDesc('updated_at') as $lockRecord)
                                @php
                                    $latestAction = '';
                                    $latestUser = '';
                                    $latestReason = '';
                                    $latestDate = '';
                                    if ($lockRecord->is_locked) {
                                        $latestAction = 'Locked';
                                        $latestUser = $lockRecord->lockedBy->name ?? ($lockRecord->locked_by_name ?? 'N/A');
                                        $latestReason = $lockRecord->lock_reason ?? '-';
                                        $latestDate = $lockRecord->locked_at ? $lockRecord->locked_at->format('M d, Y h:i A') : '-';
                                    } else {
                                        $latestAction = 'Unlocked';
                                        $latestUser = $lockRecord->unlockedBy->name ?? ($lockRecord->unlocked_by_name ?? ($lockRecord->lockedBy->name ?? 'N/A'));
                                        $latestReason = $lockRecord->unlock_reason ?? $lockRecord->lock_reason ?? '-';
                                        $latestDate = $lockRecord->unlocked_at ? $lockRecord->unlocked_at->format('M d, Y h:i A') : ($lockRecord->locked_at ? $lockRecord->locked_at->format('M d, Y h:i A') : '-');
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $latestDate }}</td>
                                    <td>{{ $lockRecord->branch->name ?? 'N/A' }}</td>
                                    <td>{{ $lockRecord->academicYear->name ?? 'N/A' }}</td>
                                    <td>{{ $lockRecord->term->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($lockRecord->is_locked)
                                            <span class="action-locked">
                                                <i class="bi bi-lock-fill"></i> Locked
                                            </span>
                                        @else
                                            <span class="action-unlocked">
                                                <i class="bi bi-unlock-fill"></i> Unlocked
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $latestUser }}</td>
                                    <td>{{ $latestReason }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="mlock-empty">
                    <i class="bi bi-clock-history"></i>
                    <p>No lock history records found.</p>
                    <p class="sub">Lock and unlock actions will appear here once recorded.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ════════════════ RANK PUBLISHING SECTION ════════════════ --}}
    <div class="mlock-card" style="margin-top:1.5rem;">
        <div class="mlock-card-head">
            <div class="mlock-card-icon green"><i class="fas fa-trophy"></i></div>
            <div>
                <h3 class="mlock-card-title">Rank Publishing</h3>
                <p class="mlock-card-desc">Control when class ranks are visible to students and parents. Ranks are hidden by default until you publish them.</p>
            </div>
        </div>
        <div class="mlock-card-body" style="padding:1.25rem 1.5rem;">
            @if(session('success'))
                <div class="mlock-alert mlock-alert-success" style="margin-bottom:1rem;">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
            @endif
            <div class="mlock-table-wrap" style="overflow-x:auto;">
                <table class="mlock-table" style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            <th style="padding:0.65rem 1rem;text-align:left;border-bottom:2px solid #e5e7eb;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px;color:#6b7280;">Academic Year</th>
                            <th style="padding:0.65rem 1rem;text-align:left;border-bottom:2px solid #e5e7eb;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px;color:#6b7280;">Term</th>
                            <th style="padding:0.65rem 1rem;text-align:center;border-bottom:2px solid #e5e7eb;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px;color:#6b7280;">Rank Status</th>
                            <th style="padding:0.65rem 1rem;text-align:left;border-bottom:2px solid #e5e7eb;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px;color:#6b7280;">Published By</th>
                            <th style="padding:0.65rem 1rem;text-align:left;border-bottom:2px solid #e5e7eb;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px;color:#6b7280;">Published At</th>
                            <th style="padding:0.65rem 1rem;text-align:right;border-bottom:2px solid #e5e7eb;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px;color:#6b7280;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $allTerms = \App\Models\Term::with('academicYear')->orderBy('academic_year_id','desc')->orderBy('term_number')->get();
                        @endphp
                        @foreach($allTerms as $term)
                        <tr>
                            <td style="padding:0.6rem 1rem;border-bottom:1px solid #f3f4f6;">{{ $term->academicYear->name ?? 'N/A' }}</td>
                            <td style="padding:0.6rem 1rem;border-bottom:1px solid #f3f4f6;font-weight:600;">{{ $term->name }}</td>
                            <td style="padding:0.6rem 1rem;border-bottom:1px solid #f3f4f6;text-align:center;">
                                @if($term->ranks_published)
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:50px;background:#d1fae5;color:#065f46;font-size:0.72rem;font-weight:700;">
                                        <i class="fas fa-eye"></i> Published
                                    </span>
                                @else
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:50px;background:#fef3c7;color:#92400e;font-size:0.72rem;font-weight:700;">
                                        <i class="fas fa-eye-slash"></i> Hidden
                                    </span>
                                @endif
                            </td>
                            <td style="padding:0.6rem 1rem;border-bottom:1px solid #f3f4f6;">
                                @if($term->ranks_published_by)
                                    {{ \App\Models\User::find($term->ranks_published_by)?->name ?? 'N/A' }}
                                @else
                                    <span style="color:#9ca3af;">—</span>
                                @endif
                            </td>
                            <td style="padding:0.6rem 1rem;border-bottom:1px solid #f3f4f6;">
                                @if($term->ranks_published_at)
                                    {{ $term->ranks_published_at->format('M d, Y h:i A') }}
                                @else
                                    <span style="color:#9ca3af;">—</span>
                                @endif
                            </td>
                            <td style="padding:0.6rem 1rem;border-bottom:1px solid #f3f4f6;text-align:right;">
                                @if($term->ranks_published)
                                    <form method="POST" action="{{ route('admin.mark-entry-locks.unpublish-ranks') }}" style="display:inline;" onsubmit="return confirm('Hide ranks for {{ $term->name }}? Students and parents will no longer see class ranks.')">
                                        @csrf
                                        <input type="hidden" name="term_id" value="{{ $term->id }}">
                                        <button type="submit" style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;border-radius:8px;border:1px solid #fde68a;background:#fffbeb;color:#92400e;font-size:0.75rem;font-weight:600;cursor:pointer;">
                                            <i class="fas fa-eye-slash"></i> Hide Ranks
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.mark-entry-locks.publish-ranks') }}" style="display:inline;" onsubmit="return confirm('Publish ranks for {{ $term->name }}? Students and parents will be able to see class ranks.')">
                                        @csrf
                                        <input type="hidden" name="term_id" value="{{ $term->id }}">
                                        <button type="submit" style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;border-radius:8px;border:1px solid #a7f3d0;background:#ecfdf5;color:#065f46;font-size:0.75rem;font-weight:600;cursor:pointer;">
                                            <i class="fas fa-eye"></i> Publish Ranks
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════ LOCK CONFIRMATION MODAL ════════════════ --}}
<div class="modal fade" id="lockModal" tabindex="-1" aria-labelledby="lockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="modal-body" style="padding: 2rem 1.75rem 1.5rem;">
                <div class="mlock-modal-icon lock">
                    <i class="bi bi-lock-fill"></i>
                </div>
                <h5 class="mlock-modal-title" id="lockModalLabel">Confirm Lock Mark Entry</h5>
                <p class="mlock-modal-desc">
                    Are you sure you want to <strong style="color:#dc2626;">lock</strong> mark entry for <strong id="lockTermName"></strong>?<br>
                    <span style="font-size:0.82rem;color:#9ca3af;">Teachers will not be able to edit marks unless they have special permission.</span>
                </p>
                <form id="lockForm" method="POST" action="{{ route('admin.mark-entry-locks.lock') }}">
                    @csrf
                    <input type="hidden" name="branch_id" id="lockBranchId" value="">
                    <input type="hidden" name="academic_year_id" id="lockAyId" value="">
                    <input type="hidden" name="term_id" id="lockTermId" value="">
                    <div class="mb-3">
                        <label class="mlock-filter-label" style="margin-bottom: 0.5rem;">
                            Reason for locking <span style="color: #ef4444;">*</span>
                        </label>
                        <textarea name="lock_reason" class="mlock-textarea" placeholder="Enter reason for locking mark entry (e.g., Report card generation in progress, final grades approved)..." required></textarea>
                    </div>
                    <div class="mlock-modal-footer">
                        <button type="button" class="mlock-btn mlock-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="mlock-btn mlock-btn-lock">
                            <i class="bi bi-lock-fill"></i> Confirm Lock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════ UNLOCK CONFIRMATION MODAL ════════════════ --}}
<div class="modal fade" id="unlockModal" tabindex="-1" aria-labelledby="unlockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="modal-body" style="padding: 2rem 1.75rem 1.5rem;">
                <div class="mlock-modal-icon unlock">
                    <i class="bi bi-unlock-fill"></i>
                </div>
                <h5 class="mlock-modal-title" id="unlockModalLabel">Confirm Unlock Mark Entry</h5>
                <p class="mlock-modal-desc">
                    Are you sure you want to <strong style="color:#059669;">unlock</strong> mark entry for <strong id="unlockTermName"></strong>?<br>
                    <span style="font-size:0.82rem;color:#9ca3af;">Teachers will be able to edit marks for this term.</span>
                </p>
                <form id="unlockForm" method="POST" action="{{ route('admin.mark-entry-locks.unlock') }}">
                    @csrf
                    <input type="hidden" name="branch_id" id="unlockBranchId" value="">
                    <input type="hidden" name="academic_year_id" id="unlockAyId" value="">
                    <input type="hidden" name="term_id" id="unlockTermId" value="">
                    <div class="mb-3">
                        <label class="mlock-filter-label" style="margin-bottom: 0.5rem;">
                            Reason for unlocking <span style="color: #ef4444;">*</span>
                        </label>
                        <textarea name="unlock_reason" class="mlock-textarea" placeholder="Enter reason for unlocking mark entry (e.g., Corrections needed, additional marks to enter)..." required></textarea>
                    </div>
                    <div class="mlock-modal-footer">
                        <button type="button" class="mlock-btn mlock-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="mlock-btn mlock-btn-unlock">
                            <i class="bi bi-unlock-fill"></i> Confirm Unlock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    const filterBranch = document.getElementById('filterBranch');
    const filterAy = document.getElementById('filterAy');
    const filterTerm = document.getElementById('filterTerm');
    let lockModal = null;
    let unlockModal = null;

    // Initialize Bootstrap modals
    document.addEventListener('DOMContentLoaded', function() {
        const lockModalEl = document.getElementById('lockModal');
        const unlockModalEl = document.getElementById('unlockModal');
        if (lockModalEl) lockModal = new bootstrap.Modal(lockModalEl);
        if (unlockModalEl) unlockModal = new bootstrap.Modal(unlockModalEl);
    });

    // ── CASCADE: Branch → Academic Year ──
    if (filterBranch && !filterBranch.disabled) {
        filterBranch.addEventListener('change', function() {
            const branchId = this.value;

            // Reset academic year and term dropdowns
            filterAy.innerHTML = '<option value="">-- Select Academic Year --</option>';
            filterTerm.innerHTML = '<option value="">-- All Terms --</option>';

            if (!branchId) {
                // Restore all academic years when no branch is selected
                @foreach($academicYears as $ay)
                filterAy.innerHTML += '<option value="{{ $ay->id }}">{{ $ay->name }}</option>';
                @endforeach
                return;
            }

            // Fetch academic years for the selected branch via AJAX
            fetch('{{ route("admin.mark-entry-locks.index") }}?branch_id=' + encodeURIComponent(branchId), {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(r) {
                if (!r.ok) throw new Error('Network error');
                return r.json();
            })
            .then(function(data) {
                filterAy.innerHTML = '<option value="">-- Select Academic Year --</option>';
                if (data.academic_years) {
                    data.academic_years.forEach(function(ay) {
                        const opt = document.createElement('option');
                        opt.value = ay.id;
                        opt.textContent = ay.name;
                        filterAy.appendChild(opt);
                    });
                }
            })
            .catch(function() {
                // Fallback: reload page with branch filter
                const url = new URL(window.location.href);
                url.searchParams.set('branch_id', branchId);
                url.searchParams.delete('academic_year_id');
                url.searchParams.delete('term_id');
                window.location.href = url.toString();
            });
        });
    }

    // ── CASCADE: Academic Year → Term ──
    if (filterAy) {
        filterAy.addEventListener('change', function() {
            const ayId = this.value;
            const branchId = filterBranch ? filterBranch.value : '';

            // Reset term dropdown
            filterTerm.innerHTML = '<option value="">-- All Terms --</option>';

            if (!ayId) return;

            // Fetch terms for the selected academic year
            fetch('{{ route("admin.terms.index", []) }}?academic_year_id=' + encodeURIComponent(ayId), {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(r) {
                if (!r.ok) throw new Error('Network error');
                return r.json();
            })
            .then(function(data) {
                const terms = Array.isArray(data) ? data : (data.data || []);
                terms.forEach(function(t) {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = t.name;
                    filterTerm.appendChild(opt);
                });
            })
            .catch(function() {
                // Silently fail — user can still submit the form
            });
        });
    }

    // ── LOCK MODAL ──
    window.openLockModal = function(termId, termName, branchId, ayId) {
        document.getElementById('lockTermId').value = termId;
        document.getElementById('lockTermName').textContent = termName;
        document.getElementById('lockBranchId').value = branchId || (filterBranch ? filterBranch.value : '');
        document.getElementById('lockAyId').value = ayId || (filterAy ? filterAy.value : '');
        // Reset textarea
        const form = document.getElementById('lockForm');
        form.querySelector('textarea').value = '';
        if (lockModal) lockModal.show();
    };

    // ── UNLOCK MODAL ──
    window.openUnlockModal = function(termId, termName, branchId, ayId) {
        document.getElementById('unlockTermId').value = termId;
        document.getElementById('unlockTermName').textContent = termName;
        document.getElementById('unlockBranchId').value = branchId || (filterBranch ? filterBranch.value : '');
        document.getElementById('unlockAyId').value = ayId || (filterAy ? filterAy.value : '');
        // Reset textarea
        const form = document.getElementById('unlockForm');
        form.querySelector('textarea').value = '';
        if (unlockModal) unlockModal.show();
    };
})();
</script>
@endpush
