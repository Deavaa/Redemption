@extends('layouts.portal')

@section('home_route', route('portal.dashboard'))

@section('title', 'Fee Progress')

@section('topbar_title', 'Fee Progress')

@section('sidebar_menu')
    <a href="{{ route('portal.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="{{ route('portal.marks') }}"><i class="fas fa-chart-bar"></i> My Marks</a>
    <a href="{{ route('portal.fees') }}" class="active"><i class="fas fa-wallet"></i> Fee Progress</a>
    <a href="{{ route('portal.profile') }}"><i class="fas fa-user"></i> My Profile</a>
@endsection

@section('content')
{{-- Summary Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="stat-card">
            <div style="font-size:1.3rem; margin-bottom:0.35rem;"><i class="fas fa-file-invoice-dollar" style="color:#4361ee;"></i></div>
            <div class="stat-value">{{ number_format($totalFees, 2) }}</div>
            <div class="stat-label">Total Fees</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat-card">
            <div style="font-size:1.3rem; margin-bottom:0.35rem;"><i class="fas fa-check-circle" style="color:#10b981;"></i></div>
            <div class="stat-value" style="color:#10b981;">{{ number_format($totalPaid, 2) }}</div>
            <div class="stat-label">Total Paid</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat-card">
            <div style="font-size:1.3rem; margin-bottom:0.35rem;"><i class="fas fa-exclamation-circle" style="color:{{ $balance > 0 ? '#f59e0b' : '#10b981' }};"></i></div>
            <div class="stat-value" style="color:{{ $balance > 0 ? '#f59e0b' : '#10b981' }};">{{ number_format($balance, 2) }}</div>
            <div class="stat-label">Balance</div>
        </div>
    </div>
</div>

{{-- Payment Progress Bar --}}
<div class="portal-card mb-4">
    <div class="portal-card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold" style="font-size:0.88rem;">Payment Progress</span>
            <span class="fw-bold" style="font-size:0.88rem; color:#4361ee;">
                {{ $totalFees > 0 ? round(($totalPaid / $totalFees) * 100, 1) : 0 }}%
            </span>
        </div>
        <div class="progress" style="height: 10px; border-radius: 8px; background: #e5e7eb;">
            <div class="progress-bar" role="progressbar"
                 style="width: {{ $totalFees > 0 ? min(($totalPaid / $totalFees) * 100, 100) : 0 }}%; background: linear-gradient(135deg, #4361ee, #10b981); border-radius: 8px;"
                 aria-valuenow="{{ $totalPaid }}" aria-valuemin="0" aria-valuemax="{{ $totalFees }}">
            </div>
        </div>
        <div class="d-flex justify-content-between mt-2" style="font-size:0.78rem; color:#9ca3af;">
            <span>Paid: {{ number_format($totalPaid, 2) }}</span>
            <span>Remaining: {{ number_format($balance, 2) }}</span>
        </div>
    </div>
</div>

{{-- Fee Structures --}}
<div class="portal-card">
    <div class="portal-card-header">
        <i class="fas fa-list-alt" style="color:#4361ee;"></i>
        Fee Structure
        @if($currentAy)
            <span class="ms-auto" style="font-size:0.82rem; font-weight:600; color:#6b7280;">
                {{ $currentAy->name ?? $currentAy->year }}
            </span>
        @endif
    </div>
    <div class="portal-card-body p-0">
        @if($feeStructures->count() > 0)
            <div class="table-responsive">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($feeStructures as $i => $fee)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $fee->description ?? $fee->fee_type ?? 'Fee' }}</td>
                                <td class="text-end fw-bold">{{ number_format($fee->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc;">
                            <td colspan="2" class="fw-bold text-end">
                                <i class="fas fa-calculator me-1" style="color:#4361ee;"></i> Total
                            </td>
                            <td class="text-end fw-bold" style="color:#4361ee;">{{ number_format($totalFees, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-4 px-3">
                <p class="mb-0" style="color:#9ca3af; font-size:0.88rem;">
                    No fee structure has been set up for your class this academic year.
                </p>
            </div>
        @endif
    </div>
</div>

{{-- Payment History --}}
<div class="portal-card">
    <div class="portal-card-header">
        <i class="fas fa-history" style="color:#10b981;"></i>
        Payment History
    </div>
    <div class="portal-card-body p-0">
        @if($payments->count() > 0)
            <div class="table-responsive">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th class="text-end">Amount Paid</th>
                            <th>Payment Method</th>
                            <th>Receipt #</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : '—' }}</td>
                                <td class="fw-semibold">{{ $payment->fee->description ?? $payment->fee->fee_type ?? 'Payment' }}</td>
                                <td class="text-end fw-bold" style="color:#10b981;">{{ number_format($payment->amount_paid, 2) }}</td>
                                <td>
                                    @if($payment->payment_method)
                                        <span class="badge bg-light text-dark" style="font-size:0.78rem; font-weight:600;">
                                            {{ ucfirst($payment->payment_method) }}
                                        </span>
                                    @else
                                        <span style="color:#9ca3af;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->receipt_number)
                                        <span class="fw-semibold" style="font-size:0.82rem;">{{ $payment->receipt_number }}</span>
                                    @else
                                        <span style="color:#9ca3af;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 px-3">
                <div style="font-size:2.5rem; color:#d1d5db; margin-bottom:0.5rem;">
                    <i class="fas fa-receipt"></i>
                </div>
                <p class="mb-0" style="color:#9ca3af; font-size:0.88rem;">
                    No payments recorded yet. Payment history will appear here once payments are made.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
