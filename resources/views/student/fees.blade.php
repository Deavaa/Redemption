@extends('student.layout')

@section('title', 'My Fees')

@section('content')
<div class="dash-welcome">
    <h2><i class="fas fa-wallet me-2" style="color: var(--primary);"></i>My Fees</h2>
    <p>View your fee payment history and outstanding balance.</p>
</div>

{{-- Fee Summary --}}
<div class="fee-summary">
    <div class="fee-card total">
        <h6>Total Fees</h6>
        <div class="amount">{{ number_format($totalFees, 2) }}</div>
    </div>
    <div class="fee-card paid">
        <h6>Total Paid</h6>
        <div class="amount">{{ number_format($totalPaid, 2) }}</div>
    </div>
    <div class="fee-card balance">
        <h6>Balance Due</h6>
        <div class="amount">{{ number_format($balance, 2) }}</div>
    </div>
</div>

{{-- Fee Payment History --}}
<div class="student-card">
    <div class="student-card-header">
        <h5><i class="fas fa-receipt me-2" style="color: var(--primary);"></i>Payment History</h5>
    </div>
    <div class="student-card-body" style="padding:0;">
        @if($feePayments->count() > 0)
            <div class="table-responsive">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fee Type</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                            <th>Method</th>
                            <th>Receipt #</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $sn = 1; @endphp
                        @foreach($feePayments as $payment)
                            <tr>
                                <td class="text-muted">{{ $sn++ }}</td>
                                <td class="fw-semibold">
                                    {{ $payment->fee ? ($payment->fee->fee_type ?? $payment->fee->description ?? 'Fee') : 'Fee' }}
                                    @if($payment->fee && $payment->fee->classroom)
                                        <br><small class="text-muted">{{ $payment->fee->classroom->name }}</small>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ number_format($payment->amount_paid, 2) }}</td>
                                <td>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : '-' }}</td>
                                <td>
                                    @php
                                        $method = $payment->payment_method ?? 'N/A';
                                        $methodIcon = 'fas fa-money-bill';
                                        if ($method === 'bank_transfer') $methodIcon = 'fas fa-university';
                                        elseif ($method === 'card') $methodIcon = 'fas fa-credit-card';
                                        elseif ($method === 'online') $methodIcon = 'fas fa-globe';
                                        elseif ($method === 'cheque') $methodIcon = 'fas fa-money-check';
                                    @endphp
                                    <i class="{{ $methodIcon }} me-1 text-muted"></i>
                                    {{ ucfirst(str_replace('_', ' ', $method)) }}
                                </td>
                                <td>
                                    @if($payment->receipt_number)
                                        <span class="badge rounded-pill" style="background: var(--accent-light); color: var(--accent); font-size: 11px;">
                                            {{ $payment->receipt_number }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $status = $payment->status ?? 'pending';
                                        $statusClass = 'grade-c';
                                        if ($status === 'paid' || $status === 'completed') $statusClass = 'grade-a';
                                        elseif ($status === 'pending') $statusClass = 'grade-c';
                                        elseif ($status === 'partial') $statusClass = 'grade-b';
                                        elseif ($status === 'overdue' || $status === 'failed') $statusClass = 'grade-f';
                                    @endphp
                                    <span class="grade-badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <p>No fee payment records found. Your fee information will appear here once payments are recorded.</p>
            </div>
        @endif
    </div>
</div>
@endsection
