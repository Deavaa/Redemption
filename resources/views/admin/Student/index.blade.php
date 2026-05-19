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
                                <span class="compact-row-number">{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</span>
                            </td>
                            <td>
                                <div class="compact-cell-user">
                                    @if($student->photo)
                                        <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->full_name }}" class="compact-avatar-img">
                                    @else
                                        <div class="compact-avatar-placeholder">
                                            {{ strtoupper(substr($student->full_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="compact-cell-name">{{ $student->full_name }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="compact-cell-text">{{ $student->classroom?->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="compact-cell-text">{{ $student->section?->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="compact-cell-text">{{ $student->roll_number ?? '-' }}</span>
                            </td>
                            <td class="td-center">
                                @php
                                    $statusBadge = match($student->status ?? '') {
                                        'active' => 'badge-success',
                                        'inactive' => 'badge-danger',
                                        'graduated' => 'badge-info',
                                        'transferred' => 'badge-warning',
                                        default => 'badge-light'
                                    };
                                @endphp
                                <span class="compact-badge {{ $statusBadge }}">{{ ucfirst($student->status ?? 'N/A') }}</span>
                            </td>
                            <td class="td-actions">
                                <div class="compact-action-group">
                                    <a href="{{ route('admin.students.show', $student->id) }}" class="compact-btn compact-btn-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.students.edit', $student->id) }}" class="compact-btn compact-btn-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}" style="display:inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="compact-btn compact-btn-delete" title="Delete">
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

            {{-- INLINE PAGINATION - No Laravel links() to avoid Tailwind conflict --}}
            @if($students->hasPages())
            <div class="sp-container">
                <div class="sp-nav">
                    @if($students->onFirstPage())
                        <span class="sp-btn sp-disabled">&lsaquo;</span>
                    @else
                        <a href="{{ $students->previousPageUrl() }}" class="sp-btn">&lsaquo;</a>
                    @endif

                    @php
                        $currentPage = $students->currentPage();
                        $lastPage = $students->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);

                        if ($start > 1) {
                            echo '<a href="' . $students->url(1) . '" class="sp-btn">1</a>';
                            if ($start > 2) echo '<span class="sp-dots">...</span>';
                        }
                        for ($i = $start; $i <= $end; $i++) {
                            if ($i == $currentPage) {
                                echo '<span class="sp-btn sp-active">' . $i . '</span>';
                            } else {
                                echo '<a href="' . $students->url($i) . '" class="sp-btn">' . $i . '</a>';
                            }
                        }
                        if ($end < $lastPage) {
                            if ($end < $lastPage - 1) echo '<span class="sp-dots">...</span>';
                            echo '<a href="' . $students->url($lastPage) . '" class="sp-btn">' . $lastPage . '</a>';
                        }
                    @endphp

                    @if($students->hasMorePages())
                        <a href="{{ $students->nextPageUrl() }}" class="sp-btn">&rsaquo;</a>
                    @else
                        <span class="sp-btn sp-disabled">&rsaquo;</span>
                    @endif
                </div>
                <div class="sp-info">{{ $students->firstItem() }}-{{ $students->lastItem() }} of {{ $students->total() }}</div>
            </div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon"><i class="fas fa-user-graduate"></i></div>
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
/* ===== BASE LAYOUT ===== */
.modern-page { animation: cpFadeIn 0.3s ease-out; }
@keyframes cpFadeIn { from { opacity: 0; } to { opacity: 1; } }

.modern-page-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 0.75rem !important;
    flex-wrap: wrap !important;
    gap: 0.5rem !important;
}
.modern-page-header-left { flex: 1; }

/* Breadcrumb */
.modern-breadcrumb ol {
    display: flex; list-style: none; padding: 0; margin: 0;
    gap: 0.3rem; font-size: 0.72rem; align-items: center;
}
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.3rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* ===== COMPACT STATS ===== */
.compact-stats-row {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 0.5rem !important;
    margin-bottom: 0.75rem !important;
}
.compact-stat-card {
    background: #fff; border-radius: 8px; padding: 0.5rem 0.75rem;
    display: flex; align-items: center; gap: 0.5rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;
}
.compact-stat-icon {
    width: 32px; height: 32px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; flex-shrink: 0;
}
.compact-stat-blue { background: #eef2ff; color: #4361ee; }
.compact-stat-green { background: #ecfdf5; color: #10b981; }
.compact-stat-red { background: #fef2f2; color: #ef4444; }
.compact-stat-info { display: flex; flex-direction: column; }
.compact-stat-value { font-size: 1.1rem; font-weight: 800; color: #1a1a2e; line-height: 1.1; }
.compact-stat-label { font-size: 0.65rem; color: #6c757d; font-weight: 500; }

/* ===== CARD ===== */
.modern-card {
    background: #fff; border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;
    overflow: hidden; margin-bottom: 0.75rem;
}
.compact-card-header { padding: 0.5rem 0.75rem !important; }
.modern-card-header {
    display: flex !important; justify-content: space-between !important;
    align-items: center !important; padding: 0.5rem 0.75rem;
    border-bottom: 1px solid #f0f0f0; flex-wrap: wrap; gap: 0.5rem;
}
.modern-card-header-left { display: flex; align-items: center; gap: 0.4rem; }
.modern-card-title { font-size: 0.9rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-card-body { padding: 0 !important; }

/* Badge */
.compact-badge {
    display: inline-block; padding: 2px 8px; border-radius: 20px;
    font-size: 0.68rem; font-weight: 600; line-height: 1.4;
}
.badge-success { background: #ecfdf5; color: #059669; }
.badge-danger { background: #fef2f2; color: #dc2626; }
.badge-info { background: #eff6ff; color: #2563eb; }
.badge-warning { background: #fefce8; color: #b45309; }
.badge-light { background: #f3f4f6; color: #6b7280; }
.modern-badge { display: inline-block; padding: 2px 6px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; }
.modern-badge-light { background: #f3f4f6; color: #6b7280; }

/* Search */
.modern-search-box { position: relative; display: flex; align-items: center; }
.modern-search-box i { position: absolute; left: 8px; color: #adb5bd; font-size: 0.75rem; }
.modern-search-box input {
    border: 1px solid #e5e7eb; border-radius: 6px;
    padding: 0.3rem 0.5rem 0.3rem 1.75rem; font-size: 0.78rem;
    width: 160px; background: #f9fafb; color: #374151;
}
.modern-search-box input:focus { outline: none; border-color: #4361ee; background: #fff; }
.modern-search-box input::placeholder { color: #9ca3af; }

/* ===== TABLE ===== */
.modern-table-wrapper { overflow-x: auto; }
.modern-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; }
.modern-table thead th {
    background: #f9fafb; padding: 0.4rem 0.6rem; text-align: left;
    font-weight: 600; font-size: 0.65rem; text-transform: uppercase;
    letter-spacing: 0.3px; color: #6b7280; border-bottom: 2px solid #e5e7eb;
    white-space: nowrap;
}
.modern-table tbody tr { border-bottom: 1px solid #f3f4f6; }
.modern-table tbody tr:hover { background: #f8f9ff; }
.compact-table td { padding: 0.35rem 0.5rem !important; vertical-align: middle !important; }
.th-center, .td-center { text-align: center !important; }
.th-actions, .td-actions { text-align: right !important; white-space: nowrap !important; }
.th-narrow, .td-narrow { width: 36px !important; }

.compact-row-number {
    display: inline-flex !important; align-items: center !important;
    justify-content: center !important; width: 22px !important; height: 22px !important;
    border-radius: 5px !important; background: #f3f4f6 !important;
    color: #6b7280 !important; font-weight: 600 !important; font-size: 0.68rem !important;
    line-height: 1 !important;
}

/* Cell User */
.compact-cell-user { display: flex !important; align-items: center !important; gap: 0.35rem !important; }
.compact-avatar-img {
    width: 26px !important; height: 26px !important; border-radius: 6px !important;
    object-fit: cover !important; flex-shrink: 0 !important; display: block !important;
}
.compact-avatar-placeholder {
    width: 26px !important; height: 26px !important; border-radius: 6px !important;
    background: linear-gradient(135deg, #4361ee, #3a0ca3) !important;
    color: #fff !important; display: flex !important; align-items: center !important;
    justify-content: center !important; font-weight: 700 !important; font-size: 0.7rem !important;
    flex-shrink: 0 !important; line-height: 1 !important;
}
.compact-cell-name { font-weight: 600; color: #1a1a2e; font-size: 0.78rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px; }
.compact-cell-text { color: #4b5563; font-size: 0.75rem; max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; }

/* Action Buttons */
.compact-action-group { display: inline-flex !important; gap: 2px !important; }
.compact-btn {
    width: 24px !important; height: 24px !important; border-radius: 5px !important;
    border: none !important; display: inline-flex !important; align-items: center !important;
    justify-content: center !important; cursor: pointer !important; font-size: 0.65rem !important;
    text-decoration: none !important; transition: all 0.15s !important; padding: 0 !important;
    line-height: 1 !important;
}
.compact-btn-view { background: #eef2ff !important; color: #4361ee !important; }
.compact-btn-view:hover { background: #4361ee !important; color: #fff !important; }
.compact-btn-edit { background: #fefce8 !important; color: #d97706 !important; }
.compact-btn-edit:hover { background: #d97706 !important; color: #fff !important; }
.compact-btn-delete { background: #fef2f2 !important; color: #dc2626 !important; }
.compact-btn-delete:hover { background: #dc2626 !important; color: #fff !important; }

/* Header Buttons */
.btn-modern {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.35rem 0.7rem; border-radius: 6px; font-weight: 600;
    font-size: 0.75rem; text-decoration: none; border: none; cursor: pointer;
    transition: all 0.2s;
}
.btn-sm { padding: 0.3rem 0.6rem; font-size: 0.72rem; }
.btn-modern-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff; box-shadow: 0 1px 4px rgba(67,97,238,0.3);
}
.btn-modern-primary:hover { box-shadow: 0 2px 8px rgba(67,97,238,0.4); color: #fff; }
.btn-modern-outline { background: #fff; color: #4361ee; border: 1px solid #4361ee; }
.btn-modern-outline:hover { background: #4361ee; color: #fff; }

/* Alert */
.modern-alert {
    display: flex; align-items: center; gap: 0.4rem;
    padding: 0.5rem 0.75rem; margin: 0.4rem 0.75rem; border-radius: 6px;
    font-size: 0.78rem; font-weight: 500;
}
.modern-alert-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.modern-alert-close { margin-left: auto; background: none; border: none; cursor: pointer; color: inherit; opacity: 0.6; }
.modern-alert-close:hover { opacity: 1; }

/* Empty State */
.modern-empty-state { text-align: center; padding: 2.5rem 1.5rem; }
.modern-empty-icon { width: 56px; height: 56px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #d1d5db; margin-bottom: 0.75rem; }
.modern-empty-state h3 { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0 0 0.3rem; }
.modern-empty-state p { color: #9ca3af; font-size: 0.82rem; margin: 0 0 1rem; }

/* ============================================================
   SUPER COMPACT PAGINATION - No Tailwind/Bootstrap conflict
   Uses unique .sp-* prefix to avoid ANY CSS collision
   ============================================================ */
.sp-container {
    padding: 6px 12px !important;
    border-top: 1px solid #f0f0f0 !important;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
    background: #fff !important;
}

.sp-nav {
    display: flex !important;
    align-items: center !important;
    gap: 3px !important;
}

.sp-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 26px !important;
    height: 26px !important;
    padding: 0 5px !important;
    border-radius: 5px !important;
    font-size: 0.7rem !important;
    font-weight: 600 !important;
    color: #4b5563 !important;
    background: #f3f4f6 !important;
    border: 1px solid #e5e7eb !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: all 0.15s !important;
    line-height: 1 !important;
    margin: 0 !important;
}

.sp-btn:hover {
    background: #4361ee !important;
    color: #fff !important;
    border-color: #4361ee !important;
}

.sp-active {
    background: #4361ee !important;
    color: #fff !important;
    border-color: #4361ee !important;
    cursor: default !important;
}

.sp-disabled {
    color: #d1d5db !important;
    background: #f9fafb !important;
    border-color: #e5e7eb !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

.sp-dots {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 20px !important;
    height: 26px !important;
    font-size: 0.7rem !important;
    color: #9ca3af !important;
}

.sp-info {
    font-size: 0.65rem !important;
    color: #9ca3af !important;
    margin-left: 4px !important;
}

/* ===== NUCLEAR OVERRIDE - kill any Tailwind pagination bleed ===== */
.modern-card .pagination,
.modern-card nav[role="navigation"] {
    display: none !important;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column !important; align-items: stretch !important; }
    .compact-stats-row { grid-template-columns: 1fr 1fr 1fr !important; gap: 0.35rem !important; }
    .compact-stat-card { padding: 0.4rem 0.5rem; gap: 0.3rem; }
    .compact-stat-icon { width: 28px; height: 28px; font-size: 0.75rem; }
    .compact-stat-value { font-size: 0.95rem; }
    .compact-stat-label { font-size: 0.6rem; }
    .modern-card-header { flex-direction: column !important; align-items: stretch !important; padding: 0.4rem 0.5rem !important; }
    .modern-search-box input { width: 100%; }
    .modern-table { font-size: 0.72rem; }
    .compact-table td { padding: 0.25rem 0.35rem !important; }
    .compact-cell-text { max-width: 70px; }
    .compact-avatar-img, .compact-avatar-placeholder { width: 22px !important; height: 22px !important; font-size: 0.6rem !important; }
    .compact-btn { width: 22px !important; height: 22px !important; font-size: 0.6rem !important; }
    .btn-modern { padding: 0.3rem 0.6rem; font-size: 0.7rem; }
    .sp-btn { min-width: 24px !important; height: 24px !important; font-size: 0.65rem !important; }
    .sp-info { font-size: 0.6rem !important; }
    .compact-cell-name { max-width: 100px; }
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
