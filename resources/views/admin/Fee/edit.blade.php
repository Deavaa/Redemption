@extends("layouts.admin")
@section("page-title","Edit Fee")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-receipt me-2"></i>Edit Fee</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.fees.index') }}" class="text-decoration-none text-muted">Fees</a></li>
            <li class="breadcrumb-item active text-gold">Edit</li>
        </ol></nav></div>
        <a href="{{ route('admin.fees.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2"></i>Edit: {{ $item->fee_type }}</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.fees.update',$item->id) }}">@csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Fee Type *</label><input type="text" name="fee_type" class="form-control" value="{{ $item->fee_type }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Category *</label><select name="type" class="form-select" required><option value="">-- Select --</option><option value="tuition" {{ $item->type==="tuition"?"selected":"" }}>Tuition</option><option value="lab" {{ $item->type==="lab"?"selected":"" }}>Lab</option><option value="library" {{ $item->type==="library"?"selected":"" }}>Library</option><option value="transport" {{ $item->type==="transport"?"selected":"" }}>Transport</option><option value="sports" {{ $item->type==="sports"?"selected":"" }}>Sports</option><option value="other" {{ $item->type==="other"?"selected":"" }}>Other</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Amount (ETB) *</label><input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ $item->amount }}" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Classroom *</label><select name="class_id" class="form-select" required><option value="">-- Select --</option>@foreach($classrooms as $cls)<option value="{{ $cls->id }}" {{ $item->class_id==$cls->id?"selected":"" }}>{{ $cls->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Academic Year *</label><select name="academic_year_id" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $item->academic_year_id==$ay->id?"selected":"" }}>{{ $ay->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Due Date</label><input type="date" name="due_date" class="form-control" value="{{ $item->due_date }}"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="2">{{ $item->description }}</textarea></div>
            <div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $item->is_active?"checked":"" }}><label class="form-check-label fw-semibold">Active</label></div></div>
            <div class="col-12 mt-4"><button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Update</button><a href="{{ route('admin.fees.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div></form>
    </div></div>
</div>
@endsection