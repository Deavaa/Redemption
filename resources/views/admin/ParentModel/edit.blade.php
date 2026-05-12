@extends("layouts.admin")
@section("page-title","Edit Parent")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-people me-2"></i>Edit Parent</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.parents.index') }}" class="text-decoration-none text-muted">Parents</a></li>
            <li class="breadcrumb-item active text-gold">Edit</li>
        </ol></nav></div>
        <a href="{{ route('admin.parents.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2"></i>Edit: {{ $item->father_name }}</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.parents.update',$item->id) }}">@csrf @method('PUT')
        <div class="row g-3">
            <div class="col-12"><h6 class="text-primary fw-bold"><i class="bi bi-person me-1"></i>Father Details</h6><hr class="mt-0"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Father Name *</label><input type="text" name="father_name" class="form-control" value="{{ $item->father_name }}" required></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Father Phone *</label><input type="text" name="father_phone" class="form-control" value="{{ $item->father_phone }}" required></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Father Occupation</label><input type="text" name="father_occupation" class="form-control" value="{{ $item->father_occupation }}"></div>
            <div class="col-12"><h6 class="text-success fw-bold mt-2"><i class="bi bi-person me-1"></i>Mother Details</h6><hr class="mt-0"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Mother Name</label><input type="text" name="mother_name" class="form-control" value="{{ $item->mother_name }}"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Mother Phone</label><input type="text" name="mother_phone" class="form-control" value="{{ $item->mother_phone }}"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Mother Occupation</label><input type="text" name="mother_occupation" class="form-control" value="{{ $item->mother_occupation }}"></div>
            <div class="col-12"><h6 class="text-info fw-bold mt-2"><i class="bi bi-shield-check me-1"></i>Guardian Details</h6><hr class="mt-0"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Guardian Name</label><input type="text" name="guardian_name" class="form-control" value="{{ $item->guardian_name }}"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Guardian Relation</label><input type="text" name="guardian_relation" class="form-control" value="{{ $item->guardian_relation }}"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Guardian Phone</label><input type="text" name="guardian_phone" class="form-control" value="{{ $item->guardian_phone }}"></div>
            <div class="col-12 mt-4"><button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Update</button><a href="{{ route('admin.parents.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div></form>
    </div></div>
</div>
@endsection