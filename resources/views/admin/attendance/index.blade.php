@extends('layouts.admin')
@section('title', 'Attendance Dashboard')

@section('content')
<div class="modern-page">
    {{-- Header --}}
    <div class="modern-page-header" style="margin-bottom:0.75rem;">
        <div class="modern-page-header-left" style="display:flex;align-items:center;gap:10px;">
            <nav aria-label="breadcrumb" class="modern-breadcrumb" style="margin:0;">
                <ol style="margin:0;">
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li class="active">Attendance</li>
                </ol>
            </nav>
            <span style="color:var(--border);font-size:0.65rem;">|</span>
            <h1 style="font-size:0.85rem;font-weight:700;color:var(--text-dark);margin:0;">Attendance Dashboard</h1>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.attendance.report') }}" class="btn-modern btn-modern-ghost att-header-btn"><i class="fas fa-chart-bar"></i> Report</a>
            <a href="{{ route('admin.attendance.create') }}" class="btn-modern btn-modern-primary att-header-btn">
                <i class="fas fa-plus"></i> Record Attendance
            </a>
        </div>
    </div>

    {{-- Stats Cards (compact) --}}
    <div class="att-stats-grid">
        <div class="att-stat-card" style="padding:4px 8px;border-left:3px solid #6366f1;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div class="att-stat-label">Attendance Rate</div>
                    <div class="att-stat-value" style="color:#6366f1;">{{ $attendanceRate }}%</div>
                </div>
                <div class="att-stat-icon" style="background:rgba(99,102,241,0.1);">
                    <i class="fas fa-percentage" style="color:#6366f1;"></i>
                </div>
            </div>
        </div>
        <div class="att-stat-card" style="padding:4px 8px;border-left:3px solid #10b981;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div class="att-stat-label">Present</div>
                    <div class="att-stat-value" style="color:#10b981;">{{ $presentCount }}</div>
                </div>
                <div class="att-stat-icon" style="background:rgba(16,185,129,0.1);">
                    <i class="fas fa-check-circle" style="color:#10b981;"></i>
                </div>
            </div>
        </div>
        <div class="att-stat-card" style="padding:4px 8px;border-left:3px solid #ef4444;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div class="att-stat-label">Absent</div>
                    <div class="att-stat-value" style="color:#ef4444;">{{ $absentCount }}</div>
                </div>
                <div class="att-stat-icon" style="background:rgba(239,68,68,0.1);">
                    <i class="fas fa-times-circle" style="color:#ef4444;"></i>
                </div>
            </div>
        </div>
        <div class="att-stat-card" style="padding:4px 8px;border-left:3px solid #f59e0b;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div class="att-stat-label">Late</div>
                    <div class="att-stat-value" style="color:#f59e0b;">{{ $lateCount }}</div>
                </div>
                <div class="att-stat-icon" style="background:rgba(245,158,11,0.1);">
                    <i class="fas fa-clock" style="color:#f59e0b;"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Compact Filter Summary (shown when filter is active) --}}
    @if($classId || $date !== now()->format('Y-m-d'))
    <div class="att-filter-summary visible" id="attFilterSummary">
        <i class="fas fa-check-circle" style="color:#10b981;"></i>
        <span id="attFilterSummaryText">
            @if($classId)
                <span class="att-filter-chip"><i class="fas fa-chalkboard"></i> {{ $classes->where('id', $classId)->first()?->name ?? 'Class' }}</span>
            @endif
            <span class="att-filter-chip"><i class="fas fa-calendar-alt"></i> {{ $date }}</span>
        </span>
        <a href="{{ route('admin.attendance.index') }}" class="att-filter-change-btn">
            <i class="fas fa-times"></i> Clear
        </a>
    </div>
    @endif

    {{-- Filter Bar --}}
    <div class="modern-card att-filter-card {{ ($classId || $date !== now()->format('Y-m-d')) ? 'att-filter-collapsed' : '' }}" id="attFilterPanel" style="margin-bottom:12px;">
        <div class="certgen-toolbar">
            <span class="certgen-toolbar-label"><i class="fas fa-filter" style="margin-right:4px;"></i> Filter</span>
        </div>
        <div class="att-filter-body" style="padding:8px 14px;">
            <form method="GET" action="{{ route('admin.attendance.index') }}" class="att-filter-form">
                <div class="att-filter-group">
                    <label class="att-filter-label">Date</label>
                    <input type="date" name="date" value="{{ $date }}" class="att-filter-input">
                </div>
                <div class="att-filter-group">
                    <label class="att-filter-label">Class</label>
                    <select name="class_id" class="att-filter-input">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-modern btn-modern-primary att-filter-btn"><i class="fas fa-search"></i> Apply</button>
                <a href="{{ route('admin.attendance.index') }}" class="btn-modern btn-modern-ghost att-filter-btn">Clear</a>
            </form>
        </div>
    </div>

    {{-- Class Summary Table --}}
    <div class="modern-card" style="margin-bottom:12px;">
        <div class="certgen-toolbar">
            <span class="certgen-toolbar-label"><i class="fas fa-chalkboard" style="margin-right:4px;"></i> Attendance by Class — {{ $date }}</span>
        </div>
        <div style="padding:0;">
            <div class="table-responsive">
                <table class="modern-table att-table">
                    <thead>
                        <tr>
                            <th class="att-th att-th-left">Class</th>
                            <th class="att-th">Total</th>
                            <th class="att-th">Present</th>
                            <th class="att-th">Absent</th>
                            <th class="att-th att-hide-mobile">Late</th>
                            <th class="att-th att-hide-mobile">Excused</th>
                            <th class="att-th">Rate</th>
                            <th class="att-th att-hide-mobile">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classSummary as $class)
                        <tr>
                            <td class="att-td att-td-left att-td-bold">{{ $class->name }}</td>
                            <td class="att-td">{{ $class->att_total }}</td>
                            <td class="att-td"><span class="att-badge att-badge-present">{{ $class->att_present }}</span></td>
                            <td class="att-td"><span class="att-badge att-badge-absent">{{ $class->att_absent }}</span></td>
                            <td class="att-td att-hide-mobile"><span class="att-badge att-badge-late">{{ $class->att_late }}</span></td>
                            <td class="att-td att-hide-mobile"><span class="att-badge att-badge-excused">{{ $class->att_excused }}</span></td>
                            <td class="att-td att-td-bold" style="color:{{ $class->att_rate !== null ? ($class->att_rate >= 80 ? '#10b981' : ($class->att_rate >= 60 ? '#f59e0b' : '#ef4444')) : 'var(--text-muted)' }};">{{ $class->att_rate !== null ? $class->att_rate . '%' : '-' }}</td>
                            <td class="att-td att-hide-mobile">
                                <a href="{{ route('admin.attendance.edit', ['date' => $date, 'classId' => $class->id]) }}" class="btn-modern btn-modern-ghost" style="font-size:0.65rem;padding:2px 8px;" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('admin.attendance.create', ['class_id' => $class->id, 'date' => $date]) }}" class="btn-modern btn-modern-ghost" style="font-size:0.65rem;padding:2px 8px;" title="Record"><i class="fas fa-plus"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="att-empty-cell">
                                <i class="fas fa-clipboard-check"></i>
                                No attendance records for this date.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent Records --}}
    @if($recentRecords->isNotEmpty())
    <div class="modern-card">
        <div class="certgen-toolbar">
            <span class="certgen-toolbar-label"><i class="fas fa-history" style="margin-right:4px;"></i> Recent Records</span>
        </div>
        <div style="padding:0;">
            <div class="table-responsive">
                <table class="modern-table att-table">
                    <thead>
                        <tr>
                            <th class="att-th att-th-left">Student</th>
                            <th class="att-th att-hide-mobile">Roll #</th>
                            <th class="att-th att-hide-mobile">Class</th>
                            <th class="att-th att-hide-mobile">Section</th>
                            <th class="att-th">Status</th>
                            <th class="att-th att-hide-mobile">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentRecords as $record)
                        <tr>
                            <td class="att-td att-td-left att-td-bold">
                                {{ $record->student?->full_name ?? '' }}
                                <span class="att-mobile-meta">
                                    @if($record->student?->roll_number) #{{ $record->student->roll_number }}@endif
                                    {{ $record->classRoom?->name }} {{ $record->section?->name ?? '' }}
                                </span>
                            </td>
                            <td class="att-td att-hide-mobile">{{ $record->student?->roll_number ?? '-' }}</td>
                            <td class="att-td att-hide-mobile">{{ $record->classRoom?->name }}</td>
                            <td class="att-td att-hide-mobile">{{ $record->section?->name ?? '-' }}</td>
                            <td class="att-td">
                                @php
                                    $statusColors = ['present' => '#10b981', 'absent' => '#ef4444', 'late' => '#f59e0b', 'excused' => '#3b82f6'];
                                @endphp
                                <span class="att-badge att-badge-{{ $record->status }}">{{ ucfirst($record->status) }}</span>
                            </td>
                            <td class="att-td att-hide-mobile" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $record->remarks ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
