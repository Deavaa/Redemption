@extends('layouts.admin')
@section('title', 'Enrollment Details')

@section('content')
<div class="sl-page">
    {{-- Page Header --}}
    <div class="sl-header">
        <div class="sl-header-left">
            <nav aria-label="breadcrumb" class="sl-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.enrollments.index') }}">Enrollment</a></li>
                    <li class="active">{{ $enrollment->student->full_name ?? 'Student' }}</li>
                </ol>
            </nav>
        </div>
        <div class="sl-header-right">
            <a href="{{ route('admin.enrollments.index') }}" class="sl-btn sl-btn-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <a href="{{ route('admin.enrollments.edit', $enrollment->id) }}" class="sl-btn sl-btn-outline" style="color:#d97706;border-color:#d97706;">
                <i class="fas fa-pen"></i> Edit
            </a>
            @if(in_array($enrollment->registration_fee_status ?? '', ['unpaid', 'partial']))
            <a href="{{ route('admin.enrollments.pay-registration-fee', $enrollment->id) }}" class="sl-btn sl-btn-outline" style="color:#059669;border-color:#059669;">
                <i class="fas fa-money-bill-wave"></i> Pay Fee
            </a>
            @endif
            @if($enrollment->registration_fee_status !== 'waived' && $enrollment->registration_fee_status !== 'paid')
            <form method="POST" action="{{ route('admin.enrollments.waive-registration-fee', $enrollment->id) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to waive the registration fee?')">
                @csrf
                <button type="submit" class="sl-btn sl-btn-outline" style="color:#2563eb;border-color:#2563eb;">
                    <i class="fas fa-hand-holding-heart"></i> Waive Fee
                </button>
            </form>
            @endif
            @if($enrollment->status === 'enrolled')
            <a href="{{ route('admin.enrollments.withdraw', $enrollment->id) }}" class="sl-btn sl-btn-outline" style="color:#dc2626;border-color:#dc2626;">
                <i class="fas fa-user-minus"></i> Withdraw
            </a>
            @endif
        </div>
    </div>

    {{-- Info Cards Row --}}
    <div class="sl-detail-grid">
        {{-- Student Info Card --}}
        <div class="sl-card">
            <div class="sl-card-head">
                <h2 class="sl-card-title"><i class="fas fa-user" style="margin-right:0.3rem;color:#4361ee;font-size:0.75rem;"></i> Student Information</h2>
            </div>
            <div class="sl-detail-body">
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Name</span>
                    <span class="sl-detail-val">{{ $enrollment->student->full_name ?? '-' }}</span>
                </div>
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Admission No.</span>
                    <span class="sl-detail-val">{{ $enrollment->student->admission_number ?? '-' }}</span>
                </div>
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Branch</span>
                    <span class="sl-detail-val">{{ $enrollment->branch->name ?? '-' }}</span>
                </div>
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Class</span>
                    <span class="sl-detail-val">{{ $enrollment->classroom->name ?? '-' }}</span>
                </div>
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Section</span>
                    <span class="sl-detail-val">{{ $enrollment->section->name ?? '-' }}</span>
                </div>
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Roll Number</span>
                    <span class="sl-detail-val">{{ $enrollment->roll_number ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Enrollment Info Card --}}
        <div class="sl-card">
            <div class="sl-card-head">
                <h2 class="sl-card-title"><i class="fas fa-clipboard-check" style="margin-right:0.3rem;color:#10b981;font-size:0.75rem;"></i> Enrollment Information</h2>
            </div>
            <div class="sl-detail-body">
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Academic Year</span>
                    <span class="sl-detail-val">{{ $enrollment->academicYear->name ?? '-' }}</span>
                </div>
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Enrollment Type</span>
                    <span class="sl-detail-val">{{ ucfirst(str_replace('_', ' ', $enrollment->enrollment_type ?? '-')) }}</span>
                </div>
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Enrollment Date</span>
                    <span class="sl-detail-val">{{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('M d, Y') : '-' }}</span>
                </div>
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Status</span>
                    <span class="sl-detail-val">
                        @php
                            $esb = match($enrollment->status ?? '') {
                                'enrolled' => 'sl-tag-green',
                                'pending' => 'sl-tag-yellow',
                                'withdrawn' => 'sl-tag-red',
                                'graduated' => 'sl-tag-blue',
                                'transferred' => 'sl-tag-gray',
                                default => 'sl-tag-gray'
                            };
                        @endphp
                        <span class="sl-tag {{ $esb }}">{{ ucfirst($enrollment->status ?? 'N/A') }}</span>
                    </span>
                </div>
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Enrolled By</span>
                    <span class="sl-detail-val">{{ $enrollment->enrolledBy->name ?? 'System' }}</span>
                </div>
                @if($enrollment->notes)
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Notes</span>
                    <span class="sl-detail-val sl-detail-note">{{ $enrollment->notes }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Registration Fee Card --}}
        <div class="sl-card">
            <div class="sl-card-head">
                <h2 class="sl-card-title"><i class="fas fa-money-bill-wave" style="margin-right:0.3rem;color:#d97706;font-size:0.75rem;"></i> Registration Fee</h2>
            </div>
            <div class="sl-detail-body">
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Fee Amount</span>
                    <span class="sl-detail-val sl-detail-amount">{{ number_format($enrollment->registration_fee ?? 0, 2) }} ETB</span>
                </div>
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Paid Amount</span>
                    <span class="sl-detail-val" style="color:#059669;">{{ number_format($enrollment->registration_fee_paid ?? 0, 2) }} ETB</span>
                </div>
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Balance</span>
                    @php $balance = ($enrollment->registration_fee ?? 0) - ($enrollment->registration_fee_paid ?? 0); @endphp
                    <span class="sl-detail-val" style="{{ $balance > 0 ? 'color:#dc2626;' : 'color:#059669;' }}">{{ number_format($balance, 2) }} ETB</span>
                </div>
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Status</span>
                    <span class="sl-detail-val">
                        @php
                            $fsb = match($enrollment->registration_fee_status ?? '') {
                                'paid' => 'sl-tag-green',
                                'unpaid' => 'sl-tag-red',
                                'partial' => 'sl-tag-yellow',
                                'waived' => 'sl-tag-blue',
                                default => 'sl-tag-gray'
                            };
                            $fsLabel = match($enrollment->registration_fee_status ?? '') {
                                'waived' => 'Waived',
                                default => ucfirst($enrollment->registration_fee_status ?? 'N/A')
                            };
                        @endphp
                        <span class="sl-tag {{ $fsb }}">{{ $fsLabel }}</span>
                    </span>
                </div>
                @if($enrollment->registration_fee_payment_method)
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Payment Method</span>
                    <span class="sl-detail-val">{{ ucfirst($enrollment->registration_fee_payment_method) }}</span>
                </div>
                @endif
                @if($enrollment->registration_fee_receipt_number)
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Receipt No.</span>
                    <span class="sl-detail-val">{{ $enrollment->registration_fee_receipt_number }}</span>
                </div>
                @endif
                @if($enrollment->registration_fee_date)
                <div class="sl-detail-row">
                    <span class="sl-detail-lbl">Payment Date</span>
                    <span class="sl-detail-val">{{ \Carbon\Carbon::parse($enrollment->registration_fee_date)->format('M d, Y') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Enrollment History --}}
    <div class="sl-card" style="margin-top:0.75rem;">
        <div class="sl-card-head">
            <div class="sl-card-head-left">
                <h2 class="sl-card-title">Enrollment History</h2>
                <span class="sl-count">{{ $enrollmentHistory->count() }}</span>
            </div>
        </div>

        @if($enrollmentHistory->count() > 0)
        <div class="sl-table-wrap">
            <table class="sl-table">
                <thead>
                    <tr>
                        <th class="sl-th-narrow">#</th>
                        <th>Academic Year</th>
                        <th>Branch</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Type</th>
                        <th class="sl-th-center">Fee Status</th>
                        <th class="sl-th-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enrollmentHistory as $hist)
                    <tr>
                        <td class="sl-td-narrow"><span class="sl-num">{{ $loop->iteration }}</span></td>
                        <td><span class="sl-text">{{ $hist->academicYear->name ?? '-' }}</span></td>
                        <td><span class="sl-text">{{ $hist->branch->name ?? '-' }}</span></td>
                        <td><span class="sl-text">{{ $hist->classroom->name ?? '-' }}</span></td>
                        <td><span class="sl-text">{{ $hist->section->name ?? '-' }}</span></td>
                        <td><span class="sl-text">{{ ucfirst(str_replace('_', ' ', $hist->enrollment_type ?? '-')) }}</span></td>
                        <td class="sl-td-center">
                            @php
                                $hfsb = match($hist->registration_fee_status ?? '') {
                                    'paid' => 'sl-tag-green',
                                    'unpaid' => 'sl-tag-red',
                                    'partial' => 'sl-tag-yellow',
                                    'waived' => 'sl-tag-blue',
                                    default => 'sl-tag-gray'
                                };
                            @endphp
                            <span class="sl-tag {{ $hfsb }}">{{ ucfirst($hist->registration_fee_status ?? 'N/A') }}</span>
                        </td>
                        <td class="sl-td-center">
                            @php
                                $hesb = match($hist->status ?? '') {
                                    'enrolled' => 'sl-tag-green',
                                    'pending' => 'sl-tag-yellow',
                                    'withdrawn' => 'sl-tag-red',
                                    'graduated' => 'sl-tag-blue',
                                    'transferred' => 'sl-tag-gray',
                                    default => 'sl-tag-gray'
                                };
                            @endphp
                            <span class="sl-tag {{ $hesb }}">{{ ucfirst($hist->status ?? 'N/A') }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="sl-empty" style="padding:1.5rem;">
            <p style="margin:0;color:#9ca3af;font-size:0.82rem;">No enrollment history available.</p>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
/* ========================================================
   ENROLLMENT SHOW - sl-* namespace
   ======================================================== */
.sl-page { animation: slIn 0.3s ease-out; }
@keyframes slIn { from { opacity: 0; } to { opacity: 1; } }

.sl-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;
}
.sl-header-left { flex: 1; }
.sl-header-right { display: flex; gap: 0.4rem; flex-wrap: wrap; }

