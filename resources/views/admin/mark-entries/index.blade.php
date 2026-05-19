@extends('layouts.admin')
@section('title', 'Mark Entry')

@push('styles')
<style>
/* ===== MARK ENTRY INDEX - MODERN DESIGN ===== */
.me-page { animation: meFadeIn 0.4s ease-out; }
@keyframes meFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

/* Page Header */
.me-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.me-header-left { flex: 1; }
.me-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.me-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }

/* Breadcrumb */
.me-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.me-breadcrumb li { color: #adb5bd; }
.me-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.me-breadcrumb li a:hover { color: #4361ee; }
.me-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.me-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Current Term Info Bar */
.me-term-bar { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
.me-term-chip { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 0.85rem; font-weight: 600; color: #1a1a2e; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
.me-term-chip i { font-size: 0.9rem; }
.me-term-chip.chip-ay i { color: #4361ee; }
.me-term-chip.chip-term i { color: #10b981; }
.me-term-chip.chip-lock i { color: #ef4444; }
.me-term-chip.chip-unlock i { color: #10b981; }

/* Filter Panel */
.me-filter-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.25rem; }
.me-filter-header { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: #fafbfc; }
.me-filter-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; background: #eef2ff; color: #4361ee; }
.me-filter-title { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.me-filter-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.1rem 0 0; }
.me-filter-body { padding: 1.25rem 1.5rem; }
.me-filter-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; }
.me-filter-group { display: flex; flex-direction: column; }
.me-filter-label { font-weight: 600; color: #374151; margin-bottom: 0.4rem; font-size: 0.85rem; }
.me-filter-label .me-required { color: #ef4444; margin-left: 2px; }
.me-filter-select { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.6rem 2.2rem 0.6rem 0.8rem; font-size: 0.88rem; color: #1a1a2e; background: #fff; appearance: none; cursor: pointer; transition: all 0.2s; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.6rem center; background-repeat: no-repeat; background-size: 1.15rem; }
.me-filter-select:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.me-filter-select:disabled { background: #f9fafb; color: #9ca3af; cursor: not-allowed; }

/* Lock Status Banner */
.me-lock-banner { border-radius: 12px; padding: 0.85rem 1.25rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.75rem; font-weight: 500; font-size: 0.9rem; animation: meFadeIn 0.3s ease-out; }
.me-lock-banner i { font-size: 1.15rem; flex-shrink: 0; }
.me-lock-banner.locked { background: #fef2f2; border: 1.5px solid #fecaca; color: #991b1b; }
.me-lock-banner.locked i { color: #dc2626; }
.me-lock-banner.unlocked { background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; }
.me-lock-banner.unlocked i { color: #059669; }

/* Mark Entry Table Card */
.me-table-card { background: #fff; border-radius: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden; }
.me-table-card-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: #fafbfc; gap: 1rem; flex-wrap: wrap; }
.me-table-card-header-left { display: flex; align-items: center; gap: 0.75rem; }
.me-table-card-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; background: #eef2ff; color: #4361ee; }
.me-table-card-title { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.me-table-card-subtitle { font-size: 0.82rem; color: #9ca3af; margin: 0.1rem 0 0; }
.me-table-card-header-right { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
.me-student-nav { display: flex; align-items: center; gap: 0.5rem; }
.me-nav-btn { width: 34px; height: 34px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; color: #6b7280; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 0.85rem; }
.me-nav-btn:hover { border-color: #4361ee; color: #4361ee; background: #eef2ff; }
.me-nav-btn:disabled { opacity: 0.35; cursor: not-allowed; background: #f9fafb; }
.me-nav-counter { font-size: 0.82rem; font-weight: 600; color: #6b7280; min-width: 60px; text-align: center; }
.me-save-badge { font-size: 0.72rem; padding: 0.2rem 0.6rem; border-radius: 6px; font-weight: 600; white-space: nowrap; }
.me-save-badge.saving { background: #fef3c7; color: #d97706; }
.me-save-badge.saved { background: #d1fae5; color: #059669; }
.me-save-badge.error { background: #fee2e2; color: #dc2626; }
.me-save-badge.idle { background: #f3f4f6; color: #9ca3af; }

/* Field Badge - shows which mark field is active */
.me-field-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 10px; font-size: 0.82rem; font-weight: 700; margin-bottom: 0.75rem; animation: meFadeIn 0.3s ease-out; }
.me-field-badge.badge-ca { background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1d4ed8; }
.me-field-badge.badge-extra-ca { background: #f0fdf4; border: 1.5px solid #bbf7d0; color: #15803d; }
.me-field-badge.badge-exam { background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #059669; }
.me-field-badge.badge-all { background: #f5f3ff; border: 1.5px solid #ddd6fe; color: #7c3aed; }
.me-field-badge i { font-size: 0.85rem; }

/* Mark Entry Table */
.me-table-wrapper { overflow-x: auto; max-height: 65vh; overflow-y: auto; }
.me-table-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
.me-table-wrapper::-webkit-scrollbar-track { background: #f9fafb; }
.me-table-wrapper::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
.me-table-wrapper::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
.me-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.82rem; }
.me-table thead th { position: sticky; top: 0; z-index: 10; padding: 9px 8px; text-align: center; font-weight: 700; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.3px; color: #6b7280; background: #f9fafb; border-bottom: 2px solid #e5e7eb; white-space: nowrap; }
.me-table thead th.col-sticky { position: sticky; left: 0; z-index: 20; background: #f9fafb; min-width: 160px; text-align: left; }
.me-table thead th.section-ca { background: #eff6ff; border-bottom-color: #bfdbfe; color: #1d4ed8; }
.me-table thead th.section-exam { background: #ecfdf5; border-bottom-color: #a7f3d0; color: #059669; }
.me-table thead th.section-total { background: #f5f3ff; border-bottom-color: #ddd6fe; color: #7c3aed; }
.me-table thead th.section-total-ca { background: #eff6ff; border-bottom-color: #bfdbfe; color: #1d4ed8; }
.me-table thead th.section-total-exam { background: #ecfdf5; border-bottom-color: #a7f3d0; color: #059669; }
.me-table thead th.section-grade { background: #fefce8; border-bottom-color: #fde68a; color: #a16207; }
.me-table thead th.th-sub { font-size: 0.68rem; font-weight: 500; color: #9ca3af; padding-top: 0; }

.me-table tbody td { padding: 4px 4px; border-bottom: 1px solid #f0f0f0; color: #1f2937; vertical-align: middle; text-align: center; }
.me-table tbody td.col-sticky { position: sticky; left: 0; z-index: 5; background: #fff; text-align: left; min-width: 160px; border-right: 1px solid #e5e7eb; }
.me-table tbody tr { transition: background 0.15s; }
.me-table tbody tr:hover { background: rgba(67,97,238,0.03); }
.me-table tbody tr:hover td.col-sticky { background: #f8f9ff; }
.me-table tbody tr.row-highlight { background: rgba(67,97,238,0.07); }
.me-table tbody tr.row-highlight td.col-sticky { background: #eef2ff; }

/* Single-field mode table adjustments */
.me-table.me-table-single { font-size: 0.9rem; }
.me-table.me-table-single thead th { font-size: 0.78rem; padding: 10px 12px; }
.me-table.me-table-single tbody td { padding: 8px 8px; }
.me-table.me-table-single td.col-row-num { min-width: 40px; text-align: center; }

/* Student Name Cell */
.me-student-cell { display: flex; align-items: center; gap: 8px; padding: 2px 8px; }
.me-student-avatar { width: 30px; height: 30px; border-radius: 8px; background: linear-gradient(135deg, #4361ee, #818cf8); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; flex-shrink: 0; }
.me-student-name-text { font-weight: 600; color: #1a1a2e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.me-student-roll { font-size: 0.68rem; color: #9ca3af; display: block; }

/* Mark Input Cell */
.me-mark-input { width: 100%; min-width: 42px; border: 1.5px solid #e5e7eb; border-radius: 6px; outline: none; text-align: center; padding: 4px 2px; font-size: 0.82rem; font-weight: 600; color: #1a1a2e; background: #fff; transition: all 0.2s; }
.me-mark-input:focus { border-color: #4361ee; box-shadow: 0 0 0 2px rgba(67,97,238,0.12); background: #f8f9ff; }
.me-mark-input.exam-input:focus { border-color: #10b981; box-shadow: 0 0 0 2px rgba(16,185,129,0.12); background: #f0fdf4; }
.me-mark-input:disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; border-color: #e5e7eb; }
.me-mark-input.input-saved { border-color: #10b981; background: #ecfdf5; }
.me-mark-input.input-error { border-color: #ef4444; background: #fef2f2; }

/* Large Mark Input for single-field mode */
.me-mark-input-large { width: 100%; min-width: 80px; max-width: 120px; border: 2px solid #4361ee; border-radius: 10px; outline: none; text-align: center; padding: 10px 8px; font-size: 1.1rem; font-weight: 700; color: #1a1a2e; background: #f8f9ff; transition: all 0.2s; }
.me-mark-input-large:focus { border-color: #3b52d4; box-shadow: 0 0 0 3px rgba(67,97,238,0.2); background: #fff; }
.me-mark-input-large.exam-input { border-color: #10b981; background: #f0fdf4; }
.me-mark-input-large.exam-input:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(16,185,129,0.2); background: #fff; }
.me-mark-input-large.extra-ca-input { border-color: #15803d; background: #f0fdf4; }
.me-mark-input-large.extra-ca-input:focus { border-color: #15803d; box-shadow: 0 0 0 3px rgba(21,128,61,0.2); background: #fff; }
.me-mark-input-large:disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; border-color: #e5e7eb; }
.me-mark-input-large.input-saved { border-color: #10b981; background: #ecfdf5; }
.me-mark-input-large.input-error { border-color: #ef4444; background: #fef2f2; }

/* Total Cell */
.me-total-cell { font-weight: 700; font-size: 0.85rem; padding: 4px 6px; }
.me-total-cell.ca-total { color: #1d4ed8; background: #eff6ff; border-radius: 6px; }
.me-total-cell.exam-total { color: #059669; background: #ecfdf5; border-radius: 6px; }
.me-total-cell.grand-total { color: #7c3aed; background: #f5f3ff; border-radius: 6px; font-size: 0.92rem; }
.me-total-cell.grade-cell { font-size: 0.92rem; }
.me-grade-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; padding: 2px 8px; border-radius: 6px; font-weight: 800; font-size: 0.82rem; }
.me-grade-A { background: rgba(52,211,153,0.15); color: #059669; }
.me-grade-B { background: rgba(96,165,250,0.15); color: #2563eb; }
.me-grade-C { background: rgba(251,191,36,0.15); color: #d97706; }
.me-grade-D { background: rgba(251,146,60,0.15); color: #ea580c; }
.me-grade-F { background: rgba(248,113,113,0.15); color: #dc2626; }

/* Empty State */
.me-empty { text-align: center; padding: 3rem 1.5rem; background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
.me-empty i { font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
.me-empty p { color: #9ca3af; font-size: 0.95rem; margin: 0; }
.me-empty-hint { font-size: 0.82rem; color: #b0b8c4; margin-top: 0.5rem; }

/* Keyboard hint */
.me-keyboard-hint { text-align: center; padding: 0.5rem; font-size: 0.75rem; color: #9ca3af; }
.me-keyboard-hint kbd { background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 4px; padding: 0.1rem 0.4rem; font-size: 0.7rem; font-family: inherit; }

/* Loading Spinner */
.me-loading { display: flex; align-items: center; justify-content: center; padding: 3rem; gap: 0.75rem; color: #9ca3af; font-size: 0.9rem; }
.me-spinner { width: 24px; height: 24px; border: 3px solid #e5e7eb; border-top-color: #4361ee; border-radius: 50%; animation: meSpin 0.7s linear infinite; }
@keyframes meSpin { to { transform: rotate(360deg); } }

/* Row number */
.me-row-num { font-weight: 600; color: #9ca3af; font-size: 0.75rem; min-width: 24px; text-align: center; }

/* Filter collapse/expand */
.me-filter-card.me-filter-collapsed .me-filter-body { display: none; }
.me-filter-card.me-filter-collapsed .me-filter-header { border-bottom: none; padding: 0.65rem 1rem; }

/* Compact summary bar when filter is collapsed */
.me-filter-summary {
    display: none;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    background: #f0fdf4;
    border: 1.5px solid #a7f3d0;
    border-radius: 10px;
    margin-bottom: 1rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: #065f46;
    flex-wrap: wrap;
    animation: meFadeIn 0.3s ease-out;
}
.me-filter-summary.visible { display: flex; }
.me-filter-summary-chip {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; background: #fff; border: 1px solid #d1fae5;
    border-radius: 6px; font-size: 0.78rem; color: #1a1a2e;
}
.me-filter-summary-chip i { font-size: 0.7rem; color: #10b981; }
.me-filter-change-btn {
    margin-left: auto;
    padding: 4px 12px; border-radius: 6px; border: 1px solid #a7f3d0;
    background: #fff; color: #059669; font-size: 0.78rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s; white-space: nowrap;
}
.me-filter-change-btn:hover { background: #ecfdf5; border-color: #10b981; }

/* Responsive */
@media (max-width: 1200px) {
    .me-filter-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 992px) {
    .me-filter-grid { grid-template-columns: repeat(2, 1fr); }
}

/* ===== CARD VIEW (one student per card — desktop + mobile) ===== */
.me-mobile-card-area { display: block; }
.me-table-card { display: none !important; }
.me-field-badge { display: none !important; }

/* Desktop Card Container */
.me-card-wrapper {
    max-width: 860px; margin: 0 auto;
    background: #fff; border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.07), 0 1px 4px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

/* Desktop Card Nav Bar */
.me-card-nav {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 20px; background: #fafbfc;
    border-bottom: 1px solid #e5e7eb;
}
.me-card-nav-btn {
    width: 42px; height: 42px; border-radius: 12px; border: 1.5px solid #e5e7eb;
    background: #fff; color: #4361ee; font-size: 1rem;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all 0.2s; flex-shrink: 0;
}
.me-card-nav-btn:hover { background: #eef2ff; border-color: #4361ee; }
.me-card-nav-btn:disabled { background: #f9fafb; color: #d1d5db; border-color: #e5e7eb; cursor: not-allowed; }
.me-card-nav-btn:not(:disabled):active { transform: scale(0.95); }
.me-card-student-info { flex: 1; text-align: center; padding: 0 16px; min-width: 0; }
.me-card-student-name { display: block; font-weight: 800; font-size: 1.15rem; color: #1a1a2e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.me-card-student-meta { display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 2px; }
.me-card-counter { font-size: 0.78rem; color: #6b7280; font-weight: 600; }
.me-card-avatar {
    width: 36px; height: 36px; border-radius: 10px;
    background: linear-gradient(135deg, #4361ee, #818cf8); color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.8rem; font-weight: 700; flex-shrink: 0;
}
.me-card-save-badge { margin-left: auto; }

/* Desktop Card Body */
.me-card-body { padding: 20px 24px; }

/* Section Label (desktop) */
.me-card-section-label {
    font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;
    padding: 0 0 8px; margin: 0 0 10px; color: #6b7280;
    border-bottom: 2px solid #e5e7eb;
}
.me-card-section-label.ca-label { color: #1d4ed8; border-bottom-color: #bfdbfe; }
.me-card-section-label.exam-label { color: #059669; border-bottom-color: #a7f3d0; }

/* Desktop CA grid — 5 columns */
.me-card-ca-grid {
    display: grid; grid-template-columns: repeat(5, 1fr);
    gap: 8px; margin-bottom: 16px;
}

/* Desktop Exam grid — 4 columns */
.me-card-exam-grid {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 8px; margin-bottom: 16px;
}

/* Desktop Card Field Item */
.me-card-field {
    display: flex; flex-direction: column; align-items: center;
    background: #f9fafb; border-radius: 10px; padding: 8px 4px 6px;
    border: 1.5px solid #f0f0f0; transition: all 0.2s;
}
.me-card-field:hover { border-color: #e5e7eb; background: #fff; }
.me-card-field.field-exam { background: #f0fdf4; border-color: #d1fae5; }
.me-card-field.field-exam:hover { border-color: #a7f3d0; background: #ecfdf5; }
.me-card-field-label {
    font-size: 0.7rem; font-weight: 700; color: #6b7280;
    text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;
}
.me-card-field.field-exam .me-card-field-label { color: #059669; }
.me-card-field-input {
    width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px;
    text-align: center; padding: 8px 4px; font-size: 1.05rem; font-weight: 700;
    color: #1a1a2e; background: #fff; outline: none; transition: all 0.2s;
}
.me-card-field-input:focus { border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.12); background: #f8f9ff; }
.me-card-field.field-exam .me-card-field-input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.12); background: #f0fdf4; }
.me-card-field-input:disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }
.me-card-field-input.input-saved { border-color: #10b981; background: #ecfdf5; }
.me-card-field-input.input-error { border-color: #ef4444; background: #fef2f2; }
.me-card-field-max { font-size: 0.6rem; color: #9ca3af; margin-top: 2px; }

/* Desktop Card Totals Footer */
.me-card-totals {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 8px; padding: 14px 24px; background: #fafbfc;
    border-top: 1px solid #e5e7eb;
}
.me-card-total-item { text-align: center; }
.me-card-total-label { font-size: 0.65rem; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.3px; }
.me-card-total-value { font-size: 1.1rem; font-weight: 800; margin-top: 2px; }
.me-card-total-value.ca-val { color: #1d4ed8; }
.me-card-total-value.exam-val { color: #059669; }
.me-card-total-value.grand-val { color: #7c3aed; }

/* Keyboard hint for card view */
.me-card-hint {
    text-align: center; padding: 8px; font-size: 0.78rem; color: #9ca3af;
}
.me-card-hint kbd { background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 4px; padding: 1px 6px; font-size: 0.72rem; font-family: inherit; }

@media (max-width: 768px) {
    /* Mobile overrides for card layout */
    .me-card-wrapper { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .me-card-nav { padding: 8px 12px; }
    .me-card-nav-btn { width: 40px; height: 40px; border-radius: 50%; border: none; background: #4361ee; color: #fff; }
    .me-card-nav-btn:disabled { background: #e5e7eb; color: #9ca3af; }
    .me-card-nav-btn:not(:disabled):active { transform: scale(0.92); }
    .me-card-student-name { font-size: 0.95rem; font-weight: 700; }
    .me-card-avatar { width: 30px; height: 30px; font-size: 0.7rem; }
    .me-card-body { padding: 10px 12px; touch-action: pan-y; }
    .me-card-section-label { font-size: 0.7rem; padding: 0 0 4px; margin: 0 0 8px; border-bottom-width: 1.5px; }
    .me-card-ca-grid { grid-template-columns: repeat(3, 1fr); gap: 4px; margin-bottom: 10px; }
    .me-card-exam-grid { grid-template-columns: repeat(2, 1fr); gap: 4px; margin-bottom: 10px; }
    .me-card-field { padding: 4px 2px; border-radius: 6px; border-width: 1px; }
    .me-card-field-label { font-size: 0.62rem; margin-bottom: 2px; }
    .me-card-field-input { font-size: 1rem; padding: 6px 2px; border-radius: 6px; border-width: 1.5px; }
    .me-card-field-max { font-size: 0.55rem; }
    .me-card-totals { grid-template-columns: repeat(4, 1fr); gap: 4px; padding: 8px 12px; }
    .me-card-total-label { font-size: 0.58rem; }
    .me-card-total-value { font-size: 0.85rem; }

    /* Other mobile overrides */
    .me-header { flex-direction: column; align-items: stretch; }
    .me-title { font-size: 1.35rem; }
    .me-filter-grid { grid-template-columns: 1fr 1fr; }
    .me-term-bar { flex-direction: column; align-items: flex-start; }
    .me-filter-summary { font-size: 0.75rem; gap: 0.35rem; padding: 0.5rem 0.75rem; }
}

@media (max-width: 480px) {
    .me-filter-grid { grid-template-columns: 1fr; }
    .me-filter-body { padding: 0.75rem; }
    .me-filter-header { padding: 0.6rem 0.75rem; }
    .me-term-bar { gap: 0.4rem; }
    .me-term-chip { font-size: 0.78rem; padding: 5px 10px; }
    .me-card-field-input { font-size: 0.95rem; padding: 5px 1px; }
    .me-card-student-name { font-size: 0.88rem; }
    .me-card-ca-grid { grid-template-columns: repeat(3, 1fr); }
    .me-card-body { padding: 8px; }
}
</style>
@endpush

@section('content')
<div class="me-page">
    {{-- Page Header --}}
    <div class="me-header">
        <div class="me-header-left">
            <nav aria-label="breadcrumb" class="me-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li class="active">Mark Entry</li>
                </ol>
            </nav>
            <h1 class="me-title">Mark Entry</h1>
            <p class="me-subtitle">Enter and manage student marks by class, section, and subject</p>
        </div>
        <div class="me-header-right d-flex gap-2 align-items-center flex-wrap">
            @can('mark-entry.lock')
                <a href="{{ route('admin.mark-entry-locks.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.82rem;padding:0.45rem 1rem;">
                    <i class="fas fa-lock"></i> Lock Management
                </a>
            @endcan
            @can('mark-entry.permissions')
                <a href="{{ route('admin.mark-entry-permissions.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.82rem;padding:0.45rem 1rem;">
                    <i class="fas fa-key"></i> Permissions
                </a>
            @endcan
        </div>
    </div>

    {{-- Current Term Info Bar --}}
    <div class="me-term-bar" id="termInfoBar">
        <div class="me-term-chip chip-ay">
            <i class="fas fa-calendar-alt"></i>
            <span id="chipAy">{{ $currentAy ? $currentAy->name : 'No Academic Year' }}</span>
        </div>
        <div class="me-term-chip chip-term">
            <i class="fas fa-list-ol"></i>
            <span id="chipTerm">{{ $currentTerm ? $currentTerm->name : 'No Active Term' }}</span>
        </div>
        <div class="me-term-chip" id="chipLock" style="display:none;">
            <i class="fas fa-lock"></i>
            <span id="chipLockText">--</span>
        </div>
    </div>

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
        <div class="me-filter-header">
            <div class="me-filter-icon"><i class="fas fa-filter"></i></div>
            <div>
                <h3 class="me-filter-title">Select Class & Subject</h3>
                <p class="me-filter-desc">Choose academic year, term, class, section, and subject to load students</p>
            </div>
        </div>
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
                    <label class="me-filter-label" for="filterClass">Class <span class="me-required">*</span></label>
                    <select id="filterClass" class="me-filter-select">
                        <option value="">-- Select Class --</option>
                        @foreach ($classes as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }}</option>
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
            <div style="margin-top:1rem;display:flex;gap:0.75rem;align-items:center;">
                <button type="button" class="btn-modern btn-modern-primary" id="btnLoadStudents" style="font-size:0.85rem;padding:0.55rem 1.25rem;" disabled>
                    <i class="fas fa-download"></i> Load Students
                </button>
                <span id="filterHint" style="font-size:0.78rem;color:#9ca3af;">Select all filters above to load students</span>
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

    {{-- Mark Entry Card View (hidden until students load) --}}
    <div id="markEntryArea" class="d-none">
        {{-- Student Card --}}
        <div class="me-card-wrapper" id="studentCardWrapper">
            {{-- Card Navigation Bar --}}
            <div class="me-card-nav">
                <button type="button" class="me-card-nav-btn" id="cardPrev" onclick="cardNavigate(-1)" aria-label="Previous Student" title="Previous Student">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="me-card-student-info">
                    <div class="me-card-student-meta">
                        <div class="me-card-avatar" id="cardAvatar">?</div>
                        <span class="me-card-student-name" id="cardStudentName">--</span>
                    </div>
                    <span class="me-card-counter" id="cardCounter">0 / 0</span>
                </div>
                <span class="me-save-badge idle me-card-save-badge" id="globalSaveStatus">Ready</span>
                <button type="button" class="me-card-nav-btn" id="cardNext" onclick="cardNavigate(1)" aria-label="Next Student" title="Next Student">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            {{-- Card Body (mark fields) --}}
            <div class="me-card-body" id="cardBody">
                {{-- dynamically built --}}
            </div>

            {{-- Card Totals Footer --}}
            <div class="me-card-totals" id="cardTotals">
                {{-- dynamically built --}}
            </div>
        </div>

        {{-- Keyboard Hint --}}
        <div class="me-card-hint" id="keyboardHint">
            Use <kbd>&larr;</kbd> <kbd>&rarr;</kbd> arrow keys to navigate between students &middot;
            <kbd>Tab</kbd> to move between fields &middot;
            Marks auto-save after 900ms
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
    var currentIndex = -1;
    var saveTimers = {};  // keyed by student_id + mark_key
    var isLocked = false;
    var hasPermission = false;
    var undoStack = [];
    var currentMarkField = 'all'; // 'all' or a specific field key like 'ca1', 'test1', etc.

    // ========== DOM REFS ==========
    var filterAy = document.getElementById('filterAy');
    var filterTerm = document.getElementById('filterTerm');
    var filterClass = document.getElementById('filterClass');
    var filterSection = document.getElementById('filterSection');
    var filterSubject = document.getElementById('filterSubject');
    // Mark Field removed from filter — always show all fields
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

    // Card DOM refs
    var cardBody = document.getElementById('cardBody');
    var cardTotals = document.getElementById('cardTotals');
    var cardStudentName = document.getElementById('cardStudentName');
    var cardCounter = document.getElementById('cardCounter');
    var cardAvatar = document.getElementById('cardAvatar');

    // ========== TEACHER ASSIGNMENTS DATA ==========
    var teacherAssignments = @json($teacherAssignments);

    // ========== SERVER-SIDE SECTIONS DATA (fallback) ==========
    var serverSections = @json($sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'class_id' => $s->class_id]));

    // ========== API ROUTES ==========
    var API_TERMS = '{{ route("admin.mark-entries.api.terms") }}';
    var API_CLASSES = '{{ route("admin.mark-entries.api.classes") }}';
    var API_SECTIONS = '{{ route("admin.mark-entries.api.sections") }}';
    var API_SUBJECTS = '{{ route("admin.mark-entries.api.subjects") }}';
    var API_LOAD_STUDENTS = '{{ route("admin.mark-entries.api.load-students") }}';
    var API_SAVE = '{{ route("admin.mark-entries.api.save") }}';
    var API_CHECK_LOCK = '{{ route("admin.mark-entries.api.check-lock") }}';
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

    // ========== INIT ==========
    function init() {
        console.log('[MarkEntry] Initializing... isTeacher={{ $isTeacher ? "true" : "false" }}, classes={{ $classes->count() }}');

        // If teacher, populate class dropdown from assignments
        if (teacherAssignments && teacherAssignments.length > 0) {
            populateTeacherClasses();
        } else if (!{{ $isTeacher ? 'true' : 'false' }}) {
            // Admin: classes are already populated server-side in the HTML <select>.
            // Only call loadClasses() if the server-side dropdown is empty (no classes found)
            var hasServerClasses = filterClass.querySelectorAll('option[value!=""]').length > 0;
            if (!hasServerClasses) {
                console.log('[MarkEntry] No server-side classes, loading via API...');
                loadClasses();
            } else {
                console.log('[MarkEntry] Classes already populated from server (' + filterClass.querySelectorAll('option[value!=""]').length + ' classes)');
            }
        }

        // If we have current AY & Term, check lock
        if (filterAy.value && filterTerm.value) {
            checkLockStatus();
        }

        updateLoadButton();
    }

    // ========== TEACHER CLASS POPULATION ==========
    function populateTeacherClasses() {
        var ayId = filterAy.value;
        var classes = {};

        teacherAssignments.forEach(function(a) {
            // Include if: no AY selected, or assignment matches AY, or assignment has no AY (homeroom)
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

        // If only one class, auto-select
        if (Object.keys(classes).length === 1) {
            filterClass.value = Object.keys(classes)[0];
            loadSections();
        }
    }

    // ========== CASCADE: AY -> Terms ==========
    filterAy.addEventListener('change', function() {
        var ayId = this.value;
        // Update chip
        document.getElementById('chipAy').textContent = ayId
            ? filterAy.selectedOptions[0].textContent
            : 'No Academic Year';

        // Reset downstream
        filterTerm.innerHTML = '<option value="">-- Select Term --</option>';
        filterClass.innerHTML = '<option value="">-- Select Class --</option>';
        filterSection.innerHTML = '<option value="">-- Select Section --</option>';
        filterSection.disabled = true;
        filterSubject.innerHTML = '<option value="">-- Select Subject --</option>';
        filterSubject.disabled = true;
        hideMarkEntry();

        if (!ayId) { updateLoadButton(); return; }

        // Load terms
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
                // Auto-select if only one term
                if (data.length === 1) {
                    filterTerm.value = data[0].id;
                    filterTerm.dispatchEvent(new Event('change'));
                }
            })
            .catch(function(err) { console.error('[MarkEntry] Failed to load terms:', err); });

        // Load classes
        if (teacherAssignments && teacherAssignments.length > 0) {
            populateTeacherClasses();
        } else {
            loadClasses();
        }

        updateLoadButton();
    });

    // ========== CASCADE: Term -> Check Lock ==========
    filterTerm.addEventListener('change', function() {
        var termId = this.value;
        document.getElementById('chipTerm').textContent = termId
            ? filterTerm.selectedOptions[0].textContent
            : 'No Active Term';

        // Reset downstream
        filterSubject.innerHTML = '<option value="">-- Select Subject --</option>';
        filterSubject.disabled = true;
        hideMarkEntry();

        if (termId) {
            checkLockStatus();
            // Reload subjects if section is selected
            if (filterSection.value) loadSubjects();
        } else {
            hideLockBanner();
        }
        updateLoadButton();
    });

    // ========== CASCADE: Class -> Sections ==========
    filterClass.addEventListener('change', function() {
        filterSection.innerHTML = '<option value="">-- Select Section --</option>';
        filterSection.disabled = true;
        filterSubject.innerHTML = '<option value="">-- Select Subject --</option>';
        filterSubject.disabled = true;
        hideMarkEntry();

        if (this.value) {
            loadSections();
        }
        updateLoadButton();
    });

    // ========== CASCADE: Section -> Subjects ==========
    filterSection.addEventListener('change', function() {
        filterSubject.innerHTML = '<option value="">-- Select Subject --</option>';
        filterSubject.disabled = true;
        hideMarkEntry();

        if (this.value) {
            loadSubjects();
        }
        updateLoadButton();
    });

    // ========== CASCADE: Subject ==========
    filterSubject.addEventListener('change', function() {
        updateLoadButton();
        if (this.value) {
            // Auto-load if all filters are set
            if (filterAy.value && filterTerm.value && filterClass.value && filterSection.value) {
                loadStudents();
            }
        } else {
            hideMarkEntry();
        }
    });



    // ========== LOAD BUTTON ==========
    btnLoad.addEventListener('click', function() {
        if (filterAy.value && filterTerm.value && filterClass.value && filterSection.value && filterSubject.value) {
            loadStudents();
        }
    });

    function updateLoadButton() {
        var ready = filterAy.value && filterTerm.value && filterClass.value && filterSection.value && filterSubject.value;
        btnLoad.disabled = !ready;
        filterHint.textContent = ready
            ? 'Click "Load Students" to view marks'
            : 'Select all filters above to load students';
    }

    // ========== LOAD CLASSES ==========
    function loadClasses() {
        console.log('[MarkEntry] loadClasses() called, API_CLASSES=' + API_CLASSES);

        fetch(API_CLASSES, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) {
                console.log('[MarkEntry] API response status:', r.status, 'redirected:', r.redirected);
                if (!r.ok) {
                    // If redirected (e.g. to login), the response won't be JSON
                    if (r.status === 302 || r.redirected) {
                        throw new Error('Session expired. Please refresh the page and log in again.');
                    }
                    throw new Error('HTTP ' + r.status);
                }
                return r.json();
            })
            .then(function(data) {
                filterClass.innerHTML = '<option value="">-- Select Class --</option>';
                // API returns array of {id, name} directly
                var classes = Array.isArray(data) ? data : (data.classes || data.data || []);
                console.log('[MarkEntry] API returned ' + classes.length + ' classes:', classes);
                if (classes.length === 0) {
                    var opt = document.createElement('option');
                    opt.disabled = true;
                    opt.textContent = 'No classes found';
                    filterClass.appendChild(opt);
                }
                classes.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    filterClass.appendChild(opt);
                });
            })
            .catch(function(err) {
                console.error('[MarkEntry] Failed to load classes:', err);
                filterClass.innerHTML = '<option value="">-- Error loading classes --</option>';
            });
    }

    // ========== LOAD SECTIONS ==========
    function loadSections() {
        var classId = filterClass.value;
        var ayId = filterAy.value;
        if (!classId) return;

        console.log('[MarkEntry] loadSections() called, classId=' + classId + ', API_SECTIONS=' + API_SECTIONS);

        var url = API_SECTIONS + '?class_id=' + classId;
        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) {
                console.log('[MarkEntry] loadSections response: status=' + r.status + ', redirected=' + r.redirected);
                if (!r.ok) {
                    if (r.status === 302 || r.redirected) {
                        throw new Error('Session expired. Please refresh the page and log in again.');
                    }
                    throw new Error('HTTP ' + r.status);
                }
                return r.json();
            })
            .then(function(data) {
                filterSection.innerHTML = '<option value="">-- Select Section --</option>';
                // API returns array of {id, name} directly
                var sections = Array.isArray(data) ? data : (data.sections || []);
                console.log('[MarkEntry] loadSections returned ' + sections.length + ' sections for classId=' + classId, sections);

                // Filter by teacher assignments if teacher
                if (teacherAssignments && teacherAssignments.length > 0) {
                    var assignedSections = teacherAssignments
                        .filter(function(a) { return a.class_id == classId; })
                        .map(function(a) { return a.section_id; });
                    if (assignedSections.length > 0 && assignedSections[0] !== null) {
                        sections = sections.filter(function(s) {
                            return assignedSections.indexOf(String(s.id)) !== -1 || assignedSections.indexOf(s.id) !== -1;
                        });
                    }
                }

                populateSectionsDropdown(sections, classId);
            })
            .catch(function(err) {
                console.error('[MarkEntry] Failed to load sections via API:', err);
                // FALLBACK: Use server-side sections data
                console.log('[MarkEntry] Falling back to server-side sections data...');
                var fallbackSections = serverSections.filter(function(s) { return s.class_id == classId; });
                if (fallbackSections.length > 0) {
                    populateSectionsDropdown(fallbackSections, classId);
                } else {
                    filterSection.innerHTML = '<option value="">-- Error loading sections --</option>';
                    filterSection.disabled = true;
                }
            });
    }

    // Helper: populate sections dropdown from data
    function populateSectionsDropdown(sections, classId) {
        filterSection.innerHTML = '<option value="">-- Select Section --</option>';

        if (sections.length === 0) {
            var opt = document.createElement('option');
            opt.disabled = true;
            opt.textContent = 'No sections found';
            filterSection.appendChild(opt);
        }

        sections.forEach(function(s) {
            var opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name;
            filterSection.appendChild(opt);
        });
        filterSection.disabled = sections.length === 0;

        // Auto-select if only one
        if (sections.length === 1) {
            filterSection.value = sections[0].id;
            filterSection.dispatchEvent(new Event('change'));
        }
    }

    // ========== LOAD SUBJECTS ==========
    function loadSubjects() {
        var classId = filterClass.value;
        var sectionId = filterSection.value;
        var ayId = filterAy.value;
        if (!classId || !ayId) return;

        console.log('[MarkEntry] loadSubjects() called, classId=' + classId + ', sectionId=' + sectionId);

        var url = API_SUBJECTS + '?class_id=' + classId + '&section_id=' + (sectionId || '') + '&academic_year_id=' + ayId;
        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) {
                console.log('[MarkEntry] loadSubjects response: status=' + r.status + ', redirected=' + r.redirected);
                if (!r.ok) {
                    if (r.status === 302 || r.redirected) {
                        throw new Error('Session expired. Please refresh the page and log in again.');
                    }
                    throw new Error('HTTP ' + r.status);
                }
                return r.json();
            })
            .then(function(data) {
                filterSubject.innerHTML = '<option value="">-- Select Subject --</option>';
                var subjects = Array.isArray(data) ? data : (data.subjects || []);
                console.log('[MarkEntry] loadSubjects returned ' + subjects.length + ' subjects', subjects);

                // Filter by teacher assignments if teacher
                if (teacherAssignments && teacherAssignments.length > 0) {
                    var isHomeroom = teacherAssignments.some(function(a) {
                        return a.class_id == classId && a.section_id == sectionId && a.is_homeroom;
                    });

                    if (!isHomeroom) {
                        var assignedSubjects = teacherAssignments
                            .filter(function(a) { return a.class_id == classId && (!a.section_id || a.section_id == sectionId); })
                            .map(function(a) { return a.subject_id; });
                        if (assignedSubjects.length > 0 && assignedSubjects[0] !== null) {
                            subjects = subjects.filter(function(s) {
                                return assignedSubjects.indexOf(String(s.id)) !== -1 || assignedSubjects.indexOf(s.id) !== -1;
                            });
                        }
                    }
                }

                if (subjects.length === 0) {
                    var opt = document.createElement('option');
                    opt.disabled = true;
                    opt.textContent = 'No subjects found';
                    filterSubject.appendChild(opt);
                }

                subjects.forEach(function(s) {
                    var opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    filterSubject.appendChild(opt);
                });
                filterSubject.disabled = subjects.length === 0;

                // Auto-select if only one
                if (subjects.length === 1) {
                    filterSubject.value = subjects[0].id;
                    filterSubject.dispatchEvent(new Event('change'));
                }

                updateLoadButton();
            })
            .catch(function(err) {
                console.error('[MarkEntry] Failed to load subjects:', err);
                filterSubject.innerHTML = '<option value="">-- Error loading subjects --</option>';
                filterSubject.disabled = true;
            });
    }

    // ========== CHECK LOCK STATUS ==========
    function checkLockStatus() {
        var ayId = filterAy.value;
        var termId = filterTerm.value;
        if (!ayId || !termId) { hideLockBanner(); return; }

        fetch(API_CHECK_LOCK + '?academic_year_id=' + ayId + '&term_id=' + termId, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) {
                if (!r.ok) {
                    if (r.status === 302 || r.redirected) {
                        throw new Error('Session expired. Please refresh the page and log in again.');
                    }
                    throw new Error('HTTP ' + r.status);
                }
                return r.json();
            })
            .then(function(data) {
                isLocked = !!(data.is_locked);
                hasPermission = !!(data.has_permission);

                if (isLocked && !hasPermission) {
                    showLockBanner(true, data.message || 'Mark entry is locked for this term. Contact administrator for permission.');
                } else if (isLocked && hasPermission) {
                    showLockBanner(true, 'Mark entry is locked, but you have permission to edit.');
                } else {
                    showLockBanner(false, 'Mark entry is open for this term.');
                }

                // Disable/enable mark inputs
                updateInputLockState();
            })
            .catch(function(err) {
                console.error('[MarkEntry] Failed to check lock status:', err);
                hideLockBanner();
            });
    }

    function showLockBanner(locked, message) {
        lockBanner.classList.remove('d-none', 'locked', 'unlocked');
        lockBanner.classList.add(locked ? 'locked' : 'unlocked');
        lockBanner.querySelector('i').className = locked ? 'fas fa-lock' : 'fas fa-lock-open';
        lockBannerText.textContent = message;

        chipLock.style.display = '';
        chipLock.className = 'me-term-chip ' + (locked ? 'chip-lock' : 'chip-unlock');
        chipLock.querySelector('i').className = locked ? 'fas fa-lock' : 'fas fa-lock-open';
        chipLockText.textContent = locked ? 'Locked' : 'Unlocked';
    }

    function hideLockBanner() {
        lockBanner.classList.add('d-none');
        chipLock.style.display = 'none';
        isLocked = false;
    }

    function updateInputLockState() {
        var disabled = isLocked && !hasPermission;
        document.querySelectorAll('.me-mark-input, .me-mark-input-large').forEach(function(inp) {
            inp.disabled = disabled;
        });
        // Also update mobile inputs
        document.querySelectorAll('.me-mobile-field-input').forEach(function(inp) {
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

        // Mark field is always 'all' now
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

                    // Populate marks from response
                    ALL_MARK_FIELDS.forEach(function(f) {
                        studentObj.marks[f.key] = (s[f.key] !== null && s[f.key] !== undefined) ? s[f.key] : null;
                    });
                    // Also grab server-calculated totals if available
                    studentObj.marks.ca_total = s.ca_total || null;
                    studentObj.marks.exam_total = s.exam_total || null;
                    studentObj.marks.grand_total = s.grand_total || null;
                    studentObj.marks.grade = s.grade || null;

                    return studentObj;
                });

                if (students.length > 0) {
                    buildTable();
                    showMarkEntry();
                    updateFieldBadge();
                    // Re-check lock status to ensure inputs are correctly disabled
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

    // ========== BUILD STUDENT CARD ==========
    function buildTable() {
        // Always build card view (one student at a time)
        if (currentIndex < 0) currentIndex = 0;
        buildStudentCard(currentIndex);

        // Update keyboard hint
        keyboardHint.innerHTML = 'Use <kbd>&larr;</kbd> <kbd>&rarr;</kbd> arrow keys to navigate between students &middot; <kbd>Tab</kbd> to move between fields &middot; Marks auto-save after 900ms';
    }

    function buildStudentCard(idx) {
        if (idx < 0 || idx >= students.length) return;
        var s = students[idx];

        // Update nav bar
        cardStudentName.textContent = s.student_name;
        cardAvatar.textContent = getInitials(s.student_name);
        cardCounter.textContent = (idx + 1) + ' / ' + students.length;
        document.getElementById('cardPrev').disabled = idx === 0;
        document.getElementById('cardNext').disabled = idx === students.length - 1;

        var html = '';

        // CA Section
        html += '<div class="me-card-section-label ca-label">Continuous Assessment</div>';
        html += '<div class="me-card-ca-grid">';

        // CA1-CA10
        CA_FIELDS.forEach(function(f) {
            var val = s.marks[f.key];
            html += '<div class="me-card-field">'
                + '<span class="me-card-field-label">' + f.label + '</span>'
                + '<input type="text" inputmode="decimal" class="me-card-field-input mark-input"'
                + ' data-student-id="' + s.id + '" data-student-index="' + idx + '"'
                + ' data-mark-key="' + f.key + '" data-max="' + f.max + '"'
                + ' value="' + (val !== null && val !== undefined ? val : '') + '"'
                + ' placeholder="/' + f.max + '"'
                + (isLocked ? ' disabled' : '')
                + '>'
                + '<span class="me-card-field-max">/' + f.max + '</span>'
                + '</div>';
        });

        // Extra CA: Conduct, Handwriting, Creativity
        EXTRA_CA_FIELDS.forEach(function(f) {
            var val = s.marks[f.key];
            html += '<div class="me-card-field">'
                + '<span class="me-card-field-label">' + f.label + '</span>'
                + '<input type="text" inputmode="decimal" class="me-card-field-input mark-input"'
                + ' data-student-id="' + s.id + '" data-student-index="' + idx + '"'
                + ' data-mark-key="' + f.key + '" data-max="' + f.max + '"'
                + ' value="' + (val !== null && val !== undefined ? val : '') + '"'
                + ' placeholder="/' + f.max + '"'
                + (isLocked ? ' disabled' : '')
                + '>'
                + '<span class="me-card-field-max">/' + f.max + '</span>'
                + '</div>';
        });

        html += '</div>';

        // Exam Section
        html += '<div class="me-card-section-label exam-label">Examination</div>';
        html += '<div class="me-card-exam-grid">';

        EXAM_FIELDS.forEach(function(f) {
            var val = s.marks[f.key];
            html += '<div class="me-card-field field-exam">'
                + '<span class="me-card-field-label">' + f.label + '</span>'
                + '<input type="text" inputmode="decimal" class="me-card-field-input mark-input exam-input"'
                + ' data-student-id="' + s.id + '" data-student-index="' + idx + '"'
                + ' data-mark-key="' + f.key + '" data-max="' + f.max + '"'
                + ' value="' + (val !== null && val !== undefined ? val : '') + '"'
                + ' placeholder="/' + f.max + '"'
                + (isLocked ? ' disabled' : '')
                + '>'
                + '<span class="me-card-field-max">/' + f.max + '</span>'
                + '</div>';
        });

        html += '</div>';

        cardBody.innerHTML = html;

        // Build totals footer
        var caTotal = s.marks.ca_total;
        var examTotal = s.marks.exam_total;
        var grandTotal = s.marks.grand_total;
        var grade = s.marks.grade || '-';
        var gradeClass = getGradeClass(grade);

        var totalsHtml = '<div class="me-card-total-item">'
            + '<div class="me-card-total-label">CA /30</div>'
            + '<div class="me-card-total-value ca-val" id="cardCaTotal_' + s.id + '">' + (caTotal !== null && caTotal !== undefined ? parseFloat(caTotal).toFixed(1) : '-') + '</div>'
            + '</div>'
            + '<div class="me-card-total-item">'
            + '<div class="me-card-total-label">Exam /70</div>'
            + '<div class="me-card-total-value exam-val" id="cardExamTotal_' + s.id + '">' + (examTotal !== null && examTotal !== undefined ? parseFloat(examTotal).toFixed(1) : '-') + '</div>'
            + '</div>'
            + '<div class="me-card-total-item">'
            + '<div class="me-card-total-label">Total /100</div>'
            + '<div class="me-card-total-value grand-val" id="cardGrandTotal_' + s.id + '">' + (grandTotal !== null && grandTotal !== undefined ? parseFloat(grandTotal).toFixed(1) : '-') + '</div>'
            + '</div>'
            + '<div class="me-card-total-item">'
            + '<div class="me-card-total-label">Grade</div>'
            + '<div class="me-card-total-value"><span class="me-grade-badge ' + gradeClass + '" id="cardGrade_' + s.id + '">' + grade + '</span></div>'
            + '</div>';

        cardTotals.innerHTML = totalsHtml;

        // Attach listeners to the new inputs
        attachMarkInputListeners();
    }

    // Card navigation
    window.cardNavigate = function(dir) {
        var newIdx = currentIndex + dir;
        if (newIdx < 0) newIdx = 0;
        if (newIdx >= students.length) newIdx = students.length - 1;
        if (newIdx === currentIndex) return;
        currentIndex = newIdx;
        buildStudentCard(currentIndex);
        // Focus the first input in the card
        var firstInput = cardBody.querySelector('.mark-input');
        if (firstInput) firstInput.focus();
    };

    // Swipe gesture support
    var swipeStartX = 0;
    var swipeStartY = 0;
    var swipeThreshold = 50;

    if (cardBody) {
        cardBody.addEventListener('touchstart', function(e) {
            swipeStartX = e.changedTouches[0].screenX;
            swipeStartY = e.changedTouches[0].screenY;
        }, { passive: true });

        cardBody.addEventListener('touchend', function(e) {
            var deltaX = e.changedTouches[0].screenX - swipeStartX;
            var deltaY = e.changedTouches[0].screenY - swipeStartY;
            // Only trigger if horizontal swipe is dominant
            if (Math.abs(deltaX) > swipeThreshold && Math.abs(deltaX) > Math.abs(deltaY) * 1.5) {
                if (deltaX > 0) {
                    cardNavigate(-1); // swipe right = previous
                } else {
                    cardNavigate(1); // swipe left = next
                }
            }
        }, { passive: true });
    }

    // No resize handler needed — card view works on all sizes

    // ========== MARK INPUT LISTENERS ==========
    function attachMarkInputListeners() {
        document.querySelectorAll('.mark-input').forEach(function(inp) {
            // KEYDOWN: Intercept period/comma for locale compatibility
            inp.addEventListener('keydown', function(e) {
                // Allow navigation & control keys
                if ([8, 9, 13, 27, 46, 35, 36, 37, 38, 39, 40].indexOf(e.keyCode) !== -1) return;
                if ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88, 90].indexOf(e.keyCode) !== -1) return;

                // Ctrl+Arrow left/right for student navigation while in input
                if (e.keyCode === 37 && e.ctrlKey) { e.preventDefault(); cardNavigate(-1); return; }
                if (e.keyCode === 39 && e.ctrlKey) { e.preventDefault(); cardNavigate(1); return; }

                // Enter moves to next student
                if (e.keyCode === 13) {
                    e.preventDefault();
                    cardNavigate(1);
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

            // FOCUS: Update current index
            inp.addEventListener('focus', function() {
                var idx = parseInt(this.dataset.studentIndex);
                if (!isNaN(idx) && idx >= 0) currentIndex = idx;
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

        // CA scaled: round(($caRaw / 70) * 30, 2)
        var caScaled = Math.round((caRaw / 70) * 30 * 100) / 100;
        // Exam total: min($examRaw, 70)
        var examTotal = Math.min(examRaw, 70);
        // Grand total: round($caScaled + $examTotal, 2)
        var grandTotal = Math.round((caScaled + examTotal) * 100) / 100;

        // Update local data
        s.marks.ca_total = caScaled;
        s.marks.exam_total = examTotal;
        s.marks.grand_total = grandTotal;

        // Update DOM — card totals
        var caEl = document.getElementById('cardCaTotal_' + s.id);
        var exEl = document.getElementById('cardExamTotal_' + s.id);
        var gtEl = document.getElementById('cardGrandTotal_' + s.id);
        var grEl = document.getElementById('cardGrade_' + s.id);

        if (caEl) caEl.textContent = caScaled.toFixed(1);
        if (exEl) exEl.textContent = examTotal.toFixed(1);
        if (gtEl) gtEl.textContent = grandTotal.toFixed(1);

        // Grade calculation
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

    // ========== SAVE MARK ==========
    function saveMark(studentId, markKey, value) {
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
            if (!r.ok) {
                return r.json().then(function(e) { throw new Error(e.error || 'Server error ' + r.status); })
                    .catch(function(err) { throw (err.message ? err : new Error('Server error ' + r.status)); });
            }
            return r.json();
        })
        .then(function(res) {
            if (res.success) {
                setGlobalSaveStatus('saved', 'Saved');

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

                    // Update card total DOM cells
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

                setTimeout(function() { setGlobalSaveStatus('idle', 'Ready'); }, 2000);
            } else {
                setGlobalSaveStatus('error', res.error || 'Failed');
                // Flash input red
                var inp = document.querySelector('.mark-input[data-student-id="' + studentId + '"][data-mark-key="' + markKey + '"]');
                if (inp) {
                    inp.classList.add('input-error');
                    setTimeout(function() { inp.classList.remove('input-error'); }, 2000);
                }
            }
        })
        .catch(function(err) {
            setGlobalSaveStatus('error', err.message || 'Error');
            var inp = document.querySelector('.mark-input[data-student-id="' + studentId + '"][data-mark-key="' + markKey + '"]');
            if (inp) {
                inp.classList.add('input-error');
                setTimeout(function() { inp.classList.remove('input-error'); }, 2000);
            }
            console.error('Save error:', err);
        });
    }

    function setGlobalSaveStatus(state, text) {
        globalSaveStatus.className = 'me-save-badge ' + state;
        globalSaveStatus.textContent = text;
    }

    // ========== NAVIGATION ==========
    window.navigateStudent = function(dir) {
        cardNavigate(dir);
    };

    // ========== KEYBOARD NAVIGATION ==========
    document.addEventListener('keydown', function(e) {
        // Arrow left/right for student navigation when not in a mark input
        if (e.target.classList.contains('mark-input')) {
            return;
        }

        if (e.key === 'ArrowLeft') { e.preventDefault(); cardNavigate(-1); }
        if (e.key === 'ArrowRight') { e.preventDefault(); cardNavigate(1); }
    });

    // ========== UI STATE HELPERS ==========
    function hideMarkEntry() {
        markEntryArea.classList.add('d-none');
        emptyState.classList.remove('d-none');
        noStudentsState.classList.add('d-none');
        loadingState.classList.add('d-none');
        // Show the filter panel again when mark entry is hidden
        expandFilterPanel();
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

    // Global function called by the "Change" button
    window.showFilterPanel = function() {
        expandFilterPanel();
        // Scroll to filter panel
        document.getElementById('filterPanel').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    function updateFilterSummary() {
        var summaryText = document.getElementById('filterSummaryText');
        var parts = [];

        var ayName = filterAy.selectedOptions[0] ? filterAy.selectedOptions[0].textContent : '';
        var termName = filterTerm.selectedOptions[0] ? filterTerm.selectedOptions[0].textContent : '';
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

    function showMarkEntry() {
        markEntryArea.classList.remove('d-none');
        emptyState.classList.add('d-none');
        noStudentsState.classList.add('d-none');
        loadingState.classList.add('d-none');
        // Collapse the filter panel and show summary
        collapseFilterPanel();
        // Build the first student card
        if (students.length > 0) {
            currentIndex = 0;
            buildStudentCard(0);
        }
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
})();
</script>
@endpush
