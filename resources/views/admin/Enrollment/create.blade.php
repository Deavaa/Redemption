@extends('layouts.admin')
@section('title', 'New Enrollment')

@section('content')
<div class="sl-page">
    {{-- Page Header --}}
    <div class="sl-header">
        <div class="sl-header-left">
            <nav aria-label="breadcrumb" class="sl-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.enrollments.index') }}">Enrollment</a></li>
                    <li class="active">New Enrollment</li>
                </ol>
            </nav>
        </div>
        <div class="sl-header-right">
            <a href="{{ route('admin.enrollments.index') }}" class="sl-btn sl-btn-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="sl-card">
        <form method="POST" action="{{ route('admin.enrollments.store') }}">
            @csrf

            {{-- Student & Academic Info Section --}}
            <div class="sl-form-section">
                <div class="sl-form-section-head">
                    <div class="sl-form-section-icon sl-form-icon-blue"><i class="fas fa-user-plus"></i></div>
                    <div>
                        <h3 class="sl-form-section-title">Student & Academic Information</h3>
                        <p class="sl-form-section-desc">Select the student and academic details for enrollment</p>
                    </div>
                </div>
                <div class="sl-form-section-body">
                    <div class="sl-form-grid">
                        <div class="sl-form-group">
                            <label class="sl-form-label" for="student_id">Student <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-user sl-input-icon"></i>
                                <select name="student_id" id="student_id" class="sl-input sl-select {{ $errors->has('student_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Student --</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->full_name }} ({{ $student->admission_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('student_id')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="academic_year_id">Academic Year <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-calendar-alt sl-input-icon"></i>
                                <select name="academic_year_id" id="academic_year_id" class="sl-input sl-select {{ $errors->has('academic_year_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Academic Year --</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('academic_year_id')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="branch_id">Branch <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-building sl-input-icon"></i>
                                <select name="branch_id" id="branch_id" class="sl-input sl-select {{ $errors->has('branch_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Branch --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('branch_id')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="class_id">Class <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-chalkboard sl-input-icon"></i>
                                <select name="class_id" id="class_id" class="sl-input sl-select {{ $errors->has('class_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Class --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('class_id')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="section_id">Section <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-layer-group sl-input-icon"></i>
                                <select name="section_id" id="section_id" class="sl-input sl-select {{ $errors->has('section_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Section --</option>
                                </select>
                            </div>
                            @error('section_id')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="registration_fee">Registration Fee</label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-dollar-sign sl-input-icon"></i>
                                <input type="number" name="registration_fee" id="registration_fee"
                                    step="0.01" min="0"
                                    class="sl-input {{ $errors->has('registration_fee') ? 'is-invalid' : '' }}"
                                    value="{{ old('registration_fee', 500) }}"
                                    placeholder="e.g. 500.00">
                            </div>
                            @error('registration_fee')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Enrollment Details Section --}}
            <div class="sl-form-section">
                <div class="sl-form-section-head">
                    <div class="sl-form-section-icon sl-form-icon-green"><i class="fas fa-clipboard-check"></i></div>
                    <div>
                        <h3 class="sl-form-section-title">Enrollment Details</h3>
                        <p class="sl-form-section-desc">Specify enrollment type and additional information</p>
                    </div>
                </div>
                <div class="sl-form-section-body">
                    <div class="sl-form-grid">
                        <div class="sl-form-group">
                            <label class="sl-form-label" for="enrollment_type">Enrollment Type <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-sign-in-alt sl-input-icon"></i>
                                <select name="enrollment_type" id="enrollment_type" class="sl-input sl-select {{ $errors->has('enrollment_type') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="new" {{ old('enrollment_type') == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="returning" {{ old('enrollment_type') == 'returning' ? 'selected' : '' }}>Returning</option>
                                    <option value="transferred_in" {{ old('enrollment_type') == 'transferred_in' ? 'selected' : '' }}>Transferred In</option>
                                </select>
                            </div>
                            @error('enrollment_type')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="enrollment_date">Enrollment Date <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-calendar-check sl-input-icon"></i>
                                <input type="date" name="enrollment_date" id="enrollment_date"
                                    class="sl-input {{ $errors->has('enrollment_date') ? 'is-invalid' : '' }}"
                                    value="{{ old('enrollment_date', date('Y-m-d')) }}" required>
                            </div>
                            @error('enrollment_date')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group sl-form-span-2">
                            <label class="sl-form-label" for="notes">Notes <small>(optional)</small></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-align-left sl-input-icon" style="top:0.85rem;transform:none;"></i>
                                <textarea name="notes" id="notes"
                                    class="sl-input sl-textarea {{ $errors->has('notes') ? 'is-invalid' : '' }}"
                                    placeholder="Additional enrollment notes..."
                                    rows="3">{{ old('notes') }}</textarea>
                            </div>
                            @error('notes')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="sl-form-actions">
                <a href="{{ route('admin.enrollments.index') }}" class="sl-btn sl-btn-ghost">Cancel</a>
                <button type="submit" class="sl-btn sl-btn-primary">
                    <i class="fas fa-check"></i> Enroll Student
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* ========================================================
   ENROLLMENT CREATE - sl-* namespace
   ======================================================== */
