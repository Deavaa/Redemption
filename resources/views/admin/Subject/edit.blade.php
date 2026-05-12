@extends("layouts.admin")
@section("page-title", "Edit Subject")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Subject - {{ $data->name }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}" class="text-decoration-none text-muted">Subjects</a></li>
                <li class="breadcrumb-item active text-gold">Edit</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to List</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Subject Details</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.subjects.update', ['subject' => $data->id]) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Subject Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ $data->name }}" required>
                    @error("name")<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Subject Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" value="{{ $data->code }}" required>
                    @error("code")<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="theory" {{ $data->type === 'Core' ? 'selected' : '' }}>Theory</option>
                        <option value="practical" {{ $data->type === 'Elective' ? 'selected' : '' }}>Practical</option>
                        <option value="both" {{ $data->type === 'Extra' ? 'selected' : '' }}>Both</option>
                    </select>
                    @error("type")<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ $data->description }}</textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-gold px-4">
                    <i class="bi bi-check-lg me-2"></i>Save Changes
                </button>
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection
