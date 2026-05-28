@extends('layouts.admin')

@section('title', 'Create Assessment Question')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.assessment-questions.index') }}">Self-Assessment</a></li>
                <li class="active">Create Question</li>
            </ol></nav>
            <h1 class="modern-page-title">Create Assessment Question</h1>
            <p class="modern-page-subtitle">Add a self-assessment question with explanation for students</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.assessment-questions.index') }}" class="btn-modern btn-modern-ghost"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    @if(session('error'))
    <div class="modern-alert modern-alert-danger">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button class="modern-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.assessment-questions.store') }}" id="questionForm">
        @csrf

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
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Class <span class="modern-required">*</span></label>
                        <select name="class_id" id="classSelect" class="modern-input modern-select" style="padding-left:0.75rem" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Applies To</label>
                        <div class="modern-input" style="padding-left:0.75rem;display:flex;align-items:center;gap:6px;color:#059669;font-weight:500;background:#ecfdf5;border:1px solid #a7f3d0">
                            <i class="fas fa-globe"></i> All Branches &amp; Sections
                        </div>
                        <div style="font-size:0.72rem;color:#6b7280;margin-top:3px">Questions are class-level and visible to all students in this class</div>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Question Type <span class="modern-required">*</span></label>
                        <select name="question_type" id="questionType" class="modern-input modern-select" style="padding-left:0.75rem" required>
                            <option value="multiple_choice" {{ old('question_type') === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                            <option value="true_false" {{ old('question_type') === 'true_false' ? 'selected' : '' }}>True / False</option>
                            <option value="short_answer" {{ old('question_type') === 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Difficulty <span class="modern-required">*</span></label>
                        <select name="difficulty" class="modern-input modern-select" style="padding-left:0.75rem" required>
                            <option value="easy" {{ old('difficulty') === 'easy' ? 'selected' : '' }}>Easy</option>
                            <option value="medium" {{ old('difficulty') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Marks</label>
                        <input type="number" name="marks" class="modern-input" style="padding-left:0.75rem" value="{{ old('marks', 1) }}" min="1" max="100">
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Title (Optional)</label>
                        <input type="text" name="title" class="modern-input" style="padding-left:0.75rem" value="{{ old('title') }}" placeholder="Short title for this question">
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Topic / Chapter</label>
                        <input type="text" name="topic" class="modern-input" style="padding-left:0.75rem" value="{{ old('topic') }}" placeholder="e.g. Chapter 3 - Algebra">
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
                    <textarea name="question_text" class="modern-input modern-textarea" rows="4" required placeholder="Enter your question here...">{{ old('question_text') }}</textarea>
                    @error('question_text')<span class="modern-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="modern-form-group" style="margin-top:1rem">
                    <label class="modern-form-label">Hint (shown before answering)</label>
                    <textarea name="hint" class="modern-input modern-textarea" rows="2" placeholder="Optional hint to help students think...">{{ old('hint') }}</textarea>
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
                    <button type="button" class="btn-modern btn-modern-outline btn-modern-sm" id="addOptionBtn"><i class="fas fa-plus"></i> Add Option</button>
                </div>
            </div>
            <div style="padding:1.5rem" id="optionsContainer">
                <p style="font-size:0.82rem;color:#6b7280;margin-bottom:0.75rem">Mark the correct answer by checking the "Correct" checkbox.</p>
                @php
                    $oldOptions = old('options', [
                        ['option_text' => '', 'option_label' => 'A', 'is_correct' => false],
                        ['option_text' => '', 'option_label' => 'B', 'is_correct' => false],
                        ['option_text' => '', 'option_label' => 'C', 'is_correct' => false],
                        ['option_text' => '', 'option_label' => 'D', 'is_correct' => false],
                    ]);
                @endphp
                @foreach($oldOptions as $idx => $opt)
                <div class="option-row" style="display:flex;gap:0.5rem;align-items:center;margin-bottom:0.5rem">
                    <input type="text" name="options[{{ $idx }}][option_label]" class="modern-input" style="width:45px;text-align:center;padding-left:4px" value="{{ $opt['option_label'] ?? chr(65 + $idx) }}" readonly>
                    <input type="text" name="options[{{ $idx }}][option_text]" class="modern-input" style="flex:1;padding-left:0.75rem" value="{{ $opt['option_text'] ?? '' }}" placeholder="Option {{ $opt['option_label'] ?? chr(65 + $idx) }}">
                    <label style="display:flex;align-items:center;gap:4px;font-size:0.8rem;white-space:nowrap;cursor:pointer">
                        <input type="checkbox" name="options[{{ $idx }}][is_correct]" class="is-correct-check" value="1" {{ !empty($opt['is_correct']) ? 'checked' : '' }}>
                        Correct
                    </label>
                    <button type="button" class="modern-btn-icon modern-btn-delete remove-option-btn" title="Remove" style="width:32px;height:32px"><i class="fas fa-times"></i></button>
                </div>
                @endforeach
                @error('options')<span class="modern-form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        {{-- T/F Card --}}
        <div class="modern-card" style="margin-bottom:1.25rem;display:none" id="tfCard">
            <div class="modern-card-header">
                <div class="modern-card-header-left">
                    <i class="fas fa-check-double" style="color:#10b981"></i>
                    <span class="modern-card-title">True / False Answer</span>
                </div>
            </div>
            <div style="padding:1.5rem">
                <label class="modern-form-label">Correct Answer <span class="modern-required">*</span></label>
                <div style="display:flex;gap:1rem;margin-top:0.5rem">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:10px 20px;border:2px solid #e5e7eb;border-radius:10px;transition:all 0.2s" class="tf-option">
                        <input type="radio" name="correct_tf" id="tfTrue" value="true" class="tf-radio" {{ old('correct_tf') === 'true' ? 'checked' : '' }} required>
                        <i class="fas fa-check" style="color:#10b981"></i> True
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:10px 20px;border:2px solid #e5e7eb;border-radius:10px;transition:all 0.2s" class="tf-option">
                        <input type="radio" name="correct_tf" id="tfFalse" value="false" class="tf-radio" {{ old('correct_tf') === 'false' ? 'checked' : '' }}>
                        <i class="fas fa-times" style="color:#ef4444"></i> False
                    </label>
                </div>
                @error('correct_tf')<span class="modern-form-error">{{ $message }}</span>@enderror
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
                    <textarea name="explanation" class="modern-input modern-textarea" rows="4" placeholder="Explain WHY the answer is correct. This helps students understand the concept.">{{ old('explanation') }}</textarea>
                    <div style="font-size:0.75rem;color:#9ca3af;margin-top:4px">Shown after the student submits their answer, whether correct or incorrect.</div>
                </div>
                <div class="modern-form-group" style="margin-top:1rem">
                    <label class="modern-form-label">Worked-Out Solution (Step-by-step)</label>
                    <textarea name="worked_out_solution" class="modern-input modern-textarea" rows="6" placeholder="Step 1: ...&#10;Step 2: ...&#10;Step 3: ...&#10;Therefore, the answer is ...">{{ old('worked_out_solution') }}</textarea>
                    <div style="font-size:0.75rem;color:#9ca3af;margin-top:4px">Provide a step-by-step breakdown of how to solve the problem. This is crucial for self-learning.</div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="modern-card">
            <div style="padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="is_active" value="1" id="isActive" {{ old('is_active', true) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:#4361ee">
                    <span style="font-size:0.9rem;font-weight:500">Active (visible to students)</span>
                </label>
                <div style="display:flex;gap:0.5rem">
                    <a href="{{ route('admin.assessment-questions.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                    <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-save"></i> Save Question</button>
                </div>
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
    // ── Question type toggle ────────────────────────────────
    function toggleQuestionType() {
        var type = $('#questionType').val();
        if (type === 'multiple_choice') {
            $('#optionsCard').show();
            // Re-enable option inputs
            $('#optionsContainer input').prop('disabled', false);
            $('#tfCard').hide();
            // Clear T/F selection
            $('input[name="correct_tf"]').prop('checked', false);
        } else if (type === 'true_false') {
            $('#optionsCard').hide();
            // Disable option inputs so they don't submit
            $('#optionsContainer input').prop('disabled', true);
            $('#tfCard').show();
        } else {
            // Short answer — hide both, disable option inputs
            $('#optionsCard').hide();
            $('#optionsContainer input').prop('disabled', true);
            $('#tfCard').hide();
            $('input[name="correct_tf"]').prop('checked', false);
        }
    }
    $('#questionType').on('change', toggleQuestionType);
    toggleQuestionType();

    // ── MCQ Options management ──────────────────────────────
    $('#addOptionBtn').on('click', function() {
        var idx = $('#optionsContainer .option-row').length;
        if (idx >= 6) return;
        var label = String.fromCharCode(65 + idx);
        var html = '<div class="option-row" style="display:flex;gap:0.5rem;align-items:center;margin-bottom:0.5rem">' +
            '<input type="text" name="options[' + idx + '][option_label]" class="modern-input" style="width:45px;text-align:center;padding-left:4px" value="' + label + '" readonly>' +
            '<input type="text" name="options[' + idx + '][option_text]" class="modern-input" style="flex:1;padding-left:0.75rem" placeholder="Option ' + label + '">' +
            '<label style="display:flex;align-items:center;gap:4px;font-size:0.8rem;white-space:nowrap;cursor:pointer"><input type="checkbox" name="options[' + idx + '][is_correct]" class="is-correct-check" value="1"> Correct</label>' +
            '<button type="button" class="modern-btn-icon modern-btn-delete remove-option-btn" title="Remove" style="width:32px;height:32px"><i class="fas fa-times"></i></button>' +
            '</div>';
        $('#optionsContainer').append(html);
    });

    $(document).on('click', '.remove-option-btn', function() {
        if ($('#optionsContainer .option-row').length > 2) {
            $(this).closest('.option-row').remove();
            reindexOptions();
        }
    });

    $(document).on('change', '.is-correct-check', function() {
        if ($(this).is(':checked')) {
            $('.is-correct-check').not(this).prop('checked', false);
        }
    });

    function reindexOptions() {
        $('#optionsContainer .option-row').each(function(idx) {
            $(this).find('input[name*="option_label"]').val(String.fromCharCode(65 + idx));
            $(this).find('input[name*="option_text"]').attr('placeholder', 'Option ' + String.fromCharCode(65 + idx));
        });
    }
});
</script>
@endpush
@endsection
