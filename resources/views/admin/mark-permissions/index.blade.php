@extends('layouts.admin')
@section('title', 'Mark Edit Permissions')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Marks</a></li>
                    <li class="active">Edit Permissions</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Mark Edit Permissions</h1>
            <p class="modern-page-subtitle">Manage special permissions for teachers to edit locked marks</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.mark-permissions.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i>
                <span>Grant New Permission</span>
            </a>
        </div>
    </div>

    {{-- Info Alert --}}
    <div class="modern-alert modern-alert-info">
        <i class="fas fa-info-circle"></i>
        <span>When mark entry is locked for a term, you can grant specific teachers permission to edit marks for specific students. This is useful for corrections or special cases.</span>
        <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- Filter Card --}}
    <div class="modern-card" style="margin-bottom: 1.25rem;">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <div class="modern-filter-icon modern-filter-icon-blue">
                    <i class="fas fa-filter"></i>
                </div>
                <h2 class="modern-card-title">Filter Permissions</h2>
            </div>
            <div class="modern-card-header-right">
                <a href="{{ route('admin.mark-permissions.index') }}" class="btn-modern btn-modern-ghost" style="font-size: 0.82rem;">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
        </div>
        <div class="modern-card-body">
            <form method="GET" action="{{ route('admin.mark-permissions.index') }}">
                <div class="modern-form-grid modern-form-grid-3" style="padding: 1.25rem 1.5rem;">
                    <div class="modern-form-group">
                        <label class="modern-form-label">Academic Year</label>
                        <select name="academic_year_id" class="modern-select modern-select-no-icon">
                            <option value="">All Years</option>
                            @foreach ($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ $selectedAy == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Term</label>
                        <select name="term_id" class="modern-select modern-select-no-icon">
                            <option value="">All Terms</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}" {{ $selectedTerm == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Teacher</label>
                        <select name="teacher_id" class="modern-select modern-select-no-icon">
                            <option value="">All Teachers</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ $selectedTeacher == $teacher->id ? 'selected' : '' }}>{{ $teacher->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="padding: 0 1.5rem 1.25rem;">
                    <button type="submit" class="btn-modern btn-modern-primary" style="padding: 0.5rem 1.2rem; font-size: 0.85rem;">
                        <i class="fas fa-search"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Permissions Table Card --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">All Permissions</h2>
                <span class="modern-badge modern-badge-light">{{ $permissions->total() }} records</span>
            </div>
        </div>
        <div class="modern-card-body">
            @if(session('success'))
                <div class="modern-alert modern-alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="modern-alert modern-alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if($permissions->count() > 0)
            <div class="modern-table-wrapper">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="th-narrow">#</th>
                            <th>Teacher Name</th>
                            <th>Student Name</th>
                            <th>Subject</th>
                            <th>Term</th>
                            <th>Granted By</th>
                            <th>Reason</th>
                            <th>Expires At</th>
                            <th class="th-center">Status</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $permission)
                        <tr>
                            <td class="td-narrow">
                                <span class="modern-row-number">{{ $loop->iteration + ($permissions->currentPage() - 1) * $permissions->perPage() }}</span>
                            </td>
                            <td>
                                <div class="modern-cell-title">{{ $permission->teacher?->full_name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="modern-cell-title">{{ $permission->student?->full_name ?? $permission->student?->name ?? 'N/A' }}</div>
                                @if($permission->student?->admission_number)
                                    <div class="modern-cell-sub">{{ $permission->student->admission_number }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $permission->subject?->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $permission->term?->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $permission->grantedBy?->name ?? 'System' }}</div>
                            </td>
                            <td>
                                <div class="modern-cell-text" style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $permission->reason }}">
                                    {{ Str::limit($permission->reason, 40) }}
                                </div>
                            </td>
                            <td>
                                <div class="modern-cell-text">
                                    @if($permission->expires_at)
                                        {{ \Carbon\Carbon::parse($permission->expires_at)->format('M d, Y H:i') }}
                                    @else
                                        <span class="modern-cell-muted">Indefinite</span>
                                    @endif
                                </div>
                            </td>
                            <td class="td-center">
                                @if($permission->status === 'active')
                                    <span class="modern-badge modern-badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                @elseif($permission->status === 'expired')
                                    <span class="modern-badge modern-badge-warning"><i class="fas fa-clock"></i> Expired</span>
                                @elseif($permission->status === 'revoked')
                                    <span class="modern-badge modern-badge-danger"><i class="fas fa-ban"></i> Revoked</span>
                                @else
                                    <span class="modern-badge modern-badge-light">{{ ucfirst($permission->status ?? 'N/A') }}</span>
                                @endif
                            </td>
                            <td class="td-actions">
                                <div class="modern-action-group">
                                    @if($permission->status === 'active')
                                        <form method="POST" action="{{ route('admin.mark-permissions.revoke', $permission->id) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to revoke this permission? The teacher will no longer be able to edit marks for this student.')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="modern-btn-icon modern-btn-delete" title="Revoke Permission">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="modern-cell-muted" style="font-size: 0.78rem;">—</span>
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
            <div class="modern-pagination-wrapper">
                {{ $permissions->withQueryString()->links() }}
            </div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h3>No Permissions Granted</h3>
                <p>No mark edit permissions have been granted yet, or no results match your filters.</p>
                <a href="{{ route('admin.mark-permissions.create') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-plus"></i> Grant New Permission
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
/* Modern Page Layout */
.modern-page { animation: fadeSlideIn 0.4s ease-out; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.modern-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-page-header-left { flex: 1; }

.modern-page-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0;
    letter-spacing: -0.5px;
}

