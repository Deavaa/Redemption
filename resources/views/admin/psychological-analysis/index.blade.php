@extends('layouts.admin')
@section('title', __('app.psychological_analysis') ?? 'Psychological & Performance Analysis')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.performance-analysis.index') }}">{{ __('app.performance_analysis') ?? 'Performance' }}</a></li><li class="active">{{ __('app.psychological_analysis') ?? 'Psychological Analysis' }}</li></ol></nav>
            <h1 class="modern-page-title">{{ __('app.psychological_analysis') ?? 'Psychological & Performance Analysis' }}</h1>
            <p class="modern-page-subtitle">{{ __('app.psych_desc') ?? 'Analyze student psychological profiles, motivation, and progress' }}</p>
        </div>
    </div>

    @isset($totalStudents)
    {{-- Risk Distribution --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;">
        <div class="modern-card" style="padding:16px;text-align:center;border-left:4px solid #ef4444;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">High Risk</div><div style="font-size:22px;font-weight:800;color:#ef4444;">{{ $riskDistribution['high'] ?? 0 }}</div></div>
        <div class="modern-card" style="padding:16px;text-align:center;border-left:4px solid #f59e0b;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Moderate Risk</div><div style="font-size:22px;font-weight:800;color:#f59e0b;">{{ $riskDistribution['moderate'] ?? 0 }}</div></div>
        <div class="modern-card" style="padding:16px;text-align:center;border-left:4px solid #3b82f6;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Low Risk</div><div style="font-size:22px;font-weight:800;color:#3b82f6;">{{ $riskDistribution['low'] ?? 0 }}</div></div>
        <div class="modern-card" style="padding:16px;text-align:center;border-left:4px solid #22c55e;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Stable</div><div style="font-size:22px;font-weight:800;color:#22c55e;">{{ $riskDistribution['stable'] ?? 0 }}</div></div>
    </div>

    {{-- Student Analysis Table --}}
    <div class="modern-card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="font-size:15px;font-weight:700;margin:0;">{{ $class->name ?? '' }} - {{ $totalStudents }} Students</h3>
            <span style="font-size:12px;color:var(--text-muted);">{{ ($academicYear->name ?? '') . ' - ' . ($term->name ?? '') }}</span>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead><tr style="background:var(--bg-light);border-bottom:1px solid var(--border);">
                    <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--text-muted);">Student</th>
                    <th style="padding:10px 14px;text-align:center;font-size:12px;font-weight:600;color:var(--text-muted);">Avg</th>
                    <th style="padding:10px 14px;text-align:center;font-size:12px;font-weight:600;color:var(--text-muted);">Risk</th>
                    <th style="padding:10px 14px;text-align:center;font-size:12px;font-weight:600;color:var(--text-muted);">Motivation</th>
                    <th style="padding:10px 14px;text-align:center;font-size:12px;font-weight:600;color:var(--text-muted);">Trend</th>
                    <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--text-muted);">Strengths</th>
                    <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--text-muted);">Recommendations</th>
                </tr></thead>
                <tbody>
                    @foreach($analysis as $a)
                    @php $p = $a['psych_profile']; @endphp
                    <tr style="border-bottom:1px solid var(--border);background:{{ $p['risk_level'] === 'high' ? '#fef2f2' : ($p['risk_level'] === 'moderate' ? '#fffbeb' : 'transparent') }};">
                        <td style="padding:10px 14px;font-size:13px;font-weight:600;">{{ $a['student']->first_name }} {{ $a['student']->last_name }}</td>
                        <td style="padding:10px 14px;text-align:center;font-size:13px;font-weight:700;">{{ $a['average_mark'] }}</td>
                        <td style="padding:10px 14px;text-align:center;">
                            <span style="padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;background:{{ $p['risk_level'] === 'high' ? '#fee2e2' : ($p['risk_level'] === 'moderate' ? '#fef3c7' : ($p['risk_level'] === 'low' ? '#dbeafe' : '#dcfce7')) }};color:{{ $p['risk_level'] === 'high' ? '#dc2626' : ($p['risk_level'] === 'moderate' ? '#d97706' : ($p['risk_level'] === 'low' ? '#2563eb' : '#16a34a')) }};">{{ ucfirst($p['risk_level']) }}</span>
                        </td>
                        <td style="padding:10px 14px;text-align:center;font-size:11px;">{{ str_replace('_', ' ', ucfirst($p['motivation_level'])) }}</td>
                        <td style="padding:10px 14px;text-align:center;">@if($p['is_declining'])<i class="fas fa-arrow-trend-down" style="color:var(--danger);" title="Declining"></i>@else<i class="fas fa-arrow-trend-up" style="color:var(--success);" title="Stable/Improving"></i>@endif</td>
                        <td style="padding:10px 14px;font-size:11px;">{{ implode(', ', array_slice($p['strengths'], 0, 3)) ?: '-' }}</td>
                        <td style="padding:10px 14px;font-size:11px;">{{ implode('; ', array_slice($p['recommendations'], 0, 2)) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endisset

    {{-- Filter Form (shown when no analysis yet) --}}
    @empty($totalStudents)
    <div class="modern-card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);"><h3 style="font-size:15px;font-weight:700;margin:0;">Generate Analysis</h3></div>
        <form method="POST" action="{{ route('admin.psychological-analysis.generate') }}" style="padding:20px;">
            @csrf
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;">
                <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Academic Year *</label><select name="academic_year_id" required style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;">@foreach($academicYears as $ay)<option value="{{ $ay->id }}">{{ $ay->name }}</option>@endforeach</select></div>
                <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Term *</label><select name="term_id" required style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;">@foreach($terms as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select></div>
                <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Class *</label><select name="class_id" required style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;">@foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
            </div>
            <div style="margin-top:16px;"><button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-brain"></i> Generate Analysis</button></div>
        </form>
    </div>
    @endempty
</div>
@endsection
