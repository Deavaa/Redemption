@extends('layouts.admin')
@section('title', 'Attendance - ' . $date)

@section('content')
<div class="modern-page">
    {{-- Header --}}
    <div class="modern-page-header" style="margin-bottom:0.75rem;">
        <div class="modern-page-header-left" style="display:flex;align-items:center;gap:10px;">
            <nav aria-label="breadcrumb" class="modern-breadcrumb" style="margin:0;">
                <ol style="margin:0;">
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.attendance.index') }}">Attendance</a></li>
                    <li class="active">{{ $date }}</li>
                </ol>
            </nav>
            <span style="color:var(--border);font-size:0.65rem;">|</span>
            <h1 style="font-size:0.85rem;font-weight:700;color:var(--text-dark);margin:0;">Attendance Detail — {{ $date }}</h1>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.attendance.report') }}" class="btn-modern btn-modern-ghost" style="font-size:0.7rem;padding:4px 10px;"><i class="fas fa-chart-bar"></i> Report</a>
            <a href="{{ route('admin.attendance.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.7rem;padding:4px 10px;"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="att-stats-grid" style="display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-bottom:12px;">
        <div class="modern-card" style="padding:12px 14px;border-left:3px solid #6366f1;">
            <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Attendance Rate</div>
            <div style="font-size:20px;font-weight:800;color:#6366f1;">{{ $attendanceRate }}%</div>
        </div>
        <div class="modern-card" style="padding:12px 14px;border-left:3px solid #10b981;">
            <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Present</div>
            <div style="font-size:20px;font-weight:800;color:#10b981;">{{ $presentCount }}</div>
        </div>
        <div class="modern-card" style="padding:12px 14px;border-left:3px solid #ef4444;">
            <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Absent</div>
            <div style="font-size:20px;font-weight:800;color:#ef4444;">{{ $absentCount }}</div>
        </div>
        <div class="modern-card" style="padding:12px 14px;border-left:3px solid #f59e0b;">
            <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Late</div>
            <div style="font-size:20px;font-weight:800;color:#f59e0b;">{{ $lateCount }}</div>
        </div>
        <div class="modern-card" style="padding:12px 14px;border-left:3px solid #3b82f6;">
            <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Excused</div>
            <div style="font-size:20px;font-weight:800;color:#3b82f6;">{{ $excusedCount }}</div>
        </div>
    </div>

    {{-- By Class --}}
    @foreach($byClass as $classId => $classRecords)
    @php $class = $classes->get($classId) @endphp
    <div class="modern-card" style="margin-bottom:12px;">
        <div class="certgen-toolbar">
            <span class="certgen-toolbar-label">
                <i class="fas fa-chalkboard" style="margin-right:4px;"></i>
                {{ $class?->name ?? 'Class #' . $classId }}
                — {{ $classRecords->count() }} records
            </span>
            <div style="margin-left:auto;display:flex;gap:4px;">
                <a href="{{ route('admin.attendance.edit', ['date' => $date, 'classId' => $classId]) }}" class="btn-modern btn-modern-ghost" style="font-size:0.65rem;padding:2px 8px;">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>
        <div style="padding:0;">
            <div class="table-responsive">
                <table class="modern-table" style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead>
                        <tr style="background:var(--bg);border-bottom:2px solid var(--border);">
                            <th style="padding:8px 14px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Student</th>
                            <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Roll #</th>
                            <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Section</th>
                            <th style="padding:8px 10px;text-align:center;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Status</th>
                            <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Remarks</th>
                            <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classRecords as $record)
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:6px 14px;font-weight:600;color:var(--text-dark);">{{ $record->student?->first_name }} {{ $record->student?->last_name }}</td>
                            <td style="padding:6px 10px;color:var(--text-muted);">{{ $record->student?->roll_number ?? '-' }}</td>
                            <td style="padding:6px 10px;color:var(--text-muted);">{{ $record->section?->name ?? '-' }}</td>
                            <td style="padding:6px 10px;text-align:center;">
                                @php
                                    $statusColors = ['present' => '#10b981', 'absent' => '#ef4444', 'late' => '#f59e0b', 'excused' => '#3b82f6'];
                                    $statusBgs = ['present' => 'rgba(16,185,129,0.12)', 'absent' => 'rgba(239,68,68,0.12)', 'late' => 'rgba(245,158,11,0.12)', 'excused' => 'rgba(59,130,246,0.12)'];
                                @endphp
                                <span style="background:{{ $statusBgs[$record->status] ?? 'var(--bg)' }};color:{{ $statusColors[$record->status] ?? 'var(--text)' }};padding:2px 10px;border-radius:6px;font-weight:600;font-size:11px;text-transform:capitalize;">{{ $record->status }}</span>
                            </td>
                            <td style="padding:6px 10px;color:var(--text-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $record->remarks ?? '-' }}</td>
                            <td style="padding:6px 10px;color:var(--text-muted);font-size:11px;">{{ $record->recorder?->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach

    @if($records->isEmpty())
    <div class="modern-card" style="text-align:center;padding:3rem;">
        <i class="fas fa-clipboard-check" style="font-size:2rem;color:var(--text-muted);opacity:0.4;display:block;margin-bottom:8px;"></i>
        <p style="color:var(--text-muted);font-size:13px;">No attendance records for {{ $date }}.</p>
        <a href="{{ route('admin.attendance.create', ['date' => $date]) }}" class="btn-modern btn-modern-primary" style="font-size:0.7rem;padding:5px 14px;margin-top:8px;">
            <i class="fas fa-plus"></i> Record Attendance
        </a>
    </div>
    @endif
</div>

@push('styles')
<style>
@media (max-width: 768px) {
    .att-stats-grid { grid-template-columns: repeat(3, 1fr) !important; }
}
@media (max-width: 480px) {
    .att-stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
</style>
@endpush
@endsection
