@extends('layouts.admin')

@section('title', 'Edit Assessment Question')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.assessment-questions.index') }}">Self-Assessment</a></li>
                <li class="active">Edit Question</li>
            </ol></nav>
            <h1 class="modern-page-title">Edit Assessment Question</h1>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.assessment-questions.index') }}" class="btn-modern btn-modern-ghost"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.assessment-questions.update', $assessment_question->id) }}">
        @csrf @method('PUT')

        {{-- Question Details --}}
        <div class="modern-card" style="margin-bottom:1.25rem">
            <div class="modern-card-header">
                <div class="modern-card-header-left">
                    <i class="fas fa-info-circle" style="color:#4361ee"></i>
                    <span class="modern-card-title">Question Details</span>
                </div>
            </div>
            <div style="padding:1.5rem">
                <div class="modern-form-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem">
                    <div class="modern-form-group">
                        <label class="modern-form-label">Subject <span class="modern-required">*</span></label>
                        <select name="subject_id" class="modern-input modern-select" style="padding-left:0.75rem" required>
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $assessment_question->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Class <span class="modern-required">*</span></label>
                        <select name="class_id" id="classSelect" class="modern-input modern-select" style="padding-left:0.75rem" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $assessment_question->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Section</label>
                        <select name="section_id" id="sectionSelect" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Sections</option>
                            @foreach($assessment_question->classroom->sections ?? [] as $sec)
                            <option value="{{ $sec->id }}" {{ old('section_id', $assessment_question->section_id) == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Question Type <span class="modern-required">*</span></label>
                        <select name="question_type" id="questionType" class="modern-input modern-select" style="padding-left:0.75rem" required>
                            <option value="multiple_choice" {{ old('question_type', $assessment_question->question_type) === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                            <option value="true_false" {{ old('question_type', $assessment_question->question_type) === 'true_false' ? 'selected' : '' }}>True / False</option>
                            <option value="short_answer" {{ old('question_type', $assessment_question->question_type) === 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Difficulty</label>
                        <select name="difficulty" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="easy" {{ old('difficulty', $assessment_question->difficulty) === 'easy' ? 'selected' : '' }}>Easy</option>
                            <option value="medium" {{ old('difficulty', $assessment_question->difficulty) === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="hard" {{ old('difficulty', $assessment_question->difficulty) === 'hard' ? 'selected' : '' }}>Hard</option>
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Marks</label>
                        <input type="number" name="marks" class="modern-input" style="padding-left:0.75rem" value="{{ old('marks', $assessment_question->marks) }}" min="1" max="100">
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Title</label>
                        <input type="text" name="title" class="modern-input" style="padding-left:0.75rem" value="{{ old('title', $assessment_question->title) }}">
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Topic / Chapter</label>
                        <input type="text" name="topic" class="modern-input" style="padding-left:0.75rem" value="{{ old('topic', $assessment_question->topic) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Question Text --}}
        <div class="modern-card" style="margin-bottom:1.25rem">
            <div class="modern-card-header">
                <div class="modern-card-header-left">
                    <i class="fas fa-question-circle" style="color:#7c3aed"></i>
                    <span class="modern-card-title">Question Text <span class="modern-required">*</span></span>
                </div>
            </div>
            <div style="padding:1.5rem">
                <div class="modern-form-group">
                    <textarea name="question_text" class="modern-input modern-textarea" rows="4" required>{{ old('question_text', $assessment_question->question_text) }}</textarea>
                </div>
                <div class="modern-form-group" style="margin-top:1rem">
                    <label class="modern-form-label">Hint</label>
                    <textarea name="hint" class="modern-input modern-textarea" rows="2">{{ old('hint', $assessment_question->hint) }}</textarea>
                </div>
            </div>
        </div>

        {{-- MCQ Options --}}
        <div class="modern-card" style="margin-bottom:1.25rem" id="optionsCard">
            <div class="modern-card-header">
                <div class="modern-card-header-left">
                    <i class="fas fa-list-ol" style="color:#4361ee"></i>
                    <span class="modern-card-title">Answer Options</span>
                </div>
                <div class="modern-card-header-right">
                    <button type="button" class="btn-modern btn-modern-outline btn-modern-sm" id="addOptionBtn"><i class="fas fa-plus"></i> Add</button>
                </div>
            </div>
            <div style="padding:1.5rem" id="optionsContainer">
                @php
                    $existingOptions = old('options', $assessment_question->options->map(fn($o) => [
                        'option_text' => $o->option_text,
                        'option_label' => $o->option_label,
                        'is_correct' => $o->is_correct,
                    ])->toArray());
                @endphp
                @foreach($existingOptions as $idx => $opt)
                <div class="option-row" style="display:flex;gap:0.5rem;align-items:center;margin-bottom:0.5rem">
                    <input type="text" name="options[{{ $idx }}][option_label]" class="modern-input" style="width:45px;text-align:center;padding-left:4px" value="{{ $opt['option_label'] ?? chr(65 + $idx) }}" readonly>
                    <input type="text" name="options[{{ $idx }}][option_text]" class="modern-input" style="flex:1;padding-left:0.75rem" value="{{ $opt['option_text'] ?? '' }}">
                    <label style="display:flex;align-items:center;gap:4px;font-size:0.8rem;white-space:nowrap;cursor:pointer">
                        <input type="checkbox" name="options[{{ $idx }}][is_correct]" class="is-correct-check" value="1" {{ !empty($opt['is_correct']) ? 'checked' : '' }}>
                        Correct
                    </label>
                    <button type="button" class="modern-btn-icon modern-btn-delete remove-option-btn" title="Remove" style="width:32px;height:32px"><i class="fas fa-times"></i></button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- T/F --}}
        <div class="modern-card" style="margin-bottom:1.25rem;display:none" id="tfCard">
            <div class="modern-card-header">
                <div class="modern-card-header-left">
                    <i class="fas fa-check-double" style="color:#10b981"></i>
                    <span class="modern-card-title">True / False</span>
                </div>
            </div>
            <div style="padding:1.5rem">
                @php $correctLabel = $assessment_question->getCorrectOptionLabel(); @endphp
                <div style="display:flex;gap:1rem">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:10px 20px;border:2px solid #e5e7eb;border-radius:10px" class="tf-option">
                        <input type="radio" name="correct_tf" id="tfTrue" value="true" class="tf-radio" {{ old('correct_tf') === 'true' || $correctLabel === 'A' ? 'checked' : '' }}>
                        <i class="fas fa-check" style="color:#10b981"></i> True
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:10px 20px;border:2px solid #e5e7eb;border-radius:10px" class="tf-option">
                        <input type="radio" name="correct_tf" id="tfFalse" value="false" class="tf-radio" {{ old('correct_tf') === 'false' || $correctLabel === 'B' ? 'checked' : '' }}>
                        <i class="fas fa-times" style="color:#ef4444"></i> False
                    </label>
                </div>
            </div>
        </div>

        {{-- Explanation --}}
        <div class="modern-card" style="margin-bottom:1.25rem">
            <div class="modern-card-header" style="background:linear-gradient(135deg,#0ea5e9,#2563eb)">
                <div class="modern-card-header-left">
                    <i class="fas fa-lightbulb" style="color:#fff"></i>
                    <span class="modern-card-title" style="color:#fff">Post-Answer Explanation</span>
                </div>
            </div>
            <div style="padding:1.5rem">
                <div class="modern-form-group">
                    <label class="modern-form-label">Detailed Explanation</label>
                    <textarea name="explanation" class="modern-input modern-textarea" rows="4">{{ old('explanation', $assessment_question->explanation) }}</textarea>
                </div>
                <div class="modern-form-group" style="margin-top:1rem">
                    <label class="modern-form-label">Worked-Out Solution</label>
                    <textarea name="worked_out_solution" class="modern-input modern-textarea" rows="6">{{ old('worked_out_solution', $assessment_question->worked_out_solution) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="modern-card">
            <div style="padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="is_active" value="1" id="isActive" {{ old('is_active', $assessment_question->is_active) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:#4361ee">
                    <span style="font-size:0.9rem;font-weight:500">Active</span>
                </label>
                <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-save"></i> Update Question</button>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .tf-option:has(input:checked) { border-color: #4361ee; background: #f0f4ff; }
</style>
@endpush

@push('scripts')
<script>
$(function() {
    // ── Pre-loaded sections data (no AJAX needed) ──────────
    var allSections = @json($allSections ?? []);
    var selectedSectionId = '{{ old("section_id", $assessment_question->section_id) }}';

    function updateSectionDropdown(classId) {
        var html = '<option value="">All Sections</option>';
        if (classId) {
            var filtered = allSections.filter(function(s) { return s.class_id == classId; });
            filtered.forEach(function(s) {
                html += '<option value="' + s.id + '"' + (s.id == selectedSectionId ? ' selected' : '') + '>' + s.name + '</option>';
            });
        }
        $('#sectionSelect').html(html);
    }

    // Initialize section dropdown on page load
    updateSectionDropdown($('#classSelect').val());

    // Update section dropdown when class changes
    $('#classSelect').on('change', function() {
        selectedSectionId = ''; // Reset selection when class changes
        updateSectionDropdown($(this).val());
    });

    // ── Question type toggle ────────────────────────────────
    function toggleType() {
        var t = $('#questionType').val();
        $('#optionsCard').toggle(t === 'multiple_choice');
        $('#tfCard').toggle(t === 'true_false');
    }
    $('#questionType').on('change', toggleType);
    toggleType();

    // ── MCQ Options management ──────────────────────────────
    $('#addOptionBtn').on('click', function() {
        var i = $('#optionsContainer .option-row').length;
        if (i >= 6) return;
        var l = String.fromCharCode(65 + i);
        $('#optionsContainer').append(
            '<div class="option-row" style="display:flex;gap:0.5rem;align-items:center;margin-bottom:0.5rem">' +
            '<input type="text" name="options['+i+'][option_label]" class="modern-input" style="width:45px;text-align:center;padding-left:4px" value="'+l+'" readonly>' +
            '<input type="text" name="options['+i+'][option_text]" class="modern-input" style="flex:1;padding-left:0.75rem">' +
            '<label style="display:flex;align-items:center;gap:4px;font-size:0.8rem;white-space:nowrap;cursor:pointer"><input type="checkbox" name="options['+i+'][is_correct]" class="is-correct-check" value="1"> Correct</label>' +
            '<button type="button" class="modern-btn-icon modern-btn-delete remove-option-btn" title="Remove" style="width:32px;height:32px"><i class="fas fa-times"></i></button>' +
            '</div>'
        );
    });
    $(document).on('click', '.remove-option-btn', function() {
        if ($('#optionsContainer .option-row').length > 2) { $(this).closest('.option-row').remove(); }
    });
    $(document).on('change', '.is-correct-check', function() {
        if ($(this).is(':checked')) $('.is-correct-check').not(this).prop('checked', false);
    });
});
</script>
@endpush
@endsection
