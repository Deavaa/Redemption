@extends('layouts.admin')

@section('title', 'Bulk Add Assessment Questions')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.assessment-questions.index') }}">Self-Assessment</a></li>
                <li class="active">Bulk Add</li>
            </ol></nav>
            <h1 class="modern-page-title">Bulk Add Questions</h1>
            <p class="modern-page-subtitle">Add multiple questions at once for the same subject and class</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.assessment-questions.index') }}" class="btn-modern btn-modern-ghost"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.assessment-questions.bulk-store') }}" id="bulkForm">
        @csrf

        {{-- Common Settings --}}
        <div class="modern-card" style="margin-bottom:1.25rem">
            <div class="modern-card-header">
                <div class="modern-card-header-left">
                    <i class="fas fa-cog" style="color:#4361ee"></i>
                    <span class="modern-card-title">Common Settings</span>
                </div>
            </div>
            <div style="padding:1.5rem">
                <div class="modern-form-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem">
                    <div class="modern-form-group">
                        <label class="modern-form-label">Subject <span class="modern-required">*</span></label>
                        <select name="subject_id" class="modern-input modern-select" style="padding-left:0.75rem" required>
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Class <span class="modern-required">*</span></label>
                        <select name="class_id" class="modern-input modern-select" style="padding-left:0.75rem" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Difficulty</label>
                        <select name="difficulty" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="easy">Easy</option>
                            <option value="medium" selected>Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Topic</label>
                        <input type="text" name="topic" class="modern-input" style="padding-left:0.75rem" placeholder="e.g. Chapter 3">
                    </div>
                </div>
            </div>
        </div>

        {{-- Questions --}}
        <div id="questionsContainer">
            <div class="modern-card mb-3 question-block" data-index="0">
                <div class="modern-card-header">
                    <div class="modern-card-header-left">
                        <span class="modern-card-title">Question #1</span>
                    </div>
                    <div class="modern-card-header-right">
                        <button type="button" class="modern-btn-icon modern-btn-delete remove-question-btn" title="Remove"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div style="padding:1.5rem">
                    <div class="modern-form-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Type</label>
                            <select name="questions[0][question_type]" class="modern-input modern-select" style="padding-left:0.75rem">
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="true_false">True / False</option>
                                <option value="short_answer">Short Answer</option>
                            </select>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Marks</label>
                            <input type="number" name="questions[0][marks]" class="modern-input" style="padding-left:0.75rem" value="1" min="1">
                        </div>
                        <div class="modern-form-group" style="grid-column:1/-1">
                            <label class="modern-form-label">Question Text <span class="modern-required">*</span></label>
                            <textarea name="questions[0][question_text]" class="modern-input modern-textarea" rows="2" required></textarea>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Hint</label>
                            <input type="text" name="questions[0][hint]" class="modern-input" style="padding-left:0.75rem" placeholder="Optional hint">
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Explanation</label>
                            <input type="text" name="questions[0][explanation]" class="modern-input" style="padding-left:0.75rem" placeholder="Why is the answer correct?">
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Worked Solution</label>
                            <input type="text" name="questions[0][worked_out_solution]" class="modern-input" style="padding-left:0.75rem" placeholder="Step-by-step solution">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align:center;margin-bottom:1.25rem">
            <button type="button" class="btn-modern btn-modern-outline" id="addQuestionBtn">
                <i class="fas fa-plus"></i> Add Another Question
            </button>
        </div>

        <div class="modern-card">
            <div style="padding:1.25rem 1.5rem;display:flex;justify-content:flex-end;gap:0.5rem">
                <a href="{{ route('admin.assessment-questions.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-save"></i> Save All Questions</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(function() {
    var qIdx = 1;
    $('#addQuestionBtn').on('click', function() {
        var html = '<div class="modern-card mb-3 question-block" data-index="' + qIdx + '">' +
            '<div class="modern-card-header">' +
            '<div class="modern-card-header-left"><span class="modern-card-title">Question #' + (qIdx + 1) + '</span></div>' +
            '<div class="modern-card-header-right"><button type="button" class="modern-btn-icon modern-btn-delete remove-question-btn" title="Remove"><i class="fas fa-times"></i></button></div>' +
            '</div>' +
            '<div style="padding:1.5rem"><div class="modern-form-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem">' +
            '<div class="modern-form-group"><label class="modern-form-label">Type</label><select name="questions[' + qIdx + '][question_type]" class="modern-input modern-select" style="padding-left:0.75rem"><option value="multiple_choice">Multiple Choice</option><option value="true_false">True / False</option><option value="short_answer">Short Answer</option></select></div>' +
            '<div class="modern-form-group"><label class="modern-form-label">Marks</label><input type="number" name="questions[' + qIdx + '][marks]" class="modern-input" style="padding-left:0.75rem" value="1" min="1"></div>' +
            '<div class="modern-form-group" style="grid-column:1/-1"><label class="modern-form-label">Question Text <span class="modern-required">*</span></label><textarea name="questions[' + qIdx + '][question_text]" class="modern-input modern-textarea" rows="2" required></textarea></div>' +
            '<div class="modern-form-group"><label class="modern-form-label">Hint</label><input type="text" name="questions[' + qIdx + '][hint]" class="modern-input" style="padding-left:0.75rem" placeholder="Optional hint"></div>' +
            '<div class="modern-form-group"><label class="modern-form-label">Explanation</label><input type="text" name="questions[' + qIdx + '][explanation]" class="modern-input" style="padding-left:0.75rem" placeholder="Why is the answer correct?"></div>' +
            '<div class="modern-form-group"><label class="modern-form-label">Worked Solution</label><input type="text" name="questions[' + qIdx + '][worked_out_solution]" class="modern-input" style="padding-left:0.75rem" placeholder="Step-by-step solution"></div>' +
            '</div></div></div>';
        $('#questionsContainer').append(html);
        qIdx++;
    });

    $(document).on('click', '.remove-question-btn', function() {
        $(this).closest('.question-block').remove();
    });
});
</script>
@endpush
@endsection
