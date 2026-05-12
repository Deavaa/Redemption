@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-plus-circle me-2"></i>Add Term</h2>
                <a href="{{ route('admin.terms.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.terms.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Term Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" required 
                                       class="form-control" placeholder="e.g. Term 1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                                <select name="academic_year_id" required class="form-select">
                                    <option value="">-- Select Academic Year --</option>
                                    @foreach($academicYears as $ay)
                                    <option value="{{ $ay->id }}" {{ old('academic_year_id')==$ay->id?'selected':'' }}>
                                        {{ $ay->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required 
                                       class="form-control" onchange="updateEthiopianDate('start')">
                                <div id="ethiopian_start_date" class="mt-2" style="display:none;">
                                    <small class="text-primary">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Ethiopian: <strong id="ethiopian_start_text"></strong>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required 
                                       class="form-control" onchange="updateEthiopianDate('end')">
                                <div id="ethiopian_end_date" class="mt-2" style="display:none;">
                                    <small class="text-primary">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Ethiopian: <strong id="ethiopian_end_text"></strong>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="is_active" required class="form-select">
                                    <option value="1" {{ old('is_active','1')=='1'?'selected':'' }}>Active</option>
                                    <option value="0" {{ old('is_active')=='0'?'selected':'' }}>Inactive</option>
                                </select>
                                <small class="text-muted">Set whether this term is currently active</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Term
                            </button>
                            <a href="{{ route('admin.terms.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Ethiopian month names
const ethiopianMonths = [
    'Meskerem', 'Tikimt', 'Hidar', 'Tahsas', 
    'Tir', 'Yekatit', 'Megabit', 'Miyazia', 
    'Ginbot', 'Sene', 'Hamle', 'Nehase', 'Pagume'
];

// Days in each Ethiopian month
const ethiopianDaysInMonth = [30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 5];

function gregorianToEthiopian(dateStr) {
    const date = new Date(dateStr + 'T12:00:00'); // Add time to avoid timezone issues
    let gYear = date.getFullYear();
    let gMonth = date.getMonth() + 1; // 1-12
    let gDay = date.getDate();
    
    // Calculate Ethiopian year
    let ethYear = gYear - 8;
    
    // Ethiopian New Year is on Meskerem 1, which corresponds to:
    // September 11 in Gregorian (or September 12 in the year before Gregorian leap year)
    const isGregorianLeapYearEve = ((gYear % 4) === 3); // Year before leap year (2023, 2027, etc.)
    const ethNewYearDay = isGregorianLeapYearEve ? 12 : 11;
    
    // Check if date is before Ethiopian New Year
    if (gMonth < 9 || (gMonth === 9 && gDay < ethNewYearDay)) {
        ethYear = ethYear - 1;
    }
    
    // Calculate day of year for the given date
    const isGregorianLeap = (gYear % 4 === 0);
    const gregorianDaysInMonth = [0, 31, (isGregorianLeap ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    
    let dayOfYear = gDay;
    for (let m = 1; m < gMonth; m++) {
        dayOfYear += gregorianDaysInMonth[m];
    }
    
    // Calculate day of year for Ethiopian New Year
    let newYearDayOfYear = ethNewYearDay;
    for (let m = 1; m < 9; m++) {
        newYearDayOfYear += gregorianDaysInMonth[m];
    }
    
    // Days since Ethiopian New Year
    let daysSinceNewYear = dayOfYear - newYearDayOfYear;
    
    if (daysSinceNewYear < 0) {
        // Date is before Ethiopian New Year
        const prevYearDays = isGregorianLeapYearEve ? 366 : 365;
        daysSinceNewYear = prevYearDays + daysSinceNewYear;
    }
    
    // Convert to Ethiopian month and day
    let ethMonth = 1;
    let ethDay = daysSinceNewYear + 1;
    
    // Adjust for Ethiopian leap year (Pagume has 6 days every 4 years)
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

// Update Ethiopian dates on page load if values exist
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('start_date').value) {
        updateEthiopianDate('start');
    }
    if (document.getElementById('end_date').value) {
        updateEthiopianDate('end');
    }
});
</script>
@endsection