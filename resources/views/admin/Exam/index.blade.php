@extends('layouts.admin')
@section('title', 'Exam Schedule')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Academics</a></li>
                    <li class="active">Exams</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.exams.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i>
                <span>Schedule Exam</span>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    @php
        $today = \Carbon\Carbon::today()->format('Y-m-d');
        $upcomingCount = 0;
        $completedCount = 0;
        $ongoingCount = 0;
        foreach ($data as $exam) {
            if ($exam->end_date && $today > $exam->end_date->format('Y-m-d')) {
                $completedCount++;
            } elseif ($exam->start_date && $today >= $exam->start_date->format('Y-m-d')) {
                $ongoingCount++;
            } else {
                $upcomingCount++;
            }
        }
    @endphp

    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $data->total() }}</span>
                <span class="modern-stat-label">Total Exams</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold">
                <i class="fas fa-clock"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $upcomingCount }}</span>
                <span class="modern-stat-label">Upcoming</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $completedCount }}</span>
                <span class="modern-stat-label">Completed</span>
            </div>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="modern-info-banner">
        <i class="fas fa-info-circle"></i>
        <span>All exams are scheduled for <strong>all subjects</strong> and <strong>all classes</strong> simultaneously.</span>
    </div>

    {{-- Exams Table Card --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">All Exams</h2>
                <span class="modern-badge modern-badge-light">{{ $data->total() }} records</span>
            </div>
            <div class="modern-card-header-right">
                <div class="modern-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="examSearch" placeholder="Search exams..." onkeyup="filterTable()">
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

            @if($data->count() > 0)
            <div class="modern-table-wrapper">
                <table class="modern-table" id="examTable">
                    <thead>
                        <tr>
                            <th class="th-narrow">#</th>
                            <th>Exam Name</th>
                            <th>Type</th>
                            <th class="th-center">Marks</th>
                            <th>Academic Year</th>
                            <th>Term</th>
                            <th>Schedule</th>
                            <th>Daily Time</th>
                            <th class="th-center">Status</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        @php
                            $status = 'upcoming';
                            $statusLabel = 'Upcoming';
                            if ($item->end_date && $today > $item->end_date->format('Y-m-d')) {
                                $status = 'completed';
                                $statusLabel = 'Completed';
                            } elseif ($item->start_date && $today >= $item->start_date->format('Y-m-d')) {
                                $status = 'ongoing';
                                $statusLabel = 'Ongoing';
                            }
                        @endphp
                        <tr class="modern-table-row">
                            <td class="td-narrow">
                                <span class="modern-row-number">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</span>
                            </td>
                            <td>
                                <div class="modern-cell-title">{{ $item->name }}</div>
                            </td>
                            <td>
                                <span class="modern-badge modern-badge-light">{{ ucfirst(str_replace('_', ' ', $item->type)) }}</span>
                            </td>
                            <td class="td-center">
                                <span class="modern-cell-marks">{{ $item->total_marks }}</span>
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $item->academicYear->name ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $item->term->name ?? '-' }}</div>
                            </td>
                            <td>
                                @if($item->start_date)
                                    <div class="modern-cell-date">{{ $item->start_date->format('M d, Y') }}</div>
                                    <div class="modern-cell-sub">to {{ $item->end_date ? $item->end_date->format('M d, Y') : '-' }}</div>
                                @else
                                    <span class="modern-cell-muted">Not set</span>
                                @endif
                            </td>
                            <td>
                                @if($item->start_time)
                                    <div class="modern-cell-time">{{ $item->start_time }} - {{ $item->end_time ?? '-' }}</div>
                                @else
                                    <span class="modern-cell-muted">-</span>
                                @endif
                            </td>
                            <td class="td-center">
                                @if($status === 'completed')
                                    <span class="modern-badge modern-badge-success"><i class="fas fa-check-circle"></i> {{ $statusLabel }}</span>
                                @elseif($status === 'ongoing')
                                    <span class="modern-badge modern-badge-warning"><i class="fas fa-clock"></i> {{ $statusLabel }}</span>
                                @else
                                    <span class="modern-badge modern-badge-info"><i class="fas fa-calendar-alt"></i> {{ $statusLabel }}</span>
                                @endif
                            </td>
                            <td class="td-actions">
                                <div class="modern-action-group">
                                    <a href="{{ route('admin.exams.show', $item->id) }}" class="modern-btn-icon modern-btn-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.exams.edit', $item->id) }}" class="modern-btn-icon modern-btn-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.exams.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this exam schedule?')">
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
            @if($data->hasPages())
            <div class="modern-pagination-wrapper">
                {{ $data->withQueryString()->links() }}
            </div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3>No Exams Scheduled</h3>
                <p>Get started by scheduling your first exam.</p>
                <a href="{{ route('admin.exams.create') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-plus"></i> Schedule Exam
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

/* Info Banner */
.modern-info-banner {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.85rem 1.25rem;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 12px;
    margin-bottom: 1.75rem;
    font-size: 0.88rem;
    color: #1e40af;
}

.modern-info-banner i { color: #3b82f6; font-size: 1rem; }
.modern-info-banner strong { color: #1e3a8a; }

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

.modern-stat-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-stat-icon-green { background: #ecfdf5; color: #10b981; }
.modern-stat-icon-gold { background: #fefce8; color: #d97706; }

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
.modern-badge-gold { background: #fefce8; color: #b45309; }
.modern-badge-warning { background: #fffbeb; color: #d97706; }
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

.modern-cell-marks {
    font-weight: 700;
    color: #1a1a2e;
    font-size: 0.95rem;
}

.modern-cell-date {
    font-weight: 500;
    color: #374151;
    font-size: 0.88rem;
}

.modern-cell-time {
    color: #4b5563;
    font-size: 0.88rem;
    font-weight: 500;
}

.modern-cell-muted { color: #d1d5db; }

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

.modern-btn-view { background: #eef2ff; color: #4361ee; }
.modern-btn-view:hover { background: #4361ee; color: #fff; transform: translateY(-1px); }

.modern-btn-edit { background: #fefce8; color: #d97706; }
.modern-btn-edit:hover { background: #d97706; color: #fff; transform: translateY(-1px); }

.modern-btn-delete { background: #fef2f2; color: #dc2626; }
.modern-btn-delete:hover { background: #dc2626; color: #fff; transform: translateY(-1px); }

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

/* Responsive */
@media (max-width: 768px) {
    .modern-page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .modern-stats-row {
        grid-template-columns: 1fr;
    }

    .modern-card-header {
        flex-direction: column;
        align-items: stretch;
    }

    .modern-search-box input { width: 100%; }

    .modern-table { font-size: 0.82rem; }

    .modern-cell-text { max-width: 120px; }

    .modern-info-banner { flex-direction: column; text-align: center; }
}
</style>
@endpush

@push('scripts')
<script>
function filterTable() {
    const input = document.getElementById('examSearch');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('examTable');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
