@extends('layouts.admin')
@section('title', 'Students')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Students</a></li>
                    <li class="active">All Students</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.students.generateIds') }}" class="btn-modern btn-modern-outline btn-sm">
                <i class="fas fa-id-badge"></i>
                <span>Generate IDs</span>
            </a>
            <a href="{{ route('admin.students.create') }}" class="btn-modern btn-modern-primary btn-sm">
                <i class="fas fa-plus"></i>
                <span>Add Student</span>
            </a>
        </div>
    </div>

    {{-- Compact Stats Row --}}
    <div class="compact-stats-row">
        <div class="compact-stat-card">
            <div class="compact-stat-icon compact-stat-blue"><i class="fas fa-user-graduate"></i></div>
            <div class="compact-stat-info">
                <span class="compact-stat-value">{{ $totalStudents ?? 0 }}</span>
                <span class="compact-stat-label">Total</span>
            </div>
        </div>
        <div class="compact-stat-card">
            <div class="compact-stat-icon compact-stat-green"><i class="fas fa-check-circle"></i></div>
            <div class="compact-stat-info">
                <span class="compact-stat-value">{{ $activeStudents ?? 0 }}</span>
                <span class="compact-stat-label">Active</span>
            </div>
        </div>
        <div class="compact-stat-card">
            <div class="compact-stat-icon compact-stat-red"><i class="fas fa-times-circle"></i></div>
            <div class="compact-stat-info">
                <span class="compact-stat-value">{{ $inactiveStudents ?? 0 }}</span>
                <span class="compact-stat-label">Inactive</span>
            </div>
        </div>
    </div>

    {{-- Students Table Card --}}
    <div class="modern-card">
        <div class="modern-card-header compact-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">All Students</h2>
                <span class="modern-badge modern-badge-light">{{ $students->total() }}</span>
            </div>
            <div class="modern-card-header-right">
                <div class="modern-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="studentSearch" placeholder="Search..." onkeyup="filterTable()">
                </div>
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

            @if($students->count() > 0)
            <div class="modern-table-wrapper">
                <table class="modern-table compact-table" id="studentTable">
                    <thead>
                        <tr>
                            <th class="th-narrow">#</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Roll No</th>
                            <th class="th-center">Status</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr class="modern-table-row">
                            <td class="td-narrow">
                                <span class="modern-row-number compact-row-number">{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</span>
                            </td>
                            <td>
                                <div class="modern-cell-user compact-cell-user">
                                    @if($student->photo)
                                        <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->full_name }}" class="modern-avatar-img compact-avatar">
                                    @else
                                        <div class="modern-avatar-placeholder compact-avatar">
                                            {{ strtoupper(substr($student->full_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="modern-cell-user-info">
                                        <div class="modern-cell-title">{{ $student->full_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $student->classroom?->name ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $student->section?->name ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $student->roll_number ?? '-' }}</div>
                            </td>
                            <td class="td-center">
                                @php
                                    $statusBadge = match($student->status ?? '') {
                                        'active' => 'modern-badge-success',
                                        'inactive' => 'modern-badge-danger',
                                        'graduated' => 'modern-badge-info',
                                        'transferred' => 'modern-badge-warning',
                                        default => 'modern-badge-light'
                                    };
                                @endphp
                                <span class="modern-badge {{ $statusBadge }}">{{ ucfirst($student->status ?? 'N/A') }}</span>
                            </td>
                            <td class="td-actions">
                                <div class="modern-action-group">
                                    <a href="{{ route('admin.students.show', $student->id) }}" class="modern-btn-icon modern-btn-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.students.edit', $student->id) }}" class="modern-btn-icon modern-btn-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this student?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="modern-btn-icon modern-btn-delete" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($students->hasPages())
            <div class="compact-pagination-wrapper">
                {{ $students->withQueryString()->links() }}
            </div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3>No Students Yet</h3>
                <p>Get started by enrolling your first student.</p>
                <a href="{{ route('admin.students.create') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-plus"></i> Add Student
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
/* ===== COMPACT PAGE LAYOUT ===== */
.modern-page { animation: fadeSlideIn 0.4s ease-out; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.modern-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.modern-page-header-left { flex: 1; }

/* Breadcrumb */
.modern-breadcrumb ol {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 0.4rem;
    font-size: 0.75rem;
    align-items: center;
}

.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.4rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* ===== COMPACT STATS ===== */
.compact-stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.compact-stat-card {
    background: #fff;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    border: 1px solid #f0f0f0;
}

.compact-stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.compact-stat-blue { background: #eef2ff; color: #4361ee; }
.compact-stat-green { background: #ecfdf5; color: #10b981; }
.compact-stat-red { background: #fef2f2; color: #ef4444; }

.compact-stat-info { display: flex; flex-direction: column; }

.compact-stat-value {
    font-size: 1.25rem;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1.1;
}

.compact-stat-label {
    font-size: 0.7rem;
    color: #6c757d;
    font-weight: 500;
}

/* ===== COMPACT CARD ===== */
.modern-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    border: 1px solid #f0f0f0;
    overflow: hidden;
    margin-bottom: 1rem;
}

.compact-card-header {
    padding: 0.75rem 1rem !important;
}

.modern-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f0f0f0;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.modern-card-header-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-card-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}

.modern-card-body { padding: 0; }

/* Badges */
.modern-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.15rem 0.5rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.2px;
}

.modern-badge-light { background: #f3f4f6; color: #6b7280; }
.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-info { background: #eff6ff; color: #2563eb; }
.modern-badge-warning { background: #fefce8; color: #b45309; }

/* Search Box */
.modern-search-box {
    position: relative;
    display: flex;
    align-items: center;
}

.modern-search-box i {
    position: absolute;
    left: 10px;
    color: #adb5bd;
    font-size: 0.8rem;
}

.modern-search-box input {
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    padding: 0.4rem 0.6rem 0.4rem 2rem;
    font-size: 0.8rem;
    width: 180px;
    transition: all 0.2s;
    background: #f9fafb;
    color: #374151;
}

.modern-search-box input:focus {
    outline: none;
    border-color: #4361ee;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.modern-search-box input::placeholder { color: #9ca3af; }

/* ===== COMPACT TABLE ===== */
.modern-table-wrapper { overflow-x: auto; }

.modern-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}

.modern-table thead th {
    background: #f9fafb;
    padding: 0.55rem 0.75rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #6b7280;
    border-bottom: 2px solid #e5e7eb;
    white-space: nowrap;
}

.modern-table.th-center, .modern-table thead th.th-center { text-align: center; }
.modern-table.th-actions, .modern-table thead th.th-actions { text-align: right; }

.modern-table tbody tr {
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.15s;
}

.modern-table tbody tr:hover { background: #f8f9ff; }

.modern-table td {
    padding: 0.5rem 0.75rem;
    vertical-align: middle;
    color: #374151;
}

.compact-table td {
    padding: 0.4rem 0.6rem !important;
}

.th-center, .td-center { text-align: center !important; }
.th-actions, .td-actions { text-align: right !important; }
.th-narrow, .td-narrow { width: 40px; }

.modern-row-number, .compact-row-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: #f3f4f6;
    color: #6b7280;
    font-weight: 600;
    font-size: 0.72rem;
}

/* Cell User (Avatar + Info) */
.modern-cell-user {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.compact-cell-user {
    gap: 0.4rem;
}

.compact-avatar {
    width: 30px !important;
    height: 30px !important;
    border-radius: 7px !important;
    min-width: 30px;
}

.modern-avatar-placeholder {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.8rem;
}

.modern-cell-user-info {
    display: flex;
    flex-direction: column;
}

.modern-cell-title {
    font-weight: 600;
    color: #1a1a2e;
    font-size: 0.82rem;
}

.modern-cell-text {
    color: #4b5563;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.8rem;
}

/* Action Buttons */
.modern-action-group {
    display: inline-flex;
    gap: 0.25rem;
}

.modern-btn-icon {
    width: 28px;
    height: 28px;
    border-radius: 7px;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.72rem;
    text-decoration: none;
}

.modern-btn-view { background: #eef2ff; color: #4361ee; }
.modern-btn-view:hover { background: #4361ee; color: #fff; }
.modern-btn-edit { background: #fefce8; color: #d97706; }
.modern-btn-edit:hover { background: #d97706; color: #fff; }
.modern-btn-delete { background: #fef2f2; color: #dc2626; }
.modern-btn-delete:hover { background: #dc2626; color: #fff; }

/* Modern Button - Smaller */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.9rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.8rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.25s;
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.78rem;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
}

.btn-modern-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 12px rgba(67, 97, 238, 0.4);
    color: #fff;
}

.btn-modern-outline {
    background: #fff;
    color: #4361ee;
    border: 1.5px solid #4361ee;
}

.btn-modern-outline:hover {
    background: #4361ee;
    color: #fff;
    transform: translateY(-1px);
}

/* Alert */
.modern-alert {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    margin: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 500;
    animation: fadeSlideIn 0.3s ease;
}

.modern-alert-success {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.modern-alert-close {
    margin-left: auto;
    background: none;
    border: none;
    cursor: pointer;
    color: inherit;
    opacity: 0.6;
    transition: opacity 0.2s;
}
.modern-alert-close:hover { opacity: 1; }

/* Empty State */
.modern-empty-state {
    text-align: center;
    padding: 3rem 2rem;
}

.modern-empty-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #f3f4f6;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.modern-empty-state h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 0.5rem;
}

.modern-empty-state p {
    color: #9ca3af;
    font-size: 0.85rem;
    margin: 0 0 1.25rem;
}

/* ===== COMPACT PAGINATION ===== */
.compact-pagination-wrapper {
    padding: 0.5rem 1rem;
    border-top: 1px solid #f0f0f0;
    display: flex;
    justify-content: center;
    align-items: center;
}

.compact-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.cp-list {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 3px;
}

.cp-item {
    display: inline-block;
}

.cp-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    padding: 0 6px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #4b5563;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s;
    line-height: 1;
}

.cp-link:hover {
    background: #4361ee;
    color: #fff;
    border-color: #4361ee;
}

.cp-active .cp-link {
    background: #4361ee;
    color: #fff;
    border-color: #4361ee;
}

.cp-disabled .cp-link {
    color: #d1d5db;
    background: #f9fafb;
    border-color: #e5e7eb;
    cursor: not-allowed;
}

.cp-dots {
    background: transparent;
    border-color: transparent;
    color: #9ca3af;
}

.cp-info {
    font-size: 0.7rem;
    color: #9ca3af;
    margin-left: 0.5rem;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .modern-page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .compact-stats-row {
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.5rem;
    }

    .compact-stat-card {
        padding: 0.5rem 0.6rem;
        gap: 0.4rem;
    }

    .compact-stat-icon {
        width: 30px;
        height: 30px;
        font-size: 0.85rem;
    }

    .compact-stat-value {
        font-size: 1rem;
    }

    .compact-stat-label {
        font-size: 0.65rem;
    }

    .modern-card-header {
        flex-direction: column;
        align-items: stretch;
        padding: 0.5rem 0.75rem;
    }

    .modern-search-box input { width: 100%; }

    .modern-table { font-size: 0.75rem; }

    .compact-table td {
        padding: 0.3rem 0.4rem !important;
    }

    .modern-cell-text { max-width: 80px; }

    .compact-avatar {
        width: 26px !important;
        height: 26px !important;
    }

    .modern-btn-icon {
        width: 24px;
        height: 24px;
        font-size: 0.65rem;
    }

    .btn-modern {
        padding: 0.35rem 0.7rem;
        font-size: 0.75rem;
    }

    .cp-link {
        min-width: 26px;
        height: 26px;
        font-size: 0.7rem;
    }

    .cp-info {
        font-size: 0.65rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function filterTable() {
    const input = document.getElementById('studentSearch');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('studentTable');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
