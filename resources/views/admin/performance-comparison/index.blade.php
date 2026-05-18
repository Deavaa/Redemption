@extends('layouts.admin')
@section('title', __('app.performance_comparison') ?? 'Branch Performance Comparison')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.performance-analysis.index') }}">{{ __('app.performance_analysis') ?? 'Performance' }}</a></li><li class="active">{{ __('app.performance_comparison') }}</li></ol></nav>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:16px;">
        <form method="GET" style="padding:16px 20px;display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
            <div><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;">Academic Year</label><select name="academic_year_id" style="border:1px solid var(--border);border-radius:var(--radius-sm);padding:7px 10px;font-size:13px;"><option value="">Select</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $selectedYear?->id == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach</select></div>
            <div><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;">Term</label><select name="term_id" style="border:1px solid var(--border);border-radius:var(--radius-sm);padding:7px 10px;font-size:13px;"><option value="">Select</option>@foreach($terms as $t)<option value="{{ $t->id }}" {{ $selectedTerm?->id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach</select></div>
            <button type="submit" class="btn-modern btn-modern-primary btn-modern-sm"><i class="fas fa-filter"></i> Compare</button>
        </form>
    </div>

    {{-- Overall Stats --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
        <div class="modern-card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Total Students</div><div style="font-size:20px;font-weight:800;color:var(--primary);">{{ $overallStats['total_students'] }}</div></div>
        <div class="modern-card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Branches Compared</div><div style="font-size:20px;font-weight:800;color:var(--primary);">{{ $overallStats['total_branches'] }}</div></div>
        <div class="modern-card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Overall Average</div><div style="font-size:20px;font-weight:800;color:var(--success);">{{ $overallStats['overall_avg'] }}</div></div>
    </div>

    {{-- Branch Comparison --}}
    <div class="modern-card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);"><h3 style="font-size:15px;font-weight:700;margin:0;">Branch Ranking</h3></div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead><tr style="background:var(--bg-light);border-bottom:1px solid var(--border);">
                    <th style="padding:10px 14px;text-align:center;font-size:12px;font-weight:600;color:var(--text-muted);">Rank</th>
                    <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--text-muted);">Branch</th>
                    <th style="padding:10px 14px;text-align:center;font-size:12px;font-weight:600;color:var(--text-muted);">Students</th>
                    <th style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:var(--text-muted);">Avg Score</th>
                    <th style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:var(--text-muted);">Highest</th>
                    <th style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:var(--text-muted);">Lowest</th>
                    <th style="padding:10px 14px;text-align:center;font-size:12px;font-weight:600;color:var(--text-muted);">Pass Rate</th>
                    <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--text-muted);">Grade Distribution</th>
                </tr></thead>
                <tbody>
                    @foreach($branchComparison as $bc)
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 14px;text-align:center;font-size:15px;font-weight:800;color:{{ $bc['rank'] <= 3 ? 'var(--warning)' : 'var(--text)' }};">#{{ $bc['rank'] }}</td>
                        <td style="padding:10px 14px;font-size:13px;font-weight:600;">{{ $bc['branch']->name }}</td>
                        <td style="padding:10px 14px;text-align:center;font-size:13px;">{{ $bc['student_count'] }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;font-weight:700;">{{ $bc['avg_performance'] }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;color:var(--success);">{{ $bc['highest_score'] }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;color:var(--danger);">{{ $bc['lowest_score'] }}</td>
                        <td style="padding:10px 14px;text-align:center;font-size:13px;font-weight:700;color:{{ $bc['pass_rate'] >= 80 ? 'var(--success)' : 'var(--warning)' }};">{{ $bc['pass_rate'] }}%</td>
                        <td style="padding:10px 14px;font-size:11px;">
                            @foreach($bc['grade_distribution'] as $grade => $count)
                                @if($count > 0)
                                <span style="display:inline-block;padding:1px 5px;border-radius:3px;margin:1px;font-size:10px;background:{{ $grade === 'F' ? '#fee2e2' : ($grade === 'A+' || $grade === 'A' ? '#dcfce7' : '#f3f4f6') }};color:{{ $grade === 'F' ? '#dc2626' : '#333' }};">{{ $grade }}:{{ $count }}</span>
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
