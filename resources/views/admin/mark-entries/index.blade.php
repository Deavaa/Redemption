@extends('layouts.admin')
@section('title', 'Mark Entry')

@push('styles')
<style>
/* ===== MARK ENTRY INDEX - MOBILE-FIRST ULTRA-COMPACT ===== */
/* AGGRESSIVE viewport containment — nothing escapes the screen */
*, *::before, *::after { box-sizing: border-box !important; }
.admin-content {
    padding-top: 0 !important; padding-left: 8px !important; padding-right: 8px !important;
    padding-bottom: 8px !important; margin-top: 0 !important;
    overflow-x: hidden !important; max-width: 100vw !important; box-sizing: border-box !important;
    width: 100% !important;
}
.me-page {
    margin: 0; padding: 0; width: 100%; max-width: 100%;
    overflow-x: hidden; box-sizing: border-box;
}
.me-page *, .me-page *::before, .me-page *::after {
    max-width: 100% !important; box-sizing: border-box !important;
}
.me-page > *, .me-page > div,
.me-page .me-filter-card, .me-page .me-filter-summary,
.me-page .me-global-status, .me-page .me-carousel-nav,
.me-page .me-cards-container, .me-page .me-mark-entry-area,
.me-page .me-lock-banner, .me-page .me-empty, .me-page .me-loading {
    max-width: 100% !important; box-sizing: border-box !important;
    overflow-x: hidden !important;
}

