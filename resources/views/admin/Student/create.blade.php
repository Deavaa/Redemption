@extends('layouts.admin')
@section('title', 'Add Student')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.students.index') }}">Students</a></li>
                    <li class="active">Add New</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.students.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="modern-card">
        {{-- Tab Navigation --}}
        <div class="modern-tabs">
            <button type="button" class="modern-tab active" data-tab="personal">
                <i class="fas fa-user"></i> Personal Info
            </button>
            <button type="button" class="modern-tab" data-tab="academic">
                <i class="fas fa-graduation-cap"></i> Academic Info
            </button>
            <button type="button" class="modern-tab" data-tab="guardian">
                <i class="fas fa-shield-alt"></i> Guardian Info
            </button>
            <button type="button" class="modern-tab" data-tab="comments">
                <i class="fas fa-comment-alt"></i> Comments
            </button>
        </div>

        <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Personal Info Tab --}}
            <div class="modern-tab-content active" id="tab-personal">
                <div class="modern-form-section">
                    <div class="modern-form-section-header">
                        <div class="modern-form-section-icon modern-form-section-icon-blue">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h3 class="modern-form-section-title">Personal Information</h3>
                            <p class="modern-form-section-desc">Enter the student's personal details and photo</p>
                        </div>
                    </div>
                    <div class="modern-form-section-body">
                        <div class="modern-form-layout">
                            {{-- Left: Form Fields --}}
                            <div class="modern-form-fields">
                                <div class="modern-form-grid">
                                    <div class="modern-form-group modern-form-span-2">
                                        <label class="modern-form-label" for="full_name">
                                            Full Name <span class="modern-required">*</span>
                                        </label>
                                        <div class="modern-input-wrapper">
                                            <i class="fas fa-user modern-input-icon"></i>
                                            <input type="text" name="full_name" id="full_name"
                                                class="modern-input {{ $errors->has('full_name') ? 'is-invalid' : '' }}"
                                                value="{{ old('full_name') }}"
                                                placeholder="e.g. Abebe Kebede" required autofocus>
                                        </div>
                                        @error('full_name')
                                            <span class="modern-form-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="modern-form-group">
                                        <label class="modern-form-label" for="dob">
                                            Date of Birth
                                        </label>
                                        <div class="modern-input-wrapper">
                                            <i class="fas fa-birthday-cake modern-input-icon"></i>
                                            <input type="date" id="dob" name="date_of_birth"
                                                class="modern-input {{ $errors->has('date_of_birth') ? 'is-invalid' : '' }}"
                                                value="{{ old('date_of_birth') }}">
                                        </div>
                                        <div class="modern-input-hint">
                                            Ethiopian: <span id="ethioDob">-</span> &middot; Age: <span id="ageDob">-</span>
                                        </div>
                                        @error('date_of_birth')
                                            <span class="modern-form-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="modern-form-group">
                                        <label class="modern-form-label" for="phone">
                                            Phone
                                        </label>
                                        <div class="modern-input-wrapper">
                                            <i class="fas fa-phone modern-input-icon"></i>
                                            <input type="tel" name="phone" id="phone"
                                                class="modern-input {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                                value="{{ old('phone') }}"
                                                placeholder="e.g. +251 91 234 5678">
                                        </div>
                                        @error('phone')
                                            <span class="modern-form-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="modern-form-group">
                                        <label class="modern-form-label" for="gender">
                                            Gender
                                        </label>
                                        <div class="modern-input-wrapper">
                                            <i class="fas fa-venus-mars modern-input-icon"></i>
                                            <select name="gender" id="gender"
                                                class="modern-input modern-select {{ $errors->has('gender') ? 'is-invalid' : '' }}">
                                                <option value="">Select Gender</option>
                                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                            </select>
                                        </div>
                                        @error('gender')
                                            <span class="modern-form-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="modern-form-group modern-form-span-2">
                                        <label class="modern-form-label" for="address">
                                            Address
                                        </label>
                                        <div class="modern-input-wrapper">
                                            <i class="fas fa-map-marker-alt modern-input-icon"></i>
                                            <textarea name="address" id="address"
                                                class="modern-input modern-textarea {{ $errors->has('address') ? 'is-invalid' : '' }}"
                                                placeholder="e.g. Bole, Addis Ababa"
                                                rows="3">{{ old('address') }}</textarea>
                                        </div>
                                        @error('address')
                                            <span class="modern-form-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Photo Upload --}}
                            <div class="modern-form-sidebar">
                                <div class="modern-photo-upload">
                                    <img id="photoPreview"
                                        src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgZmlsbD0iI2YzZjRmNiIvPjx0ZXh0IHg9IjYwIiB5PSI1MCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzliYTNhZiIgZm9udC1zaXplPSIyNCI+8J+OrTwvdGV4dD48dGV4dCB4PSI2MCIgeT0iNzUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiM5YmEzYWYiIGZvbnQtc2l6ZT0iMTAiPlN0dWRlbnQgUGhvdG88L3RleHQ+PC9zdmc+"
                                        alt="Student Photo" class="modern-photo-preview">
                                    <div class="modern-photo-label">Student Photo</div>
                                    <label for="photoInput" class="modern-photo-btn">
                                        <i class="fas fa-camera"></i> Choose Photo
                                    </label>
                                    <input type="file" name="photo" id="photoInput"
                                        class="modern-photo-file"
                                        accept="image/*">
                                    @error('photo')
                                        <span class="modern-form-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Academic Info Tab --}}
            <div class="modern-tab-content" id="tab-academic">
                <div class="modern-form-section">
                    <div class="modern-form-section-header">
                        <div class="modern-form-section-icon modern-form-section-icon-green">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h3 class="modern-form-section-title">Academic Information</h3>
                            <p class="modern-form-section-desc">Enrollment details, class assignment and academic year</p>
                        </div>
                    </div>
                    <div class="modern-form-section-body">
                        <div class="modern-form-grid">
                            <div class="modern-form-group">
                                <label class="modern-form-label" for="branch_id">
                                    Branch <span class="modern-required">*</span>
                                </label>
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-building modern-input-icon"></i>
                                    <select name="branch_id" id="branch_id"
                                        class="modern-input modern-select {{ $errors->has('branch_id') ? 'is-invalid' : '' }}"
                                        required>
                                        <option value="">Select Branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('branch_id')
                                    <span class="modern-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="modern-form-group">
                                <label class="modern-form-label" for="academic_year_id">
                                    Academic Year <span class="modern-required">*</span>
                                </label>
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-calendar-alt modern-input-icon"></i>
                                    <select name="academic_year_id" id="academic_year_id"
                                        class="modern-input modern-select {{ $errors->has('academic_year_id') ? 'is-invalid' : '' }}"
                                        required>
                                        <option value="">Select Academic Year</option>
                                        @if ($academicYears->isEmpty())
                                            <option value="1">2024-2025</option>
                                        @else
                                            @foreach ($academicYears as $year)
                                                <option value="{{ $year->id }}"
                                                    {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                    {{ $year->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @error('academic_year_id')
                                    <span class="modern-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="modern-form-group">
                                <label class="modern-form-label" for="section_id">
                                    Class / Grade & Section <span class="modern-required">*</span>
                                </label>
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-chalkboard modern-input-icon"></i>
                                    <select name="section_id" id="section"
                                        class="modern-input modern-select {{ $errors->has('section_id') ? 'is-invalid' : '' }}"
                                        required>
                                        <option value="">Select Class - Section</option>
                                        @foreach ($classrooms as $class)
                                            @foreach ($class->sections as $section)
                                                <option value="{{ $section->id }}"
                                                    {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                                    {{ $class->name }} - {{ $section->name }}
                                                    ({{ $class->branch->name ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                                <div class="modern-input-hint">Select class and section</div>
                                @error('section_id')
                                    <span class="modern-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="modern-form-group">
                                <label class="modern-form-label">
                                    Admission Number <small>(auto-assigned)</small>
                                </label>
                                <div class="auto-gen-field">
                                    <div class="auto-gen-badge" id="admissionNumberBadge">
                                        <i class="fas fa-id-badge"></i>
                                        <span>{{ $nextAdmissionNumber ?? 'YYYY-0001' }}</span>
                                    </div>
                                    <input type="hidden" name="admission_number" id="admission_number" value="{{ old('admission_number') }}">
                                </div>
                                <div class="modern-input-hint">Automatically assigned when student is saved</div>
                                @error('admission_number')
                                    <span class="modern-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="modern-form-group">
                                <label class="modern-form-label">
                                    Roll Number <small>(auto-assigned)</small>
                                </label>
                                <div class="auto-gen-field">
                                    <div class="auto-gen-badge auto-gen-badge-orange" id="rollNumberBadge">
                                        <i class="fas fa-hashtag"></i>
                                        <span id="rollNumberValue">Select class & section first</span>
                                    </div>
                                    <input type="hidden" name="roll_number" id="roll_number" value="{{ old('roll_number') }}">
                                </div>
                                <div class="modern-input-hint">Automatically assigned based on section</div>
                                @error('roll_number')
                                    <span class="modern-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="modern-form-group">
                                <label class="modern-form-label" for="admission_date">
                                    Admission Date
                                </label>
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-calendar-check modern-input-icon"></i>
                                    <input type="date" id="admission_date" name="admission_date"
                                        class="modern-input {{ $errors->has('admission_date') ? 'is-invalid' : '' }}"
                                        value="{{ old('admission_date') }}">
                                </div>
                                <div class="modern-input-hint">
                                    Ethiopian: <span id="ethioAdmission">-</span>
                                </div>
                                @error('admission_date')
                                    <span class="modern-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="modern-form-group">
                                <label class="modern-form-label" for="status">
                                    Status
                                </label>
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-info-circle modern-input-icon"></i>
                                    <select name="status" id="status"
                                        class="modern-input modern-select {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="graduated" {{ old('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                                        <option value="transferred" {{ old('status') == 'transferred' ? 'selected' : '' }}>Transferred</option>
                                    </select>
                                </div>
                                @error('status')
                                    <span class="modern-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="modern-form-group modern-form-span-2">
                                <label class="modern-form-label" for="notes">
                                    Notes <small>(optional)</small>
                                </label>
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-sticky-note modern-input-icon"></i>
                                    <textarea name="notes" id="notes"
                                        class="modern-input modern-textarea {{ $errors->has('notes') ? 'is-invalid' : '' }}"
                                        placeholder="Additional notes about the student..."
                                        rows="3">{{ old('notes') }}</textarea>
                                </div>
                                @error('notes')
                                    <span class="modern-form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Guardian Info Tab --}}
            <div class="modern-tab-content" id="tab-guardian">
                <div class="modern-form-section">
                    <div class="modern-form-section-header">
                        <div class="modern-form-section-icon modern-form-section-icon-purple">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h3 class="modern-form-section-title">Guardian Information</h3>
                            <p class="modern-form-section-desc">Parent or guardian contact details</p>
                        </div>
                    </div>
                    <div class="modern-form-section-body">
                        <div class="modern-form-grid">
                            <div class="modern-form-group">
                                <label class="modern-form-label" for="guardian_select">
                                    Guardian Name
                                </label>
                                <div class="modern-input-wrapper modern-input-wrapper-with-btn">
                                    <i class="fas fa-user-shield modern-input-icon"></i>
                                    <select id="guardian_select"
                                        class="modern-input modern-select {{ $errors->has('guardian_name') ? 'is-invalid' : '' }}">
                                        <option value="">Search or select existing parent</option>
                                        @foreach ($parents as $parent)
                                            <option value="{{ $parent->id }}" data-phone="{{ $parent->phone }}">
                                                {{ $parent->name }} ({{ $parent->phone }})</option>
                                        @endforeach
                                        <option value="new">+ Add New Parent</option>
                                    </select>
                                    <button type="button" class="modern-input-btn" id="addParentBtn"
                                        data-bs-toggle="modal" data-bs-target="#addParentModal">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="guardian_name" id="guardian_name"
                                    value="{{ old('guardian_name') }}">
                                <input type="hidden" name="guardian_id" id="guardian_id"
                                    value="{{ old('guardian_id') }}">
                                <div class="modern-input-hint">Select an existing parent or add a new one</div>
                                @error('guardian_name')
                                    <span class="modern-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="modern-form-group">
                                <label class="modern-form-label" for="guardian_phone">
                                    Guardian Phone
                                </label>
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-phone modern-input-icon"></i>
                                    <input type="tel" name="guardian_phone" id="guardian_phone"
                                        class="modern-input {{ $errors->has('guardian_phone') ? 'is-invalid' : '' }}"
                                        value="{{ old('guardian_phone') }}"
                                        placeholder="e.g. +251 91 234 5678">
                                </div>
                                @error('guardian_phone')
                                    <span class="modern-form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Comments Tab --}}
            <div class="modern-tab-content" id="tab-comments">
                <div class="modern-form-section">
                    <div class="modern-form-section-header">
                        <div class="modern-form-section-icon modern-form-section-icon-gray">
                            <i class="fas fa-comment-alt"></i>
                        </div>
                        <div>
                            <h3 class="modern-form-section-title">Comments</h3>
                            <p class="modern-form-section-desc">Teacher and admin staff comments</p>
                        </div>
                    </div>
                    <div class="modern-form-section-body">
                        <div class="modern-form-grid">
                            <div class="modern-form-group">
                                <label class="modern-form-label">Teacher Comments <small>(read only)</small></label>
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-chalkboard-teacher modern-input-icon"></i>
                                    <textarea class="modern-input modern-textarea" rows="3" readonly>No comments available for new students.</textarea>
                                </div>
                            </div>

                            <div class="modern-form-group">
                                <label class="modern-form-label">Admin Staff Comments <small>(read only)</small></label>
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-user-cog modern-input-icon"></i>
                                    <textarea class="modern-input modern-textarea" rows="3" readonly>No comments available for new students.</textarea>
                                </div>
                            </div>

                            <div class="modern-form-group modern-form-span-2">
                                <label class="modern-form-label" for="new_comment">
                                    Add Comment <small>(optional)</small>
                                </label>
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-pen modern-input-icon"></i>
                                    <textarea name="new_comment" id="new_comment"
                                        class="modern-input modern-textarea"
                                        rows="3"
                                        placeholder="Add a new comment...">{{ old('new_comment') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions">
                <a href="{{ route('admin.students.index') }}" class="btn-modern btn-modern-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-check"></i>
                    <span>Save Student</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal for adding new parent --}}
<div class="modal fade" id="addParentModal" tabindex="-1" aria-labelledby="addParentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modern-modal-content">
            <div class="modern-modal-header">
                <h5 class="modern-modal-title" id="addParentModalLabel">Add New Parent/Guardian</h5>
                <button type="button" class="modern-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modern-modal-body">
                <form id="newParentForm">
                    @csrf
                    <div class="modern-form-group">
                        <label class="modern-form-label">Full Name <span class="modern-required">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-user modern-input-icon"></i>
                            <input type="text" class="modern-input" id="new_parent_name" required placeholder="e.g. Kebede Abebe">
                        </div>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Phone Number <span class="modern-required">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-phone modern-input-icon"></i>
                            <input type="tel" class="modern-input" id="new_parent_phone" required placeholder="e.g. +251 91 234 5678">
                        </div>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Email <small>(optional)</small></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-envelope modern-input-icon"></i>
                            <input type="email" class="modern-input" id="new_parent_email" placeholder="e.g. parent@email.com">
                        </div>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Address <small>(optional)</small></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-map-marker-alt modern-input-icon"></i>
                            <textarea class="modern-input modern-textarea" id="new_parent_address" rows="2" placeholder="e.g. Bole, Addis Ababa"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modern-modal-footer">
                <button type="button" class="btn-modern btn-modern-ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-modern btn-modern-primary" id="saveParentBtn">
                    <i class="fas fa-check"></i> Save Parent
                </button>
            </div>
        </div>
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

/* Tabs */
.modern-tabs {
    display: flex;
    border-bottom: 2px solid #f0f0f0;
    padding: 0 1.5rem;
    gap: 0;
    overflow-x: auto;
}

.modern-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
    font-size: 0.88rem;
    font-weight: 600;
    color: #9ca3af;
    background: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    position: relative;
}

.modern-tab::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: transparent;
    transition: all 0.2s;
}

.modern-tab:hover {
    color: #4361ee;
}

.modern-tab.active {
    color: #4361ee;
}

.modern-tab.active::after {
    background: #4361ee;
}

.modern-tab i {
    font-size: 0.82rem;
}

/* Tab Content */
.modern-tab-content {
    display: none;
}

.modern-tab-content.active {
    display: block;
}

/* Form Section */
.modern-form-section { border-bottom: none; }

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
.modern-form-section-icon-purple { background: #faf5ff; color: #8b5cf6; }
.modern-form-section-icon-gray { background: #f3f4f6; color: #6b7280; }

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

/* Form Layout (for photo + fields side-by-side) */
.modern-form-layout {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
}

.modern-form-fields { flex: 1; }

.modern-form-sidebar {
    width: 200px;
    flex-shrink: 0;
}

/* Photo Upload */
.modern-photo-upload {
    text-align: center;
    padding: 1.25rem;
    border: 2px dashed #e5e7eb;
    border-radius: 14px;
    background: #f9fafb;
}

.modern-photo-preview {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 0.75rem;
}

.modern-photo-label {
    font-weight: 600;
    color: #374151;
    font-size: 0.85rem;
    margin-bottom: 0.5rem;
}

.modern-photo-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.85rem;
    border-radius: 8px;
    background: #eef2ff;
    color: #4361ee;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.modern-photo-btn:hover {
    background: #4361ee;
    color: #fff;
}

.modern-photo-file {
    display: none;
}

/* Form Grid */
.modern-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
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

.modern-input[readonly] {
    background: #f9fafb;
    color: #6b7280;
    cursor: not-allowed;
}

/* Auto-generated field badge */
.auto-gen-field {
    display: flex;
    align-items: center;
}
.auto-gen-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1rem;
    border-radius: 10px;
    background: linear-gradient(135deg, #eef2ff, #e0e7ff);
    color: #4361ee;
    font-weight: 700;
    font-size: 0.95rem;
    letter-spacing: 0.5px;
    border: 1.5px solid #c7d2fe;
    width: 100%;
    box-sizing: border-box;
}
.auto-gen-badge i {
    font-size: 0.85rem;
    opacity: 0.7;
}
.auto-gen-badge-orange {
    background: linear-gradient(135deg, #fff7ed, #ffedd5);
    color: #c2410c;
    border-color: #fed7aa;
}

.modern-textarea { resize: vertical; min-height: 80px; }

.modern-select {
    appearance: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.25rem;
    padding-right: 2.5rem;
}

.modern-input-hint {
    font-size: 0.78rem;
    color: #9ca3af;
    margin-top: 0.3rem;
}

.modern-form-error {
    display: block;
    color: #ef4444;
    font-size: 0.8rem;
    margin-top: 0.35rem;
    font-weight: 500;
}

/* Input with button (guardian select) */
.modern-input-wrapper-with-btn {
    display: flex;
    gap: 0;
}

.modern-input-wrapper-with-btn .modern-input {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    flex: 1;
}

.modern-input-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    border: 1.5px solid #e5e7eb;
    border-left: none;
    border-radius: 0 10px 10px 0;
    background: #f9fafb;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.85rem;
}

.modern-input-btn:hover {
    background: #4361ee;
    color: #fff;
    border-color: #4361ee;
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

/* Modal */
.modern-modal-content {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
    border: none;
}

.modern-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
}

.modern-modal-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}

.modern-modal-close {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.modern-modal-close:hover {
    background: #fef2f2;
    color: #dc2626;
}

.modern-modal-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.modern-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #f0f0f0;
    background: #fafbfc;
}

/* Responsive */
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-tabs {
        padding: 0 1rem;
        gap: 0;
    }

    .modern-tab {
        padding: 0.75rem 0.85rem;
        font-size: 0.82rem;
    }

    .modern-form-layout {
        flex-direction: column;
    }

    .modern-form-sidebar {
        width: 100%;
    }

    .modern-photo-upload {
        display: flex;
        align-items: center;
        gap: 1rem;
        text-align: left;
    }

    .modern-photo-preview {
        width: 80px;
        height: 80px;
    }

    .modern-form-grid { grid-template-columns: 1fr; }
    .modern-form-span-2 { grid-column: span 1; }
    .modern-form-section-body { padding: 1rem 1.25rem 1.5rem; }
    .modern-form-section-header { padding: 1.25rem 1.25rem 0.75rem; }
    .modern-form-actions { padding: 1rem 1.25rem; flex-direction: column; }
    .btn-modern { justify-content: center; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // === Tab Navigation ===
    const tabs = document.querySelectorAll('.modern-tab');
    const tabContents = document.querySelectorAll('.modern-tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(tc => tc.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById('tab-' + target).classList.add('active');
        });
    });

    // === Ethiopian Date Conversion ===
    const dobInput = document.getElementById('dob');
    const admissionInput = document.getElementById('admission_date');
    const ethioDobSpan = document.getElementById('ethioDob');
    const ageDobSpan = document.getElementById('ageDob');
    const ethioAdmissionSpan = document.getElementById('ethioAdmission');

    function convertToEthiopian(dateStr) {
        if (!dateStr) return '';
        try {
            const g = new Date(dateStr);
            const gYear = g.getFullYear();
            const newYearGregorian = new Date(gYear, 8, 11);
            let eYear, daysSinceNewYear;
            if (g >= newYearGregorian) {
                eYear = gYear - 7;
                daysSinceNewYear = Math.floor((g - newYearGregorian) / (1000 * 60 * 60 * 24));
            } else {
                eYear = gYear - 8;
                const prevNewYear = new Date(gYear - 1, 8, 11);
                daysSinceNewYear = Math.floor((g - prevNewYear) / (1000 * 60 * 60 * 24));
            }
            let eMonth = Math.floor(daysSinceNewYear / 30) + 1;
            let eDay = (daysSinceNewYear % 30) + 1;
            if (eMonth > 13) {
                eMonth = 13;
                eDay = daysSinceNewYear - 360;
            }
            return `${eYear}/${eMonth.toString().padStart(2, '0')}/${eDay.toString().padStart(2, '0')}`;
        } catch (e) {
            return '';
        }
    }

    function calculateAge(dateStr) {
        if (!dateStr) return '';
        const birthDate = new Date(dateStr);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
        return age >= 0 ? age : '';
    }

    function updateDob() {
        if (dobInput && ethioDobSpan && ageDobSpan) {
            if (dobInput.value) {
                ethioDobSpan.textContent = convertToEthiopian(dobInput.value);
                ageDobSpan.textContent = calculateAge(dobInput.value);
            } else {
                ethioDobSpan.textContent = '-';
                ageDobSpan.textContent = '-';
            }
        }
    }

    function updateAdmission() {
        if (admissionInput && ethioAdmissionSpan) {
            if (admissionInput.value) {
                ethioAdmissionSpan.textContent = convertToEthiopian(admissionInput.value);
            } else {
                ethioAdmissionSpan.textContent = '-';
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

    updateDob();
    updateAdmission();

    // === Photo Preview ===
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');
    if (photoInput && photoPreview) {
        photoInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) { photoPreview.src = e.target.result; };
                reader.readAsDataURL(file);
            } else {
                photoPreview.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgZmlsbD0iI2YzZjRmNiIvPjx0ZXh0IHg9IjYwIiB5PSI1MCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzliYTNhZiIgZm9udC1zaXplPSIyNCI+8J+OrTwvdGV4dD48dGV4dCB4PSI2MCIgeT0iNzUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiM5YmEzYWYiIGZvbnQtc2l6ZT0iMTAiPlN0dWRlbnQgUGhvdG88L3RleHQ+PC9zdmc+';
            }
        });
    }

    // === Guardian Selection ===
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

    // Auto-generate roll number preview when section changes
    const sectionSelect = document.getElementById('section');
    const rollNumberValue = document.getElementById('rollNumberValue');
    const rollNumberInput = document.getElementById('roll_number');

    if (sectionSelect) {
        sectionSelect.addEventListener('change', function() {
            const sectionId = this.value;
            if (!sectionId) {
                rollNumberValue.textContent = 'Select class & section first';
                rollNumberInput.value = '';
                return;
            }
            fetch('{{ route("admin.students.api.roll-preview") }}?section_id=' + sectionId, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.roll_number !== undefined) {
                    rollNumberValue.textContent = data.roll_number;
                    rollNumberInput.value = data.roll_number;
                }
            })
            .catch(function() {
                rollNumberValue.textContent = 'Will be assigned';
            });
        });
    }
});
</script>
@endpush
@endsection
