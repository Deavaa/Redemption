@extends('layouts.admin')
@section('title', __('app.financial_comparison') ?? 'Branch Financial Comparison')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.finance-statements.index') }}">{{ __('app.finance_statements') ?? 'Finance' }}</a></li><li class="active">{{ __('app.financial_comparison') }}</li></ol></nav>
            <h1 class="modern-page-title">{{ __('app.financial_comparison') ?? 'Branch Financial Comparison' }}</h1>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:16px;">
        <form method="GET" style="padding:16px 20px;display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
            <div><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;">Academic Year</label><select name="academic_year_id" style="border:1px solid var(--border);border-radius:var(--radius-sm);padding:7px 10px;font-size:13px;"><option value="">All</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $selectedAcademicYear?->id == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach</select></div>
            <div><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;">From</label><input type="date" name="date_from" value="{{ $dateFrom }}" style="border:1px solid var(--border);border-radius:var(--radius-sm);padding:7px 10px;font-size:13px;"></div>
            <div><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;">To</label><input type="date" name="date_to" value="{{ $dateTo }}" style="border:1px solid var(--border);border-radius:var(--radius-sm);padding:7px 10px;font-size:13px;"></div>
            <button type="submit" class="btn-modern btn-modern-primary btn-modern-sm"><i class="fas fa-filter"></i> Filter</button>
        </form>
    </div>

    {{-- Summary --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;">
        <div class="modern-card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Total Income</div><div style="font-size:20px;font-weight:800;color:var(--success);">{{ number_format($overallSummary['total_income'], 2) }}</div></div>
        <div class="modern-card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Total Expense</div><div style="font-size:20px;font-weight:800;color:var(--danger);">{{ number_format($overallSummary['total_expense'], 2) }}</div></div>
        <div class="modern-card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Net Balance</div><div style="font-size:20px;font-weight:800;color:{{ $overallSummary['total_net_balance'] >= 0 ? 'var(--success)' : 'var(--danger)' }};">{{ number_format($overallSummary['total_net_balance'], 2) }}</div></div>
        <div class="modern-card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);font-weight:600;">Avg Income/Branch</div><div style="font-size:20px;font-weight:800;color:var(--primary);">{{ number_format($overallSummary['avg_income_per_branch'], 2) }}</div></div>
    </div>

    {{-- Branch Table --}}
    <div class="modern-card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);"><h3 style="font-size:15px;font-weight:700;margin:0;">Branch Financial Comparison</h3></div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead><tr style="background:var(--bg-light);border-bottom:1px solid var(--border);">
                    <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--text-muted);">Branch</th>
                    <th style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:var(--text-muted);">Income</th>
                    <th style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:var(--text-muted);">Expense</th>
                    <th style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:var(--text-muted);">Net Balance</th>
                    <th style="padding:10px 14px;text-align:center;font-size:12px;font-weight:600;color:var(--text-muted);">Income %</th>
                    <th style="padding:10px 14px;text-align:center;font-size:12px;font-weight:600;color:var(--text-muted);">Ratio</th>
                </tr></thead>
                <tbody>
                    @foreach($branchComparison as $bc)
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 14px;font-size:13px;font-weight:600;">{{ $bc['branch']->name }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;color:var(--success);">{{ number_format($bc['total_income'], 2) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;color:var(--danger);">{{ number_format($bc['total_expense'], 2) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;font-weight:700;color:{{ $bc['net_balance'] >= 0 ? 'var(--success)' : 'var(--danger)' }};">{{ number_format($bc['net_balance'], 2) }}</td>
                        <td style="padding:10px 14px;text-align:center;font-size:12px;">{{ $bc['income_percentage'] }}%</td>
                        <td style="padding:10px 14px;text-align:center;font-size:12px;">{{ $bc['income_expense_ratio'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
