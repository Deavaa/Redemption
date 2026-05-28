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
            <p class="modern-page-subtitle">Add multiple questions at once via file import or manual entry</p>
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

    {{-- ── Import from File ──────────────────────────────────── --}}
    <div class="modern-card" style="margin-bottom:1.25rem">
        <div class="modern-card-header" style="background:linear-gradient(135deg,#10b981,#059669)">
            <div class="modern-card-header-left">
                <i class="fas fa-file-upload" style="color:#fff"></i>
                <span class="modern-card-title" style="color:#fff">Import from File</span>
            </div>
            <div class="modern-card-header-right">
                <a href="{{ route('admin.assessment-questions.download-template') }}" class="btn-modern" style="background:#fff;color:#059669;font-size:0.82rem;padding:6px 14px;border-radius:8px">
                    <i class="fas fa-download"></i> Download Template
                </a>
            </div>
        </div>
        <div style="padding:1.5rem">
            <form method="POST" action="{{ route('admin.assessment-questions.bulk-import') }}" enctype="multipart/form-data" id="importForm">
                @csrf
                <div style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap">
                    <div class="modern-form-group" style="flex:1;min-width:250px">
                        <label class="modern-form-label">Upload CSV / Excel File</label>
                        <input type="file" name="import_file" id="importFile" accept=".csv,.txt,.xlsx,.xls" class="modern-input" style="padding:0.5rem" required>
                        <div style="font-size:0.75rem;color:#9ca3af;margin-top:4px">
                            Accepted formats: .csv, .xlsx, .xls &nbsp;|&nbsp; Max size: 5MB
                        </div>
                    </div>
                    <button type="submit" class="btn-modern btn-modern-primary" id="importBtn" style="height:42px">
                        <i class="fas fa-upload"></i> Import Questions
                    </button>
                </div>
            </form>

            {{-- Template Instructions --}}
            <div style="margin-top:1.25rem;padding:1rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px">
                <h4 style="font-size:0.85rem;font-weight:600;color:#166534;margin-bottom:0.5rem">
                    <i class="fas fa-info-circle"></i> Template Instructions
                </h4>
                <ul style="font-size:0.8rem;color:#374151;margin:0;padding-left:1.25rem;line-height:1.7">
                    <li><strong>question_text</strong> (required) — The question to ask students</li>
                    <li><strong>question_type</strong> — <code>multiple_choice</code>, <code>true_false</code>, or <code>short_answer</code></li>
                    <li><strong>subject_name</strong> — Must match an existing subject name in the system</li>
                    <li><strong>class_name</strong> — Must match an existing class name (e.g., "Grade 7")</li>
                    <li><strong>difficulty</strong> — <code>easy</code>, <code>medium</code>, or <code>hard</code></li>
                    <li><strong>marks</strong> — Number (1-100), defaults to 1</li>
                    <li><strong>option_A</strong> to <strong>option_D</strong> — For multiple choice, at least A and B are required</li>
                    <li><strong>correct_option</strong> — Letter (A/B/C/D) for MCQ, or <code>true</code>/<code>false</code> for T/F</li>
                    <li><strong>explanation</strong> — Why the answer is correct (shown after answering)</li>
                    <li><strong>worked_out_solution</strong> — Step-by-step solution</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ── Manual Entry Divider ────────────────────────────────── --}}
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.25rem">
        <div style="flex:1;height:1px;background:#e5e7eb"></div>
        <span style="font-size:0.82rem;color:#9ca3af;font-weight:500">OR ENTER MANUALLY</span>
        <div style="flex:1;height:1px;background:#e5e7eb"></div>
    </div>

    {{-- ── Manual Entry Form ────────────────────────────────────── --}}
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
                        <select name="class_id" id="classSelect" class="modern-input modern-select" style="padding-left:0.75rem" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Applies To</label>
                        <div class="modern-input" style="padding-left:0.75rem;display:flex;align-items:center;gap:6px;color:#059669;font-weight:500;background:#ecfdf5;border:1px solid #a7f3d0">
                            <i class="fas fa-globe"></i> All Branches &amp; Sections
                        </div>
                        <div style="font-size:0.72rem;color:#6b7280;margin-top:3px">Questions apply to all students in this class</div>
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

@push('styles')
<style>
    #importFile::file-selector-button {
        background: #4361ee;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        margin-right: 12px;
        font-size: 0.82rem;
    }
    #importFile::file-selector-button:hover {
        background: #3a56d4;
    }
</style>
@endpush

@push('scripts')
<script>
$(function() {
    // ── Manual question blocks ──────────────────────────────
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

    // Import form validation
    $('#importForm').on('submit', function() {
        var file = $('#importFile').val();
        if (!file) {
            alert('Please select a file to import.');
            return false;
        }
        $('#importBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');
    });
});
</script>
@endpush
@endsection
