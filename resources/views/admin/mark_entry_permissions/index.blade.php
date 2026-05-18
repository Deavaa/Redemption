@extends('layouts.admin')
@section('title', 'Mark Entry Permissions')

@push('styles')
<style>
/* ===== MARK ENTRY PERMISSIONS - MODERN DESIGN ===== */
.mep-page { animation: mepFadeIn 0.4s ease-out; }
@keyframes mepFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

/* Page Header */
.mep-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.mep-header-left { flex: 1; }
.mep-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.mep-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }

/* Breadcrumb */
.mep-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.mep-breadcrumb li { color: #adb5bd; }
.mep-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.mep-breadcrumb li a:hover { color: #4361ee; }
.mep-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.mep-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Card */
.mep-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.5rem; }
.mep-card-head { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: #fafbfc; flex-wrap: wrap; }
.mep-card-head-left { display: flex; align-items: center; gap: 0.75rem; }
.mep-card-head-right { display: flex; align-items: center; gap: 0.75rem; }
.mep-card-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.mep-card-icon.blue { background: #eef2ff; color: #4361ee; }
.mep-card-icon.purple { background: #f5f3ff; color: #8b5cf6; }
.mep-card-icon.green { background: #ecfdf5; color: #10b981; }
.mep-card-icon.amber { background: #fffbeb; color: #f59e0b; }
.mep-card-title { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.mep-card-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.1rem 0 0; }
.mep-card-body { padding: 1.25rem 1.5rem; }

/* Filter Grid */
.mep-filter-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.mep-filter-group { display: flex; flex-direction: column; }
.mep-filter-label { font-weight: 600; color: #374151; margin-bottom: 0.4rem; font-size: 0.85rem; }
.mep-filter-select { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.6rem 2.2rem 0.6rem 0.8rem; font-size: 0.88rem; color: #1a1a2e; background: #fff; appearance: none; cursor: pointer; transition: all 0.2s; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.6rem center; background-repeat: no-repeat; background-size: 1.15rem; }
.mep-filter-select:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }

/* Info Alert */
.mep-alert { border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.9rem; line-height: 1.55; }
.mep-alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
.mep-alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; }
.mep-alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
.mep-alert i:first-child { font-size: 1.1rem; margin-top: 0.1rem; flex-shrink: 0; }
.mep-alert-close { margin-left: auto; background: none; border: none; cursor: pointer; color: inherit; opacity: 0.6; transition: opacity 0.2s; flex-shrink: 0; padding: 0; }
.mep-alert-close:hover { opacity: 1; }

/* Badges */
.mep-badge { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.65rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.3px; }
.mep-badge-success { background: #ecfdf5; color: #059669; }
.mep-badge-danger { background: #fef2f2; color: #dc2626; }
.mep-badge-warning { background: #fefce8; color: #b45309; }
.mep-badge-light { background: #f3f4f6; color: #6b7280; }
.mep-badge-count { background: #eef2ff; color: #4361ee; font-weight: 700; }

/* Table */
.mep-table-wrap { overflow-x: auto; }
.mep-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
.mep-table thead th { padding: 0.8rem 1rem; background: #f9fafb; border-bottom: 2px solid #e5e7eb; font-weight: 700; color: #374151; text-align: left; white-space: nowrap; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.3px; }
.mep-table tbody td { padding: 0.75rem 1rem; border-bottom: 1px solid #f0f0f0; color: #4b5563; vertical-align: middle; }
.mep-table tbody tr:hover { background: #f8f9ff; }
.mep-table tbody tr:nth-child(even) { background: #fafbfc; }
.mep-table tbody tr:nth-child(even):hover { background: #f0f4ff; }
.mep-th-center, .mep-td-center { text-align: center !important; }
.mep-th-actions, .mep-td-actions { text-align: right !important; }

/* Cell Styles */
.mep-cell-title { font-weight: 600; color: #1a1a2e; margin-bottom: 2px; }
.mep-cell-sub { font-size: 0.78rem; color: #6b7280; }
.mep-cell-text { color: #4b5563; }
.mep-cell-muted { color: #9ca3af; font-size: 0.8rem; }
.mep-row-num { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; background: #f3f4f6; color: #6b7280; font-weight: 600; font-size: 0.8rem; }

/* Action Buttons */
.mep-action-group { display: inline-flex; gap: 0.35rem; }
.mep-btn-icon { width: 34px; height: 34px; border-radius: 9px; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 0.82rem; text-decoration: none; }
.mep-btn-revoke { background: #fef2f2; color: #dc2626; }
.mep-btn-revoke:hover { background: #dc2626; color: #fff; transform: translateY(-1px); }

/* Buttons */
.mep-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 10px; font-weight: 600; font-size: 0.88rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.mep-btn-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.3); }
.mep-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.4); color: #fff; }
.mep-btn-outline { background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb; }
.mep-btn-outline:hover { border-color: #4361ee; color: #4361ee; background: #f8f9ff; }
.mep-btn-ghost { background: transparent; color: #6b7280; padding: 0.6rem 0.85rem; }
.mep-btn-ghost:hover { color: #1a1a2e; background: #f3f4f6; }

/* Empty State */
.mep-empty { text-align: center; padding: 4rem 2rem; }
.mep-empty-icon { width: 80px; height: 80px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; color: #d1d5db; margin-bottom: 1.25rem; }
.mep-empty h3 { font-size: 1.2rem; font-weight: 700; color: #1a1a2e; margin: 0 0 0.5rem; }
.mep-empty p { color: #9ca3af; font-size: 0.9rem; margin: 0 0 1.5rem; }

/* Pagination */
.mep-pagination { padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: center; }

/* Revoke Form */
.mep-revoke-form { display: inline; }

/* Responsive */
@media (max-width: 992px) {
    .mep-filter-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .mep-header { flex-direction: column; align-items: stretch; }
    .mep-title { font-size: 1.35rem; }
    .mep-filter-grid { grid-template-columns: 1fr; }
    .mep-table { font-size: 0.82rem; }
    .mep-card-head { flex-direction: column; align-items: stretch; }
}
</style>
@endpush

@section('content')
<div class="mep-page">
    {{-- Page Header --}}
    <div class="mep-header">
        <div class="mep-header-left">
            <nav aria-label="breadcrumb" class="mep-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Marks</a></li>
                    <li class="active">Mark Entry Permissions</li>
                </ol>
            </nav>
            <h1 class="mep-title">Mark Entry Permissions</h1>
            <p class="mep-subtitle">Grant and manage special edit permissions for teachers when mark entry is locked</p>
        </div>
        <div>
            <a href="{{ route('admin.mark-entry-permissions.create') }}" class="mep-btn mep-btn-primary">
                <i class="fas fa-plus"></i>
                <span>Grant New Permission</span>
            </a>
        </div>
    </div>

    {{-- Info Alert --}}
    <div class="mep-alert mep-alert-info">
        <i class="fas fa-info-circle"></i>
        <span>When mark entry is locked for a term, you can grant specific teachers permission to edit marks for specific students. This is useful for corrections or special cases.</span>
        <button type="button" class="mep-alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mep-alert mep-alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="mep-alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="mep-alert mep-alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="mep-alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Filter Panel --}}
    <div class="mep-card">
        <div class="mep-card-head">
            <div class="mep-card-head-left">
                <div class="mep-card-icon blue"><i class="fas fa-filter"></i></div>
                <div>
                    <h3 class="mep-card-title">Filter Permissions</h3>
                    <p class="mep-card-desc">Narrow down permissions by academic year, term, or teacher</p>
                </div>
            </div>
            <div class="mep-card-head-right">
                <a href="{{ route('admin.mark-entry-permissions.index') }}" class="mep-btn mep-btn-ghost" style="font-size: 0.82rem;">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
        </div>
        <div class="mep-card-body">
            <form method="GET" action="{{ route('admin.mark-entry-permissions.index') }}">
                <div class="mep-filter-grid">
                    <div class="mep-filter-group">
                        <label class="mep-filter-label" for="filterAy">Academic Year</label>
                        <select id="filterAy" name="academic_year_id" class="mep-filter-select">
                            <option value="">All Years</option>
                            @foreach ($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ $selectedAy && $selectedAy->id == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mep-filter-group">
                        <label class="mep-filter-label" for="filterTerm">Term</label>
                        <select id="filterTerm" name="term_id" class="mep-filter-select">
                            <option value="">All Terms</option>
                            @foreach ($terms ?? [] as $term)
                                <option value="{{ $term->id }}" {{ $selectedTerm && $selectedTerm->id == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mep-filter-group">
                        <label class="mep-filter-label" for="filterTeacher">Teacher</label>
                        <select id="filterTeacher" name="teacher_id" class="mep-filter-select">
                            <option value="">All Teachers</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ $selectedTeacher && $selectedTeacher->id == $teacher->id ? 'selected' : '' }}>{{ $teacher->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="margin-top: 1rem;">
                    <button type="submit" class="mep-btn mep-btn-primary" style="padding: 0.5rem 1.2rem; font-size: 0.85rem;">
                        <i class="fas fa-search"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Permissions Table --}}
    <div class="mep-card">
        <div class="mep-card-head">
            <div class="mep-card-head-left">
                <div class="mep-card-icon purple"><i class="fas fa-key"></i></div>
                <div>
                    <h3 class="mep-card-title">All Permissions</h3>
                </div>
                <span class="mep-badge mep-badge-count">{{ $permissions->total() }} records</span>
            </div>
        </div>
        <div class="mep-card-body" style="padding: 0;">
            @if($permissions->count() > 0)
                <div class="mep-table-wrap">
                    <table class="mep-table">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>Teacher</th>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Granted By</th>
                                <th>Reason</th>
                                <th>Expires At</th>
                                <th class="mep-th-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissions as $permission)
                                @php
                                    $isActive = $permission->is_active;
                                    $isExpired = $isActive && $permission->expires_at && $permission->expires_at->isPast();
                                    $isRevoked = !$isActive;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="mep-row-num">{{ $loop->iteration + ($permissions->currentPage() - 1) * $permissions->perPage() }}</span>
                                    </td>
                                    <td>
                                        <div class="mep-cell-title">{{ $permission->teacher?->full_name ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <div class="mep-cell-title">{{ $permission->student?->full_name ?? $permission->student?->first_name ?? 'N/A' }}</div>
                                        @if($permission->student?->admission_number)
                                            <div class="mep-cell-sub">{{ $permission->student->admission_number }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="mep-cell-text">{{ $permission->subject?->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="mep-td-center">
                                        @if($isRevoked)
                                            <span class="mep-badge mep-badge-danger"><i class="fas fa-ban"></i> Revoked</span>
                                        @elseif($isExpired)
                                            <span class="mep-badge mep-badge-warning"><i class="fas fa-clock"></i> Expired</span>
                                        @else
                                            <span class="mep-badge mep-badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="mep-cell-text">{{ $permission->grantedBy?->name ?? 'System' }}</div>
                                    </td>
                                    <td>
                                        <div class="mep-cell-text" style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $permission->reason }}">
                                            {{ $permission->reason ? \Illuminate\Support\Str::limit($permission->reason, 40) : '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mep-cell-text">
                                            @if($permission->expires_at)
                                                {{ $permission->expires_at->format('M d, Y H:i') }}
                                            @else
                                                <span class="mep-cell-muted">Indefinite</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="mep-td-actions">
                                        <div class="mep-action-group">
                                            @if($isActive && !$isExpired)
                                                <form method="POST" action="{{ route('admin.mark-entry-permissions.revoke', $permission->id) }}" class="mep-revoke-form" onsubmit="return confirm('Are you sure you want to revoke this permission? The teacher will no longer be able to edit marks for this student.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="mep-btn-icon mep-btn-revoke" title="Revoke Permission">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="mep-cell-muted" style="font-size: 0.78rem;">&mdash;</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($permissions->hasPages())
                    <div class="mep-pagination">
                        {{ $permissions->withQueryString()->links() }}
                    </div>
                @endif
            @else
                <div class="mep-empty">
                    <div class="mep-empty-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3>No Permissions Granted</h3>
                    <p>No mark edit permissions have been granted yet, or no results match your filters.</p>
                    <a href="{{ route('admin.mark-entry-permissions.create') }}" class="mep-btn mep-btn-primary">
                        <i class="fas fa-plus"></i> Grant New Permission
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    // Cascade: Academic Year change -> reload Terms dropdown
    var filterAy = document.getElementById('filterAy');
    var filterTerm = document.getElementById('filterTerm');

    if (filterAy) {
        filterAy.addEventListener('change', function() {
            var ayId = this.value;
            if (!ayId) {
                filterTerm.innerHTML = '<option value="">All Terms</option>';
                return;
            }
            fetch('{{ route("admin.mark-entries.api.terms") }}?academic_year_id=' + encodeURIComponent(ayId), {
                credentials: 'same-origin'
            })
            .then(function(r) { return r.json(); })
            .then(function(terms) {
                filterTerm.innerHTML = '<option value="">All Terms</option>';
                if (Array.isArray(terms)) {
                    terms.forEach(function(t) {
                        var opt = document.createElement('option');
                        opt.value = t.id;
                        opt.textContent = t.name;
                        filterTerm.appendChild(opt);
                    });
                }
            })
            .catch(function() {
                // Silent fallback - keep current options
            });
        });
    }
})();
</script>
@endpush
