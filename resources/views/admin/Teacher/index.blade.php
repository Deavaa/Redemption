@extends('layouts.admin')
@section('title', 'Teachers')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">People</a></li>
                    <li class="active">Teachers</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.chat.index') }}" class="btn-modern btn-modern-ghost" style="color:#ea580c;border-color:#ea580c;" title="Send Message to Teachers">
                <i class="fas fa-paper-plane"></i>
                <span>Message</span>
            </a>
            <a href="{{ route('admin.teachers.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i>
                <span>Add Teacher</span>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $data->total() }}</span>
                <span class="modern-stat-label">Total Teachers</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $data->where('status', 'active')->count() }}</span>
                <span class="modern-stat-label">Active</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-purple">
                <i class="fas fa-building"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $data->pluck('department')->unique()->filter()->count() }}</span>
                <span class="modern-stat-label">Departments</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-orange">
                <i class="fas fa-calendar-minus"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $data->where('status', 'on_leave')->count() }}</span>
                <span class="modern-stat-label">On Leave</span>
            </div>
        </div>
    </div>

    {{-- Teachers Table Card --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">All Teachers</h2>
                <span class="modern-badge modern-badge-light">{{ $data->total() }} records</span>
            </div>
            <div class="modern-card-header-right">
                <div class="modern-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="teacherSearch" placeholder="Search teachers..." onkeyup="filterTable()">
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
                <table class="modern-table" id="teacherTable">
                    <thead>
                        <tr>
                            <th class="th-narrow">#</th>
                            <th>Teacher</th>
                            <th>Phone</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Qualification</th>
                            <th class="th-center">Status</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr class="modern-table-row">
                            <td class="td-narrow">
                                <span class="modern-row-number">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</span>
                            </td>
                            <td>
                                <div class="modern-cell-user">
                                    <div class="modern-avatar modern-avatar-{{ collect(['blue','green','purple','orange','pink','teal'])[crc32($item->id) % 6] }}">
                                        {{ strtoupper(substr($item->full_name ?? 'NA', 0, 1)) }}{{ strtoupper(substr(preg_replace('/^[^ ]+\\s*/', '', $item->full_name ?? 'A'), 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="modern-cell-title">{{ $item->full_name }}</div>
                                        @if($item->email)
                                            <div class="modern-cell-sub">{{ $item->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($item->phone)
                                    <div class="modern-cell-contact">
                                        <i class="fas fa-phone"></i> {{ $item->phone }}
                                    </div>
                                @else
                                    <span class="modern-cell-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->branch)
                                    <span class="modern-badge modern-badge-branch">{{ $item->branch->name }}</span>
                                @else
                                    <span class="modern-cell-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->department)
                                    <span class="modern-badge modern-badge-department">{{ $item->department }}</span>
                                @else
                                    <span class="modern-cell-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $item->qualification ?? '-' }}</div>
                            </td>
                            <td class="td-center">
                                @if($item->status === 'active')
                                    <span class="modern-badge modern-badge-success">Active</span>
                                @elseif($item->status === 'on_leave')
                                    <span class="modern-badge modern-badge-warning">On Leave</span>
                                @else
                                    <span class="modern-badge modern-badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="td-actions">
                                <div class="modern-action-group">
                                    <a href="{{ route('admin.teachers.show', $item->id) }}" class="modern-btn-icon modern-btn-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.teachers.edit', $item->id) }}" class="modern-btn-icon modern-btn-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="{{ route('admin.chat.index') }}?recipient_id={{ $item->user_id ?? $item->id }}&recipient_type=teacher" class="modern-btn-icon modern-btn-msg" title="Send Message">
                                        <i class="fas fa-paper-plane"></i>
                                    </a>
                                    <a href="{{ route('admin.teachers.transfer', $item->id) }}" class="modern-btn-icon" style="background:#eef2ff;color:#4361ee;" title="Transfer">
                                        <i class="fas fa-exchange-alt"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.teachers.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this teacher?')">
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
            <div class="modern-pagination-wrapper" style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:10px;">
                <div style="display:flex;align-items:center;gap:3px;">
                    @if($data->onFirstPage())
                        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;padding:0 5px;border-radius:6px;font-size:0.75rem;font-weight:600;color:#d1d5db;background:#f9fafb;border:1px solid #e5e7eb;cursor:not-allowed;">&lsaquo;</span>
                    @else
                        <a href="{{ $data->previousPageUrl() }}" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;padding:0 5px;border-radius:6px;font-size:0.75rem;font-weight:600;color:#4b5563;background:#f3f4f6;border:1px solid #e5e7eb;text-decoration:none;cursor:pointer;transition:all 0.15s;">&lsaquo;</a>
                    @endif

                    @php
                        $cp = $data->currentPage();
                        $lp = $data->lastPage();
                        $s = max(1, $cp - 2);
                        $e = min($lp, $cp + 2);
                        if ($s > 1) { echo '<a href="' . $data->url(1) . '" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;padding:0 5px;border-radius:6px;font-size:0.75rem;font-weight:600;color:#4b5563;background:#f3f4f6;border:1px solid #e5e7eb;text-decoration:none;cursor:pointer;">1</a>'; if ($s > 2) echo '<span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:28px;font-size:0.75rem;color:#9ca3af;">...</span>'; }
                        for ($i = $s; $i <= $e; $i++) {
                            if ($i == $cp) echo '<span style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;padding:0 5px;border-radius:6px;font-size:0.75rem;font-weight:600;color:#fff;background:#4361ee;border:1px solid #4361ee;">' . $i . '</span>';
                            else echo '<a href="' . $data->url($i) . '" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;padding:0 5px;border-radius:6px;font-size:0.75rem;font-weight:600;color:#4b5563;background:#f3f4f6;border:1px solid #e5e7eb;text-decoration:none;cursor:pointer;">' . $i . '</a>';
                        }
                        if ($e < $lp) { if ($e < $lp - 1) echo '<span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:28px;font-size:0.75rem;color:#9ca3af;">...</span>'; echo '<a href="' . $data->url($lp) . '" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;padding:0 5px;border-radius:6px;font-size:0.75rem;font-weight:600;color:#4b5563;background:#f3f4f6;border:1px solid #e5e7eb;text-decoration:none;cursor:pointer;">' . $lp . '</a>'; }
                    @endphp

                    @if($data->hasMorePages())
                        <a href="{{ $data->nextPageUrl() }}" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;padding:0 5px;border-radius:6px;font-size:0.75rem;font-weight:600;color:#4b5563;background:#f3f4f6;border:1px solid #e5e7eb;text-decoration:none;cursor:pointer;transition:all 0.15s;">&rsaquo;</a>
                    @else
                        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;padding:0 5px;border-radius:6px;font-size:0.75rem;font-weight:600;color:#d1d5db;background:#f9fafb;border:1px solid #e5e7eb;cursor:not-allowed;">&rsaquo;</span>
                    @endif
                </div>
                <span style="font-size:0.7rem;color:#9ca3af;">{{ $data->firstItem() }}-{{ $data->lastItem() }} of {{ $data->total() }}</span>
            </div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h3>No Teachers Yet</h3>
                <p>Get started by adding your first teacher.</p>
                <a href="{{ route('admin.teachers.create') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-plus"></i> Add Teacher
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

.modern-stat-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-stat-icon-green { background: #ecfdf5; color: #10b981; }
.modern-stat-icon-purple { background: #f3e8ff; color: #7c3aed; }
.modern-stat-icon-orange { background: #fff7ed; color: #f97316; }

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
.modern-badge-warning { background: #fffbeb; color: #d97706; }
.modern-badge-department { background: #f3e8ff; color: #7c3aed; }
.modern-badge-branch { background: #eff6ff; color: #2563eb; }

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

/* Cell User with Avatar */
.modern-cell-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modern-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    flex-shrink: 0;
    color: #fff;
}

.modern-avatar-blue { background: linear-gradient(135deg, #4361ee, #3a0ca3); }
.modern-avatar-green { background: linear-gradient(135deg, #10b981, #059669); }
.modern-avatar-purple { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
.modern-avatar-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
.modern-avatar-pink { background: linear-gradient(135deg, #ec4899, #db2777); }
.modern-avatar-teal { background: linear-gradient(135deg, #14b8a6, #0d9488); }

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
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.modern-cell-contact {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: #4b5563;
    font-size: 0.88rem;
}

.modern-cell-contact i { color: #4361ee; font-size: 0.75rem; }
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

.modern-btn-view {
    background: #eef2ff;
    color: #4361ee;
}
.modern-btn-view:hover { background: #4361ee; color: #fff; transform: translateY(-1px); }

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

.modern-btn-msg {
    background: #fff7ed;
    color: #ea580c;
}
.modern-btn-msg:hover { background: #ea580c; color: #fff; transform: translateY(-1px); }

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

    .modern-cell-text { max-width: 150px; }
}
</style>
@endpush

@push('scripts')
<script>
function filterTable() {
    const input = document.getElementById('teacherSearch');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('teacherTable');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
