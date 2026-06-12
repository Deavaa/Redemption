@extends('layouts.admin')
@section('title', 'Academic Year Transition')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Academic</a></li>
                    <li><a href="{{ route('admin.academic-years.index') }}">Academic Years</a></li>
                    <li class="active">Year Transition</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Academic Year Transition</h1>
            <p class="modern-page-subtitle">Carry forward classes, sections, and teacher assignments to a new academic year. Teacher assignments can be cleared so you can reassign them for the new year.</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.academic-years.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i><span>Back</span>
            </a>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="transition-info-banner">
        <div class="transition-info-icon">
            <i class="fas fa-info-circle"></i>
        </div>
        <div class="transition-info-content">
            <h3>How Academic Year Transition Works</h3>
            <p>When a new academic year begins, you need to set up classes, sections, and teacher assignments. This tool copies the structure from a previous academic year to the new one. You can choose to <strong>clear all teacher assignments</strong> so that homeroom teachers and subject teachers must be reassigned for the new year — this is the typical workflow since teachers are reassigned every new academic year.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="modern-alert modern-alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Step 1: Select Source & Target --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <div class="step-badge">1</div>
                <h2 class="modern-card-title">Select Academic Years</h2>
            </div>
        </div>
        <div class="modern-card-body-inner">
            <form id="transitionForm" method="POST" action="{{ route('admin.academic-years.process-transition') }}">
                @csrf
                <div class="transition-form-grid">
                    <div class="transition-form-group">
                        <label class="transition-label">Source Academic Year <span class="text-danger">*</span></label>
                        <select name="source_academic_year_id" id="sourceAy" class="transition-select" required>
                            <option value="">-- Select source year --</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ $currentAy && $currentAy->id == $ay->id ? 'selected' : '' }}>
                                    {{ $ay->name }} {{ $ay->is_current ? '(Current)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="transition-hint">The year to copy FROM</span>
                    </div>
                    <div class="transition-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <div class="transition-form-group">
                        <label class="transition-label">Target Academic Year <span class="text-danger">*</span></label>
                        <select name="target_academic_year_id" id="targetAy" class="transition-select" required>
                            <option value="">-- Select target year --</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ !$ay->is_current ? 'selected' : '' }}>
                                    {{ $ay->name }} {{ $ay->is_current ? '(Current)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="transition-hint">The year to copy TO</span>
                    </div>
                </div>

                {{-- Step 2: Options --}}
                <div class="transition-options-section">
                    <div class="modern-card-header" style="padding-left:0;border-bottom:none;">
                        <div class="modern-card-header-left">
                            <div class="step-badge">2</div>
                            <h2 class="modern-card-title">What to Carry Forward</h2>
                        </div>
                    </div>
                    <div class="transition-options-grid">
                        <label class="transition-option-card">
                            <input type="checkbox" name="carry_classes" value="1" checked>
                            <div class="transition-option-icon"><i class="fas fa-building"></i></div>
                            <div class="transition-option-info">
                                <span class="transition-option-title">Classes</span>
                                <span class="transition-option-desc">Copy class structure (Grade 1, Grade 2, etc.)</span>
                            </div>
                            <div class="transition-option-check"><i class="fas fa-check"></i></div>
                        </label>
                        <label class="transition-option-card">
                            <input type="checkbox" name="carry_sections" value="1" checked>
                            <div class="transition-option-icon"><i class="fas fa-layer-group"></i></div>
                            <div class="transition-option-info">
                                <span class="transition-option-title">Sections</span>
                                <span class="transition-option-desc">Copy sections (A, B, C) under each class</span>
                            </div>
                            <div class="transition-option-check"><i class="fas fa-check"></i></div>
                        </label>
                        <label class="transition-option-card">
                            <input type="checkbox" name="carry_assignments" value="1" checked>
                            <div class="transition-option-icon"><i class="fas fa-chalkboard"></i></div>
                            <div class="transition-option-info">
                                <span class="transition-option-title">Subject Assignments</span>
                                <span class="transition-option-desc">Copy subject-class-section mappings (without teachers if cleared)</span>
                            </div>
                            <div class="transition-option-check"><i class="fas fa-check"></i></div>
                        </label>
                    </div>
                </div>

                {{-- Step 3: Teacher Assignment Option --}}
                <div class="transition-teacher-section">
                    <div class="modern-card-header" style="padding-left:0;border-bottom:none;">
                        <div class="modern-card-header-left">
                            <div class="step-badge">3</div>
                            <h2 class="modern-card-title">Teacher Assignment Policy</h2>
                        </div>
                    </div>
                    <div class="teacher-policy-cards">
                        <label class="teacher-policy-card teacher-policy-reassign">
                            <input type="checkbox" name="clear_teacher_ids" value="1" checked>
                            <div class="teacher-policy-indicator"></div>
                            <div class="teacher-policy-icon"><i class="fas fa-user-clock"></i></div>
                            <div class="teacher-policy-info">
                                <span class="teacher-policy-title">Clear & Reassign Teachers</span>
                                <span class="teacher-policy-desc">Remove all homeroom and subject teacher assignments. You will reassign teachers for the new academic year using the Teacher Reassignment page. <strong>This is the recommended option</strong> since teachers are reassigned every new academic year.</span>
                            </div>
                        </label>
                        <label class="teacher-policy-card teacher-policy-keep">
                            <input type="checkbox" name="clear_teacher_ids" value="0" onclick="this.checked=false;">
                            <div class="teacher-policy-indicator"></div>
                            <div class="teacher-policy-icon"><i class="fas fa-user-check"></i></div>
                            <div class="teacher-policy-info">
                                <span class="teacher-policy-title">Keep Current Teachers</span>
                                <span class="teacher-policy-desc">Carry forward all teacher assignments from the source year. Homeroom teachers and subject teachers remain the same in the new year. Uncheck the "Clear & Reassign" option above to keep teachers.</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Preview --}}
                <div id="previewSection" class="transition-preview-section" style="display:none;">
                    <div class="modern-card-header" style="padding-left:0;border-bottom:none;">
                        <div class="modern-card-header-left">
                            <div class="step-badge">4</div>
                            <h2 class="modern-card-title">Preview</h2>
                        </div>
                    </div>
                    <div id="previewContent" class="transition-preview-content">
                        <div class="transition-preview-loading">
                            <i class="fas fa-spinner fa-spin"></i> Loading preview...
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="transition-submit-section">
                    <button type="button" class="btn-modern btn-modern-outline" onclick="loadPreview()">
                        <i class="fas fa-eye"></i><span>Preview Changes</span>
                    </button>
                    <button type="submit" class="btn-modern btn-modern-primary" id="submitBtn">
                        <i class="fas fa-play-circle"></i><span>Execute Transition</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

