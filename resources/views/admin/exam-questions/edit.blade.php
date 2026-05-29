@extends('layouts.admin')
@section('title', 'Edit Exam Question')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.exam-questions.index') }}">Exam Questions</a></li>
                    <li class="active">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.exam-questions.show', $exam_question->id) }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-eye"></i>
                <span>View Detail</span>
            </a>
        </div>
    </div>

    {{-- Rejection/Revision Info --}}
    @if(in_array($exam_question->status, ['rejected_by_department', 'rejected_by_principal', 'revision']))
    @php
        $isRevision = $exam_question->status === 'revision';
        $isDeptRejection = $exam_question->status === 'rejected_by_department';
        $rejectionComment = $isDeptRejection ? $exam_question->department_head_comment : $exam_question->principal_comment;
        $reviewerLabel = $isRevision
            ? (strpos($exam_question->status, 'department') !== false ? 'Department Head' : 'Principal')
            : ($isDeptRejection ? 'Department Head' : 'Principal');
    @endphp
    <div class="modern-info-banner" style="background:#fef2f2;border-color:#fecaca;color:#991b1b;">
        <i class="fas fa-exclamation-triangle" style="color:#dc2626;"></i>
        <span>
            @if($isRevision)
                Revision has been requested. Please update your questions and resubmit.
            @else
                Rejected by {{ $reviewerLabel }}. Please revise and resubmit.
            @endif
            @if($rejectionComment)
                <br><strong>Feedback:</strong> {{ $rejectionComment }}
            @endif
        </span>
    </div>
    @endif

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

    <div class="modern-card">
        <form method="POST" action="{{ route('admin.exam-questions.update', $exam_question->id) }}" id="examQuestionEditForm" enctype="multipart/form-data">
            @csrf @method('PUT')

            {{-- Question Information --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Question Information</h3>
                        <p class="modern-form-section-desc">Update the exam question details</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="title">Title <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-pen modern-input-icon"></i>
                                <input type="text" name="title" id="title"
                                    class="modern-input {{ $errors->has('title') ? 'is-invalid' : '' }}"
                                    value="{{ old('title', $exam_question->title) }}" required>
                            </div>
                            @error('title')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="question_type">Question Type <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-list modern-input-icon"></i>
                                <select name="question_type" id="question_type" class="modern-input modern-select" required>
                                    @foreach(\App\Models\ExamQuestion::questionTypeOptions() as $key => $label)
                                    <option value="{{ $key }}" {{ old('question_type', $exam_question->question_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('question_type')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="total_marks">Total Marks <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-star modern-input-icon"></i>
                                <input type="number" name="total_marks" id="total_marks"
                                    class="modern-input" value="{{ old('total_marks', $exam_question->total_marks) }}" min="1" required>
                            </div>
                            @error('total_marks')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="duration_minutes">Duration (minutes) <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-clock modern-input-icon"></i>
                                <input type="number" name="duration_minutes" id="duration_minutes"
                                    class="modern-input" value="{{ old('duration_minutes', $exam_question->duration_minutes) }}" min="1">
                            </div>
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
                        <p class="modern-form-section-desc">Select the subject, class, section, branch and academic year</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="subject_id">Subject <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-book modern-input-icon"></i>
                                <select name="subject_id" id="subject_id" class="modern-input modern-select" required>
                                    <option value="">-- Select Subject --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id', $exam_question->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('subject_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="class_id">Class <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-building modern-input-icon"></i>
                                <select name="class_id" id="class_id" class="modern-input modern-select" required>
                                    <option value="">-- Select Class --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id', $exam_question->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('class_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="section_id">Section <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-layer-group modern-input-icon"></i>
                                <select name="section_id" id="section_id" class="modern-input modern-select">
                                    <option value="">-- All Sections --</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}" {{ old('section_id', $exam_question->section_id) == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('section_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="branch_id">Branch <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-sitemap modern-input-icon"></i>
                                <select name="branch_id" id="branch_id" class="modern-input modern-select">
                                    <option value="">-- Select Branch --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', $exam_question->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('branch_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="exam_id">Exam <small>(optional — auto-fills year & term)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-file-signature modern-input-icon"></i>
                                <select name="exam_id" id="exam_id" class="modern-input modern-select">
                                    <option value="">-- Select Exam --</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}" {{ old('exam_id', $exam_question->exam_id) == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('exam_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="academic_year_id">Academic Year <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar modern-input-icon"></i>
                                <select name="academic_year_id" id="academic_year_id" class="modern-input modern-select">
                                    <option value="">-- Select Year --</option>
                                    @foreach($academicYears as $ay)
                                        <option value="{{ $ay->id }}" {{ old('academic_year_id', $exam_question->academic_year_id) == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="term_id">Term <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-list-ol modern-input-icon"></i>
                                <select name="term_id" id="term_id" class="modern-input modern-select">
                                    <option value="">-- Select Term --</option>
                                    @foreach($terms as $term)
                                        <option value="{{ $term->id }}" {{ old('term_id', $exam_question->term_id) == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                                    @endforeach
                                </select>
                            </div>
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
                        <p class="modern-form-section-desc">Edit your exam questions below</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <textarea name="questions" id="questions"
                        class="modern-input modern-textarea" rows="12" required
                        style="padding-left:0.9rem;font-family:inherit;"
                        placeholder="Type or paste all exam questions here...">{{ old('questions', $exam_question->questions) }}</textarea>
                    @error('questions')<span class="modern-form-error">{{ $message }}</span>@enderror

                    <div style="margin-top:1.25rem;">
                        <label class="modern-form-label">Notes / Special Instructions</label>
                        <textarea name="description" class="modern-input modern-textarea" rows="2"
                            style="padding-left:0.9rem;"
                            placeholder="Additional notes or special instructions for the reviewer...">{{ old('description', $exam_question->description) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Attachment --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-purple">
                        <i class="fas fa-paperclip"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Attachment <small style="font-weight:400;color:#9ca3af;">(optional)</small></h3>
                        <p class="modern-form-section-desc">Upload or replace the attached file</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    @if($exam_question->attachment)
                    <div style="margin-bottom:0.75rem;padding:0.5rem 0.75rem;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;display:flex;align-items:center;gap:0.5rem;">
                        <i class="fas fa-file" style="color:#10b981;"></i>
                        <span style="color:#065f46;font-weight:500;">{{ basename($exam_question->attachment) }}</span>
                        <span style="color:#6b7280;font-size:0.8rem;">(current file)</span>
                    </div>
                    @endif
                    <div class="eq-dropzone" onclick="document.getElementById('attachmentInput').click()" style="border:2px dashed #d1d5db;border-radius:12px;padding:1.5rem;text-align:center;cursor:pointer;transition:all 0.2s;">
                        <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:#9ca3af;margin-bottom:0.25rem;"></i>
                        <p style="color:#6b7280;margin:0;font-size:0.85rem;">Click to upload replacement file</p>
                        <p style="color:#9ca3af;font-size:0.75rem;margin:0.15rem 0 0;">PDF, DOC, DOCX, XLSX, PPT, TXT, JPG, PNG (max 10MB)</p>
                        <input type="file" name="attachment" id="attachmentInput" accept=".pdf,.doc,.docx,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png" style="display:none;">
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions" style="justify-content:space-between;">
                <a href="{{ route('admin.exam-questions.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <div style="display:flex;gap:0.75rem;">
                    <button type="submit" name="action" value="draft" class="btn-modern btn-modern-outline">
                        <i class="fas fa-save"></i> Save as Draft
                    </button>
                    <button type="submit" name="action" value="submit" class="btn-modern btn-modern-primary">
                        <i class="fas fa-paper-plane"></i>
                        @if(in_array($exam_question->status, ['draft','dept_rejected','principal_rejected','rejected_by_department','rejected_by_principal','revision']))
                            Resubmit for Review
                        @else
                            Submit for Review
                        @endif
                    </button>
                </div>
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
.modern-info-banner { display:flex; align-items:flex-start; gap:.65rem; padding:.85rem 1.25rem; border-radius:12px; margin-bottom:1.75rem; font-size:.88rem; }
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
    .modern-page-header { flex-direction:column; }
    .modern-form-grid { grid-template-columns:1fr; }
    .modern-form-span-2 { grid-column:span 1; }
    .modern-form-actions { flex-direction:column; }
    .btn-modern { justify-content:center; width:100%; }
}
</style>
@endpush

@push('scripts')
<script>
$(function() {
    // Load sections when class changes
    var classSelect = $('#class_id');
    var sectionSelect = $('#section_id');

    classSelect.on('change', function() {
        var classId = $(this).val();
        sectionSelect.html('<option value="">-- All Sections --</option>');
        if (!classId) return;

        $.ajax({
            url: '{{ route("admin.api.sections-by-class") }}',
            data: { class_id: classId },
            dataType: 'json',
            success: function(data) {
                $.each(data, function(i, sec) {
                    sectionSelect.append('<option value="' + sec.id + '">' + sec.name + '</option>');
                });
                // Re-select the current section if it's still in the list
                var currentSection = '{{ $exam_question->section_id }}';
                if (currentSection) {
                    sectionSelect.val(currentSection);
                }
            }
        });
    });

    // Auto-fill academic year and term when exam is selected
    var examSelect = $('#exam_id');
    var aySelect = $('#academic_year_id');
    var termSelect = $('#term_id');

    examSelect.on('change', function() {
        var examId = $(this).val();
        if (!examId) return;

        $.ajax({
            url: '{{ route("admin.api.exam-details", ["exam" => 0]) }}'.replace('/0', '/' + examId),
            dataType: 'json',
            success: function(data) {
                if (data.academic_year_id) {
                    aySelect.val(data.academic_year_id);
                }
                if (data.term_id) {
                    termSelect.val(data.term_id);
                }
            },
            error: function() {
                // Silently ignore — manual selection still works
            }
        });
    });
});
</script>
@endpush
@endsection
