@extends('layouts.admin')

@section('title', 'Edit Assessment Question')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Edit Assessment Question</h3>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.assessment-questions.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.assessment-questions.update', $assessment_question->id) }}">
    @csrf @method('PUT')

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Question Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id', $assessment_question->subject_id) == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="classSelect" class="form-select" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id', $assessment_question->class_id) == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Section</label>
                    <select name="section_id" id="sectionSelect" class="form-select">
                        <option value="">All Sections</option>
                        @foreach($assessment_question->classroom->sections ?? [] as $sec)
                        <option value="{{ $sec->id }}" {{ old('section_id', $assessment_question->section_id) == $sec->id ? 'selected' : '' }}>
                            {{ $sec->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Question Type <span class="text-danger">*</span></label>
                    <select name="question_type" id="questionType" class="form-select" required>
                        <option value="multiple_choice" {{ old('question_type', $assessment_question->question_type) === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                        <option value="true_false" {{ old('question_type', $assessment_question->question_type) === 'true_false' ? 'selected' : '' }}>True / False</option>
                        <option value="short_answer" {{ old('question_type', $assessment_question->question_type) === 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Difficulty</label>
                    <select name="difficulty" class="form-select">
                        <option value="easy" {{ old('difficulty', $assessment_question->difficulty) === 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty', $assessment_question->difficulty) === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty', $assessment_question->difficulty) === 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Marks</label>
                    <input type="number" name="marks" class="form-control" value="{{ old('marks', $assessment_question->marks) }}" min="1" max="100">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $assessment_question->title) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Topic / Chapter</label>
                    <input type="text" name="topic" class="form-control" value="{{ old('topic', $assessment_question->topic) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Question Text <span class="text-danger">*</span></h5></div>
        <div class="card-body">
            <textarea name="question_text" class="form-control" rows="4" required>{{ old('question_text', $assessment_question->question_text) }}</textarea>
            <div class="mt-3">
                <label class="form-label">Hint</label>
                <textarea name="hint" class="form-control" rows="2">{{ old('hint', $assessment_question->hint) }}</textarea>
            </div>
        </div>
    </div>

    {{-- MCQ Options --}}
    <div class="card mb-4" id="optionsCard">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Answer Options</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addOptionBtn"><i class="fas fa-plus me-1"></i> Add</button>
        </div>
        <div class="card-body" id="optionsContainer">
            @php
                $existingOptions = old('options', $assessment_question->options->map(fn($o) => [
                    'option_text' => $o->option_text,
                    'option_label' => $o->option_label,
                    'is_correct' => $o->is_correct,
                ])->toArray());
            @endphp
            @foreach($existingOptions as $idx => $opt)
            <div class="row g-2 mb-2 option-row">
                <div class="col-auto">
                    <input type="text" name="options[{{ $idx }}][option_label]" class="form-control text-center" style="width:50px" value="{{ $opt['option_label'] ?? chr(65 + $idx) }}" readonly>
                </div>
                <div class="col">
                    <input type="text" name="options[{{ $idx }}][option_text]" class="form-control" value="{{ $opt['option_text'] ?? '' }}">
                </div>
                <div class="col-auto">
                    <div class="form-check mt-2">
                        <input type="checkbox" name="options[{{ $idx }}][is_correct]" class="form-check-input is-correct-check" value="1" {{ !empty($opt['is_correct']) ? 'checked' : '' }}>
                        <label class="form-check-label small">Correct</label>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn"><i class="fas fa-times"></i></button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- T/F --}}
    <div class="card mb-4" id="tfCard" style="display:none">
        <div class="card-header"><h5 class="mb-0">True / False</h5></div>
        <div class="card-body">
            @php $correctLabel = $assessment_question->getCorrectOptionLabel(); @endphp
            <div class="form-check form-check-inline">
                <input type="radio" name="correct_tf" id="tfTrue" value="true" class="form-check-input" {{ old('correct_tf') === 'true' || $correctLabel === 'A' ? 'checked' : '' }}>
                <label class="form-check-label" for="tfTrue">True</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" name="correct_tf" id="tfFalse" value="false" class="form-check-input" {{ old('correct_tf') === 'false' || $correctLabel === 'B' ? 'checked' : '' }}>
                <label class="form-check-label" for="tfFalse">False</label>
            </div>
        </div>
    </div>

    {{-- Explanation --}}
    <div class="card mb-4 border-info">
        <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Post-Answer Explanation</h5></div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Detailed Explanation</label>
                <textarea name="explanation" class="form-control" rows="4">{{ old('explanation', $assessment_question->explanation) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Worked-Out Solution</label>
                <textarea name="worked_out_solution" class="form-control" rows="6">{{ old('worked_out_solution', $assessment_question->worked_out_solution) }}</textarea>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body d-flex justify-content-between">
            <div class="form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" {{ old('is_active', $assessment_question->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="isActive">Active</label>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Question</button>
        </div>
    </div>
</form>

@section('scripts')
<script>
$(function() {
    function toggleType() {
        var t = $('#questionType').val();
        $('#optionsCard').toggle(t === 'multiple_choice');
        $('#tfCard').toggle(t === 'true_false');
    }
    $('#questionType').on('change', toggleType);
    toggleType();

    $('#addOptionBtn').on('click', function() {
        var i = $('#optionsContainer .option-row').length;
        if (i >= 6) return;
        var l = String.fromCharCode(65 + i);
        $('#optionsContainer').append('<div class="row g-2 mb-2 option-row"><div class="col-auto"><input type="text" name="options['+i+'][option_label]" class="form-control text-center" style="width:50px" value="'+l+'" readonly></div><div class="col"><input type="text" name="options['+i+'][option_text]" class="form-control"></div><div class="col-auto"><div class="form-check mt-2"><input type="checkbox" name="options['+i+'][is_correct]" class="form-check-input is-correct-check" value="1"><label class="form-check-label small">Correct</label></div></div><div class="col-auto"><button type="button" class="btn btn-sm btn-outline-danger remove-option-btn"><i class="fas fa-times"></i></button></div></div>');
    });
    $(document).on('click', '.remove-option-btn', function() {
        if ($('#optionsContainer .option-row').length > 2) { $(this).closest('.option-row').remove(); }
    });
    $(document).on('change', '.is-correct-check', function() {
        if ($(this).is(':checked')) $('.is-correct-check').not(this).prop('checked', false);
    });
    $('#classSelect').on('change', function() {
        var c = $(this).val(); if(!c) return;
        $.get('{{ route("admin.assessment-questions.api-sections", 0) }}'.replace('/0','/'+c), function(d) {
            var h = '<option value="">All Sections</option>';
            d.forEach(function(s) { h += '<option value="'+s.id+'">'+s.name+'</option>'; });
            $('#sectionSelect').html(h);
        });
    });
});
</script>
@endsection
