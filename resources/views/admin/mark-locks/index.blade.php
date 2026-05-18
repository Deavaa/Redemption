@extends('layouts.admin')
@section('title', 'Mark Entry Lock Management')

@push('styles')
<style>
/* ===== MARK ENTRY LOCK - MODERN DESIGN ===== */
.modern-page { animation: modernFadeIn 0.4s ease-out; }
@keyframes modernFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

/* Page Header */
.modern-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.modern-header-left { flex: 1; }
.modern-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.modern-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }

/* Breadcrumb */
.modern-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Card Base */
.modern-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.5rem; }
.modern-card-head { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: #fafbfc; }
.modern-card-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.modern-card-icon.blue { background: #eef2ff; color: #4361ee; }
.modern-card-icon.green { background: #ecfdf5; color: #10b981; }
.modern-card-icon.red { background: #fef2f2; color: #ef4444; }
.modern-card-icon.amber { background: #fffbeb; color: #f59e0b; }
.modern-card-icon.purple { background: #f5f3ff; color: #8b5cf6; }
.modern-card-title { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-card-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.1rem 0 0; }
.modern-card-body { padding: 1.25rem 1.5rem; }

/* Filter Grid */
.modern-filter-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.modern-filter-group { display: flex; flex-direction: column; }
.modern-filter-label { font-weight: 600; color: #374151; margin-bottom: 0.4rem; font-size: 0.85rem; }
.modern-filter-select { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.6rem 2.2rem 0.6rem 0.8rem; font-size: 0.88rem; color: #1a1a2e; background: #fff; appearance: none; cursor: pointer; transition: all 0.2s; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.6rem center; background-repeat: no-repeat; background-size: 1.15rem; }
.modern-filter-select:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }

/* Info Alert */
.modern-alert { border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.9rem; line-height: 1.55; }
.modern-alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
.modern-alert i { font-size: 1.15rem; margin-top: 0.1rem; flex-shrink: 0; }

/* Term Status Cards */
.modern-term-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
.modern-term-card { background: #fff; border-radius: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden; transition: box-shadow 0.25s, transform 0.25s; }
.modern-term-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.08); transform: translateY(-2px); }
.modern-term-head { padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; }
.modern-term-head.locked { background: linear-gradient(135deg, #fef2f2, #fee2e2); border-bottom: 1px solid #fecaca; }
.modern-term-head.unlocked { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-bottom: 1px solid #a7f3d0; }
.modern-term-name { font-size: 1.05rem; font-weight: 700; color: #1a1a2e; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.modern-term-dates { font-size: 0.78rem; color: #6b7280; margin-top: 0.15rem; }

/* Lock Badge */
.modern-lock-badge { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.3rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; white-space: nowrap; }
.modern-lock-badge.locked { background: #fee2e2; color: #dc2626; }
.modern-lock-badge.unlocked { background: #d1fae5; color: #059669; }
.modern-lock-badge i { font-size: 0.85rem; }

/* Term Card Body */
.modern-term-body { padding: 1rem 1.25rem; }
.modern-term-detail { display: flex; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.6rem; font-size: 0.85rem; color: #4b5563; }
.modern-term-detail:last-child { margin-bottom: 0; }
.modern-term-detail-label { font-weight: 600; color: #374151; min-width: 80px; flex-shrink: 0; }
.modern-term-detail-value { color: #6b7280; }
.modern-term-detail-value.empty { font-style: italic; color: #9ca3af; }

/* Term Card Action */
.modern-term-action { padding: 0.85rem 1.25rem; border-top: 1px solid #f0f0f0; background: #fafbfc; display: flex; justify-content: flex-end; }
.modern-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.55rem 1.15rem; border-radius: 10px; font-weight: 600; font-size: 0.85rem; border: none; cursor: pointer; transition: all 0.25s; }
.modern-btn-lock { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; box-shadow: 0 2px 8px rgba(239,68,68,0.3); }
.modern-btn-lock:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(239,68,68,0.4); }
.modern-btn-unlock { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 2px 8px rgba(16,185,129,0.3); }
.modern-btn-unlock:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(16,185,129,0.4); }
.modern-btn-outline { background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb; box-shadow: none; }
.modern-btn-outline:hover { border-color: #4361ee; color: #4361ee; background: #f8f9ff; }

/* History Table */
.modern-table-wrap { overflow-x: auto; }
.modern-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.modern-table thead th { padding: 0.75rem 1rem; background: #f9fafb; border-bottom: 2px solid #e5e7eb; font-weight: 700; color: #374151; text-align: left; white-space: nowrap; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; }
.modern-table tbody td { padding: 0.7rem 1rem; border-bottom: 1px solid #f0f0f0; color: #4b5563; vertical-align: middle; }
.modern-table tbody tr:hover { background: #f8f9ff; }
.modern-table tbody tr:nth-child(even) { background: #fafbfc; }
.modern-table tbody tr:nth-child(even):hover { background: #f0f4ff; }
.modern-table .action-locked { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.6rem; border-radius: 6px; background: #fee2e2; color: #dc2626; font-weight: 600; font-size: 0.78rem; }
.modern-table .action-unlocked { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.6rem; border-radius: 6px; background: #d1fae5; color: #059669; font-weight: 600; font-size: 0.78rem; }

/* Empty State */
.modern-empty { text-align: center; padding: 3rem 1.5rem; }
.modern-empty i { font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
.modern-empty p { color: #9ca3af; font-size: 0.95rem; margin: 0; }
.modern-empty .sub { font-size: 0.82rem; margin-top: 0.5rem; color: #b0b8c4; }

/* Modal Enhancements */
.modern-modal-icon { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1rem; }
.modern-modal-icon.lock { background: #fee2e2; color: #dc2626; }
.modern-modal-icon.unlock { background: #d1fae5; color: #059669; }
.modern-modal-title { font-size: 1.15rem; font-weight: 700; color: #1a1a2e; text-align: center; margin-bottom: 0.5rem; }
.modern-modal-desc { font-size: 0.9rem; color: #6b7280; text-align: center; margin-bottom: 1.25rem; }
.modern-textarea { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1a1a2e; resize: vertical; min-height: 80px; transition: all 0.2s; }
.modern-textarea:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.modern-textarea::placeholder { color: #9ca3af; }

/* Responsive */
@media (max-width: 992px) {
    .modern-term-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .modern-header { flex-direction: column; align-items: stretch; }
    .modern-title { font-size: 1.35rem; }
    .modern-filter-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) {
    .modern-filter-grid { grid-template-columns: 1fr; }
    .modern-term-head { flex-direction: column; align-items: flex-start; }
}
</style>
@endpush

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-header">
        <div class="modern-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li class="active">Mark Entry Lock Management</li>
                </ol>
            </nav>
            <h1 class="modern-title">Mark Entry Lock Management</h1>
            <p class="modern-subtitle">Control mark entry access for each term across branches</p>
        </div>
    </div>

    {{-- Important Info Alert --}}
    <div class="modern-alert modern-alert-info">
        <i class="bi bi-info-circle-fill"></i>
        <div>
            <strong>Important:</strong> Lock mark entry to prevent teachers from editing marks for a specific term. When locked, only teachers with special permission can edit specific students' marks.
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="modern-card" id="filterPanel">
        <div class="modern-card-head">
            <div class="modern-card-icon blue"><i class="fas fa-filter"></i></div>
            <div>
                <h3 class="modern-card-title">Select Branch & Academic Year</h3>
                <p class="modern-card-desc">Choose a branch and academic year to view term lock statuses</p>
            </div>
        </div>
        <div class="modern-card-body">
            <div class="modern-filter-grid">
                <div class="modern-filter-group">
                    <label class="modern-filter-label">Branch</label>
                    <select id="filterBranch" name="branch_id" class="modern-filter-select">
                        <option value="">-- All Branches --</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modern-filter-group">
                    <label class="modern-filter-label">Academic Year</label>
                    <select id="filterAy" name="academic_year_id" class="modern-filter-select">
                        <option value="">-- Select Academic Year --</option>
                        @foreach ($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $selectedAy == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Current Term Lock Status Cards --}}
    <div class="modern-card" style="margin-bottom: 1.5rem;">
        <div class="modern-card-head">
            <div class="modern-card-icon purple"><i class="bi bi-shield-lock"></i></div>
            <div>
                <h3 class="modern-card-title">Current Term Lock Status</h3>
                <p class="modern-card-desc">Overview of lock status for each term in the selected branch and academic year</p>
            </div>
        </div>
        <div class="modern-card-body">
            @if ($lockStatuses->count() > 0)
                <div class="modern-term-grid">
                    @foreach ($lockStatuses as $status)
                        @php
                            $term = $status['term'];
                            $lock = $status['lock'];
                            $isLocked = $lock && $lock->is_locked;
                        @endphp
                        <div class="modern-term-card">
                            <div class="modern-term-head {{ $isLocked ? 'locked' : 'unlocked' }}">
                                <div>
                                    <h4 class="modern-term-name">
                                        <i class="bi {{ $isLocked ? 'bi-lock-fill' : 'bi-unlock-fill' }}" style="color: {{ $isLocked ? '#dc2626' : '#059669' }}"></i>
                                        {{ $term->name }}
                                    </h4>
                                    <div class="modern-term-dates">
                                        @if ($term->start_date && $term->end_date)
                                            {{ $term->start_date->format('M d, Y') }} &mdash; {{ $term->end_date->format('M d, Y') }}
                                        @else
                                            Dates not set
                                        @endif
                                    </div>
                                </div>
                                <span class="modern-lock-badge {{ $isLocked ? 'locked' : 'unlocked' }}">
                                    <i class="bi {{ $isLocked ? 'bi-lock-fill' : 'bi-unlock-fill' }}"></i>
                                    {{ $isLocked ? 'LOCKED' : 'UNLOCKED' }}
                                </span>
                            </div>
                            <div class="modern-term-body">
                                @if ($lock)
                                    <div class="modern-term-detail">
                                        <span class="modern-term-detail-label">{{ $isLocked ? 'Locked By' : 'Unlocked By' }}:</span>
                                        <span class="modern-term-detail-value">{{ $lock->locked_by_name ?? ($lock->user->name ?? 'N/A') }}</span>
                                    </div>
                                    <div class="modern-term-detail">
                                        <span class="modern-term-detail-label">{{ $isLocked ? 'Locked At' : 'Unlocked At' }}:</span>
                                        <span class="modern-term-detail-value">{{ $lock->updated_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                    <div class="modern-term-detail">
                                        <span class="modern-term-detail-label">Reason:</span>
                                        <span class="modern-term-detail-value {{ !$lock->reason ? 'empty' : '' }}">{{ $lock->reason ?? 'No reason provided' }}</span>
                                    </div>
                                @else
                                    <div class="modern-term-detail">
                                        <span class="modern-term-detail-label">Status:</span>
                                        <span class="modern-term-detail-value">No lock record found &mdash; marks are open for entry</span>
                                    </div>
                                @endif
                            </div>
                            <div class="modern-term-action">
                                @if ($isLocked)
                                    <button type="button" class="modern-btn modern-btn-unlock"
                                            onclick="openUnlockModal({{ $term->id }}, '{{ addslashes($term->name) }}')">
                                        <i class="bi bi-unlock-fill"></i> Unlock This Term
                                    </button>
                                @else
                                    <button type="button" class="modern-btn modern-btn-lock"
                                            onclick="openLockModal({{ $term->id }}, '{{ addslashes($term->name) }}')">
                                        <i class="bi bi-lock-fill"></i> Lock This Term
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="modern-empty">
                    <i class="bi bi-shield-lock"></i>
                    <p>No terms found for the selected branch and academic year.</p>
                    <p class="sub">Please select a branch and academic year above to view term lock statuses.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Lock History Table --}}
    <div class="modern-card">
        <div class="modern-card-head">
            <div class="modern-card-icon amber"><i class="bi bi-clock-history"></i></div>
            <div>
                <h3 class="modern-card-title">Lock History</h3>
                <p class="modern-card-desc">Chronological record of all lock and unlock actions</p>
            </div>
        </div>
        <div class="modern-card-body" style="padding: 0;">
            @if ($allLocks->count() > 0)
                <div class="modern-table-wrap">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Branch</th>
                                <th>Academic Year</th>
                                <th>Term</th>
                                <th>Action</th>
                                <th>By Whom</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allLocks as $lockRecord)
                                <tr>
                                    <td>{{ $lockRecord->created_at->format('M d, Y h:i A') }}</td>
                                    <td>{{ $lockRecord->branch->name ?? 'N/A' }}</td>
                                    <td>{{ $lockRecord->academicYear->name ?? 'N/A' }}</td>
                                    <td>{{ $lockRecord->term->name ?? 'N/A' }}</td>
                                    <td>
                                        @if ($lockRecord->is_locked)
                                            <span class="action-locked">
                                                <i class="bi bi-lock-fill"></i> Locked
                                            </span>
                                        @else
                                            <span class="action-unlocked">
                                                <i class="bi bi-unlock-fill"></i> Unlocked
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $lockRecord->locked_by_name ?? ($lockRecord->user->name ?? 'N/A') }}</td>
                                    <td>{{ $lockRecord->reason ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="modern-empty">
                    <i class="bi bi-clock-history"></i>
                    <p>No lock history records found.</p>
                    <p class="sub">Lock and unlock actions will appear here once recorded.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Lock Confirmation Modal --}}
<div class="modal fade" id="lockModal" tabindex="-1" aria-labelledby="lockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="modal-body" style="padding: 2rem 1.75rem 1.5rem;">
                <div class="modern-modal-icon lock">
                    <i class="bi bi-lock-fill"></i>
                </div>
                <h5 class="modern-modal-title" id="lockModalLabel">Confirm Lock</h5>
                <p class="modern-modal-desc">Are you sure you want to lock mark entry for <strong id="lockTermName"></strong>?</p>
                <form id="lockForm" method="POST" action="{{ route('admin.mark-locks.lock') }}">
                    @csrf
                    <input type="hidden" name="branch_id" id="lockBranchId" value="{{ $selectedBranch ?? '' }}">
                    <input type="hidden" name="academic_year_id" id="lockAyId" value="{{ $selectedAy ?? '' }}">
                    <input type="hidden" name="term_id" id="lockTermId" value="">
                    <div class="mb-3">
                        <label class="modern-filter-label" style="margin-bottom: 0.5rem;">Reason for locking <span style="color: #ef4444;">*</span></label>
                        <textarea name="lock_reason" class="modern-textarea" placeholder="Enter reason for locking mark entry (e.g., Report card generation in progress)..." required></textarea>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.25rem;">
                        <button type="button" class="modern-btn modern-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modern-btn modern-btn-lock">
                            <i class="bi bi-lock-fill"></i> Confirm Lock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Unlock Confirmation Modal --}}
<div class="modal fade" id="unlockModal" tabindex="-1" aria-labelledby="unlockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="modal-body" style="padding: 2rem 1.75rem 1.5rem;">
                <div class="modern-modal-icon unlock">
                    <i class="bi bi-unlock-fill"></i>
                </div>
                <h5 class="modern-modal-title" id="unlockModalLabel">Confirm Unlock</h5>
                <p class="modern-modal-desc">Are you sure you want to unlock mark entry for <strong id="unlockTermName"></strong>?</p>
                <form id="unlockForm" method="POST" action="{{ route('admin.mark-locks.unlock') }}">
                    @csrf
                    <input type="hidden" name="branch_id" id="unlockBranchId" value="{{ $selectedBranch ?? '' }}">
                    <input type="hidden" name="academic_year_id" id="unlockAyId" value="{{ $selectedAy ?? '' }}">
                    <input type="hidden" name="term_id" id="unlockTermId" value="">
                    <div class="mb-3">
                        <label class="modern-filter-label" style="margin-bottom: 0.5rem;">Reason for unlocking <span style="color: #ef4444;">*</span></label>
                        <textarea name="unlock_reason" class="modern-textarea" placeholder="Enter reason for unlocking mark entry (e.g., Corrections needed, additional marks to enter)..." required></textarea>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.25rem;">
                        <button type="button" class="modern-btn modern-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modern-btn modern-btn-unlock">
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
    const filterBranch = document.getElementById('filterBranch');
    const filterAy = document.getElementById('filterAy');
    let lockModal = null;
    let unlockModal = null;

    // Initialize Bootstrap modals
    document.addEventListener('DOMContentLoaded', function() {
        lockModal = new bootstrap.Modal(document.getElementById('lockModal'));
        unlockModal = new bootstrap.Modal(document.getElementById('unlockModal'));
    });

    // Cascade: Branch change -> reload Academic Years
    if (filterBranch) {
        filterBranch.addEventListener('change', function() {
            const branchId = this.value;
            if (!branchId) {
                filterAy.innerHTML = '<option value="">-- Select Academic Year --</option>';
                @foreach ($academicYears as $ay)
                filterAy.innerHTML += '<option value="{{ $ay->id }}">{{ $ay->name }}</option>';
                @endforeach
                return;
            }
            // Fetch academic years for the selected branch
            fetch('{{ route("admin.mark-locks.index") }}?branch_id=' + encodeURIComponent(branchId), {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
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
                window.location.href = url.toString();
            });
        });
    }

    // Auto-submit on Academic Year change
    if (filterAy) {
        filterAy.addEventListener('change', function() {
            const branchId = filterBranch.value;
            const ayId = this.value;
            const url = new URL(window.location.href);
            if (branchId) url.searchParams.set('branch_id', branchId);
            else url.searchParams.delete('branch_id');
            if (ayId) url.searchParams.set('academic_year_id', ayId);
            else url.searchParams.delete('academic_year_id');
            window.location.href = url.toString();
        });
    }

    // Lock Modal
    window.openLockModal = function(termId, termName) {
        document.getElementById('lockTermId').value = termId;
        document.getElementById('lockTermName').textContent = termName;
        document.getElementById('lockBranchId').value = filterBranch ? filterBranch.value : '';
        document.getElementById('lockAyId').value = filterAy ? filterAy.value : '';
        // Reset textarea
        const form = document.getElementById('lockForm');
        form.querySelector('textarea').value = '';
        if (lockModal) lockModal.show();
    };

    // Unlock Modal
    window.openUnlockModal = function(termId, termName) {
        document.getElementById('unlockTermId').value = termId;
        document.getElementById('unlockTermName').textContent = termName;
        document.getElementById('unlockBranchId').value = filterBranch ? filterBranch.value : '';
        document.getElementById('unlockAyId').value = filterAy ? filterAy.value : '';
        // Reset textarea
        const form = document.getElementById('unlockForm');
        form.querySelector('textarea').value = '';
        if (unlockModal) unlockModal.show();
    };
})();
</script>
@endpush
