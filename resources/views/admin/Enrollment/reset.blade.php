@extends('layouts.admin')
@section('title', 'Reset Enrollments')
@push('styles')
<style>
.rw-stat-card{border-radius:10px;padding:14px 18px;background:#fff;border:1px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,.04)}
.rw-stat-num{font-size:1.6rem;font-weight:700;line-height:1.1}
.rw-stat-label{font-size:.78rem;color:#6b7280;font-weight:500;margin-top:2px}
.rw-danger-card{border:2px solid #dc2626;border-radius:10px;background:#fef2f2}
.rw-status-pill{padding:3px 10px;border-radius:12px;font-size:.75rem;font-weight:600}
.rw-status-enrolled{background:#dcfce7;color:#15803d}
.rw-status-pending{background:#fef9c3;color:#a16207}
.rw-status-withdrawn{background:#fee2e2;color:#b91c1c}
.rw-status-graduated{background:#dbeafe;color:#1d4ed8}
.rw-status-transferred{background:#f3e8ff;color:#7e22ce}
.btn-reset{background:linear-gradient(135deg,#dc2626,#991b1b);color:#fff;border:none;padding:12px 28px;border-radius:8px;font-weight:600}
.btn-reset:hover{background:linear-gradient(135deg,#b91c1c,#7f1d1d);color:#fff}
</style>
@endpush
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="bi bi-arrow-counterclockwise text-danger me-2"></i>Reset Enrollments</h4>
            <p class="text-muted mb-0">Remove enrollment records for an academic year so you can re-run bulk enrollment. Use when enrollment data is messed up.</p>
        </div>
        <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back to Enrollments</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-info-circle me-2"></i>{{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('admin.enrollments.reset') }}" class="card mb-3">
        <div class="card-header bg-light py-2"><span class="fw-semibold"><i class="bi bi-funnel me-1"></i> Step 1: Select filters & preview</span></div>
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Academic Year <span class="text-danger">*</span></label>
                    <select name="academic_year_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Select --</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ (string)$selectedAyId === (string)$ay->id ? 'selected' : '' }}>{{ $ay->name }}@if($ay->is_current) (current)@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Branch (optional)</label>
                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Branches --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Class (optional)</label>
                    <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Classes --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->branch->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Status (optional)</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Statuses --</option>
                        <option value="enrolled" {{ request('status') === 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="withdrawn" {{ request('status') === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                        <option value="graduated" {{ request('status') === 'graduated' ? 'selected' : '' }}>Graduated</option>
                        <option value="transferred" {{ request('status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                    </select>
                </div>
            </div>
        </div>
    </form>

    {{-- Preview --}}
    @if($selectedAyId)
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="rw-stat-card"><div class="rw-stat-num text-danger">{{ $preview['enrollments'] }}</div><div class="rw-stat-label">Enrollment rows will be DELETED</div></div></div>
        <div class="col-md-4"><div class="rw-stat-card"><div class="rw-stat-num text-secondary">{{ $preview['branches'] }}</div><div class="rw-stat-label">Branches affected</div></div></div>
        <div class="col-md-4"><div class="rw-stat-card">
            <div class="fw-bold mb-1 small">Breakdown by status:</div>
            @foreach($byStatus as $status => $cnt)
                <span class="rw-status-pill rw-status-{{ $status }}">{{ $status }}: {{ $cnt }}</span>
            @endforeach
            @if(empty($byStatus))<span class="text-muted small">No enrollments found.</span>@endif
        </div></div>
    </div>

    {{-- Reset Form --}}
    @if($preview['enrollments'] > 0)
    <form method="POST" action="{{ route('admin.enrollments.process-reset') }}">@csrf
        <input type="hidden" name="academic_year_id" value="{{ $selectedAyId }}">
        @if(request('branch_id'))<input type="hidden" name="branch_id" value="{{ request('branch_id') }}">@endif
        @if(request('class_id'))<input type="hidden" name="class_id" value="{{ request('class_id') }}">@endif
        @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif

        <div class="rw-danger-card p-4 mb-3">
            <h5 class="text-danger fw-bold mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Reset</h5>
            <p class="mb-3">You are about to <strong>permanently delete {{ $preview['enrollments'] }} enrollment record(s)</strong> for the selected academic year and filters.</p>
            <ul class="mb-3">
                <li>This <strong>does NOT delete students</strong>, marks, attendance, or prior-year enrollments.</li>
                <li>Students will simply appear as "not enrolled" for the selected AY.</li>
                <li>You can then re-run <a href="{{ route('admin.enrollments.bulk-enroll') }}" class="alert-link">Bulk Enrollment</a> cleanly.</li>
            </ul>

            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" name="reset_student_class" value="1" id="resetStudentClass">
                <label for="resetStudentClass" class="form-check-label">
                    <strong>Also restore student class/section pointers</strong>
                    <span class="text-muted small d-block">If checked, each affected student's <code>class_id</code>, <code>section_id</code>, and <code>academic_year_id</code> will be restored to their previous-year values. Also re-activates students mistakenly marked as graduated (only those with leave_reason "Graduated from Grade 12").</span>
                </label>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Type <code>RESET</code> to confirm <span class="text-danger">*</span></label>
                <input type="text" name="confirmation" class="form-control" placeholder="Type RESET in capital letters" required autocomplete="off">
                <div class="form-text small text-muted">This is your safety word. Without it, the reset will not proceed.</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn-reset" onclick="return confirm('FINAL CONFIRMATION: Delete {{ $preview['enrollments'] }} enrollment record(s)? This cannot be undone.')">
                    <i class="bi bi-trash-fill me-1"></i> Delete {{ $preview['enrollments'] }} Enrollment Record(s)
                </button>
                <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i> Cancel</a>
            </div>
        </div>
    </form>
    @else
        <div class="card"><div class="card-body text-center py-5">
            <i class="bi bi-check-circle display-1 text-success"></i>
            <h5 class="mt-3 text-success">No enrollment records match these filters</h5>
            <p class="text-muted small mb-0">Nothing to delete. Try removing filters or selecting a different academic year.</p>
        </div></div>
    @endif
    @else
    <div class="card"><div class="card-body text-center py-5">
        <i class="bi bi-arrow-counterclockwise display-1 text-muted"></i>
        <h5 class="mt-3 text-muted">Select an Academic Year to begin</h5>
        <p class="text-muted small mb-0">Pick the year whose enrollment data is messed up, and we'll show you exactly what will be deleted.</p>
    </div></div>
    @endif
</div>
@endsection
