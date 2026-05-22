@extends('layouts.admin')
@section('title', 'Submit Exam Question')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.exam-questions.index') }}">Exam Questions</a></li>
                    <li class="active">Submit New</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.exam-questions.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="modern-alert modern-alert-error" style="margin-bottom:1rem;">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="modern-alert modern-alert-error" style="margin-bottom:1rem;">
            <i class="fas fa-exclamation-circle"></i>
            <span>Please fix the errors below:</span>
            <ul style="margin:0.5rem 0 0 1rem;font-size:0.85rem;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    {{-- Workflow Info --}}
    <div class="modern-info-banner">
        <i class="fas fa-route"></i>
        <span>After submission, your questions will be reviewed by the <strong>Department Head</strong> first, then forwarded to the <strong>Principal</strong> for final approval.</span>
    </div>

    {{-- Form Card --}}
    <div class="modern-card">
        <form method="POST" action="{{ route('admin.exam-questions.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Question Information --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Question Information</h3>
                        <p class="modern-form-section-desc">Enter the exam question title, type, and content</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="title">
                                Title <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-pen modern-input-icon"></i>
                                <input type="text" name="title" id="title"
                                    class="modern-input {{ $errors->has('title') ? 'is-invalid' : '' }}"
                                    value="{{ old('title') }}" placeholder="e.g. Grade 10 Math Midterm Questions" required autofocus>
                            </div>
                            @error('title')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="question_type">
                                Question Type <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-list modern-input-icon"></i>
                                <select name="question_type" id="question_type" class="modern-input modern-select {{ $errors->has('question_type') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="multiple_choice" {{ old('question_type') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                    <option value="true_false" {{ old('question_type') == 'true_false' ? 'selected' : '' }}>True / False</option>
                                    <option value="short_answer" {{ old('question_type') == 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                                    <option value="essay" {{ old('question_type') == 'essay' ? 'selected' : '' }}>Essay</option>
                                    <option value="fill_blank" {{ old('question_type') == 'fill_blank' ? 'selected' : '' }}>Fill in the Blank</option>
                                    <option value="mixed" {{ old('question_type') == 'mixed' ? 'selected' : '' }}>Mixed</option>
                                </select>
                            </div>
                            @error('question_type')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="total_marks">
                                Total Marks <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-star modern-input-icon"></i>
                                <input type="number" name="total_marks" id="total_marks"
                                    class="modern-input {{ $errors->has('total_marks') ? 'is-invalid' : '' }}"
                                    value="{{ old('total_marks', 100) }}" min="0" placeholder="100" required>
                            </div>
                            @error('total_marks')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="duration_minutes">
                                Duration (minutes) <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-clock modern-input-icon"></i>
                                <input type="number" name="duration_minutes" id="duration_minutes"
                                    class="modern-input {{ $errors->has('duration_minutes') ? 'is-invalid' : '' }}"
                                    value="{{ old('duration_minutes') }}" min="1" placeholder="e.g. 60">
                            </div>
                            @error('duration_minutes')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Academic Context --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Academic Context</h3>
                        <p class="modern-form-section-desc">Select the subject, class, term and academic year</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="subject_id">
                                Subject <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-book modern-input-icon"></i>
                                <select name="subject_id" id="subject_id" class="modern-input modern-select {{ $errors->has('subject_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Subject --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('subject_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="class_id">
                                Class <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-building modern-input-icon"></i>
                                <select name="class_id" id="class_id" class="modern-input modern-select {{ $errors->has('class_id') ? 'is-invalid' : '' }}">
                                    <option value="">-- All Classes --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('class_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="academic_year_id">
                                Academic Year <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar modern-input-icon"></i>
                                <select name="academic_year_id" id="exam_ay" class="modern-input modern-select {{ $errors->has('academic_year_id') ? 'is-invalid' : '' }}">
                                    <option value="">-- Select Year --</option>
                                    @foreach($academicYears as $ay)
                                        <option value="{{ $ay->id }}" {{ old('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('academic_year_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="term_id">
                                Term <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-list-ol modern-input-icon"></i>
                                <select name="term_id" id="exam_term" class="modern-input modern-select {{ $errors->has('term_id') ? 'is-invalid' : '' }}">
                                    <option value="">-- Select Year First --</option>
                                </select>
                            </div>
                            @error('term_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Questions Content --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-gold">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Questions Content</h3>
                        <p class="modern-form-section-desc">Type or paste the exam questions below, or upload a document</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-group" style="margin-bottom:1.25rem;">
                        <label class="modern-form-label" for="content">
                            Questions Content <small>(optional if uploading a file)</small>
                        </label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-comment-dots modern-input-icon modern-input-icon-textarea"></i>
                            <textarea name="content" id="content"
                                class="modern-input modern-textarea {{ $errors->has('content') ? 'is-invalid' : '' }}"
                                placeholder="Type or paste the exam questions here..." rows="10">{{ old('content') }}</textarea>
                        </div>
                        @error('content')<span class="modern-form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="attachment">
                            Upload Attachment <small>(PDF, Word, Excel, Image — max 10MB)</small>
                        </label>
                        <div class="modern-input-wrapper">
                            <input type="file" name="attachment" id="attachment"
                                class="modern-input" style="padding-left:0.9rem;"
                                accept=".pdf,.doc,.docx,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png">
                        </div>
                        @error('attachment')<span class="modern-form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-purple">
                        <i class="fas fa-sticky-note"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Additional Notes</h3>
                        <p class="modern-form-section-desc">Any notes for the reviewers</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-group">
                        <div class="modern-input-wrapper">
                            <i class="fas fa-comment modern-input-icon modern-input-icon-textarea"></i>
                            <textarea name="notes" id="notes"
                                class="modern-input modern-textarea {{ $errors->has('notes') ? 'is-invalid' : '' }}"
                                placeholder="Notes or special instructions for reviewers..." rows="3">{{ old('notes') }}</textarea>
                        </div>
                        @error('notes')<span class="modern-form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions">
                <a href="{{ route('admin.exam-questions.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" name="action" value="draft" class="btn-modern btn-modern-outline">
                    <i class="fas fa-save"></i>
                    <span>Save as Draft</span>
                </button>
                <button type="submit" name="action" value="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-paper-plane"></i>
                    <span>Submit for Review</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
.modern-page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem; }
.modern-page-header-left { flex:1; }
.modern-breadcrumb ol { display:flex; list-style:none; padding:0; margin:0 0 .5rem; gap:.5rem; font-size:.8rem; align-items:center; }
.modern-breadcrumb li { color:#adb5bd; }
.modern-breadcrumb li a { color:#6c757d; text-decoration:none; }
.modern-breadcrumb li a:hover { color:#4361ee; }
.modern-breadcrumb li+li::before { content:'/'; margin-right:.5rem; color:#dee2e6; }
.modern-breadcrumb li.active { color:#4361ee; font-weight:500; }
.modern-info-banner { display:flex; align-items:center; gap:.65rem; padding:.85rem 1.25rem; background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; margin-bottom:1.75rem; font-size:.88rem; color:#1e40af; }
.modern-info-banner i { color:#3b82f6; }
.modern-info-banner strong { color:#1e3a8a; }
.modern-card { background:#fff; border-radius:14px; box-shadow:0 1px 3px rgba(0,0,0,.06); border:1px solid #f0f0f0; overflow:hidden; }
.modern-alert { display:flex; align-items:flex-start; gap:.65rem; padding:.85rem 1.25rem; border-radius:10px; font-size:.88rem; font-weight:500; }
.modern-alert-error { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
.modern-alert-close { margin-left:auto; background:none; border:none; cursor:pointer; color:inherit; opacity:.6; font-size:1.2rem; }
.modern-alert-close:hover { opacity:1; }
.modern-form-section { border-bottom:1px solid #f0f0f0; }
.modern-form-section:last-of-type { border-bottom:none; }
.modern-form-section-header { display:flex; align-items:center; gap:1rem; padding:1.5rem 2rem .75rem; }
.modern-form-section-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
.modern-form-section-icon-blue { background:#eef2ff; color:#4361ee; }
.modern-form-section-icon-green { background:#ecfdf5; color:#10b981; }
.modern-form-section-icon-gold { background:#fefce8; color:#d97706; }
.modern-form-section-icon-purple { background:#f5f3ff; color:#7c3aed; }
.modern-form-section-title { font-size:1.05rem; font-weight:700; color:#1a1a2e; margin:0; }
.modern-form-section-desc { font-size:.82rem; color:#9ca3af; margin:.15rem 0 0; }
.modern-form-section-body { padding:1.25rem 2rem 1.75rem; }
.modern-form-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:1.25rem; }
.modern-form-span-2 { grid-column:span 2; }
.modern-form-group { display:flex; flex-direction:column; }
.modern-form-label { font-weight:600; color:#374151; margin-bottom:.45rem; font-size:.88rem; }
.modern-form-label small { font-weight:400; color:#9ca3af; font-size:.78rem; }
.modern-required { color:#ef4444; font-weight:700; }
.modern-input-wrapper { position:relative; }
.modern-input-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:.85rem; pointer-events:none; z-index:1; }
.modern-input-icon-textarea { top:1.1rem; transform:none; }
.modern-input { width:100%; border:1.5px solid #e5e7eb; border-radius:10px; padding:.7rem .9rem .7rem 2.5rem; font-size:.9rem; color:#1a1a2e; background:#fff; transition:all .2s; }
.modern-input:focus { outline:none; border-color:#4361ee; box-shadow:0 0 0 3px rgba(67,97,238,.1); }
.modern-input::placeholder { color:#c5c9d2; }
.modern-input.is-invalid { border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.1); }
.modern-textarea { resize:vertical; min-height:80px; }
.modern-select { appearance:none; cursor:pointer; background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position:right .75rem center; background-repeat:no-repeat; background-size:1.25rem; padding-right:2.5rem; }
.modern-form-error { display:block; color:#ef4444; font-size:.8rem; margin-top:.35rem; font-weight:500; }
.modern-form-actions { display:flex; justify-content:flex-end; gap:.75rem; padding:1.5rem 2rem; border-top:1px solid #f0f0f0; background:#fafbfc; }
.btn-modern { display:inline-flex; align-items:center; gap:.5rem; padding:.65rem 1.35rem; border-radius:10px; font-weight:600; font-size:.9rem; text-decoration:none; border:none; cursor:pointer; transition:all .25s; }
.btn-modern-primary { background:linear-gradient(135deg,#4361ee,#3a0ca3); color:#fff; box-shadow:0 2px 8px rgba(67,97,238,.3); }
.btn-modern-primary:hover { transform:translateY(-2px); box-shadow:0 4px 16px rgba(67,97,238,.4); color:#fff; }
.btn-modern-outline { background:transparent; color:#6b7280; border:1.5px solid #e5e7eb; }
.btn-modern-outline:hover { border-color:#4361ee; color:#4361ee; background:#f8f9ff; }
.btn-modern-ghost { background:transparent; color:#6b7280; padding:.65rem 1rem; }
.btn-modern-ghost:hover { color:#1a1a2e; background:#f3f4f6; }
@media(max-width:768px) {
    .modern-page-header { flex-direction:column; align-items:stretch; }
    .modern-form-grid { grid-template-columns:1fr; }
    .modern-form-span-2 { grid-column:span 1; }
    .modern-form-section-body { padding:1rem 1.25rem 1.5rem; }
    .modern-form-section-header { padding:1.25rem 1.25rem .75rem; }
    .modern-form-actions { padding:1rem 1.25rem; flex-direction:column; }
    .btn-modern { justify-content:center; width:100%; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    var allTerms = {!! $allTerms->toJson() !!};
    var selAY = document.getElementById('exam_ay');
    var selTerm = document.getElementById('exam_term');

    function filterTerms(ayId) {
        selTerm.innerHTML = '<option value="">-- Select Term --</option>';
        if (!ayId) return;
        for (var i = 0; i < allTerms.length; i++) {
            if (allTerms[i].academic_year_id == ayId) {
                var opt = document.createElement('option');
                opt.value = allTerms[i].id;
                opt.textContent = allTerms[i].name;
                selTerm.appendChild(opt);
            }
        }
    }

    if (selAY) {
        selAY.addEventListener('change', function() { filterTerms(this.value); });
        @if(old('academic_year_id'))
        selAY.value = '{{ old('academic_year_id') }}';
        filterTerms(selAY.value);
        @endif
    }
})();
</script>
@endpush
@endsection
