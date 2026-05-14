@extends('layouts.admin')
@section('title', __('app.budget_comparison') ?? 'Branch Budget Comparison')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.budgets.index') }}">{{ __('app.budgets') }}</a></li><li class="active">{{ __('app.budget_comparison') }}</li></ol></nav>
            <h1 class="modern-page-title">{{ __('app.budget_comparison') ?? 'Branch Budget Comparison' }}</h1>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:16px;">
        <form method="GET" style="padding:16px 20px;display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
            <div><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;">Academic Year</label><select name="academic_year_id" style="border:1px solid var(--border);border-radius:var(--radius-sm);padding:7px 10px;font-size:13px;"><option value="">All</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $selectedAcademicYear?->id == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach</select></div>
            <button type="submit" class="btn-modern btn-modern-primary btn-modern-sm"><i class="fas fa-filter"></i> Filter</button>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;">
        <div class="modern-card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Total Allocated</div><div style="font-size:20px;font-weight:800;color:var(--primary);">{{ number_format($overallSummary['total_allocated'], 2) }}</div></div>
        <div class="modern-card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Total Spent</div><div style="font-size:20px;font-weight:800;color:var(--danger);">{{ number_format($overallSummary['total_spent'], 2) }}</div></div>
        <div class="modern-card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Remaining</div><div style="font-size:20px;font-weight:800;color:var(--success);">{{ number_format($overallSummary['total_remaining'], 2) }}</div></div>
        <div class="modern-card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Utilization</div><div style="font-size:20px;font-weight:800;color:var(--warning);">{{ $overallSummary['overall_utilization'] }}%</div></div>
    </div>

    {{-- Branch Comparison Table --}}
    <div class="modern-card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);"><h3 style="font-size:15px;font-weight:700;margin:0;">Branch Comparison</h3></div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead><tr style="background:var(--bg-light);border-bottom:1px solid var(--border);">
                    <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--text-muted);">Branch</th>
                    <th style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:var(--text-muted);">Allocated</th>
                    <th style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:var(--text-muted);">Spent</th>
                    <th style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:var(--text-muted);">Remaining</th>
                    <th style="padding:10px 14px;text-align:center;font-size:12px;font-weight:600;color:var(--text-muted);">Utilization</th>
                    <th style="padding:10px 14px;text-align:center;font-size:12px;font-weight:600;color:var(--text-muted);">% of Total</th>
                    <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--text-muted);">Progress</th>
                </tr></thead>
                <tbody>
                    @foreach($branchComparison as $bc)
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 14px;font-size:13px;font-weight:600;">{{ $bc['branch']->name }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;">{{ number_format($bc['total_allocated'], 2) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;">{{ number_format($bc['total_spent'], 2) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;color:{{ $bc['remaining'] >= 0 ? 'var(--success)' : 'var(--danger)' }};">{{ number_format($bc['remaining'], 2) }}</td>
                        <td style="padding:10px 14px;text-align:center;font-size:13px;font-weight:700;">{{ $bc['utilization_rate'] }}%</td>
                        <td style="padding:10px 14px;text-align:center;font-size:12px;">{{ $bc['allocation_percentage'] }}%</td>
                        <td style="padding:10px 14px;"><div style="background:#e5e7eb;border-radius:4px;height:8px;overflow:hidden;"><div style="background:{{ $bc['utilization_rate'] > 90 ? 'var(--danger)' : ($bc['utilization_rate'] > 70 ? 'var(--warning)' : 'var(--success)') }};height:100%;width:{{ min($bc['utilization_rate'], 100) }}%;border-radius:4px;"></div></div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
