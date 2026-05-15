@extends('layouts.admin')
@section('title', 'Gender Performance Analysis')

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
.pf-card-body{padding:1.25rem 1.5rem}

.pf-gender-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.25rem}
.pf-gender-card{border-radius:14px;padding:1.5rem;text-align:center;border:1px solid #f0f0f0}
.pf-gender-card.male{background:linear-gradient(135deg,#eef2ff,#dbeafe);border-color:#c7d2fe}
.pf-gender-card.female{background:linear-gradient(135deg,#fef2f2,#fce7f3);border-color:#fbcfe8}
.pf-gender-icon{font-size:2.5rem;margin-bottom:.75rem}
.pf-gender-label{font-size:.82rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.5rem}
.pf-gender-count{font-size:.82rem;color:#9ca3af;margin-bottom:1rem}
.pf-gender-stats{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.pf-gender-stat{text-align:center}
.pf-gender-stat-label{font-size:.72rem;color:#9ca3af;font-weight:600;text-transform:uppercase}
.pf-gender-stat-value{font-size:1.3rem;font-weight:800;color:#1a1a2e}

.pf-filter{display:flex;gap:1rem;flex-wrap:wrap;align-items:end;margin-bottom:1.25rem}
.pf-select{border:1.5px solid #e5e7eb;border-radius:10px;padding:.5rem .8rem;font-size:.88rem;color:#1a1a2e;background:#fff;min-width:160px}
.pf-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.15rem;border-radius:10px;font-weight:600;font-size:.88rem;border:none;cursor:pointer;color:#fff;background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3)}

.pf-compare-bar{display:flex;height:30px;border-radius:8px;overflow:hidden;margin-bottom:.5rem}
.pf-compare-seg{display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:#fff;min-width:20px;transition:width .3s}

.pf-table-wrap{overflow-x:auto}
.pf-table{width:100%;border-collapse:collapse;font-size:.82rem}
.pf-table th{background:#f8fafc;color:#374151;font-weight:700;padding:10px 14px;border-bottom:1px solid #e5e7eb;white-space:nowrap;text-align:center;font-size:12px}
.pf-table td{padding:10px 14px;border-bottom:1px solid #e5e7eb;text-align:center;font-size:13px}
.pf-table tbody tr:hover{background:#eef2ff}
.pf-diff{font-weight:700;font-size:.82rem}
.pf-diff.positive{color:#10b981}.pf-diff.negative{color:#ef4444}.pf-diff.neutral{color:#6b7280}

.pf-empty{text-align:center;padding:3rem 1rem;color:#9ca3af}
.pf-empty i{font-size:3rem;margin-bottom:.75rem;display:block;opacity:.4}
@media(max-width:768px){.pf-gender-grid{grid-template-columns:1fr}.pf-filter{flex-direction:column}}
</style>
@endpush

@section('content')
<div class="pf-page">
    <div class="pf-header">
        <nav aria-label="breadcrumb" class="pf-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.performance.index') }}">Performance</a></li><li class="active">Gender Analysis</li></ol></nav>
        <h1 class="pf-title">Gender Performance Analysis</h1>
        <p class="pf-subtitle">Compare academic performance between male and female students</p>
    </div>

    <form method="GET">
        <div class="pf-filter">
            <div><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;">Academic Year</label><select name="academic_year_id" class="pf-select"><option value="">Select</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $selectedYear?->id == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach</select></div>
            <div><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;">Term</label><select name="term_id" class="pf-select"><option value="">Select</option>@foreach($terms as $t)<option value="{{ $t->id }}" {{ $selectedTerm?->id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach</select></div>
            <button type="submit" class="pf-btn"><i class="fas fa-filter"></i> Analyze</button>
        </div>
    </form>

    @if(!empty($genderData))
    {{-- Gender Comparison Cards --}}
    <div class="pf-gender-grid">
        <div class="pf-gender-card male">
            <div class="pf-gender-icon" style="color:#4361ee"><i class="fas fa-mars"></i></div>
            <div class="pf-gender-label">Male Students</div>
            <div class="pf-gender-count">{{ $genderData['male']['count'] }} students</div>
            <div class="pf-gender-stats">
                <div class="pf-gender-stat"><div class="pf-gender-stat-label">Average</div><div class="pf-gender-stat-value" style="color:#4361ee">{{ $genderData['male']['average'] }}</div></div>
                <div class="pf-gender-stat"><div class="pf-gender-stat-label">Pass Rate</div><div class="pf-gender-stat-value" style="color:{{ $genderData['male']['pass_rate'] >= 70 ? '#10b981' : '#d97706' }}">{{ $genderData['male']['pass_rate'] }}%</div></div>
                <div class="pf-gender-stat"><div class="pf-gender-stat-label">Highest</div><div class="pf-gender-stat-value" style="color:#10b981">{{ $genderData['male']['highest'] }}</div></div>
                <div class="pf-gender-stat"><div class="pf-gender-stat-label">Lowest</div><div class="pf-gender-stat-value" style="color:#ef4444">{{ $genderData['male']['lowest'] }}</div></div>
            </div>
        </div>
        <div class="pf-gender-card female">
            <div class="pf-gender-icon" style="color:#ec4899"><i class="fas fa-venus"></i></div>
            <div class="pf-gender-label">Female Students</div>
            <div class="pf-gender-count">{{ $genderData['female']['count'] }} students</div>
            <div class="pf-gender-stats">
                <div class="pf-gender-stat"><div class="pf-gender-stat-label">Average</div><div class="pf-gender-stat-value" style="color:#ec4899">{{ $genderData['female']['average'] }}</div></div>
                <div class="pf-gender-stat"><div class="pf-gender-stat-label">Pass Rate</div><div class="pf-gender-stat-value" style="color:{{ $genderData['female']['pass_rate'] >= 70 ? '#10b981' : '#d97706' }}">{{ $genderData['female']['pass_rate'] }}%</div></div>
                <div class="pf-gender-stat"><div class="pf-gender-stat-label">Highest</div><div class="pf-gender-stat-value" style="color:#10b981">{{ $genderData['female']['highest'] }}</div></div>
                <div class="pf-gender-stat"><div class="pf-gender-stat-label">Lowest</div><div class="pf-gender-stat-value" style="color:#ef4444">{{ $genderData['female']['lowest'] }}</div></div>
            </div>
        </div>
    </div>

    {{-- Grade Distribution Comparison --}}
    <div class="pf-card">
        <div class="pf-card-head"><div class="pf-card-icon purple"><i class="fas fa-chart-pie"></i></div><div><h3 class="pf-card-title">Grade Distribution Comparison</h3></div></div>
        <div class="pf-card-body">
            @php
                $maleTotal = array_sum($genderData['male']['grade_distribution']);
                $femaleTotal = array_sum($genderData['female']['grade_distribution']);
                $gradeColors = ['A+'=>'#059669','A'=>'#10b981','B+'=>'#2563eb','B'=>'#3b82f6','C'=>'#d97706','D'=>'#ea580c','F'=>'#ef4444'];
            @endphp
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem">
                <div>
                    <h5 style="font-size:.85rem;font-weight:700;color:#4361ee;margin:0 0 .75rem"><i class="fas fa-mars"></i> Male Grade Distribution</h5>
                    <div class="pf-compare-bar">
                        @foreach($genderData['male']['grade_distribution'] as $grade => $count)
                            @if($count > 0)
                            <div class="pf-compare-seg" style="width:{{ $maleTotal > 0 ? ($count/$maleTotal*100) : 0 }}%;background:{{ $gradeColors[$grade] ?? '#6b7280' }}">{{ $count }}</div>
                            @endif
                        @endforeach
                    </div>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;font-size:.72rem;color:#6b7280">
                        @foreach($genderData['male']['grade_distribution'] as $grade => $count)
                        <span><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:{{ $gradeColors[$grade] ?? '#6b7280' }}"></span> {{ $grade }}:{{ $count }}</span>
                        @endforeach
                    </div>
                </div>
                <div>
                    <h5 style="font-size:.85rem;font-weight:700;color:#ec4899;margin:0 0 .75rem"><i class="fas fa-venus"></i> Female Grade Distribution</h5>
                    <div class="pf-compare-bar">
                        @foreach($genderData['female']['grade_distribution'] as $grade => $count)
                            @if($count > 0)
                            <div class="pf-compare-seg" style="width:{{ $femaleTotal > 0 ? ($count/$femaleTotal*100) : 0 }}%;background:{{ $gradeColors[$grade] ?? '#6b7280' }}">{{ $count }}</div>
                            @endif
                        @endforeach
                    </div>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;font-size:.72rem;color:#6b7280">
                        @foreach($genderData['female']['grade_distribution'] as $grade => $count)
                        <span><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:{{ $gradeColors[$grade] ?? '#6b7280' }}"></span> {{ $grade }}:{{ $count }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Subject-wise Gender Comparison --}}
    @if(count($subjectGenderData) > 0)
    <div class="pf-card">
        <div class="pf-card-head"><div class="pf-card-icon green"><i class="fas fa-book"></i></div><div><h3 class="pf-card-title">Subject-wise Gender Comparison</h3></div></div>
        <div class="pf-table-wrap">
            <table class="pf-table">
                <thead><tr>
                    <th style="text-align:left">Subject</th><th>Male Avg</th><th>Male Count</th><th>Female Avg</th><th>Female Count</th><th>Difference</th><th>Leader</th>
                </tr></thead>
                <tbody>
                    @foreach($subjectGenderData as $sgd)
                    <tr>
                        <td style="text-align:left;font-weight:600">{{ $sgd['subject']->name }}</td>
                        <td style="font-weight:700;color:#4361ee">{{ $sgd['male_avg'] }}</td>
                        <td>{{ $sgd['male_count'] }}</td>
                        <td style="font-weight:700;color:#ec4899">{{ $sgd['female_avg'] }}</td>
                        <td>{{ $sgd['female_count'] }}</td>
                        <td>
                            <span class="pf-diff {{ $sgd['difference'] > 0 ? 'positive' : ($sgd['difference'] < 0 ? 'negative' : 'neutral') }}">
                                @if($sgd['difference'] > 0)+@endif{{ $sgd['difference'] }}
                            </span>
                        </td>
                        <td>
                            @if($sgd['difference'] > 0)
                                <span style="font-weight:600;color:#ec4899"><i class="fas fa-venus"></i> Female</span>
                            @elseif($sgd['difference'] < 0)
                                <span style="font-weight:600;color:#4361ee"><i class="fas fa-mars"></i> Male</span>
                            @else
                                <span style="color:#6b7280">Equal</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @else
    <div class="pf-empty">
        <i class="fas fa-venus-mars"></i>
        <p>Select an academic year and term to view gender analysis</p>
    </div>
    @endif
</div>
@endsection
