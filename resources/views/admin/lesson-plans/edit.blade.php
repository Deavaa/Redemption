@extends('layouts.admin')
@section('title', 'Edit Lesson Plan')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.lesson-plans.index') }}">Lesson Plans</a></li>
                <li class="active">Edit</li>
            </ol></nav>
            <h1 class="modern-page-title">Edit Lesson Plan</h1>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.lesson-plans.index') }}" class="btn-modern btn-modern-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    @if($errors->any())
    <div class="modern-alert modern-alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <div>@foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach</div>
    </div>
    @endif

    {{-- Status Banner --}}
    @if($lessonPlan->status === 'revision')
    <div class="modern-alert modern-alert-warning" style="margin-bottom:1.25rem">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Revision Requested</strong>
            @if($lessonPlan->reviewer_comment)<br><em>{{ $lessonPlan->reviewer_comment }}</em>@endif
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.lesson-plans.update', $lessonPlan->id) }}">
        @csrf @method('PUT')

        {{-- Basic Info --}}
        <div class="modern-card" style="margin-bottom:1.25rem">
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue"><i class="fas fa-info-circle"></i></div>
                    <div><h3 class="modern-form-section-title">Basic Information</h3></div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Academic Year <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar modern-input-icon"></i>
                                <select name="academic_year_id" class="modern-input modern-select" {{ $isTeacher ? 'disabled' : '' }}>
                                    @foreach($academicYears as $ay)
                                    <option value="{{ $ay->id }}" {{ old('academic_year_id', $lessonPlan->academic_year_id) == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                                    @endforeach
                                </select>
                                @if($isTeacher)<input type="hidden" name="academic_year_id" value="{{ $lessonPlan->academic_year_id }}">@endif
                            </div>
                            @error('academic_year_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Term <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-bookmark modern-input-icon"></i>
                                <select name="term_id" class="modern-input modern-select" {{ $isTeacher ? 'disabled' : '' }}>
                                    @foreach($terms as $t)
                                    <option value="{{ $t->id }}" {{ old('term_id', $lessonPlan->term_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                    @endforeach
                                </select>
                                @if($isTeacher)<input type="hidden" name="term_id" value="{{ $lessonPlan->term_id }}">@endif
                            </div>
                            @error('term_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        @unless($isTeacher)
                        <div class="modern-form-group">
                            <label class="modern-form-label">Teacher <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-chalkboard-teacher modern-input-icon"></i>
                                <select name="teacher_id" class="modern-input modern-select">
                                    @foreach($teachers as $t)
                                    <option value="{{ $t->id }}" {{ old('teacher_id', $lessonPlan->teacher_id) == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endunless
                        <div class="modern-form-group">
                            <label class="modern-form-label">Class <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-building modern-input-icon"></i>
                                <select name="class_id" class="modern-input modern-select" id="selClass">
                                    <option value="">Select Class</option>
                                    @foreach($classes as $c)
                                    <option value="{{ $c->id }}" {{ old('class_id', $lessonPlan->class_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('class_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Section</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-layer-group modern-input-icon"></i>
                                <select name="section_id" class="modern-input modern-select" id="selSection">
                                    <option value="">All Sections</option>
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Subject <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-book modern-input-icon"></i>
                                <select name="subject_id" class="modern-input modern-select">
                                    @foreach($subjects as $s)
                                    <option value="{{ $s->id }}" {{ old('subject_id', $lessonPlan->subject_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('subject_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lesson Details --}}
        <div class="modern-card" style="margin-bottom:1.25rem">
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green"><i class="fas fa-clipboard-list"></i></div>
                    <div><h3 class="modern-form-section-title">Lesson Details</h3></div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label">Lesson Title <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-heading modern-input-icon"></i>
                                <input type="text" name="title" value="{{ old('title', $lessonPlan->title) }}" class="modern-input">
                            </div>
                            @error('title')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Week Number</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar-week modern-input-icon"></i>
                                <input type="number" name="week_number" value="{{ old('week_number', $lessonPlan->week_number) }}" class="modern-input" min="1">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Lesson Date</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar-day modern-input-icon"></i>
                                <input type="date" name="lesson_date" value="{{ old('lesson_date', $lessonPlan->lesson_date?->format('Y-m-d')) }}" class="modern-input">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Duration (minutes)</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-clock modern-input-icon"></i>
                                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $lessonPlan->duration_minutes) }}" class="modern-input" min="1">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Save as</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-flag modern-input-icon"></i>
                                <select name="status" class="modern-input modern-select">
                                    <option value="draft" {{ old('status', $lessonPlan->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="submitted" {{ old('status', $lessonPlan->status) === 'submitted' ? 'selected' : '' }}>Submit for Review</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="modern-card" style="margin-bottom:1.25rem">
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-purple"><i class="fas fa-book-open"></i></div>
                    <div><h3 class="modern-form-section-title">Lesson Content</h3></div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label">Learning Objectives</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-bullseye modern-input-icon modern-input-icon-textarea"></i>
                                <textarea name="objectives" class="modern-input modern-textarea" rows="3">{{ old('objectives', $lessonPlan->objectives) }}</textarea>
                            </div>
                        </div>
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label">Teaching Materials</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-tools modern-input-icon modern-input-icon-textarea"></i>
                                <textarea name="materials" class="modern-input modern-textarea" rows="2">{{ old('materials', $lessonPlan->materials) }}</textarea>
                            </div>
                        </div>
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label">Lesson Activities</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-tasks modern-input-icon modern-input-icon-textarea"></i>
                                <textarea name="activities" class="modern-input modern-textarea" rows="4">{{ old('activities', $lessonPlan->activities) }}</textarea>
                            </div>
                        </div>
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label">Assessment Methods</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-clipboard-check modern-input-icon modern-input-icon-textarea"></i>
                                <textarea name="assessment" class="modern-input modern-textarea" rows="2">{{ old('assessment', $lessonPlan->assessment) }}</textarea>
                            </div>
                        </div>
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label">Homework / Assignment</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-pencil-alt modern-input-icon modern-input-icon-textarea"></i>
                                <textarea name="homework" class="modern-input modern-textarea" rows="2">{{ old('homework', $lessonPlan->homework) }}</textarea>
                            </div>
                        </div>
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label">Additional Notes</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-sticky-note modern-input-icon modern-input-icon-textarea"></i>
                                <textarea name="notes" class="modern-input modern-textarea" rows="2">{{ old('notes', $lessonPlan->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modern-card">
            <div class="modern-form-actions">
                <a href="{{ route('admin.lesson-plans.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Load sections on class change
    const classSel = document.getElementById('selClass');
    const sectionSel = document.getElementById('selSection');
    if (classSel && sectionSel) {
        async function loadSections(classId, selectedId) {
            sectionSel.innerHTML = '<option value="">All Sections</option>';
            if (!classId) return;
            try {
                const res = await fetch('{{ route("admin.subject-assignments.api.sections") }}?class_id=' + classId);
                const data = await res.json();
                data.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id; opt.textContent = s.name;
                    if (s.id == selectedId) opt.selected = true;
                    sectionSel.appendChild(opt);
                });
            } catch(e) {}
        }
        // Load initial sections
        loadSections(classSel.value, {{ $lessonPlan->section_id ?? 'null' }});
        classSel.addEventListener('change', function() { loadSections(this.value, null); });
    }
</script>
@endpush
@endsection
