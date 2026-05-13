@extends('layouts.admin')
@section('title', 'Edit Subject')
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1 fw-bold">Edit Subject</h4></div><a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a></div>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">@csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Subject Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $subject->name) }}" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Code</label><input type="text" name="code" class="form-control" value="{{ old('code', $subject->code) }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Type</label><input type="text" name="type" class="form-control" value="{{ old('type', $subject->type) }}"></div>
                <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $subject->description) }}</textarea></div>
            </div>
            <div class="mt-3"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update</button><a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
    </div></div>
</div>
@endsection