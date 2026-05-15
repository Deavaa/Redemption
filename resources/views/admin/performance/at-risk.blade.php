@extends('layouts.admin')
@section('title', 'At-Risk Students')

@push('styles')
<style>
.pf-page{animation:pfIn .4s ease-out}@keyframes pfIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.pf-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.pf-title{font-size:1.75rem;font-weight:800;color:#1a1a2e;margin:0;letter-spacing:-.5px}
.pf-subtitle{font-size:.9rem;color:#6c757d;margin:.25rem 0 0}
.pf-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}
.pf-breadcrumb li{color:#adb5bd}.pf-breadcrumb li a{color:#6c757d;text-decoration:none;transition:color .2s}.pf-breadcrumb li a:hover{color:#4361ee}
.pf-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.pf-breadcrumb li.active{color:#4361ee;font-weight:500}

.pf-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.25rem}
.pf-card-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.pf-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.pf-card-icon.blue{background:#eef2ff;color:#4361ee}.pf-card-icon.green{background:#ecfdf5;color:#10b981}
.pf-card-icon.gold{background:#fefce8;color:#d97706}.pf-card-icon.purple{background:#f5f3ff;color:#7c3aed}
.pf-card-icon.red{background:#fef2f2;color:#ef4444}
.pf-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.pf-card-body{padding:1.25rem 1.5rem}

.pf-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.25rem}
.pf-stat{background:#fff;border-radius:12px;padding:1.25rem;border:1px solid #f0f0f0;box-shadow:0 1px 3px rgba(0,0,0,.04);text-align:center}
.pf-stat-label{font-size:.78rem;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem}
.pf-stat-value{font-size:1.75rem;font-weight:800;margin:0}
.pf-stat-value.blue{color:#4361ee}.pf-stat-value.green{color:#10b981}.pf-stat-value.red{color:#ef4444}.pf-stat-value.gold{color:#d97706}

.pf-filter{display:flex;gap:1rem;flex-wrap:wrap;align-items:end;margin-bottom:1.25rem}
.pf-filter-group{display:flex;flex-direction:column}
.pf-filter-label{font-weight:600;color:#374151;margin-bottom:.4rem;font-size:.85rem}
.pf-select{border:1.5px solid #e5e7eb;border-radius:10px;padding:.5rem .8rem;font-size:.88rem;color:#1a1a2e;background:#fff;min-width:160px}
.pf-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.15rem;border-radius:10px;font-weight:600;font-size:.88rem;border:none;cursor:pointer;transition:all .25s;color:#fff;background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3)}
.pf-btn:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4)}

.pf-table-wrap{overflow-x:auto}
.pf-table{width:100%;border-collapse:collapse;font-size:.82rem}
.pf-table th{background:#f8fafc;color:#374151;font-weight:700;padding:.55rem .6rem;border:1px solid #e5e7eb;white-space:nowrap;text-align:center}
.pf-table td{padding:.45rem .6rem;border:1px solid #e5e7eb;text-align:center}
.pf-table tbody tr:nth-child(even){background:#f9fafb}.pf-table tbody tr:hover{background:#eef2ff}
.pf-table .stu-name{text-align:left;white-space:nowrap;font-weight:600;color:#1a1a2e}

.pf-risk-badge{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .6rem;border-radius:6px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
.pf-risk-badge.critical{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
.pf-risk-badge.warning{background:#fefce8;color:#d97706;border:1px solid #fde68a}
.pf-risk-badge.improvement{background:#eef2ff;color:#4361ee;border:1px solid #c7d2fe}

.pf-weak-subj{display:flex;align-items:center;gap:.35rem;font-size:.78rem;padding:.15rem 0}
.pf-weak-subj-name{color:#6b7280}.pf-weak-subj-score{font-weight:700;color:#ef4444}

.pf-empty{text-align:center;padding:3rem 1rem;color:#9ca3af}
.pf-empty i{font-size:3rem;margin-bottom:.75rem;display:block;opacity:.4}
.pf-empty p{font-size:1rem;margin:0;font-weight:500}
.pf-empty small{font-size:.82rem;color:#9ca3af}

@media(max-width:768px){.pf-stats{grid-template-columns:1fr}.pf-filter{flex-direction:column}.pf-title{font-size:1.35rem}}
</style>
@endpush

@section('content')
<div class="pf-page">
    <div class="pf-header">
        <div>
            <nav aria-label="breadcrumb" class="pf-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.performance.index') }}">Performance</a></li><li class="active">At-Risk Students</li></ol></nav>
            <h1 class="pf-title">At-Risk Students</h1>
            <p class="pf-subtitle">Students with performance below threshold who need intervention</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.performance.at-risk') }}">
        <div class="pf-filter">
            <div class="pf-filter-group">
                <label class="pf-filter-label">Academic Year</label>
                <select name="academic_year_id" class="pf-select">
                    <option value="">-- All Years --</option>
                    @foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $selectedYear?->id == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach
                </select>
            </div>
            <div class="pf-filter-group">
                <label class="pf-filter-label">Term</label>
                <select name="term_id" class="pf-select">
                    <option value="">-- All Terms --</option>
                    @foreach($terms as $t)<option value="{{ $t->id }}" {{ $selectedTerm?->id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div class="pf-filter-group">
                <label class="pf-filter-label">Threshold</label>
                <select name="threshold" class="pf-select">
                    <option value="30" {{ $threshold == 30 ? 'selected' : '' }}>Below 30 (Critical)</option>
                    <option value="40" {{ $threshold == 40 ? 'selected' : '' }}>Below 40 (Severe)</option>
                    <option value="50" {{ $threshold == 50 ? 'selected' : '' }}>Below 50 (At Risk)</option>
                    <option value="60" {{ $threshold == 60 ? 'selected' : '' }}>Below 60 (Warning)</option>
                </select>
            </div>
            <div class="pf-filter-group">
                <label class="pf-filter-label">&nbsp;</label>
                <button type="submit" class="pf-btn"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </div>
    </form>

    {{-- Risk Summary Stats --}}
    <div class="pf-stats">
        <div class="pf-stat">
            <div class="pf-stat-label">Total At-Risk</div>
            <div class="pf-stat-value red">{{ count($atRiskStudents) }}</div>
        </div>
        <div class="pf-stat">
            <div class="pf-stat-label">Critical (&lt;30)</div>
            <div class="pf-stat-value" style="color:#dc2626">{{ $riskLevels['critical'] }}</div>
        </div>
        <div class="pf-stat">
            <div class="pf-stat-label">Warning (30-40)</div>
            <div class="pf-stat-value gold">{{ $riskLevels['warning'] }}</div>
        </div>
    </div>

    {{-- At-Risk Students Table --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <div class="pf-card-icon red"><i class="fas fa-exclamation-triangle"></i></div>
            <div><h3 class="pf-card-title">Students Below {{ $threshold }} Average</h3></div>
        </div>
        <div class="pf-card-body" style="padding:0">
            @if(count($atRiskStudents) > 0)
            <div class="pf-table-wrap">
                <table class="pf-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="text-align:left">Student</th>
                            <th>Average</th>
                            <th>Risk Level</th>
                            <th style="text-align:left">Weakest Subjects</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($atRiskStudents as $i => $risk)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="stu-name">
                                {{ $risk['student']->first_name ?? '' }} {{ $risk['student']->last_name ?? '' }}
                                @if($risk['student']->classroom)<div style="font-size:.72rem;color:#9ca3af;font-weight:400">{{ $risk['student']->classroom->name }}</div>@endif
                            </td>
                            <td style="font-weight:800;color:{{ $risk['average'] < 30 ? '#dc2626' : ($risk['average'] < 40 ? '#d97706' : '#4361ee') }}">{{ $risk['average'] }}</td>
                            <td><span class="pf-risk-badge {{ $risk['risk_level'] }}"><i class="fas fa-{{ $risk['risk_level'] === 'critical' ? 'exclamation-triangle' : ($risk['risk_level'] === 'warning' ? 'exclamation-circle' : 'arrow-up') }}"></i> {{ ucfirst($risk['risk_level']) }}</span></td>
                            <td style="text-align:left">
                                @foreach($risk['weak_subjects'] as $ws)
                                <div class="pf-weak-subj">
                                    <span class="pf-weak-subj-name">{{ $ws['subject']->name ?? 'N/A' }}</span>
                                    <span class="pf-weak-subj-score">{{ $ws['score'] }}</span>
                                </div>
                                @endforeach
                            </td>
                            <td>
                                <div style="display:flex;gap:.35rem;justify-content:center;flex-wrap:wrap">
                                    <a href="{{ route('admin.performance.student', $risk['student']->id) }}" style="display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .5rem;border-radius:6px;background:#eef2ff;color:#4361ee;font-size:.72rem;font-weight:600;text-decoration:none"><i class="fas fa-chart-line"></i> Analyze</a>
                                    <a href="{{ route('admin.performance.suggestions', $risk['student']->id) }}" style="display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .5rem;border-radius:6px;background:#f5f3ff;color:#7c3aed;font-size:.72rem;font-weight:600;text-decoration:none"><i class="fas fa-lightbulb"></i> Suggestions</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="pf-empty">
                <i class="fas fa-check-circle" style="color:#10b981"></i>
                <p>No students below the {{ $threshold }} threshold</p>
                <small>All students are performing above the risk level</small>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
