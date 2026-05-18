@extends('layouts.admin')
@section('title', 'Student Promotion & Detention')

@push('styles')
<style>
/* ===== PROMOTION INDEX - MODERN DESIGN ===== */
.promo-page { animation: promoFadeIn 0.4s ease-out; }
@keyframes promoFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

/* Page Header */
.promo-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem; }
.promo-header-left { flex: 1; }
.promo-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark, #1a1a2e); margin: 0; letter-spacing: -0.5px; }
.promo-subtitle { font-size: 0.88rem; color: var(--text-muted, #6c757d); margin: 0.25rem 0 0; }

/* Breadcrumb */
.promo-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.promo-breadcrumb li { color: #adb5bd; }
.promo-breadcrumb li a { color: var(--text-muted, #6c757d); text-decoration: none; transition: color 0.2s; }
.promo-breadcrumb li a:hover { color: #4361ee; }
.promo-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.promo-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Filter Panel */
.promo-filter-card { background: var(--card-bg, #fff); border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid var(--border, #f0f0f0); overflow: hidden; margin-bottom: 1.25rem; }
.promo-filter-header { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; border-bottom: 1px solid var(--border, #f0f0f0); background: var(--bg, #fafbfc); }
.promo-filter-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; background: #eef2ff; color: #4361ee; }
.promo-filter-title { font-size: 1rem; font-weight: 700; color: var(--text-dark, #1a1a2e); margin: 0; }
.promo-filter-desc { font-size: 0.82rem; color: var(--text-muted, #9ca3af); margin: 0.1rem 0 0; }
.promo-filter-body { padding: 1.25rem 1.5rem; }
.promo-filter-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.promo-filter-group { display: flex; flex-direction: column; }
.promo-filter-label { font-weight: 600; color: #374151; margin-bottom: 0.4rem; font-size: 0.85rem; }
.promo-filter-select { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.6rem 2.2rem 0.6rem 0.8rem; font-size: 0.88rem; color: #1a1a2e; background: #fff; appearance: none; cursor: pointer; transition: all 0.2s; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.6rem center; background-repeat: no-repeat; background-size: 1.15rem; }
.promo-filter-select:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }

/* Stats Grid */
.promo-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 1.25rem; }
.promo-stat-card { display: flex; flex-direction: column; padding: 16px 18px; background: var(--card-bg, #fff); border-radius: 14px; border: 1px solid var(--border, #e5e7eb); transition: all 0.2s ease; cursor: default; position: relative; overflow: hidden; }
.promo-stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-color: transparent; }
.promo-stat-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.promo-stat-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 15px; }
.promo-stat-value { font-size: 1.5rem; font-weight: 800; line-height: 1.2; }
.promo-stat-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }

/* Stat colors */
.promo-stat-green .promo-stat-icon { background: rgba(16,185,129,0.12); color: #10b981; }
.promo-stat-green .promo-stat-value { color: #10b981; }
.promo-stat-green .promo-stat-label { color: #34d399; }
.promo-stat-green:hover { background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(16,185,129,0.03)); }

.promo-stat-red .promo-stat-icon { background: rgba(239,68,68,0.12); color: #ef4444; }
.promo-stat-red .promo-stat-value { color: #ef4444; }
.promo-stat-red .promo-stat-label { color: #f87171; }
.promo-stat-red:hover { background: linear-gradient(135deg, rgba(239,68,68,0.08), rgba(239,68,68,0.03)); }

.promo-stat-yellow .promo-stat-icon { background: rgba(245,158,11,0.12); color: #f59e0b; }
.promo-stat-yellow .promo-stat-value { color: #f59e0b; }
.promo-stat-yellow .promo-stat-label { color: #fbbf24; }
.promo-stat-yellow:hover { background: linear-gradient(135deg, rgba(245,158,11,0.08), rgba(245,158,11,0.03)); }

.promo-stat-gray .promo-stat-icon { background: rgba(107,114,128,0.12); color: #6b7280; }
.promo-stat-gray .promo-stat-value { color: #6b7280; }
.promo-stat-gray .promo-stat-label { color: #9ca3af; }
.promo-stat-gray:hover { background: linear-gradient(135deg, rgba(107,114,128,0.08), rgba(107,114,128,0.03)); }

/* Action Buttons Row */
.promo-actions-bar { display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; align-items: center; }

/* Table Enhancements */
.promo-table-wrapper { overflow-x: auto; }
.promo-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
.promo-table thead th { padding: 10px 14px; text-align: left; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.3px; color: var(--text-muted, #6b7280); background: var(--bg, #f9fafb); border-bottom: 2px solid var(--border, #e5e7eb); white-space: nowrap; }
.promo-table tbody td { padding: 10px 14px; border-bottom: 1px solid var(--border, #f0f0f0); color: var(--text-dark, #1f2937); vertical-align: middle; }
.promo-table tbody tr { transition: background 0.15s; }
.promo-table tbody tr:hover { background: rgba(67,97,238,0.03); }
.promo-table .promo-student-name { font-weight: 600; display: flex; align-items: center; gap: 8px; }

/* Status Badges */
.promo-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; gap: 4px; }
.promo-badge-promoted { background: rgba(16,185,129,0.12); color: #10b981; }
.promo-badge-detained { background: rgba(239,68,68,0.12); color: #ef4444; }
.promo-badge-conditional { background: rgba(245,158,11,0.12); color: #f59e0b; }
.promo-badge-pending { background: rgba(107,114,128,0.12); color: #6b7280; }

/* Action Buttons in Table */
.promo-table-actions { display: flex; gap: 4px; align-items: center; }
.promo-action-btn { width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--border, #e5e7eb); background: var(--card-bg, #fff); color: var(--text-muted, #6b7280); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 0.75rem; text-decoration: none; }
.promo-action-btn:hover { border-color: #4361ee; color: #4361ee; background: #eef2ff; }
.promo-action-btn.promo-action-override:hover { border-color: #f59e0b; color: #f59e0b; background: #fffbeb; }

/* Empty State */
.promo-empty { text-align: center; padding: 3rem 1.5rem; background: var(--card-bg, #fff); border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid var(--border, #f0f0f0); }
.promo-empty i { font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
.promo-empty p { color: var(--text-muted, #9ca3af); font-size: 0.95rem; margin: 0; }
.promo-empty-hint { font-size: 0.82rem; color: #b0b8c4; margin-top: 0.5rem; }

/* Responsive */
@media (max-width: 992px) {
    .promo-filter-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .promo-header { flex-direction: column; align-items: stretch; }
    .promo-title { font-size: 1.25rem; }
    .promo-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .promo-filter-grid { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
    .promo-stats-grid { grid-template-columns: 1fr 1fr; }
    .promo-actions-bar { flex-direction: column; }
}
</style>
@endpush

@section('content')
<div class="promo-page">
    {{-- Page Header --}}
    <div class="promo-header">
        <div class="promo-header-left">
            <nav aria-label="breadcrumb" class="promo-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li class="active">Student Promotion & Detention</li>
                </ol>
            </nav>
            <h1 class="promo-title">Student Promotion & Detention</h1>
            <p class="promo-subtitle">Manage student promotion, detention, and conditional advancement</p>
        </div>
        <div class="promo-header-right d-flex gap-2 align-items-center flex-wrap">
            <a href="{{ route('admin.promotion.settings.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.82rem;padding:0.45rem 1rem;">
                <i class="fas fa-cog"></i> Promotion Settings
            </a>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="promo-filter-card" id="filterPanel">
        <div class="promo-filter-header">
            <div class="promo-filter-icon"><i class="fas fa-filter"></i></div>
            <div>
                <h3 class="promo-filter-title">Select Academic Period & Class</h3>
                <p class="promo-filter-desc">Choose academic year, term, and class to view promotion results</p>
            </div>
        </div>
        <div class="promo-filter-body">
            <form method="GET" action="{{ route('admin.promotion.index') }}" id="promoFilterForm">
                <div class="promo-filter-grid">
                    <div class="promo-filter-group">
                        <label class="promo-filter-label" for="filterAy">Academic Year</label>
                        <select name="academic_year_id" id="filterAy" class="promo-filter-select">
                            <option value="">-- All Academic Years --</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ $selectedAy == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="promo-filter-group">
                        <label class="promo-filter-label" for="filterTerm">Term</label>
                        <select name="term_id" id="filterTerm" class="promo-filter-select">
                            <option value="">-- All Terms --</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}" {{ $selectedTerm == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="promo-filter-group">
                        <label class="promo-filter-label" for="filterClass">Class</label>
                        <select name="class_id" id="filterClass" class="promo-filter-select">
                            <option value="">-- All Classes --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $selectedClass == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="promo-filter-group" style="justify-content:flex-end;">
                        <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.85rem;padding:0.6rem 1.25rem;">
                            <i class="fas fa-search"></i> Apply Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="promo-stats-grid">
        <div class="promo-stat-card promo-stat-green">
            <div class="promo-stat-top">
                <div class="promo-stat-icon"><i class="fas fa-arrow-up"></i></div>
            </div>
            <div class="promo-stat-value">{{ $stats['promoted'] ?? 0 }}</div>
            <div class="promo-stat-label">Promoted</div>
        </div>
        <div class="promo-stat-card promo-stat-red">
            <div class="promo-stat-top">
                <div class="promo-stat-icon"><i class="fas fa-arrow-down"></i></div>
            </div>
            <div class="promo-stat-value">{{ $stats['detained'] ?? 0 }}</div>
            <div class="promo-stat-label">Detained</div>
        </div>
        <div class="promo-stat-card promo-stat-yellow">
            <div class="promo-stat-top">
                <div class="promo-stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="promo-stat-value">{{ $stats['conditional'] ?? 0 }}</div>
            <div class="promo-stat-label">Conditional</div>
        </div>
        <div class="promo-stat-card promo-stat-gray">
            <div class="promo-stat-top">
                <div class="promo-stat-icon"><i class="fas fa-clock"></i></div>
            </div>
            <div class="promo-stat-value">{{ $stats['pending'] ?? 0 }}</div>
            <div class="promo-stat-label">Pending</div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="promo-actions-bar">
        <a href="{{ route('admin.promotion.preview', array_filter(['academic_year_id' => $selectedAy, 'term_id' => $selectedTerm, 'class_id' => $selectedClass])) }}" class="btn-modern btn-modern-info" style="font-size:0.82rem;padding:0.5rem 1.1rem;">
            <i class="fas fa-eye"></i> Preview Promotion
        </a>
        <form method="POST" action="{{ route('admin.promotion.process') }}" id="processForm" style="display:inline;">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $selectedAy }}">
            <input type="hidden" name="term_id" value="{{ $selectedTerm }}">
            <input type="hidden" name="class_id" value="{{ $selectedClass }}">
            <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.82rem;padding:0.5rem 1.1rem;" onclick="return confirm('Are you sure you want to process promotion for the selected class? This action will finalize promotion results.')">
                <i class="fas fa-play-circle"></i> Process Class Promotion
            </button>
        </form>
        @if($promotionSetting)
            <a href="{{ route('admin.promotion.settings.index') }}" class="btn-modern btn-modern-outline" style="font-size:0.78rem;padding:0.4rem 0.9rem;">
                <i class="fas fa-cog"></i> Using: {{ $promotionSetting->name }}
            </a>
        @endif
    </div>

    {{-- Results Table --}}
    @if($results->count() > 0)
        <div class="modern-card" style="margin-bottom:1.25rem;">
            <div class="modern-card-header">
                <div class="modern-card-header-left">
                    <div class="modern-form-section-icon modern-form-section-icon-blue" style="width:36px;height:36px;border-radius:10px;font-size:0.95rem;">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <h3 class="modern-card-title">Promotion Results</h3>
                </div>
                <div style="font-size:0.78rem;color:var(--text-muted);">
                    Showing {{ $results->firstItem() }} - {{ $results->lastItem() }} of {{ $results->total() }}
                </div>
            </div>
            <div class="modern-card-body" style="padding:0;">
                <div class="promo-table-wrapper">
                    <table class="promo-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Average Score</th>
                                <th>Overall Grade</th>
                                <th>Subjects Passed/Failed</th>
                                <th>Attendance %</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $index => $result)
                            <tr>
                                <td style="font-weight:600;color:var(--text-muted);">{{ $results->firstItem() + $index }}</td>
                                <td>
                                    <div class="promo-student-name">
                                        <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#818cf8);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;flex-shrink:0;">
                                            {{ strtoupper(substr($result->student->first_name ?? 'S', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight:600;color:var(--text-dark);">{{ $result->student->first_name ?? '' }} {{ $result->student->last_name ?? '' }}</div>
                                            <div style="font-size:0.72rem;color:var(--text-muted);">{{ $result->student->roll_number ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight:700;color:{{ ($result->average_score ?? 0) >= 50 ? '#10b981' : '#ef4444' }};">
                                        {{ number_format($result->average_score ?? 0, 1) }}%
                                    </span>
                                </td>
                                <td>
                                    <span style="font-weight:700;">{{ $result->overall_grade ?? '-' }}</span>
                                </td>
                                <td>
                                    <span style="color:#10b981;font-weight:600;">{{ $result->subjects_passed ?? 0 }}</span>
                                    <span style="color:var(--text-muted);margin:0 2px;">/</span>
                                    <span style="color:#ef4444;font-weight:600;">{{ $result->subjects_failed ?? 0 }}</span>
                                </td>
                                <td>
                                    @php
                                        $att = $result->attendance_percentage ?? 0;
                                        $attColor = $att >= 80 ? '#10b981' : ($att >= 60 ? '#f59e0b' : '#ef4444');
                                    @endphp
                                    <span style="font-weight:600;color:{{ $attColor }};">{{ number_format($att, 1) }}%</span>
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($result->status ?? 'pending') {
                                            'promoted' => 'promo-badge-promoted',
                                            'detained' => 'promo-badge-detained',
                                            'conditional' => 'promo-badge-conditional',
                                            default => 'promo-badge-pending',
                                        };
                                    @endphp
                                    <span class="promo-badge {{ $statusClass }}">
                                        <i class="fas {{ ($result->status ?? 'pending') === 'promoted' ? 'fa-check-circle' : (($result->status ?? 'pending') === 'detained' ? 'fa-times-circle' : (($result->status ?? 'pending') === 'conditional' ? 'fa-exclamation-circle' : 'fa-clock')) }}"></i>
                                        {{ ucfirst($result->status ?? 'pending') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="promo-table-actions">
                                        <a href="{{ route('admin.promotion.detail', $result->id) }}" class="promo-action-btn" title="View Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.promotion.edit', $result->id) }}" class="promo-action-btn" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="promo-action-btn promo-action-override" title="Override Status" data-id="{{ $result->id }}" data-student="{{ $result->student->first_name ?? '' }} {{ $result->student->last_name ?? '' }}" data-status="{{ $result->status ?? 'pending' }}" onclick="openOverrideModal(this)">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        <div style="display:flex;justify-content:center;margin-top:1rem;">
            {{ $results->withQueryString()->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="promo-empty">
            <i class="fas fa-filter"></i>
            <p>Select filters and process promotion</p>
            <p class="promo-empty-hint">Choose an academic year, term, and class above, then click "Process Class Promotion" to generate results.</p>
        </div>
    @endif
</div>

{{-- Override Status Modal --}}
<div class="modal fade" id="overrideModal" tabindex="-1" aria-labelledby="overrideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#f59e0b,#d97706);border:none;padding:1rem 1.5rem;">
                <h5 class="modal-title" style="color:#fff;font-weight:700;font-size:1rem;" id="overrideModalLabel">
                    <i class="fas fa-exchange-alt me-2"></i> Override Promotion Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.promotion.override') }}" id="overrideForm">
                @csrf
                @method('PATCH')
                <div class="modal-body" style="padding:1.5rem;">
                    <p style="font-size:0.88rem;color:var(--text-dark);margin-bottom:1rem;">
                        Override status for: <strong id="overrideStudentName">-</strong>
                    </p>
                    <input type="hidden" name="promotion_id" id="overridePromotionId">
                    <div style="margin-bottom:1rem;">
                        <label style="font-weight:600;font-size:0.85rem;color:#374151;margin-bottom:0.4rem;display:block;">New Status</label>
                        <select name="status" id="overrideStatus" class="promo-filter-select" style="width:100%;" required>
                            <option value="promoted">Promoted</option>
                            <option value="detained">Detained</option>
                            <option value="conditional">Conditional</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-weight:600;font-size:0.85rem;color:#374151;margin-bottom:0.4rem;display:block;">Override Reason</label>
                        <textarea name="reason" rows="3" style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:0.6rem 0.8rem;font-size:0.88rem;color:#1a1a2e;resize:vertical;" placeholder="Enter reason for overriding the promotion status..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border);padding:1rem 1.5rem;">
                    <button type="button" class="btn-modern btn-modern-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern btn-modern-primary" style="background:linear-gradient(135deg,#f59e0b,#d97706);border:none;">
                        <i class="fas fa-check"></i> Override Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Cascade filter: Academic Year -> Term -> Class
document.getElementById('filterAy').addEventListener('change', function() {
    const ayId = this.value;
    const termSelect = document.getElementById('filterTerm');
    const classSelect = document.getElementById('filterClass');

    // Reset dependent selects
    termSelect.innerHTML = '<option value="">-- All Terms --</option>';
    classSelect.innerHTML = '<option value="">-- All Classes --</option>';

    if (!ayId) return;

    // Load terms for selected academic year
    fetch('{{ route("admin.terms.index") }}?academic_year_id=' + ayId, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.ok ? r.json() : [])
    .then(data => {
        const terms = Array.isArray(data) ? data : (data.data || []);
        terms.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.name;
            termSelect.appendChild(opt);
        });
    })
    .catch(() => {});

    // Load classes for selected academic year
    fetch('{{ route("admin.classrooms.index") }}?academic_year_id=' + ayId, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.ok ? r.json() : [])
    .then(data => {
        const classes = Array.isArray(data) ? data : (data.data || []);
        classes.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            classSelect.appendChild(opt);
        });
    })
    .catch(() => {});
});

document.getElementById('filterTerm').addEventListener('change', function() {
    // Optionally cascade further if needed
});

// Override modal
function openOverrideModal(btn) {
    document.getElementById('overridePromotionId').value = btn.dataset.id;
    document.getElementById('overrideStudentName').textContent = btn.dataset.student;
    document.getElementById('overrideStatus').value = btn.dataset.status;
    const modal = new bootstrap.Modal(document.getElementById('overrideModal'));
    modal.show();
}
</script>
@endpush
