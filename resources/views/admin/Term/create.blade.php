@extends('layouts.admin')
@section('title', 'Add Term')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.terms.index') }}">Terms</a></li>
                    <li class="active">Add New</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Add New Term</h1>
            <p class="modern-page-subtitle">Create a new academic term or semester</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.terms.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="modern-card">
        <form method="POST" action="{{ route('admin.terms.store') }}">
            @csrf

            {{-- Term Details --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Term Details</h3>
                        <p class="modern-form-section-desc">Enter the term name, academic year, and date range</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="name">
                                Term Name <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-font modern-input-icon"></i>
                                <input type="text"
                                    name="name"
                                    id="name"
                                    class="modern-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    value="{{ old('name') }}"
                                    placeholder="e.g. Term 1"
                                    required
                                    autofocus>
                            </div>
                            @error('name')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="academic_year_id">
                                Academic Year <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-graduation-cap modern-input-icon"></i>
                                <select name="academic_year_id" id="academic_year_id" class="modern-input modern-select {{ $errors->has('academic_year_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Academic Year --</option>
                                    @foreach($academicYears as $ay)
                                        <option value="{{ $ay->id }}" {{ old('academic_year_id') == $ay->id ? 'selected' : '' }}>
                                            {{ $ay->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('academic_year_id')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="start_date">
                                Start Date <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar-alt modern-input-icon"></i>
                                <input type="date"
                                    name="start_date"
                                    id="start_date"
                                    class="modern-input {{ $errors->has('start_date') ? 'is-invalid' : '' }}"
                                    value="{{ old('start_date') }}"
                                    onchange="updateEthiopianDate('start')"
                                    required>
                            </div>
                            <div id="ethiopian_start_date" class="modern-eth-hint" style="display:none;">
                                <i class="fas fa-calendar-alt"></i>
                                Ethiopian: <strong id="ethiopian_start_text"></strong>
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
                                <i class="fas fa-calendar-alt modern-input-icon"></i>
                                <input type="date"
                                    name="end_date"
                                    id="end_date"
                                    class="modern-input {{ $errors->has('end_date') ? 'is-invalid' : '' }}"
                                    value="{{ old('end_date') }}"
                                    onchange="updateEthiopianDate('end')"
                                    required>
                            </div>
                            <div id="ethiopian_end_date" class="modern-eth-hint" style="display:none;">
                                <i class="fas fa-calendar-alt"></i>
                                Ethiopian: <strong id="ethiopian_end_text"></strong>
                            </div>
                            @error('end_date')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group modern-form-span-2">
                            <div class="modern-toggle-wrapper">
                                <label class="modern-toggle">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                    <span class="modern-toggle-slider"></span>
                                </label>
                                <div class="modern-toggle-info">
                                    <span class="modern-toggle-title">Active Status</span>
                                    <span class="modern-toggle-desc">Set whether this term is currently active (only one term can be active at a time)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions">
                <a href="{{ route('admin.terms.index') }}" class="btn-modern btn-modern-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-check"></i>
                    <span>Create Term</span>
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

/* Ethiopian Date Hint */
.modern-eth-hint {
    margin-top: 0.4rem;
    padding: 0.4rem 0.65rem;
    background: #f8f4e8;
    border-left: 3px solid #d97706;
    border-radius: 6px;
    font-size: 0.8rem;
    color: #92400e;
}

.modern-eth-hint i { color: #d97706; margin-right: 0.25rem; }

/* Toggle */
.modern-toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding-top: 0.5rem;
}

.modern-toggle {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
}

.modern-toggle input { opacity: 0; width: 0; height: 0; }

.modern-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background: #d1d5db;
    border-radius: 50px;
    transition: 0.3s;
}

.modern-toggle-slider::before {
    content: '';
    position: absolute;
    height: 20px; width: 20px;
    left: 3px; bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.3s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}

.modern-toggle input:checked + .modern-toggle-slider { background: #4361ee; }
.modern-toggle input:checked + .modern-toggle-slider::before { transform: translateX(22px); }

.modern-toggle-info { display: flex; flex-direction: column; }
.modern-toggle-title { font-weight: 600; color: #374151; font-size: 0.88rem; }
.modern-toggle-desc { font-size: 0.78rem; color: #9ca3af; }

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
// Ethiopian date conversion
const ethiopianMonths = [
    'Meskerem', 'Tikimt', 'Hidar', 'Tahsas',
    'Tir', 'Yekatit', 'Megabit', 'Miyazia',
    'Ginbot', 'Sene', 'Hamle', 'Nehase', 'Pagume'
];

function gregorianToEthiopian(dateStr) {
    const date = new Date(dateStr + 'T12:00:00');
    let gYear = date.getFullYear();
    let gMonth = date.getMonth() + 1;
    let gDay = date.getDate();

    let ethYear = gYear - 8;
    const isGregorianLeapYearEve = ((gYear % 4) === 3);
    const ethNewYearDay = isGregorianLeapYearEve ? 12 : 11;

    if (gMonth < 9 || (gMonth === 9 && gDay < ethNewYearDay)) {
        ethYear = ethYear - 1;
    }

    const isGregorianLeap = (gYear % 4 === 0);
    const gregorianDaysInMonth = [0, 31, (isGregorianLeap ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    let dayOfYear = gDay;
    for (let m = 1; m < gMonth; m++) {
        dayOfYear += gregorianDaysInMonth[m];
    }

    let newYearDayOfYear = ethNewYearDay;
    for (let m = 1; m < 9; m++) {
        newYearDayOfYear += gregorianDaysInMonth[m];
    }

    let daysSinceNewYear = dayOfYear - newYearDayOfYear;
    if (daysSinceNewYear < 0) {
        const prevYearDays = isGregorianLeapYearEve ? 366 : 365;
        daysSinceNewYear = prevYearDays + daysSinceNewYear;
    }

    let ethMonth = 1;
    let ethDay = daysSinceNewYear + 1;

    const isEthiopianLeapYear = ((ethYear + 1) % 4) === 0;
    const daysInPagume = isEthiopianLeapYear ? 6 : 5;

    while (ethMonth <= 12 && ethDay > (ethMonth === 13 ? daysInPagume : 30)) {
        ethDay -= (ethMonth === 13 ? daysInPagume : 30);
        ethMonth++;
    }

    return {
        year: ethYear,
        month: ethMonth,
        day: ethDay,
        monthName: ethiopianMonths[ethMonth - 1],
        formatted: ethDay + ' ' + ethiopianMonths[ethMonth - 1] + ', ' + ethYear
    };
}

function updateEthiopianDate(type) {
    const dateInput = document.getElementById(type + '_date');
    const ethiopianDiv = document.getElementById('ethiopian_' + type + '_date');
    const ethiopianText = document.getElementById('ethiopian_' + type + '_text');

    if (dateInput.value) {
        const ethiopian = gregorianToEthiopian(dateInput.value);
        ethiopianText.textContent = ethiopian.formatted;
        ethiopianDiv.style.display = 'block';
    } else {
        ethiopianDiv.style.display = 'none';
    }
}
</script>
@endpush
@endsection
