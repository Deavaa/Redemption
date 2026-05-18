@extends('layouts.admin')
@section('title', 'New Leave')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">HR</a></li>
                    <li><a href="{{ route('admin.leaves.index') }}">Leaves</a></li>
                    <li class="active">New Leave</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.leaves.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="modern-card">
        <form method="POST" action="{{ route('admin.leaves.store') }}">
            @csrf

            {{-- Employee & Leave Type --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Employee & Leave Type</h3>
                        <p class="modern-form-section-desc">Select the employee and type of leave</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="employee_id">
                                Employee <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-user modern-input-icon"></i>
                                <input type="text"
                                    name="employee_id"
                                    id="employee_id"
                                    class="modern-input {{ $errors->has('employee_id') ? 'is-invalid' : '' }}"
                                    value="{{ old('employee_id') }}"
                                    placeholder="Employee ID"
                                    required
                                    autofocus>
                            </div>
                            @error('employee_id')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="leave_type">
                                Leave Type <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-tag modern-input-icon"></i>
                                <select name="leave_type" id="leave_type" class="modern-select {{ $errors->has('leave_type') ? 'is-invalid' : '' }}" required>
                                    <option value="">Select leave type...</option>
                                    <option value="sick" {{ old('leave_type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                                    <option value="annual" {{ old('leave_type') == 'annual' ? 'selected' : '' }}>Annual Leave</option>
                                    <option value="emergency" {{ old('leave_type') == 'emergency' ? 'selected' : '' }}>Emergency Leave</option>
                                    <option value="maternity" {{ old('leave_type') == 'maternity' ? 'selected' : '' }}>Maternity Leave</option>
                                    <option value="paternity" {{ old('leave_type') == 'paternity' ? 'selected' : '' }}>Paternity Leave</option>
                                </select>
                            </div>
                            @error('leave_type')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Duration --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-gold">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Leave Duration</h3>
                        <p class="modern-form-section-desc">Set the start and end dates for the leave</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="start_date">
                                Start Date <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar-plus modern-input-icon"></i>
                                <input type="date"
                                    name="start_date"
                                    id="start_date"
                                    class="modern-input {{ $errors->has('start_date') ? 'is-invalid' : '' }}"
                                    value="{{ old('start_date') }}"
                                    required>
                            </div>
                            @error('start_date')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="end_date">
                                End Date <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar-minus modern-input-icon"></i>
                                <input type="date"
                                    name="end_date"
                                    id="end_date"
                                    class="modern-input {{ $errors->has('end_date') ? 'is-invalid' : '' }}"
                                    value="{{ old('end_date') }}"
                                    required>
                            </div>
                            @error('end_date')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="total_days">
                                Total Days
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calculator modern-input-icon"></i>
                                <input type="number"
                                    name="total_days"
                                    id="total_days"
                                    class="modern-input {{ $errors->has('total_days') ? 'is-invalid' : '' }}"
                                    value="{{ old('total_days') }}"
                                    placeholder="Auto-calculated or manual"
                                    min="1">
                            </div>
                            @error('total_days')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reason & Status --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-purple">
                        <i class="fas fa-clipboard"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Reason & Status</h3>
                        <p class="modern-form-section-desc">Provide the leave reason and set initial status</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="reason">
                                Reason
                            </label>
                            <textarea name="reason"
                                id="reason"
                                class="modern-textarea {{ $errors->has('reason') ? 'is-invalid' : '' }}"
                                rows="4"
                                placeholder="Describe the reason for the leave...">{{ old('reason') }}</textarea>
                            @error('reason')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="status">
                                Status <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-flag modern-input-icon"></i>
                                <select name="status" id="status" class="modern-select {{ $errors->has('status') ? 'is-invalid' : '' }}" required>
                                    <option value="">Select status...</option>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            @error('status')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions">
                <a href="{{ route('admin.leaves.index') }}" class="btn-modern btn-modern-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-check"></i>
                    <span>Create Leave</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* Modern Page Layout */
.modern-page { animation: fadeSlideIn 0.4s ease-out; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.modern-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-page-header-left { flex: 1; }

.modern-page-header-right {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Breadcrumb */
.modern-breadcrumb ol {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0 0 0.5rem;
    gap: 0.5rem;
    font-size: 0.8rem;
    align-items: center;
}

.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Card */
.modern-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

/* Form Section */
.modern-form-section { border-bottom: 1px solid #f0f0f0; }
.modern-form-section:last-of-type { border-bottom: none; }

.modern-form-section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem 2rem 0.75rem;
}

.modern-form-section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.modern-form-section-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-form-section-icon-green { background: #ecfdf5; color: #10b981; }
.modern-form-section-icon-gold { background: #fff7ed; color: #f59e0b; }
.modern-form-section-icon-purple { background: #f5f3ff; color: #7c3aed; }

.modern-form-section-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}

.modern-form-section-desc {
    font-size: 0.82rem;
    color: #9ca3af;
    margin: 0.15rem 0 0;
}

.modern-form-section-body { padding: 1.25rem 2rem 1.75rem; }

/* Form Grid */
.modern-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.modern-form-span-2 { grid-column: span 2; }

/* Form Group */
.modern-form-group { display: flex; flex-direction: column; }

.modern-form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.45rem;
    font-size: 0.88rem;
}

.modern-form-label small {
    font-weight: 400;
    color: #9ca3af;
    font-size: 0.78rem;
}

.modern-required { color: #ef4444; font-weight: 700; }

/* Input */
.modern-input-wrapper { position: relative; }

.modern-input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.85rem;
    pointer-events: none;
    z-index: 1;
}

.modern-input {
    width: 100%;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.7rem 0.9rem 0.7rem 2.5rem;
    font-size: 0.9rem;
    color: #1a1a2e;
    background: #fff;
    transition: all 0.2s;
}

.modern-input:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.modern-input::placeholder { color: #c5c9d2; }

.modern-input.is-invalid {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* Textarea */
.modern-textarea {
    width: 100%;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.7rem 0.9rem;
    font-size: 0.9rem;
    color: #1a1a2e;
    background: #fff;
    transition: all 0.2s;
    resize: vertical;
    font-family: inherit;
}

.modern-textarea:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.modern-textarea::placeholder { color: #c5c9d2; }

.modern-textarea.is-invalid {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* Select */
.modern-select {
    width: 100%;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.7rem 0.9rem 0.7rem 2.5rem;
    font-size: 0.9rem;
    color: #1a1a2e;
    background: #fff;
    transition: all 0.2s;
    appearance: none;
    cursor: pointer;
}

.modern-select:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.modern-select.is-invalid {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.modern-form-error {
    display: block;
    color: #ef4444;
    font-size: 0.8rem;
    margin-top: 0.35rem;
    font-weight: 500;
}

/* Form Actions */
.modern-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem 2rem;
    border-top: 1px solid #f0f0f0;
    background: #fafbfc;
}

/* Modern Button */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.35rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.25s;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
}

.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4);
    color: #fff;
}

.btn-modern-outline {
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
}

.btn-modern-outline:hover {
    border-color: #4361ee;
    color: #4361ee;
    background: #f8f9ff;
}

.btn-modern-ghost {
    background: transparent;
    color: #6b7280;
    padding: 0.65rem 1rem;
}

.btn-modern-ghost:hover {
    color: #1a1a2e;
    background: #f3f4f6;
}

/* Responsive */
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-form-grid { grid-template-columns: 1fr; }
    .modern-form-span-2 { grid-column: span 1; }
    .modern-form-section-body { padding: 1rem 1.25rem 1.5rem; }
    .modern-form-section-header { padding: 1.25rem 1.25rem 0.75rem; }
    .modern-form-actions { padding: 1rem 1.25rem; flex-direction: column; }
    .btn-modern { justify-content: center; width: 100%; }
}
</style>
@endpush
@endsection
