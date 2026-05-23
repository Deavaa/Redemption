@extends('layouts.admin')

@section('title', 'Create Assessment Question')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Create Assessment Question</h3>
            <p class="page-subtitle">Add a self-assessment question with explanation for students</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.assessment-questions.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.assessment-questions.store') }}" id="questionForm">
    @csrf

    {{-- Question Details Card --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Question Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('subject_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="classSelect" class="form-select" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('class_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Section</label>
                    <select name="section_id" id="sectionSelect" class="form-select">
                        <option value="">All Sections</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Question Type <span class="text-danger">*</span></label>
                    <select name="question_type" id="questionType" class="form-select" required>
                        <option value="multiple_choice" {{ old('question_type') === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                        <option value="true_false" {{ old('question_type') === 'true_false' ? 'selected' : '' }}>True / False</option>
                        <option value="short_answer" {{ old('question_type') === 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Difficulty <span class="text-danger">*</span></label>
                    <select name="difficulty" class="form-select" required>
                        <option value="easy" {{ old('difficulty') === 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Marks</label>
                    <input type="number" name="marks" class="form-control" value="{{ old('marks', 1) }}" min="1" max="100">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Title (Optional)</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Short title for this question">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Topic / Chapter</label>
                    <input type="text" name="topic" class="form-control" value="{{ old('topic') }}" placeholder="e.g. Chapter 3 - Algebra">
                </div>
            </div>
        </div>
    </div>

    {{-- Question Text Card --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Question Text <span class="text-danger">*</span></h5></div>
        <div class="card-body">
            <textarea name="question_text" class="form-control" rows="4" required placeholder="Enter your question here...">{{ old('question_text') }}</textarea>
            @error('question_text') <span class="text-danger small">{{ $message }}</span> @enderror

            <div class="mt-3">
                <label class="form-label">Hint (shown before answering)</label>
                <textarea name="hint" class="form-control" rows="2" placeholder="Optional hint to help students think...">{{ old('hint') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Options Card (Multiple Choice) --}}
    <div class="card mb-4" id="optionsCard">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Answer Options</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addOptionBtn">
                <i class="fas fa-plus me-1"></i> Add Option
            </button>
        </div>
        <div class="card-body" id="optionsContainer">
            <div class="text-muted mb-2">Mark the correct answer by checking the "Correct" checkbox.</div>
            @php
                $oldOptions = old('options', [
                    ['option_text' => '', 'option_label' => 'A', 'is_correct' => false],
                    ['option_text' => '', 'option_label' => 'B', 'is_correct' => false],
                    ['option_text' => '', 'option_label' => 'C', 'is_correct' => false],
                    ['option_text' => '', 'option_label' => 'D', 'is_correct' => false],
                ]);
            @endphp
            @foreach($oldOptions as $idx => $opt)
            <div class="row g-2 mb-2 option-row">
                <div class="col-auto">
                    <input type="text" name="options[{{ $idx }}][option_label]" class="form-control text-center" style="width:50px" value="{{ $opt['option_label'] ?? chr(65 + $idx) }}" readonly>
                </div>
                <div class="col">
                    <input type="text" name="options[{{ $idx }}][option_text]" class="form-control" value="{{ $opt['option_text'] ?? '' }}" placeholder="Option {{ $opt['option_label'] ?? chr(65 + $idx) }}">
                </div>
                <div class="col-auto">
                    <div class="form-check mt-2">
                        <input type="checkbox" name="options[{{ $idx }}][is_correct]" class="form-check-input is-correct-check" value="1" {{ !empty($opt['is_correct']) ? 'checked' : '' }}>
                        <label class="form-check-label small">Correct</label>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn" title="Remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
            @endforeach
            @error('options') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- True/False Card --}}
    <div class="card mb-4" id="tfCard" style="display:none">
        <div class="card-header"><h5 class="mb-0">True / False Answer</h5></div>
        <div class="card-body">
            <label class="form-label">Correct Answer <span class="text-danger">*</span></label>
            <div>
                <div class="form-check form-check-inline">
                    <input type="radio" name="correct_tf" id="tfTrue" value="true" class="form-check-input" {{ old('correct_tf') === 'true' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="tfTrue">True</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="radio" name="correct_tf" id="tfFalse" value="false" class="form-check-input" {{ old('correct_tf') === 'false' ? 'checked' : '' }}>
                    <label class="form-check-label" for="tfFalse">False</label>
                </div>
            </div>
            @error('correct_tf') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Explanation Card --}}
    <div class="card mb-4">
        <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Post-Answer Explanation (shown AFTER student answers)</h5></div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Detailed Explanation</label>
                <textarea name="explanation" class="form-control" rows="4" placeholder="Explain WHY the answer is correct. This helps students understand the concept.">{{ old('explanation') }}</textarea>
                <div class="form-text">This is shown after the student submits their answer, whether correct or incorrect.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Worked-Out Solution (Step-by-step)</label>
                <textarea name="worked_out_solution" class="form-control" rows="6" placeholder="Step 1: ...&#10;Step 2: ...&#10;Step 3: ...&#10;Therefore, the answer is ...">{{ old('worked_out_solution') }}</textarea>
                <div class="form-text">Provide a step-by-step breakdown of how to solve the problem. This is crucial for self-learning.</div>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="card">
        <div class="card-body d-flex justify-content-between">
            <div class="form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="isActive">Active (visible to students)</label>
            </div>
            <div>
                <a href="{{ route('admin.assessment-questions.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Question</button>
            </div>
        </div>
    </div>
</form>

@section('scripts')
<script>
$(function() {
    // Toggle between option types
    function toggleQuestionType() {
        var type = $('#questionType').val();
        if (type === 'multiple_choice') {
            $('#optionsCard').show();
            $('#tfCard').hide();
        } else if (type === 'true_false') {
            $('#optionsCard').hide();
            $('#tfCard').show();
        } else {
            $('#optionsCard').hide();
            $('#tfCard').hide();
        }
    }
    $('#questionType').on('change', toggleQuestionType);
    toggleQuestionType();

    // Add option
    $('#addOptionBtn').on('click', function() {
        var idx = $('#optionsContainer .option-row').length;
        if (idx >= 6) return;
        var label = String.fromCharCode(65 + idx);
        var html = '<div class="row g-2 mb-2 option-row">' +
            '<div class="col-auto"><input type="text" name="options[' + idx + '][option_label]" class="form-control text-center" style="width:50px" value="' + label + '" readonly></div>' +
            '<div class="col"><input type="text" name="options[' + idx + '][option_text]" class="form-control" placeholder="Option ' + label + '"></div>' +
            '<div class="col-auto"><div class="form-check mt-2"><input type="checkbox" name="options[' + idx + '][is_correct]" class="form-check-input is-correct-check" value="1"><label class="form-check-label small">Correct</label></div></div>' +
            '<div class="col-auto"><button type="button" class="btn btn-sm btn-outline-danger remove-option-btn" title="Remove"><i class="fas fa-times"></i></button></div>' +
            '</div>';
        $('#optionsContainer').append(html);
    });

    // Remove option
    $(document).on('click', '.remove-option-btn', function() {
        if ($('#optionsContainer .option-row').length > 2) {
            $(this).closest('.option-row').remove();
            reindexOptions();
        }
    });

    // Only one correct answer
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

    // Load sections dynamically
    $('#classSelect').on('change', function() {
        var classId = $(this).val();
        if (!classId) return;
        $.get('{{ route("admin.assessment-questions.api-sections", 0) }}'.replace('/0', '/' + classId), function(data) {
            var html = '<option value="">All Sections</option>';
            data.forEach(function(s) {
                html += '<option value="' + s.id + '">' + s.name + '</option>';
            });
            $('#sectionSelect').html(html);
        });
    });
});
</script>
@endsection
