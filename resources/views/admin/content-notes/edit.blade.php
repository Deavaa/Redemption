@extends('layouts.admin')

@section('title', 'Edit Content Note')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.content-notes.index') }}">Content Note Bank</a></li>
                <li class="active">Edit Note</li>
            </ol></nav>
            <h1 class="modern-page-title">Edit Content Note</h1>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.content-notes.index') }}" class="btn-modern btn-modern-ghost"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    @if(session('error'))
    <div class="modern-alert modern-alert-danger" style="margin-bottom:1rem">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="modern-alert modern-alert-danger" style="margin-bottom:1rem">
        <div style="display:flex;align-items:flex-start;gap:0.75rem">
            <i class="fas fa-exclamation-triangle" style="margin-top:2px"></i>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul style="margin:0.5rem 0 0 1.25rem;font-size:0.85rem">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.content-notes.update', $content_note->id) }}" id="noteForm">
        @csrf @method('PUT')

        {{-- Basic Info --}}
        <div class="modern-card" style="margin-bottom:1.25rem">
            <div class="modern-card-header">
                <div class="modern-card-header-left">
                    <i class="fas fa-info-circle" style="color:#4361ee"></i>
                    <span class="modern-card-title">Note Details</span>
                </div>
            </div>
            <div style="padding:1.5rem">
                <div class="modern-form-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem">
                    <div class="modern-form-group">
                        <label class="modern-form-label">Subject <span class="modern-required">*</span></label>
                        <select name="subject_id" class="modern-input modern-select" style="padding-left:0.75rem" required>
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $content_note->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Class <span class="modern-required">*</span></label>
                        <select name="class_id" id="classSelect" class="modern-input modern-select" style="padding-left:0.75rem" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $content_note->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Sections <span style="font-size:0.7rem;color:#6b7280">(select multiple)</span></label>
                        <div id="sectionsContainer" style="display:flex;flex-wrap:wrap;gap:6px;min-height:40px;padding:8px;border:1.5px solid #e5e7eb;border-radius:10px;background:#f9fafb">
                            @foreach($sections as $sec)
                            @php $isChecked = in_array($sec->id, $selectedSections); @endphp
                            <label style="display:flex;align-items:center;gap:4px;font-size:0.8rem;cursor:pointer;padding:4px 10px;border-radius:6px;background:{{ $isChecked ? '#4361ee' : '#fff' }};color:{{ $isChecked ? '#fff' : '#374151' }};border:1px solid {{ $isChecked ? '#4361ee' : '#e5e7eb' }};transition:all 0.15s">
                                <input type="checkbox" name="section_ids[]" value="{{ $sec->id }}" {{ $isChecked ? 'checked' : '' }} style="display:none" class="section-chk">
                                {{ $sec->name }}
                            </label>
                            @endforeach
                            @if($sections->isEmpty())
                            <span style="color:#9ca3af;font-size:0.82rem">No sections found for this class</span>
                            @endif
                        </div>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Note Type <span class="modern-required">*</span></label>
                        <select name="note_type" class="modern-input modern-select" style="padding-left:0.75rem" required>
                            @foreach(\App\Models\SubjectContentNote::noteTypeOptions() as $key => $label)
                            <option value="{{ $key }}" {{ old('note_type', $content_note->note_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Difficulty</label>
                        <select name="difficulty" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="easy" {{ old('difficulty', $content_note->difficulty) === 'easy' ? 'selected' : '' }}>Easy</option>
                            <option value="medium" {{ old('difficulty', $content_note->difficulty) === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="hard" {{ old('difficulty', $content_note->difficulty) === 'hard' ? 'selected' : '' }}>Hard</option>
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Topic</label>
                        <input type="text" name="topic" class="modern-input" style="padding-left:0.75rem" value="{{ old('topic', $content_note->topic) }}">
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Chapter</label>
                        <input type="text" name="chapter" class="modern-input" style="padding-left:0.75rem" value="{{ old('chapter', $content_note->chapter) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Title & Description --}}
        <div class="modern-card" style="margin-bottom:1.25rem">
            <div class="modern-card-header">
                <div class="modern-card-header-left">
                    <i class="fas fa-heading" style="color:#7c3aed"></i>
                    <span class="modern-card-title">Title & Description</span>
                </div>
            </div>
            <div style="padding:1.5rem">
                <div class="modern-form-group">
                    <label class="modern-form-label">Title <span class="modern-required">*</span></label>
                    <input type="text" name="title" class="modern-input" style="padding-left:0.75rem" value="{{ old('title', $content_note->title) }}" required>
                </div>
                <div class="modern-form-group" style="margin-top:1rem">
                    <label class="modern-form-label">Brief Description</label>
                    <textarea name="description" class="modern-input modern-textarea" rows="2">{{ old('description', $content_note->description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="modern-card" style="margin-bottom:1.25rem">
            <div class="modern-card-header" style="background:linear-gradient(135deg,#4361ee,#3a0ca3)">
                <div class="modern-card-header-left">
                    <i class="fas fa-file-alt" style="color:#fff"></i>
                    <span class="modern-card-title" style="color:#fff">Note Content <span class="modern-required">*</span></span>
                </div>
            </div>
            <div style="padding:1.5rem">
                <div class="modern-form-group">
                    <textarea name="content" class="modern-input modern-textarea" rows="12" required>{{ old('content', $content_note->content) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Link to Lesson Plan --}}
        <div class="modern-card" style="margin-bottom:1.25rem">
            <div class="modern-card-header">
                <div class="modern-card-header-left">
                    <i class="fas fa-link" style="color:#f59e0b"></i>
                    <span class="modern-card-title">Link to Lesson Plan</span>
                </div>
            </div>
            <div style="padding:1.5rem">
                <div class="modern-form-group">
                    <label class="modern-form-label">Associated Lesson Plan</label>
                    <select name="lesson_plan_id" class="modern-input modern-select" style="padding-left:0.75rem">
                        <option value="">— None (standalone note) —</option>
                        @foreach($lessonPlans as $lp)
                        <option value="{{ $lp->id }}" {{ old('lesson_plan_id', $content_note->lesson_plan_id) == $lp->id ? 'selected' : '' }}>
                            {{ $lp->title }} ({{ $lp->subject->name ?? '' }} — {{ $lp->classRoom->name ?? '' }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="modern-card">
            <div style="padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem">
                <div style="display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="is_shared" value="1" {{ old('is_shared', $content_note->is_shared) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:#10b981">
                        <span style="font-size:0.9rem;font-weight:500"><i class="fas fa-share-alt" style="color:#10b981;margin-right:4px"></i> Shared</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $content_note->is_active) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:#4361ee">
                        <span style="font-size:0.9rem;font-weight:500">Active</span>
                    </label>
                </div>
                <div style="display:flex;gap:0.5rem">
                    <a href="{{ route('admin.content-notes.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                    <button type="submit" id="saveBtn" class="btn-modern btn-modern-primary"><i class="fas fa-save"></i> Update Note</button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .is-invalid { border-color: #ef4444 !important; }
    #saveBtn.saving { opacity: 0.7; pointer-events: none; }
</style>
@endpush

@push('scripts')
<script>
$(function() {
    // ── Load sections when class changes ──────────────────
    $('#classSelect').on('change', function() {
        var classId = $(this).val();
        var container = $('#sectionsContainer');
        if (!classId) {
            container.html('<span style="color:#9ca3af;font-size:0.82rem">Select a class first</span>');
            return;
        }
        container.html('<span style="color:#9ca3af;font-size:0.82rem"><i class="fas fa-spinner fa-spin"></i> Loading...</span>');

        $.ajax({
            url: '{{ route("admin.content-notes.api-sections") }}',
            data: { class_id: classId },
            dataType: 'json',
            success: function(sections) {
                if (sections.length === 0) {
                    container.html('<span style="color:#6b7280;font-size:0.82rem">No sections found for this class</span>');
                    return;
                }
                var html = '';
                sections.forEach(function(sec) {
                    html += '<label style="display:flex;align-items:center;gap:4px;font-size:0.8rem;cursor:pointer;padding:4px 10px;border-radius:6px;background:#fff;color:#374151;border:1px solid #e5e7eb;transition:all 0.15s">';
                    html += '<input type="checkbox" name="section_ids[]" value="' + sec.id + '" style="display:none" class="section-chk">';
                    html += sec.name;
                    html += '</label>';
                });
                container.html(html);
            },
            error: function() {
                container.html('<span style="color:#ef4444;font-size:0.82rem">Failed to load sections</span>');
            }
        });
    });

    // Toggle section checkbox styling
    $(document).on('change', '.section-chk', function() {
        var label = $(this).closest('label');
        if ($(this).is(':checked')) {
            label.css({ 'background': '#4361ee', 'color': '#fff', 'border-color': '#4361ee' });
        } else {
            label.css({ 'background': '#fff', 'color': '#374151', 'border-color': '#e5e7eb' });
        }
    });

    // ── Form submit feedback ────────────────────────────────
    $('#noteForm').on('submit', function() {
        var btn = $('#saveBtn');
        btn.addClass('saving');
        btn.html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        return true;
    });
});
</script>
@endpush
@endsection
