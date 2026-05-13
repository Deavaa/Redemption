@extends('layouts.admin')
@section('title', 'Edit Payroll')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.payrolls.index') }}">Payroll</a></li>
                    <li class="active">Edit</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Edit Payroll</h1>
            <p class="modern-page-subtitle">Update payroll for <strong>{{ $item->employee->name ?? 'Employee' }}</strong></p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.payrolls.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="modern-card">
        <form method="POST" action="{{ route('admin.payrolls.update', $item->id) }}">
            @csrf @method('PUT')

            {{-- Employee & Period --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Employee & Period</h3>
                        <p class="modern-form-section-desc">Update the employee and payroll period</p>
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
                                <select name="employee_id" id="employee_id" class="modern-input modern-select {{ $errors->has('employee_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Employee --</option>
                                    @foreach(\App\Models\User::where('role', 'teacher')->orWhere('role', 'staff')->orderBy('name')->get() as $emp)
                                        <option value="{{ $emp->id }}" {{ old('employee_id', $item->employee_id) == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('employee_id')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="pay_period">
                                Pay Period <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar-alt modern-input-icon"></i>
                                <input type="text"
                                    name="pay_period"
                                    id="pay_period"
                                    class="modern-input {{ $errors->has('pay_period') ? 'is-invalid' : '' }}"
                                    value="{{ old('pay_period', $item->pay_period) }}"
                                    placeholder="e.g. January 2024"
                                    required>
                            </div>
                            @error('pay_period')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="payment_date">
                                Payment Date <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar-check modern-input-icon"></i>
                                <input type="date"
                                    name="payment_date"
                                    id="payment_date"
                                    class="modern-input {{ $errors->has('payment_date') ? 'is-invalid' : '' }}"
                                    value="{{ old('payment_date', $item->payment_date ? $item->payment_date->format('Y-m-d') : '') }}"
                                    required>
                            </div>
                            @error('payment_date')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="status">
                                Status <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-flag modern-input-icon"></i>
                                <select name="status" id="status" class="modern-input modern-select {{ $errors->has('status') ? 'is-invalid' : '' }}" required>
                                    <option value="pending" {{ old('status', $item->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processed" {{ old('status', $item->status) == 'processed' ? 'selected' : '' }}>Processed</option>
                                    <option value="paid" {{ old('status', $item->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </div>
                            @error('status')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Salary Breakdown --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Salary Breakdown</h3>
                        <p class="modern-form-section-desc">Update salary components</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="basic_salary">
                                Basic Salary <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-dollar-sign modern-input-icon"></i>
                                <input type="number"
                                    name="basic_salary"
                                    id="basic_salary"
                                    step="0.01"
                                    min="0"
                                    class="modern-input {{ $errors->has('basic_salary') ? 'is-invalid' : '' }}"
                                    value="{{ old('basic_salary', $item->basic_salary) }}"
                                    placeholder="e.g. 10000.00"
                                    required>
                            </div>
                            @error('basic_salary')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="allowances">
                                Allowances <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-plus-circle modern-input-icon"></i>
                                <input type="number"
                                    name="allowances"
                                    id="allowances"
                                    step="0.01"
                                    min="0"
                                    class="modern-input {{ $errors->has('allowances') ? 'is-invalid' : '' }}"
                                    value="{{ old('allowances', $item->allowances) }}"
                                    placeholder="0.00">
                            </div>
                            @error('allowances')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="deductions">
                                Deductions <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-minus-circle modern-input-icon"></i>
                                <input type="number"
                                    name="deductions"
                                    id="deductions"
                                    step="0.01"
                                    min="0"
                                    class="modern-input {{ $errors->has('deductions') ? 'is-invalid' : '' }}"
                                    value="{{ old('deductions', $item->deductions) }}"
                                    placeholder="0.00">
                            </div>
                            @error('deductions')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="tax">
                                Tax <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-percentage modern-input-icon"></i>
                                <input type="number"
                                    name="tax"
                                    id="tax"
                                    step="0.01"
                                    min="0"
                                    class="modern-input {{ $errors->has('tax') ? 'is-invalid' : '' }}"
                                    value="{{ old('tax', $item->tax) }}"
                                    placeholder="0.00">
                            </div>
                            @error('tax')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="net_salary">
                                Net Salary <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-wallet modern-input-icon"></i>
                                <input type="number"
                                    name="net_salary"
                                    id="net_salary"
                                    step="0.01"
                                    min="0"
                                    class="modern-input {{ $errors->has('net_salary') ? 'is-invalid' : '' }}"
                                    value="{{ old('net_salary', $item->net_salary) }}"
                                    placeholder="Auto-calculated"
                                    required>
                            </div>
                            @error('net_salary')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions">
                <a href="{{ route('admin.payrolls.index') }}" class="btn-modern btn-modern-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-save"></i>
                    <span>Save Changes</span>
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

.modern-page-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0;
    letter-spacing: -0.5px;
}

.modern-page-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0.25rem 0 0;
}

.modern-page-subtitle strong { color: #4361ee; }

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

.modern-select {
    appearance: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.25rem;
    padding-right: 2.5rem;
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
    .modern-page-title { font-size: 1.35rem; }
    .modern-form-grid { grid-template-columns: 1fr; }
    .modern-form-span-2 { grid-column: span 1; }
    .modern-form-section-body { padding: 1rem 1.25rem 1.5rem; }
    .modern-form-section-header { padding: 1.25rem 1.25rem 0.75rem; }
    .modern-form-actions { padding: 1rem 1.25rem; flex-direction: column; }
    .btn-modern { justify-content: center; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const basicSalary = document.getElementById('basic_salary');
    const allowances = document.getElementById('allowances');
    const deductions = document.getElementById('deductions');
    const tax = document.getElementById('tax');
    const netSalary = document.getElementById('net_salary');

    function calculateNet() {
        const basic = parseFloat(basicSalary.value) || 0;
        const allow = parseFloat(allowances.value) || 0;
        const deduct = parseFloat(deductions.value) || 0;
        const taxVal = parseFloat(tax.value) || 0;
        netSalary.value = (basic + allow - deduct - taxVal).toFixed(2);
    }

    [basicSalary, allowances, deductions, tax].forEach(el => {
        el.addEventListener('input', calculateNet);
    });
});
</script>
@endpush
@endsection
