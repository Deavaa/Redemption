@extends('layouts.admin')
@section('page-title', 'Edit Student')
@section('content')
    <div class="container-fluid py-1">
        <ul class="nav nav-tabs" id="studentTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button"
                    role="tab" aria-controls="personal" aria-selected="true">Personal Info</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic" type="button"
                    role="tab" aria-controls="academic" aria-selected="false">Academic Info</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guardian-tab" data-bs-toggle="tab" data-bs-target="#guardian" type="button"
                    role="tab" aria-controls="guardian" aria-selected="false">Guardian Info</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments" type="button"
                    role="tab" aria-controls="comments" aria-selected="false">Comments</button>
            </li>
        </ul>

        <form action="{{ route('admin.students.update', ['student' => $data->id]) }}" method="POST"
            enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="tab-content" id="studentTabContent">
                <!-- Personal Info Tab -->
                <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-person me-2 text-primary"></i>Personal Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-start">
                                <div class="col-lg-9">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Full Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="full_name"
                                                class="form-control form-control-sm @error('full_name') is-invalid @enderror"
                                                value="{{ old('full_name', $data->first_name . ' ' . $data->last_name) }}"
                                                required>
                                            @error('full_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Date of Birth</label>
                                            <input type="date" id="dob" name="date_of_birth"
                                                class="form-control form-control-sm @error('date_of_birth') is-invalid @enderror"
                                                value="{{ old('date_of_birth', $data->date_of_birth) }}">
                                            @error('date_of_birth')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="mt-1">
                                                <small>Ethiopian Date: <span id="ethioDob"></span></small> | <small>Age:
                                                    <span id="ageDob"></span></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Phone</label>
                                            <input type="tel" name="phone"
                                                class="form-control form-control-sm @error('phone') is-invalid @enderror"
                                                value="{{ old('phone', $data->phone) }}">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Gender</label>
                                            <select name="gender"
                                                class="form-select form-select-sm @error('gender') is-invalid @enderror">
                                                <option value="">Select Gender</option>
                                                <option value="male"
                                                    {{ old('gender', $data->gender) == 'male' ? 'selected' : '' }}>Male
                                                </option>
                                                <option value="female"
                                                    {{ old('gender', $data->gender) == 'female' ? 'selected' : '' }}>Female
                                                </option>
                                            </select>
                                            @error('gender')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold">Address</label>
                                            <textarea name="address" class="form-control form-control-sm @error('address') is-invalid @enderror" rows="2">{{ old('address', $data->address) }}</textarea>
                                            @error('address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body text-center">
                                            <img id="photoPreview"
                                                src="{{ $data->photo ? asset('storage/' . $data->photo) : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgZmlsbD0iI2NjYyIvPjx0ZXh0IHg9IjYwIiB5PSI2MCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzk5OSI+U3R1ZGVudDwvdGV4dD48L3N2Zz4=' }}"
                                                alt="Student Photo" class="img-fluid rounded mb-3"
                                                style="max-height: 260px; width: auto;">
                                            <div class="mb-2 fw-semibold">Student Photo</div>
                                            <input type="file" name="photo" id="photoInput"
                                                class="form-control form-control-sm @error('photo') is-invalid @enderror"
                                                accept="image/*">
                                            @error('photo')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Info Tab -->
                <div class="tab-pane fade" id="academic" role="tabpanel" aria-labelledby="academic-tab">
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-mortarboard me-2 text-warning"></i>Academic
                                Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Branch <span
                                            class="text-danger">*</span></label>
                                    <select name="branch_id"
                                        class="form-select form-select-sm @error('branch_id') is-invalid @enderror"
                                        required>
                                        <option value="">Select Branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ old('branch_id', $data->branch_id) == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Academic Year <span
                                            class="text-danger">*</span></label>
                                    <select name="academic_year_id"
                                        class="form-select form-select-sm @error('academic_year_id') is-invalid @enderror"
                                        required>
                                        <option value="">Select Academic Year</option>
                                        @foreach ($academicYears as $year)
                                            <option value="{{ $year->id }}"
                                                {{ old('academic_year_id', $data->academic_year_id) == $year->id ? 'selected' : '' }}>
                                                {{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Admission Number</label>
                                    <input type="text" name="admission_number"
                                        class="form-control form-control-sm @error('admission_number') is-invalid @enderror"
                                        value="{{ old('admission_number', $data->admission_number) }}"
                                        placeholder="Auto-generated" readonly>
                                    <small class="text-muted">Auto-generated</small>
                                    @error('admission_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Class / Grade & Section <span
                                            class="text-danger">*</span></label>
                                    <select name="section_id" id="section"
                                        class="form-select form-select-sm @error('section_id') is-invalid @enderror"
                                        data-enable-search required>
                                        <option value="">Select Class - Section</option>
                                        @foreach ($classrooms as $class)
                                            @foreach ($class->sections as $section)
                                                <option value="{{ $section->id }}"
                                                    {{ old('section_id', $data->section_id) == $section->id ? 'selected' : '' }}>
                                                    {{ $class->name }} - {{ $section->name }}
                                                    ({{ $class->branch->name ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                    @error('section_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Select class and section</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Roll Number</label>
                                    <input type="text" name="roll_number"
                                        class="form-control form-control-sm @error('roll_number') is-invalid @enderror"
                                        value="{{ old('roll_number', $data->roll_number) }}" placeholder="Auto-generated"
                                        readonly>
                                    <small class="text-muted">Auto-generated</small>
                                    @error('roll_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Admission Date</label>
                                    <input type="date" id="admission_date" name="admission_date"
                                        class="form-control form-control-sm @error('admission_date') is-invalid @enderror"
                                        value="{{ old('admission_date', $data->admission_date) }}">
                                    @error('admission_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="mt-1">
                                        <small>Ethiopian Date: <span id="ethioAdmission"></span></small> |
                                        <small>Age:
                                            <span id="ageAdmission"></span></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Status</label>
                                    <select name="status"
                                        class="form-select form-select-sm @error('status') is-invalid @enderror">
                                        <option value="active"
                                            {{ old('status', $data->status) == 'active' ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="inactive"
                                            {{ old('status', $data->status) == 'inactive' ? 'selected' : '' }}>
                                            Inactive</option>
                                        <option value="graduated"
                                            {{ old('status', $data->status) == 'graduated' ? 'selected' : '' }}>
                                            Graduated</option>
                                        <option value="transferred"
                                            {{ old('status', $data->status) == 'transferred' ? 'selected' : '' }}>
                                            Transferred</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Notes</label>
                                    <textarea name="notes" class="form-control form-control-sm @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $data->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guardian Info Tab -->
                <div class="tab-pane fade" id="guardian" role="tabpanel" aria-labelledby="guardian-tab">
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-shield-lock me-2 text-info"></i>Guardian
                                Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Guardian Name</label>
                                    <div class="input-group">
                                        <select id="guardian_select"
                                            class="form-select form-select-sm @error('guardian_name') is-invalid @enderror"
                                            data-enable-search>
                                            <option value="">Search or select existing parent</option>
                                            @foreach ($parents as $parent)
                                                <option value="{{ $parent->id }}" data-phone="{{ $parent->phone }}"
                                                    {{ old('guardian_id', $data->parents->first()?->id ?? '') == $parent->id ? 'selected' : '' }}>
                                                    {{ $parent->name }} ({{ $parent->phone }})</option>
                                            @endforeach
                                            <option value="new">+ Add New Parent</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="addParentBtn"
                                            data-bs-toggle="modal" data-bs-target="#addParentModal">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="guardian_name" id="guardian_name"
                                        value="{{ old('guardian_name', $data->parents->first()?->name ?? '') }}">
                                    <input type="hidden" name="guardian_id" id="guardian_id"
                                        value="{{ old('guardian_id', $data->parents->first()?->id ?? '') }}">
                                    @error('guardian_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Select an existing parent or add a new one.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Guardian Phone</label>
                                    <input type="tel" name="guardian_phone" id="guardian_phone"
                                        class="form-control form-control-sm @error('guardian_phone') is-invalid @enderror"
                                        value="{{ old('guardian_phone', $data->parents->first()?->phone ?? '') }}">
                                    @error('guardian_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comments Tab -->
                <div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-chat-left-text me-2 text-secondary"></i>Comments
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Teacher Comments</label>
                                <textarea class="form-control" rows="3" readonly>{{ $data->teacher_comments ?? 'No comments' }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Admin Staff Comments</label>
                                <textarea class="form-control" rows="3" readonly>{{ $data->admin_comments ?? 'No comments' }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Add New Comment (Optional)</label>
                                <textarea name="new_comment" class="form-control" rows="3" placeholder="Add a new comment...">{{ old('new_comment') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-warning btn-sm px-4"><i class="bi bi-check-lg me-1"></i>Update
                    Student</button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary btn-sm px-4">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dobInput = document.getElementById('dob');
            const admissionInput = document.getElementById('admission_date');
            const ethioDobSpan = document.getElementById('ethioDob');
            const ageDobSpan = document.getElementById('ageDob');
            const ethioAdmissionSpan = document.getElementById('ethioAdmission');
            const ageAdmissionSpan = document.getElementById('ageAdmission');

            function convertToEthiopian(dateStr) {
                if (!dateStr) return '';
                try {
                    const g = new Date(dateStr);
                    const gYear = g.getFullYear();
                    const gMonth = g.getMonth() + 1;
                    const gDay = g.getDate();
                    // Ethiopian new year is September 11
                    const newYearGregorian = new Date(gYear, 8, 11); // September 11 (month 8 is September)
                    let eYear;
                    let daysSinceNewYear;
                    if (g >= newYearGregorian) {
                        eYear = gYear - 7;
                        daysSinceNewYear = Math.floor((g - newYearGregorian) / (1000 * 60 * 60 * 24));
                    } else {
                        eYear = gYear - 8;
                        const prevNewYear = new Date(gYear - 1, 8, 11);
                        daysSinceNewYear = Math.floor((g - prevNewYear) / (1000 * 60 * 60 * 24));
                    }
                    // Ethiopian months: 12 months of 30 days, 13th month 5 or 6 days
                    let eMonth = Math.floor(daysSinceNewYear / 30) + 1;
                    let eDay = (daysSinceNewYear % 30) + 1;
                    if (eMonth > 13) {
                        eMonth = 13;
                        eDay = daysSinceNewYear - 360;
                    }
                    return `${eYear}/${eMonth.toString().padStart(2, '0')}/${eDay.toString().padStart(2, '0')}`;
                } catch (e) {
                    console.error('Ethiopian date conversion error:', e);
                    return '';
                }
            }

            function calculateAge(dateStr) {
                if (!dateStr) return '';
                const birthDate = new Date(dateStr);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                return age >= 0 ? age : '';
            }

            function updateDob() {
                if (dobInput && ethioDobSpan && ageDobSpan) {
                    if (dobInput.value) {
                        ethioDobSpan.textContent = convertToEthiopian(dobInput.value);
                        ageDobSpan.textContent = calculateAge(dobInput.value);
                    } else {
                        ethioDobSpan.textContent = '';
                        ageDobSpan.textContent = '';
                    }
                }
            }

            function updateAdmission() {
                if (admissionInput && ethioAdmissionSpan && ageAdmissionSpan) {
                    if (admissionInput.value) {
                        ethioAdmissionSpan.textContent = convertToEthiopian(admissionInput.value);
                        ageAdmissionSpan.textContent = calculateAge(admissionInput.value);
                    } else {
                        ethioAdmissionSpan.textContent = '';
                        ageAdmissionSpan.textContent = '';
                    }
                }
            }

            if (dobInput) {
                dobInput.addEventListener('change', updateDob);
                dobInput.addEventListener('input', updateDob);
            }
            if (admissionInput) {
                admissionInput.addEventListener('change', updateAdmission);
                admissionInput.addEventListener('input', updateAdmission);
            }

            // Run on page load
            updateDob();
            updateAdmission();
        });
    </script>

    <script>
        // Class-Section dependency and search + image preview
        document.addEventListener('DOMContentLoaded', function() {
            const classSelect = document.getElementById('class_grade');
            const sectionSelect = document.getElementById('section');
            const allSectionOptions = sectionSelect ? Array.from(sectionSelect.options) : [];

            if (classSelect && sectionSelect) {
                classSelect.addEventListener('change', function() {
                    const selectedClassId = this.value;
                    sectionSelect.innerHTML = '<option value="">Select Section</option>';
                    allSectionOptions.forEach(opt => {
                        if (opt.value && opt.dataset.classId === selectedClassId) {
                            sectionSelect.appendChild(opt.cloneNode(true));
                        }
                    });
                    enableSearch(sectionSelect);
                });
                enableSearch(classSelect);
                enableSearch(sectionSelect);
            } else if (sectionSelect) {
                enableSearch(sectionSelect);
            }

            function enableSearch(select) {
                if (!select || select.options.length <= 10) return;
                if (select.parentNode.querySelector('input[type="text"]')) return;
                const wrapper = document.createElement('div');
                wrapper.className = 'position-relative';
                const searchInput = document.createElement('input');
                searchInput.type = 'text';
                searchInput.className = 'form-control form-control-sm mb-1';
                searchInput.placeholder = 'Search...';
                select.parentNode.insertBefore(wrapper, select);
                wrapper.appendChild(searchInput);
                wrapper.appendChild(select);
                searchInput.addEventListener('input', function() {
                    const filter = this.value.toLowerCase();
                    Array.from(select.options).forEach(option => {
                        option.style.display = option.text.toLowerCase().includes(filter) ? '' :
                            'none';
                    });
                });
            }

            const photoInput = document.getElementById('photoInput');
            const photoPreview = document.getElementById('photoPreview');
            if (photoInput && photoPreview) {
                photoInput.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            photoPreview.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        photoPreview.src =
                            'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgZmlsbD0iI2NjYyIvPjx0ZXh0IHg9IjYwIiB5PSI2MCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzk5OSI+U3R1ZGVudDwvdGV4dD48L3N2Zz4=';
                    }
                });
            }
        });
    </script>

    <div class="modal fade" id="addParentModal" tabindex="-1" aria-labelledby="addParentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addParentModalLabel">Add New Parent/Guardian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newParentForm">
                        @csrf
                        <div class="mb-1">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="new_parent_name" required>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="new_parent_phone" required>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Email (Optional)</label>
                            <input type="email" class="form-control" id="new_parent_email">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Address (Optional)</label>
                            <textarea class="form-control" id="new_parent_address" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveParentBtn">Save Parent</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const guardianSelect = document.getElementById('guardian_select');
            const guardianNameInput = document.getElementById('guardian_name');
            const guardianIdInput = document.getElementById('guardian_id');
            const guardianPhoneInput = document.getElementById('guardian_phone');
            const saveParentBtn = document.getElementById('saveParentBtn');

            if (guardianSelect) {
                guardianSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.value === 'new') {
                        const modal = new bootstrap.Modal(document.getElementById('addParentModal'));
                        modal.show();
                        this.selectedIndex = 0;
                    } else if (selectedOption.value) {
                        guardianNameInput.value = selectedOption.text.split(' (')[0];
                        guardianIdInput.value = selectedOption.value;
                        guardianPhoneInput.value = selectedOption.dataset.phone || '';
                    } else {
                        guardianNameInput.value = '';
                        guardianIdInput.value = '';
                        guardianPhoneInput.value = '';
                    }
                });
            }

            if (saveParentBtn) {
                saveParentBtn.addEventListener('click', function() {
                    const name = document.getElementById('new_parent_name').value;
                    const phone = document.getElementById('new_parent_phone').value;
                    if (!name || !phone) {
                        alert('Name and phone are required');
                        return;
                    }
                    const newOption = document.createElement('option');
                    newOption.value = 'temp';
                    newOption.text = name + ' (' + phone + ')';
                    newOption.dataset.phone = phone;
                    guardianSelect.insertBefore(newOption, guardianSelect.lastElementChild);
                    guardianSelect.selectedIndex = guardianSelect.options.length - 2;
                    guardianSelect.dispatchEvent(new Event('change'));
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addParentModal'));
                    modal.hide();
                    document.getElementById('newParentForm').reset();
                });
            }
        });
    </script>
@endsection
