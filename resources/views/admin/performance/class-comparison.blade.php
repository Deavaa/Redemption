@extends('layouts.admin')
@section('title', 'Class Performance Comparison')

@push('styles')
<style>
.pf-page{animation:pfIn .4s ease-out}@keyframes pfIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.pf-header{margin-bottom:1.5rem}
.pf-title{font-size:1.75rem;font-weight:800;color:#1a1a2e;margin:0;letter-spacing:-.5px}
.pf-subtitle{font-size:.9rem;color:#6c757d;margin:.25rem 0 0}
.pf-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}
.pf-breadcrumb li{color:#adb5bd}.pf-breadcrumb li a{color:#6c757d;text-decoration:none}.pf-breadcrumb li a:hover{color:#4361ee}
.pf-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.pf-breadcrumb li.active{color:#4361ee;font-weight:500}
.pf-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.25rem}
.pf-card-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.pf-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.pf-card-icon.blue{background:#eef2ff;color:#4361ee}.pf-card-icon.green{background:#ecfdf5;color:#10b981}
.pf-card-icon.gold{background:#fefce8;color:#d97706}.pf-card-icon.purple{background:#f5f3ff;color:#7c3aed}
.pf-card-icon.red{background:#fef2f2;color:#ef4444}.pf-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.pf-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.25rem}
.pf-stat{background:#fff;border-radius:12px;padding:1.25rem;border:1px solid #f0f0f0;box-shadow:0 1px 3px rgba(0,0,0,.04);text-align:center}
.pf-stat-label{font-size:.78rem;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem}
.pf-stat-value{font-size:1.75rem;font-weight:800;margin:0;color:#4361ee}
.pf-filter{display:flex;gap:1rem;flex-wrap:wrap;align-items:end;margin-bottom:1.25rem}
.pf-select{border:1.5px solid #e5e7eb;border-radius:10px;padding:.5rem .8rem;font-size:.88rem;color:#1a1a2e;background:#fff;min-width:160px}
.pf-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.15rem;border-radius:10px;font-weight:600;font-size:.88rem;border:none;cursor:pointer;color:#fff;background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3)}
.pf-table-wrap{overflow-x:auto}
.pf-table{width:100%;border-collapse:collapse;font-size:.82rem}
.pf-table th{background:#f8fafc;color:#374151;font-weight:700;padding:10px 14px;border-bottom:1px solid #e5e7eb;white-space:nowrap;text-align:center;font-size:12px}
.pf-table td{padding:10px 14px;border-bottom:1px solid #e5e7eb;text-align:center;font-size:13px}
.pf-table tbody tr:hover{background:#eef2ff}
.pf-grade-chip{display:inline-block;padding:1px 5px;border-radius:3px;margin:1px;font-size:10px;font-weight:600}
@media(max-width:768px){.pf-stats{grid-template-columns:1fr}.pf-filter{flex-direction:column}}
</style>
@endpush

@section('content')
<div class="pf-page">
    <div class="pf-header">
        <nav aria-label="breadcrumb" class="pf-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.performance.index') }}">Performance</a></li><li class="active">Class Comparison</li></ol></nav>
        <h1 class="pf-title">Class Performance Comparison</h1>
        <p class="pf-subtitle">Compare academic performance across all classes</p>
    </div>

    <form method="GET">
        <div class="pf-filter">
            <div><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;">Academic Year</label><select name="academic_year_id" class="pf-select"><option value="">Select</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $selectedYear?->id == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach</select></div>
            <div><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;">Term</label><select name="term_id" class="pf-select"><option value="">Select</option>@foreach($terms as $t)<option value="{{ $t->id }}" {{ $selectedTerm?->id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach</select></div>
            <button type="submit" class="pf-btn"><i class="fas fa-filter"></i> Compare</button>
        </div>
    </form>

    <div class="pf-stats">
        <div class="pf-stat"><div class="pf-stat-label">Total Students</div><div class="pf-stat-value" style="color:var(--primary,#4361ee)">{{ $overallStats['total_students'] }}</div></div>
        <div class="pf-stat"><div class="pf-stat-label">Classes Compared</div><div class="pf-stat-value" style="color:#10b981">{{ $overallStats['total_classes'] }}</div></div>
        <div class="pf-stat"><div class="pf-stat-label">Overall Average</div><div class="pf-stat-value" style="color:#d97706">{{ $overallStats['overall_avg'] }}</div></div>
    </div>

    <div class="pf-card">
        <div class="pf-card-head"><div class="pf-card-icon green"><i class="fas fa-code-compare"></i></div><div><h3 class="pf-card-title">Class Ranking</h3></div></div>
        <div class="pf-table-wrap">
            <table class="pf-table">
                <thead><tr>
                    <th>Rank</th><th style="text-align:left">Class</th><th>Students</th><th>Avg Score</th><th>Highest</th><th>Lowest</th><th>Pass Rate</th><th>Grade Distribution</th>
                </tr></thead>
                <tbody>
                    @foreach($classComparison as $cc)
                    <tr>
                        <td style="font-weight:800;font-size:15px;color:{{ $cc['rank'] <= 3 ? '#d97706' : '#6b7280' }}">#{{ $cc['rank'] }}</td>
                        <td style="text-align:left;font-weight:600">{{ $cc['class']->name }}</td>
                        <td>{{ $cc['student_count'] }}</td>
                        <td style="font-weight:700">{{ $cc['avg_performance'] }}</td>
                        <td style="color:#10b981">{{ $cc['highest_score'] }}</td>
                        <td style="color:#ef4444">{{ $cc['lowest_score'] }}</td>
                        <td style="font-weight:700;color:{{ $cc['pass_rate'] >= 80 ? '#10b981' : '#d97706' }}">{{ $cc['pass_rate'] }}%</td>
                        <td>
                            @foreach($cc['grade_distribution'] as $grade => $count)
                                @if($count > 0)
                                <span class="pf-grade-chip" style="background:{{ $grade === 'F' ? '#fee2e2' : ($grade === 'A' ? '#dcfce7' : '#f3f4f6') }};color:{{ $grade === 'F' ? '#dc2626' : '#333' }}">{{ $grade }}:{{ $count }}</span>
                                @endif
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