.sl-page { animation: slIn 0.3s ease-out; }
@keyframes slIn { from { opacity: 0; } to { opacity: 1; } }

.sl-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;
}
.sl-header-left { flex: 1; }
.sl-header-right { display: flex; gap: 0.4rem; flex-wrap: wrap; }

.sl-breadcrumb ol {
    display: flex; list-style: none; padding: 0; margin: 0;
    gap: 0.3rem; font-size: 0.72rem; align-items: center;
}
.sl-breadcrumb li { color: #adb5bd; }
.sl-breadcrumb li a { color: #6c757d; text-decoration: none; }
.sl-breadcrumb li a:hover { color: #4361ee; }
.sl-breadcrumb li + li::before { content: '/'; margin-right: 0.3rem; color: #dee2e6; }
.sl-breadcrumb li.active { color: #4361ee; font-weight: 500; }

.sl-btn {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.3rem 0.65rem; border-radius: 6px; font-weight: 600;
    font-size: 0.73rem; text-decoration: none; border: none; cursor: pointer;
    transition: all 0.2s; white-space: nowrap;
}
.sl-btn-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff; box-shadow: 0 1px 4px rgba(67,97,238,0.3);
}
.sl-btn-primary:hover { color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.4); }
.sl-btn-outline { background: #fff; color: #4361ee; border: 1px solid #4361ee; }
.sl-btn-outline:hover { background: #4361ee; color: #fff; }
.sl-btn-ghost { background: transparent; color: #6b7280; }
.sl-btn-ghost:hover { color: #1a1a2e; background: #f3f4f6; }

.sl-card {
    background: #fff; border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;
    overflow: hidden;
}

/* Form Section */
.sl-form-section { border-bottom: 1px solid #f0f0f0; }
.sl-form-section:last-of-type { border-bottom: none; }
.sl-form-section-head {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 1rem 1.25rem 0.5rem;
}
.sl-form-section-icon {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem; flex-shrink: 0;
}
.sl-form-icon-blue { background: #eef2ff; color: #4361ee; }
.sl-form-icon-green { background: #ecfdf5; color: #10b981; }
.sl-form-section-title {
    font-size: 0.9rem; font-weight: 700; color: #1a1a2e; margin: 0;
}
.sl-form-section-desc {
    font-size: 0.72rem; color: #9ca3af; margin: 0.1rem 0 0;
}
.sl-form-section-body { padding: 0.75rem 1.25rem 1.25rem; }

/* Form Grid */
.sl-form-grid {
    display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;
}
.sl-form-span-2 { grid-column: span 2; }

/* Form Group */
.sl-form-group { display: flex; flex-direction: column; }
.sl-form-label {
    font-weight: 600; color: #374151; margin-bottom: 0.3rem;
    font-size: 0.78rem;
}
.sl-form-label small { font-weight: 400; color: #9ca3af; font-size: 0.7rem; }
.sl-required { color: #ef4444; font-weight: 700; }

/* Input */
.sl-input-wrap { position: relative; }
.sl-input-icon {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: #9ca3af; font-size: 0.75rem; pointer-events: none; z-index: 1;
}
.sl-input {
    width: 100%; border: 1px solid #e5e7eb; border-radius: 7px;
    padding: 0.45rem 0.7rem 0.45rem 2rem; font-size: 0.82rem;
    color: #1a1a2e; background: #fff; transition: all 0.2s;
}
.sl-input:focus {
    outline: none; border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67,97,238,0.1);
}
.sl-input::placeholder { color: #c5c9d2; }
.sl-input.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }
.sl-textarea { resize: vertical; min-height: 70px; }
.sl-select {
    appearance: none; cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.6rem center;
    background-repeat: no-repeat; background-size: 1rem;
    padding-right: 2rem;
}
.sl-form-error {
    display: block; color: #ef4444; font-size: 0.72rem;
    margin-top: 0.25rem; font-weight: 500;
}

/* Form Actions */
.sl-form-actions {
    display: flex; justify-content: flex-end; gap: 0.5rem;
    padding: 1rem 1.25rem; border-top: 1px solid #f0f0f0;
    background: #fafbfc;
}

@media (max-width: 768px) {
    .sl-header { flex-direction: column; align-items: stretch; }
    .sl-form-grid { grid-template-columns: 1fr; }
    .sl-form-span-2 { grid-column: span 1; }
    .sl-form-section-body { padding: 0.5rem 0.75rem 1rem; }
    .sl-form-section-head { padding: 0.75rem 0.75rem 0.4rem; }
    .sl-form-actions { padding: 0.75rem; flex-direction: column; }
    .sl-btn { justify-content: center; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const branchSelect = document.getElementById('branch_id');
    const academicYearSelect = document.getElementById('academic_year_id');
    const classSelect = document.getElementById('class_id');
    const sectionSelect = document.getElementById('section_id');
    const studentSelect = document.getElementById('student_id');

    // Load classes when branch or academic year changes
    function loadClasses() {
        const branchId = branchSelect.value;
        const academicYearId = academicYearSelect.value;

        var params = [];
        if (branchId) params.push('branch_id=' + branchId);
        if (academicYearId) params.push('academic_year_id=' + academicYearId);

        if (params.length === 0) {
            // No filters - still load all classes
        }

        var url = '{{ route("admin.enrollments.api.classes") }}';
        var sep = url.indexOf('?') > -1 ? '&' : '?';
        if (params.length > 0) url += sep + params.join('&');

        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                classSelect.innerHTML = '<option value="">-- Select Class --</option>';
                var classes = data.classes || data || [];
                classes.forEach(function(cls) {
                    var opt = document.createElement('option');
                    opt.value = cls.id;
                    opt.textContent = cls.name;
                    classSelect.appendChild(opt);
                });
                sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
            })
            .catch(function(err) { console.error('Error loading classes:', err); });
    }

    // Load sections when class changes
    function loadSections() {
        var classId = classSelect.value;

        if (!classId) {
            sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
            return;
        }

        fetch('{{ route("admin.enrollments.api.sections") }}?class_id=' + classId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
                var sections = data.sections || data || [];
                sections.forEach(function(sec) {
                    var opt = document.createElement('option');
                    opt.value = sec.id;
                    opt.textContent = sec.name;
                    sectionSelect.appendChild(opt);
                });
            })
            .catch(function(err) { console.error('Error loading sections:', err); });
    }

    // Load unenrolled students when academic year changes
    function loadUnenrolledStudents() {
        var academicYearId = academicYearSelect.value;
        var branchId = branchSelect.value;

        if (!academicYearId) return;

        var url = '{{ route("admin.enrollments.api.unenrolled-students") }}?academic_year_id=' + academicYearId;
        if (branchId) url += '&branch_id=' + branchId;

        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                studentSelect.innerHTML = '<option value="">-- Select Student --</option>';
                var students = data.students || data || [];
                students.forEach(function(stu) {
                    var opt = document.createElement('option');
                    opt.value = stu.id;
                    opt.textContent = stu.full_name + ' (' + stu.admission_number + ')';
                    studentSelect.appendChild(opt);
                });
            })
            .catch(function(err) { console.error('Error loading students:', err); });
    }

    branchSelect.addEventListener('change', function() { loadClasses(); loadUnenrolledStudents(); });
    academicYearSelect.addEventListener('change', function() { loadClasses(); loadUnenrolledStudents(); });
    classSelect.addEventListener('change', loadSections);
});
</script>
@endpush
@endsection