/* ===== Attendance Dashboard Styles ===== */
.att-stats-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:12px; }
.att-stat-label { font-size:7px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:1px; }
.att-stat-value { font-size:14px;font-weight:800;line-height:1.2; }
.att-stat-icon { width:24px;height:24px;border-radius:8px;display:flex;align-items:center;justify-content:center; }
.att-stat-icon i { font-size:10px; }
.att-stat-card { background:var(--card-bg);border-radius:10px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,0.04);transition: transform 0.15s, box-shadow 0.15s; }
.att-stat-card:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,0.06); }

/* Filter collapse */
.att-filter-card.att-filter-collapsed .att-filter-body { display: none; }
.att-filter-card.att-filter-collapsed .certgen-toolbar { border-bottom: none; }

/* Filter summary */
.att-filter-summary { display:none;align-items:center;gap:0.5rem;padding:0.6rem 1rem;background:#f0fdf4;border:1.5px solid #a7f3d0;border-radius:10px;margin-bottom:0.75rem;font-size:0.82rem;font-weight:600;color:#065f46;flex-wrap:wrap; }
.att-filter-summary.visible { display:flex; }
.att-filter-chip { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#fff;border:1px solid #d1fae5;border-radius:6px;font-size:0.78rem;color:#1a1a2e; }
.att-filter-chip i { font-size:0.7rem;color:#10b981; }
.att-filter-change-btn { margin-left:auto;padding:4px 12px;border-radius:6px;border:1px solid #a7f3d0;background:#fff;color:#059669;font-size:0.78rem;font-weight:600;cursor:pointer;transition:all 0.2s;text-decoration:none;white-space:nowrap; }
.att-filter-change-btn:hover { background:#ecfdf5;border-color:#10b981; }

/* Filter form */
.att-filter-form { display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap; }
.att-filter-group { display:flex;flex-direction:column; }
.att-filter-label { font-size:9px;font-weight:600;color:var(--text-muted);margin-bottom:2px;text-transform:uppercase; }
.att-filter-input { border:1.5px solid var(--border);border-radius:8px;padding:5px 10px;font-size:12px;font-family:var(--font);color:var(--text-dark);background:var(--card-bg);min-width:150px; }
.att-filter-btn { font-size:0.7rem;padding:5px 12px; }

/* Table styles */
.att-table { width:100%;border-collapse:collapse;font-size:12px; }
.att-th { padding:8px 10px;text-align:center;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);background:var(--bg);border-bottom:2px solid var(--border); }
.att-th-left { text-align:left;padding-left:14px; }
.att-td { padding:8px 10px;text-align:center;border-bottom:1px solid var(--border);color:var(--text); }
.att-td-left { text-align:left;padding-left:14px; }
.att-td-bold { font-weight:600;color:var(--text-dark); }

/* Badges */
.att-badge { display:inline-block;padding:2px 8px;border-radius:6px;font-weight:600;font-size:11px; }
.att-badge-present { background:rgba(16,185,129,0.12);color:#10b981; }
.att-badge-absent { background:rgba(239,68,68,0.12);color:#ef4444; }
.att-badge-late { background:rgba(245,158,11,0.12);color:#f59e0b; }
.att-badge-excused { background:rgba(59,130,246,0.12);color:#3b82f6; }

/* Mobile meta shown only on mobile */
.att-mobile-meta { display:none;font-size:9px;color:var(--text-muted);display:block; }

/* Empty cell */
.att-empty-cell { padding:24px;text-align:center;color:var(--text-muted);font-size:12px; }
.att-empty-cell i { font-size:24px;opacity:0.3;display:block;margin-bottom:6px; }

/* Header buttons */
.att-header-btn { font-size:0.7rem;padding:4px 10px; }

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .att-stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 4px !important; }
    .att-stat-value { font-size: 14px; }
    .att-stat-icon { width: 24px; height: 24px; }
    .att-stat-icon i { font-size: 10px; }
    .att-stat-card { padding: 4px 8px !important; }
    .att-filter-form { flex-direction: column; gap: 8px; }
    .att-filter-group { min-width: 100%; }
    .att-filter-input { min-width: 100%; }
    .att-filter-btn { width: 100%; text-align: center; }
    .att-hide-mobile { display: none !important; }
    .att-mobile-meta { display: block !important; }
    .att-th, .att-td { padding: 6px 8px; font-size: 10px; }
    .att-badge { font-size: 9px; padding: 2px 6px; }
    .att-header-btn { font-size: 0.65rem; padding: 3px 8px; }
}
@media (max-width: 480px) {
    .att-stats-grid { grid-template-columns: 1fr 1fr !important; gap: 3px !important; }
    .att-stat-card { padding: 3px 6px !important; }
    .att-stat-value { font-size: 13px; }
    .att-stat-label { font-size: 7px; }
    .att-stat-icon { width: 22px; height: 22px; border-radius: 6px; }
    .att-stat-icon i { font-size: 9px; }
    .att-filter-summary { font-size: 0.75rem; gap: 0.35rem; padding: 0.5rem 0.75rem; }
    .att-filter-chip { font-size: 0.72rem; padding: 2px 7px; }
}
</style>
@endpush
@endsection
