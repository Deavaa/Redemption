@extends('layouts.admin')
@section('title', 'Performance Analysis')

@push('styles')
<style>
.pa-page{animation:paIn .4s ease-out}@keyframes paIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.pa-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.pa-header-left{flex:1}
.pa-title{font-size:1.75rem;font-weight:800;color:#1a1a2e;margin:0;letter-spacing:-.5px}
.pa-subtitle{font-size:.9rem;color:#6c757d;margin:.25rem 0 0}
.pa-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}
.pa-breadcrumb li{color:#adb5bd}.pa-breadcrumb li a{color:#6c757d;text-decoration:none;transition:color .2s}.pa-breadcrumb li a:hover{color:#4361ee}
.pa-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.pa-breadcrumb li.active{color:#4361ee;font-weight:500}
.pa-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.25rem}
.pa-card-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.pa-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.pa-card-icon.blue{background:#eef2ff;color:#4361ee}.pa-card-icon.green{background:#ecfdf5;color:#10b981}.pa-card-icon.gold{background:#fefce8;color:#d97706}.pa-card-icon.purple{background:#f5f3ff;color:#7c3aed}.pa-card-icon.red{background:#fef2f2;color:#ef4444}
.pa-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}.pa-card-desc{font-size:.82rem;color:#9ca3af;margin:.1rem 0 0}
.pa-card-body{padding:1.25rem 1.5rem}
.pa-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
.pa-group{display:flex;flex-direction:column}
.pa-label{font-weight:600;color:#374151;margin-bottom:.4rem;font-size:.85rem}
.pa-select{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.6rem 2.2rem .6rem .8rem;font-size:.88rem;color:#1a1a2e;background:#fff;appearance:none;cursor:pointer;transition:all .2s;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .6rem center;background-repeat:no-repeat;background-size:1.15rem}
.pa-select:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.pa-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:10px;font-weight:600;font-size:.88rem;border:none;cursor:pointer;transition:all .25s;color:#fff;background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3)}
.pa-btn:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4)}
.pa-actions{display:flex;justify-content:flex-end;gap:.75rem;padding:1rem 1.5rem;border-top:1px solid #f0f0f0;background:#fafbfc}

/* Stats Cards */
.pa-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.25rem}
.pa-stat{background:#fff;border-radius:12px;padding:1.25rem;border:1px solid #f0f0f0;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.pa-stat-label{font-size:.78rem;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem}
.pa-stat-value{font-size:1.75rem;font-weight:800;margin:0}
.pa-stat-value.blue{color:#4361ee}.pa-stat-value.green{color:#10b981}.pa-stat-value.gold{color:#d97706}.pa-stat-value.red{color:#ef4444}

/* Report Header */
.pa-report-head{background:linear-gradient(135deg,#1e3a5f,#264b73);color:#fff;padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem}
.pa-report-title{font-size:1.2rem;font-weight:800;margin:0}.pa-report-meta{display:flex;gap:.75rem;flex-wrap:wrap}
.pa-report-chip{font-size:.78rem;background:rgba(255,255,255,.13);padding:.15rem .6rem;border-radius:6px}

/* Ranking Table */
.pa-table-wrap{overflow-x:auto}
.pa-table{width:100%;border-collapse:collapse;font-size:.82rem}
.pa-table th{background:#f8fafc;color:#374151;font-weight:700;padding:.55rem .6rem;border:1px solid #e5e7eb;white-space:nowrap;text-align:center}
.pa-table td{padding:.45rem .6rem;border:1px solid #e5e7eb;text-align:center}
.pa-table tbody tr:nth-child(even){background:#f9fafb}.pa-table tbody tr:hover{background:#eef2ff}
.pa-table .stu-name{text-align:left;white-space:nowrap;font-weight:600;color:#1a1a2e;min-width:140px}
.pa-table .rank-1{color:#d97706;font-weight:800}.pa-table .rank-2{color:#6b7280;font-weight:700}.pa-table .rank-3{color:#b45309;font-weight:700}

/* Grade bar */
.pa-grade-bar{display:flex;height:28px;border-radius:6px;overflow:hidden;margin-bottom:.5rem}
.pa-grade-seg{display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#fff;min-width:24px;transition:width .3s}
.pa-grade-legend{display:flex;gap:.75rem;flex-wrap:wrap;font-size:.72rem;color:#6b7280}
.pa-grade-legend span{display:flex;align-items:center;gap:.25rem}
.pa-grade-legend .dot{width:10px;height:10px;border-radius:3px;display:inline-block}

/* Subject averages */
.pa-subj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem}
.pa-subj-card{border:1px solid #f0f0f0;border-radius:10px;padding:.75rem 1rem;display:flex;justify-content:space-between;align-items:center}
.pa-subj-name{font-weight:600;color:#1a1a2e;font-size:.85rem}
.pa-subj-avg{font-size:1.1rem;font-weight:800;color:#4361ee}

@media print{.pa-header,.pa-card form,.pa-actions,.pa-report-head{display:none!important}.pa-stats{grid-template-columns:repeat(4,1fr)}.pa-table{font-size:10pt}}
@media(max-width:768px){.pa-grid{grid-template-columns:1fr 1fr}.pa-stats{grid-template-columns:1fr 1fr}.pa-title{font-size:1.35rem}}
@media(max-width:480px){.pa-grid{grid-template-columns:1fr}.pa-stats{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="pa-page">
    <div class="pa-header">
        <div class="pa-header-left">
            <nav aria-label="breadcrumb" class="pa-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li class="active">Performance Analysis</li></ol></nav>
            <h1 class="pa-title">Performance Analysis</h1>
            <p class="pa-subtitle">Auto-generated student performance from mark entry data</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="pa-card">
        <div class="pa-card-head">
            <div class="pa-card-icon blue"><i class="fas fa-chart-line"></i></div>
            <div><h3 class="pa-card-title">Select Filters</h3><p class="pa-card-desc">Choose academic year, term, and class to analyze</p></div>
        </div>
        <form method="POST" action="{{ route('admin.performance-analysis.generate') }}">
            @csrf
            <div class="pa-card-body">
                <div class="pa-grid">
                    <div class="pa-group">
                        <label class="pa-label">Academic Year <span style="color:#ef4444">*</span></label>
                        <select name="academic_year_id" class="pa-select" required>
                            <option value="">-- Select Year --</option>
                            @foreach($academicYears as $ay)<option value="{{ $ay->id }}">{{ $ay->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="pa-group">
                        <label class="pa-label">Term <span style="color:#ef4444">*</span></label>
                        <select name="term_id" class="pa-select" required>
                            <option value="">-- Select Term --</option>
                            @foreach($terms as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="pa-group">
                        <label class="pa-label">Class <span style="color:#ef4444">*</span></label>
                        <select name="class_id" id="filterClass" class="pa-select" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="pa-group">
                        <label class="pa-label">Section</label>
                        <select name="section_id" id="filterSection" class="pa-select">
                            <option value="">-- All Sections --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="pa-actions">
                <button type="submit" class="pa-btn"><i class="fas fa-chart-bar"></i> Analyze</button>
            </div>
        </form>
    </div>

    @isset($classStats)
    {{-- Stats Cards --}}
    <div class="pa-stats">
        <div class="pa-stat">
            <div class="pa-stat-label">Total Students</div>
            <div class="pa-stat-value blue">{{ $classStats['total_students'] }}</div>
        </div>
        <div class="pa-stat">
            <div class="pa-stat-label">Class Average</div>
            <div class="pa-stat-value green">{{ $classStats['class_average'] }}</div>
        </div>
        <div class="pa-stat">
            <div class="pa-stat-label">Highest Average</div>
            <div class="pa-stat-value gold">{{ $classStats['highest_average'] }}</div>
        </div>
        <div class="pa-stat">
            <div class="pa-stat-label">Lowest Average</div>
            <div class="pa-stat-value red">{{ $classStats['lowest_average'] }}</div>
        </div>
    </div>

    {{-- Grade Distribution --}}
    <div class="pa-card">
        <div class="pa-card-head">
            <div class="pa-card-icon purple"><i class="fas fa-chart-pie"></i></div>
            <div><h3 class="pa-card-title">Grade Distribution</h3></div>
        </div>
        <div class="pa-card-body">
            @php
                $total = array_sum($gradeDistribution);
                $colors = ['A'=>'#059669','B'=>'#3b82f6','C'=>'#f59e0b','D'=>'#ea580c','F'=>'#ef4444','I'=>'#9ca3af'];
            @endphp
            <div class="pa-grade-bar">
                @foreach($gradeDistribution as $grade => $count)
                    @if($count > 0)
                    <div class="pa-grade-seg" style="width:{{ $total > 0 ? ($count/$total*100) : 0 }}%;background:{{ $colors[$grade] ?? '#6b7280' }}">{{ $count }}</div>
                    @endif
                @endforeach
            </div>
            <div class="pa-grade-legend">
                @foreach($gradeDistribution as $grade => $count)
                <span><span class="dot" style="background:{{ $colors[$grade] ?? '#6b7280' }}"></span>{{ $grade }}: {{ $count }}</span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Student Rankings --}}
    <div class="pa-card">
        <div class="pa-report-head">
            <h2 class="pa-report-title">Student Rankings</h2>
            <div class="pa-report-meta">
                <span class="pa-report-chip">{{ $academicYear->name ?? '' }}</span>
                <span class="pa-report-chip">{{ $term->name ?? '' }}</span>
                <span class="pa-report-chip">{{ $class->name ?? '' }}</span>
            </div>
        </div>
        <div class="pa-table-wrap">
            <table class="pa-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th style="text-align:left">Student Name</th>
                        <th>Subjects</th>
                        <th>Total</th>
                        <th>Average</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analysis as $row)
                    <tr>
                        <td class="{{ $row['rank'] <= 3 ? 'rank-'.$row['rank'] : '' }}">{{ $row['rank'] }}</td>
                        <td class="stu-name">{{ $row['student']->full_name ?? '' }}</td>
                        <td>{{ $row['subject_count'] }}</td>
                        <td>{{ $row['total_marks'] }}</td>
                        <td>{{ $row['average'] }}</td>
                        <td><strong>{{ $row['grade'] ?? '-' }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Subject Averages --}}
    <div class="pa-card">
        <div class="pa-card-head">
            <div class="pa-card-icon green"><i class="fas fa-book"></i></div>
            <div><h3 class="pa-card-title">Subject Performance</h3></div>
        </div>
        <div class="pa-card-body">
            <div class="pa-subj-grid">
                @foreach($subjectAverages as $sa)
                <div class="pa-subj-card">
                    <div>
                        <div class="pa-subj-name">{{ $sa['subject']->name }}</div>
                        <div style="font-size:.72rem;color:#9ca3af">H: {{ $sa['highest'] }} / L: {{ $sa['lowest'] }}</div>
                    </div>
                    <div class="pa-subj-avg">{{ $sa['average'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div style="text-align:right;margin-bottom:1rem">
        <button onclick="window.print()" class="pa-btn" style="background:transparent;color:#6b7280;border:1.5px solid #e5e7eb;box-shadow:none"><i class="fas fa-print"></i> Print Report</button>
    </div>
    @endisset
</div>
@endsection

@push('scripts')
<script>
(function(){
    var cls=document.getElementById('filterClass');
    var sec=document.getElementById('filterSection');
    cls.addEventListener('change',function(){
        if(!this.value){sec.innerHTML='<option value="">-- All Sections --</option>';return;}
        fetch('{{ route("admin.performance-analysis.sections") }}?class_id='+this.value,{credentials:'same-origin'}).then(function(r){return r.json();}).then(function(data){
            sec.innerHTML='<option value="">-- All Sections --</option>';
            data.forEach(function(s){sec.innerHTML+='<option value="'+s.id+'">'+s.name+'</option>';});
        });
    });
})();
</script>
@endpush
