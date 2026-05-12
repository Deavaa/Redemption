@extends("layouts.admin")
@section("page-title", "Edit Asset")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-box-seam me-2"></i>Edit Asset</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.class-assets.index') }}" class="text-decoration-none text-muted">Class Assets</a></li>
                <li class="breadcrumb-item active text-gold">Edit</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.class-assets.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2"></i>Edit: {{ $item->name }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.class-assets.update', $item->id) }}">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Classroom <span class="text-danger">*</span></label>
                                <select name="class_id" id="classSelect" class="form-select @error('class_id') is-invalid @enderror" required>
                                    <option value="">-- Select --</option>
                                    @foreach($classrooms as $cls)
                                    <option value="{{ $cls->id }}" {{ old('class_id', $item->class_id) == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                                    @endforeach
                                </select>
                                @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Section</label>
                                <select name="section_id" id="sectionSelect" class="form-select @error('section_id') is-invalid @enderror">
                                    <option value="">-- All Sections --</option>
                                </select>
                                @error('section_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', $item->quantity) }}" min="1" required>
                                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Condition <span class="text-danger">*</span></label>
                                <select name="condition" class="form-select @error('condition') is-invalid @enderror" required>
                                    <option value="">-- Select --</option>
                                    <option value="new" {{ old('condition', $item->condition) === 'new' ? 'selected' : '' }}>New</option>
                                    <option value="good" {{ old('condition', $item->condition) === 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="fair" {{ old('condition', $item->condition) === 'fair' ? 'selected' : '' }}>Fair</option>
                                    <option value="poor" {{ old('condition', $item->condition) === 'poor' ? 'selected' : '' }}>Poor</option>
                                    <option value="damaged" {{ old('condition', $item->condition) === 'damaged' ? 'selected' : '' }}>Damaged</option>
                                </select>
                                @error('condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Purchase Date</label>
                                <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date', $item->purchase_date) }}">
                                @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Price (ETB)</label>
                                <input type="number" step="0.01" min="0" name="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror" value="{{ old('purchase_price', $item->purchase_price) }}">
                                @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $item->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-gold">
                                    <i class="bi bi-check-lg me-1"></i>Update Asset
                                </button>
                                <a href="{{ route('admin.class-assets.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push("scripts")
<script>
document.addEventListener("DOMContentLoaded", function() {
    const classSelect = document.getElementById("classSelect");
    const sectionSelect = document.getElementById("sectionSelect");
    const currentSectionId = {{ $item->section_id ?? "null" }};
    const allSections = @json($classrooms->flatMap(fn($c) => $c->sections->map(fn($s) => ["id" => $s->id, "name" => $s->name, "class_id" => $s->class_id])));

    function loadSections(classId) {
        sectionSelect.innerHTML = '<option value="">-- All Sections --</option>';
        if (!classId) return;
        const sections = allSections.filter(s => s.class_id == classId);
        if (sections.length === 0) {
            sectionSelect.innerHTML = '<option value="">No sections</option>';
            return;
        }
        sections.forEach(s => {
            const opt = document.createElement("option");
            opt.value = s.id;
            opt.textContent = s.name;
            if (s.id == currentSectionId) opt.selected = true;
            sectionSelect.appendChild(opt);
        });
    }

    // Load sections for the current classroom on page load
    loadSections(classSelect.value);

    classSelect.addEventListener("change", function() {
        loadSections(this.value);
    });
});
</script>
@endpush
@endsection