.modern-page-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0.25rem 0 0;
}

.modern-page-header-right {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Breadcrumb */
.modern-breadcrumb ol {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0 0 0.5rem;
    gap: 0.5rem;
    font-size: 0.8rem;
    align-items: center;
}

.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Card */
.modern-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.modern-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-card-header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modern-card-header-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modern-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}

.modern-card-body { padding: 0; }

/* Filter Icon */
.modern-filter-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.modern-filter-icon-blue { background: #eef2ff; color: #4361ee; }

/* Badges */
.modern-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.65rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.3px;
}

.modern-badge-light { background: #f3f4f6; color: #6b7280; }
.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-warning { background: #fefce8; color: #b45309; }
.modern-badge-info { background: #eff6ff; color: #2563eb; }

/* Alert */
.modern-alert {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    padding: 0.85rem 1.25rem;
    margin-bottom: 1.25rem;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 500;
    animation: fadeSlideIn 0.3s ease;
    line-height: 1.5;
}

.modern-alert-info {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}

.modern-alert-success {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.modern-alert-danger {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.modern-alert i:first-child {
    margin-top: 2px;
    flex-shrink: 0;
}

.modern-alert span { flex: 1; }

.modern-alert-close {
    margin-left: auto;
    background: none;
    border: none;
    cursor: pointer;
    color: inherit;
    opacity: 0.6;
    transition: opacity 0.2s;
    flex-shrink: 0;
}
.modern-alert-close:hover { opacity: 1; }

/* Form Grid */
.modern-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.modern-form-grid-3 {
    grid-template-columns: repeat(3, 1fr);
}

.modern-form-group { display: flex; flex-direction: column; }

.modern-form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.45rem;
    font-size: 0.88rem;
}

/* Select (no icon variant for filters) */
.modern-select-no-icon {
    width: 100%;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.65rem 2.2rem 0.65rem 0.85rem;
    font-size: 0.88rem;
    color: #1a1a2e;
    background: #fff;
    appearance: none;
    cursor: pointer;
    transition: all 0.2s;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.6rem center;
    background-repeat: no-repeat;
    background-size: 1.1rem;
}

.modern-select-no-icon:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

/* Table */
.modern-table-wrapper { overflow-x: auto; }

.modern-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.modern-table thead th {
    background: #f9fafb;
    padding: 0.85rem 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    border-bottom: 2px solid #e5e7eb;
    white-space: nowrap;
}

.th-center, .td-center { text-align: center !important; }
.th-actions, .td-actions { text-align: right !important; }
.th-narrow, .td-narrow { width: 50px; }

.modern-table tbody tr {
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.15s;
}

.modern-table tbody tr:hover { background: #f8f9ff; }

.modern-table td {
    padding: 0.9rem 1rem;
    vertical-align: middle;
    color: #374151;
}

.modern-row-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: #f3f4f6;
    color: #6b7280;
    font-weight: 600;
    font-size: 0.8rem;
}

.modern-cell-title {
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 2px;
}

.modern-cell-sub {
    font-size: 0.8rem;
    color: #6b7280;
}

.modern-cell-text {
    color: #4b5563;
}

.modern-cell-muted { color: #d1d5db; font-size: 0.8rem; }

/* Action Buttons */
.modern-action-group {
    display: inline-flex;
    gap: 0.35rem;
}

.modern-btn-icon {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.82rem;
    text-decoration: none;
}

.modern-btn-delete {
    background: #fef2f2;
    color: #dc2626;
}
.modern-btn-delete:hover { background: #dc2626; color: #fff; transform: translateY(-1px); }

/* Modern Button */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.35rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.25s;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
}

.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4);
    color: #fff;
}

.btn-modern-outline {
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
}

.btn-modern-outline:hover {
    border-color: #4361ee;
    color: #4361ee;
    background: #f8f9ff;
}

.btn-modern-ghost {
    background: transparent;
    color: #6b7280;
    padding: 0.65rem 1rem;
}

.btn-modern-ghost:hover {
    color: #1a1a2e;
    background: #f3f4f6;
}

/* Empty State */
.modern-empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.modern-empty-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #f3f4f6;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #d1d5db;
    margin-bottom: 1.25rem;
}

.modern-empty-state h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 0.5rem;
}

.modern-empty-state p {
    color: #9ca3af;
    font-size: 0.9rem;
    margin: 0 0 1.5rem;
}

/* Pagination */
.modern-pagination-wrapper {
    padding: 1rem 1.5rem;
    border-top: 1px solid #f0f0f0;
    display: flex;
    justify-content: center;
}

/* Responsive */
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-page-title { font-size: 1.35rem; }
    .modern-form-grid-3 { grid-template-columns: 1fr; }
    .modern-card-header { flex-direction: column; align-items: stretch; }
    .modern-table { font-size: 0.82rem; }
}

@media (max-width: 992px) {
    .modern-form-grid-3 { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 480px) {
    .modern-form-grid-3 { grid-template-columns: 1fr; }
}
</style>
@endpush
@endsection