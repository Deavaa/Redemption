@extends('layouts.admin')
@section('title', 'Withdraw Student')

@section('content')
<div class="sl-page">
    {{-- Page Header --}}
    <div class="sl-header">
        <div class="sl-header-left">
            <nav aria-label="breadcrumb" class="sl-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.enrollments.index') }}">Enrollment</a></li>
                    <li class="active">Withdraw Student</li>
                </ol>
            </nav>
        </div>
        <div class="sl-header-right">
            <a href="{{ route('admin.enrollments.show', $enrollment->id) }}" class="sl-btn sl-btn-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    {{-- Warning Box --}}
    <div class="sl-danger-box">
        <div class="sl-danger-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div>
            <h4 class="sl-danger-title">Warning: Student Withdrawal</h4>
            <p class="sl-danger-desc">You are about to withdraw this student from the current academic year enrollment. This action will:</p>
            <ul class="sl-danger-list">
                <li>Change the enrollment status to <strong>Withdrawn</strong></li>
                <li>Set the student's main status to <strong>Inactive</strong></li>
                <li>Record the withdrawal date and reason</li>
                <li>Deactivate the student's user account (if applicable)</li>
            </ul>
            <p class="sl-danger-desc" style="margin-top:0.5rem;margin-bottom:0;"><strong>This action cannot be easily undone.</strong> Please ensure this is the intended action before proceeding.</p>
        </div>
    </div>

    {{-- Student Summary Card --}}
    <div class="sl-card" style="margin-top:0.75rem;margin-bottom:0.75rem;">
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
                <span class="sl-detail-lbl">Branch</span>
                <span class="sl-detail-val">{{ $enrollment->branch->name ?? '-' }}</span>
            </div>
            <div class="sl-detail-row">
                <span class="sl-detail-lbl">Class / Section</span>
                <span class="sl-detail-val">{{ $enrollment->classroom->name ?? '-' }} / {{ $enrollment->section->name ?? '-' }}</span>
            </div>
            @if($enrollment->registration_fee_status !== 'paid' && $enrollment->registration_fee_status !== 'waived')
            <div class="sl-detail-row">
                <span class="sl-detail-lbl">Fee Balance</span>
                @php $bal = ($enrollment->registration_fee ?? 0) - ($enrollment->registration_fee_paid ?? 0); @endphp
                <span class="sl-detail-val" style="color:#dc2626;">{{ number_format($bal, 2) }} ETB outstanding</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Withdrawal Form Card --}}
    <div class="sl-card">
        <form method="POST" action="{{ route('admin.enrollments.process-withdraw', $enrollment->id) }}">
            @csrf

            <div class="sl-form-section">
                <div class="sl-form-section-head">
                    <div class="sl-form-section-icon sl-form-icon-red"><i class="fas fa-user-minus"></i></div>
                    <div>
                        <h3 class="sl-form-section-title">Withdrawal Details</h3>
                        <p class="sl-form-section-desc">Provide the reason and date for this withdrawal</p>
                    </div>
                </div>
                <div class="sl-form-section-body">
                    <div class="sl-form-grid">
                        <div class="sl-form-group sl-form-span-2">
                            <label class="sl-form-label" for="withdrawal_reason">Withdrawal Reason <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-comment-alt sl-input-icon" style="top:0.85rem;transform:none;"></i>
                                <textarea name="withdrawal_reason" id="withdrawal_reason"
                                    class="sl-input sl-textarea {{ $errors->has('withdrawal_reason') ? 'is-invalid' : '' }}"
                                    placeholder="Please provide a detailed reason for the withdrawal..."
                                    rows="4" required>{{ old('withdrawal_reason') }}</textarea>
                            </div>
                            @error('withdrawal_reason')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="withdrawal_date">Withdrawal Date</label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-calendar-minus sl-input-icon"></i>
                                <input type="date" name="withdrawal_date" id="withdrawal_date"
                                    class="sl-input {{ $errors->has('withdrawal_date') ? 'is-invalid' : '' }}"
                                    value="{{ old('withdrawal_date', date('Y-m-d')) }}">
                            </div>
                            @error('withdrawal_date')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="sl-form-actions">
                <a href="{{ route('admin.enrollments.show', $enrollment->id) }}" class="sl-btn sl-btn-ghost">Cancel</a>
                <button type="submit" class="sl-btn sl-btn-danger" onclick="return confirm('Are you absolutely sure you want to withdraw this student? This action will deactivate the student account.')">
                    <i class="fas fa-user-minus"></i> Confirm Withdrawal
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* ========================================================
   ENROLLMENT WITHDRAW - sl-* namespace
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
.sl-btn-outline { background: #fff; color: #4361ee; border: 1px solid #4361ee; }
.sl-btn-outline:hover { background: #4361ee; color: #fff; }
.sl-btn-ghost { background: transparent; color: #6b7280; }
.sl-btn-ghost:hover { color: #1a1a2e; background: #f3f4f6; }
.sl-btn-danger {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: #fff; box-shadow: 0 1px 4px rgba(220,38,38,0.3);
}
.sl-btn-danger:hover { color: #fff; box-shadow: 0 2px 8px rgba(220,38,38,0.4); }

/* Danger Box */
.sl-danger-box {
    display: flex; gap: 0.75rem; padding: 1rem;
    background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px;
    align-items: flex-start;
}
.sl-danger-icon { color: #dc2626; font-size: 1.2rem; flex-shrink: 0; margin-top: 0.1rem; }
.sl-danger-title { font-size: 0.9rem; font-weight: 700; color: #991b1b; margin: 0 0 0.35rem; }
.sl-danger-desc { font-size: 0.78rem; color: #991b1b; margin: 0 0 0.35rem; line-height: 1.5; }
.sl-danger-list {
    margin: 0.35rem 0 0 0; padding-left: 1.25rem;
    font-size: 0.75rem; color: #991b1b; line-height: 1.7;
}
.sl-danger-list li { margin-bottom: 0.15rem; }

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
.sl-form-icon-red { background: #fef2f2; color: #dc2626; }
.sl-form-section-title { font-size: 0.9rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.sl-form-section-desc { font-size: 0.72rem; color: #9ca3af; margin: 0.1rem 0 0; }
.sl-form-section-body { padding: 0.75rem 1.25rem 1.25rem; }

.sl-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
.sl-form-span-2 { grid-column: span 2; }
.sl-form-group { display: flex; flex-direction: column; }
.sl-form-label { font-weight: 600; color: #374151; margin-bottom: 0.3rem; font-size: 0.78rem; }
.sl-required { color: #ef4444; font-weight: 700; }

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
.sl-textarea { resize: vertical; min-height: 80px; }
.sl-form-error { display: block; color: #ef4444; font-size: 0.72rem; margin-top: 0.25rem; font-weight: 500; }

.sl-form-actions {
    display: flex; justify-content: flex-end; gap: 0.5rem;
    padding: 1rem 1.25rem; border-top: 1px solid #f0f0f0; background: #fafbfc;
}

@media (max-width: 768px) {
    .sl-header { flex-direction: column; align-items: stretch; }
    .sl-form-grid { grid-template-columns: 1fr; }
    .sl-form-span-2 { grid-column: span 1; }
    .sl-form-section-body { padding: 0.5rem 0.75rem 1rem; }
    .sl-form-section-head { padding: 0.75rem 0.75rem 0.4rem; }
    .sl-form-actions { padding: 0.75rem; flex-direction: column; }
    .sl-btn { justify-content: center; width: 100%; }
    .sl-danger-box { flex-direction: column; gap: 0.5rem; }
}
</style>
@endpush
@endsection