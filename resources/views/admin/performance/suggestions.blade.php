@extends('layouts.admin')
@section('title', 'Performance Suggestions')

@push('styles')
<style>
.pf-page{animation:pfIn .4s ease-out}@keyframes pfIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.pf-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
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
.pf-card-icon.red{background:#fef2f2;color:#ef4444}
.pf-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.pf-card-body{padding:1.25rem 1.5rem}

.pf-info-card{display:flex;gap:1.5rem;align-items:center;padding:1.5rem;background:linear-gradient(135deg,#1e3a5f,#264b73);border-radius:14px;color:#fff;margin-bottom:1.25rem}
.pf-avatar{width:60px;height:60px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;flex-shrink:0}
.pf-info-details{flex:1}
.pf-info-name{font-size:1.3rem;font-weight:800;margin:0}
.pf-info-meta{display:flex;gap:1rem;flex-wrap:wrap;margin-top:.5rem;font-size:.82rem;opacity:.85}

.pf-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.25rem}
.pf-stat{background:#fff;border-radius:12px;padding:1.25rem;border:1px solid #f0f0f0;box-shadow:0 1px 3px rgba(0,0,0,.04);text-align:center}
.pf-stat-label{font-size:.78rem;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem}
.pf-stat-value{font-size:1.75rem;font-weight:800;margin:0}
.pf-stat-value.blue{color:#4361ee}.pf-stat-value.green{color:#10b981}.pf-stat-value.gold{color:#d97706}.pf-stat-value.red{color:#ef4444}

.pf-suggestion{padding:1.25rem 1.5rem;border-radius:12px;margin-bottom:1rem;border-left:5px solid}
.pf-suggestion.critical{background:linear-gradient(135deg,#fef2f2,#fee2e2);border-color:#ef4444}
.pf-suggestion.warning{background:linear-gradient(135deg,#fefce8,#fef9c3);border-color:#d97706}
.pf-suggestion.improvement{background:linear-gradient(135deg,#eef2ff,#e0e7ff);border-color:#4361ee}
.pf-suggestion.good{background:linear-gradient(135deg,#ecfdf5,#d1fae5);border-color:#10b981}
.pf-suggestion.very_good{background:linear-gradient(135deg,#f5f3ff,#ede9fe);border-color:#7c3aed}
.pf-suggestion.excellent{background:linear-gradient(135deg,#ecfdf5,#a7f3d0);border-color:#059669}
.pf-suggestion.moderate{background:linear-gradient(135deg,#ecfeff,#cffafe);border-color:#06b6d4}

.pf-suggestion-header{display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem}
.pf-suggestion-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;background:rgba(255,255,255,.7)}
.pf-suggestion-title{font-weight:800;font-size:1rem;color:#1a1a2e;margin:0}
.pf-suggestion-msg{font-size:.9rem;color:#4b5563;margin-bottom:.75rem;line-height:1.5}
.pf-suggestion-actions{list-style:none;padding:0;margin:0}
.pf-suggestion-actions li{font-size:.85rem;color:#374151;padding:.3rem 0;padding-left:1.25rem;position:relative}
.pf-suggestion-actions li::before{content:'\f00c';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;left:0;color:#10b981;font-size:.75rem}

.pf-trend-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .75rem;border-radius:8px;font-size:.82rem;font-weight:700}
.pf-trend-badge.improving{background:#ecfdf5;color:#059669}.pf-trend-badge.declining{background:#fef2f2;color:#dc2626}.pf-trend-badge.stable{background:#eef2ff;color:#4361ee}

.pf-subj-sug{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem}

@media(max-width:768px){.pf-stats{grid-template-columns:1fr}.pf-info-card{flex-direction:column;text-align:center}.pf-subj-sug{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="pf-page">
    <div class="pf-header">
        <div>
            <nav aria-label="breadcrumb" class="pf-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.performance.index') }}">Performance</a></li><li class="active">Suggestions</li></ol></nav>
            <h1 class="pf-title">Performance Suggestions</h1>
            <p class="pf-subtitle">AI-generated recommendations based on academic performance</p>
        </div>
        <div>
            <a href="{{ route('admin.performance.student', $student->id) }}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:10px;background:#f8fafc;color:#374151;text-decoration:none;font-weight:600;font-size:.88rem;border:1px solid #e5e7eb"><i class="fas fa-chart-line"></i> Full Analysis</a>
        </div>
    </div>

    {{-- Student Info Card --}}
    <div class="pf-info-card">
        <div class="pf-avatar">{{ strtoupper(substr($student->full_name ?? 'S', 0, 1)) }}</div>
        <div class="pf-info-details">
            <h2 class="pf-info-name">{{ $student->full_name ?? '' }}</h2>
            <div class="pf-info-meta">
                @if($student->classroom)<span><i class="fas fa-chalkboard me-1"></i>{{ $student->classroom->name }}</span>@endif
                @if($student->gender)<span><i class="fas fa-venus-mars me-1"></i>{{ ucfirst($student->gender) }}</span>@endif
            </div>
        </div>
        <div style="text-align:right">
            <div style="font-size:.78rem;opacity:.7;margin-bottom:.25rem">Overall Average</div>
            <div style="font-size:2rem;font-weight:800">{{ $overallAvg }}</div>
            <div>
                <span class="pf-trend-badge {{ $trend }}">
                    <i class="fas fa-{{ $trend === 'improving' ? 'arrow-up' : ($trend === 'declining' ? 'arrow-down' : 'minus') }}"></i>
                    {{ ucfirst($trend) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="pf-stats">
        <div class="pf-stat">
            <div class="pf-stat-label">Overall Average</div>
            <div class="pf-stat-value {{ $overallAvg >= 70 ? 'green' : ($overallAvg >= 50 ? 'gold' : 'red') }}">{{ $overallAvg }}</div>
        </div>
        <div class="pf-stat">
            <div class="pf-stat-label">Performance Level</div>
            <div class="pf-stat-value blue">{{ ucfirst($suggestions['overall']['level'] ?? 'N/A') }}</div>
        </div>
        <div class="pf-stat">
            <div class="pf-stat-label">Trend</div>
            <div class="pf-stat-value {{ $trend === 'improving' ? 'green' : ($trend === 'declining' ? 'red' : 'gold') }}">{{ ucfirst($trend) }}</div>
        </div>
    </div>

    {{-- Overall Suggestion --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <div class="pf-card-icon {{ $suggestions['overall']['level'] === 'critical' || $suggestions['overall']['level'] === 'warning' ? 'red' : ($suggestions['overall']['level'] === 'excellent' || $suggestions['overall']['level'] === 'very_good' ? 'green' : 'gold') }}">
                <i class="{{ $suggestions['overall']['icon'] ?? 'fas fa-lightbulb' }}"></i>
            </div>
            <div><h3 class="pf-card-title">Overall Performance Recommendation</h3></div>
        </div>
        <div class="pf-card-body">
            @if(isset($suggestions['overall']))
            <div class="pf-suggestion {{ $suggestions['overall']['level'] }}">
                <div class="pf-suggestion-header">
                    <div class="pf-suggestion-icon"><i class="{{ $suggestions['overall']['icon'] }}"></i></div>
                    <h3 class="pf-suggestion-title">{{ $suggestions['overall']['title'] }}</h3>
                </div>
                <div class="pf-suggestion-msg">{{ $suggestions['overall']['message'] }}</div>
                <h4 style="font-size:.85rem;font-weight:700;color:#374151;margin:.75rem 0 .5rem">Recommended Actions:</h4>
                <ul class="pf-suggestion-actions">
                    @foreach($suggestions['overall']['actions'] as $action)<li>{{ $action }}</li>@endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    {{-- Subject-Specific Suggestions --}}
    @if(isset($suggestions['subjects']) && count($suggestions['subjects']) > 0)
    <div class="pf-card">
        <div class="pf-card-head">
            <div class="pf-card-icon purple"><i class="fas fa-book"></i></div>
            <div><h3 class="pf-card-title">Subject-Specific Suggestions</h3></div>
        </div>
        <div class="pf-card-body">
            <div class="pf-subj-sug">
                @foreach($suggestions['subjects'] as $subjSug)
                <div class="pf-suggestion {{ $subjSug['level'] }}">
                    <div class="pf-suggestion-header">
                        <div class="pf-suggestion-icon"><i class="fas fa-book"></i></div>
                        <div>
                            <h4 class="pf-suggestion-title" style="font-size:.92rem">{{ $subjSug['subject']->name }}</h4>
                            <span style="font-size:.78rem;color:#6b7280">Average: {{ $subjSug['average'] }}</span>
                        </div>
                    </div>
                    <div class="pf-suggestion-msg">{{ $subjSug['message'] }}</div>
                    <ul class="pf-suggestion-actions">
                        @foreach($subjSug['actions'] as $action)<li>{{ $action }}</li>@endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Term Trend --}}
    @if(count($termAverages) > 0)
    <div class="pf-card">
        <div class="pf-card-head">
            <div class="pf-card-icon gold"><i class="fas fa-chart-line"></i></div>
            <div><h3 class="pf-card-title">Performance Trend Across Terms</h3></div>
        </div>
        <div class="pf-card-body">
            <div style="display:flex;align-items:end;gap:1.5rem;height:150px;padding:0 1rem">
                @foreach($termAverages as $i => $avg)
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.35rem">
                    <div style="font-size:.82rem;font-weight:700;color:{{ $avg >= 70 ? '#10b981' : ($avg >= 50 ? '#d97706' : '#ef4444') }}">{{ $avg }}</div>
                    <div style="width:100%;background:{{ $avg >= 70 ? '#10b981' : ($avg >= 50 ? '#d97706' : '#ef4444') }};border-radius:6px 6px 0 0;height:{{ max($avg, 5) }}%;transition:height .5s"></div>
                    <div style="font-size:.72rem;color:#9ca3af">Term {{ $i + 1 }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
