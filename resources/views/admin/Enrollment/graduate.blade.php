@extends('layouts.admin')
@section('title', 'Graduate Grade 12 Students')
@push('styles')
<style>
.gw-stat-card{border-radius:10px;padding:14px 18px;background:#fff;border:1px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,.04)}
.gw-stat-num{font-size:1.6rem;font-weight:700;line-height:1.1}
.gw-stat-label{font-size:.78rem;color:#6b7280;font-weight:500;margin-top:2px}
.gw-student-row{padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;transition:all .15s}
.gw-student-row:hover{border-color:#3b82f6;background:#f0f7ff}
.gw-student-row.selected{border-color:#3b82f6;background:#eff6ff}
.gw-student-row.graduated{opacity:.55;background:#f3f4f6}
.gw-badge-grad{background:#10b981;color:#fff;padding:2px 8px;border-radius:10px;font-size:.7rem;font-weight:600}
.gw-badge-active{background:#3b82f6;color:#fff;padding:2px 8px;border-radius:10px;font-size:.7rem;font-weight:600}
.btn-graduate{background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;border:none;padding:10px 22px;border-radius:8px;font-weight:600}
.btn-graduate:hover{background:linear-gradient(135deg,#6d28d9,#4338ca);color:#fff}
</style>
@endpush
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="bi bi-mortarboard-fill text-primary me-2"></i>Graduate Grade 12 Students</h4>
            <p class="text-muted mb-0">Mark Grade 12 students as graduated. Auto-generates a Grades 9-12 transcript for each.</p>
        </div>
        <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back to Enrollments</a>
    </div>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('admin.enrollments.graduate') }}" class="card mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Academic Year</label>
                    <select name="academic_year_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Select Academic Year --</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ (string)$selectedAyId === (string)$ay->id ? 'selected' : '' }}>{{ $ay->name }}@if($ay->is_current) (current)@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        @if($grade12Classes->isEmpty())
                            <span class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i>No Grade 12 classes found (numeric_name = 12).</span>
                        @else
                            {{ $grade12Classes->count() }} Grade 12 class(es): {{ $grade12Classes->pluck('name')->implode(', ') }}
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </form>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-info-circle me-2"></i>{{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="gw-stat-card"><div class="gw-stat-num text-secondary">{{ $stats['total'] }}</div><div class="gw-stat-label">Total Grade 12 Students</div></div></div>
        <div class="col-md-3"><div class="gw-stat-card"><div class="gw-stat-num text-success">{{ $stats['already_graduated'] }}</div><div class="gw-stat-label">Already Graduated</div></div></div>
        <div class="col-md-3"><div class="gw-stat-card"><div class="gw-stat-num text-primary">{{ $stats['eligible'] }}</div><div class="gw-stat-label">Eligible to Graduate</div></div></div>
        <div class="col-md-3"><div class="gw-stat-card"><div class="gw-stat-num text-info">{{ $grade12Classes->count() }}</div><div class="gw-stat-label">Grade 12 Classes</div></div></div>
    </div>

    @if($students->isEmpty())
        <div class="card"><div class="card-body text-center py-5">
            <i class="bi bi-mortarboard display-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No Grade 12 students found</h5>
            <p class="text-muted small mb-0">Make sure a class with <code>numeric_name = 12</code> exists and has active students enrolled for the selected academic year.</p>
        </div></div>
    @else
    <form method="POST" action="{{ route('admin.enrollments.process-graduate') }}">@csrf
        <input type="hidden" name="academic_year_id" value="{{ $selectedAyId }}">
        <input type="hidden" name="mode" id="modeInput" value="selected">

        <div class="card mb-3">
            <div class="card-header bg-light py-2 d-flex align-items-center">
                <span class="fw-semibold"><i class="bi bi-people me-1"></i> Grade 12 Students ({{ $students->count() }})</span>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn"><i class="bi bi-check2-all me-1"></i>Select All Eligible</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn"><i class="bi bi-x-circle me-1"></i>Deselect All</button>
                </div>
            </div>
            <div class="card-body p-2">
                <div class="row g-2">
                    @foreach($students as $row)
                        @php $s = $row->student; @endphp
                        <div class="col-md-6 col-xl-4">
                            <div class="gw-student-row {{ $row->already_graduated ? 'graduated' : '' }}" data-student-id="{{ $s->id }}">
                                <div class="d-flex align-items-start gap-2">
                                    <input type="checkbox" class="form-check-input mt-1 student-check" name="student_ids[]" value="{{ $s->id }}" id="stu_{{ $s->id }}" {{ $row->already_graduated ? 'disabled' : '' }}>
                                    <label for="stu_{{ $s->id }}" class="flex-grow-1 cursor-pointer user-select-none">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="fw-semibold">{{ $s->full_name }}</span>
                                            @if($row->already_graduated)<span class="gw-badge-grad">Graduated</span>@else<span class="gw-badge-active">Active</span>@endif
                                        </div>
                                        <div class="text-muted small mt-1">
                                            <i class="bi bi-person-badge me-1"></i>{{ $s->admission_number ?? '-' }}
                                            @if($s->roll_number) <span class="ms-2"><i class="bi bi-hash"></i>{{ $s->roll_number }}</span>@endif
                                        </div>
                                        <div class="text-muted small">
                                            <i class="bi bi-mortarboard me-1"></i>{{ $row->class_name }}
                                            @if($row->section_name && $row->section_name !== '-') <span class="ms-1">/ {{ $row->section_name }}</span>@endif
                                            @if($row->branch_name && $row->branch_name !== '-') <span class="ms-2 text-muted"><i class="bi bi-building"></i> {{ $row->branch_name }}</span>@endif
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer bg-light py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <span class="fw-semibold text-primary" id="selectedCount">0</span> <span class="text-muted">student(s) selected for graduation</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="if(confirm('Graduate ALL eligible Grade 12 students?')){document.getElementById('modeInput').value='all';document.getElementById('gradForm').submit();}">
                            <i class="bi bi-stars me-1"></i> Graduate ALL Eligible ({{ $stats['eligible'] }})
                        </button>
                        <button type="submit" class="btn-graduate" id="graduateSelectedBtn" disabled>
                            <i class="bi bi-mortarboard-fill me-1"></i> Graduate Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endif

    {{-- Hidden form for "Graduate All" mode --}}
    <form id="gradForm" method="POST" action="{{ route('admin.enrollments.process-graduate') }}">@csrf
        <input type="hidden" name="academic_year_id" value="{{ $selectedAyId }}">
        <input type="hidden" name="mode" value="all">
    </form>
</div>
@endsection
@push('scripts')
<script>
const checks = document.querySelectorAll('.student-check');
const countEl = document.getElementById('selectedCount');
const gradBtn = document.getElementById('graduateSelectedBtn');

checks.forEach(cb => {
    cb.addEventListener('change', () => {
        const item = cb.closest('.gw-student-row');
        if (cb.checked) item.classList.add('selected');
        else item.classList.remove('selected');
        updateCount();
    });
});

function updateCount() {
    const checked = document.querySelectorAll('.student-check:checked');
    countEl.textContent = checked.length;
    gradBtn.disabled = checked.length === 0;
}

document.getElementById('selectAllBtn').addEventListener('click', () => {
    checks.forEach(cb => { if (!cb.disabled) { cb.checked = true; cb.closest('.gw-student-row').classList.add('selected'); } });
    updateCount();
});
document.getElementById('deselectAllBtn').addEventListener('click', () => {
    checks.forEach(cb => { cb.checked = false; cb.closest('.gw-student-row').classList.remove('selected'); });
    updateCount();
});

updateCount();
</script>
@endpush
