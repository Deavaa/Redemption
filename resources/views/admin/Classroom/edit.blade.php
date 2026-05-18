@extends('layouts.admin')
@section('title', 'Edit Classroom')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.classrooms.index') }}">Classrooms</a></li>
                    <li class="active">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.classrooms.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="modern-card">
        <form method="POST" action="{{ route('admin.classrooms.update', $data->id) }}">
            @csrf @method('PUT')

            {{-- Class Information --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Class Information</h3>
                        <p class="modern-form-section-desc">Update the class name, academic year, and capacity</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="name">
                                Class Name <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-book modern-input-icon"></i>
                                <input type="text"
                                    name="name"
                                    id="name"
                                    class="modern-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    value="{{ old('name', $data->name) }}"
                                    placeholder="e.g. Grade 1"
                                    required
                                    autofocus>
                            </div>
                            @error('name')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="academic_year_id">
                                Academic Year <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar-alt modern-input-icon"></i>
                                <select name="academic_year_id" id="academic_year_id" class="modern-input modern-select {{ $errors->has('academic_year_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Academic Year --</option>
                                    @foreach($academicYears as $ay)
                                        <option value="{{ $ay->id }}" {{ old('academic_year_id', $data->academic_year_id) == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('academic_year_id')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="branch_id">
                                Branch <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-map-marker-alt modern-input-icon"></i>
                                <select name="branch_id" id="branch_id" class="modern-input modern-select {{ $errors->has('branch_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Branch --</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ old('branch_id', $data->branch_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('branch_id')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="teacher_id">
                                Class Teacher <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-user-tie modern-input-icon"></i>
                                <select name="teacher_id" id="teacher_id" class="modern-input modern-select">
                                    <option value="">-- Select Teacher --</option>
                                    @foreach($teachers as $t)
                                        <option value="{{ $t->id }}" {{ old('teacher_id', $data->teacher_id) == $t->id ? 'selected' : '' }}>{{ $t->first_name }} {{ $t->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label">
                                Class Capacity <small>(auto-calculated)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calculator modern-input-icon"></i>
                                <input type="text"
                                    id="capacityDisplay"
                                    class="modern-input"
                                    style="background:#f9fafb;color:#6b7280;cursor:default;"
                                    value="{{ $data->calculated_capacity ? $data->calculated_capacity . ' ' . __('students (sum of sections)') : __('Sum of section capacities') }}"
                                    readonly
                                    tabindex="-1">
                            </div>
                            <small class="text-muted mt-1" style="font-size:0.78rem;">{{ __('Auto-calculated from sections below') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sections --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Sections</h3>
                        <p class="modern-form-section-desc">Edit existing sections or add new ones. Removed sections will be permanently deleted.</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-section-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Each section can have its own capacity and homeroom teacher. Removed sections will be permanently deleted.</span>
                    </div>
                    <div id="sectionRows">
                        @foreach($data->sections as $idx => $sec)
                        <div class="modern-section-row">
                            <div class="modern-section-row-header">
                                <span class="modern-section-row-number">{{ $loop->iteration }}</span>
                                <span class="modern-section-row-title">Section</span>
                            </div>
                            <div class="modern-section-row-body">
                                <div class="modern-form-grid modern-form-grid-3">
                                    <div class="modern-form-group">
                                        <label class="modern-form-label">Section Name <span class="modern-required">*</span></label>
                                        <div class="modern-input-wrapper">
                                            <i class="fas fa-tag modern-input-icon"></i>
                                            <input type="text" name="sections[{{ $idx }}][name]" class="modern-input" value="{{ $sec->name }}" placeholder="e.g. A" required>
                                        </div>
                                    </div>
                                    <div class="modern-form-group">
                                        <label class="modern-form-label">Max Students</label>
                                        <div class="modern-input-wrapper">
                                            <i class="fas fa-users modern-input-icon"></i>
                                            <input type="number" name="sections[{{ $idx }}][max_students]" class="modern-input" value="{{ $sec->max_students ?? 40 }}" min="1" placeholder="40">
                                        </div>
                                    </div>
                                    <div class="modern-form-group">
                                        <label class="modern-form-label">Section Teacher</label>
                                        <div class="modern-input-wrapper">
                                            <i class="fas fa-user modern-input-icon"></i>
                                            <select name="sections[{{ $idx }}][teacher_id]" class="modern-input modern-select">
                                                <option value="">-- Not Assigned --</option>
                                                @foreach($teachers as $t)
                                                    <option value="{{ $t->id }}" {{ $sec->teacher_id == $t->id ? 'selected' : '' }}>{{ $t->first_name }} {{ $t->last_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="sections[{{ $idx }}][id]" value="{{ $sec->id }}">
                            <button type="button" class="modern-section-remove" onclick="removeSectionRow(this)" title="Remove section">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endforeach

                        @if($data->sections->count() === 0)
                        {{-- Fallback: at least one section row if none exist --}}
                        <div class="modern-section-row">
                            <div class="modern-section-row-header">
                                <span class="modern-section-row-number">1</span>
                                <span class="modern-section-row-title">Section</span>
                            </div>
                            <div class="modern-section-row-body">
                                <div class="modern-form-grid modern-form-grid-3">
                                    <div class="modern-form-group">
                                        <label class="modern-form-label">Section Name <span class="modern-required">*</span></label>
                                        <div class="modern-input-wrapper">
                                            <i class="fas fa-tag modern-input-icon"></i>
                                            <input type="text" name="sections[0][name]" class="modern-input" placeholder="e.g. A" required>
                                        </div>
                                    </div>
                                    <div class="modern-form-group">
                                        <label class="modern-form-label">Max Students</label>
                                        <div class="modern-input-wrapper">
                                            <i class="fas fa-users modern-input-icon"></i>
                                            <input type="number" name="sections[0][max_students]" class="modern-input" value="40" min="1" placeholder="40">
                                        </div>
                                    </div>
                                    <div class="modern-form-group">
                                        <label class="modern-form-label">Section Teacher</label>
                                        <div class="modern-input-wrapper">
                                            <i class="fas fa-user modern-input-icon"></i>
                                            <select name="sections[0][teacher_id]" class="modern-input modern-select">
                                                <option value="">-- Not Assigned --</option>
                                                @foreach($teachers as $t)
                                                    <option value="{{ $t->id }}">{{ $t->first_name }} {{ $t->last_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="modern-section-remove" onclick="removeSectionRow(this)" disabled title="Remove section">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endif
                    </div>
                    <button type="button" class="modern-add-section-btn" onclick="addSectionRow()">
                        <i class="fas fa-plus"></i>
                        <span>Add Section</span>
                    </button>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions">
                <a href="{{ route('admin.classrooms.index') }}" class="btn-modern btn-modern-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-save"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* Modern Page Layout */
.modern-page { animation: fadeSlideIn 0.4s ease-out; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.modern-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-page-header-left { flex: 1; }

/* Breadcrumb */
.modern-breadcrumb ol {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0 0 0.5rem;
    gap: 0.5rem;
    font-size: 0.8rem;
    align-items: center;
}

.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Card */
.modern-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

/* Form Section */
.modern-form-section { border-bottom: 1px solid #f0f0f0; }
.modern-form-section:last-of-type { border-bottom: none; }

.modern-form-section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem 2rem 0.75rem;
}

.modern-form-section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.modern-form-section-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-form-section-icon-green { background: #ecfdf5; color: #10b981; }

.modern-form-section-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}

.modern-form-section-desc {
    font-size: 0.82rem;
    color: #9ca3af;
    margin: 0.15rem 0 0;
}

.modern-form-section-body { padding: 1.25rem 2rem 1.75rem; }

/* Form Grid */
.modern-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.modern-form-grid-3 {
    grid-template-columns: 1fr 1fr 1.5fr;
}

.modern-form-span-2 { grid-column: span 2; }

/* Form Group */
.modern-form-group { display: flex; flex-direction: column; }

.modern-form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.45rem;
    font-size: 0.88rem;
}

.modern-form-label small {
    font-weight: 400;
    color: #9ca3af;
    font-size: 0.78rem;
}

.modern-required { color: #ef4444; font-weight: 700; }

/* Input */
.modern-input-wrapper { position: relative; }

.modern-input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.85rem;
    pointer-events: none;
    z-index: 1;
}

.modern-input {
    width: 100%;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.7rem 0.9rem 0.7rem 2.5rem;
    font-size: 0.9rem;
    color: #1a1a2e;
    background: #fff;
    transition: all 0.2s;
}

.modern-input:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.modern-input::placeholder { color: #c5c9d2; }

.modern-input.is-invalid {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.modern-select {
    appearance: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.25rem;
    padding-right: 2.5rem;
}

.modern-form-error {
    display: block;
    color: #ef4444;
    font-size: 0.8rem;
    margin-top: 0.35rem;
    font-weight: 500;
}

/* Section Rows */
.modern-section-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1rem;
    background: #f8f9ff;
    border-radius: 10px;
    margin-bottom: 1.25rem;
    font-size: 0.82rem;
    color: #6b7280;
    border: 1px solid #eef2ff;
}

.modern-section-info i { color: #4361ee; }

.modern-section-row {
    position: relative;
    background: #fafbfc;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    margin-bottom: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.modern-section-row:hover {
    border-color: #c7d2fe;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.06);
}

.modern-section-row-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem 0;
}

.modern-section-row-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: #eef2ff;
    color: #4361ee;
    font-weight: 700;
    font-size: 0.75rem;
}

.modern-section-row-title {
    font-weight: 600;
    font-size: 0.85rem;
    color: #374151;
}

.modern-section-row-body {
    padding: 1rem 1.25rem 1.25rem;
}

.modern-section-remove {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    background: #fef2f2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.75rem;
}

.modern-section-remove:hover:not(:disabled) {
    background: #dc2626;
    color: #fff;
    transform: scale(1.05);
}

.modern-section-remove:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* Add Section Button */
.modern-add-section-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.25rem;
    border-radius: 10px;
    border: 2px dashed #c7d2fe;
    background: #f8f9ff;
    color: #4361ee;
    font-weight: 600;
    font-size: 0.88rem;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 0.5rem;
}

.modern-add-section-btn:hover {
    background: #eef2ff;
    border-color: #4361ee;
    transform: translateY(-1px);
}

/* Form Actions */
.modern-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem 2rem;
    border-top: 1px solid #f0f0f0;
    background: #fafbfc;
}

/* Modern Button */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.35rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.25s;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
}

.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4);
    color: #fff;
}

.btn-modern-outline {
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
}

.btn-modern-outline:hover {
    border-color: #4361ee;
    color: #4361ee;
    background: #f8f9ff;
}

.btn-modern-ghost {
    background: transparent;
    color: #6b7280;
    padding: 0.65rem 1rem;
}

.btn-modern-ghost:hover {
    color: #1a1a2e;
    background: #f3f4f6;
}

/* Responsive */
@media (max-width: 768px) {
    .modern-page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .modern-form-grid,
    .modern-form-grid-3 {
        grid-template-columns: 1fr;
    }

    .modern-form-span-2 { grid-column: span 1; }

    .modern-form-section-body {
        padding: 1rem 1.25rem 1.5rem;
    }

    .modern-form-section-header {
        padding: 1.25rem 1.25rem 0.75rem;
    }

    .modern-form-actions {
        padding: 1rem 1.25rem;
        flex-direction: column;
    }

    .btn-modern {
        justify-content: center;
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
var sectionIndex = document.querySelectorAll('.modern-section-row').length;
var teacherOpts = "";

function initTeacherOpts() {
    var sel = document.querySelector('select[name^="sections"][name$="[teacher_id]"]');
    if (sel) teacherOpts = sel.innerHTML;
}
initTeacherOpts();

function addSectionRow() {
    var container = document.getElementById('sectionRows');
    var rowCount = document.querySelectorAll('.modern-section-row').length;
    row = document.createElement('div');
    row.className = 'modern-section-row';
    row.style.animation = 'fadeSlideIn 0.3s ease';
    row.innerHTML =
        '<div class="modern-section-row-header">' +
            '<span class="modern-section-row-number">' + (rowCount + 1) + '</span>' +
            '<span class="modern-section-row-title">Section</span>' +
        '</div>' +
        '<div class="modern-section-row-body">' +
            '<div class="modern-form-grid modern-form-grid-3">' +
                '<div class="modern-form-group">' +
                    '<label class="modern-form-label">Section Name <span class="modern-required">*</span></label>' +
                    '<div class="modern-input-wrapper">' +
                        '<i class="fas fa-tag modern-input-icon"></i>' +
                        '<input type="text" name="sections[' + sectionIndex + '][name]" class="modern-input" placeholder="e.g. B" required>' +
                    '</div>' +
                '</div>' +
                '<div class="modern-form-group">' +
                    '<label class="modern-form-label">Max Students</label>' +
                    '<div class="modern-input-wrapper">' +
                        '<i class="fas fa-users modern-input-icon"></i>' +
                        '<input type="number" name="sections[' + sectionIndex + '][max_students]" class="modern-input" value="40" min="1" placeholder="40">' +
                    '</div>' +
                '</div>' +
                '<div class="modern-form-group">' +
                    '<label class="modern-form-label">Section Teacher</label>' +
                    '<div class="modern-input-wrapper">' +
                        '<i class="fas fa-user modern-input-icon"></i>' +
                        '<select name="sections[' + sectionIndex + '][teacher_id]" class="modern-input modern-select">' + teacherOpts + '</select>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>' +
        '<button type="button" class="modern-section-remove" onclick="removeSectionRow(this)" title="Remove section">' +
            '<i class="fas fa-times"></i>' +
        '</button>';
    container.appendChild(row);
    sectionIndex++;
    updateRemoveButtons();
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function removeSectionRow(btn) {
    btn.closest('.modern-section-row').remove();
    renumberSections();
    updateRemoveButtons();
}

function renumberSections() {
    var rows = document.querySelectorAll('.modern-section-row');
    rows.forEach(function(row, index) {
        var numEl = row.querySelector('.modern-section-row-number');
        if (numEl) numEl.textContent = index + 1;
    });
}

function updateRemoveButtons() {
    var rows = document.querySelectorAll('.modern-section-row');
    rows.forEach(function(r) {
        var btn = r.querySelector('.modern-section-remove');
        if (btn) btn.disabled = rows.length <= 1;
    });
    updateCapacityDisplay();
}

function updateCapacityDisplay() {
    var total = 0;
    var inputs = document.querySelectorAll('input[name*="[max_students]"]');
    inputs.forEach(function(input) {
        var val = parseInt(input.value);
        if (!isNaN(val) && val > 0) total += val;
    });
    var display = document.getElementById('capacityDisplay');
    if (display) {
        display.value = total > 0 ? total + ' {{ __("students (sum of sections)") }}' : '{{ __("Sum of section capacities") }}';
    }
}

// Listen for max_students changes
document.addEventListener('input', function(e) {
    if (e.target.name && e.target.name.indexOf('[max_students]') !== -1) {
        updateCapacityDisplay();
    }
});

// Initial calculation
updateCapacityDisplay();
</script>
@endpush
@endsection