@extends('layouts.admin')

@section('title', 'Bulk Add Assessment Questions')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Bulk Add Questions</h3>
            <p class="page-subtitle">Add multiple questions at once for the same subject and class</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.assessment-questions.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.assessment-questions.bulk-store') }}" id="bulkForm">
    @csrf

    {{-- Common Settings --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Common Settings (applies to all questions)</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class <span class="text-danger">*</span></label>
                    <select name="class_id" class="form-select" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Difficulty</label>
                    <select name="difficulty" class="form-select">
                        <option value="easy">Easy</option>
                        <option value="medium" selected>Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Topic</label>
                    <input type="text" name="topic" class="form-control" placeholder="e.g. Chapter 3">
                </div>
            </div>
        </div>
    </div>

    {{-- Questions --}}
    <div id="questionsContainer">
        <div class="card mb-3 question-block" data-index="0">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0">Question #1</h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-question-btn" title="Remove"><i class="fas fa-times"></i></button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select name="questions[0][question_type]" class="form-select">
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True / False</option>
                            <option value="short_answer">Short Answer</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Marks</label>
                        <input type="number" name="questions[0][marks]" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Question Text <span class="text-danger">*</span></label>
                        <textarea name="questions[0][question_text]" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hint</label>
                        <input type="text" name="questions[0][hint]" class="form-control" placeholder="Optional hint">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Explanation</label>
                        <input type="text" name="questions[0][explanation]" class="form-control" placeholder="Why is the answer correct?">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Worked Solution</label>
                        <input type="text" name="questions[0][worked_out_solution]" class="form-control" placeholder="Step-by-step solution">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mb-4">
        <button type="button" class="btn btn-outline-primary" id="addQuestionBtn">
            <i class="fas fa-plus me-1"></i> Add Another Question
        </button>
    </div>

    <div class="card">
        <div class="card-body text-end">
            <a href="{{ route('admin.assessment-questions.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save All Questions</button>
        </div>
    </div>
</form>

@section('scripts')
<script>
$(function() {
    var qIdx = 1;
    $('#addQuestionBtn').on('click', function() {
        var html = '<div class="card mb-3 question-block" data-index="' + qIdx + '">' +
            '<div class="card-header d-flex justify-content-between">' +
            '<h6 class="mb-0">Question #' + (qIdx + 1) + '</h6>' +
            '<button type="button" class="btn btn-sm btn-outline-danger remove-question-btn" title="Remove"><i class="fas fa-times"></i></button>' +
            '</div>' +
            '<div class="card-body">' +
            '<div class="row g-3">' +
            '<div class="col-md-4"><label class="form-label">Type</label><select name="questions[' + qIdx + '][question_type]" class="form-select"><option value="multiple_choice">Multiple Choice</option><option value="true_false">True / False</option><option value="short_answer">Short Answer</option></select></div>' +
            '<div class="col-md-2"><label class="form-label">Marks</label><input type="number" name="questions[' + qIdx + '][marks]" class="form-control" value="1" min="1"></div>' +
            '<div class="col-12"><label class="form-label">Question Text <span class="text-danger">*</span></label><textarea name="questions[' + qIdx + '][question_text]" class="form-control" rows="2" required></textarea></div>' +
            '<div class="col-md-4"><label class="form-label">Hint</label><input type="text" name="questions[' + qIdx + '][hint]" class="form-control" placeholder="Optional hint"></div>' +
            '<div class="col-md-4"><label class="form-label">Explanation</label><input type="text" name="questions[' + qIdx + '][explanation]" class="form-control" placeholder="Why is the answer correct?"></div>' +
            '<div class="col-md-4"><label class="form-label">Worked Solution</label><input type="text" name="questions[' + qIdx + '][worked_out_solution]" class="form-control" placeholder="Step-by-step solution"></div>' +
            '</div></div></div>';
        $('#questionsContainer').append(html);
        qIdx++;
    });

    $(document).on('click', '.remove-question-btn', function() {
        $(this).closest('.question-block').remove();
    });
});
</script>
@endsection
