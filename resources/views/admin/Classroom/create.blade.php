@extends("layouts.admin")
@section("page-title", "Add Class")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-plus-circle me-2 text-primary"></i>Add New Class</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route("admin.classrooms.index") }}" class="text-decoration-none text-muted">Classes</a></li>
                <li class="breadcrumb-item active text-gold">Add New</li>
            </ol></nav>
        </div>
        <a href="{{ route("admin.classrooms.index") }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to List</a>
    </div>

    <form method="POST" action="{{ route("admin.classrooms.store") }}" id="classForm">
    @csrf

    <!-- CLASS INFO CARD -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Class Information</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Class Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Grade 1" value="{{ old("name") }}" required>
                    @error("name")<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                    <select name="academic_year_id" class="form-select" required>
                        <option value="">-- Select Year --</option>
                        @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ old("academic_year_id") == $ay->id ? "selected" : "" }}>{{ $ay->name }}</option>
                        @endforeach
                    </select>
                    @error("academic_year_id")<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">-- None --</option>
                        @foreach(\App\Models\Branch::orderBy("name")->get() as $b)
                        <option value="{{ $b->id }}" {{ old("branch_id") == $b->id ? "selected" : "" }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTIONS CARD -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-semibold mb-0"><i class="bi bi-grid-3x3 me-2 text-success"></i>Sections</h6>
            <button type="button" class="btn btn-success btn-sm" onclick="addSectionRow()">
                <i class="bi bi-plus-lg me-1"></i>Add Section
            </button>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1"></i>Each section can have its own capacity and homeroom teacher. Add at least one section.</p>
            <div id="sectionRows">
                <div class="section-row row g-2 mb-3 align-items-end p-3 rounded border bg-light">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">Name <span class="text-danger">*</span></label>
                        <input type="text" name="sections[0][name]" class="form-control form-control-sm" placeholder="e.g. A" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">Max Students</label>
                        <input type="number" name="sections[0][max_students]" class="form-control form-control-sm" value="40" min="1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Homeroom Teacher</label>
                        <select name="sections[0][teacher_id]" class="form-select form-select-sm">
                            <option value="">-- Not Assigned --</option>
                            @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->first_name }} {{ $t->last_name }} ({{ $t->department ?? "N/A" }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removeSectionRow(this)" disabled>
                            <i class="bi bi-trash me-1"></i>Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SUBMIT -->
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-gold btn-lg px-4">
            <i class="bi bi-check-lg me-2"></i>Save Class &amp; Sections
        </button>
        <a href="{{ route("admin.classrooms.index") }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
    </div>
    </form>
</div>

@push("scripts")
<script>
var sectionIndex = 1;
var teacherOpts = "";

function initTeacherOpts() {
    var sel = document.querySelector("[name^=\"sections\"][name$=\"[teacher_id]\"]");
    if (sel) teacherOpts = sel.innerHTML;
}
initTeacherOpts();

function addSectionRow() {
    var container = document.getElementById("sectionRows");
    var row = document.createElement("div");
    row.className = "section-row row g-2 mb-3 align-items-end p-3 rounded border bg-light";
    row.innerHTML = "<div class=\"col-md-2\"><label class=\"form-label fw-semibold small\">Name <span class=\"text-danger\">*</span></label><input type=\"text\" name=\"sections[" + sectionIndex + "][name]\" class=\"form-control form-control-sm\" placeholder=\"e.g. B\" required></div><div class=\"col-md-2\"><label class=\"form-label fw-semibold small\">Max Students</label><input type=\"number\" name=\"sections[" + sectionIndex + "][max_students]\" class=\"form-control form-control-sm\" value=\"40\" min=\"1\"></div><div class=\"col-md-6\"><label class=\"form-label fw-semibold small\">Homeroom Teacher</label><select name=\"sections[" + sectionIndex + "][teacher_id]\" class=\"form-select form-select-sm\">" + teacherOpts + "</select></div><div class=\"col-md-2\"><button type=\"button\" class=\"btn btn-outline-danger btn-sm w-100\" onclick=\"removeSectionRow(this)\"><i class=\"bi bi-trash me-1\"></i>Remove</button></div>";
    container.appendChild(row);
    sectionIndex++;
    updateRemoveButtons();
    row.scrollIntoView({ behavior: "smooth", block: "center" });
}

function removeSectionRow(btn) {
    btn.closest(".section-row").remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    var rows = document.querySelectorAll(".section-row");
    rows.forEach(function(r) {
        var btn = r.querySelector("button[onclick*=removeSectionRow]");
        if (btn) btn.disabled = rows.length <= 1;
    });
}
</script>
@endpush
@endsection