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
            <a href="{{ route('admin.attendance.report') }}" class="btn-modern btn-modern-ghost" style="font-size:0.7rem;padding:4px 10px;"><i class="fas fa-chart-bar"></i> Report</a>
            <a href="{{ route('admin.attendance.create') }}" class="btn-modern btn-modern-primary" style="font-size:0.7rem;padding:4px 12px;">
                <i class="fas fa-plus"></i> Record Attendance
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="att-stats-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:12px;">
        <div class="modern-card att-stat-card" style="padding:12px 14px;border-left:3px solid #6366f1;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Attendance Rate</div>
                    <div style="font-size:22px;font-weight:800;color:#6366f1;">{{ $attendanceRate }}%</div>
                </div>
                <div style="width:36px;height:36px;border-radius:10px;background:rgba(99,102,241,0.1);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-percentage" style="color:#6366f1;font-size:14px;"></i>
                </div>
            </div>
        </div>
        <div class="modern-card att-stat-card" style="padding:12px 14px;border-left:3px solid #10b981;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Present</div>
                    <div style="font-size:22px;font-weight:800;color:#10b981;">{{ $presentCount }}</div>
                </div>
                <div style="width:36px;height:36px;border-radius:10px;background:rgba(16,185,129,0.1);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-check-circle" style="color:#10b981;font-size:14px;"></i>
                </div>
            </div>
        </div>
        <div class="modern-card att-stat-card" style="padding:12px 14px;border-left:3px solid #ef4444;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Absent</div>
                    <div style="font-size:22px;font-weight:800;color:#ef4444;">{{ $absentCount }}</div>
                </div>
                <div style="width:36px;height:36px;border-radius:10px;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-times-circle" style="color:#ef4444;font-size:14px;"></i>
                </div>
            </div>
        </div>
        <div class="modern-card att-stat-card" style="padding:12px 14px;border-left:3px solid #f59e0b;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Late</div>
                    <div style="font-size:22px;font-weight:800;color:#f59e0b;">{{ $lateCount }}</div>
                </div>
                <div style="width:36px;height:36px;border-radius:10px;background:rgba(245,158,11,0.1);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-clock" style="color:#f59e0b;font-size:14px;"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="modern-card" style="margin-bottom:12px;">
        <div class="certgen-toolbar">
            <span class="certgen-toolbar-label"><i class="fas fa-filter" style="margin-right:4px;"></i> Filter</span>
        </div>
        <div style="padding:8px 14px;">
            <form method="GET" action="{{ route('admin.attendance.index') }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
                <div style="display:flex;flex-direction:column;">
                    <label style="font-size:9px;font-weight:600;color:var(--text-muted);margin-bottom:2px;text-transform:uppercase;">Date</label>
                    <input type="date" name="date" value="{{ $date }}" class="att-filter-input" style="border:1.5px solid var(--border);border-radius:8px;padding:5px 10px;font-size:12px;font-family:var(--font);color:var(--text-dark);background:var(--card-bg);">
                </div>
                <div style="display:flex;flex-direction:column;">
                    <label style="font-size:9px;font-weight:600;color:var(--text-muted);margin-bottom:2px;text-transform:uppercase;">Class</label>
                    <select name="class_id" class="att-filter-input" style="border:1.5px solid var(--border);border-radius:8px;padding:5px 10px;font-size:12px;font-family:var(--font);color:var(--text-dark);background:var(--card-bg);min-width:150px;">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.7rem;padding:5px 12px;"><i class="fas fa-search"></i> Apply</button>
                <a href="{{ route('admin.attendance.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.7rem;padding:5px 10px;">Clear</a>
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
                <table class="modern-table" style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead>
                        <tr style="background:var(--bg);border-bottom:2px solid var(--border);">
                            <th style="padding:8px 14px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Class</th>
                            <th style="padding:8px 10px;text-align:center;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Total</th>
                            <th style="padding:8px 10px;text-align:center;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Present</th>
                            <th style="padding:8px 10px;text-align:center;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Absent</th>
                            <th style="padding:8px 10px;text-align:center;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Late</th>
                            <th style="padding:8px 10px;text-align:center;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Excused</th>
                            <th style="padding:8px 10px;text-align:center;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Rate</th>
                            <th style="padding:8px 10px;text-align:center;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classSummary as $class)
                        <tr style="border-bottom:1px solid var(--border);transition:background 0.15s;" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:8px 14px;font-weight:600;color:var(--text-dark);">{{ $class->name }}</td>
                            <td style="padding:8px 10px;text-align:center;font-weight:600;">{{ $class->att_total }}</td>
                            <td style="padding:8px 10px;text-align:center;"><span style="background:rgba(16,185,129,0.12);color:#10b981;padding:2px 8px;border-radius:6px;font-weight:600;font-size:11px;">{{ $class->att_present }}</span></td>
                            <td style="padding:8px 10px;text-align:center;"><span style="background:rgba(239,68,68,0.12);color:#ef4444;padding:2px 8px;border-radius:6px;font-weight:600;font-size:11px;">{{ $class->att_absent }}</span></td>
                            <td style="padding:8px 10px;text-align:center;"><span style="background:rgba(245,158,11,0.12);color:#f59e0b;padding:2px 8px;border-radius:6px;font-weight:600;font-size:11px;">{{ $class->att_late }}</span></td>
                            <td style="padding:8px 10px;text-align:center;"><span style="background:rgba(59,130,246,0.12);color:#3b82f6;padding:2px 8px;border-radius:6px;font-weight:600;font-size:11px;">{{ $class->att_excused }}</span></td>
                            <td style="padding:8px 10px;text-align:center;font-weight:700;color:{{ $class->att_rate !== null ? ($class->att_rate >= 80 ? '#10b981' : ($class->att_rate >= 60 ? '#f59e0b' : '#ef4444')) : 'var(--text-muted)' }};">{{ $class->att_rate !== null ? $class->att_rate . '%' : '-' }}</td>
                            <td style="padding:8px 10px;text-align:center;">
                                <a href="{{ route('admin.attendance.edit', ['date' => $date, 'classId' => $class->id]) }}" class="btn-modern btn-modern-ghost" style="font-size:0.65rem;padding:2px 8px;" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('admin.attendance.create', ['class_id' => $class->id, 'date' => $date]) }}" class="btn-modern btn-modern-ghost" style="font-size:0.65rem;padding:2px 8px;" title="Record"><i class="fas fa-plus"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="padding:24px;text-align:center;color:var(--text-muted);font-size:12px;">
                                <i class="fas fa-clipboard-check" style="font-size:24px;opacity:0.3;display:block;margin-bottom:6px;"></i>
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
                <table class="modern-table" style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead>
                        <tr style="background:var(--bg);border-bottom:2px solid var(--border);">
                            <th style="padding:8px 14px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Student</th>
                            <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Roll #</th>
                            <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Class</th>
                            <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Section</th>
                            <th style="padding:8px 10px;text-align:center;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Status</th>
                            <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentRecords as $record)
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:6px 14px;font-weight:600;color:var(--text-dark);">{{ $record->student?->first_name }} {{ $record->student?->last_name }}</td>
                            <td style="padding:6px 10px;color:var(--text-muted);">{{ $record->student?->roll_number ?? '-' }}</td>
                            <td style="padding:6px 10px;color:var(--text-muted);">{{ $record->classRoom?->name }}</td>
                            <td style="padding:6px 10px;color:var(--text-muted);">{{ $record->section?->name ?? '-' }}</td>
                            <td style="padding:6px 10px;text-align:center;">
                                @php
                                    $statusColors = ['present' => '#10b981', 'absent' => '#ef4444', 'late' => '#f59e0b', 'excused' => '#3b82f6'];
                                    $statusBgs = ['present' => 'rgba(16,185,129,0.12)', 'absent' => 'rgba(239,68,68,0.12)', 'late' => 'rgba(245,158,11,0.12)', 'excused' => 'rgba(59,130,246,0.12)'];
                                @endphp
                                <span style="background:{{ $statusBgs[$record->status] ?? 'var(--bg)' }};color:{{ $statusColors[$record->status] ?? 'var(--text)' }};padding:2px 10px;border-radius:6px;font-weight:600;font-size:11px;text-transform:capitalize;">{{ $record->status }}</span>
                            </td>
                            <td style="padding:6px 10px;color:var(--text-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $record->remarks ?? '-' }}</td>
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
.att-stat-card { transition: transform 0.2s, box-shadow 0.2s; }
.att-stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
@media (max-width: 768px) {
    .att-stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
@media (max-width: 480px) {
    .att-stats-grid { grid-template-columns: 1fr !important; }
}
</style>
@endpush
@endsection
