@extends('layouts.admin')
@section('title', 'Edit Enrollment')

@section('content')
<div class="sl-page">
    {{-- Page Header --}}
    <div class="sl-header">
        <div class="sl-header-left">
            <nav aria-label="breadcrumb" class="sl-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.enrollments.index') }}">Enrollment</a></li>
                    <li class="active">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="sl-header-right">
            <a href="{{ route('admin.enrollments.show', $enrollment->id) }}" class="sl-btn sl-btn-outline">
                <i class="fas fa-eye"></i> View
            </a>
            <a href="{{ route('admin.enrollments.index') }}" class="sl-btn sl-btn-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="sl-card">
        <form method="POST" action="{{ route('admin.enrollments.update', $enrollment->id) }}">
            @csrf @method('PUT')

            {{-- Placement Section --}}
            <div class="sl-form-section">
                <div class="sl-form-section-head">
                    <div class="sl-form-section-icon sl-form-icon-blue"><i class="fas fa-school"></i></div>
                    <div>
                        <h3 class="sl-form-section-title">Placement & Status</h3>
                        <p class="sl-form-section-desc">Update the student's branch, class, section, and enrollment status</p>
                    </div>
                </div>
                <div class="sl-form-section-body">
                    <div class="sl-form-grid">
                        <div class="sl-form-group">
                            <label class="sl-form-label" for="branch_id">Branch <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-building sl-input-icon"></i>
                                <select name="branch_id" id="branch_id" class="sl-input sl-select {{ $errors->has('branch_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Branch --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', $enrollment->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('branch_id')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="section_id">Section <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-layer-group sl-input-icon"></i>
                                <select name="section_id" id="section_id" class="sl-input sl-select {{ $errors->has('section_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Section --</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}" {{ old('section_id', $enrollment->section_id) == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('section_id')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="status">Status <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-toggle-on sl-input-icon"></i>
                                <select name="status" id="status" class="sl-input sl-select {{ $errors->has('status') ? 'is-invalid' : '' }}" required>
                                    <option value="enrolled" {{ old('status', $enrollment->status) === 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                                    <option value="pending" {{ old('status', $enrollment->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="withdrawn" {{ old('status', $enrollment->status) === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                                    <option value="graduated" {{ old('status', $enrollment->status) === 'graduated' ? 'selected' : '' }}>Graduated</option>
                                    <option value="transferred" {{ old('status', $enrollment->status) === 'transferred' ? 'selected' : '' }}>Transferred</option>
                                </select>
                            </div>
                            @error('status')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Registration Fee Section --}}
            <div class="sl-form-section">
                <div class="sl-form-section-head">
                    <div class="sl-form-section-icon sl-form-icon-gold"><i class="fas fa-money-bill-wave"></i></div>
                    <div>
                        <h3 class="sl-form-section-title">Registration Fee</h3>
                        <p class="sl-form-section-desc">Update fee details and payment information</p>
                    </div>
                </div>
                <div class="sl-form-section-body">
                    <div class="sl-form-grid">
                        <div class="sl-form-group">
                            <label class="sl-form-label" for="registration_fee">Fee Amount <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-dollar-sign sl-input-icon"></i>
                                <input type="number" name="registration_fee" id="registration_fee"
                                    step="0.01" min="0"
                                    class="sl-input {{ $errors->has('registration_fee') ? 'is-invalid' : '' }}"
                                    value="{{ old('registration_fee', $enrollment->registration_fee) }}"
                                    required>
                            </div>
                            @error('registration_fee')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="registration_fee_paid">Paid Amount <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-coins sl-input-icon"></i>
                                <input type="number" name="registration_fee_paid" id="registration_fee_paid"
                                    step="0.01" min="0"
                                    class="sl-input {{ $errors->has('registration_fee_paid') ? 'is-invalid' : '' }}"
                                    value="{{ old('registration_fee_paid', $enrollment->registration_fee_paid) }}"
                                    required>
                            </div>
                            @error('registration_fee_paid')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="registration_fee_status">Fee Status <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-toggle-on sl-input-icon"></i>
                                <select name="registration_fee_status" id="registration_fee_status" class="sl-input sl-select {{ $errors->has('registration_fee_status') ? 'is-invalid' : '' }}" required>
                                    <option value="unpaid" {{ old('registration_fee_status', $enrollment->registration_fee_status) === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                    <option value="partial" {{ old('registration_fee_status', $enrollment->registration_fee_status) === 'partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="paid" {{ old('registration_fee_status', $enrollment->registration_fee_status) === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="waived" {{ old('registration_fee_status', $enrollment->registration_fee_status) === 'waived' ? 'selected' : '' }}>Waived</option>
                                </select>
                            </div>
                            @error('registration_fee_status')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="registration_fee_payment_method">Payment Method</label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-credit-card sl-input-icon"></i>
                                <select name="registration_fee_payment_method" id="registration_fee_payment_method" class="sl-input sl-select">
                                    <option value="">-- Select Method --</option>
                                    <option value="cash" {{ old('registration_fee_payment_method', $enrollment->registration_fee_payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank" {{ old('registration_fee_payment_method', $enrollment->registration_fee_payment_method) === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="mobile" {{ old('registration_fee_payment_method', $enrollment->registration_fee_payment_method) === 'mobile' ? 'selected' : '' }}>Mobile Payment</option>
                                    <option value="cheque" {{ old('registration_fee_payment_method', $enrollment->registration_fee_payment_method) === 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="online" {{ old('registration_fee_payment_method', $enrollment->registration_fee_payment_method) === 'online' ? 'selected' : '' }}>Online</option>
                                </select>
                            </div>
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="registration_fee_receipt_number">Receipt Number</label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-receipt sl-input-icon"></i>
                                <input type="text" name="registration_fee_receipt_number" id="registration_fee_receipt_number"
                                    class="sl-input"
                                    value="{{ old('registration_fee_receipt_number', $enrollment->registration_fee_receipt_number) }}"
                                    placeholder="e.g. REC-00123">
                            </div>
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="registration_fee_notes">Fee Notes</label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-sticky-note sl-input-icon" style="top:0.85rem;transform:none;"></i>
                                <textarea name="registration_fee_notes" id="registration_fee_notes"
                                    class="sl-input sl-textarea"
                                    placeholder="Fee payment notes..."
                                    rows="2">{{ old('registration_fee_notes', $enrollment->registration_fee_notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes Section --}}
            <div class="sl-form-section">
                <div class="sl-form-section-head">
                    <div class="sl-form-section-icon sl-form-icon-gray"><i class="fas fa-align-left"></i></div>
                    <div>
                        <h3 class="sl-form-section-title">Additional Notes</h3>
                        <p class="sl-form-section-desc">Any additional information about this enrollment</p>
                    </div>
                </div>
                <div class="sl-form-section-body">
                    <div class="sl-form-grid">
                        <div class="sl-form-group sl-form-span-2">
                            <label class="sl-form-label" for="notes">Notes</label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-align-left sl-input-icon" style="top:0.85rem;transform:none;"></i>
                                <textarea name="notes" id="notes"
                                    class="sl-input sl-textarea"
                                    placeholder="Additional enrollment notes..."
                                    rows="3">{{ old('notes', $enrollment->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="sl-form-actions">
                <a href="{{ route('admin.enrollments.index') }}" class="sl-btn sl-btn-ghost">Cancel</a>
                <button type="submit" class="sl-btn sl-btn-primary">
                    <i class="fas fa-save"></i> Update Enrollment
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* ========================================================
   ENROLLMENT EDIT - sl-* namespace
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
.sl-form-icon-blue { background: #eef2ff; color: #4361ee; }
.sl-form-icon-gold { background: #fefce8; color: #d97706; }
.sl-form-icon-gray { background: #f3f4f6; color: #6b7280; }
.sl-form-section-title { font-size: 0.9rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.sl-form-section-desc { font-size: 0.72rem; color: #9ca3af; margin: 0.1rem 0 0; }
.sl-form-section-body { padding: 0.75rem 1.25rem 1.25rem; }

.sl-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
.sl-form-span-2 { grid-column: span 2; }
.sl-form-group { display: flex; flex-direction: column; }
.sl-form-label { font-weight: 600; color: #374151; margin-bottom: 0.3rem; font-size: 0.78rem; }
.sl-form-label small { font-weight: 400; color: #9ca3af; font-size: 0.7rem; }
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
    .sl-form-grid { grid-template-columns: 1fr; }
    .sl-form-span-2 { grid-column: span 1; }
    .sl-form-section-body { padding: 0.5rem 0.75rem 1rem; }
    .sl-form-section-head { padding: 0.75rem 0.75rem 0.4rem; }
    .sl-form-actions { padding: 0.75rem; flex-direction: column; }
    .sl-btn { justify-content: center; width: 100%; }
}
</style>
@endpush
@endsection