.sl-breadcrumb ol {
    display: flex; list-style: none; padding: 0; margin: 0;
    gap: 0.3rem; font-size: 0.72rem; align-items: center;
}
.sl-breadcrumb li { color: #adb5bd; }
.sl-breadcrumb li a { color: #6c757d; text-decoration: none; }
.sl-breadcrumb li a:hover { color: #4361ee; }
.sl-breadcrumb li + li::before { content: '/'; margin-right: 0.3rem; color: #dee2e6; }
.sl-breadcrumb li.active { color: #4361ee; font-weight: 500; }

.sl-btn {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.3rem 0.65rem; border-radius: 6px; font-weight: 600;
    font-size: 0.73rem; text-decoration: none; border: none; cursor: pointer;
    transition: all 0.2s; white-space: nowrap;
}
.sl-btn-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 1px 4px rgba(67,97,238,0.3); }
.sl-btn-primary:hover { color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.4); }
.sl-btn-outline { background: #fff; color: #4361ee; border: 1px solid #4361ee; }
.sl-btn-outline:hover { background: #4361ee; color: #fff; }

/* Detail Grid */
.sl-detail-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.sl-card {
    background: #fff; border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;
    overflow: hidden;
}
.sl-card-head {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.5rem 0.75rem; border-bottom: 1px solid #f0f0f0;
}
.sl-card-head-left { display: flex; align-items: center; gap: 0.4rem; }
.sl-card-title { font-size: 0.85rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.sl-count {
    display: inline-block; padding: 1px 7px; border-radius: 20px;
    font-size: 0.68rem; font-weight: 600; background: #f3f4f6; color: #6b7280;
}

/* Detail Body */
.sl-detail-body { padding: 0.65rem 0.75rem; }
.sl-detail-row {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 0.35rem 0; border-bottom: 1px solid #f9fafb;
}
.sl-detail-row:last-child { border-bottom: none; }
.sl-detail-lbl {
    font-size: 0.72rem; color: #6b7280; font-weight: 500;
    min-width: 90px; flex-shrink: 0;
}
.sl-detail-val {
    font-size: 0.78rem; color: #1a1a2e; font-weight: 600;
    text-align: right; word-break: break-word;
}
.sl-detail-note {
    font-weight: 400; color: #6b7280; font-style: italic;
}
.sl-detail-amount { font-size: 0.9rem; color: #d97706; }

/* Tags */
.sl-tag {
    display: inline-block; padding: 1px 7px; border-radius: 20px;
    font-size: 0.66rem; font-weight: 600; line-height: 1.5;
}
.sl-tag-green { background: #ecfdf5; color: #059669; }
.sl-tag-red { background: #fef2f2; color: #dc2626; }
.sl-tag-blue { background: #eff6ff; color: #2563eb; }
.sl-tag-yellow { background: #fefce8; color: #b45309; }
.sl-tag-gray { background: #f3f4f6; color: #6b7280; }

/* Table */
.sl-table-wrap { overflow-x: auto; }
.sl-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; }
.sl-table thead th {
    background: #f9fafb; padding: 0.4rem 0.55rem; text-align: left;
    font-weight: 600; font-size: 0.65rem; text-transform: uppercase;
    letter-spacing: 0.3px; color: #6b7280; border-bottom: 2px solid #e5e7eb;
    white-space: nowrap;
}
.sl-table tbody tr { border-bottom: 1px solid #f3f4f6; }
.sl-table tbody tr:hover { background: #f8f9ff; }
.sl-table td { padding: 0.35rem 0.55rem; vertical-align: middle; color: #374151; }
.sl-th-narrow { width: 36px; }
.sl-th-center { text-align: center; }
.sl-td-narrow { width: 36px; }
.sl-td-center { text-align: center; }
.sl-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px; border-radius: 5px;
    background: #f3f4f6; color: #6b7280; font-weight: 600; font-size: 0.68rem;
}
.sl-text { color: #4b5563; font-size: 0.75rem; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; }

.sl-empty { text-align: center; padding: 2.5rem 1.5rem; }

@media (max-width: 1024px) {
    .sl-detail-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 768px) {
    .sl-header { flex-direction: column; align-items: stretch; }
    .sl-detail-grid { grid-template-columns: 1fr; }
    .sl-btn { padding: 0.25rem 0.5rem; font-size: 0.68rem; }
}
</style>
@endpush
@endsection