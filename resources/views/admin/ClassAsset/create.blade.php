@extends("layouts.admin")
@section("page-title", "Register Asset")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-box-seam me-2"></i>Register Asset</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.class-assets.index') }}" class="text-decoration-none text-muted">Class Assets</a></li>
                <li class="breadcrumb-item active text-gold">Register</li>
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
                    <h6 class="fw-semibold mb-0"><i class="bi bi-plus-circle me-2"></i>Asset Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.class-assets.store') }}" id="assetForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Projector, Whiteboard, Chairs" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Classroom <span class="text-danger">*</span></label>
                                <select name="class_id" id="classSelect" class="form-select @error('class_id') is-invalid @enderror" required>
                                    <option value="">-- Select --</option>
                                    @foreach($classrooms as $cls)
                                    <option value="{{ $cls->id }}" {{ old('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
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
                                <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" min="1" required>
                                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Condition <span class="text-danger">*</span></label>
                                <select name="condition" class="form-select @error('condition') is-invalid @enderror" required>
                                    <option value="">-- Select --</option>
                                    <option value="new" {{ old('condition') === 'new' ? 'selected' : '' }}>New</option>
                                    <option value="good" {{ old('condition') === 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="fair" {{ old('condition') === 'fair' ? 'selected' : '' }}>Fair</option>
                                    <option value="poor" {{ old('condition') === 'poor' ? 'selected' : '' }}>Poor</option>
                                    <option value="damaged" {{ old('condition') === 'damaged' ? 'selected' : '' }}>Damaged</option>
                                </select>
                                @error('condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Purchase Date</label>
                                <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date') }}">
                                @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Price (ETB)</label>
                                <input type="number" step="0.01" min="0" name="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror" value="{{ old('purchase_price') }}" placeholder="0.00">
                                @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Optional description...">{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-gold">
                                    <i class="bi bi-check-lg me-1"></i>Register Asset
                                </button>
                                <a href="{{ route('admin.class-assets.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-info-circle me-2"></i>Asset Registration Info</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-1-circle text-gold me-2"></i>Select the <strong>Classroom</strong> first</li>
                        <li class="mb-2"><i class="bi bi-2-circle text-gold me-2"></i>Optionally choose a <strong>Section</strong></li>
                        <li class="mb-2"><i class="bi bi-3-circle text-gold me-2"></i>Leave section as "All" for classroom-wide assets</li>
                        <li class="mb-2"><i class="bi bi-4-circle text-gold me-2"></i>Set the <strong>condition</strong> accurately</li>
                        <li><i class="bi bi-5-circle text-gold me-2"></i>Add <strong>price</strong> for valuation</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-layers me-2"></i>Existing Assets Quick View</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="existingAssets">
                        <div class="list-group-item text-center text-muted py-3">
                            <small>Select a classroom to see its assets</small>
                        </div>
                    </div>
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
            sectionSelect.appendChild(opt);
        });
    }

    classSelect.addEventListener("change", function() {
        const classId = this.value;
        loadSections(classId);
        loadExistingAssets(classId);
    });

    function loadExistingAssets(classId) {
        const container = document.getElementById("existingAssets");
        if (!classId) {
            container.innerHTML = '<div class="list-group-item text-center text-muted py-3"><small>Select a classroom to see its assets</small></div>';
            return;
        }
        fetch("/admin/class-assets/api/by-class/" + classId)
            .then(r => r.json())
            .then(assets => {
                if (assets.length === 0) {
                    container.innerHTML = '<div class="list-group-item text-center text-muted py-3"><small>No assets registered</small></div>';
                    return;
                }
                container.innerHTML = assets.map(a => `
                    <div class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="small">${a.name}</strong>
                                ${a.section_name ? `<span class="badge bg-info bg-opacity-10 text-info ms-1">${a.section_name}</span>` : '<span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">All</span>'}
                            </div>
                            <small class="text-muted">Qty: ${a.quantity}</small>
                        </div>
                    </div>
                `).join("");
            })
            .catch(() => {
                container.innerHTML = '<div class="list-group-item text-center text-muted py-3"><small>Could not load assets</small></div>';
            });
    }
});
</script>
@endpush
@endsection