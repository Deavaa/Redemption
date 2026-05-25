@extends('layouts.admin')
@section('title', 'Pay Registration Fee')

@section('content')
<div class="sl-page">
    {{-- Page Header --}}
    <div class="sl-header">
        <div class="sl-header-left">
            <nav aria-label="breadcrumb" class="sl-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.enrollments.index') }}">Enrollment</a></li>
                    <li class="active">Pay Registration Fee</li>
                </ol>
            </nav>
        </div>
        <div class="sl-header-right">
            <a href="{{ route('admin.enrollments.show', $enrollment->id) }}" class="sl-btn sl-btn-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    {{-- Student Summary Card --}}
    <div class="sl-card" style="margin-bottom:0.75rem;">
        <div class="sl-card-head">
            <h2 class="sl-card-title"><i class="fas fa-user" style="margin-right:0.3rem;color:#4361ee;font-size:0.75rem;"></i> Student Information</h2>
        </div>
        <div class="sl-detail-body">
            <div class="sl-detail-row">
                <span class="sl-detail-lbl">Student</span>
                <span class="sl-detail-val">{{ $enrollment->student->full_name ?? '-' }}</span>
            </div>
            <div class="sl-detail-row">
                <span class="sl-detail-lbl">Admission No.</span>
                <span class="sl-detail-val">{{ $enrollment->student->admission_number ?? '-' }}</span>
            </div>
            <div class="sl-detail-row">
                <span class="sl-detail-lbl">Academic Year</span>
                <span class="sl-detail-val">{{ $enrollment->academicYear->name ?? '-' }}</span>
            </div>
            <div class="sl-detail-row">
                <span class="sl-detail-lbl">Class / Section</span>
                <span class="sl-detail-val">{{ $enrollment->classroom->name ?? '-' }} / {{ $enrollment->section->name ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- Fee Info Card --}}
    <div class="sl-card" style="margin-bottom:0.75rem;">
        <div class="sl-card-head">
            <h2 class="sl-card-title"><i class="fas fa-money-bill-wave" style="margin-right:0.3rem;color:#d97706;font-size:0.75rem;"></i> Fee Summary</h2>
        </div>
        <div class="sl-fee-grid">
            <div class="sl-fee-item">
                <span class="sl-fee-lbl">Total Fee</span>
                <span class="sl-fee-val">{{ number_format($enrollment->registration_fee ?? 0, 2) }} ETB</span>
            </div>
            <div class="sl-fee-item">
                <span class="sl-fee-lbl">Already Paid</span>
                <span class="sl-fee-val sl-fee-paid">{{ number_format($enrollment->registration_fee_paid ?? 0, 2) }} ETB</span>
            </div>
            @php $balance = ($enrollment->registration_fee ?? 0) - ($enrollment->registration_fee_paid ?? 0); @endphp
            <div class="sl-fee-item">
                <span class="sl-fee-lbl">Balance Due</span>
                <span class="sl-fee-val sl-fee-balance">{{ number_format($balance, 2) }} ETB</span>
            </div>
        </div>
    </div>

    {{-- Payment Form Card --}}
    <div class="sl-card">
        <form method="POST" action="{{ route('admin.enrollments.process-pay-registration-fee', $enrollment->id) }}">
            @csrf

            <div class="sl-form-section">
                <div class="sl-form-section-head">
                    <div class="sl-form-section-icon sl-form-icon-green"><i class="fas fa-credit-card"></i></div>
                    <div>
                        <h3 class="sl-form-section-title">Record Payment</h3>
                        <p class="sl-form-section-desc">Enter the payment details for this registration fee</p>
                    </div>
                </div>
                <div class="sl-form-section-body">
                    <div class="sl-form-grid">
                        <div class="sl-form-group">
                            <label class="sl-form-label" for="amount">Amount (ETB) <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-coins sl-input-icon"></i>
                                <input type="number" name="amount" id="amount"
                                    step="0.01" min="0.01" max="{{ $balance }}"
                                    class="sl-input {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                                    placeholder="Enter payment amount" required>
                            </div>
                            <span class="sl-help-text">Maximum amount: {{ number_format($balance, 2) }} ETB</span>
                            @error('amount')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="payment_method">Payment Method <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-wallet sl-input-icon"></i>
                                <select name="payment_method" id="payment_method" class="sl-input sl-select {{ $errors->has('payment_method') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Method --</option>
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="mobile" {{ old('payment_method') === 'mobile' ? 'selected' : '' }}>Mobile Payment</option>
                                    <option value="cheque" {{ old('payment_method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="online" {{ old('payment_method') === 'online' ? 'selected' : '' }}>Online</option>
                                </select>
                            </div>
                            @error('payment_method')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="receipt_number">Receipt Number</label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-receipt sl-input-icon"></i>
                                <input type="text" name="receipt_number" id="receipt_number"
                                    class="sl-input"
                                    value="{{ old('receipt_number') }}"
                                    placeholder="e.g. REC-00123">
                            </div>
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="notes">Notes</label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-sticky-note sl-input-icon" style="top:0.85rem;transform:none;"></i>
                                <textarea name="notes" id="notes"
                                    class="sl-input sl-textarea"
                                    placeholder="Payment notes..."
                                    rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="sl-form-actions">
                <a href="{{ route('admin.enrollments.show', $enrollment->id) }}" class="sl-btn sl-btn-ghost">Cancel</a>
                <button type="submit" class="sl-btn sl-btn-primary">
                    <i class="fas fa-check"></i> Record Payment
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* ========================================================
   ENROLLMENT PAY FEE - sl-* namespace
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
.sl-btn-ghost { background: transparent; color: #6b7280; }
.sl-btn-ghost:hover { color: #1a1a2e; background: #f3f4f6; }

.sl-card {
    background: #fff; border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;
    overflow: hidden;
}
.sl-card-head {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.5rem 0.75rem; border-bottom: 1px solid #f0f0f0;
}
.sl-card-title { font-size: 0.85rem; font-weight: 700; color: #1a1a2e; margin: 0; }

/* Detail */
.sl-detail-body { padding: 0.65rem 0.75rem; }
.sl-detail-row {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 0.35rem 0; border-bottom: 1px solid #f9fafb;
}
.sl-detail-row:last-child { border-bottom: none; }
.sl-detail-lbl { font-size: 0.72rem; color: #6b7280; font-weight: 500; min-width: 100px; flex-shrink: 0; }
.sl-detail-val { font-size: 0.78rem; color: #1a1a2e; font-weight: 600; text-align: right; }

/* Fee Grid */
.sl-fee-grid {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 0; border-top: 0;
}
.sl-fee-item {
    padding: 0.75rem; text-align: center;
    border-right: 1px solid #f0f0f0;
}
.sl-fee-item:last-child { border-right: none; }
.sl-fee-lbl { display: block; font-size: 0.68rem; color: #6b7280; font-weight: 500; margin-bottom: 0.2rem; text-transform: uppercase; letter-spacing: 0.3px; }
.sl-fee-val { display: block; font-size: 1.1rem; font-weight: 800; color: #1a1a2e; }
.sl-fee-paid { color: #059669; }
.sl-fee-balance { color: #dc2626; }

/* Form */
.sl-form-section { border-bottom: 1px solid #f0f0f0; }
.sl-form-section:last-of-type { border-bottom: none; }
.sl-form-section-head {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 1rem 1.25rem 0.5rem;
}
.sl-form-section-icon {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem; flex-shrink: 0;
}
.sl-form-icon-green { background: #ecfdf5; color: #10b981; }
.sl-form-section-title { font-size: 0.9rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.sl-form-section-desc { font-size: 0.72rem; color: #9ca3af; margin: 0.1rem 0 0; }
.sl-form-section-body { padding: 0.75rem 1.25rem 1.25rem; }

.sl-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
.sl-form-group { display: flex; flex-direction: column; }
.sl-form-label { font-weight: 600; color: #374151; margin-bottom: 0.3rem; font-size: 0.78rem; }
.sl-required { color: #ef4444; font-weight: 700; }
.sl-help-text { font-size: 0.68rem; color: #9ca3af; margin: 0.2rem 0 0; line-height: 1.4; }

.sl-input-wrap { position: relative; }
.sl-input-icon {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: #9ca3af; font-size: 0.75rem; pointer-events: none; z-index: 1;
}
.sl-input {
    width: 100%; border: 1px solid #e5e7eb; border-radius: 7px;
    padding: 0.45rem 0.7rem 0.45rem 2rem; font-size: 0.82rem;
    color: #1a1a2e; background: #fff; transition: all 0.2s;
}
.sl-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.sl-input::placeholder { color: #c5c9d2; }
.sl-input.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }
.sl-textarea { resize: vertical; min-height: 70px; }
.sl-select {
    appearance: none; cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.6rem center; background-repeat: no-repeat; background-size: 1rem;
    padding-right: 2rem;
}
.sl-form-error { display: block; color: #ef4444; font-size: 0.72rem; margin-top: 0.25rem; font-weight: 500; }

.sl-form-actions {
    display: flex; justify-content: flex-end; gap: 0.5rem;
    padding: 1rem 1.25rem; border-top: 1px solid #f0f0f0; background: #fafbfc;
}

@media (max-width: 768px) {
    .sl-header { flex-direction: column; align-items: stretch; }
    .sl-fee-grid { grid-template-columns: 1fr; }
    .sl-fee-item { border-right: none; border-bottom: 1px solid #f0f0f0; }
    .sl-fee-item:last-child { border-bottom: none; }
    .sl-form-grid { grid-template-columns: 1fr; }
    .sl-form-section-body { padding: 0.5rem 0.75rem 1rem; }
    .sl-form-section-head { padding: 0.75rem 0.75rem 0.4rem; }
    .sl-form-actions { padding: 0.75rem; flex-direction: column; }
    .sl-btn { justify-content: center; width: 100%; }
}
</style>
@endpush
@endsection