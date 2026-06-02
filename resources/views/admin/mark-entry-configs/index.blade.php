@extends('layouts.admin')

@section('title', 'Mark Entry Configuration')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-gear me-2"></i>Mark Entry Configuration</h4>
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetDefaults()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset to Defaults
                    </button>
                </div>
            </div>
            <p class="text-muted mt-1 mb-0">Configure how marks are entered, calculated, and graded. Changes take effect immediately.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.mark-entry-configs.update') }}" id="configForm">
        @csrf
        @method('PUT')

        <!-- General Settings -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-sliders me-2"></i>General Settings
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">CA Weight (%)</label>
                        <input type="number" name="ca_weight" class="form-control"
                            value="{{ $grouped->get('general')?->firstWhere('name', 'ca_weight')?->value ?? 30 }}"
                            min="0" max="100" step="1">
                        <small class="text-muted">Percentage weight of CA marks in grand total</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Exam Weight (%)</label>
                        <input type="number" name="exam_weight" class="form-control"
                            value="{{ $grouped->get('general')?->firstWhere('name', 'exam_weight')?->value ?? 70 }}"
                            min="0" max="100" step="1">
                        <small class="text-muted">Percentage weight of Exam marks in grand total</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Pass Mark</label>
                        <input type="number" name="pass_mark" class="form-control"
                            value="{{ $grouped->get('general')?->firstWhere('name', 'pass_mark')?->value ?? 50 }}"
                            min="0" max="100" step="1">
                        <small class="text-muted">Minimum score to pass a subject</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Rounding Precision</label>
                        <input type="number" name="rounding_precision" class="form-control"
                            value="{{ $grouped->get('general')?->firstWhere('name', 'rounding_precision')?->value ?? 2 }}"
                            min="0" max="4" step="1">
                        <small class="text-muted">Decimal places for calculated totals (backend)</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Input Precision</label>
                        <input type="number" name="input_precision" class="form-control"
                            value="{{ $grouped->get('general')?->firstWhere('name', 'input_precision')?->value ?? 1 }}"
                            min="0" max="4" step="1">
                        <small class="text-muted">Decimal places allowed in mark input fields</small>
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <div class="alert alert-info py-2 px-3 mb-0 w-100">
                            <small><strong>CA + Exam = 100%</strong><br>
                            CA Raw Total is auto-calculated from mark fields below.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mark Fields Configuration -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <i class="bi bi-list-ol me-2"></i>Mark Fields
                <button type="button" class="btn btn-sm btn-light float-end" onclick="addMarkField()">
                    <i class="bi bi-plus-circle me-1"></i>Add Field
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Define each mark entry field, its maximum value, display label, and category. The CA Raw Total is calculated automatically from all CA and Extra CA fields.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="markFieldsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:30px">#</th>
                                <th style="width:180px">Column Name</th>
                                <th style="width:120px">Max Mark</th>
                                <th style="width:180px">Label</th>
                                <th style="width:160px">Category</th>
                                <th style="width:80px">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="markFieldsBody">
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="2" class="text-end fw-bold">CA Raw Total:</td>
                                <td id="caRawTotalDisplay" class="fw-bold"></td>
                                <td colspan="3" class="text-muted">Auto-calculated sum of CA + Extra CA max values</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <input type="hidden" name="mark_fields_json" id="markFieldsJson">
            </div>
        </div>

        <!-- Grade Scale Configuration -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-star me-2"></i>Grade Scale
                <button type="button" class="btn btn-sm btn-dark float-end" onclick="addGradeRow()">
                    <i class="bi bi-plus-circle me-1"></i>Add Grade
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Define grade thresholds. Grades are matched from highest to lowest. A score must be >= minimum to receive that grade.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="gradeScaleTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:30px">#</th>
                                <th style="width:120px">Min Score</th>
                                <th style="width:100px">Grade</th>
                                <th style="width:120px">Grade Point</th>
                                <th style="width:150px">Label</th>
                                <th style="width:100px">Passing?</th>
                                <th style="width:80px">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="gradeScaleBody">
                        </tbody>
                    </table>
                </div>
                <input type="hidden" name="grade_scale_json" id="gradeScaleJson">
            </div>
        </div>

        <!-- Save Button -->
        <div class="row mb-4">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle me-2"></i>Save Configuration
                </button>
                <button type="button" class="btn btn-outline-secondary btn-lg ms-2" onclick="resetDefaults()">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Reset to Defaults
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// ===== MARK FIELDS =====
var markFieldsData = @json($grouped->get('fields')?->firstWhere('name', 'mark_fields')?->parsed_value ?? \App\Models\MarkEntryConfig::defaultMarkFields());

