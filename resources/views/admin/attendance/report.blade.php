@extends('layouts.admin')
@section('title', 'Attendance Report')

@section('content')
<div class="modern-page">
    {{-- Header --}}
    <div class="modern-page-header" style="margin-bottom:0.75rem;">
        <div class="modern-page-header-left" style="display:flex;align-items:center;gap:10px;">
            <nav aria-label="breadcrumb" class="modern-breadcrumb" style="margin:0;">
                <ol style="margin:0;">
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.attendance.index') }}">Attendance</a></li>
                    <li class="active">Report</li>
                </ol>
            </nav>
            <span style="color:var(--border);font-size:0.65rem;">|</span>
            <h1 style="font-size:0.85rem;font-weight:700;color:var(--text-dark);margin:0;">Attendance Report</h1>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.attendance.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.7rem;padding:4px 10px;"><i class="fas fa-arrow-left"></i> Dashboard</a>
        </div>
    </div>

    {{-- Student/Parent Context Banner --}}
    @if(isset($studentName) && $studentName)
    <div style="display:flex;align-items:center;gap:8px;padding:6px 12px;margin-bottom:10px;background:linear-gradient(135deg,#ede9fe,#e0e7ff);border:1px solid #c7d2fe;border-radius:10px;">
        <i class="fas fa-user-graduate" style="color:#6366f1;font-size:14px;"></i>
        <span style="font-size:12px;font-weight:700;color:#4338ca;">Your Attendance Summary — {{ $studentName }}</span>
    </div>
    @endif
    @if(isset($childNames) && count($childNames) > 0)
    <div style="display:flex;align-items:center;gap:8px;padding:6px 12px;margin-bottom:10px;background:linear-gradient(135deg,#fce7f3,#fde2e4);border:1px solid #fbcfe8;border-radius:10px;">
        <i class="fas fa-users" style="color:#ec4899;font-size:14px;"></i>
        <span style="font-size:12px;font-weight:700;color:#9d174d;">Your Children's Attendance — {{ implode(', ', $childNames) }}</span>
    </div>
    @endif

    {{-- Summary Stats --}}
    <div class="att-stats-grid" style="display:grid;grid-template-columns:repeat(5,1fr);gap:6px;margin-bottom:12px;">
        <div class="modern-card" style="padding:4px 8px;border-left:3px solid #6366f1;">
            <div style="font-size:7px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Overall Rate</div>
            <div style="font-size:14px;font-weight:800;color:#6366f1;">{{ $overallRate }}%</div>
        </div>
        <div class="modern-card" style="padding:4px 8px;border-left:3px solid #10b981;">
            <div style="font-size:7px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Present</div>
            <div style="font-size:14px;font-weight:800;color:#10b981;">{{ $totalPresent }}</div>
        </div>
        <div class="modern-card" style="padding:4px 8px;border-left:3px solid #ef4444;">
            <div style="font-size:7px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Absent</div>
            <div style="font-size:14px;font-weight:800;color:#ef4444;">{{ $totalAbsent }}</div>
        </div>
        <div class="modern-card" style="padding:4px 8px;border-left:3px solid #f59e0b;">
            <div style="font-size:7px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Late</div>
            <div style="font-size:14px;font-weight:800;color:#f59e0b;">{{ $totalLate }}</div>
        </div>
        <div class="modern-card" style="padding:4px 8px;border-left:3px solid #3b82f6;">
            <div style="font-size:7px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);margin-bottom:2px;">Excused</div>
            <div style="font-size:14px;font-weight:800;color:#3b82f6;">{{ $totalExcused }}</div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="modern-card" style="margin-bottom:12px;">
        <div class="certgen-toolbar">
            <span class="certgen-toolbar-label"><i class="fas fa-filter" style="margin-right:4px;"></i> Date Range & Filters</span>
        </div>
        <div style="padding:8px 14px;">
            <form method="GET" action="{{ route('admin.attendance.report') }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
                <div style="display:flex;flex-direction:column;">
                    <label style="font-size:9px;font-weight:600;color:var(--text-muted);margin-bottom:2px;text-transform:uppercase;">From</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control form-control-sm" style="border:1.5px solid var(--border);border-radius:8px;padding:5px 10px;font-size:12px;">
                </div>
                <div style="display:flex;flex-direction:column;">
                    <label style="font-size:9px;font-weight:600;color:var(--text-muted);margin-bottom:2px;text-transform:uppercase;">To</label>
                    <input type="date" name="to_date" value="{{ $toDate }}" class="form-control form-control-sm" style="border:1.5px solid var(--border);border-radius:8px;padding:5px 10px;font-size:12px;">
                </div>
                <div style="display:flex;flex-direction:column;">
                    <label style="font-size:9px;font-weight:600;color:var(--text-muted);margin-bottom:2px;text-transform:uppercase;">Class</label>
                    <select name="class_id" class="form-select form-select-sm" style="border:1.5px solid var(--border);border-radius:8px;padding:5px 10px;font-size:12px;min-width:150px;">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;flex-direction:column;">
                    <label style="font-size:9px;font-weight:600;color:var(--text-muted);margin-bottom:2px;text-transform:uppercase;">Section</label>
                    <select name="section_id" class="form-select form-select-sm" style="border:1.5px solid var(--border);border-radius:8px;padding:5px 10px;font-size:12px;min-width:120px;">
                        <option value="">All</option>
                        @foreach($sections as $s)
                        <option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.7rem;padding:5px 12px;"><i class="fas fa-search"></i> Apply</button>
                <a href="{{ route('admin.attendance.report') }}" class="btn-modern btn-modern-ghost" style="font-size:0.7rem;padding:5px 10px;">Clear</a>
            </form>
        </div>
    </div>

    {{-- Records Table --}}
    <div class="modern-card">
        <div class="certgen-toolbar">
            <span class="certgen-toolbar-label"><i class="fas fa-table" style="margin-right:4px;"></i> Attendance Records ({{ $fromDate }} to {{ $toDate }})</span>
        </div>
        <div style="padding:0;">
            <div class="table-responsive">
                <table class="modern-table" style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead>
                        <tr style="background:var(--bg);border-bottom:2px solid var(--border);">
                            <th style="padding:8px 14px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Date</th>
                            <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Student</th>
                            <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Class</th>
                            <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Section</th>
                            <th style="padding:8px 10px;text-align:center;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Status</th>
                            <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr style="border-bottom:1px solid var(--border);transition:background 0.15s;" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:6px 14px;font-weight:600;color:var(--text-dark);">{{ $record->date?->format('M d, Y') }}</td>
                            <td style="padding:6px 10px;font-weight:600;color:var(--text-dark);">{{ $record->student?->full_name ?? '' }}</td>
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
                        @empty
                        <tr>
                            <td colspan="6" style="padding:24px;text-align:center;color:var(--text-muted);font-size:12px;">
                                <i class="fas fa-clipboard-check" style="font-size:24px;opacity:0.3;display:block;margin-bottom:6px;"></i>
                                No attendance records found for the selected period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Pagination --}}
        @if($records->hasPages())
        <div style="padding:10px 14px;border-top:1px solid var(--border);">
            {{ $records->withQueryString()->links() }}
        </div>
        @endif
    </div>
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