.modern-page-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;
}
.modern-page-header-left { flex: 1; }
.modern-page-header-right { display: flex; gap: 0.75rem; flex-wrap: wrap; }

.modern-page-title {
    font-size: 1.5rem; font-weight: 800; color: #1a1a2e; margin: 0 0 0.35rem;
}
.modern-page-subtitle {
    font-size: 0.9rem; color: #6b7280; margin: 0; line-height: 1.5;
}

.modern-breadcrumb ol {
    display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem;
    gap: 0.5rem; font-size: 0.8rem; align-items: center;
}
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #059669; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #059669; font-weight: 500; }

/* Info Banner */
.transition-info-banner {
    display: flex; gap: 1rem; padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #eef2ff 0%, #f0fdf4 100%);
    border: 1px solid #d1fae5; border-radius: 14px; margin-bottom: 1.5rem;
}
.transition-info-icon { font-size: 1.5rem; color: #059669; flex-shrink: 0; margin-top: 2px; }
.transition-info-content h3 { font-size: 0.95rem; font-weight: 700; color: #1a1a2e; margin: 0 0 0.35rem; }
.transition-info-content p { font-size: 0.85rem; color: #4b5563; margin: 0; line-height: 1.6; }

/* Card */
.modern-card {
    background: #fff; border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.5rem;
}
.modern-card-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0;
    flex-wrap: wrap; gap: 1rem;
}
.modern-card-header-left { display: flex; align-items: center; gap: 0.75rem; }
.modern-card-title { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-card-body-inner { padding: 1.5rem; }

.step-badge {
    width: 32px; height: 32px; border-radius: 50%;
    background: linear-gradient(135deg, #059669, #047857);
    color: #fff; font-weight: 800; font-size: 0.85rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* Form Grid */
.transition-form-grid {
    display: flex; align-items: flex-end; gap: 1.5rem; margin-bottom: 2rem;
    flex-wrap: wrap;
}
.transition-form-group { flex: 1; min-width: 220px; }
.transition-label {
    display: block; font-size: 0.85rem; font-weight: 600;
    color: #374151; margin-bottom: 0.5rem;
}
.transition-select {
    width: 100%; padding: 0.7rem 1rem; border: 1.5px solid #e5e7eb;
    border-radius: 10px; font-size: 0.9rem; color: #1a1a2e;
    background: #f9fafb; transition: all 0.2s;
}
.transition-select:focus {
    outline: none; border-color: #059669; background: #fff;
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}
.transition-hint { display: block; font-size: 0.75rem; color: #9ca3af; margin-top: 0.35rem; }
.transition-arrow {
    font-size: 1.5rem; color: #059669; padding-bottom: 0.5rem; flex-shrink: 0;
}

/* Options Section */
.transition-options-section { margin-bottom: 2rem; }
.transition-options-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}
.transition-option-card {
    display: flex; align-items: center; gap: 1rem; padding: 1.25rem;
    border: 2px solid #e5e7eb; border-radius: 14px; cursor: pointer;
    transition: all 0.25s; position: relative;
}
.transition-option-card:hover { border-color: #059669; background: #f0fdf4; }
.transition-option-card input[type="checkbox"] { display: none; }
.transition-option-card:has(input:checked) {
    border-color: #059669; background: #f0fdf4;
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}
.transition-option-card:has(input:checked) .transition-option-check { opacity: 1; }
.transition-option-icon {
    width: 44px; height: 44px; border-radius: 12px;
    background: #eef2ff; color: #059669;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.transition-option-info { flex: 1; }
.transition-option-title { display: block; font-weight: 600; color: #1a1a2e; font-size: 0.9rem; }
.transition-option-desc { display: block; font-size: 0.78rem; color: #6b7280; margin-top: 0.2rem; }
.transition-option-check {
    width: 24px; height: 24px; border-radius: 50%;
    background: #059669; color: #fff; font-size: 0.7rem;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity 0.2s; flex-shrink: 0;
}

/* Teacher Policy Section */
.transition-teacher-section { margin-bottom: 2rem; }
.teacher-policy-cards { display: flex; flex-direction: column; gap: 1rem; }
.teacher-policy-card {
    display: flex; align-items: flex-start; gap: 1rem; padding: 1.25rem;
    border: 2px solid #e5e7eb; border-radius: 14px; cursor: pointer;
    transition: all 0.25s; position: relative;
}
.teacher-policy-card input[type="checkbox"] { display: none; }
.teacher-policy-indicator {
    width: 24px; height: 24px; border-radius: 50%; border: 2px solid #d1d5db;
    flex-shrink: 0; margin-top: 2px; transition: all 0.2s;
    display: flex; align-items: center; justify-content: center;
}
.teacher-policy-reassign:has(input:checked) {
    border-color: #f59e0b; background: #fffbeb;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}
.teacher-policy-reassign:has(input:checked) .teacher-policy-indicator {
    border-color: #f59e0b; background: #f59e0b;
}
.teacher-policy-reassign:has(input:checked) .teacher-policy-indicator::after {
    content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
    color: #fff; font-size: 0.6rem;
}
.teacher-policy-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.teacher-policy-reassign .teacher-policy-icon { background: #fff7ed; color: #f59e0b; }
.teacher-policy-keep .teacher-policy-icon { background: #ecfdf5; color: #059669; }
.teacher-policy-info { flex: 1; }
.teacher-policy-title { display: block; font-weight: 600; color: #1a1a2e; font-size: 0.9rem; }
.teacher-policy-desc { display: block; font-size: 0.8rem; color: #6b7280; margin-top: 0.3rem; line-height: 1.5; }

/* Preview Section */
.transition-preview-section { margin-bottom: 2rem; }
.transition-preview-content {
    background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px;
    padding: 1.25rem; min-height: 100px;
}
.transition-preview-loading { text-align: center; padding: 2rem; color: #6b7280; font-size: 0.9rem; }

.preview-stats-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.75rem; margin-bottom: 1rem;
}
.preview-stat {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;
    padding: 1rem; text-align: center;
}
.preview-stat-value { font-size: 1.5rem; font-weight: 800; color: #1a1a2e; }
.preview-stat-label { font-size: 0.75rem; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px; }

.preview-classes-list { margin-top: 1rem; }
.preview-class-item {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.6rem 0.85rem; background: #fff; border: 1px solid #e5e7eb;
    border-radius: 8px; margin-bottom: 0.35rem;
}
.preview-class-name { font-weight: 600; color: #1a1a2e; font-size: 0.88rem; }
.preview-section-badge {
    background: #eef2ff; color: #4361ee; border-radius: 50px;
    padding: 0.15rem 0.5rem; font-size: 0.7rem; font-weight: 600;
}

/* Submit Section */
.transition-submit-section {
    display: flex; gap: 1rem; justify-content: flex-end;
    padding-top: 1.5rem; border-top: 1px solid #f0f0f0;
}

/* Buttons */
.btn-modern {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600;
    font-size: 0.9rem; text-decoration: none; border: none;
    cursor: pointer; transition: all 0.25s;
}
.btn-modern-primary {
    background: linear-gradient(135deg, #059669, #047857); color: #fff;
    box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
}
.btn-modern-primary:hover {
    transform: translateY(-2px); box-shadow: 0 4px 16px rgba(5, 150, 105, 0.4); color: #fff;
}
.btn-modern-outline {
    background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb;
}
.btn-modern-outline:hover { border-color: #059669; color: #059669; background: #f0fdf4; }

/* Alert */
.modern-alert {
    display: flex; align-items: center; gap: 0.65rem;
    padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; border-radius: 10px;
    font-size: 0.88rem; font-weight: 500; animation: fadeSlideIn 0.3s ease;
}
.modern-alert-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.modern-alert-close {
    margin-left: auto; background: none; border: none;
    cursor: pointer; color: inherit; opacity: 0.6; transition: opacity 0.2s;
}
.modern-alert-close:hover { opacity: 1; }

/* Responsive */
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .transition-form-grid { flex-direction: column; }
    .transition-arrow { transform: rotate(90deg); align-self: center; }
    .transition-options-grid { grid-template-columns: 1fr; }
    .preview-stats-grid { grid-template-columns: 1fr 1fr; }
    .transition-submit-section { flex-direction: column; }
    .transition-submit-section .btn-modern { width: 100%; justify-content: center; }
}
</style>
@endpush

@push('scripts')
<script>
function loadPreview() {
    const sourceAy = document.getElementById('sourceAy').value;
    const targetAy = document.getElementById('targetAy').value;
    const previewSection = document.getElementById('previewSection');
    const previewContent = document.getElementById('previewContent');

    if (!sourceAy || !targetAy) {
        alert('Please select both source and target academic years.');
        return;
    }

    if (sourceAy === targetAy) {
        alert('Source and target academic years must be different.');
        return;
    }

    previewSection.style.display = 'block';
    previewContent.innerHTML = '<div class="transition-preview-loading"><i class="fas fa-spinner fa-spin"></i> Loading preview...</div>';

    fetch('{{ route("admin.academic-years.transition-preview") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            source_academic_year_id: sourceAy,
            target_academic_year_id: targetAy
        })
    })
    .then(response => response.json())
    .then(data => {
        let html = '<div class="preview-stats-grid">';
        html += `<div class="preview-stat"><div class="preview-stat-value">${data.source_classes_count}</div><div class="preview-stat-label">Source Classes</div></div>`;
        html += `<div class="preview-stat"><div class="preview-stat-value">${data.target_classes_count}</div><div class="preview-stat-label">Target Classes (Existing)</div></div>`;
        html += `<div class="preview-stat"><div class="preview-stat-value">${data.classes_to_copy_count}</div><div class="preview-stat-label">Classes to Copy</div></div>`;
        html += `<div class="preview-stat"><div class="preview-stat-value">${data.sections_to_copy_count}</div><div class="preview-stat-label">Sections to Copy</div></div>`;
        html += `<div class="preview-stat"><div class="preview-stat-value">${data.source_assignments_count}</div><div class="preview-stat-label">Teacher Assignments</div></div>`;
        html += `<div class="preview-stat"><div class="preview-stat-value">${data.target_assignments_count}</div><div class="preview-stat-label">Target Assignments (Existing)</div></div>`;
        html += '</div>';

        if (data.classes_to_copy && data.classes_to_copy.length > 0) {
            html += '<div class="preview-classes-list">';
            html += '<div style="font-weight:600;color:#1a1a2e;margin-bottom:0.5rem;font-size:0.88rem;">Classes to be copied:</div>';
            data.classes_to_copy.forEach(cls => {
                const sections = data.sections_to_copy.filter(s => s.class_id === cls.id);
                const sectionBadges = sections.map(s =>
                    `<span class="preview-section-badge">${s.name}</span>`
                ).join(' ');
                html += `<div class="preview-class-item">
                    <span class="preview-class-name">${cls.name}</span>
                    ${sectionBadges}
                </div>`;
            });
            html += '</div>';
        }

        const clearTeachers = document.querySelector('input[name="clear_teacher_ids"]').checked;
        if (clearTeachers) {
            html += '<div style="margin-top:1rem;padding:0.85rem 1rem;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;font-size:0.85rem;color:#92400e;"><i class="fas fa-user-clock" style="margin-right:0.5rem;"></i><strong>Teacher assignments will be cleared.</strong> You will need to reassign teachers using the <a href="' + '{{ route("admin.teacher-reassignment.index") }}' + '" style="color:#059669;font-weight:600;">Teacher Reassignment</a> page after the transition.</div>';
        }

        previewContent.innerHTML = html;
    })
    .catch(error => {
        previewContent.innerHTML = '<div style="color:#dc2626;padding:1rem;"><i class="fas fa-exclamation-triangle"></i> Failed to load preview. Please check your selection and try again.</div>';
        console.error('Preview error:', error);
    });
}

// Confirm before submit
document.getElementById('transitionForm').addEventListener('submit', function(e) {
    const sourceAy = document.getElementById('sourceAy');
    const targetAy = document.getElementById('targetAy');
    const sourceText = sourceAy.options[sourceAy.selectedIndex].text;
    const targetText = targetAy.options[targetAy.selectedIndex].text;

    if (!confirm(`Are you sure you want to transition from "${sourceText}" to "${targetText}"? This action cannot be undone.`)) {
        e.preventDefault();
    }
});
</script>
@endpush
@endsection
