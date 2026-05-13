@extends('layouts.admin')
@section('title', 'Create Subject')
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1 fw-bold">Add Subject</h4></div><a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a></div>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('admin.subjects.store') }}">@csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Subject Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Code</label><input type="text" name="code" class="form-control" placeholder="e.g. MATH101"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Type</label><input type="text" name="type" class="form-control" placeholder="Core, Elective"></div>
                <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
            </div>
            <div class="mt-3"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save</button><a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
    </div></div>
</div>
@endsection