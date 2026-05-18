@extends('layouts.admin')
@section('title', 'Readmit Student')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.students.index') }}">Students</a></li>
                    <li><a href="{{ route('admin.students.inactive') }}">Inactive</a></li>
                    <li class="active">Readmit</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.students.inactive') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Inactive List</span>
            </a>
        </div>
    </div>

    {{-- Student Info Card --}}
    <div class="modern-card modern-info-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <div class="modern-form-section-icon modern-form-section-icon-blue">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <h2 class="modern-card-title">Student Information</h2>
                    <span class="modern-card-subtitle">Details of the student being readmitted</span>
                </div>
            </div>
            @php
                $statusBadge = match($student->status ?? '') {
                    'inactive' => 'modern-badge-danger',
                    'transferred' => 'modern-badge-warning',
                    default => 'modern-badge-light'
                };
            @endphp
            <span class="modern-badge {{ $statusBadge }}">{{ ucfirst($student->status ?? 'N/A') }}</span>
        </div>
        <div class="modern-card-body-padded">
            <div class="modern-info-grid">
                <div class="modern-info-item">
                    <div class="modern-info-avatar">
                        @if($student->photo)
                            <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->full_name }}" class="modern-avatar modern-avatar-img">
                        @else
                            <div class="modern-avatar modern-avatar-placeholder">
                                {{ strtoupper(substr($student->full_name ?? 'S', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="modern-info-details">
                        <div class="modern-info-name">{{ $student->full_name }}</div>
                        <div class="modern-info-meta">
                            <span><i class="fas fa-id-badge"></i> {{ $student->admission_number ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                <div class="modern-info-item">
                    <div class="modern-info-label"><i class="fas fa-chalkboard"></i> Previous Class</div>
                    <div class="modern-info-value">{{ $student->classroom?->name ?? 'N/A' }}</div>
                </div>
                <div class="modern-info-item">
                    <div class="modern-info-label"><i class="fas fa-calendar-times"></i> Leave Date</div>
                    <div class="modern-info-value">{{ $student->leave_date ? \Carbon\Carbon::parse($student->leave_date)->format('M d, Y') : 'N/A' }}</div>
                </div>
                <div class="modern-info-item">
                    <div class="modern-info-label"><i class="fas fa-comment-dots"></i> Leave Reason</div>
                    <div class="modern-info-value">{{ $student->leave_reason ?? 'Not specified' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Readmission Form Card --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <div class="modern-form-section-icon modern-form-section-icon-green">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h2 class="modern-card-title">Readmission Details</h2>
                    <span class="modern-card-subtitle">Assign new class, section and academic year for readmission</span>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.students.readmit.store', $student->id) }}" method="POST">
            @csrf

            <div class="modern-form-section-body">
                @if(session('error'))
                    <div class="modern-alert modern-alert-danger" style="margin-bottom: 1.25rem;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                        <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                <div class="modern-form-grid">
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="classroom_id">
                            New Class <span class="modern-required">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-chalkboard modern-input-icon"></i>
                            <select name="classroom_id" id="classroom_id"
                                class="modern-input modern-select {{ $errors->has('classroom_id') ? 'is-invalid' : '' }}"
                                required>
                                <option value="">Select Class</option>
                                @foreach ($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}"
                                        data-branch="{{ $classroom->branch->name ?? 'N/A' }}"
                                        {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
                                        {{ $classroom->name }} ({{ $classroom->branch->name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('classroom_id')
                            <span class="modern-form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="section_id">
                            New Section <span class="modern-required">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-layer-group modern-input-icon"></i>
                            <select name="section_id" id="section_id"
                                class="modern-input modern-select {{ $errors->has('section_id') ? 'is-invalid' : '' }}"
                                required>
                                <option value="">Select class first</option>
                            </select>
                        </div>
                        @error('section_id')
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
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}"
                                        {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('academic_year_id')
                            <span class="modern-form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="admission_date">
                            New Admission Date <span class="modern-required">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-calendar-check modern-input-icon"></i>
                            <input type="date" id="admission_date" name="admission_date"
                                class="modern-input {{ $errors->has('admission_date') ? 'is-invalid' : '' }}"
                                value="{{ old('admission_date', now()->format('Y-m-d')) }}"
                                required>
                        </div>
                        <div class="modern-input-hint">Defaults to today's date</div>
                        @error('admission_date')
                            <span class="modern-form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="modern-form-group modern-form-span-2">
                        <label class="modern-form-label" for="readmission_remarks">
                            Readmission Remarks
                        </label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-sticky-note modern-input-icon" style="top: 1.1rem; transform: none;"></i>
                            <textarea name="readmission_remarks" id="readmission_remarks"
                                class="modern-input modern-textarea {{ $errors->has('readmission_remarks') ? 'is-invalid' : '' }}"
                                placeholder="Enter any remarks or notes regarding this readmission..."
                                rows="4">{{ old('readmission_remarks') }}</textarea>
                        </div>
                        @error('readmission_remarks')
                            <span class="modern-form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions">
                <a href="{{ route('admin.students.inactive') }}" class="btn-modern btn-modern-ghost">
                    <i class="fas fa-times"></i>
                    <span>Cancel</span>
                </a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-user-check"></i>
                    <span>Readmit Student</span>
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
    margin-bottom: 1.5rem;
}

.modern-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-card-header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modern-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}

.modern-card-subtitle {
    font-size: 0.82rem;
    color: #9ca3af;
    display: block;
    margin-top: 0.1rem;
}

/* Badges */
.modern-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.65rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.3px;
}

.modern-badge-light { background: #f3f4f6; color: #6b7280; }
.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-warning { background: #fefce8; color: #b45309; }
.modern-badge-info { background: #eff6ff; color: #2563eb; }

/* Info Card Body */
.modern-card-body-padded { padding: 1.5rem; }

.modern-info-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 1.5rem;
    align-items: center;
}

.modern-info-item {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.modern-info-avatar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.15rem;
}

.modern-info-details {
    display: flex;
    flex-direction: column;
}

.modern-info-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a2e;
}

.modern-info-meta {
    display: flex;
    gap: 1rem;
    margin-top: 0.2rem;
}

.modern-info-meta span {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    color: #6b7280;
}

.modern-info-meta span i { color: #4361ee; font-size: 0.75rem; }

.modern-info-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.modern-info-label i { font-size: 0.72rem; }

.modern-info-value {
    font-size: 0.95rem;
    font-weight: 600;
    color: #374151;
}

/* Avatar */
.modern-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    flex-shrink: 0;
    object-fit: cover;
}

.modern-avatar-img {
    width: 48px;
    height: 48px;
    border-radius: 12px;
}

.modern-avatar-placeholder {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
}

/* Form Section Icon */
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

.modern-form-section-body { padding: 1.5rem; }

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
    font-size: 0.78rem;
    color: #dc2626;
    margin-top: 0.3rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
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
    background: #fff;
    color: #4361ee;
    border: 1.5px solid #4361ee;
}

.btn-modern-outline:hover {
    background: #4361ee;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(67, 97, 238, 0.3);
}

.btn-modern-ghost {
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
}

.btn-modern-ghost:hover {
    background: #f3f4f6;
    color: #374151;
}

/* Form Actions */
.modern-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
}

/* Alert */
.modern-alert {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.85rem 1.25rem;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 500;
    animation: fadeSlideIn 0.3s ease;
}

.modern-alert-danger {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.modern-alert-close {
    margin-left: auto;
    background: none;
    border: none;
    cursor: pointer;
    color: inherit;
    opacity: 0.6;
    transition: opacity 0.2s;
}

.modern-alert-close:hover { opacity: 1; }

/* Responsive */
@media (max-width: 768px) {
    .modern-page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .modern-info-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .modern-info-item {
        flex-direction: row;
        align-items: center;
        gap: 0.75rem;
    }

    .modern-info-label { min-width: 120px; }

    .modern-form-grid {
        grid-template-columns: 1fr;
    }

    .modern-form-actions {
        flex-direction: column;
    }

    .modern-form-actions .btn-modern {
        justify-content: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const classroomSelect = document.getElementById('classroom_id');
    const sectionSelect = document.getElementById('section_id');

    // Sections data embedded from PHP
    const sectionsByClassroom = @json($classrooms->mapWithKeys(function ($classroom) {
        return [$classroom->id => $classroom->sections->map(function ($section) {
            return ['id' => $section->id, 'name' => $section->name];
        })];
    }));

    classroomSelect.addEventListener('change', function () {
        const classroomId = this.value;
        sectionSelect.innerHTML = '';

        if (!classroomId || !sectionsByClassroom[classroomId]) {
            sectionSelect.innerHTML = '<option value="">Select class first</option>';
            return;
        }

        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        sectionsByClassroom[classroomId].forEach(function (section) {
            const option = document.createElement('option');
            option.value = section.id;
            option.textContent = section.name;
            sectionSelect.appendChild(option);
        });
    });

    // If there's an old value for classroom, trigger change to reload sections
    @if(old('classroom_id'))
        classroomSelect.dispatchEvent(new Event('change'));
        @if(old('section_id'))
            sectionSelect.value = '{{ old("section_id") }}';
        @endif
    @endif
});
</script>
@endpush
@endsection
