@extends("layouts.admin")
@section("page-title","Create Report")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph me-2"></i>Create Report</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.progress-reports.index') }}" class="text-decoration-none text-muted">Reports</a></li>
            <li class="breadcrumb-item active text-gold">Create</li>
        </ol></nav></div>
        <a href="{{ route('admin.progress-reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-plus-circle me-2"></i>Report Details</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.progress-reports.store') }}">@csrf
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Student *</label><select name="student_id" class="form-select" required><option value="">-- Select --</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Academic Year *</label><select name="academic_year_id" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}">{{ $ay->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Term *</label><select name="term_id" class="form-select" required><option value="">-- Select --</option>@foreach($terms as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Class *</label><select name="class_id" class="form-select" required><option value="">-- Select --</option>@foreach($classrooms as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Total Marks *</label><input type="number" step="0.01" name="total_marks" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Percentage *</label><input type="number" step="0.01" name="percentage" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Grade *</label><select name="grade" class="form-select" required><option value="">-- Select --</option><option value="A+">A+</option><option value="A">A</option><option value="B+">B+</option><option value="B">B</option><option value="C">C</option><option value="D">D</option><option value="F">F</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Rank</label><input type="number" name="rank" class="form-control" min="1"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Remarks</label><textarea name="remarks" class="form-control" rows="2"></textarea></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Teacher Comment</label><textarea name="teacher_comment" class="form-control" rows="2"></textarea></div>
            <div class="col-12 mt-4"><button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Create Report</button><a href="{{ route('admin.progress-reports.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div></form>
    </div></div>
</div>
@endsection