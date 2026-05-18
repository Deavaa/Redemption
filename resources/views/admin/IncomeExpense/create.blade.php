@extends('layouts.admin')
@section('title', 'Add Income/Expense')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.income-expenses.index') }}">Income & Expenses</a></li>
                    <li class="active">Add New</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.income-expenses.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="modern-card">
        <form method="POST" action="{{ route('admin.income-expenses.store') }}">
            @csrf

            {{-- Transaction Details --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Transaction Details</h3>
                        <p class="modern-form-section-desc">Enter the transaction type and amount</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="type">
                                Type <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-arrows-alt-v modern-input-icon"></i>
                                <select name="type" id="type" class="modern-input modern-select {{ $errors->has('type') ? 'is-invalid' : '' }}" required>
                                    <option value="income" {{ old('type', 'income') == 'income' ? 'selected' : '' }}>Income</option>
                                    <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                                </select>
                            </div>
                            @error('type')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="category">
                                Category <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-tag modern-input-icon"></i>
                                <input type="text"
                                    name="category"
                                    id="category"
                                    class="modern-input {{ $errors->has('category') ? 'is-invalid' : '' }}"
                                    value="{{ old('category') }}"
                                    placeholder="e.g. Tuition Fees, Rent, Utilities"
                                    required>
                            </div>
                            @error('category')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="amount">
                                Amount <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-dollar-sign modern-input-icon"></i>
                                <input type="number"
                                    name="amount"
                                    id="amount"
                                    step="0.01"
                                    min="0"
                                    class="modern-input {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                                    value="{{ old('amount') }}"
                                    placeholder="e.g. 50000.00"
                                    required>
                            </div>
                            @error('amount')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="date">
                                Date <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar modern-input-icon"></i>
                                <input type="date"
                                    name="date"
                                    id="date"
                                    class="modern-input {{ $errors->has('date') ? 'is-invalid' : '' }}"
                                    value="{{ old('date', date('Y-m-d')) }}"
                                    required>
                            </div>
                            @error('date')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="academic_year_id">
                                Academic Year <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar-alt modern-input-icon"></i>
                                <select name="academic_year_id" id="academic_year_id" class="modern-input modern-select {{ $errors->has('academic_year_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Academic Year --</option>
                                    @foreach(\App\Models\AcademicYear::orderBy('name', 'desc')->get() as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('academic_year_id')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="branch_id">
                                Branch <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-map-marker-alt modern-input-icon"></i>
                                <select name="branch_id" id="branch_id" class="modern-input modern-select {{ $errors->has('branch_id') ? 'is-invalid' : '' }}">
                                    <option value="">-- All Branches --</option>
                                    @foreach(\App\Models\Branch::orderBy('name')->get() as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('branch_id')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Additional Info --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Additional Information</h3>
                        <p class="modern-form-section-desc">Reference and description details</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="reference">
                                Reference <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-hashtag modern-input-icon"></i>
                                <input type="text"
                                    name="reference"
                                    id="reference"
                                    class="modern-input {{ $errors->has('reference') ? 'is-invalid' : '' }}"
                                    value="{{ old('reference') }}"
                                    placeholder="e.g. INV-2024-001">
                            </div>
                            @error('reference')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="description">
                                Description <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-align-left modern-input-icon"></i>
                                <textarea
                                    name="description"
                                    id="description"
                                    class="modern-input modern-textarea {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    placeholder="Additional details about this transaction"
                                    rows="3">{{ old('description') }}</textarea>
                            </div>
                            @error('description')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions">
                <a href="{{ route('admin.income-expenses.index') }}" class="btn-modern btn-modern-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-check"></i>
                    <span>Create Record</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* Modern Page Layout */
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.modern-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.modern-page-header-left { flex: 1; }
.modern-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

.modern-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden; }

.modern-form-section { border-bottom: 1px solid #f0f0f0; }
.modern-form-section:last-of-type { border-bottom: none; }
.modern-form-section-header { display: flex; align-items: center; gap: 1rem; padding: 1.5rem 2rem 0.75rem; }
.modern-form-section-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.modern-form-section-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-form-section-icon-green { background: #ecfdf5; color: #10b981; }
.modern-form-section-title { font-size: 1.05rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-form-section-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.15rem 0 0; }
.modern-form-section-body { padding: 1.25rem 2rem 1.75rem; }

.modern-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
.modern-form-span-2 { grid-column: span 2; }
.modern-form-group { display: flex; flex-direction: column; }
.modern-form-label { font-weight: 600; color: #374151; margin-bottom: 0.45rem; font-size: 0.88rem; }
.modern-form-label small { font-weight: 400; color: #9ca3af; font-size: 0.78rem; }
.modern-required { color: #ef4444; font-weight: 700; }

.modern-input-wrapper { position: relative; }
.modern-input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.85rem; pointer-events: none; z-index: 1; }
.modern-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.7rem 0.9rem 0.7rem 2.5rem; font-size: 0.9rem; color: #1a1a2e; background: #fff; transition: all 0.2s; }
.modern-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1); }
.modern-input::placeholder { color: #c5c9d2; }
.modern-input.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1); }
.modern-textarea { resize: vertical; min-height: 80px; }
.modern-select { appearance: none; cursor: pointer; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.25rem; padding-right: 2.5rem; }
.modern-form-error { display: block; color: #ef4444; font-size: 0.8rem; margin-top: 0.35rem; font-weight: 500; }

.modern-form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.5rem 2rem; border-top: 1px solid #f0f0f0; background: #fafbfc; }

.btn-modern { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.btn-modern-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3); }
.btn-modern-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4); color: #fff; }
.btn-modern-outline { background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb; }
.btn-modern-outline:hover { border-color: #4361ee; color: #4361ee; background: #f8f9ff; }
.btn-modern-ghost { background: transparent; color: #6b7280; padding: 0.65rem 1rem; }
.btn-modern-ghost:hover { color: #1a1a2e; background: #f3f4f6; }

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