function renderMarkFields() {
    var tbody = document.getElementById('markFieldsBody');
    tbody.innerHTML = '';
    var caRawTotal = 0;

    markFieldsData.forEach(function(f, i) {
        var cat = f.category || 'ca';
        if (cat === 'ca' || cat === 'extra_ca') {
            caRawTotal += parseFloat(f.max) || 0;
        }
        var catLabel = cat === 'ca' ? 'CA' : (cat === 'extra_ca' ? 'Extra CA' : 'Exam');
        var catClass = cat === 'ca' ? 'table-success' : (cat === 'extra_ca' ? 'table-info' : 'table-warning');

        var tr = document.createElement('tr');
        tr.className = catClass;
        tr.innerHTML =
            '<td>' + (i + 1) + '</td>' +
            '<td><input type="text" class="form-control form-control-sm" value="' + (f.col || '') + '" onchange="updateMarkField(' + i + ', \'col\', this.value)" placeholder="e.g. ca1, test1"></td>' +
            '<td><input type="number" class="form-control form-control-sm" value="' + (f.max || 0) + '" min="0" step="0.5" onchange="updateMarkField(' + i + ', \'max\', parseFloat(this.value))"></td>' +
            '<td><input type="text" class="form-control form-control-sm" value="' + (f.label || '') + '" onchange="updateMarkField(' + i + ', \'label\', this.value)" placeholder="Display label"></td>' +
            '<td><select class="form-select form-select-sm" onchange="updateMarkField(' + i + ', \'category\', this.value)">' +
                '<option value="ca"' + (cat === 'ca' ? ' selected' : '') + '>CA (Continuous Assessment)</option>' +
                '<option value="extra_ca"' + (cat === 'extra_ca' ? ' selected' : '') + '>Extra CA (Conduct, etc.)</option>' +
                '<option value="exam"' + (cat === 'exam' ? ' selected' : '') + '>Exam (Tests, Mid-term, Final)</option>' +
            '</select></td>' +
            '<td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeMarkField(' + i + ')"><i class="bi bi-trash"></i></button></td>';
        tbody.appendChild(tr);
    });

    document.getElementById('caRawTotalDisplay').textContent = caRawTotal;
    updateMarkFieldsJson();
}

function updateMarkField(idx, key, value) {
    markFieldsData[idx][key] = value;
    renderMarkFields();
}

function removeMarkField(idx) {
    if (markFieldsData.length <= 1) { alert('Must have at least one field.'); return; }
    if (confirm('Remove field "' + (markFieldsData[idx].label || markFieldsData[idx].col) + '"?')) {
        markFieldsData.splice(idx, 1);
        renderMarkFields();
    }
}

function addMarkField() {
    markFieldsData.push({ col: 'new_field', max: 5, label: 'New Field', category: 'ca' });
    renderMarkFields();
}

function updateMarkFieldsJson() {
    document.getElementById('markFieldsJson').value = JSON.stringify(markFieldsData);
}

// ===== GRADE SCALE =====
var gradeScaleData = @json($grouped->get('grading')?->firstWhere('name', 'grade_scale')?->parsed_value ?? \App\Models\MarkEntryConfig::defaultGradeScale());

