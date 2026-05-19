@extends('layouts.admin')
@section('title', 'Inactive Students')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.students.index') }}">Students</a></li>
                    <li class="active">Inactive Students</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.students.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-user-graduate"></i>
                <span>Active Students</span>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-red">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalInactive ?? 0 }}</span>
                <span class="modern-stat-label">Total Inactive</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-orange">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalTransferred ?? 0 }}</span>
                <span class="modern-stat-label">Transferred</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $canBeReadmitted ?? 0 }}</span>
                <span class="modern-stat-label">Can Be Readmitted</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gray">
                <i class="fas fa-list"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $students->total() }}</span>
                <span class="modern-stat-label">Showing</span>
            </div>
        </div>
    </div>

    {{-- Students Table Card --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">Inactive & Transferred Students</h2>
                <span class="modern-badge modern-badge-light">{{ $students->total() }} records</span>
            </div>
            <div class="modern-card-header-right">
                <div class="modern-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="studentSearch" placeholder="Search inactive students..." onkeyup="filterTable()">
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
                <table class="modern-table" id="studentTable">
                    <thead>
                        <tr>
                            <th class="th-narrow">#</th>
                            <th>Student</th>
                            <th>Admission No</th>
                            <th>Previous Class</th>
                            <th>Leave Date</th>
                            <th>Leave Reason</th>
                            <th class="th-center">Status</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr class="modern-table-row">
                            <td class="td-narrow">
                                <span class="modern-row-number">{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</span>
                            </td>
                            <td>
                                <div class="modern-cell-user">
                                    @if($student->photo)
                                        <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->full_name }}" class="modern-avatar modern-avatar-img">
                                    @else
                                        <div class="modern-avatar modern-avatar-placeholder">
                                            {{ strtoupper(substr($student->full_name ?? 'S', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="modern-cell-user-info">
                                        <div class="modern-cell-title">{{ $student->full_name }}</div>
                                        @if($student->email)
                                            <div class="modern-cell-sub">{{ $student->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="modern-cell-text">
                                    <span class="modern-cell-admission-no">{{ $student->admission_number ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $student->classroom?->name ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="modern-cell-text">
                                    @if($student->leave_date)
                                        <span class="modern-cell-date">{{ \Carbon\Carbon::parse($student->leave_date)->format('M d, Y') }}</span>
                                    @else
                                        <span class="modern-cell-muted">-</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="modern-cell-text" title="{{ $student->leave_reason ?? '' }}">
                                    {{ $student->leave_reason ? \Illuminate\Support\Str::limit($student->leave_reason, 40) : '-' }}
                                </div>
                            </td>
                            <td class="td-center">
                                @php
                                    $statusBadge = match($student->status ?? '') {
                                        'inactive' => 'modern-badge-danger',
                                        'transferred' => 'modern-badge-warning',
                                        'left' => 'modern-badge-danger',
                                        default => 'modern-badge-light'
                                    };
                                    $statusLabel = match($student->status ?? '') {
                                        'inactive' => 'Inactive',
                                        'transferred' => 'Transferred',
                                        'left' => 'Left',
                                        default => ucfirst($student->status ?? 'N/A')
                                    };
                                @endphp
                                <span class="modern-badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="td-actions">
                                <div class="modern-action-group">
                                    <a href="{{ route('admin.students.readmit', $student->id) }}" class="modern-btn-icon modern-btn-readmit" title="Readmit">
                                        <i class="fas fa-user-check"></i>
                                    </a>
                                    <a href="{{ route('admin.students.show', $student->id) }}" class="modern-btn-icon modern-btn-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($students->hasPages())
            <div class="modern-pagination-wrapper">
                {{ $students->withQueryString()->links() }}
            </div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon">
                    <i class="fas fa-user-slash"></i>
                </div>
                <h3>No Inactive Students</h3>
                <p>There are currently no inactive or transferred students to display.</p>
                <a href="{{ route('admin.students.index') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-user-graduate"></i> View Active Students
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

/* Stats Row */
.modern-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
}

.modern-stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    transition: transform 0.2s, box-shadow 0.2s;
}

.modern-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.modern-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.modern-stat-icon-red { background: #fef2f2; color: #ef4444; }
.modern-stat-icon-orange { background: #fff7ed; color: #f97316; }
.modern-stat-icon-green { background: #ecfdf5; color: #10b981; }
.modern-stat-icon-gray { background: #f3f4f6; color: #6b7280; }

.modern-stat-info { display: flex; flex-direction: column; }

.modern-stat-value {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1.2;
}

.modern-stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
}

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

.modern-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}

.modern-card-body { padding: 0; }

/* Badges */
.modern-badge {
    display: inline-flex;
    align-items: center;
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

/* Search Box */
.modern-search-box {
    position: relative;
    display: flex;
    align-items: center;
}

.modern-search-box i {
    position: absolute;
    left: 12px;
    color: #adb5bd;
    font-size: 0.85rem;
}

.modern-search-box input {
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.55rem 0.75rem 0.55rem 2.25rem;
    font-size: 0.875rem;
    width: 220px;
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

.modern-table.th-center, .modern-table thead th.th-center { text-align: center; }
.modern-table.th-actions, .modern-table thead th.th-actions { text-align: right; }

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

.th-center, .td-center { text-align: center !important; }
.th-actions, .td-actions { text-align: right !important; }
.th-narrow, .td-narrow { width: 50px; }

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

/* Cell User (Avatar + Info) */
.modern-cell-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modern-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    flex-shrink: 0;
    object-fit: cover;
}

.modern-avatar-img {
    width: 40px;
    height: 40px;
    border-radius: 10px;
}

.modern-avatar-placeholder {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.95rem;
}

.modern-cell-user-info {
    display: flex;
    flex-direction: column;
}

.modern-cell-title {
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 2px;
}

.modern-cell-sub {
    font-size: 0.8rem;
    color: #9ca3af;
}

.modern-cell-text {
    color: #4b5563;
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.modern-cell-muted { color: #d1d5db; }

.modern-cell-admission-no {
    font-weight: 600;
    color: #4361ee;
    font-size: 0.88rem;
    letter-spacing: 0.3px;
}

.modern-cell-date {
    font-size: 0.88rem;
    color: #4b5563;
}

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

.modern-btn-view {
    background: #eef2ff;
    color: #4361ee;
}

.modern-btn-view:hover { background: #4361ee; color: #fff; transform: translateY(-1px); }

.modern-btn-readmit {
    background: #ecfdf5;
    color: #059669;
}

.modern-btn-readmit:hover { background: #059669; color: #fff; transform: translateY(-1px); }

.modern-btn-edit {
    background: #fefce8;
    color: #d97706;
}

.modern-btn-edit:hover { background: #d97706; color: #fff; transform: translateY(-1px); }

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
    background: #fff;
    color: #4361ee;
    border: 1.5px solid #4361ee;
}

.btn-modern-outline:hover {
    background: #4361ee;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(67, 97, 238, 0.3);
}

/* Alert */
.modern-alert {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.85rem 1.25rem;
    margin: 1rem 1.5rem;
    border-radius: 10px;
    font-size: 0.88rem;
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

.modern-pagination-wrapper :deep(.pagination) {
    gap: 0.25rem;
}

/* Responsive */
@media (max-width: 768px) {
    .modern-page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .modern-stats-row {
        grid-template-columns: 1fr 1fr;
    }

    .modern-card-header {
        flex-direction: column;
        align-items: stretch;
    }

    .modern-search-box input { width: 100%; }

    .modern-table { font-size: 0.82rem; }

    .modern-cell-text { max-width: 100px; }

    .modern-cell-user-info .modern-cell-sub { display: none; }
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
