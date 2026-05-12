@extends("layouts.admin")
@section("page-title","Edit Report")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph me-2"></i>Edit Report</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.progress-reports.index') }}" class="text-decoration-none text-muted">Reports</a></li>
            <li class="breadcrumb-item active text-gold">Edit</li>
        </ol></nav></div>
        <a href="{{ route('admin.progress-reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Report</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.progress-reports.update',$item->id) }}">@csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Student *</label><select name="student_id" class="form-select" required><option value="">-- Select --</option>@foreach($students as $s)<option value="{{ $s->id }}" {{ $item->student_id==$s->id?"selected":"" }}>{{ $s->first_name }} {{ $s->last_name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Academic Year *</label><select name="academic_year_id" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $item->academic_year_id==$ay->id?"selected":"" }}>{{ $ay->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Term *</label><select name="term_id" class="form-select" required><option value="">-- Select --</option>@foreach($terms as $t)<option value="{{ $t->id }}" {{ $item->term_id==$t->id?"selected":"" }}>{{ $t->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Class *</label><select name="class_id" class="form-select" required><option value="">-- Select --</option>@foreach($classrooms as $c)<option value="{{ $c->id }}" {{ $item->class_id==$c->id?"selected":"" }}>{{ $c->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Total Marks *</label><input type="number" step="0.01" name="total_marks" class="form-control" value="{{ $item->total_marks }}" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Percentage *</label><input type="number" step="0.01" name="percentage" class="form-control" value="{{ $item->percentage }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Grade *</label><select name="grade" class="form-select" required><option value="">-- Select --</option><option value="A+" {{ $item->grade==="A+"?"selected":"" }}>A+</option><option value="A" {{ $item->grade==="A"?"selected":"" }}>A</option><option value="B+" {{ $item->grade==="B+"?"selected":"" }}>B+</option><option value="B" {{ $item->grade==="B"?"selected":"" }}>B</option><option value="C" {{ $item->grade==="C"?"selected":"" }}>C</option><option value="D" {{ $item->grade==="D"?"selected":"" }}>D</option><option value="F" {{ $item->grade==="F"?"selected":"" }}>F</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Rank</label><input type="number" name="rank" class="form-control" value="{{ $item->rank }}" min="1"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Remarks</label><textarea name="remarks" class="form-control" rows="2">{{ $item->remarks }}</textarea></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Teacher Comment</label><textarea name="teacher_comment" class="form-control" rows="2">{{ $item->teacher_comment }}</textarea></div>
            <div class="col-12 mt-4"><button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Update</button><a href="{{ route('admin.progress-reports.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div></form>
    </div></div>
</div>
@endsection