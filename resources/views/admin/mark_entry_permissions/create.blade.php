@extends('layouts.admin')
@section('title', 'Grant Mark Edit Permission')

@push('styles')
<style>
/* ===== GRANT MARK ENTRY PERMISSION - MODERN DESIGN ===== */
.gmp-page { animation: gmpFadeIn 0.4s ease-out; }
@keyframes gmpFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

/* Page Header */
.gmp-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.gmp-header-left { flex: 1; }
.gmp-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.gmp-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }

/* Breadcrumb */
.gmp-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.gmp-breadcrumb li { color: #adb5bd; }
.gmp-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.gmp-breadcrumb li a:hover { color: #4361ee; }
.gmp-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.gmp-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Card */
.gmp-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.5rem; }
.gmp-card-head { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: #fafbfc; }
.gmp-card-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.gmp-card-icon.blue { background: #eef2ff; color: #4361ee; }
.gmp-card-icon.green { background: #ecfdf5; color: #10b981; }
.gmp-card-icon.amber { background: #fffbeb; color: #f59e0b; }
.gmp-card-icon.purple { background: #f5f3ff; color: #8b5cf6; }
.gmp-card-title { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.gmp-card-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.1rem 0 0; }
.gmp-card-body { padding: 1.5rem; }

/* Form Grid */
.gmp-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
.gmp-form-grid-3 { grid-template-columns: repeat(3, 1fr); }
.gmp-form-group { display: flex; flex-direction: column; }
.gmp-form-group.full-width { grid-column: 1 / -1; }
.gmp-form-label { font-weight: 600; color: #374151; margin-bottom: 0.45rem; font-size: 0.88rem; }
.gmp-form-label .required { color: #ef4444; margin-left: 2px; }

/* Selects */
.gmp-select { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.65rem 2.2rem 0.65rem 0.85rem; font-size: 0.88rem; color: #1a1a2e; background: #fff; appearance: none; cursor: pointer; transition: all 0.2s; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.6rem center; background-repeat: no-repeat; background-size: 1.15rem; }
.gmp-select:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.gmp-select:disabled { background: #f9fafb; color: #9ca3af; cursor: not-allowed; opacity: 0.7; }

/* Textarea */
.gmp-textarea { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #1a1a2e; resize: vertical; min-height: 90px; transition: all 0.2s; font-family: inherit; }
.gmp-textarea:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.gmp-textarea::placeholder { color: #9ca3af; }

/* Input */
.gmp-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.65rem 0.85rem; font-size: 0.88rem; color: #1a1a2e; transition: all 0.2s; }
.gmp-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }

/* Multi-Select Student Picker */
.gmp-student-picker { border: 1.5px solid #e5e7eb; border-radius: 10px; overflow: hidden; transition: all 0.2s; }
.gmp-student-picker:focus-within { border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.gmp-student-search { width: 100%; border: none; border-bottom: 1px solid #f0f0f0; padding: 0.6rem 0.85rem; font-size: 0.85rem; color: #1a1a2e; outline: none; }
.gmp-student-search::placeholder { color: #9ca3af; }
.gmp-student-list { max-height: 220px; overflow-y: auto; padding: 0.5rem; }
.gmp-student-list::-webkit-scrollbar { width: 5px; }
.gmp-student-list::-webkit-scrollbar-track { background: #f9fafb; }
.gmp-student-list::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
.gmp-student-item { display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.5rem; border-radius: 6px; cursor: pointer; transition: background 0.15s; font-size: 0.85rem; color: #374151; }
.gmp-student-item:hover { background: #f0f4ff; }
.gmp-student-item input[type="checkbox"] { width: 16px; height: 16px; accent-color: #4361ee; cursor: pointer; flex-shrink: 0; }
.gmp-student-item .student-name { font-weight: 500; }
.gmp-student-item .student-adm { font-size: 0.75rem; color: #9ca3af; margin-left: auto; }
.gmp-student-empty { padding: 1.5rem; text-align: center; color: #9ca3af; font-size: 0.85rem; }
.gmp-student-empty i { display: block; font-size: 1.5rem; margin-bottom: 0.5rem; color: #d1d5db; }
.gmp-selected-count { font-size: 0.78rem; color: #4361ee; font-weight: 600; margin-top: 0.3rem; display: none; }
.gmp-selected-count.visible { display: block; }

/* Alert */
.gmp-alert { border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.9rem; line-height: 1.55; }
.gmp-alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
.gmp-alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
.gmp-alert i:first-child { font-size: 1.1rem; margin-top: 0.1rem; flex-shrink: 0; }

/* Buttons */
.gmp-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.gmp-btn-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.3); }
.gmp-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.4); color: #fff; }
.gmp-btn-outline { background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb; }
.gmp-btn-outline:hover { border-color: #4361ee; color: #4361ee; background: #f8f9ff; }

/* Form Actions */
.gmp-form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #f0f0f0; }

/* Section Divider */
.gmp-section-divider { display: flex; align-items: center; gap: 0.75rem; margin: 1.5rem 0 1.25rem; font-size: 0.82rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
.gmp-section-divider::before, .gmp-section-divider::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }
.gmp-section-divider i { color: #4361ee; }

/* Loading Spinner */
.gmp-spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid #e5e7eb; border-top-color: #4361ee; border-radius: 50%; animation: gmpSpin 0.6s linear infinite; margin-right: 0.35rem; vertical-align: middle; }
@keyframes gmpSpin { to { transform: rotate(360deg); } }

/* Error Styling */
.gmp-has-error { border-color: #ef4444 !important; }
.gmp-has-error:focus { box-shadow: 0 0 0 3px rgba(239,68,68,0.1) !important; }
.gmp-error-text { font-size: 0.78rem; color: #ef4444; margin-top: 0.25rem; }

/* Responsive */
@media (max-width: 992px) {
    .gmp-form-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 768px) {
    .gmp-header { flex-direction: column; align-items: stretch; }
    .gmp-title { font-size: 1.35rem; }
    .gmp-form-grid, .gmp-form-grid-3 { grid-template-columns: 1fr; }
    .gmp-form-actions { flex-direction: column; }
}
</style>
@endpush

@section('content')
<div class="gmp-page">
    {{-- Page Header --}}
    <div class="gmp-header">
        <div class="gmp-header-left">
            <nav aria-label="breadcrumb" class="gmp-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.mark-entry-permissions.index') }}">Mark Entry Permissions</a></li>
                    <li class="active">Grant Permission</li>
                </ol>
            </nav>
            <h1 class="gmp-title">Grant Mark Edit Permission</h1>
            <p class="gmp-subtitle">Allow a teacher to edit a specific student's marks when entry is locked</p>
        </div>
    </div>

    {{-- Info Alert --}}
    <div class="gmp-alert gmp-alert-info">
        <i class="fas fa-info-circle"></i>
        <span>Select a teacher, then choose their assigned subject, a class/section, and the student(s) who need mark edits. The teacher will only be able to edit marks for the selected student(s) in the chosen subject.</span>
    </div>

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="gmp-alert gmp-alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul style="margin: 0.35rem 0 0; padding-left: 1.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Grant Permission Form --}}
    <form method="POST" action="{{ route('admin.mark-entry-permissions.store') }}" id="permissionForm">
        @csrf

        {{-- Teacher & Subject Section --}}
        <div class="gmp-card">
            <div class="gmp-card-head">
                <div class="gmp-card-icon blue"><i class="fas fa-chalkboard-teacher"></i></div>
                <div>
                    <h3 class="gmp-card-title">Teacher & Subject</h3>
                    <p class="gmp-card-desc">Select a teacher and one of their assigned subjects</p>
                </div>
            </div>
            <div class="gmp-card-body">
                <div class="gmp-form-grid">
                    <div class="gmp-form-group">
                        <label class="gmp-form-label" for="teacherId">Teacher <span class="required">*</span></label>
                        <select id="teacherId" name="teacher_id" class="gmp-select" required>
                            <option value="">-- Select Teacher --</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->full_name }}</option>
                            @endforeach
                        </select>
                        @error('teacher_id') <div class="gmp-error-text">{{ $message }}</div> @enderror
                    </div>
                    <div class="gmp-form-group">
                        <label class="gmp-form-label" for="subjectId">Subject <span class="required">*</span></label>
                        <select id="subjectId" name="subject_id" class="gmp-select" required disabled>
                            <option value="">-- Select Teacher First --</option>
                        </select>
                        @error('subject_id') <div class="gmp-error-text">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Class, Section & Students Section --}}
        <div class="gmp-card">
            <div class="gmp-card-head">
                <div class="gmp-card-icon green"><i class="fas fa-users"></i></div>
                <div>
                    <h3 class="gmp-card-title">Class, Section & Students</h3>
                    <p class="gmp-card-desc">Choose the class and section, then select students who need mark edit access</p>
                </div>
            </div>
            <div class="gmp-card-body">
                <div class="gmp-form-grid">
                    <div class="gmp-form-group">
                        <label class="gmp-form-label" for="classId">Class <span class="required">*</span></label>
                        <select id="classId" class="gmp-select" required>
                            <option value="">-- Select Class --</option>
                            @foreach (\App\Models\Classroom::orderBy('name')->get() as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="gmp-form-group">
                        <label class="gmp-form-label" for="sectionId">Section <span class="required">*</span></label>
                        <select id="sectionId" class="gmp-select" required disabled>
                            <option value="">-- Select Class First --</option>
                        </select>
                    </div>
                </div>

                {{-- Students Multi-Select --}}
                <div class="gmp-section-divider">
                    <i class="fas fa-user-graduate"></i> Select Students
                </div>
                <div class="gmp-form-group">
                    <div class="gmp-student-picker" id="studentPicker">
                        <input type="text" class="gmp-student-search" id="studentSearch" placeholder="Search students by name or admission number..." disabled>
                        <div class="gmp-student-list" id="studentList">
                            <div class="gmp-student-empty">
                                <i class="fas fa-arrow-up"></i>
                                Select a class and section above to load students
                            </div>
                        </div>
                    </div>
                    <div class="gmp-selected-count" id="selectedCount">0 student(s) selected</div>
                    <small style="color: #9ca3af; font-size: 0.78rem; margin-top: 0.3rem; display: block;">
                        A separate permission will be created for each selected student.
                    </small>
                </div>
            </div>
        </div>

        {{-- Academic Year, Term, Reason & Expiry Section --}}
        <div class="gmp-card">
            <div class="gmp-card-head">
                <div class="gmp-card-icon amber"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <h3 class="gmp-card-title">Academic Period & Justification</h3>
                    <p class="gmp-card-desc">Set the academic period, reason for granting access, and optional expiry</p>
                </div>
            </div>
            <div class="gmp-card-body">
                <div class="gmp-form-grid">
                    <div class="gmp-form-group">
                        <label class="gmp-form-label" for="academicYearId">Academic Year <span class="required">*</span></label>
                        <select id="academicYearId" name="academic_year_id" class="gmp-select" required>
                            <option value="">-- Select Academic Year --</option>
                            @foreach ($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ ($currentAy && $currentAy->id == $ay->id) || old('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                        @error('academic_year_id') <div class="gmp-error-text">{{ $message }}</div> @enderror
                    </div>
                    <div class="gmp-form-group">
                        <label class="gmp-form-label" for="termId">Term <span class="required">*</span></label>
                        <select id="termId" name="term_id" class="gmp-select" required>
                            <option value="">-- Select Term --</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                            @endforeach
                        </select>
                        @error('term_id') <div class="gmp-error-text">{{ $message }}</div> @enderror
                    </div>
                    <div class="gmp-form-group full-width">
                        <label class="gmp-form-label" for="reason">Reason <span class="required">*</span></label>
                        <textarea id="reason" name="reason" class="gmp-textarea" placeholder="Explain why this teacher needs special access to edit marks (e.g., Correction needed for report card, data entry error discovered, etc.)..." required>{{ old('reason') }}</textarea>
                        @error('reason') <div class="gmp-error-text">{{ $message }}</div> @enderror
                    </div>
                    <div class="gmp-form-group">
                        <label class="gmp-form-label" for="expiresAt">Expires At <small style="color: #9ca3af;">(optional)</small></label>
                        <input type="datetime-local" id="expiresAt" name="expires_at" class="gmp-input" value="{{ old('expires_at') }}">
                        <small style="color: #9ca3af; font-size: 0.78rem; margin-top: 0.3rem; display: block;">
                            Leave empty for indefinite access. If set, the permission will automatically expire.
                        </small>
                        @error('expires_at') <div class="gmp-error-text">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="gmp-form-actions">
                    <a href="{{ route('admin.mark-entry-permissions.index') }}" class="gmp-btn gmp-btn-outline">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="gmp-btn gmp-btn-primary" id="submitBtn">
                        <i class="fas fa-key"></i> Grant Permission
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var teacherSelect = document.getElementById('teacherId');
    var subjectSelect = document.getElementById('subjectId');
    var classSelect = document.getElementById('classId');
    var sectionSelect = document.getElementById('sectionId');
    var studentSearch = document.getElementById('studentSearch');
    var studentList = document.getElementById('studentList');
    var selectedCountEl = document.getElementById('selectedCount');
    var academicYearSelect = document.getElementById('academicYearId');
    var termSelect = document.getElementById('termId');
    var permissionForm = document.getElementById('permissionForm');
    var submitBtn = document.getElementById('submitBtn');

    var selectedStudentIds = [];
    var allStudents = [];

    // ============================
    // TEACHER -> SUBJECTS (AJAX)
    // ============================
    if (teacherSelect) {
        teacherSelect.addEventListener('change', function() {
            var teacherId = this.value;
            subjectSelect.innerHTML = '<option value="">Loading...</option>';
            subjectSelect.disabled = true;

            if (!teacherId) {
                subjectSelect.innerHTML = '<option value="">-- Select Teacher First --</option>';
                return;
            }

            fetch('{{ route("admin.mark-entry-permissions.api.teacher-subjects") }}?teacher_id=' + encodeURIComponent(teacherId), {
                credentials: 'same-origin'
            })
            .then(function(r) {
                if (!r.ok) throw new Error('Failed to load subjects');
                return r.json();
            })
            .then(function(subjects) {
                subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
                if (Array.isArray(subjects) && subjects.length > 0) {
                    subjects.forEach(function(s) {
                        var opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = s.name;
                        subjectSelect.appendChild(opt);
                    });
                    subjectSelect.disabled = false;
                    // Auto-select if only one subject
                    if (subjects.length === 1) {
                        subjectSelect.value = subjects[0].id;
                    }
                } else {
                    subjectSelect.innerHTML = '<option value="">No subjects assigned to this teacher</option>';
                }
            })
            .catch(function(err) {
                console.error('Error loading subjects:', err);
                subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
            });
        });
    }

    // ============================
    // CLASS -> SECTIONS (AJAX)
    // ============================
    if (classSelect) {
        classSelect.addEventListener('change', function() {
            var classId = this.value;
            sectionSelect.innerHTML = '<option value="">Loading...</option>';
            sectionSelect.disabled = true;
            clearStudents();

            if (!classId) {
                sectionSelect.innerHTML = '<option value="">-- Select Class First --</option>';
                return;
            }

            fetch('{{ route("admin.mark-entries.api.sections") }}?class_id=' + encodeURIComponent(classId), {
                credentials: 'same-origin'
            })
            .then(function(r) {
                if (!r.ok) throw new Error('Failed to load sections');
                return r.json();
            })
            .then(function(data) {
                sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';

                // The API might return an array of section objects or section names
                var sections = Array.isArray(data) ? data : (data.sections || []);

                if (sections.length > 0) {
                    sections.forEach(function(s) {
                        var opt = document.createElement('option');
                        if (typeof s === 'object') {
                            opt.value = s.id || s.section_id || s.name;
                            opt.textContent = s.name || s.section_name || s;
                            // Store class_id if returned with section
                            if (s.class_id) opt.dataset.classId = s.class_id;
                        } else {
                            opt.value = s;
                            opt.textContent = s;
                        }
                        sectionSelect.appendChild(opt);
                    });
                    sectionSelect.disabled = false;
                } else {
                    sectionSelect.innerHTML = '<option value="">No sections found for this class</option>';
                }
            })
            .catch(function(err) {
                console.error('Error loading sections:', err);
                sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
            });
        });
    }

    // ============================
    // SECTION -> STUDENTS (AJAX)
    // ============================
    if (sectionSelect) {
        sectionSelect.addEventListener('change', function() {
            var sectionId = this.value;
            clearStudents();

            if (!sectionId) {
                renderEmptyStudents('Select a section above to load students');
                return;
            }

            studentSearch.disabled = true;
            renderEmptyStudents('<span class="gmp-spinner"></span> Loading students...');

            fetch('{{ route("admin.mark-entry-permissions.api.students") }}?class_id=' + encodeURIComponent(sectionId), {
                credentials: 'same-origin'
            })
            .then(function(r) {
                if (!r.ok) throw new Error('Failed to load students');
                return r.json();
            })
            .then(function(students) {
                allStudents = Array.isArray(students) ? students : [];
                studentSearch.disabled = allStudents.length === 0;

                if (allStudents.length === 0) {
                    renderEmptyStudents('No active students found for this section');
                    return;
                }

                renderStudents(allStudents);
            })
            .catch(function(err) {
                console.error('Error loading students:', err);
                renderEmptyStudents('Error loading students. Please try again.');
            });
        });
    }

    // ============================
    // ACADEMIC YEAR -> TERMS (AJAX)
    // ============================
    if (academicYearSelect) {
        academicYearSelect.addEventListener('change', function() {
            var ayId = this.value;
            termSelect.innerHTML = '<option value="">Loading...</option>';

            if (!ayId) {
                termSelect.innerHTML = '<option value="">-- Select Academic Year First --</option>';
                return;
            }

            fetch('{{ route("admin.mark-entries.api.terms") }}?academic_year_id=' + encodeURIComponent(ayId), {
                credentials: 'same-origin'
            })
            .then(function(r) {
                if (!r.ok) throw new Error('Failed to load terms');
                return r.json();
            })
            .then(function(terms) {
                termSelect.innerHTML = '<option value="">-- Select Term --</option>';
                if (Array.isArray(terms)) {
                    terms.forEach(function(t) {
                        var opt = document.createElement('option');
                        opt.value = t.id;
                        opt.textContent = t.name;
                        termSelect.appendChild(opt);
                    });
                    // Auto-select first/active term if only one
                    if (terms.length === 1) {
                        termSelect.value = terms[0].id;
                    }
                }
            })
            .catch(function(err) {
                console.error('Error loading terms:', err);
                termSelect.innerHTML = '<option value="">Error loading terms</option>';
            });
        });
    }

    // ============================
    // STUDENT SEARCH FILTER
    // ============================
    if (studentSearch) {
        studentSearch.addEventListener('input', function() {
            var query = this.value.toLowerCase().trim();
            if (!query) {
                renderStudents(allStudents);
                return;
            }
            var filtered = allStudents.filter(function(s) {
                var name = (s.first_name || '') + ' ' + (s.last_name || '');
                name = name.toLowerCase();
                var adm = (s.admission_number || s.roll_number || '').toLowerCase();
                return name.indexOf(query) !== -1 || adm.indexOf(query) !== -1;
            });
            renderStudents(filtered);
        });
    }

    // ============================
    // RENDER STUDENTS
    // ============================
    function renderStudents(students) {
        studentList.innerHTML = '';
        if (students.length === 0) {
            renderEmptyStudents('No students match your search');
            return;
        }
        students.forEach(function(s) {
            var item = document.createElement('label');
            item.className = 'gmp-student-item';

            var studentName = [s.first_name, s.last_name].filter(Boolean).join(' ') || s.name || 'Student';
            var admNum = s.admission_number || s.roll_number || '';

            var checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = s.id;
            checkbox.name = 'student_ids[]';
            checkbox.checked = selectedStudentIds.indexOf(s.id) !== -1;

            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    if (selectedStudentIds.indexOf(s.id) === -1) {
                        selectedStudentIds.push(s.id);
                    }
                } else {
                    selectedStudentIds = selectedStudentIds.filter(function(id) { return id != s.id; });
                }
                updateSelectedCount();
            });

            var nameSpan = document.createElement('span');
            nameSpan.className = 'student-name';
            nameSpan.textContent = studentName;

            item.appendChild(checkbox);

            if (admNum) {
                var admSpan = document.createElement('span');
                admSpan.className = 'student-adm';
                admSpan.textContent = admNum;
                item.appendChild(nameSpan);
                item.appendChild(admSpan);
            } else {
                item.appendChild(nameSpan);
            }

            studentList.appendChild(item);
        });
        updateSelectedCount();
    }

    function renderEmptyStudents(message) {
        studentList.innerHTML = '<div class="gmp-student-empty">' + message + '</div>';
    }

    function clearStudents() {
        allStudents = [];
        selectedStudentIds = [];
        studentList.innerHTML = '<div class="gmp-student-empty"><i class="fas fa-arrow-up"></i>Select a class and section above to load students</div>';
        studentSearch.disabled = true;
        studentSearch.value = '';
        updateSelectedCount();
    }

    function updateSelectedCount() {
        var count = selectedStudentIds.length;
        selectedCountEl.textContent = count + ' student(s) selected';
        selectedCountEl.className = count > 0 ? 'gmp-selected-count visible' : 'gmp-selected-count';
    }

    // ============================
    // FORM SUBMISSION
    // ============================
    if (permissionForm) {
        permissionForm.addEventListener('submit', function(e) {
            var teacherId = teacherSelect.value;
            var subjectId = subjectSelect.value;
            var classId = classSelect.value;
            var sectionId = sectionSelect.value;
            var academicYearId = academicYearSelect.value;
            var termId = termSelect.value;
            var reason = document.getElementById('reason').value.trim();

            // Basic validation
            var errors = [];
            if (!teacherId) errors.push('Please select a teacher.');
            if (!subjectId) errors.push('Please select a subject.');
            if (!sectionId) errors.push('Please select a section.');
            if (!academicYearId) errors.push('Please select an academic year.');
            if (!termId) errors.push('Please select a term.');
            if (!reason) errors.push('Please provide a reason.');
            if (selectedStudentIds.length === 0) errors.push('Please select at least one student.');

            if (errors.length > 0) {
                e.preventDefault();
                alert('Please fix the following:\n\n' + errors.join('\n'));
                return;
            }

            // Add hidden inputs for selected students
            selectedStudentIds.forEach(function(sid) {
                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'student_ids[]';
                hidden.value = sid;
                permissionForm.appendChild(hidden);
            });

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="gmp-spinner"></span> Granting...';
        });
    }

    // ============================
    // AUTO-LOAD TERMS ON AY CHANGE
    // ============================
    // If a current AY is pre-selected, load its terms
    if (academicYearSelect && academicYearSelect.value) {
        // Terms are already populated from the controller for the current AY
        // so no extra AJAX needed on initial load
    }
})();
</script>
@endpush
