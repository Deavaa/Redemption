@extends("layouts.admin")
@section("page-title","New TeacherAssignment")
@section("content")
<div class="container-fluid py-4"><div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h4 class="fw-bold mb-1"><i class="bi bi-plus-circle me-2"></i>New TeacherAssignment</h4><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}" class="text-decoration-none text-muted">Dashboard</a></li><li class="breadcrumb-item"><a href="{{route('admin.teacher-assignments.index')}}" class="text-decoration-none text-muted">TeacherAssignments</a></li><li class="breadcrumb-item active text-gold">New</li></ol></nav></div></div>
<form method="POST" action="{{route('admin.teacher-assignments.store')}}">@csrf
<div class="card border-0 shadow-sm"><div class="card-header bg-white py-3" style="border-top:3px solid #c9a84c;"><h5 class="mb-0 fw-semibold">Details</h5></div><div class="card-body"><div class="row g-3"><div class="col-md-6"><label class="form-label fw-medium">Teacher id</label><input type="text" name="teacher_id" class="form-control">@error('teacher_id')<div class="text-danger small mt-1">{{$message}}</div>@enderror</div>
<div class="col-md-6"><label class="form-label fw-medium">Classroom id</label><input type="text" name="classroom_id" class="form-control">@error('classroom_id')<div class="text-danger small mt-1">{{$message}}</div>@enderror</div>
<div class="col-md-6"><label class="form-label fw-medium">Subject id</label><input type="text" name="subject_id" class="form-control">@error('subject_id')<div class="text-danger small mt-1">{{$message}}</div>@enderror</div>
<div class="col-md-12"><label class="form-label fw-medium">Academic year id</label><input type="text" name="academic_year_id" class="form-control">@error('academic_year_id')<div class="text-danger small mt-1">{{$message}}</div>@enderror</div>
</div></div></div>
<div class="my-4"><button type="submit" class="btn btn-gold me-2"><i class="bi bi-check-lg me-1"></i>Save</button><a href="{{route('admin.teacher-assignments.index')}}" class="btn btn-outline-secondary">Cancel</a></div></form></div>
@endsection