/* Filter Panel */
.me-filter-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #eee; margin-bottom: 6px; }
.me-filter-header { display: none; }
.me-filter-body { padding: 6px 8px; }
.me-filter-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; }
.me-filter-group { display: flex; flex-direction: column; min-width: 0; }
.me-filter-label { font-weight: 600; color: #555; margin-bottom: 2px; font-size: 0.65rem; }
.me-filter-label .me-required { color: #ef4444; margin-left: 1px; }
.me-filter-select { width: 100%; border: 1px solid #ddd; border-radius: 5px; padding: 3px 18px 3px 5px; font-size: 0.72rem; color: #1a1a2e; background: #fff; appearance: none; cursor: pointer; transition: all 0.15s; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 4px center; background-repeat: no-repeat; background-size: 0.8rem; box-sizing: border-box; max-width: 100%; }
.me-filter-select:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 2px rgba(67,97,238,0.08); }
.me-filter-select:disabled { background: #f9fafb; color: #9ca3af; cursor: not-allowed; }

/* Lock Banner */
.me-lock-banner { border-radius: 6px; padding: 4px 8px; margin-bottom: 4px; display: flex; align-items: center; gap: 4px; font-weight: 500; font-size: 0.72rem; flex-wrap: wrap; }
.me-lock-banner i { font-size: 0.8rem; flex-shrink: 0; }
.me-lock-banner.locked { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
.me-lock-banner.locked i { color: #dc2626; }
.me-lock-banner.unlocked { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
.me-lock-banner.unlocked i { color: #059669; }

/* Save badge */
.me-save-badge {
    font-size: 0.72rem; padding: 3px 10px; border-radius: 6px; font-weight: 700;
    white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;
    transition: all 0.2s ease; min-width: 70px; max-width: 120px; justify-content: center;
}
.me-save-badge.saving { background: #fef3c7; color: #d97706; border: 1px solid #fcd34d; animation: meBadgePulse 1s ease-in-out infinite; }
.me-save-badge.saved { background: #d1fae5; color: #059669; border: 1px solid #6ee7b7; }
.me-save-badge.error { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
.me-save-badge.idle { background: #f3f4f6; color: #9ca3af; border: 1px solid #e5e7eb; }
.me-save-badge.editing { background: #eff6ff; color: #2563eb; border: 1px solid #93c5fd; }
@keyframes meBadgePulse { 0%,100% { opacity: 1; } 50% { opacity: 0.7; } }

/* Empty State */
.me-empty { text-align: center; padding: 1.5rem 1rem; background: #fff; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #eee; }
.me-empty i { font-size: 2rem; color: #d1d5db; margin-bottom: 0.5rem; display: block; }
.me-empty p { color: #9ca3af; font-size: 0.8rem; margin: 0; }
.me-empty-hint { font-size: 0.7rem; color: #b0b8c4; margin-top: 0.25rem; }

/* Loading */
.me-loading { display: flex; align-items: center; justify-content: center; padding: 1.5rem; gap: 0.5rem; color: #9ca3af; font-size: 0.75rem; }
.me-spinner { width: 18px; height: 18px; border: 2px solid #e5e7eb; border-top-color: #4361ee; border-radius: 50%; animation: meSpin 0.7s linear infinite; }
@keyframes meSpin { to { transform: rotate(360deg); } }

/* Filter collapse */
.me-filter-card.me-filter-collapsed .me-filter-body { display: none; }
.me-filter-card.me-filter-collapsed .me-filter-header { border-bottom: none; padding: 4px 8px; }

/* Filter summary */
.me-filter-summary {
    display: none; align-items: center; gap: 4px;
    padding: 3px 8px; background: #f0fdf4; border: 1px solid #a7f3d0;
    border-radius: 6px; margin-bottom: 4px; font-size: 0.68rem;
    font-weight: 600; color: #065f46; flex-wrap: wrap;
}
.me-filter-summary.visible { display: flex; }
.me-filter-summary-chip {
    display: inline-flex; align-items: center; gap: 2px;
    padding: 1px 6px; background: #fff; border: 1px solid #d1fae5;
    border-radius: 4px; font-size: 0.65rem; color: #1a1a2e;
}
.me-filter-summary-chip i { font-size: 0.55rem; color: #10b981; }
.me-filter-change-btn {
    margin-left: auto; padding: 2px 8px; border-radius: 4px;
    border: 1px solid #a7f3d0; background: #fff; color: #059669;
    font-size: 0.65rem; font-weight: 600; cursor: pointer; white-space: nowrap;
}
.me-filter-change-btn:hover { background: #ecfdf5; border-color: #10b981; }

/* ===== ALL-CARDS VIEW ===== */

/* Global status bar */
.me-global-status {
    display: flex; align-items: center; justify-content: space-between;
    padding: 4px 8px; margin-bottom: 4px;
    background: #fff; border-radius: 6px;
    border: 1px solid #eee; flex-wrap: wrap; gap: 4px;
}
.me-global-status-left { display: flex; align-items: center; gap: 6px; }
.me-global-status-right { display: flex; align-items: center; gap: 4px; }
.me-student-count { font-size: 0.72rem; font-weight: 700; color: #1a1a2e; display: flex; align-items: center; gap: 4px; }
.me-student-count i { color: #4361ee; font-size: 0.7rem; }

/* Mark entry area */
.me-mark-entry-area { display: flex; flex-direction: column; max-width: 100% !important; overflow-x: hidden !important; }

/* Cards container */
.me-cards-container {
    overflow: hidden; position: relative; border-radius: 6px;
    max-height: calc(100vh - 260px); min-height: 200px;
    max-width: 100% !important; box-sizing: border-box !important;
    width: 100% !important;
}

/* Card slider */
.me-card-slider { display: flex; transition: transform 0.3s ease; will-change: transform; touch-action: pan-y; max-width: 100% !important; width: 100% !important; }

/* Each student card fills the carousel viewport */
.me-card-slider .me-student-card { min-width: 100%; max-width: 100%; flex-shrink: 0; width: 100%; }

/* Student Card */
.me-student-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    border: 1px solid #eee; overflow: hidden;
    transition: box-shadow 0.15s, border-color 0.15s;
    max-width: 100% !important; width: 100% !important; box-sizing: border-box !important;
}
.me-student-card:hover { box-shadow: 0 2px 6px rgba(0,0,0,0.06); border-color: #ddd; }
.me-student-card.card-active { border-color: #4361ee; box-shadow: 0 2px 8px rgba(67,97,238,0.1); }

/* Card Header */
.me-sc-header {
    display: flex; align-items: center; gap: 6px;
    padding: 4px 8px; background: #fafbfc;
    border-bottom: 1px solid #eee;
}
.me-sc-avatar {
    width: 22px; height: 22px; border-radius: 4px;
    background: linear-gradient(135deg, #4361ee, #818cf8); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.55rem; font-weight: 700; flex-shrink: 0;
}
.me-sc-info { flex: 1; min-width: 0; display: flex; align-items: center; gap: 6px; }
.me-sc-name { font-weight: 700; font-size: 0.72rem; color: #1a1a2e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.me-sc-roll { font-size: 0.6rem; color: #9ca3af; font-weight: 500; }
.me-sc-number { font-size: 0.58rem; font-weight: 700; color: #6b7280; background: #f3f4f6; padding: 1px 5px; border-radius: 3px; white-space: nowrap; }

/* Card Body — CRITICAL: overflow-x auto allows grid to scroll if needed */
.me-sc-body { padding: 4px 8px; overflow-x: auto; -webkit-overflow-scrolling: touch; }

/* Section Label */
.me-sc-section-label {
    font-size: 0.55rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
    padding: 0 0 2px; margin: 0 0 3px; color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
}
.me-sc-section-label.ca-label { color: #1d4ed8; border-bottom-color: #bfdbfe; }
.me-sc-section-label.exam-label { color: #059669; border-bottom-color: #a7f3d0; }

/* CA Grid — MOBILE-FIRST: 3 columns default */
.me-sc-ca-grid {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 3px; margin-bottom: 4px;
    min-width: 0; width: 100%;
}

/* Exam Grid — MOBILE-FIRST: 2 columns default */
.me-sc-exam-grid {
    display: grid; grid-template-columns: repeat(2, 1fr);
    gap: 3px; margin-bottom: 4px;
    min-width: 0; width: 100%;
}

/* Card Field Item */
.me-sc-field {
    display: flex; flex-direction: column; align-items: center;
    background: #f9fafb; border-radius: 4px; padding: 2px 1px;
    border: 1px solid #f0f0f0; transition: all 0.15s;
    min-width: 0; overflow: hidden; max-width: 100%;
}
.me-sc-field:hover { border-color: #ddd; background: #fff; }
.me-sc-field.field-exam { background: #f0fdf4; border-color: #d1fae5; }
.me-sc-field.field-exam:hover { border-color: #a7f3d0; background: #ecfdf5; }
.me-sc-field-label {
    font-size: 0.5rem; font-weight: 700; color: #6b7280;
    text-transform: uppercase; letter-spacing: 0.2px; margin-bottom: 1px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;
}
.me-sc-field.field-exam .me-sc-field-label { color: #059669; }
.me-sc-field-input {
    width: 100%; max-width: 100%; border: 1px solid #ddd; border-radius: 3px;
    text-align: center; padding: 3px 1px; font-size: 0.78rem; font-weight: 700;
    color: #1a1a2e; background: #fff; outline: none; transition: all 0.15s;
    box-sizing: border-box; min-width: 0;
}
.me-sc-field-input:focus { border-color: #4361ee; box-shadow: 0 0 0 2px rgba(67,97,238,0.08); background: #f8f9ff; }
.me-sc-field.field-exam .me-sc-field-input:focus { border-color: #10b981; box-shadow: 0 0 0 2px rgba(16,185,129,0.08); background: #f0fdf4; }
.me-sc-field-input:disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }
.me-sc-field-input.input-saved { border-color: #10b981; background: #ecfdf5; }
.me-sc-field-input.input-error { border-color: #ef4444; background: #fef2f2; }
.me-sc-field-max { font-size: 0.42rem; color: #9ca3af; margin-top: 0; }

/* Card Totals Footer */
.me-sc-totals {
    display: grid; grid-template-columns: repeat(2, 1fr);
    gap: 3px; padding: 3px 8px; background: #fafbfc;
    border-top: 1px solid #eee;
}
.me-sc-total-item { text-align: center; }
.me-sc-total-label { font-size: 0.48rem; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.2px; }
.me-sc-total-value { font-size: 0.75rem; font-weight: 800; margin-top: 0; }
.me-sc-total-value.ca-val { color: #1d4ed8; }
.me-sc-total-value.exam-val { color: #059669; }
.me-sc-total-value.grand-val { color: #7c3aed; }

/* Grade Badge */
.me-grade-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 22px; padding: 1px 4px; border-radius: 3px; font-weight: 800; font-size: 0.6rem; }
.me-grade-A { background: rgba(52,211,153,0.15); color: #059669; }
.me-grade-B { background: rgba(96,165,250,0.15); color: #2563eb; }
.me-grade-C { background: rgba(251,191,36,0.15); color: #d97706; }
.me-grade-D { background: rgba(251,146,60,0.15); color: #ea580c; }
.me-grade-F { background: rgba(248,113,113,0.15); color: #dc2626; }

/* Keyboard hint */
.me-card-hint { text-align: center; padding: 3px; font-size: 0.6rem; color: #9ca3af; margin-top: 2px; }
.me-card-hint kbd { background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 2px; padding: 0 3px; font-size: 0.55rem; font-family: inherit; }

/* ===== CAROUSEL NAVIGATION ===== */
.me-carousel-nav {
    display: flex; align-items: center; justify-content: space-between;
    padding: 4px 8px; margin-bottom: 4px;
    background: #fff; border-radius: 6px;
    border: 1px solid #eee;
}
.me-carousel-nav-btn {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 5px;
    border: 1px solid #ddd; background: #fff;
    font-size: 0.7rem; font-weight: 600; color: #1a1a2e;
    cursor: pointer; transition: all 0.15s; white-space: nowrap;
}
.me-carousel-nav-btn:hover:not(:disabled) { background: #f0f4ff; border-color: #4361ee; color: #4361ee; }
.me-carousel-nav-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.me-carousel-nav-btn i { font-size: 0.65rem; }
.me-carousel-counter { font-size: 0.72rem; font-weight: 700; color: #1a1a2e; }
.me-carousel-counter span { color: #4361ee; }

/* Dot indicators */
.me-carousel-dots {
    display: flex; align-items: center; justify-content: center;
    gap: 4px; padding: 6px 0 2px; flex-wrap: wrap;
}
.me-carousel-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #e5e7eb; border: none; padding: 0;
    cursor: pointer; transition: all 0.2s;
}
.me-carousel-dot:hover { background: #9ca3af; }
.me-carousel-dot.active { background: #4361ee; transform: scale(1.2); }

/* Progress bar */
.me-carousel-progress {
    height: 3px; background: #e5e7eb; border-radius: 2px;
    margin: 4px 8px 0; overflow: hidden;
}
.me-carousel-progress-bar {
    height: 100%; background: #4361ee; border-radius: 2px;
    transition: width 0.3s ease;
}

/* Swipe hint animation */
@keyframes meSwipeHint {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(-8px); }
}
.me-swipe-hint { animation: meSwipeHint 1.5s ease-in-out 2; }

/* ===== RESPONSIVE — MOBILE-FIRST, scale UP for larger screens ===== */
/* Mobile (<480px): uses defaults above — 3-col filter, 3-col CA, 2-col exam */
/* Small tablet */
@media (min-width: 481px) {
    .me-filter-grid { grid-template-columns: repeat(3, 1fr); }
    .me-sc-ca-grid { grid-template-columns: repeat(4, 1fr); }
}
/* Tablet */
@media (min-width: 768px) {
    .me-filter-grid { grid-template-columns: repeat(6, 1fr); }
    .me-sc-ca-grid { grid-template-columns: repeat(5, 1fr); }
    .me-cards-container { max-height: calc(100vh - 280px); }
}
/* Desktop */
@media (min-width: 992px) {
    .me-filter-grid { grid-template-columns: repeat(6, 1fr); }
    .me-sc-ca-grid { grid-template-columns: repeat(5, 1fr); }
}
/* Large desktop */
@media (min-width: 1200px) {
    .me-sc-ca-grid { grid-template-columns: repeat(5, 1fr); }
    .me-sc-totals { grid-template-columns: repeat(4, 1fr); }
}

/* ===== NUCLEAR OPTION: Force containment on mobile ===== */
@media (max-width: 768px) {
    html, body { overflow-x: hidden !important; max-width: 100vw !important; }
    .admin-content { padding: 4px !important; overflow-x: hidden !important; max-width: 100vw !important; width: 100% !important; }
    .me-page, .me-page > *, .me-page > div,
    .me-filter-card, .me-filter-summary, .me-global-status,
    .me-carousel-nav, .me-cards-container, .me-mark-entry-area,
    .me-student-card, .me-sc-header, .me-sc-body, .me-sc-totals,
    .me-card-slider, .me-sc-ca-grid, .me-sc-exam-grid,
    .me-sc-field, .me-filter-grid, .me-filter-group {
        max-width: 100% !important; overflow-x: hidden !important;
        box-sizing: border-box !important; width: 100% !important;
    }
    /* Card body can scroll horizontally for input grids */
    .me-sc-body { overflow-x: auto !important; width: 100% !important; }
    .me-cards-container { max-height: calc(100vh - 280px); overflow-x: hidden !important; }
    .me-sc-header { padding: 3px 5px; gap: 4px; }
    .me-sc-body { padding: 3px 5px; }
    .me-sc-totals { padding: 3px 5px; }
    .me-sc-field-input { font-size: 0.72rem; padding: 2px 1px; max-width: 100% !important; }
    .me-carousel-nav-btn { padding: 6px 10px; font-size: 0.75rem; }
    .me-carousel-dot { width: 10px; height: 10px; }
    .me-save-badge { font-size: 0.65rem; padding: 2px 8px; min-width: 60px; }
    .me-filter-summary { font-size: 0.6rem; gap: 2px; padding: 2px 5px; }
    .me-global-status { padding: 3px 5px; }
}
@media (max-width: 480px) {
    html, body { overflow-x: hidden !important; max-width: 100vw !important; }
    .admin-content { padding: 2px !important; overflow-x: hidden !important; width: 100% !important; }
    .me-filter-body { padding: 4px; }
    .me-cards-container { max-height: calc(100vh - 300px); }
    .me-sc-field-input { font-size: 0.68rem; }
    .me-save-badge { font-size: 0.6rem; padding: 2px 6px; min-width: 50px; }
}
</style>
@endpush

@section('content')
<div class="me-page">
    {{-- Compact top bar: Lock/Permissions only --}}
    @canany(['mark-entry.lock', 'mark-entry.permissions'])
    <div style="display:flex;justify-content:flex-end;gap:0.5rem;padding:0 0 0.5rem;flex-wrap:wrap;max-width:100%;box-sizing:border-box;overflow:hidden;">
        @can('mark-entry.lock')
            <a href="{{ route('admin.mark-entry-locks.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.78rem;padding:0.35rem 0.85rem;">
                <i class="fas fa-lock"></i> Lock Management
            </a>
        @endcan
        @can('mark-entry.permissions')
            <a href="{{ route('admin.mark-entry-permissions.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.78rem;padding:0.35rem 0.85rem;">
                <i class="fas fa-key"></i> Permissions
            </a>
        @endcan
    </div>
    @endcanany

    {{-- Hidden elements for JS state (academic year, term, lock info) --}}
    <span id="chipAy" style="display:none;">{{ $currentAy ? $currentAy->name : 'No Academic Year' }}</span>
    <span id="chipTerm" style="display:none;">{{ $currentTerm ? $currentTerm->name : 'No Active Term' }}</span>
    <span id="chipLock" style="display:none;"></span>
    <span id="chipLockText" style="display:none;">--</span>

    {{-- Compact Filter Summary (shown when filter is collapsed) --}}
    <div class="me-filter-summary" id="filterSummary">
        <i class="fas fa-check-circle" style="color:#10b981;"></i>
        <span id="filterSummaryText">--</span>
        <button type="button" class="me-filter-change-btn" id="btnChangeFilter" onclick="showFilterPanel()">
            <i class="fas fa-filter"></i> Change
        </button>
    </div>

    {{-- Filter Panel --}}
    <div class="me-filter-card" id="filterPanel">
        <div class="me-filter-body">
            <div class="me-filter-grid">
                <div class="me-filter-group">
                    <label class="me-filter-label" for="filterAy">Academic Year <span class="me-required">*</span></label>
                    <select id="filterAy" class="me-filter-select" {{ $isTeacher ? 'disabled' : '' }}>
                        @foreach ($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $currentAy && $currentAy->id == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="me-filter-group">
                    <label class="me-filter-label" for="filterTerm">Term <span class="me-required">*</span></label>
                    <select id="filterTerm" class="me-filter-select" {{ $isTeacher ? 'disabled' : '' }}>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" {{ $currentTerm && $currentTerm->id == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="me-filter-group">
                    <label class="me-filter-label" for="filterBranch">Branch</label>
                    <select id="filterBranch" class="me-filter-select" name="branch_id" {{ $branchScope ? 'disabled' : '' }}>
                        <option value="">-- All Branches --</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $userBranchId && $userBranchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @if($branchScope)
                        <input type="hidden" name="branch_id" value="{{ $branchScope }}">
                    @endif
                </div>
                <div class="me-filter-group">
                    <label class="me-filter-label" for="filterClass">Class <span class="me-required">*</span></label>
                    <select id="filterClass" class="me-filter-select">
                        <option value="">-- Select Class --</option>
                        @foreach ($classes as $cls)
                            <option value="{{ $cls->id }}" data-branch-id="{{ $cls->branch_id }}">{{ $cls->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="me-filter-group">
                    <label class="me-filter-label" for="filterSection">Section <span class="me-required">*</span></label>
                    <select id="filterSection" class="me-filter-select" disabled>
                        <option value="">-- Select Section --</option>
                    </select>
                </div>
                <div class="me-filter-group">
                    <label class="me-filter-label" for="filterSubject">Subject <span class="me-required">*</span></label>
                    <select id="filterSubject" class="me-filter-select" disabled>
                        <option value="">-- Select Subject --</option>
                    </select>
                </div>

            </div>
            <div style="margin-top:4px;display:flex;gap:4px;align-items:center;max-width:100%;box-sizing:border-box;overflow:hidden;">
                <button type="button" class="btn-modern btn-modern-primary" id="btnLoadStudents" style="font-size:0.68rem;padding:3px 10px;" disabled>
                    <i class="fas fa-download"></i> Load
                </button>
                <span id="filterHint" style="font-size:0.6rem;color:#9ca3af;">Select all filters</span>
            </div>
        </div>
    </div>

    {{-- Lock Status Banner --}}
    <div id="lockBanner" class="me-lock-banner d-none">
        <i class="fas fa-lock"></i>
        <span id="lockBannerText">Mark entry is locked for this term.</span>
    </div>

    {{-- Loading Indicator --}}
    <div id="loadingState" class="d-none">
        <div class="me-loading">
            <div class="me-spinner"></div>
            <span>Loading students...</span>
        </div>
    </div>

    {{-- Empty State --}}
    <div id="emptyState" class="me-empty">
        <i class="fas fa-hand-pointer"></i>
        <p>Select academic year, term, class, section, and subject above to begin entering marks</p>
        <p class="me-empty-hint">Use the filter panel to choose the class and subject, then click "Load Students"</p>
    </div>

    {{-- No Students State --}}
    <div id="noStudentsState" class="me-empty d-none">
        <i class="fas fa-users-slash"></i>
        <p>No students found for the selected class and section</p>
        <p class="me-empty-hint">Try selecting a different class, section, or subject</p>
    </div>

    {{-- Mark Entry All-Cards View (hidden until students load) --}}
    <div id="markEntryArea" class="d-none">
        {{-- Global Status Bar --}}
        <div class="me-global-status">
            <div class="me-global-status-left">
                <span class="me-student-count">
                    <i class="fas fa-users"></i>
                    <span id="totalStudentCount">0</span> Students
                </span>
            </div>
            <div class="me-global-status-right">
                <span class="me-save-badge idle" id="globalSaveStatus"><i class="fas fa-check-circle"></i> Ready</span>
            </div>
        </div>

        {{-- Carousel Navigation Bar --}}
        <div class="me-carousel-nav" id="carouselNav">
            <button type="button" class="me-carousel-nav-btn" id="btnPrevStudent" disabled>
                <i class="fas fa-chevron-left"></i> Prev
            </button>
            <div class="me-carousel-counter">
                Student <span id="currentStudentNum">1</span> of <span id="totalStudentNum">0</span>
            </div>
            <button type="button" class="me-carousel-nav-btn" id="btnNextStudent" disabled>
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        {{-- Cards Container (carousel wrapper) --}}
        <div class="me-cards-container" id="cardsContainer">
            <div class="me-card-slider" id="cardSlider">
                {{-- dynamically built --}}
            </div>
        </div>

        {{-- Dot Indicators / Progress Bar --}}
        <div class="me-carousel-dots" id="carouselDots"></div>
        <div class="me-carousel-progress" id="carouselProgress">
            <div class="me-carousel-progress-bar" id="carouselProgressBar" style="width:0%"></div>
        </div>

        {{-- Keyboard Hint --}}
        <div class="me-card-hint" id="keyboardHint">
            <kbd>&larr;</kbd> <kbd>&rarr;</kbd> to navigate students &middot; <kbd>Tab</kbd> to move between fields &middot; Swipe on mobile
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    // ========== STATE ==========
    var students = [];
    var saveTimers = {};  // keyed by student_id + mark_key
    var isLocked = false;
    var hasPermission = false;
    var currentMarkField = 'all';
    var currentStudentIndex = 0;

    // ========== CAROUSEL TOUCH STATE ==========
    var touchStartX = 0;
    var touchStartY = 0;
    var touchCurrentX = 0;
    var isSwiping = false;
    var swipeThreshold = 50;

    // ========== DOM REFS ==========
    var filterAy = document.getElementById('filterAy');
    var filterTerm = document.getElementById('filterTerm');
    var filterBranch = document.getElementById('filterBranch');
    var filterClass = document.getElementById('filterClass');
    var filterSection = document.getElementById('filterSection');
    var filterSubject = document.getElementById('filterSubject');
    var btnLoad = document.getElementById('btnLoadStudents');
    var filterHint = document.getElementById('filterHint');

    var lockBanner = document.getElementById('lockBanner');
    var lockBannerText = document.getElementById('lockBannerText');
    var chipLock = document.getElementById('chipLock');
    var chipLockText = document.getElementById('chipLockText');

    var loadingState = document.getElementById('loadingState');
    var emptyState = document.getElementById('emptyState');
    var noStudentsState = document.getElementById('noStudentsState');
    var markEntryArea = document.getElementById('markEntryArea');
    var globalSaveStatus = document.getElementById('globalSaveStatus');
    var keyboardHint = document.getElementById('keyboardHint');
    var cardsContainer = document.getElementById('cardsContainer');
    var totalStudentCount = document.getElementById('totalStudentCount');

    // Carousel DOM refs
    var cardSlider = document.getElementById('cardSlider');
    var btnPrevStudent = document.getElementById('btnPrevStudent');
    var btnNextStudent = document.getElementById('btnNextStudent');
    var currentStudentNum = document.getElementById('currentStudentNum');
    var totalStudentNum = document.getElementById('totalStudentNum');
    var carouselDots = document.getElementById('carouselDots');
    var carouselProgressBar = document.getElementById('carouselProgressBar');

    // ========== TEACHER ASSIGNMENTS DATA ==========
    var teacherAssignments = @json($teacherAssignments);

    // ========== SERVER-SIDE SECTIONS DATA (fallback) ==========
    var serverSections = @json($sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'class_id' => $s->class_id]));

    // ========== API ROUTES ==========
    var API_TERMS = '{{ route("admin.mark-entries.api.terms") }}';
    var API_BRANCHES = '{{ route("admin.mark-entries.api.branches") }}';
    var API_CLASSES = '{{ route("admin.mark-entries.api.classes") }}';
    var API_SECTIONS = '{{ route("admin.mark-entries.api.sections") }}';
    var API_SUBJECTS = '{{ route("admin.mark-entries.api.subjects") }}';
    var API_LOAD_STUDENTS = '{{ route("admin.mark-entries.api.load-students") }}';
    var API_SAVE = '{{ route("admin.mark-entries.api.save") }}';
    var API_CHECK_LOCK = '{{ route("admin.mark-entries.api.check-lock") }}';
    var API_KEEPALIVE = '{{ route("admin.keepalive") }}';
    var CSRF = '{{ csrf_token() }}';

    // ========== MARK FIELDS DEFINITION ==========
    var CA_FIELDS = [
        { key: 'ca1', max: 5, label: 'CA1' }, { key: 'ca2', max: 5, label: 'CA2' },
        { key: 'ca3', max: 5, label: 'CA3' }, { key: 'ca4', max: 5, label: 'CA4' },
        { key: 'ca5', max: 5, label: 'CA5' }, { key: 'ca6', max: 5, label: 'CA6' },
        { key: 'ca7', max: 5, label: 'CA7' }, { key: 'ca8', max: 5, label: 'CA8' },
        { key: 'ca9', max: 5, label: 'CA9' }, { key: 'ca10', max: 5, label: 'CA10' }
    ];
    var EXTRA_CA_FIELDS = [
        { key: 'conduct', max: 5, label: 'Conduct' },
        { key: 'handwriting', max: 5, label: 'Handwriting' },
        { key: 'creativity', max: 10, label: 'Creativity' }
    ];
    var EXAM_FIELDS = [
        { key: 'test1', max: 10, label: 'Test 1' }, { key: 'test2', max: 10, label: 'Test 2' },
        { key: 'mid_term', max: 20, label: 'Mid-Term' }, { key: 'final_exam', max: 30, label: 'Final Exam' }
    ];
    var ALL_MARK_FIELDS = CA_FIELDS.concat(EXTRA_CA_FIELDS).concat(EXAM_FIELDS);
    var CA_KEYS = CA_FIELDS.map(function(f) { return f.key; });
    var EXTRA_CA_KEYS = EXTRA_CA_FIELDS.map(function(f) { return f.key; });
    var EXAM_KEYS = EXAM_FIELDS.map(function(f) { return f.key; });

    // ========== FIELD INFO LOOKUP ==========
    function getFieldInfo(key) {
        for (var i = 0; i < ALL_MARK_FIELDS.length; i++) {
            if (ALL_MARK_FIELDS[i].key === key) return ALL_MARK_FIELDS[i];
        }
        return null;
    }

    function getFieldCategory(key) {
        if (CA_KEYS.indexOf(key) !== -1) return 'ca';
        if (EXTRA_CA_KEYS.indexOf(key) !== -1) return 'extra-ca';
        if (EXAM_KEYS.indexOf(key) !== -1) return 'exam';
        return 'ca';
    }

    // ========== CSRF TOKEN REFRESH ==========
    // Fetches a fresh CSRF token from the server and updates the global variable.
    // This is critical when a 419 (CSRF token mismatch / session expired) is detected.
    var csrfRefreshInProgress = false;

    function updateCSRFToken(newToken) {
        if (!newToken) return;
        CSRF = newToken;
        var metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) metaTag.setAttribute('content', CSRF);
    }

    function refreshCSRFToken() {
        if (csrfRefreshInProgress) return Promise.resolve(CSRF);
        csrfRefreshInProgress = true;

        return fetch(API_KEEPALIVE, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) {
            if (!r.ok) {
                if (r.status === 401 || r.status === 302 || r.redirected) {
                    // Session truly expired - redirect to login
                    alert('Your session has expired. You will be redirected to the login page.\n\nYour unsaved marks have been backed up to local storage and will be available after you log back in.');
                    backupMarksToLocalStorage();
                    window.location.href = '{{ route("login") }}';
                    throw new Error('Session expired');
                }
                throw new Error('Keepalive failed: HTTP ' + r.status);
            }
            return r.json();
        })
        .then(function(data) {
            if (data.csrf_token) {
                updateCSRFToken(data.csrf_token);
                console.log('[MarkEntry] CSRF token refreshed via keepalive');
            }
            csrfRefreshInProgress = false;
            return CSRF;
        })
        .catch(function(err) {
            csrfRefreshInProgress = false;
            console.error('[MarkEntry] CSRF refresh failed:', err);
            throw err;
        });
    }

    // ========== SESSION KEEPALIVE (ACTIVITY-DRIVEN) ==========
    // The keepalive pings the server to keep the session alive during long mark entry sessions.
    // It runs on TWO triggers:
    //   1. Timer: every 2 minutes (prevents idle expiry even if user types slowly)
    //   2. Activity: resets the timer whenever the user types/clicks (prevents expiry during active use)
    // The CSRF token is refreshed on every ping AND on every successful auto-save.
    var KEEPALIVE_INTERVAL_MS = 2 * 60 * 1000; // 2 minutes (reduced from 5 min)
    var keepaliveTimer = null;
    var lastKeepaliveTime = Date.now();

    function scheduleKeepalive() {
        if (keepaliveTimer) clearTimeout(keepaliveTimer);
        keepaliveTimer = setTimeout(function() {
            refreshCSRFToken()
                .then(function() { lastKeepaliveTime = Date.now(); })
                .catch(function(err) {
                    console.warn('[MarkEntry] Keepalive ping failed (will retry):', err.message);
                })
                .finally(function() { scheduleKeepalive(); });
        }, KEEPALIVE_INTERVAL_MS);
    }

    function startKeepalive() {
        scheduleKeepalive();
        console.log('[MarkEntry] Session keepalive started (every 2 minutes, activity-driven)');

        // Listen for user activity — any typing or clicking resets the keepalive timer
        // This ensures the session NEVER expires while the user is actively entering marks
        document.addEventListener('input', resetKeepaliveOnActivity);
        document.addEventListener('click', resetKeepaliveOnActivity);
        document.addEventListener('keydown', resetKeepaliveOnActivity);
        document.addEventListener('touchstart', resetKeepaliveOnActivity);
    }

    function resetKeepaliveOnActivity() {
        // If more than 30 seconds since last keepalive, reschedule immediately
        // This prevents the scenario where the user is typing constantly but the
        // keepalive hasn't had a chance to run, and the session silently expires.
        var elapsed = Date.now() - lastKeepaliveTime;
        if (elapsed > 30 * 1000) { // 30 seconds since last server contact
            scheduleKeepalive(); // Reset the 2-minute timer
        }
    }

    // ========== LOCAL STORAGE BACKUP ==========
    // Backup unsaved marks to localStorage so they survive page reloads / session expiry.
    var LS_KEY = 'markEntryBackup_' + window.location.pathname;

    function backupMarksToLocalStorage() {
        try {
            if (students.length === 0) return;
            var backup = {
                timestamp: new Date().toISOString(),
                ayId: filterAy.value,
                termId: filterTerm.value,
                classId: filterClass.value,
                sectionId: filterSection.value,
                subjectId: filterSubject.value,
                students: students.map(function(s) {
                    return { id: s.id, marks: JSON.parse(JSON.stringify(s.marks)) };
                })
            };
            localStorage.setItem(LS_KEY, JSON.stringify(backup));
            console.log('[MarkEntry] Marks backed up to localStorage (' + students.length + ' students)');
        } catch (e) {
            console.warn('[MarkEntry] localStorage backup failed:', e);
        }
    }

    function restoreMarksFromLocalStorage() {
        try {
            var raw = localStorage.getItem(LS_KEY);
            if (!raw) return null;
            var backup = JSON.parse(raw);

            // Check if the backup matches the current filters
            if (backup.ayId !== filterAy.value || backup.termId !== filterTerm.value ||
                backup.classId !== filterClass.value || backup.sectionId !== filterSection.value ||
                backup.subjectId !== filterSubject.value) {
                return null; // Different context, don't restore
            }

            // Check if backup is recent enough (within 8 hours)
            var age = Date.now() - new Date(backup.timestamp).getTime();
            if (age > 8 * 60 * 60 * 1000) {
                localStorage.removeItem(LS_KEY);
                return null;
            }

            return backup;
        } catch (e) {
            console.warn('[MarkEntry] localStorage restore failed:', e);
            return null;
        }
    }

    function clearLocalStorageBackup() {
        try {
            localStorage.removeItem(LS_KEY);
        } catch (e) { /* ignore */ }
    }

    // Auto-backup every 2 minutes while there are students loaded
    function startAutoBackup() {
        setInterval(function() {
            if (students.length > 0) {
                backupMarksToLocalStorage();
            }
        }, 2 * 60 * 1000);
    }

    // ========== INIT ==========
    function init() {
        console.log('[MarkEntry] Initializing... isTeacher={{ $isTeacher ? "true" : "false" }}, classes={{ $classes->count() }}');

        if (teacherAssignments && teacherAssignments.length > 0) {
            populateTeacherClasses();
        } else if (!{{ $isTeacher ? 'true' : 'false' }}) {
            var hasServerClasses = filterClass.querySelectorAll('option[value!=""]').length > 0;
            if (!hasServerClasses) {
                console.log('[MarkEntry] No server-side classes, loading via API...');
                loadClasses();
            } else {
                console.log('[MarkEntry] Classes already populated from server (' + filterClass.querySelectorAll('option[value!=""]').length + ' classes)');
            }
        }

        if (filterAy.value && filterTerm.value) {
            checkLockStatus();
        }

        updateLoadButton();

        // Start session keepalive and auto-backup to prevent session expiry during mark entry
        startKeepalive();
        startAutoBackup();
    }

    // ========== TEACHER CLASS POPULATION ==========
    function populateTeacherClasses() {
        var ayId = filterAy.value;
        var branchId = filterBranch ? filterBranch.value : '';
        var classes = {};

        teacherAssignments.forEach(function(a) {
            var ayMatch = !ayId || a.academic_year_id == ayId || !a.academic_year_id;
            if (ayMatch && a.class_id && a.class_name) {
                classes[a.class_id] = a.class_name;
            }
        });

        filterClass.innerHTML = '<option value="">-- Select Class --</option>';
        Object.keys(classes).forEach(function(id) {
            var opt = document.createElement('option');
            opt.value = id;
            opt.textContent = classes[id];
            filterClass.appendChild(opt);
        });

        if (Object.keys(classes).length === 1) {
            filterClass.value = Object.keys(classes)[0];
            loadSections();
        }
    }

    // ========== CASCADE: AY -> Terms + Branches ==========
    filterAy.addEventListener('change', function() {
        var ayId = this.value;
        document.getElementById('chipAy').textContent = ayId
            ? filterAy.selectedOptions[0].textContent
            : 'No Academic Year';

        filterTerm.innerHTML = '<option value="">-- Select Term --</option>';
        filterBranch.innerHTML = '<option value="">-- All Branches --</option>';
        filterClass.innerHTML = '<option value="">-- Select Class --</option>';
        filterSection.innerHTML = '<option value="">-- Select Section --</option>';
        filterSection.disabled = true;
        filterSubject.innerHTML = '<option value="">-- Select Subject --</option>';
        filterSubject.disabled = true;
        hideMarkEntry();

        if (!ayId) { updateLoadButton(); return; }

        fetch(API_TERMS + '?academic_year_id=' + ayId, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) {
                if (!r.ok) {
                    if (r.status === 302 || r.redirected) throw new Error('Session expired. Please refresh the page.');
                    throw new Error('HTTP ' + r.status);
                }
                return r.json();
            })
            .then(function(data) {
                filterTerm.innerHTML = '<option value="">-- Select Term --</option>';
                data.forEach(function(t) {
                    var opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = t.name;
                    filterTerm.appendChild(opt);
                });
                // Auto-select if there's only one term
                if (data.length === 1) {
                    filterTerm.value = data[0].id;
                    filterTerm.dispatchEvent(new Event('change'));
                }
            })
            .catch(function(err) {
                console.error('Failed to load terms:', err);
            });

        // Also reload branches for this academic year
        loadBranches(ayId);

        if (teacherAssignments && teacherAssignments.length > 0) {
            populateTeacherClasses();
        } else {
            loadClasses();
        }

        updateLoadButton();
    });

    // ========== CASCADE: Term -> Classes ==========
    filterTerm.addEventListener('change', function() {
        var termId = this.value;
        document.getElementById('chipTerm').textContent = termId
            ? filterTerm.selectedOptions[0].textContent
            : 'No Active Term';

        filterClass.innerHTML = '<option value="">-- Select Class --</option>';
        filterSection.innerHTML = '<option value="">-- Select Section --</option>';
        filterSection.disabled = true;
        filterSubject.innerHTML = '<option value="">-- Select Subject --</option>';
        filterSubject.disabled = true;
        hideMarkEntry();

        if (termId) checkLockStatus();

        // Reload classes for the current academic year when term changes
        if (teacherAssignments && teacherAssignments.length > 0) {
            populateTeacherClasses();
        } else {
            loadClasses();
        }

        updateLoadButton();
    });

    // ========== CASCADE: Branch -> Classes ==========
    filterBranch.addEventListener('change', function() {
        filterClass.innerHTML = '<option value="">-- Select Class --</option>';
        filterSection.innerHTML = '<option value="">-- Select Section --</option>';
        filterSection.disabled = true;
        filterSubject.innerHTML = '<option value="">-- Select Subject --</option>';
        filterSubject.disabled = true;
        hideMarkEntry();

        if (teacherAssignments && teacherAssignments.length > 0) {
            populateTeacherClasses();
        } else {
            loadClasses();
        }

        updateLoadButton();
    });

    // ========== CASCADE: Class -> Sections ==========
    filterClass.addEventListener('change', function() {
        var classId = this.value;
        filterSection.innerHTML = '<option value="">-- Select Section --</option>';
        filterSection.disabled = true;
        filterSubject.innerHTML = '<option value="">-- Select Subject --</option>';
        filterSubject.disabled = true;
        hideMarkEntry();

        if (!classId) { updateLoadButton(); return; }
        loadSections();
    });

    function loadSections() {
        var classId = filterClass.value;
        if (!classId) return;

        // Try server-side sections first
        var filtered = serverSections.filter(function(s) { return s.class_id == classId; });

        if (filtered.length > 0) {
            filterSection.innerHTML = '<option value="">-- Select Section --</option>';
            filtered.forEach(function(s) {
                var opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name;
                filterSection.appendChild(opt);
            });
            filterSection.disabled = false;
            if (filtered.length === 1) {
                filterSection.value = filtered[0].id;
                loadSubjects();
            }
            updateLoadButton();
            return;
        }

        // Fallback: API
        fetch(API_SECTIONS + '?class_id=' + classId, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(data) {
                filterSection.innerHTML = '<option value="">-- Select Section --</option>';
                data.forEach(function(s) {
                    var opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    filterSection.appendChild(opt);
                });
                filterSection.disabled = data.length === 0;
                if (data.length === 1) {
                    filterSection.value = data[0].id;
                    loadSubjects();
                }
                updateLoadButton();
            })
            .catch(function(err) {
                console.error('Failed to load sections:', err);
                filterSection.disabled = true;
            });
    }

    // ========== CASCADE: Section -> Subjects ==========
    filterSection.addEventListener('change', function() {
        var sectionId = this.value;
        filterSubject.innerHTML = '<option value="">-- Select Subject --</option>';
        filterSubject.disabled = true;
        hideMarkEntry();

        if (!sectionId) { updateLoadButton(); return; }
        loadSubjects();
    });

    function loadSubjects() {
        var classId = filterClass.value;
        var sectionId = filterSection.value;
        var ayId = filterAy.value;
        if (!classId) return;

        var params = 'class_id=' + classId + (sectionId ? '&section_id=' + sectionId : '') + (ayId ? '&academic_year_id=' + ayId : '');
        fetch(API_SUBJECTS + '?' + params, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(data) {
                filterSubject.innerHTML = '<option value="">-- Select Subject --</option>';
                data.forEach(function(s) {
                    var opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    filterSubject.appendChild(opt);
                });
                filterSubject.disabled = data.length === 0;
                if (data.length === 1) {
                    filterSubject.value = data[0].id;
                }
                updateLoadButton();
            })
            .catch(function(err) {
                console.error('Failed to load subjects:', err);
                filterSubject.disabled = true;
            });
    }

    filterSubject.addEventListener('change', function() {
        updateLoadButton();
    });

    // ========== LOAD CLASSES (API fallback) ==========
    function loadClasses() {
        var ayId = filterAy.value;
        var branchId = filterBranch ? filterBranch.value : '';
        var url = API_CLASSES;
        var params = [];
        if (ayId) params.push('academic_year_id=' + ayId);
        if (branchId) params.push('branch_id=' + branchId);
        if (params.length > 0) url += '?' + params.join('&');

        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(data) {
                filterClass.innerHTML = '<option value="">-- Select Class --</option>';
                data.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    if (c.branch_id) opt.setAttribute('data-branch-id', c.branch_id);
                    filterClass.appendChild(opt);
                });
                filterSection.innerHTML = '<option value="">-- Select Section --</option>';
                filterSection.disabled = true;
            })
            .catch(function(err) {
                console.error('Failed to load classes:', err);
            });
    }

    // ========== LOAD BRANCHES (API) ==========
    function loadBranches(ayId) {
        var url = API_BRANCHES;
        if (ayId) url += '?academic_year_id=' + ayId;

        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(data) {
                var currentBranchId = filterBranch ? filterBranch.value : '';
                filterBranch.innerHTML = '<option value="">-- All Branches --</option>';
                data.forEach(function(b) {
                    var opt = document.createElement('option');
                    opt.value = b.id;
                    opt.textContent = b.name;
                    filterBranch.appendChild(opt);
                });
                // Restore previous selection if still valid
                if (currentBranchId) {
                    var exists = data.some(function(b) { return b.id == currentBranchId; });
                    if (exists) filterBranch.value = currentBranchId;
                }
                // Auto-select if there's only one branch
                if (data.length === 1) {
                    filterBranch.value = data[0].id;
                }
            })
            .catch(function(err) {
                console.error('Failed to load branches:', err);
            });
    }

    // ========== UPDATE LOAD BUTTON ==========
    function updateLoadButton() {
        var ready = filterAy.value && filterTerm.value && filterClass.value && filterSection.value && filterSubject.value;
        btnLoad.disabled = !ready;
        filterHint.textContent = ready ? 'Ready to load students' : 'Select all filters above to load students';
    }

    // ========== LOAD STUDENTS BUTTON ==========
    btnLoad.addEventListener('click', function() {
        loadStudents();
    });

    // ========== CHECK LOCK STATUS ==========
    function checkLockStatus() {
        var ayId = filterAy.value;
        var termId = filterTerm.value;
        var branchId = '{{ auth()->user()->branch_id ?? "" }}';

        if (!ayId || !termId) return;

        fetch(API_CHECK_LOCK + '?academic_year_id=' + ayId + '&term_id=' + termId + (branchId ? '&branch_id=' + branchId : ''), { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(data) {
                isLocked = !!data.locked;
                hasPermission = !!data.has_permission;

                if (isLocked && !hasPermission) {
                    lockBanner.classList.remove('d-none');
                    lockBanner.className = 'me-lock-banner locked';
                    lockBannerText.textContent = 'Mark entry is locked for this term. Contact admin for permission.';
                    chipLock.style.display = '';
                    chipLock.className = 'me-term-chip chip-lock';
                    chipLockText.textContent = 'Locked';
                } else if (isLocked && hasPermission) {
                    lockBanner.classList.remove('d-none');
                    lockBanner.className = 'me-lock-banner unlocked';
                    lockBannerText.textContent = 'Mark entry is locked, but you have permission to edit.';
                    chipLock.style.display = '';
                    chipLock.className = 'me-term-chip chip-unlock';
                    chipLockText.textContent = 'Edit Permitted';
                } else {
                    lockBanner.classList.add('d-none');
                    chipLock.style.display = '';
                    chipLock.className = 'me-term-chip chip-unlock';
                    chipLockText.textContent = 'Unlocked';
                }

                updateInputLockState();
            })
            .catch(function(err) {
                console.error('Lock check error:', err);
            });
    }

    function updateInputLockState() {
        var disabled = isLocked && !hasPermission;
        document.querySelectorAll('.me-sc-field-input').forEach(function(inp) {
            inp.disabled = disabled;
        });
    }

    // ========== LOAD STUDENTS ==========
    function loadStudents() {
        var ayId = filterAy.value;
        var termId = filterTerm.value;
        var classId = filterClass.value;
        var sectionId = filterSection.value;
        var subjectId = filterSubject.value;

        if (!ayId || !termId || !classId || !sectionId || !subjectId) return;

        currentMarkField = 'all';
        showLoading();

        var url = API_LOAD_STUDENTS
            + '?academic_year_id=' + ayId
            + '&term_id=' + termId
            + '&class_id=' + classId
            + '&section_id=' + sectionId
            + '&subject_id=' + subjectId;

        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) {
                if (!r.ok) {
                    if (r.status === 302 || r.redirected) throw new Error('Session expired. Please refresh the page.');
                    throw new Error('HTTP ' + r.status);
                }
                return r.json();
            })
            .then(function(data) {
                if (data.error) throw new Error(data.error);

                var responseStudents = Array.isArray(data.students) ? data.students : [];
                students = responseStudents.map(function(s) {
                    var studentObj = {
                        id: s.student_id || s.id,
                        student_name: s.student_name || s.full_name || (s.first_name ? s.first_name + ' ' + (s.last_name || '') : 'Student'),
                        roll_number: s.roll_number || s.admission_number || '',
                        marks: {}
                    };

                    ALL_MARK_FIELDS.forEach(function(f) {
                        studentObj.marks[f.key] = (s[f.key] !== null && s[f.key] !== undefined) ? s[f.key] : null;
                    });
                    studentObj.marks.ca_total = s.ca_total || null;
                    studentObj.marks.exam_total = s.exam_total || null;
                    studentObj.marks.grand_total = s.grand_total || null;
                    studentObj.marks.grade = s.grade || null;

                    return studentObj;
                });

                if (students.length > 0) {
                    renderAllCards();
                    showMarkEntry();
                    updateInputLockState();
                } else {
                    showNoStudents();
                }
            })
            .catch(function(err) {
                console.error('Failed to load students:', err);
                showNoStudents();
            });
    }

    // ========== RENDER ALL STUDENT CARDS ==========
    function renderAllCards() {
        totalStudentCount.textContent = students.length;

        var html = '';
        students.forEach(function(s, idx) {
            var initials = getInitials(s.student_name);
            var caTotal = s.marks.ca_total;
            var examTotal = s.marks.exam_total;
            var grandTotal = s.marks.grand_total;
            var grade = s.marks.grade || '-';
            var gradeClass = getGradeClass(grade);

            html += '<div class="me-student-card" id="card_' + s.id + '" data-student-index="' + idx + '">';

            // Card Header
            html += '<div class="me-sc-header">';
            html += '<div class="me-sc-avatar">' + escapeHtml(initials) + '</div>';
            html += '<div class="me-sc-info">';
            html += '<div class="me-sc-name">' + escapeHtml(s.student_name) + '</div>';
            html += '<div class="me-sc-roll">' + (s.roll_number ? 'Roll: ' + escapeHtml(s.roll_number) : '') + '</div>';
            html += '</div>';
            html += '<span class="me-sc-number">#' + (idx + 1) + '</span>';
            html += '</div>';

            // Card Body
            html += '<div class="me-sc-body">';

            // CA Section
            html += '<div class="me-sc-section-label ca-label">Continuous Assessment</div>';
            html += '<div class="me-sc-ca-grid">';

            // CA1-CA10
            CA_FIELDS.forEach(function(f) {
                var val = s.marks[f.key];
                html += '<div class="me-sc-field">'
                    + '<span class="me-sc-field-label">' + f.label + '</span>'
                    + '<input type="text" inputmode="decimal" class="me-sc-field-input mark-input"'
                    + ' data-student-id="' + s.id + '" data-student-index="' + idx + '"'
                    + ' data-mark-key="' + f.key + '" data-max="' + f.max + '"'
                    + ' value="' + (val !== null && val !== undefined ? val : '') + '"'
                    + ' placeholder="/' + f.max + '"'
                    + (isLocked ? ' disabled' : '')
                    + '>'
                    + '<span class="me-sc-field-max">/' + f.max + '</span>'
                    + '</div>';
            });

            // Extra CA: Conduct, Handwriting, Creativity
            EXTRA_CA_FIELDS.forEach(function(f) {
                var val = s.marks[f.key];
                html += '<div class="me-sc-field">'
                    + '<span class="me-sc-field-label">' + f.label + '</span>'
                    + '<input type="text" inputmode="decimal" class="me-sc-field-input mark-input"'
                    + ' data-student-id="' + s.id + '" data-student-index="' + idx + '"'
                    + ' data-mark-key="' + f.key + '" data-max="' + f.max + '"'
                    + ' value="' + (val !== null && val !== undefined ? val : '') + '"'
                    + ' placeholder="/' + f.max + '"'
                    + (isLocked ? ' disabled' : '')
                    + '>'
                    + '<span class="me-sc-field-max">/' + f.max + '</span>'
                    + '</div>';
            });

            html += '</div>';

            // Exam Section
            html += '<div class="me-sc-section-label exam-label">Examination</div>';
            html += '<div class="me-sc-exam-grid">';

            EXAM_FIELDS.forEach(function(f) {
                var val = s.marks[f.key];
                html += '<div class="me-sc-field field-exam">'
                    + '<span class="me-sc-field-label">' + f.label + '</span>'
                    + '<input type="text" inputmode="decimal" class="me-sc-field-input mark-input exam-input"'
                    + ' data-student-id="' + s.id + '" data-student-index="' + idx + '"'
                    + ' data-mark-key="' + f.key + '" data-max="' + f.max + '"'
                    + ' value="' + (val !== null && val !== undefined ? val : '') + '"'
                    + ' placeholder="/' + f.max + '"'
                    + (isLocked ? ' disabled' : '')
                    + '>'
                    + '<span class="me-sc-field-max">/' + f.max + '</span>'
                    + '</div>';
            });

            html += '</div>';

            html += '</div>'; // end me-sc-body

            // Card Totals Footer
            html += '<div class="me-sc-totals">';
            html += '<div class="me-sc-total-item">'
                + '<div class="me-sc-total-label">CA /30</div>'
                + '<div class="me-sc-total-value ca-val" id="cardCaTotal_' + s.id + '">' + (caTotal !== null && caTotal !== undefined ? parseFloat(caTotal).toFixed(1) : '-') + '</div>'
                + '</div>';
            html += '<div class="me-sc-total-item">'
                + '<div class="me-sc-total-label">Exam /70</div>'
                + '<div class="me-sc-total-value exam-val" id="cardExamTotal_' + s.id + '">' + (examTotal !== null && examTotal !== undefined ? parseFloat(examTotal).toFixed(1) : '-') + '</div>'
                + '</div>';
            html += '<div class="me-sc-total-item">'
                + '<div class="me-sc-total-label">Total /100</div>'
                + '<div class="me-sc-total-value grand-val" id="cardGrandTotal_' + s.id + '">' + (grandTotal !== null && grandTotal !== undefined ? parseFloat(grandTotal).toFixed(1) : '-') + '</div>'
                + '</div>';
            html += '<div class="me-sc-total-item">'
                + '<div class="me-sc-total-label">Grade</div>'
                + '<div class="me-sc-total-value"><span class="me-grade-badge ' + gradeClass + '" id="cardGrade_' + s.id + '">' + grade + '</span></div>'
                + '</div>';
            html += '</div>';

            html += '</div>'; // end me-student-card
        });

        cardSlider.innerHTML = html;

        // Attach listeners to all inputs
        attachMarkInputListeners();

        // Initialize carousel
        currentStudentIndex = 0;
        buildDots();
        showStudent(0, false);
        updateCarouselNav();
    }

    // ========== MARK INPUT LISTENERS ==========
    function attachMarkInputListeners() {
        document.querySelectorAll('.mark-input').forEach(function(inp) {
            // KEYDOWN: Intercept period/comma for locale compatibility
            inp.addEventListener('keydown', function(e) {
                // Allow navigation & control keys
                if ([8, 9, 13, 27, 46, 35, 36, 37, 38, 39, 40].indexOf(e.keyCode) !== -1) return;
                if ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88, 90].indexOf(e.keyCode) !== -1) return;

                // Enter moves focus to next input
                if (e.keyCode === 13) {
                    e.preventDefault();
                    // Find all mark inputs in order and focus the next one
                    var allInputs = Array.from(document.querySelectorAll('.mark-input'));
                    var currentIdx = allInputs.indexOf(this);
                    if (currentIdx < allInputs.length - 1) {
                        allInputs[currentIdx + 1].focus();
                    }
                    return;
                }

                // Period/comma/Amharic decimal interception
                var isPeriodKey = (e.keyCode === 190 || e.keyCode === 110 || e.keyCode === 188
                    || e.code === 'Period' || e.code === 'NumpadDecimal' || e.code === 'Comma'
                    || e.key === '\u135E' || e.key === '\u1361' || e.key === ',' || e.key === '\u00B7'
                    || e.key === '\uFF0E' || e.key === '\u3002');

                if (isPeriodKey) {
                    e.preventDefault();
                    if (this.value.indexOf('.') === -1) {
                        var start = this.selectionStart;
                        var end = this.selectionEnd;
                        this.value = this.value.substring(0, start) + '.' + this.value.substring(end);
                        this.setSelectionRange(start + 1, start + 1);
                        this.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    return;
                }

                // Allow digit keys
                if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105)) return;

                // Block everything else
                e.preventDefault();
            });

            // INPUT: Handle paste, clean, recalc, auto-save
            inp.addEventListener('input', function() {
                var raw = this.value;
                // Convert alternate decimal characters
                var cleaned = raw.replace(/[\uFF0C\u3001\u135E\u1361\u00B7\uFF0E\u3002]/g, '.');
                // Remove non-numeric except dots
                cleaned = cleaned.replace(/[^0-9.]/g, '');
                // Keep only first dot
                var parts = cleaned.split('.');
                if (parts.length > 2) {
                    cleaned = parts[0] + '.' + parts.slice(1).join('');
                }
                // Limit to 1 decimal place
                if (cleaned.indexOf('.') !== -1) {
                    var dp = cleaned.split('.');
                    if (dp[1].length > 1) {
                        cleaned = dp[0] + '.' + dp[1].substring(0, 1);
                    }
                }
                if (cleaned !== raw) {
                    var selStart = this.selectionStart;
                    this.value = cleaned;
                    this.setSelectionRange(selStart, selStart);
                }

                var studentId = this.dataset.studentId;
                var markKey = this.dataset.markKey;
                var value = this.value;

                // Update local data
                var idx = parseInt(this.dataset.studentIndex);
                if (students[idx]) {
                    students[idx].marks[markKey] = value;
                    recalcStudent(idx);
                }

                // Show editing status immediately when user types
                setGlobalSaveStatus('editing', 'Editing...');

                // Debounced auto-save
                var timerKey = studentId + '_' + markKey;
                if (saveTimers[timerKey]) clearTimeout(saveTimers[timerKey]);
                saveTimers[timerKey] = setTimeout(function() { saveMark(studentId, markKey, value); }, 900);
            });

            // BLUR: Enforce max, immediate save
            inp.addEventListener('blur', function() {
                enforceMaxValue(this);
                var studentId = this.dataset.studentId;
                var markKey = this.dataset.markKey;
                var timerKey = studentId + '_' + markKey;
                if (saveTimers[timerKey]) { clearTimeout(saveTimers[timerKey]); delete saveTimers[timerKey]; }
                saveMark(studentId, markKey, this.value);
            });

            // FOCUS: Highlight the active card & navigate carousel to it
            inp.addEventListener('focus', function() {
                var card = this.closest('.me-student-card');
                document.querySelectorAll('.me-student-card.card-active').forEach(function(c) { c.classList.remove('card-active'); });
                if (card) card.classList.add('card-active');

                // Navigate carousel to the focused student's card
                var idx = parseInt(this.dataset.studentIndex);
                if (!isNaN(idx) && idx !== currentStudentIndex) {
                    showStudent(idx);
                }
            });
        });
    }

    // ========== ENFORCE MAX VALUE ==========
    function enforceMaxValue(inp) {
        var max = parseFloat(inp.dataset.max);
        if (inp.value === '') return;
        var v = parseFloat(inp.value);
        if (isNaN(v)) { inp.value = ''; return; }
        if (!isNaN(max) && v > max) v = max;
        if (v < 0) v = 0;
        inp.value = Math.round(v * 10) / 10;
    }

    // ========== RECALCULATE STUDENT TOTALS ==========
    function recalcStudent(idx) {
        var s = students[idx];
        if (!s) return;

        var caRaw = 0;
        CA_KEYS.forEach(function(k) { caRaw += parseFloat(s.marks[k]) || 0; });
        EXTRA_CA_KEYS.forEach(function(k) { caRaw += parseFloat(s.marks[k]) || 0; });

        var examRaw = 0;
        EXAM_KEYS.forEach(function(k) { examRaw += parseFloat(s.marks[k]) || 0; });

        var caScaled = Math.round((caRaw / 70) * 30 * 100) / 100;
        var examTotal = Math.min(examRaw, 70);
        var grandTotal = Math.round((caScaled + examTotal) * 100) / 100;

        s.marks.ca_total = caScaled;
        s.marks.exam_total = examTotal;
        s.marks.grand_total = grandTotal;

        var caEl = document.getElementById('cardCaTotal_' + s.id);
        var exEl = document.getElementById('cardExamTotal_' + s.id);
        var gtEl = document.getElementById('cardGrandTotal_' + s.id);
        var grEl = document.getElementById('cardGrade_' + s.id);

        if (caEl) caEl.textContent = caScaled.toFixed(1);
        if (exEl) exEl.textContent = examTotal.toFixed(1);
        if (gtEl) gtEl.textContent = grandTotal.toFixed(1);

        var grade = calcGrade(grandTotal);
        s.marks.grade = grade;
        if (grEl) {
            grEl.textContent = grade;
            grEl.className = 'me-grade-badge ' + getGradeClass(grade);
        }
    }

    function calcGrade(total) {
        if (total >= 90) return 'A+';
        if (total >= 80) return 'A';
        if (total >= 75) return 'A-';
        if (total >= 70) return 'B+';
        if (total >= 65) return 'B';
        if (total >= 60) return 'B-';
        if (total >= 55) return 'C+';
        if (total >= 50) return 'C';
        if (total >= 45) return 'C-';
        if (total >= 40) return 'D';
        return 'F';
    }

    function getGradeClass(grade) {
        if (!grade || grade === '-') return 'me-grade-F';
        var g = grade.charAt(0);
        if (g === 'A') return 'me-grade-A';
        if (g === 'B') return 'me-grade-B';
        if (g === 'C') return 'me-grade-C';
        if (g === 'D') return 'me-grade-D';
        return 'me-grade-F';
    }

    // ========== CAROUSEL NAVIGATION ==========
    function showStudent(index, animate) {
        if (index < 0) index = 0;
        if (index >= students.length) index = students.length - 1;
        if (students.length === 0) return;

        currentStudentIndex = index;

        // Slide the slider
        var offset = -index * 100;
        if (animate === false) {
            cardSlider.style.transition = 'none';
            cardSlider.style.transform = 'translateX(' + offset + '%)';
            // Force reflow then restore transition
            cardSlider.offsetHeight; // eslint-disable-line no-unused-expressions
            cardSlider.style.transition = '';
        } else {
            cardSlider.style.transform = 'translateX(' + offset + '%)';
        }

        // Update active card highlight
        document.querySelectorAll('.me-student-card.card-active').forEach(function(c) { c.classList.remove('card-active'); });
        var activeCard = cardSlider.querySelector('.me-student-card[data-student-index="' + index + '"]');
        if (activeCard) activeCard.classList.add('card-active');

        updateCarouselNav();
    }

    function navigatePrev() {
        if (currentStudentIndex > 0) {
            flushPendingSaves();
            showStudent(currentStudentIndex - 1);
        }
    }

    function navigateNext() {
        if (currentStudentIndex < students.length - 1) {
            flushPendingSaves();
            showStudent(currentStudentIndex + 1);
        }
    }

    function updateCarouselNav() {
        // Update counter
        currentStudentNum.textContent = students.length > 0 ? (currentStudentIndex + 1) : 0;
        totalStudentNum.textContent = students.length;

        // Update button states
        btnPrevStudent.disabled = (currentStudentIndex <= 0);
        btnNextStudent.disabled = (currentStudentIndex >= students.length - 1);

        // Update dots
        var dots = carouselDots.querySelectorAll('.me-carousel-dot');
        dots.forEach(function(dot, i) {
            if (i === currentStudentIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });

        // Update progress bar
        if (students.length > 0) {
            var pct = ((currentStudentIndex + 1) / students.length) * 100;
            carouselProgressBar.style.width = pct + '%';
        } else {
            carouselProgressBar.style.width = '0%';
        }

        // Show/hide dots vs progress: use dots for <= 20 students, progress bar for > 20
        if (students.length <= 20) {
            carouselDots.style.display = 'flex';
            document.getElementById('carouselProgress').style.display = 'none';
        } else {
            carouselDots.style.display = 'none';
            document.getElementById('carouselProgress').style.display = 'block';
        }
    }

    function buildDots() {
        var html = '';
        var maxDots = Math.min(students.length, 30); // cap at 30 dots for performance
        for (var i = 0; i < maxDots; i++) {
            html += '<button type="button" class="me-carousel-dot' + (i === 0 ? ' active' : '') + '" data-dot-index="' + i + '" aria-label="Student ' + (i + 1) + '"></button>';
        }
        carouselDots.innerHTML = html;
    }

    // Click handler for dots (event delegation)
    carouselDots.addEventListener('click', function(e) {
        var dot = e.target.closest('.me-carousel-dot');
        if (dot) {
            var idx = parseInt(dot.dataset.dotIndex);
            if (!isNaN(idx)) {
                flushPendingSaves();
                showStudent(idx);
            }
        }
    });

    // Prev/Next button handlers
    btnPrevStudent.addEventListener('click', function() { navigatePrev(); });
    btnNextStudent.addEventListener('click', function() { navigateNext(); });

    // Flush any pending debounced saves for current student before navigating
    function flushPendingSaves() {
        var pendingKeys = Object.keys(saveTimers);
        pendingKeys.forEach(function(timerKey) {
            if (saveTimers[timerKey]) {
                clearTimeout(saveTimers[timerKey]);
                var parts = timerKey.split('_');
                var studentId = parts[0];
                var markKey = parts.slice(1).join('_');
                // Find the input to get current value
                var inp = document.querySelector('.mark-input[data-student-id="' + studentId + '"][data-mark-key="' + markKey + '"]');
                if (inp) {
                    enforceMaxValue(inp);
                    saveMark(studentId, markKey, inp.value);
                }
                delete saveTimers[timerKey];
            }
        });
    }

    // ========== TOUCH / SWIPE HANDLERS ==========
    cardsContainer.addEventListener('touchstart', function(e) {
        // Don't capture swipe if touching an input
        if (e.target.closest('.me-sc-field-input')) return;
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        touchCurrentX = touchStartX;
        isSwiping = false;
    }, { passive: true });

    cardsContainer.addEventListener('touchmove', function(e) {
        if (e.target.closest('.me-sc-field-input')) return;
        touchCurrentX = e.touches[0].clientX;
        var diffX = touchCurrentX - touchStartX;
        var diffY = e.touches[0].clientY - touchStartY;

        // Determine if this is a horizontal swipe
        if (!isSwiping && Math.abs(diffX) > 10 && Math.abs(diffX) > Math.abs(diffY)) {
            isSwiping = true;
        }

        if (isSwiping) {
            // Live drag effect
            var baseOffset = -currentStudentIndex * 100;
            var containerWidth = cardsContainer.offsetWidth;
            var dragPercent = (diffX / containerWidth) * 100;
            cardSlider.style.transition = 'none';
            cardSlider.style.transform = 'translateX(' + (baseOffset + dragPercent) + '%)';
        }
    }, { passive: true });

    cardsContainer.addEventListener('touchend', function(e) {
        if (!isSwiping) return;
        isSwiping = false;

        // Restore transition
        cardSlider.style.transition = '';

        var diffX = touchCurrentX - touchStartX;
        if (diffX > swipeThreshold) {
            navigatePrev();
        } else if (diffX < -swipeThreshold) {
            navigateNext();
        } else {
            // Snap back to current
            showStudent(currentStudentIndex);
        }
    }, { passive: true });

    // ========== KEYBOARD NAVIGATION ==========
    document.addEventListener('keydown', function(e) {
        // Only handle arrow keys when mark entry area is visible
        if (markEntryArea.classList.contains('d-none')) return;

        // Don't navigate when focus is inside an input (allow typing)
        var activeEl = document.activeElement;
        if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'SELECT' || activeEl.tagName === 'TEXTAREA')) return;

        if (e.key === 'ArrowLeft' || e.keyCode === 37) {
            e.preventDefault();
            navigatePrev();
        } else if (e.key === 'ArrowRight' || e.keyCode === 39) {
            e.preventDefault();
            navigateNext();
        }
    });

    // ========== SAVE MARK ==========
    function saveMark(studentId, markKey, value, isRetry) {
        var ayId = filterAy.value;
        var termId = filterTerm.value;
        var classId = filterClass.value;
        var sectionId = filterSection.value;
        var subjectId = filterSubject.value;

        if (!ayId || !termId || !classId || !sectionId || !subjectId) return;

        var markValue = (value === '' || value === undefined || value === null) ? null : value;

        setGlobalSaveStatus('saving', 'Saving...');

        fetch(API_SAVE, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                student_id: studentId,
                academic_year_id: ayId,
                term_id: termId,
                class_id: classId,
                section_id: sectionId,
                subject_id: subjectId,
                mark_key: markKey,
                mark_value: markValue
            })
        })
        .then(function(r) {
            // ── Handle 419 CSRF Token Mismatch ──
            if (r.status === 419) {
                if (isRetry) {
                    // Already retried once — give up and alert the user
                    setGlobalSaveStatus('error', 'Session Expired');
                    backupMarksToLocalStorage();
                    alert('Your session has expired and marks could not be saved.\n\nYour marks have been backed up to local storage. Please refresh the page and try again.');
                    throw new Error('Session expired (419 after retry)');
                }
                // First 419 — refresh CSRF token and retry the save
                console.log('[MarkEntry] 419 detected, refreshing CSRF token and retrying...');
                return refreshCSRFToken().then(function(newToken) {
                    return saveMark(studentId, markKey, value, true);
                });
            }

            if (!r.ok) {
                // Try to parse as JSON for error message, fall back to status code
                return r.text().then(function(text) {
                    var errMsg = 'Server error ' + r.status;
                    try {
                        var jsonErr = JSON.parse(text);
                        errMsg = jsonErr.error || jsonErr.message || errMsg;
                    } catch(e) {
                        // Response is HTML (e.g. login redirect page) — check for redirect
                        if (r.status === 401 || text.indexOf('login') !== -1) {
                            errMsg = 'Session expired';
                        }
                    }
                    throw new Error(errMsg);
                });
            }
            return r.json();
        })
        .then(function(res) {
            if (res && res.success) {
                // Refresh CSRF token from server response — this is the MOST RELIABLE
                // way to prevent 419 errors. Every successful save returns a fresh token.
                if (res.csrf_token) {
                    updateCSRFToken(res.csrf_token);
                    lastKeepaliveTime = Date.now(); // Save itself counts as activity
                }

                setGlobalSaveStatus('saved', 'Saved \u2713');

                // Flash the input green
                var inp = document.querySelector('.mark-input[data-student-id="' + studentId + '"][data-mark-key="' + markKey + '"]');
                if (inp) {
                    inp.classList.add('input-saved');
                    setTimeout(function() { inp.classList.remove('input-saved'); }, 1200);
                }

                // Update totals from server response
                var idx = students.findIndex(function(s) { return s.id == studentId; });
                if (idx !== -1 && res.entry) {
                    if (res.ca_total !== undefined) students[idx].marks.ca_total = res.ca_total;
                    if (res.exam_total !== undefined) students[idx].marks.exam_total = res.exam_total;
                    if (res.grand_total !== undefined) students[idx].marks.grand_total = res.grand_total;
                    if (res.grade !== undefined) students[idx].marks.grade = res.grade;

                    var caEl = document.getElementById('cardCaTotal_' + studentId);
                    var exEl = document.getElementById('cardExamTotal_' + studentId);
                    var gtEl = document.getElementById('cardGrandTotal_' + studentId);
                    var grEl = document.getElementById('cardGrade_' + studentId);
                    if (caEl) caEl.textContent = res.ca_total !== undefined ? parseFloat(res.ca_total).toFixed(1) : '-';
                    if (exEl) exEl.textContent = res.exam_total !== undefined ? parseFloat(res.exam_total).toFixed(1) : '-';
                    if (gtEl) gtEl.textContent = res.grand_total !== undefined ? parseFloat(res.grand_total).toFixed(1) : '-';
                    if (grEl && res.grade) {
                        grEl.textContent = res.grade;
                        grEl.className = 'me-grade-badge ' + getGradeClass(res.grade);
                    }
                }

                // Save succeeded — clear localStorage backup after a short delay
                // (keep it briefly in case the next save fails)
                setTimeout(clearLocalStorageBackup, 5000);
            } else {
                setGlobalSaveStatus('error', 'Not Saved');
                var inp = document.querySelector('.mark-input[data-student-id="' + studentId + '"][data-mark-key="' + markKey + '"]');
                if (inp) {
                    inp.classList.add('input-error');
                    setTimeout(function() { inp.classList.remove('input-error'); }, 2000);
                }
            }
        })
        .catch(function(err) {
            setGlobalSaveStatus('error', 'Not Saved');
            var inp = document.querySelector('.mark-input[data-student-id="' + studentId + '"][data-mark-key="' + markKey + '"]');
            if (inp) {
                inp.classList.add('input-error');
                setTimeout(function() { inp.classList.remove('input-error'); }, 2000);
            }
            console.error('Save error:', err);
        });
    }

    var saveIconMap = {
        saving: '<i class="fas fa-spinner fa-spin"></i>',
        saved: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-exclamation-circle"></i>',
        idle: '<i class="fas fa-check-circle"></i>',
        editing: '<i class="fas fa-pen"></i>'
    };

    function setGlobalSaveStatus(state, text) {
        globalSaveStatus.className = 'me-save-badge ' + state;
        globalSaveStatus.innerHTML = (saveIconMap[state] || '') + ' ' + text;
    }

    // ========== UI STATE HELPERS ==========
    function hideMarkEntry() {
        markEntryArea.classList.add('d-none');
        emptyState.classList.remove('d-none');
        noStudentsState.classList.add('d-none');
        loadingState.classList.add('d-none');
        expandFilterPanel();
    }

    function showMarkEntry() {
        markEntryArea.classList.remove('d-none');
        emptyState.classList.add('d-none');
        noStudentsState.classList.add('d-none');
        loadingState.classList.add('d-none');
        collapseFilterPanel();
    }

    function showLoading() {
        loadingState.classList.remove('d-none');
        markEntryArea.classList.add('d-none');
        emptyState.classList.add('d-none');
        noStudentsState.classList.add('d-none');
    }

    function showNoStudents() {
        noStudentsState.classList.remove('d-none');
        markEntryArea.classList.add('d-none');
        emptyState.classList.add('d-none');
        loadingState.classList.add('d-none');
    }

    // ========== FILTER PANEL COLLAPSE/EXPAND ==========
    function collapseFilterPanel() {
        var panel = document.getElementById('filterPanel');
        var summary = document.getElementById('filterSummary');
        panel.classList.add('me-filter-collapsed');
        summary.classList.add('visible');
        updateFilterSummary();
    }

    function expandFilterPanel() {
        var panel = document.getElementById('filterPanel');
        var summary = document.getElementById('filterSummary');
        panel.classList.remove('me-filter-collapsed');
        summary.classList.remove('visible');
    }

    window.showFilterPanel = function() {
        expandFilterPanel();
        document.getElementById('filterPanel').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    function updateFilterSummary() {
        var summaryText = document.getElementById('filterSummaryText');
        var parts = [];

        var className = filterClass.selectedOptions[0] ? filterClass.selectedOptions[0].textContent : '';
        var sectionName = filterSection.selectedOptions[0] ? filterSection.selectedOptions[0].textContent : '';
        var subjectName = filterSubject.selectedOptions[0] ? filterSubject.selectedOptions[0].textContent : '';

        if (className) parts.push(className);
        if (sectionName) parts.push(sectionName);
        if (subjectName) parts.push(subjectName);

        summaryText.innerHTML = parts.map(function(p) {
            return '<span class="me-filter-summary-chip"><i class="fas fa-check"></i> ' + p + '</span>';
        }).join('');
    }

    // ========== UTILITY ==========
    function getInitials(name) {
        if (!name) return '?';
        var parts = name.trim().split(/\s+/);
        if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
        return parts[0][0].toUpperCase();
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text || ''));
        return div.innerHTML;
    }

    // ========== START ==========
    init();

    // Backup marks before the page is unloaded (navigation, close, refresh)
    window.addEventListener('beforeunload', function() {
        if (students.length > 0) {
            backupMarksToLocalStorage();
        }
    });
})();
</script>
@endpush
