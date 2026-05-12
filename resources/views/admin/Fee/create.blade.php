@extends("layouts.admin")
@section("page-title","Add Fee")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-receipt me-2"></i>Add Fee</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.fees.index') }}" class="text-decoration-none text-muted">Fees</a></li>
            <li class="breadcrumb-item active text-gold">Add</li>
        </ol></nav></div>
        <a href="{{ route('admin.fees.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-plus-circle me-2"></i>Fee Details</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.fees.store') }}">@csrf
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Fee Type *</label><input type="text" name="fee_type" class="form-control" value="{{ old('fee_type') }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Category *</label><select name="type" class="form-select" required><option value="">-- Select --</option><option value="tuition">Tuition</option><option value="lab">Lab</option><option value="library">Library</option><option value="transport">Transport</option><option value="sports">Sports</option><option value="other">Other</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Amount (ETB) *</label><input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ old('amount') }}" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Classroom *</label><select name="class_id" class="form-select" required><option value="">-- Select --</option>@foreach($classrooms as $cls)<option value="{{ $cls->id }}">{{ $cls->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Academic Year *</label><select name="academic_year_id" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}">{{ $ay->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Due Date</label><input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea></div>
            <div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label fw-semibold">Active</label></div></div>
            <div class="col-12 mt-4"><button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Save Fee</button><a href="{{ route('admin.fees.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div></form>
    </div></div>
</div>
@endsection