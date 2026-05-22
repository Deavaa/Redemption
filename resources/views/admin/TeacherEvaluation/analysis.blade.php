@extends('layouts.admin')
@section('title', 'Teacher Analysis')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.teacher-evaluations.index') }}">Evaluations</a></li><li class="active">Analysis</li></ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.teacher-evaluations.index') }}" class="btn-modern btn-modern-outline"><i class="fas fa-arrow-left"></i><span>Back</span></a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:1.5rem;">
        <form method="GET" action="{{ route('admin.teacher-evaluations.analysis') }}" style="padding:1.25rem 1.5rem;display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
            <div class="modern-form-group" style="flex:1;min-width:200px;">
                <label class="modern-form-label">Select Teacher</label>
                <select name="teacher_id" class="modern-input modern-select">
                    <option value="">-- Choose Teacher --</option>
                    @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ $teacherId == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modern-form-group" style="flex:1;min-width:200px;">
                <label class="modern-form-label">Or Select Department</label>
                <select name="department_id" class="modern-input modern-select">
                    <option value="">-- Choose Department --</option>
                    @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ $departmentId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-search"></i> Analyze</button>
        </form>
    </div>

    {{-- Teacher Analysis --}}
    @if($selectedTeacher)
    <div class="modern-card" style="margin-bottom:1.5rem;">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">{{ $selectedTeacher->full_name }} — Performance History</h2>
                @if($selectedTeacher->department)
                <span class="modern-badge modern-badge-info">{{ $selectedTeacher->department }}</span>
                @endif
            </div>
        </div>
        <div style="padding:1.25rem 1.5rem;">
            {{-- Stats Summary --}}
            <div class="modern-stats-row" style="margin-bottom:1.5rem;">
                <div class="modern-stat-card"><div class="modern-stat-icon modern-stat-icon-blue"><i class="fas fa-clipboard-list"></i></div><div class="modern-stat-info"><span class="modern-stat-value">{{ $evaluations->count() }}</span><span class="modern-stat-label">Total Evaluations</span></div></div>
                <div class="modern-stat-card"><div class="modern-stat-icon modern-stat-icon-green"><i class="fas fa-chart-line"></i></div><div class="modern-stat-info"><span class="modern-stat-value">{{ $evaluations->avg('overall_score') ? round($evaluations->avg('overall_score'), 1) : 'N/A' }}</span><span class="modern-stat-label">Avg Score</span></div></div>
                <div class="modern-stat-card"><div class="modern-stat-icon modern-stat-icon-gold"><i class="fas fa-trophy"></i></div><div class="modern-stat-info"><span class="modern-stat-value">{{ $evaluations->max('overall_score') ?? 'N/A' }}</span><span class="modern-stat-label">Highest Score</span></div></div>
                <div class="modern-stat-card"><div class="modern-stat-icon modern-stat-icon-red"><i class="fas fa-arrow-down"></i></div><div class="modern-stat-info"><span class="modern-stat-value">{{ $evaluations->min('overall_score') ?? 'N/A' }}</span><span class="modern-stat-label">Lowest Score</span></div></div>
            </div>

            {{-- Trend Table --}}
            @if($evaluations->count() > 0)
            <div class="modern-table-wrapper">
                <table class="modern-table">
                    <thead><tr><th>Date</th><th>Type</th><th class="th-center">Score</th><th class="th-center">Grade</th><th>Evaluator</th></tr></thead>
                    <tbody>
                        @foreach($evaluations as $eval)
                        <tr>
                            <td>{{ $eval->evaluation_date->format('M d, Y') }}</td>
                            <td><span class="modern-badge modern-badge-light">{{ $eval->evaluation_type_label }}</span></td>
                            <td class="td-center"><span class="modern-cell-marks">{{ $eval->overall_score }}</span></td>
                            <td class="td-center"><span class="modern-badge {{ $eval->grade_badge }}">{{ $eval->grade_label }}</span></td>
                            <td>{{ $eval->evaluator->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="modern-empty-state" style="padding:2rem;"><p style="color:#9ca3af;">No evaluations found for this teacher.</p></div>
            @endif
        </div>
    </div>
    @endif

    {{-- Department Analysis --}}
    @if($deptStats)
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">{{ $deptStats['department'] }} — Department Overview</h2>
            </div>
        </div>
        <div style="padding:1.25rem 1.5rem;">
            <div class="modern-stats-row" style="margin-bottom:1.5rem;">
                <div class="modern-stat-card"><div class="modern-stat-icon modern-stat-icon-blue"><i class="fas fa-users"></i></div><div class="modern-stat-info"><span class="modern-stat-value">{{ $deptStats['teachers']->count() }}</span><span class="modern-stat-label">Teachers</span></div></div>
                <div class="modern-stat-card"><div class="modern-stat-icon modern-stat-icon-green"><i class="fas fa-chart-line"></i></div><div class="modern-stat-info"><span class="modern-stat-value">{{ $deptStats['avg_score'] }}</span><span class="modern-stat-label">Avg Score</span></div></div>
                <div class="modern-stat-card"><div class="modern-stat-icon modern-stat-icon-gold"><i class="fas fa-clipboard-list"></i></div><div class="modern-stat-info"><span class="modern-stat-value">{{ $deptStats['total_evaluations'] }}</span><span class="modern-stat-label">Evaluations</span></div></div>
            </div>

            {{-- Teachers in dept --}}
            @if($deptStats['teachers']->count() > 0)
            <div class="modern-table-wrapper">
                <table class="modern-table">
                    <thead><tr><th>Teacher</th><th class="th-center">Evaluations</th><th class="th-center">Avg Score</th><th class="th-center">Latest Grade</th></tr></thead>
                    <tbody>
                        @foreach($deptStats['teachers'] as $t)
                        <tr>
                            <td><div class="modern-cell-title">{{ $t['name'] }}</div></td>
                            <td class="td-center">{{ $t['eval_count'] }}</td>
                            <td class="td-center"><span class="modern-cell-marks">{{ $t['avg_score'] }}</span></td>
                            <td class="td-center">
                                @php $g = $t['latest_grade']; @endphp
                                <span class="modern-badge {{ $g == 'excellent' ? 'modern-badge-success' : ($g == 'good' ? 'modern-badge-info' : ($g == 'satisfactory' ? 'modern-badge-light' : ($g == 'needs_improvement' ? 'modern-badge-warning' : 'modern-badge-danger'))) }}">{{ ucfirst(str_replace('_', ' ', $g)) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
    @endif

    @if(!$selectedTeacher && !$deptStats)
    <div class="modern-card">
        <div class="modern-empty-state" style="padding:4rem 2rem;">
            <div class="modern-empty-icon"><i class="fas fa-chart-line"></i></div>
            <h3>Select a Teacher or Department</h3>
            <p>Choose a teacher or department above to view their performance analysis.</p>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
.modern-page{animation:fadeSlideIn .4s ease-out}@keyframes fadeSlideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}.modern-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem}.modern-page-header-left{flex:1}.modern-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}.modern-breadcrumb li{color:#adb5bd}.modern-breadcrumb li a{color:#6c757d;text-decoration:none}.modern-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.modern-breadcrumb li.active{color:#4361ee;font-weight:500}.modern-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.5rem}.modern-card-header{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid #f0f0f0}.modern-card-header-left{display:flex;align-items:center;gap:.75rem}.modern-card-title{font-size:1.1rem;font-weight:700;color:#1a1a2e;margin:0}.modern-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;border-radius:50px;font-size:.75rem;font-weight:600}.modern-badge-light{background:#f3f4f6;color:#6b7280}.modern-badge-success{background:#ecfdf5;color:#059669}.modern-badge-danger{background:#fef2f2;color:#dc2626}.modern-badge-warning{background:#fffbeb;color:#d97706}.modern-badge-info{background:#eff6ff;color:#2563eb}.modern-stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem}.modern-stat-card{background:#fff;border-radius:14px;padding:1.25rem;display:flex;align-items:center;gap:1rem;border:1px solid #f0f0f0}.modern-stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}.modern-stat-icon-blue{background:#eef2ff;color:#4361ee}.modern-stat-icon-green{background:#ecfdf5;color:#10b981}.modern-stat-icon-gold{background:#fefce8;color:#d97706}.modern-stat-icon-red{background:#fef2f2;color:#dc2626}.modern-stat-info{display:flex;flex-direction:column}.modern-stat-value{font-size:1.5rem;font-weight:800;color:#1a1a2e;line-height:1.2}.modern-stat-label{font-size:.8rem;color:#6b757d;font-weight:500}.modern-form-group{display:flex;flex-direction:column}.modern-form-label{font-weight:600;color:#374151;margin-bottom:.45rem;font-size:.88rem}.modern-input{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.7rem .9rem .7rem 2.5rem;font-size:.9rem;color:#1a1a2e;background:#fff}.modern-select{appearance:none;cursor:pointer;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .75rem center;background-repeat:no-repeat;background-size:1.25rem;padding-right:2.5rem}.modern-table-wrapper{overflow-x:auto}.modern-table{width:100%;border-collapse:collapse;font-size:.9rem}.modern-table thead th{background:#f9fafb;padding:.85rem 1rem;text-align:left;font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:2px solid #e5e7eb}.modern-table tbody tr{border-bottom:1px solid #f3f4f6}.modern-table tbody tr:hover{background:#f8f9ff}.modern-table td{padding:.9rem 1rem;vertical-align:middle}.th-center,.td-center{text-align:center!important}.modern-cell-title{font-weight:600;color:#1a1a2e}.modern-cell-marks{font-weight:700;color:#4361ee;font-size:.95rem}.modern-empty-state{text-align:center}.modern-empty-icon{width:80px;height:80px;border-radius:50%;background:#f3f4f6;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;color:#d1d5db;margin-bottom:1.25rem}.modern-empty-state h3{font-size:1.2rem;font-weight:700;color:#1a1a2e;margin:0 0 .5rem}.modern-empty-state p{color:#9ca3af;margin:0}.btn-modern{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.35rem;border-radius:10px;font-weight:600;font-size:.9rem;text-decoration:none;border:none;cursor:pointer;transition:all .25s}.btn-modern-primary{background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;box-shadow:0 2px 8px rgba(67,97,238,.3)}.btn-modern-primary:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4);color:#fff}.btn-modern-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb}.btn-modern-outline:hover{border-color:#4361ee;color:#4361ee}
</style>
@endpush
@endsection
