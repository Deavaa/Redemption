@extends('layouts.admin')
@section('title', 'Student Performance Analysis')

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
.pf-card-icon.red{background:#fef2f2;color:#ef4444}.pf-card-icon.cyan{background:#ecfeff;color:#06b6d4}
.pf-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.pf-card-body{padding:1.25rem 1.5rem}

.pf-info-card{display:flex;gap:1.5rem;align-items:center;padding:1.5rem;background:linear-gradient(135deg,#1e3a5f,#264b73);border-radius:14px;color:#fff;margin-bottom:1.25rem}
.pf-avatar{width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:800;flex-shrink:0}
.pf-info-details{flex:1}
.pf-info-name{font-size:1.4rem;font-weight:800;margin:0}
.pf-info-meta{display:flex;gap:1rem;flex-wrap:wrap;margin-top:.5rem;font-size:.82rem;opacity:.85}

.pf-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.25rem}
.pf-stat{background:#fff;border-radius:12px;padding:1.25rem;border:1px solid #f0f0f0;box-shadow:0 1px 3px rgba(0,0,0,.04);text-align:center}
.pf-stat-label{font-size:.78rem;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem}
.pf-stat-value{font-size:1.75rem;font-weight:800;margin:0}
.pf-stat-value.blue{color:#4361ee}.pf-stat-value.green{color:#10b981}.pf-stat-value.gold{color:#d97706}
.pf-stat-value.red{color:#ef4444}.pf-stat-value.purple{color:#7c3aed}

.pf-table-wrap{overflow-x:auto}
.pf-table{width:100%;border-collapse:collapse;font-size:.82rem}
.pf-table th{background:#f8fafc;color:#374151;font-weight:700;padding:.55rem .6rem;border:1px solid #e5e7eb;white-space:nowrap;text-align:center}
.pf-table td{padding:.45rem .6rem;border:1px solid #e5e7eb;text-align:center}
.pf-table tbody tr:nth-child(even){background:#f9fafb}.pf-table tbody tr:hover{background:#eef2ff}
.pf-table .subj-name{text-align:left;white-space:nowrap;font-weight:600;color:#1a1a2e}

.pf-suggestion{padding:1rem 1.25rem;border-radius:10px;margin-bottom:.75rem;border-left:4px solid}
.pf-suggestion.critical{background:#fef2f2;border-color:#ef4444}.pf-suggestion.warning{background:#fefce8;border-color:#d97706}
.pf-suggestion.improvement{background:#eef2ff;border-color:#4361ee}.pf-suggestion.good{background:#ecfdf5;border-color:#10b981}
.pf-suggestion.very_good{background:#f5f3ff;border-color:#7c3aed}.pf-suggestion.excellent{background:#ecfdf5;border-color:#059669}
.pf-suggestion.moderate{background:#ecfeff;border-color:#06b6d4}
.pf-suggestion-title{font-weight:700;font-size:.92rem;margin-bottom:.25rem}
.pf-suggestion-msg{font-size:.85rem;color:#4b5563;margin-bottom:.5rem}
.pf-suggestion-actions{list-style:none;padding:0;margin:0}
.pf-suggestion-actions li{font-size:.82rem;color:#374151;padding:.15rem 0;padding-left:1rem;position:relative}
.pf-suggestion-actions li::before{content:'\f00c';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;left:0;color:#10b981;font-size:.7rem}

.pf-strengths-weaknesses{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.pf-sw-card{padding:1rem;border-radius:10px;border:1px solid #f0f0f0}
.pf-sw-card.strengths{background:#ecfdf5;border-color:#a7f3d0}
.pf-sw-card.weaknesses{background:#fef2f2;border-color:#fecaca}
.pf-sw-title{font-weight:700;font-size:.92rem;margin-bottom:.5rem;display:flex;align-items:center;gap:.4rem}
.pf-sw-item{display:flex;justify-content:space-between;align-items:center;padding:.35rem 0;font-size:.85rem;border-bottom:1px solid rgba(0,0,0,.05)}
.pf-sw-item:last-child{border-bottom:none}

.pf-trend-card{display:flex;align-items:center;gap:1.5rem;padding:1rem;border-radius:10px;background:#f8fafc;border:1px solid #f0f0f0;margin-bottom:.5rem}
.pf-trend-term{font-weight:700;color:#1a1a2e;min-width:100px}
.pf-trend-avg{font-size:1.2rem;font-weight:800;min-width:60px}
.pf-trend-bar{flex:1;height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden}
.pf-trend-bar-fill{height:100%;border-radius:4px;transition:width .5s}
.pf-trend-grade{font-weight:700;min-width:35px}

@media(max-width:768px){.pf-stats{grid-template-columns:1fr}.pf-strengths-weaknesses{grid-template-columns:1fr}.pf-info-card{flex-direction:column;text-align:center}}
</style>
@endpush

@section('content')
<div class="pf-page">
    <div class="pf-header">
        <div>
            <nav aria-label="breadcrumb" class="pf-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.performance.index') }}">Performance</a></li><li class="active">Student Analysis</li></ol></nav>
            <h1 class="pf-title">Student Performance Analysis</h1>
            <p class="pf-subtitle">Deep analysis with AI-generated suggestions</p>
        </div>
        <div>
            <a href="{{ route('admin.performance.suggestions', $student->id) }}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:10px;background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;text-decoration:none;font-weight:600;font-size:.88rem;box-shadow:0 2px 8px rgba(67,97,238,.3)"><i class="fas fa-lightbulb"></i> View Full Suggestions</a>
        </div>
    </div>

    {{-- Student Info Card --}}
    <div class="pf-info-card">
        <div class="pf-avatar">{{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}</div>
        <div class="pf-info-details">
            <h2 class="pf-info-name">{{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}</h2>
            <div class="pf-info-meta">
                @if($student->classroom)<span><i class="fas fa-chalkboard me-1"></i>{{ $student->classroom->name }}</span>@endif
                @if($student->section)<span><i class="fas fa-layer-group me-1"></i>{{ $student->section->name }}</span>@endif
                @if($student->branch)<span><i class="fas fa-building me-1"></i>{{ $student->branch->name }}</span>@endif
                @if($student->gender)<span><i class="fas fa-venus-mars me-1"></i>{{ ucfirst($student->gender) }}</span>@endif
                @if($student->roll_number)<span><i class="fas fa-hashtag me-1"></i>{{ $student->roll_number }}</span>@endif
            </div>
        </div>
        <div style="text-align:right">
            <div style="font-size:.78rem;opacity:.7;margin-bottom:.25rem">Overall Average</div>
            <div style="font-size:2.25rem;font-weight:800">{{ $overallAvg }}</div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="pf-stats">
        <div class="pf-stat">
            <div class="pf-stat-label">Overall Average</div>
            <div class="pf-stat-value {{ $overallAvg >= 70 ? 'green' : ($overallAvg >= 50 ? 'gold' : 'red') }}">{{ $overallAvg }}</div>
        </div>
        <div class="pf-stat">
            <div class="pf-stat-label">Terms Analyzed</div>
            <div class="pf-stat-value blue">{{ count($termAnalysis) }}</div>
        </div>
        <div class="pf-stat">
            <div class="pf-stat-label">Grade</div>
            <div class="pf-stat-value {{ $overallAvg >= 70 ? 'purple' : 'gold' }}">{{ $suggestions['overall']['level'] ?? 'N/A' }}</div>
        </div>
    </div>

    {{-- Strengths & Weaknesses --}}
    <div class="pf-strengths-weaknesses">
        <div class="pf-sw-card strengths">
            <div class="pf-sw-title" style="color:#059669"><i class="fas fa-arrow-up"></i> Strengths</div>
            @if(count($strengths) > 0)
                @foreach($strengths as $s)
                <div class="pf-sw-item">
                    <span>{{ $s['subject']->name }}</span>
                    <span style="font-weight:700;color:#059669">{{ $s['average'] }}</span>
                </div>
                @endforeach
            @else
                <div style="font-size:.85rem;color:#9ca3af;padding:.5rem 0">No subjects with average >= 70 yet</div>
            @endif
        </div>
        <div class="pf-sw-card weaknesses">
            <div class="pf-sw-title" style="color:#dc2626"><i class="fas fa-arrow-down"></i> Weaknesses</div>
            @if(count($weaknesses) > 0)
                @foreach($weaknesses as $w)
                <div class="pf-sw-item">
                    <span>{{ $w['subject']->name }}</span>
                    <span style="font-weight:700;color:#dc2626">{{ $w['average'] }}</span>
                </div>
                @endforeach
            @else
                <div style="font-size:.85rem;color:#9ca3af;padding:.5rem 0">No subjects below 50 - great job!</div>
            @endif
        </div>
    </div>

    {{-- Performance Trend (Terms Comparison) --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <div class="pf-card-icon cyan"><i class="fas fa-chart-line"></i></div>
            <div><h3 class="pf-card-title">Performance Trend Across Terms</h3></div>
        </div>
        <div class="pf-card-body">
            @if(count($termAnalysis) > 0)
                @foreach($termAnalysis as $ta)
                <div class="pf-trend-card">
                    <div class="pf-trend-term">{{ $ta['term']->name ?? 'Term' }}</div>
                    <div class="pf-trend-avg" style="color:{{ $ta['average'] >= 70 ? '#10b981' : ($ta['average'] >= 50 ? '#d97706' : '#ef4444') }}">{{ $ta['average'] }}</div>
                    <div class="pf-trend-bar">
                        <div class="pf-trend-bar-fill" style="width:{{ min($ta['average'], 100) }}%;background:{{ $ta['average'] >= 70 ? '#10b981' : ($ta['average'] >= 50 ? '#d97706' : '#ef4444') }}"></div>
                    </div>
                    <div class="pf-trend-grade">{{ $ta['grade'] }}</div>
                </div>
                @endforeach
            @else
                <div style="text-align:center;color:#9ca3af;padding:2rem"><i class="fas fa-chart-line" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.75rem"></i><p>No term data available</p></div>
            @endif
        </div>
    </div>

    {{-- Subject-wise Marks Breakdown (Latest Term) --}}
    @if($latestTerm)
    <div class="pf-card">
        <div class="pf-card-head">
            <div class="pf-card-icon blue"><i class="fas fa-table"></i></div>
            <div><h3 class="pf-card-title">Subject-wise Breakdown - {{ $latestTerm['term']->name ?? 'Latest Term' }}</h3></div>
        </div>
        <div class="pf-table-wrap">
            <table class="pf-table">
                <thead>
                    <tr>
                        <th style="text-align:left">Subject</th>
                        <th>CA Total</th>
                        <th>Exam Total</th>
                        <th>Grand Total</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($latestTerm['subjects'] as $subj)
                    <tr>
                        <td class="subj-name">{{ $subj['subject']->name }}</td>
                        <td>{{ $subj['ca_total'] }}</td>
                        <td>{{ $subj['exam_total'] }}</td>
                        <td style="font-weight:700;color:{{ $subj['grand_total'] >= 70 ? '#10b981' : ($subj['grand_total'] >= 50 ? '#d97706' : '#ef4444') }}">{{ $subj['grand_total'] }}</td>
                        <td style="font-weight:700">{{ $subj['grade'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- All Terms Detailed --}}
    @if(count($termAnalysis) > 1)
    <div class="pf-card">
        <div class="pf-card-head">
            <div class="pf-card-icon green"><i class="fas fa-list-ol"></i></div>
            <div><h3 class="pf-card-title">All Terms Detailed Comparison</h3></div>
        </div>
        <div class="pf-table-wrap">
            <table class="pf-table">
                <thead>
                    <tr>
                        <th style="text-align:left">Subject</th>
                        @foreach($termAnalysis as $ta)<th>{{ $ta['term']->name ?? 'Term' }}</th>@endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $allSubjects = collect();
                        foreach($termAnalysis as $ta) {
                            foreach($ta['subjects'] as $s) {
                                $allSubjects[$s['subject_id']] = $s['subject'];
                            }
                        }
                    @endphp
                    @foreach($allSubjects as $subjId => $subject)
                    <tr>
                        <td class="subj-name">{{ $subject->name }}</td>
                        @foreach($termAnalysis as $ta)
                            @php
                                $found = collect($ta['subjects'])->first(fn($s) => $s['subject_id'] == $subjId);
                            @endphp
                            <td style="font-weight:600;color:{{ $found && $found['grand_total'] >= 70 ? '#10b981' : ($found && $found['grand_total'] >= 50 ? '#d97706' : '#ef4444') }}">{{ $found ? $found['grand_total'] : '-' }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                    <tr style="background:#f8fafc;font-weight:700">
                        <td class="subj-name" style="font-weight:700">Average</td>
                        @foreach($termAnalysis as $ta)<td style="color:#4361ee">{{ $ta['average'] }}</td>@endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- AI-Generated Suggestions --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <div class="pf-card-icon purple"><i class="fas fa-lightbulb"></i></div>
            <div><h3 class="pf-card-title">AI-Generated Suggestions</h3></div>
        </div>
        <div class="pf-card-body">
            {{-- Overall Suggestion --}}
            @if(isset($suggestions['overall']))
            <div class="pf-suggestion {{ $suggestions['overall']['level'] }}">
                <div class="pf-suggestion-title"><i class="{{ $suggestions['overall']['icon'] }}"></i> {{ $suggestions['overall']['title'] }}</div>
                <div class="pf-suggestion-msg">{{ $suggestions['overall']['message'] }}</div>
                <ul class="pf-suggestion-actions">
                    @foreach($suggestions['overall']['actions'] as $action)<li>{{ $action }}</li>@endforeach
                </ul>
            </div>
            @endif

            {{-- Subject Suggestions --}}
            @if(isset($suggestions['subjects']) && count($suggestions['subjects']) > 0)
            <h4 style="font-size:.92rem;font-weight:700;color:#1a1a2e;margin:1.25rem 0 .75rem"><i class="fas fa-book me-1"></i> Subject-Specific Suggestions</h4>
            @foreach($suggestions['subjects'] as $subjSug)
            <div class="pf-suggestion {{ $subjSug['level'] }}">
                <div class="pf-suggestion-title">{{ $subjSug['subject']->name }} <span style="font-weight:400;color:#6b7280;font-size:.82rem">({{ $subjSug['average'] }})</span></div>
                <div class="pf-suggestion-msg">{{ $subjSug['message'] }}</div>
                <ul class="pf-suggestion-actions">
                    @foreach($subjSug['actions'] as $action)<li>{{ $action }}</li>@endforeach
                </ul>
            </div>
            @endforeach
            @endif
        </div>
    </div>

    {{-- Recommended Actions --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <div class="pf-card-icon gold"><i class="fas fa-clipboard-check"></i></div>
            <div><h3 class="pf-card-title">Recommended Actions</h3></div>
        </div>
        <div class="pf-card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem">
                @if($overallAvg < 50)
                <div style="padding:1rem;border-radius:10px;background:#fef2f2;border:1px solid #fecaca">
                    <div style="font-weight:700;color:#dc2626;margin-bottom:.5rem"><i class="fas fa-phone me-1"></i> Schedule Parent Meeting</div>
                    <div style="font-size:.82rem;color:#4b5563">Urgent parent-teacher conference needed to discuss intervention strategies.</div>
                </div>
                <div style="padding:1rem;border-radius:10px;background:#fef2f2;border:1px solid #fecaca">
                    <div style="font-weight:700;color:#dc2626;margin-bottom:.5rem"><i class="fas fa-user-friends me-1"></i> Assign Tutor</div>
                    <div style="font-size:.82rem;color:#4b5563">One-on-one tutoring for weak subjects recommended.</div>
                </div>
                @endif
                @if(count($weaknesses) > 0)
                <div style="padding:1rem;border-radius:10px;background:#fefce8;border:1px solid #fde68a">
                    <div style="font-weight:700;color:#d97706;margin-bottom:.5rem"><i class="fas fa-book me-1"></i> Extra Study Sessions</div>
                    <div style="font-size:.82rem;color:#4b5563">Additional study time needed for: {{ collect($weaknesses)->map(fn($w) => $w['subject']->name)->join(', ') }}</div>
                </div>
                @endif
                @if($overallAvg >= 80)
                <div style="padding:1rem;border-radius:10px;background:#ecfdf5;border:1px solid #a7f3d0">
                    <div style="font-weight:700;color:#059669;margin-bottom:.5rem"><i class="fas fa-star me-1"></i> Recognition Program</div>
                    <div style="font-size:.82rem;color:#4b5563">Consider for academic awards and advanced placement opportunities.</div>
                </div>
                @endif
                <div style="padding:1rem;border-radius:10px;background:#eef2ff;border:1px solid #c7d2fe">
                    <div style="font-weight:700;color:#4361ee;margin-bottom:.5rem"><i class="fas fa-chart-line me-1"></i> Monitor Progress</div>
                    <div style="font-size:.82rem;color:#4b5563">Set up regular progress tracking meetings and weekly check-ins.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
