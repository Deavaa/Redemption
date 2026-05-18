@extends('parent.layout')

@section('title', 'Fees - ' . $student->full_name)

@section('content')
<div class="page-header">
    <div>
        <h4><i class="fas fa-money-bill-wave me-2" style="color: var(--primary);"></i> Fee Details</h4>
        <div class="page-header-sub">
            {{ $student->full_name }}
            &bull; {{ $student->classroom->name ?? 'No Class' }}
        </div>
    </div>
    <a href="{{ route('parent.dashboard') }}" class="btn-modern btn-modern-outline">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

{{-- Fee Summary Cards --}}
<div class="stat-cards" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div class="stat-info">
            <h3>{{ number_format($totalFees, 2) }}</h3>
            <p>Total Fees</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3>{{ number_format($totalPaid, 2) }}</h3>
            <p>Total Paid</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon {{ $balance > 0 ? 'red' : 'green' }}">
            <i class="fas fa-balance-scale"></i>
        </div>
        <div class="stat-info">
            <h3 style="color: {{ $balance > 0 ? 'var(--danger)' : 'var(--success)' }};">
                {{ number_format(abs($balance), 2) }}
            </h3>
            <p>{{ $balance > 0 ? 'Balance Due' : ($balance < 0 ? 'Overpaid' : 'Fully Paid') }}</p>
        </div>
    </div>
</div>

{{-- Progress Bar --}}
@if($totalFees > 0)
<div class="info-card" style="margin-bottom: 20px;">
    <div class="info-card-body" style="padding: 16px 18px;">
        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
            <span style="font-size:13px; font-weight:600; color:var(--text-dark);">Payment Progress</span>
            <span style="font-size:13px; font-weight:600; color:var(--primary);">{{ round(($totalPaid / $totalFees) * 100, 1) }}%</span>
        </div>
        <div style="background:#f5f5f4; border-radius:6px; height:20px; overflow:hidden;">
            <div style="height:100%; border-radius:6px; background:linear-gradient(90deg, var(--primary), var(--accent)); width:{{ min(($totalPaid / $totalFees) * 100, 100) }}%; transition:width 0.5s ease;"></div>
        </div>
    </div>
</div>
@endif

{{-- Payment History --}}
@if($feePayments->count() > 0)
<div class="info-card">
    <div class="info-card-header">
        <h5><i class="fas fa-history me-2" style="color: var(--primary);"></i> Payment History</h5>
        <span class="modern-badge modern-badge-orange">{{ $feePayments->count() }} Payments</span>
    </div>
    <div class="info-card-body" style="padding:0; overflow-x:auto;">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Fee Type</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Receipt #</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($feePayments as $i => $fp)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $fp->payment_date ? $fp->payment_date->format('M d, Y') : '—' }}</td>
                    <td>{{ $fp->fee->fee_type ?? 'N/A' }}</td>
                    <td style="font-weight:600;">{{ number_format($fp->amount_paid, 2) }}</td>
                    <td>
                        @php
                            $methodLabel = ucfirst($fp->payment_method ?? 'cash');
                            $methodIcons = [
                                'cash' => 'fas fa-money-bill-wave',
                                'bank' => 'fas fa-university',
                                'mobile' => 'fas fa-mobile-alt',
                                'cheque' => 'fas fa-money-check',
                                'online' => 'fas fa-globe',
                            ];
                            $methodIcon = $methodIcons[$fp->payment_method] ?? 'fas fa-money-bill';
                        @endphp
                        <i class="{{ $methodIcon }} me-1" style="color:var(--text-muted);"></i>{{ $methodLabel }}
                    </td>
                    <td>
                        <code style="font-size:12px; background:var(--body-bg); padding:2px 6px; border-radius:3px;">{{ $fp->receipt_number ?? '—' }}</code>
                    </td>
                    <td>
                        @php
                            $statusBadge = 'modern-badge-light';
                            if ($fp->status === 'paid') $statusBadge = 'modern-badge-green';
                            elseif ($fp->status === 'partial') $statusBadge = 'modern-badge-amber';
                            elseif ($fp->status === 'pending') $statusBadge = 'modern-badge-orange';
                            elseif ($fp->status === 'overdue') $statusBadge = 'modern-badge-red';
                        @endphp
                        <span class="modern-badge {{ $statusBadge }}">{{ ucfirst($fp->status ?? 'pending') }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="empty-state">
    <i class="fas fa-receipt"></i>
    <h5>No Payment Records</h5>
    <p>No fee payment records found for this student.</p>
</div>
@endif
@endsection
