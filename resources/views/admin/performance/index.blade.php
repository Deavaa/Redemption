@extends('layouts.admin')
@section('title', 'Performance Dashboard')

@push('styles')
<style>
.pf-page{animation:pfIn .4s ease-out}@keyframes pfIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.pf-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.pf-header-left{flex:1}
.pf-title{font-size:1.75rem;font-weight:800;color:#1a1a2e;margin:0;letter-spacing:-.5px}
.pf-subtitle{font-size:.9rem;color:#6c757d;margin:.25rem 0 0}
.pf-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}
.pf-breadcrumb li{color:#adb5bd}.pf-breadcrumb li a{color:#6c757d;text-decoration:none;transition:color .2s}.pf-breadcrumb li a:hover{color:#4361ee}
.pf-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.pf-breadcrumb li.active{color:#4361ee;font-weight:500}

.pf-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.25rem}
.pf-stat{background:#fff;border-radius:12px;padding:1.25rem 1.5rem;border:1px solid #f0f0f0;box-shadow:0 1px 3px rgba(0,0,0,.04);transition:transform .2s,box-shadow .2s}
.pf-stat:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.08)}
.pf-stat-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:.75rem}
.pf-stat-icon.blue{background:#eef2ff;color:#4361ee}.pf-stat-icon.green{background:#ecfdf5;color:#10b981}
.pf-stat-icon.gold{background:#fefce8;color:#d97706}.pf-stat-icon.red{background:#fef2f2;color:#ef4444}
.pf-stat-icon.purple{background:#f5f3ff;color:#7c3aed}.pf-stat-icon.cyan{background:#ecfeff;color:#06b6d4}
.pf-stat-label{font-size:.78rem;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem}
.pf-stat-value{font-size:1.75rem;font-weight:800;margin:0}
.pf-stat-value.blue{color:#4361ee}.pf-stat-value.green{color:#10b981}.pf-stat-value.gold{color:#d97706}
.pf-stat-value.red{color:#ef4444}.pf-stat-value.purple{color:#7c3aed}.pf-stat-value.cyan{color:#06b6d4}

.pf-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.25rem}
.pf-card-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.pf-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.pf-card-icon.blue{background:#eef2ff;color:#4361ee}.pf-card-icon.green{background:#ecfdf5;color:#10b981}
.pf-card-icon.gold{background:#fefce8;color:#d97706}.pf-card-icon.purple{background:#f5f3ff;color:#7c3aed}
.pf-card-icon.red{background:#fef2f2;color:#ef4444}.pf-card-icon.cyan{background:#ecfeff;color:#06b6d4}
.pf-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.pf-card-body{padding:1.25rem 1.5rem}

.pf-quick-links{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;margin-bottom:1.25rem}
.pf-quick-link{display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;background:#fff;border-radius:12px;border:1px solid #f0f0f0;box-shadow:0 1px 3px rgba(0,0,0,.04);text-decoration:none;color:#1a1a2e;transition:all .25s}
.pf-quick-link:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(0,0,0,.1);border-color:#4361ee}
.pf-quick-link-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0}
.pf-quick-link-text{font-weight:600;font-size:.92rem}

.pf-table-wrap{overflow-x:auto}
.pf-table{width:100%;border-collapse:collapse;font-size:.82rem}
.pf-table th{background:#f8fafc;color:#374151;font-weight:700;padding:.55rem .6rem;border:1px solid #e5e7eb;white-space:nowrap;text-align:center}
.pf-table td{padding:.45rem .6rem;border:1px solid #e5e7eb;text-align:center}
.pf-table tbody tr:nth-child(even){background:#f9fafb}.pf-table tbody tr:hover{background:#eef2ff}
.pf-table .stu-name{text-align:left;white-space:nowrap;font-weight:600;color:#1a1a2e}

.pf-risk-badge{display:inline-flex;align-items:center;gap:.25rem;padding:.15rem .55rem;border-radius:6px;font-size:.72rem;font-weight:700}
.pf-risk-badge.critical{background:#fef2f2;color:#dc2626}.pf-risk-badge.warning{background:#fefce8;color:#d97706}
.pf-risk-badge.improvement{background:#eef2ff;color:#4361ee}

.pf-gender-compare{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.pf-gender-card{padding:1.25rem;border-radius:12px;border:1px solid #f0f0f0;text-align:center}
.pf-gender-card.male{background:linear-gradient(135deg,#eef2ff,#e0e7ff)}.pf-gender-card.female{background:linear-gradient(135deg,#fef2f2,#fce7f3)}
.pf-gender-label{font-size:.78rem;color:#9ca3af;font-weight:600;text-transform:uppercase;margin-bottom:.5rem}
.pf-gender-value{font-size:1.5rem;font-weight:800;color:#1a1a2e}
.pf-gender-pass{font-size:.85rem;margin-top:.5rem;font-weight:600}

.pf-empty{text-align:center;padding:2.5rem 1rem;color:#9ca3af}.pf-empty i{font-size:2.5rem;margin-bottom:.75rem;display:block;opacity:.4}
.pf-empty p{font-size:.9rem;margin:0}

@media(max-width:768px){.pf-stats{grid-template-columns:1fr 1fr}.pf-quick-links{grid-template-columns:1fr}.pf-gender-compare{grid-template-columns:1fr}.pf-title{font-size:1.35rem}}
@media(max-width:480px){.pf-stats{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="pf-page">
    <div class="pf-header">
        <div class="pf-header-left">
            <nav aria-label="breadcrumb" class="pf-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li class="active">Performance Dashboard</li></ol></nav>
            <h1 class="pf-title">Performance Dashboard</h1>
            <p class="pf-subtitle">Overall school performance metrics and quick access to detailed analysis</p>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="pf-quick-links">
        <a href="{{ route('admin.students.index') }}" class="pf-quick-link">
            <div class="pf-quick-link-icon" style="background:#eef2ff;color:#4361ee"><i class="fas fa-user-graduate"></i></div>
            <div><div class="pf-quick-link-text">Student Analysis</div><div style="font-size:.75rem;color:#9ca3af">Deep individual analysis</div></div>
        </a>
        <a href="{{ route('admin.performance.at-risk') }}" class="pf-quick-link">
            <div class="pf-quick-link-icon" style="background:#fef2f2;color:#ef4444"><i class="fas fa-exclamation-triangle"></i></div>
            <div><div class="pf-quick-link-text">At-Risk Students</div><div style="font-size:.75rem;color:#9ca3af">Identify struggling students</div></div>
        </a>
        <a href="{{ route('admin.performance.class-comparison') }}" class="pf-quick-link">
            <div class="pf-quick-link-icon" style="background:#ecfdf5;color:#10b981"><i class="fas fa-code-compare"></i></div>
            <div><div class="pf-quick-link-text">Class Comparison</div><div style="font-size:.75rem;color:#9ca3af">Compare across classes</div></div>
        </a>
        <a href="{{ route('admin.performance.branch-comparison') }}" class="pf-quick-link">
            <div class="pf-quick-link-icon" style="background:#fefce8;color:#d97706"><i class="fas fa-building"></i></div>
            <div><div class="pf-quick-link-text">Branch Comparison</div><div style="font-size:.75rem;color:#9ca3af">Compare across branches</div></div>
        </a>
        <a href="{{ route('admin.performance.gender') }}" class="pf-quick-link">
            <div class="pf-quick-link-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fas fa-venus-mars"></i></div>
            <div><div class="pf-quick-link-text">Gender Analysis</div><div style="font-size:.75rem;color:#9ca3af">Performance by gender</div></div>
        </a>
    </div>

    {{-- Overall Stats --}}
    <div class="pf-stats">
        <div class="pf-stat">
            <div class="pf-stat-icon blue"><i class="fas fa-users"></i></div>
            <div class="pf-stat-label">Total Students</div>
            <div class="pf-stat-value blue">{{ $totalStudents }}</div>
        </div>
        <div class="pf-stat">
            <div class="pf-stat-icon green"><i class="fas fa-chart-line"></i></div>
            <div class="pf-stat-label">School Average</div>
            <div class="pf-stat-value green">{{ $overallAvg }}</div>
        </div>
        <div class="pf-stat">
            <div class="pf-stat-icon gold"><i class="fas fa-check-circle"></i></div>
            <div class="pf-stat-label">Pass Rate</div>
            <div class="pf-stat-value gold">{{ $passRate }}%</div>
        </div>
        <div class="pf-stat">
            <div class="pf-stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="pf-stat-label">At-Risk Students</div>
            <div class="pf-stat-value red">{{ $atRiskCount }}</div>
        </div>
    </div>

    {{-- Additional Stats --}}
    <div class="pf-stats">
        <div class="pf-stat">
            <div class="pf-stat-icon purple"><i class="fas fa-building"></i></div>
            <div class="pf-stat-label">Total Branches</div>
            <div class="pf-stat-value purple">{{ $totalBranches }}</div>
        </div>
        <div class="pf-stat">
            <div class="pf-stat-icon cyan"><i class="fas fa-chalkboard"></i></div>
            <div class="pf-stat-label">Total Classes</div>
            <div class="pf-stat-value cyan">{{ $totalClasses }}</div>
        </div>
    </div>

    {{-- Charts Placeholder --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <div class="pf-card-icon blue"><i class="fas fa-chart-bar"></i></div>
            <div><h3 class="pf-card-title">Performance Charts</h3></div>
        </div>
        <div class="pf-card-body" style="min-height:200px;display:flex;align-items:center;justify-content:center;">
            <div style="text-align:center;color:#9ca3af">
                <i class="fas fa-chart-area" style="font-size:3rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
                <p style="margin:0;font-size:.9rem;font-weight:500">Charts will be displayed here</p>
                <p style="margin:.25rem 0 0;font-size:.78rem">Integrate Chart.js or ApexCharts for visual analytics</p>
            </div>
        </div>
    </div>

    {{-- Gender Performance Comparison --}}
    @if(!empty($genderStats))
    <div class="pf-card">
        <div class="pf-card-head">
            <div class="pf-card-icon purple"><i class="fas fa-venus-mars"></i></div>
            <div><h3 class="pf-card-title">Gender Performance Comparison</h3></div>
        </div>
        <div class="pf-card-body">
            <div class="pf-gender-compare">
                <div class="pf-gender-card male">
                    <div class="pf-gender-label"><i class="fas fa-mars"></i> Male Students</div>
                    <div class="pf-gender-value">{{ $genderStats['male']['average'] ?? 0 }}</div>
                    <div style="font-size:.8rem;color:#6b7280;margin-top:.25rem">{{ $genderStats['male']['count'] ?? 0 }} students</div>
                    <div class="pf-gender-pass" style="color:#10b981">Pass Rate: {{ $genderStats['male']['pass_rate'] ?? 0 }}%</div>
                </div>
                <div class="pf-gender-card female">
                    <div class="pf-gender-label"><i class="fas fa-venus"></i> Female Students</div>
                    <div class="pf-gender-value">{{ $genderStats['female']['average'] ?? 0 }}</div>
                    <div style="font-size:.8rem;color:#6b7280;margin-top:.25rem">{{ $genderStats['female']['count'] ?? 0 }} students</div>
                    <div class="pf-gender-pass" style="color:#10b981">Pass Rate: {{ $genderStats['female']['pass_rate'] ?? 0 }}%</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Top Performers --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <div class="pf-card-icon gold"><i class="fas fa-trophy"></i></div>
            <div><h3 class="pf-card-title">Top Performers</h3></div>
        </div>
        <div class="pf-table-wrap">
            @if($topPerformers->count() > 0)
            <table class="pf-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th style="text-align:left">Student Name</th>
                        <th>Average</th>
                        <th>Grade</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topPerformers as $i => $student)
                    <tr>
                        <td style="font-weight:800;color:{{ $i < 3 ? '#d97706' : '#6b7280' }}">{{ $i + 1 }}</td>
                        <td class="stu-name">{{ $student->full_name ?? '' }}</td>
                        <td style="font-weight:700;color:#4361ee">{{ $student->performance_avg }}</td>
                        <td><strong>{{ $student->performance_grade ?? '' }}</strong></td>
                        <td><a href="{{ route('admin.performance.student', $student->id) }}" style="color:#4361ee;font-size:.78rem;text-decoration:none;font-weight:600"><i class="fas fa-eye"></i> View</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="pf-empty"><i class="fas fa-trophy"></i><p>No performance data available yet</p></div>
            @endif
        </div>
    </div>

    {{-- At-Risk Students Summary --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <div class="pf-card-icon red"><i class="fas fa-exclamation-triangle"></i></div>
            <div><h3 class="pf-card-title">At-Risk Students (Average &lt; 50)</h3></div>
            <div style="margin-left:auto"><a href="{{ route('admin.performance.at-risk') }}" style="font-size:.82rem;color:#4361ee;text-decoration:none;font-weight:600">View All <i class="fas fa-arrow-right"></i></a></div>
        </div>
        <div class="pf-table-wrap">
            @if(count($atRiskStudents) > 0)
            <table class="pf-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Average</th>
                        <th>Risk Level</th>
                        <th>Weakest Subject</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($atRiskStudents as $risk)
                    <tr>
                        <td class="stu-name">{{ $risk['student']->full_name ?? '' }}</td>
                        <td style="font-weight:700;color:#ef4444">{{ $risk['average'] }}</td>
                        <td><span class="pf-risk-badge {{ $risk['risk_level'] }}">{{ ucfirst($risk['risk_level']) }}</span></td>
                        <td style="font-size:.8rem">{{ ($risk['weak_subjects'][0]['subject']->name ?? 'N/A') . ' (' . ($risk['weak_subjects'][0]['score'] ?? 0) . ')' }}</td>
                        <td><a href="{{ route('admin.performance.suggestions', $risk['student']->id) }}" style="color:#4361ee;font-size:.78rem;text-decoration:none;font-weight:600"><i class="fas fa-lightbulb"></i> Suggestions</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="pf-empty"><i class="fas fa-check-circle" style="color:#10b981"></i><p>No at-risk students identified. Great job!</p></div>
            @endif
        </div>
    </div>
</div>
@endsection