function renderGradeScale() {
    var tbody = document.getElementById('gradeScaleBody');
    tbody.innerHTML = '';

    gradeScaleData.forEach(function(g, i) {
        var tr = document.createElement('tr');
        var passClass = g.is_passing ? 'table-success' : 'table-danger';
        tr.className = passClass;
        tr.innerHTML =
            '<td>' + (i + 1) + '</td>' +
            '<td><input type="number" class="form-control form-control-sm" value="' + (g.min !== undefined ? g.min : '') + '" min="0" max="100" step="0.01" onchange="updateGradeRow(' + i + ', \'min\', parseFloat(this.value))"></td>' +
            '<td><input type="text" class="form-control form-control-sm" value="' + (g.grade || '') + '" maxlength="3" onchange="updateGradeRow(' + i + ', \'grade\', this.value)" style="font-weight:bold;font-size:1.1em;"></td>' +
            '<td><input type="number" class="form-control form-control-sm" value="' + (g.point !== undefined ? g.point : 0) + '" min="0" max="4" step="0.01" onchange="updateGradeRow(' + i + ', \'point\', parseFloat(this.value))"></td>' +
            '<td><input type="text" class="form-control form-control-sm" value="' + (g.label || '') + '" onchange="updateGradeRow(' + i + ', \'label\', this.value)"></td>' +
            '<td><select class="form-select form-select-sm" onchange="updateGradeRow(' + i + ', \'is_passing\', this.value === \'1\')">' +
                '<option value="1"' + (g.is_passing ? ' selected' : '') + '>Yes (Pass)</option>' +
                '<option value="0"' + (!g.is_passing ? ' selected' : '') + '>No (Fail)</option>' +
            '</select></td>' +
            '<td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeGradeRow(' + i + ')"><i class="bi bi-trash"></i></button></td>';
        tbody.appendChild(tr);
    });

    updateGradeScaleJson();
}

function updateGradeRow(idx, key, value) {
    gradeScaleData[idx][key] = value;
    renderGradeScale();
}

function removeGradeRow(idx) {
    if (gradeScaleData.length <= 1) { alert('Must have at least one grade.'); return; }
    if (confirm('Remove grade "' + (gradeScaleData[idx].grade) + '"?')) {
        gradeScaleData.splice(idx, 1);
        renderGradeScale();
    }
}

function addGradeRow() {
    gradeScaleData.push({ min: 0, grade: 'N', point: 0.00, label: 'New Grade', is_passing: false });
    renderGradeScale();
}

function updateGradeScaleJson() {
    document.getElementById('gradeScaleJson').value = JSON.stringify(gradeScaleData);
}

// ===== VALIDATION BEFORE SAVE =====
document.getElementById('configForm').addEventListener('submit', function(e) {
    var caWeight = parseFloat(document.querySelector('[name="ca_weight"]').value) || 0;
    var examWeight = parseFloat(document.querySelector('[name="exam_weight"]').value) || 0;

    if (Math.abs(caWeight + examWeight - 100) > 0.01) {
        e.preventDefault();
        alert('CA Weight (' + caWeight + '%) + Exam Weight (' + examWeight + '%) must equal 100%. Currently: ' + (caWeight + examWeight) + '%');
        return false;
    }

    // Validate mark fields have unique column names
    var cols = markFieldsData.map(function(f) { return f.col; });
    var uniqueCols = [...new Set(cols)];
    if (cols.length !== uniqueCols.length) {
        e.preventDefault();
        alert('Mark field column names must be unique. Found duplicates.');
        return false;
    }

    // Validate grade scale has unique grade letters
    var grades = gradeScaleData.map(function(g) { return g.grade; });
    var uniqueGrades = [...new Set(grades)];
    if (grades.length !== uniqueGrades.length) {
        e.preventDefault();
        alert('Grade letters must be unique. Found duplicates.');
        return false;
    }
});

// ===== RESET TO DEFAULTS =====
function resetDefaults() {
    if (confirm('Reset ALL mark entry configuration to system defaults? This cannot be undone.')) {
        window.location.href = '{{ route("admin.mark-entry-configs.reset") }}';
    }
}

// ===== INITIAL RENDER =====
renderMarkFields();
renderGradeScale();
</script>
@endsection
