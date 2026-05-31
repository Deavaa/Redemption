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

    @if(session('error'))
        <div class="modern-alert modern-alert-error" style="margin-bottom:1rem;">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
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
        </div>
    @endif

    <div class="modern-info-banner">
        <i class="fas fa-route"></i>
        <span>After submission, your questions will be reviewed by the <strong>Department Head</strong>, then forwarded to the <strong>Principal</strong> for final approval.</span>
    </div>

    <div class="modern-card">
        <form method="POST" action="{{ route('admin.exam-questions.store') }}" id="examQuestionForm" enctype="multipart/form-data">
            @csrf

            {{-- ═══════════ SECTION 1: Context ═══════════ --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Exam Context</h3>
                        <p class="modern-form-section-desc">Select exam and subject details &mdash; fields auto-fill when possible</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid" style="margin-bottom:1rem;">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label">Title <span class="modern-required">*</span></label>
                            <input type="text" name="title" class="modern-input" value="{{ old('title') }}" placeholder="e.g. Grade 10 Math Midterm Questions" required autofocus style="padding-left:0.9rem;">
                            @error('title')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="modern-form-grid" style="margin-bottom:1rem;">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Exam <small style="color:#4361ee;">(auto-fills year &amp; term)</small></label>
                            <select name="exam_id" id="exam_id" class="modern-input modern-select">
                                <option value="">-- Select Exam --</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ old('exam_id') == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                                @endforeach
                            </select>
                            @error('exam_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Subject <span class="modern-required">*</span></label>
                            <select name="subject_id" id="subject_id" class="modern-input modern-select" required>
                                <option value="">-- Select Subject --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }} data-teacher="{{ in_array($subject->id, $teacherSubjectIds) ? '1' : '0' }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Class <span class="modern-required">*</span></label>
                            <select name="class_id" id="class_id" class="modern-input modern-select" required>
                                <option value="">-- Select Class --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }} data-teacher="{{ in_array($class->id, $teacherClassIds) ? '1' : '0' }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            @error('class_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Question Type <span class="modern-required">*</span></label>
                            <select name="question_type" id="question_type" class="modern-input modern-select" required>
                                <option value="">-- Select Type --</option>
                                @foreach(\App\Models\ExamQuestion::questionTypeOptions() as $key => $label)
                                <option value="{{ $key }}" {{ old('question_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('question_type')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="modern-form-grid" style="margin-bottom:0;">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Total Marks <span class="modern-required">*</span></label>
                            <input type="number" name="total_marks" id="total_marks_input" class="modern-input" value="{{ old('total_marks', 100) }}" min="1" required style="padding-left:0.9rem;">
                            @error('total_marks')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Duration (minutes)</label>
                            <input type="number" name="duration_minutes" class="modern-input" value="{{ old('duration_minutes') }}" min="1" placeholder="e.g. 60" style="padding-left:0.9rem;">
                            @error('duration_minutes')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- More Options Toggle --}}
                    <div style="margin-top:1.25rem;border-top:1px solid #f0f0f0;padding-top:1rem;">
                        <button type="button" id="moreOptionsToggle" class="eq-more-toggle" onclick="toggleMoreOptions()">
                            <i class="fas fa-chevron-down" id="moreOptionsIcon"></i>
                            <span id="moreOptionsLabel">More Options</span>
                            <span class="eq-more-hint">Section, Branch, Year, Term</span>
                        </button>
                        <div id="moreOptionsPanel" style="display:none;margin-top:1rem;">
                            <div class="modern-form-grid">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">Section</label>
                                    <select name="section_id" id="section_id" class="modern-input modern-select">
                                        <option value="">-- All Sections --</option>
                                    </select>
                                    @error('section_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="modern-form-group">
                                    <label class="modern-form-label">Branch</label>
                                    <select name="branch_id" id="branch_id" class="modern-input modern-select">
                                        <option value="">-- Select Branch --</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="modern-form-group">
                                    <label class="modern-form-label">Academic Year</label>
                                    <select name="academic_year_id" id="academic_year_id" class="modern-input modern-select">
                                        <option value="">-- Select Year --</option>
                                        @foreach($academicYears as $ay)
                                            <option value="{{ $ay->id }}" {{ old('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="modern-form-group">
                                    <label class="modern-form-label">Term</label>
                                    <select name="term_id" id="term_id" class="modern-input modern-select">
                                        <option value="">-- Select Term --</option>
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════ SECTION 2: Questions ═══════════ --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-gold">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div style="flex:1;">
                        <h3 class="modern-form-section-title">Questions</h3>
                        <p class="modern-form-section-desc">Add questions using the type-specific organizer &mdash; auto-formatted for you</p>
                    </div>
                    <div class="eq-stats-bar" id="eqStatsBar">
                        <div class="eq-stat"><i class="fas fa-list-ol"></i> <span id="eqStatCount">0</span> Questions</div>
                        <div class="eq-stat"><i class="fas fa-star"></i> <span id="eqStatMarks">0</span> Marks</div>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    {{-- Mode Selector --}}
                    <div class="eq-mode-selector">
                        <button type="button" class="eq-mode-btn eq-mode-active" data-mode="builder" onclick="switchMode('builder')">
                            <i class="fas fa-th-list"></i>
                            <span>Question Builder</span>
                            <small>Type-specific organizer</small>
                        </button>
                        <button type="button" class="eq-mode-btn" data-mode="quicktype" onclick="switchMode('quicktype')">
                            <i class="fas fa-keyboard"></i>
                            <span>Quick Type</span>
                            <small>Paste or type</small>
                        </button>
                        <button type="button" class="eq-mode-btn" data-mode="upload" onclick="switchMode('upload')">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Upload File</span>
                            <small>Easiest</small>
                        </button>
                    </div>

                    {{-- ════════ MODE 1: QUESTION BUILDER ════════ --}}
                    <div id="modeBuilder" class="eq-mode-panel">
                        {{-- Sub-type selector (for mixed) --}}
                        <div id="builderTypeBar" class="eq-builder-typebar" style="display:none;">
                            <span class="eq-builder-typebar-label">Add question as:</span>
                            <div class="eq-type-chips">
                                <button type="button" class="eq-type-chip eq-type-chip-active" data-qt="multiple_choice" onclick="setBuilderQType('multiple_choice')">
                                    <i class="fas fa-list-ul"></i> Multiple Choice
                                </button>
                                <button type="button" class="eq-type-chip" data-qt="true_false" onclick="setBuilderQType('true_false')">
                                    <i class="fas fa-check-double"></i> True / False
                                </button>
                                <button type="button" class="eq-type-chip" data-qt="short_answer" onclick="setBuilderQType('short_answer')">
                                    <i class="fas fa-align-left"></i> Short Answer
                                </button>
                                <button type="button" class="eq-type-chip" data-qt="essay" onclick="setBuilderQType('essay')">
                                    <i class="fas fa-pen-fancy"></i> Essay
                                </button>
                                <button type="button" class="eq-type-chip" data-qt="fill_blank" onclick="setBuilderQType('fill_blank')">
                                    <i class="fas fa-underline"></i> Fill in the Blank
                                </button>
                            </div>
                        </div>

                        {{-- Question Cards List --}}
                        <div id="questionsList"></div>

                        {{-- Add Question Button --}}
                        <div class="eq-builder-actions">
                            <button type="button" onclick="addQuestion()" class="eq-add-btn">
                                <i class="fas fa-plus"></i> Add Question
                            </button>
                            <button type="button" onclick="addBulkQuestions()" class="eq-add-btn eq-add-btn-secondary" title="Add multiple questions at once">
                                <i class="fas fa-layer-group"></i> Add Multiple
                            </button>
                        </div>
                    </div>

                    {{-- ════════ MODE 2: QUICK TYPE ════════ --}}
                    <div id="modeQuicktype" class="eq-mode-panel" style="display:none;">
                        <div class="eq-quicktype-wrapper">
                            <div class="eq-quicktype-toolbar">
                                <span class="eq-quicktype-hint"><i class="fas fa-info-circle"></i> Type questions &mdash; auto-formatted by type. Add marks in parentheses.</span>
                                <div style="display:flex;gap:0.5rem;">
                                    <button type="button" class="eq-toolbar-btn" onclick="insertTemplate('mc')" title="Multiple Choice template">
                                        <i class="fas fa-list-ul"></i> MC
                                    </button>
                                    <button type="button" class="eq-toolbar-btn" onclick="insertTemplate('tf')" title="True/False template">
                                        <i class="fas fa-check-double"></i> T/F
                                    </button>
                                    <button type="button" class="eq-toolbar-btn" onclick="insertTemplate('essay')" title="Essay template">
                                        <i class="fas fa-pen-fancy"></i> Essay
                                    </button>
                                    <button type="button" class="eq-toolbar-btn" onclick="insertTemplate('fill')" title="Fill in blank template">
                                        <i class="fas fa-underline"></i> Fill
                                    </button>
                                </div>
                            </div>
                            <textarea name="questions" id="quicktypeQuestions" class="eq-quicktype-textarea" placeholder="Type your exam questions here...&#10;&#10;Examples by type:&#10;&#10;MULTIPLE CHOICE:&#10;1. What is the capital of Ethiopia? (2 marks)&#10;   A) Addis Ababa  B) Nairobi  C) Cairo  D) Kampala&#10;   Answer: A&#10;&#10;TRUE/FALSE:&#10;2. Water boils at 100 degrees Celsius. (1 mark)&#10;   Answer: True&#10;&#10;SHORT ANSWER:&#10;3. Define photosynthesis. (3 marks)&#10;&#10;ESSAY:&#10;4. Discuss the causes and effects of climate change. (10 marks)&#10;&#10;FILL IN THE BLANK:&#10;5. The largest ocean on Earth is the _____ Ocean. (2 marks)" rows="14">{{ old('questions') }}</textarea>
                            <div class="eq-quicktype-footer">
                                <span id="questionCount">0 questions</span>
                                <span id="totalMarksPreview">0 marks total</span>
                            </div>
                        </div>
                    </div>

                    {{-- ════════ MODE 3: UPLOAD FILE ════════ --}}
                    <div id="modeUpload" class="eq-mode-panel" style="display:none;">
                        <div class="eq-upload-zone" id="dropzone" onclick="document.getElementById('attachmentInput').click()">
                            <div class="eq-upload-icon">
                                <i class="fas fa-file-upload"></i>
                            </div>
                            <h4>Drop your exam question file here</h4>
                            <p>or click to browse</p>
                            <span class="eq-upload-formats">PDF, DOC, DOCX, XLSX, PPT, TXT, JPG, PNG (max 10MB)</span>
                            <input type="file" name="attachment" id="attachmentInput" accept=".pdf,.doc,.docx,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png" style="display:none;" onchange="showFileName(this)">
                        </div>
                        <div id="fileInfo" style="display:none;margin-top:0.75rem;">
                            <div class="eq-file-preview">
                                <i class="fas fa-file-check"></i>
                                <span id="fileName"></span>
                                <button type="button" onclick="removeFile()" class="eq-file-remove"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <div class="eq-upload-hint">
                            <i class="fas fa-lightbulb"></i>
                            <span>When uploading a file, the questions text is optional. The uploaded document will serve as the exam questions.</span>
                        </div>
                        <input type="hidden" name="questions" id="uploadQuestions" value="See attached file">
                    </div>

                    @error('questions')<span class="modern-form-error">{{ $message }}</span>@enderror

                    {{-- Notes/Description --}}
                    <div style="margin-top:1.25rem;">
                        <label class="modern-form-label">Notes / Special Instructions <small>(optional)</small></label>
                        <textarea name="description" class="modern-input modern-textarea" placeholder="Any additional notes for the reviewer..." rows="2" style="padding-left:0.9rem;font-family:inherit;">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ═══════════ PREVIEW SECTION ═══════════ --}}
            <div class="modern-form-section" id="previewSection" style="display:none;">
                <div class="modern-form-section-header" style="cursor:pointer;" onclick="togglePreview()">
                    <div class="modern-form-section-icon modern-form-section-icon-green">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Formatted Preview</h3>
                        <p class="modern-form-section-desc">See how your questions will appear when formatted</p>
                    </div>
                    <i class="fas fa-chevron-down" id="previewChevron" style="margin-left:auto;color:#9ca3af;transition:transform 0.3s;"></i>
                </div>
                <div class="modern-form-section-body" id="previewBody">
                    <div id="previewContent" class="eq-preview-content"></div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions" style="justify-content:space-between;">
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <a href="{{ route('admin.exam-questions.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                    <button type="button" onclick="showPreview()" class="btn-modern btn-modern-outline" title="Preview formatted questions (Ctrl+P)">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                </div>
                <div style="display:flex;gap:0.75rem;">
                    <button type="submit" name="action" value="submit" class="btn-modern btn-modern-primary" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Submit for Review
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Bulk Add Modal --}}
<div id="bulkModal" class="eq-modal" style="display:none;">
    <div class="eq-modal-overlay" onclick="closeBulkModal()"></div>
    <div class="eq-modal-content">
        <div class="eq-modal-header">
            <h3><i class="fas fa-layer-group"></i> Add Multiple Questions</h3>
            <button type="button" onclick="closeBulkModal()" class="eq-modal-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="eq-modal-body">
            <label class="modern-form-label">Number of questions to add</label>
            <div style="display:flex;gap:0.75rem;align-items:center;">
                <input type="number" id="bulkCount" class="modern-input" value="5" min="1" max="50" style="width:100px;padding-left:0.9rem;">
                <button type="button" onclick="executeBulkAdd()" class="btn-modern btn-modern-primary" style="padding:0.55rem 1.2rem;">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
            <p style="margin-top:0.5rem;font-size:0.82rem;color:#6b7280;">Questions will be added with the current type template. You can edit each one individually.</p>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ── Mode Selector ── */
.eq-mode-selector { display:flex;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap; }
.eq-mode-btn {
    flex:1;min-width:140px;padding:0.85rem 1rem;border:2px solid #e5e7eb;border-radius:12px;
    background:#fff;cursor:pointer;transition:all 0.25s;display:flex;flex-direction:column;
    align-items:center;gap:0.25rem;position:relative;
}
.eq-mode-btn i { font-size:1.3rem;color:#9ca3af;transition:color 0.25s; }
.eq-mode-btn span { font-weight:700;font-size:0.9rem;color:#374151; }
.eq-mode-btn small { font-size:0.72rem;color:#9ca3af;font-weight:500; }
.eq-mode-btn:hover { border-color:#c7d2fe;background:#f8f9ff; }
.eq-mode-btn:hover i { color:#4361ee; }
.eq-mode-active { border-color:#4361ee !important;background:#eef2ff !important; }
.eq-mode-active i { color:#4361ee !important; }
.eq-mode-active span { color:#3a0ca3 !important; }
.eq-mode-active::after {
    content:'';position:absolute;bottom:-2px;left:20%;right:20%;height:3px;
    background:linear-gradient(90deg,#4361ee,#3a0ca3);border-radius:3px 3px 0 0;
}

/* ── Stats Bar ── */
.eq-stats-bar { display:flex;gap:1.25rem;align-items:center; }
.eq-stat { display:flex;align-items:center;gap:0.35rem;font-size:0.85rem;font-weight:600;color:#6b7280;background:#f3f4f6;padding:0.3rem 0.7rem;border-radius:8px; }
.eq-stat i { color:#4361ee;font-size:0.8rem; }

/* ── Builder Type Bar (for Mixed) ── */
.eq-builder-typebar {
    display:flex;align-items:center;gap:0.75rem;padding:0.65rem 0.85rem;
    background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:10px;margin-bottom:1rem;flex-wrap:wrap;
}
.eq-builder-typebar-label { font-size:0.82rem;font-weight:600;color:#6b7280;white-space:nowrap; }
.eq-type-chips { display:flex;gap:0.4rem;flex-wrap:wrap; }
.eq-type-chip {
    padding:0.3rem 0.65rem;border:1.5px solid #e5e7eb;border-radius:20px;background:#fff;
    font-size:0.78rem;font-weight:600;color:#6b7280;cursor:pointer;transition:all 0.2s;
    display:flex;align-items:center;gap:0.3rem;
}
.eq-type-chip:hover { border-color:#c7d2fe;color:#4361ee;background:#f8f9ff; }
.eq-type-chip-active { border-color:#4361ee !important;color:#4361ee !important;background:#eef2ff !important; }

/* ── Question Cards ── */
.eq-question-card {
    border:1.5px solid #e5e7eb;border-radius:12px;padding:0.85rem 1rem;margin-bottom:0.85rem;
    background:#fafbfc;transition:all 0.25s;position:relative;
}
.eq-question-card:hover { border-color:#c7d2fe;background:#f8f9ff; }
.eq-question-card[data-qt="multiple_choice"] { border-left:4px solid #4361ee; }
.eq-question-card[data-qt="true_false"] { border-left:4px solid #10b981; }
.eq-question-card[data-qt="short_answer"] { border-left:4px solid #f59e0b; }
.eq-question-card[data-qt="essay"] { border-left:4px solid #7c3aed; }
.eq-question-card[data-qt="fill_blank"] { border-left:4px solid #ec4899; }

.eq-question-header { display:flex;align-items:center;gap:0.65rem;margin-bottom:0.65rem;flex-wrap:wrap; }
.eq-question-number {
    background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;font-weight:700;font-size:0.8rem;
    padding:0.25rem 0.6rem;border-radius:6px;min-width:32px;text-align:center;
}
.eq-question-type-badge {
    font-size:0.7rem;font-weight:700;padding:0.2rem 0.55rem;border-radius:20px;text-transform:uppercase;letter-spacing:0.5px;
}
.eq-question-type-badge[data-qt="multiple_choice"] { background:#eef2ff;color:#4361ee; }
.eq-question-type-badge[data-qt="true_false"] { background:#ecfdf5;color:#10b981; }
.eq-question-type-badge[data-qt="short_answer"] { background:#fefce8;color:#d97706; }
.eq-question-type-badge[data-qt="essay"] { background:#f5f3ff;color:#7c3aed; }
.eq-question-type-badge[data-qt="fill_blank"] { background:#fdf2f8;color:#ec4899; }

.eq-marks-input { width:75px;padding:0.25rem 0.5rem;border:1.5px solid #e5e7eb;border-radius:6px;font-size:0.82rem;text-align:center; }
.eq-marks-input:focus { outline:none;border-color:#4361ee; }

.eq-qtype-inline {
    padding:0.2rem 0.5rem;border:1.5px solid #e5e7eb;border-radius:6px;font-size:0.78rem;
    background:#fff;cursor:pointer;color:#6b7280;font-weight:600;
}
.eq-qtype-inline:focus { outline:none;border-color:#4361ee; }

.eq-remove-btn {
    margin-left:auto;background:#fef2f2;color:#ef4444;border:none;width:28px;height:28px;
    border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;
}
.eq-remove-btn:hover { background:#ef4444;color:#fff; }

.eq-question-text {
    width:100%;border:1.5px solid #e5e7eb;border-radius:8px;padding:0.6rem 0.75rem;font-size:0.9rem;
    resize:vertical;min-height:55px;font-family:inherit;
}
.eq-question-text:focus { outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,0.1); }

/* ── MC Options ── */
.eq-mc-options { display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;margin-top:0.5rem; }
.eq-mc-option { display:flex;align-items:center;gap:0.4rem; }
.eq-mc-option-label {
    width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;
    font-size:0.78rem;font-weight:700;flex-shrink:0;
}
.eq-mc-option-label:nth-child(1) { background:#eef2ff;color:#4361ee; }
.eq-mc-opt-a .eq-mc-option-label { background:#eef2ff;color:#4361ee; }
.eq-mc-opt-b .eq-mc-option-label { background:#ecfdf5;color:#10b981; }
.eq-mc-opt-c .eq-mc-option-label { background:#fefce8;color:#d97706; }
.eq-mc-opt-d .eq-mc-option-label { background:#f5f3ff;color:#7c3aed; }
.eq-mc-option input[type="text"] {
    flex:1;border:1.5px solid #e5e7eb;border-radius:6px;padding:0.35rem 0.55rem;font-size:0.85rem;
}
.eq-mc-option input[type="text"]:focus { outline:none;border-color:#4361ee; }
.eq-mc-answer { margin-top:0.5rem;display:flex;align-items:center;gap:0.5rem; }
.eq-mc-answer label { font-size:0.82rem;font-weight:600;color:#374151; }
.eq-mc-answer select {
    padding:0.25rem 0.5rem;border:1.5px solid #e5e7eb;border-radius:6px;font-size:0.82rem;
    background:#fff;cursor:pointer;
}
.eq-mc-answer select:focus { outline:none;border-color:#4361ee; }

/* ── True/False Options ── */
.eq-tf-options { display:flex;gap:0.75rem;margin-top:0.5rem;align-items:center; }
.eq-tf-option {
    display:flex;align-items:center;gap:0.35rem;padding:0.35rem 0.75rem;
    border:1.5px solid #e5e7eb;border-radius:8px;cursor:pointer;transition:all 0.2s;background:#fff;
}
.eq-tf-option:hover { border-color:#4361ee;background:#f8f9ff; }
.eq-tf-option.eq-tf-selected { border-color:#10b981;background:#ecfdf5; }
.eq-tf-option input[type="radio"] { accent-color:#10b981; }
.eq-tf-option span { font-size:0.85rem;font-weight:600;color:#374151; }

/* ── Fill Blank hint ── */
.eq-fill-hint { font-size:0.78rem;color:#6b7280;margin-top:0.35rem;display:flex;align-items:center;gap:0.3rem; }
.eq-fill-hint i { color:#ec4899; }

/* ── Essay word hint ── */
.eq-essay-hint { font-size:0.78rem;color:#6b7280;margin-top:0.35rem;display:flex;align-items:center;gap:0.3rem; }
.eq-essay-hint i { color:#7c3aed; }

/* ── Builder Actions ── */
.eq-builder-actions { display:flex;gap:0.75rem;margin-top:0.5rem; }
.eq-add-btn {
    display:inline-flex;align-items:center;gap:0.5rem;padding:0.55rem 1.1rem;background:#f0f0ff;
    color:#4361ee;border:1.5px dashed #c7d2fe;border-radius:8px;font-weight:600;font-size:0.85rem;
    cursor:pointer;transition:all 0.2s;
}
.eq-add-btn:hover { background:#eef2ff;border-color:#4361ee; }
.eq-add-btn-secondary { background:#f9fafb;color:#6b7280;border-color:#e5e7eb; }
.eq-add-btn-secondary:hover { background:#f3f4f6;border-color:#9ca3af;color:#374151; }

/* ── Upload Zone ── */
.eq-upload-zone {
    border:2.5px dashed #d1d5db;border-radius:16px;padding:2.5rem 2rem;text-align:center;
    cursor:pointer;transition:all 0.3s;background:#fafbfc;
}
.eq-upload-zone:hover { border-color:#4361ee;background:#f0f2ff;transform:translateY(-2px); }
.eq-upload-icon { margin-bottom:0.75rem; }
.eq-upload-icon i { font-size:2.5rem;color:#c7d2fe;transition:color 0.3s; }
.eq-upload-zone:hover .eq-upload-icon i { color:#4361ee; }
.eq-upload-zone h4 { color:#374151;font-size:1rem;margin:0 0 0.25rem; }
.eq-upload-zone p { color:#6b7280;font-size:0.88rem;margin:0 0 0.5rem; }
.eq-upload-formats { font-size:0.75rem;color:#9ca3af; }
.eq-file-preview {
    display:flex;align-items:center;gap:0.75rem;padding:0.65rem 1rem;
    background:#ecfdf5;border:1.5px solid #a7f3d0;border-radius:10px;color:#065f46;font-weight:500;
}
.eq-file-preview i { color:#10b981;font-size:1.1rem; }
.eq-file-remove {
    margin-left:auto;background:#fef2f2;color:#ef4444;border:none;width:28px;height:28px;
    border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;
}
.eq-file-remove:hover { background:#ef4444;color:#fff; }
.eq-upload-hint {
    display:flex;align-items:flex-start;gap:0.5rem;margin-top:1rem;padding:0.65rem 0.85rem;
    background:#fefce8;border:1px solid #fde68a;border-radius:10px;font-size:0.82rem;color:#92400e;
}
.eq-upload-hint i { color:#d97706;margin-top:0.1rem; }

/* ── Quick Type ── */
.eq-quicktype-wrapper { border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden; }
.eq-quicktype-toolbar {
    display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0.75rem;
    background:#f9fafb;border-bottom:1px solid #e5e7eb;flex-wrap:wrap;gap:0.4rem;
}
.eq-quicktype-hint { font-size:0.78rem;color:#6b7280;display:flex;align-items:center;gap:0.35rem; }
.eq-toolbar-btn {
    padding:0.25rem 0.6rem;border:1px solid #e5e7eb;border-radius:6px;background:#fff;
    color:#4361ee;font-size:0.75rem;font-weight:600;cursor:pointer;transition:all 0.2s;
    display:flex;align-items:center;gap:0.25rem;
}
.eq-toolbar-btn:hover { background:#4361ee;color:#fff;border-color:#4361ee; }
.eq-quicktype-textarea {
    width:100%;border:none !important;padding:0.85rem 1rem;font-size:0.9rem;resize:vertical;
    min-height:280px;font-family:'Sarasa Mono SC','Courier New',monospace;line-height:1.7;
    outline:none;box-shadow:none !important;
}
.eq-quicktype-textarea:focus { background:#fafbff; }
.eq-quicktype-footer {
    display:flex;justify-content:space-between;padding:0.45rem 0.85rem;
    background:#f9fafb;border-top:1px solid #e5e7eb;font-size:0.78rem;color:#6b7280;
}
.eq-quicktype-footer span { display:flex;align-items:center;gap:0.3rem; }

/* ── Preview ── */
.eq-preview-content {
    background:#fff;border:1.5px solid #e5e7eb;border-radius:10px;padding:1.5rem;
    font-family:Georgia,'Times New Roman',serif;line-height:1.8;max-height:500px;overflow-y:auto;
}
.eq-preview-content h3 { font-size:1.1rem;color:#1a1a2e;border-bottom:2px solid #4361ee;padding-bottom:0.5rem;margin:0 0 1rem; }
.eq-preview-q { margin-bottom:1rem;padding:0.5rem 0; }
.eq-preview-q-text { font-weight:600;color:#1a1a2e; }
.eq-preview-q-marks { font-size:0.82rem;color:#6b7280;font-style:italic; }
.eq-preview-mc-options { margin:0.35rem 0 0 1.5rem; }
.eq-preview-mc-opt { font-size:0.9rem;color:#374151;padding:0.15rem 0; }
.eq-preview-mc-answer { color:#10b981;font-weight:600;font-size:0.82rem;margin-left:1.5rem; }
.eq-preview-tf-answer { color:#10b981;font-weight:600;font-size:0.82rem;margin-left:0.5rem; }

/* ── Modal ── */
.eq-modal { position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;display:flex;align-items:center;justify-content:center; }
.eq-modal-overlay { position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.4); }
.eq-modal-content { position:relative;background:#fff;border-radius:14px;width:90%;max-width:450px;box-shadow:0 25px 50px rgba(0,0,0,0.15);z-index:1; }
.eq-modal-header { display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #f0f0f0; }
.eq-modal-header h3 { font-size:1rem;font-weight:700;color:#1a1a2e;margin:0;display:flex;align-items:center;gap:0.5rem; }
.eq-modal-header h3 i { color:#4361ee; }
.eq-modal-close { background:none;border:none;font-size:1.1rem;color:#6b7280;cursor:pointer;padding:0.25rem; }
.eq-modal-close:hover { color:#ef4444; }
.eq-modal-body { padding:1.25rem; }

/* ── More Options Toggle ── */
.eq-more-toggle {
    display:flex;align-items:center;gap:0.5rem;padding:0.45rem 0;background:none;
    border:none;cursor:pointer;font-size:0.88rem;font-weight:600;color:#6b7280;transition:color 0.2s;width:100%;
}
.eq-more-toggle:hover { color:#4361ee; }
.eq-more-toggle i { font-size:0.75rem;transition:transform 0.3s; }
.eq-more-toggle.open i { transform:rotate(180deg); }
.eq-more-hint { font-weight:400;font-size:0.78rem;color:#9ca3af;margin-left:0.25rem; }

/* ── Base form styles ── */
.modern-form-section { border-bottom:1px solid #f0f0f0; }
.modern-form-section:last-of-type { border-bottom:none; }
.modern-form-section-header { display:flex;align-items:center;gap:1rem;padding:1.5rem 2rem .75rem; }
.modern-form-section-icon { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0; }
.modern-form-section-icon-blue { background:#eef2ff;color:#4361ee; }
.modern-form-section-icon-green { background:#ecfdf5;color:#10b981; }
.modern-form-section-icon-gold { background:#fefce8;color:#d97706; }
.modern-form-section-icon-purple { background:#f5f3ff;color:#7c3aed; }
.modern-form-section-title { font-size:1.05rem;font-weight:700;color:#1a1a2e;margin:0; }
.modern-form-section-desc { font-size:.82rem;color:#9ca3af;margin:.15rem 0 0; }
.modern-form-section-body { padding:1.25rem 2rem 1.75rem; }
.modern-form-grid { display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem; }
.modern-form-span-2 { grid-column:span 2; }
.modern-form-group { display:flex;flex-direction:column; }
.modern-form-label { font-weight:600;color:#374151;margin-bottom:.45rem;font-size:.88rem; }
.modern-form-label small { font-weight:400;color:#9ca3af;font-size:.78rem; }
.modern-required { color:#ef4444;font-weight:700; }
.modern-input { width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.7rem .9rem .7rem 2.5rem;font-size:.9rem;color:#1a1a2e;background:#fff;transition:all .2s; }
.modern-input:focus { outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1); }
.modern-input::placeholder { color:#c5c9d2; }
.modern-select { appearance:none;cursor:pointer;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .75rem center;background-repeat:no-repeat;background-size:1.25rem;padding-right:2.5rem; }
.modern-form-error { display:block;color:#ef4444;font-size:.8rem;margin-top:.35rem;font-weight:500; }
.modern-form-actions { display:flex;justify-content:flex-end;gap:.75rem;padding:1.5rem 2rem;border-top:1px solid #f0f0f0;background:#fafbfc; }
.modern-info-banner { display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;margin-bottom:1.75rem;font-size:.88rem;color:#1e40af; }
.modern-info-banner i { color:#3b82f6; }
.modern-info-banner strong { color:#1e3a8a; }
.modern-textarea { resize:vertical;min-height:80px; }
.btn-modern { display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.35rem;border-radius:10px;font-weight:600;font-size:.9rem;text-decoration:none;border:none;cursor:pointer;transition:all .25s; }
.btn-modern-primary { background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;box-shadow:0 2px 8px rgba(67,97,238,.3); }
.btn-modern-primary:hover { transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4);color:#fff; }
.btn-modern-outline { background:transparent;color:#6b7280;border:1.5px solid #e5e7eb; }
.btn-modern-outline:hover { border-color:#4361ee;color:#4361ee;background:#f8f9ff; }
.btn-modern-ghost { background:transparent;color:#6b7280;padding:.65rem 1rem; }
.btn-modern-ghost:hover { color:#1a1a2e;background:#f3f4f6; }
@media(max-width:768px) {
    .modern-form-grid{grid-template-columns:1fr;} .modern-form-span-2{grid-column:span 1;}
    .modern-form-section-body{padding:1rem 1.25rem 1.5rem;} .modern-form-section-header{padding:1.25rem 1.25rem .75rem;flex-wrap:wrap;}
    .modern-form-actions{padding:1rem 1.25rem;flex-direction:column;} .btn-modern{justify-content:center;width:100%;}
    .eq-mode-selector{flex-direction:column;} .eq-mode-btn{min-width:auto;flex-direction:row;gap:0.75rem;}
    .eq-mode-btn i{font-size:1rem;} .eq-mode-btn small{margin-left:auto;}
    .eq-mc-options{grid-template-columns:1fr;} .eq-stats-bar{width:100%;justify-content:flex-start;margin-top:0.5rem;}
    .eq-builder-typebar{flex-direction:column;align-items:flex-start;}
}
</style>
@endpush

@push('scripts')
<script>
var currentMode = 'builder';
var questionCounter = 0;
var builderQType = 'multiple_choice'; // default type for builder mode
var mainQType = ''; // from the question_type dropdown

// Type metadata
var QTypes = {
    multiple_choice: { label: 'Multiple Choice', icon: 'fas fa-list-ul', color: '#4361ee', shortLabel: 'MC' },
    true_false: { label: 'True / False', icon: 'fas fa-check-double', color: '#10b981', shortLabel: 'T/F' },
    short_answer: { label: 'Short Answer', icon: 'fas fa-align-left', color: '#f59e0b', shortLabel: 'SA' },
    essay: { label: 'Essay', icon: 'fas fa-pen-fancy', color: '#7c3aed', shortLabel: 'Essay' },
    fill_blank: { label: 'Fill in the Blank', icon: 'fas fa-underline', color: '#ec4899', shortLabel: 'Fill' }
};

// ── When question_type dropdown changes, update builder ──
$('#question_type').on('change', function() {
    mainQType = $(this).val();
    updateBuilderForType();
});

function updateBuilderForType() {
    var typeBar = document.getElementById('builderTypeBar');
    if (mainQType === 'mixed') {
        typeBar.style.display = 'flex';
    } else {
        typeBar.style.display = 'none';
        if (mainQType && QTypes[mainQType]) {
            builderQType = mainQType;
            // Update type chips active state
            document.querySelectorAll('.eq-type-chip').forEach(function(c) {
                c.classList.toggle('eq-type-chip-active', c.dataset.qt === builderQType);
            });
        }
    }
}

function setBuilderQType(type) {
    builderQType = type;
    document.querySelectorAll('.eq-type-chip').forEach(function(c) {
        c.classList.toggle('eq-type-chip-active', c.dataset.qt === type);
    });
}

// ── Build question card HTML for each type ──
function buildQuestionCardHTML(num, type) {
    var meta = QTypes[type] || QTypes.multiple_choice;
    var html = '<div class="eq-question-card" data-qnum="' + num + '" data-qt="' + type + '">';
    html += '<div class="eq-question-header">';
    html += '<span class="eq-question-number">Q' + num + '</span>';
    html += '<span class="eq-question-type-badge" data-qt="' + type + '">' + meta.shortLabel + '</span>';

    // If mixed mode, show inline type selector
    if (mainQType === 'mixed') {
        html += '<select class="eq-qtype-inline" onchange="changeCardType(this)">';
        for (var t in QTypes) {
            html += '<option value="' + t + '"' + (t === type ? ' selected' : '') + '>' + QTypes[t].shortLabel + '</option>';
        }
        html += '</select>';
    }

    html += '<input type="number" name="q_marks[]" class="eq-marks-input" placeholder="Marks" min="1" value="1">';
    html += '<button type="button" class="eq-remove-btn" onclick="removeQuestion(this)" title="Remove question"><i class="fas fa-times"></i></button>';
    html += '</div>';

    // Question text
    var placeholder = '';
    switch(type) {
        case 'multiple_choice': placeholder = 'Type the question stem here...'; break;
        case 'true_false': placeholder = 'Type the statement here...'; break;
        case 'short_answer': placeholder = 'Type the question here...'; break;
        case 'essay': placeholder = 'Type the essay prompt here...'; break;
        case 'fill_blank': placeholder = 'Type the question with _____ for blanks...'; break;
        default: placeholder = 'Type question here...';
    }
    html += '<textarea name="q_text[]" class="eq-question-text" placeholder="' + placeholder + '" rows="' + (type === 'essay' ? 4 : 2) + '"></textarea>';

    // Type-specific fields
    html += '<div class="eq-type-fields" data-qt="' + type + '">';

    if (type === 'multiple_choice') {
        html += '<div class="eq-mc-options">';
        html += '<div class="eq-mc-option eq-mc-opt-a"><span class="eq-mc-option-label">A</span><input type="text" name="q_opt_a[]" placeholder="Option A"></div>';
        html += '<div class="eq-mc-option eq-mc-opt-b"><span class="eq-mc-option-label">B</span><input type="text" name="q_opt_b[]" placeholder="Option B"></div>';
        html += '<div class="eq-mc-option eq-mc-opt-c"><span class="eq-mc-option-label">C</span><input type="text" name="q_opt_c[]" placeholder="Option C"></div>';
        html += '<div class="eq-mc-option eq-mc-opt-d"><span class="eq-mc-option-label">D</span><input type="text" name="q_opt_d[]" placeholder="Option D"></div>';
        html += '</div>';
        html += '<div class="eq-mc-answer"><label>Answer:</label><select name="q_answer[]"><option value="">-- Select --</option><option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option></select></div>';
    } else if (type === 'true_false') {
        html += '<div class="eq-tf-options">';
        html += '<label class="eq-tf-option"><input type="radio" name="q_tf_' + num + '" value="True"><span>True</span></label>';
        html += '<label class="eq-tf-option"><input type="radio" name="q_tf_' + num + '" value="False"><span>False</span></label>';
        html += '<input type="hidden" name="q_answer[]" class="eq-tf-hidden" value="">';
        html += '</div>';
    } else if (type === 'short_answer') {
        html += '<div style="margin-top:0.4rem;"><label style="font-size:0.78rem;font-weight:600;color:#6b7280;">Expected Answer (optional):</label>';
        html += '<input type="text" name="q_answer[]" placeholder="Expected short answer..." style="width:100%;padding:0.35rem 0.55rem;border:1.5px solid #e5e7eb;border-radius:6px;font-size:0.85rem;margin-top:0.2rem;"></div>';
    } else if (type === 'essay') {
        html += '<div class="eq-essay-hint"><i class="fas fa-info-circle"></i> Students will write a long-form answer. Consider adding word count guidance in the question.</div>';
        html += '<input type="hidden" name="q_answer[]" value="">';
    } else if (type === 'fill_blank') {
        html += '<div style="margin-top:0.4rem;"><label style="font-size:0.78rem;font-weight:600;color:#6b7280;">Correct Answer:</label>';
        html += '<input type="text" name="q_answer[]" placeholder="The word/phrase that fills the blank" style="width:100%;padding:0.35rem 0.55rem;border:1.5px solid #e5e7eb;border-radius:6px;font-size:0.85rem;margin-top:0.2rem;"></div>';
        html += '<div class="eq-fill-hint"><i class="fas fa-info-circle"></i> Use _____ (underscore) in the question text to mark where the blank is.</div>';
    }

    html += '</div>'; // close eq-type-fields
    html += '</div>'; // close eq-question-card
    return html;
}

// ── Add a question card ──
function addQuestion() {
    questionCounter++;
    var qList = document.getElementById('questionsList');
    var card = document.createElement('div');
    card.innerHTML = buildQuestionCardHTML(questionCounter, builderQType);
    var cardEl = card.firstElementChild;
    qList.appendChild(cardEl);
    cardEl.querySelector('.eq-question-text').focus();
    updateRemoveButtons();
    updateStats();

    // Wire up T/F radio change
    wireTFRadios(cardEl);
}

// ── Change card type (for mixed mode) ──
function changeCardType(select) {
    var card = select.closest('.eq-question-card');
    var newType = select.value;
    var num = card.dataset.qnum;
    var oldText = card.querySelector('.eq-question-text').value;
    var oldMarks = card.querySelector('.eq-marks-input').value;

    var newCard = document.createElement('div');
    newCard.innerHTML = buildQuestionCardHTML(num, newType);
    var newEl = newCard.firstElementChild;

    // Preserve text and marks
    newEl.querySelector('.eq-question-text').value = oldText;
    newEl.querySelector('.eq-marks-input').value = oldMarks;

    card.replaceWith(newEl);
    wireTFRadios(newEl);
    updateStats();
}

// ── Wire T/F radios to hidden input ──
function wireTFRadios(card) {
    card.querySelectorAll('input[type="radio"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var hidden = this.closest('.eq-question-card').querySelector('.eq-tf-hidden');
            if (hidden) hidden.value = this.value;
            // Visual feedback
            this.closest('.eq-tf-options').querySelectorAll('.eq-tf-option').forEach(function(opt) {
                opt.classList.remove('eq-tf-selected');
            });
            this.closest('.eq-tf-option').classList.add('eq-tf-selected');
        });
    });
}

// ── Remove question ──
function removeQuestion(btn) {
    btn.closest('.eq-question-card').remove();
    renumberQuestions();
    updateStats();
}

// ── Renumber ──
function renumberQuestions() {
    var cards = document.querySelectorAll('#questionsList .eq-question-card');
    cards.forEach(function(card, i) {
        card.dataset.qnum = i + 1;
        card.querySelector('.eq-question-number').textContent = 'Q' + (i + 1);
        // Update T/F radio names
        card.querySelectorAll('input[type="radio"]').forEach(function(r) {
            r.name = 'q_tf_' + (i + 1);
        });
    });
    questionCounter = cards.length;
    updateRemoveButtons();
}

// ── Show/hide remove buttons ──
function updateRemoveButtons() {
    var cards = document.querySelectorAll('#questionsList .eq-question-card');
    cards.forEach(function(card) {
        var btn = card.querySelector('.eq-remove-btn');
        btn.style.display = cards.length > 1 ? 'flex' : 'none';
    });
}

// ── Update stats ──
function updateStats() {
    var cards = document.querySelectorAll('#questionsList .eq-question-card');
    var totalM = 0;
    cards.forEach(function(card) {
        var m = parseInt(card.querySelector('.eq-marks-input').value) || 0;
        totalM += m;
    });
    document.getElementById('eqStatCount').textContent = cards.length;
    document.getElementById('eqStatMarks').textContent = totalM;
}

// Listen for marks changes
$(document).on('input', '.eq-marks-input', function() {
    updateStats();
});

// ── Bulk Add ──
function addBulkQuestions() {
    document.getElementById('bulkModal').style.display = 'flex';
    document.getElementById('bulkCount').focus();
}

function closeBulkModal() {
    document.getElementById('bulkModal').style.display = 'none';
}

function executeBulkAdd() {
    var count = parseInt(document.getElementById('bulkCount').value) || 5;
    count = Math.min(Math.max(count, 1), 50);
    for (var i = 0; i < count; i++) {
        addQuestion();
    }
    closeBulkModal();
}

// ── Mode Switching ──
function switchMode(mode) {
    currentMode = mode;
    document.querySelectorAll('.eq-mode-btn').forEach(function(btn) {
        btn.classList.toggle('eq-mode-active', btn.dataset.mode === mode);
    });
    document.getElementById('modeBuilder').style.display = mode === 'builder' ? 'block' : 'none';
    document.getElementById('modeQuicktype').style.display = mode === 'quicktype' ? 'block' : 'none';
    document.getElementById('modeUpload').style.display = mode === 'upload' ? 'block' : 'none';
}

// ── Quick Type: Live counter ──
var qtTextarea = document.getElementById('quicktypeQuestions');
if (qtTextarea) {
    qtTextarea.addEventListener('input', function() {
        var text = this.value.trim();
        var lines = text.split('\n').filter(function(l) { return l.trim().length > 0; });
        var qCount = lines.filter(function(l) { return /^\d+[\.\)]\s/.test(l.trim()); }).length || (lines.length > 0 ? lines.length : 0);
        document.getElementById('questionCount').textContent = qCount + ' question' + (qCount !== 1 ? 's' : '');
        var marksRegex = /\((\d+)\s*mark/i;
        var totalM = 0;
        lines.forEach(function(l) {
            var m = l.match(marksRegex);
            if (m) totalM += parseInt(m[1]);
        });
        document.getElementById('totalMarksPreview').textContent = totalM > 0 ? totalM + ' marks total' : '';
    });
}

// ── Quick Type: Type-specific templates ──
function insertTemplate(type) {
    var templates = {
        mc: "1. What is the capital of Ethiopia? (2 marks)\n   A) Addis Ababa  B) Nairobi  C) Cairo  D) Kampala\n   Answer: A\n\n2. Which planet is known as the Red Planet? (2 marks)\n   A) Venus  B) Mars  C) Jupiter  D) Saturn\n   Answer: B",
        tf: "1. Water boils at 100 degrees Celsius at sea level. (1 mark)\n   Answer: True\n\n2. The sun revolves around the Earth. (1 mark)\n   Answer: False",
        essay: "1. Discuss the causes and effects of climate change on the global ecosystem. (10 marks)\n\n2. Analyze the role of education in national development. (8 marks)",
        fill: "1. The largest ocean on Earth is the _____ Ocean. (2 marks)\n   Answer: Pacific\n\n2. Photosynthesis converts sunlight into _____ and oxygen. (2 marks)\n   Answer: glucose"
    };
    var qt = document.getElementById('quicktypeQuestions');
    qt.value = templates[type] || templates.mc;
    qt.dispatchEvent(new Event('input'));
}

// ── More Options Toggle ──
function toggleMoreOptions() {
    var panel = document.getElementById('moreOptionsPanel');
    var toggle = document.getElementById('moreOptionsToggle');
    var label = document.getElementById('moreOptionsLabel');
    var isOpen = panel.style.display !== 'none';
    panel.style.display = isOpen ? 'none' : 'block';
    toggle.classList.toggle('open', !isOpen);
    label.textContent = isOpen ? 'More Options' : 'Less Options';
}

// ── File Upload ──
function showFileName(input) {
    var fileInfo = document.getElementById('fileInfo');
    var fileName = document.getElementById('fileName');
    if (input.files.length > 0) {
        fileName.textContent = input.files[0].name + ' (' + (input.files[0].size / 1024).toFixed(1) + ' KB)';
        fileInfo.style.display = 'block';
    }
}

function removeFile() {
    document.getElementById('attachmentInput').value = '';
    document.getElementById('fileInfo').style.display = 'none';
}

var dropzone = document.getElementById('dropzone');
if (dropzone) {
    dropzone.addEventListener('dragover', function(e) { e.preventDefault(); this.style.borderColor = '#4361ee'; this.style.background = '#f0f2ff'; });
    dropzone.addEventListener('dragleave', function() { this.style.borderColor = '#d1d5db'; this.style.background = ''; });
    dropzone.addEventListener('drop', function(e) { e.preventDefault(); this.style.borderColor = '#d1d5db'; this.style.background = ''; var input = document.getElementById('attachmentInput'); input.files = e.dataTransfer.files; showFileName(input); });
}

// ── Format questions for submission ──
function formatQuestions() {
    if (currentMode === 'builder') {
        var cards = document.querySelectorAll('#questionsList .eq-question-card');
        var combined = '';
        var typeLabel = document.getElementById('question_type').selectedOptions[0]?.text || 'Questions';

        cards.forEach(function(card, i) {
            var qt = card.dataset.qt;
            var text = card.querySelector('.eq-question-text').value.trim();
            var marks = card.querySelector('.eq-marks-input').value || '1';
            var meta = QTypes[qt] || QTypes.multiple_choice;

            if (!text) return;

            combined += (i > 0 ? '\n\n' : '') + 'Q' + (i+1) + '. [' + meta.shortLabel + '] ' + text + ' (' + marks + ' mark' + (marks > 1 ? 's' : '') + ')';

            if (qt === 'multiple_choice') {
                var optA = card.querySelector('input[name="q_opt_a[]"]')?.value?.trim();
                var optB = card.querySelector('input[name="q_opt_b[]"]')?.value?.trim();
                var optC = card.querySelector('input[name="q_opt_c[]"]')?.value?.trim();
                var optD = card.querySelector('input[name="q_opt_d[]"]')?.value?.trim();
                var ans = card.querySelector('select[name="q_answer[]"]')?.value;
                if (optA) combined += '\n    A) ' + optA;
                if (optB) combined += '\n    B) ' + optB;
                if (optC) combined += '\n    C) ' + optC;
                if (optD) combined += '\n    D) ' + optD;
                if (ans) combined += '\n    Answer: ' + ans;
            } else if (qt === 'true_false') {
                var tfAns = card.querySelector('.eq-tf-hidden')?.value;
                if (tfAns) combined += '\n    Answer: ' + tfAns;
            } else if (qt === 'short_answer' || qt === 'fill_blank') {
                var saAns = card.querySelector('input[name="q_answer[]"]')?.value?.trim();
                if (saAns) combined += '\n    Answer: ' + saAns;
            }
        });

        return combined;
    } else if (currentMode === 'quicktype') {
        return document.getElementById('quicktypeQuestions').value;
    } else {
        return 'See attached file';
    }
}

// ── Preview ──
function showPreview() {
    var section = document.getElementById('previewSection');
    section.style.display = 'block';
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });

    var formatted = formatQuestions();
    var title = document.querySelector('input[name="title"]').value || 'Exam Questions';
    var typeLabel = document.getElementById('question_type').selectedOptions[0]?.text || '';

    // Build preview HTML
    var html = '<h3>' + title + '</h3>';
    if (typeLabel) html += '<p style="color:#6b7280;font-size:0.88rem;margin-bottom:1rem;">Type: ' + typeLabel + '</p>';

    if (currentMode === 'builder') {
        var cards = document.querySelectorAll('#questionsList .eq-question-card');
        cards.forEach(function(card, i) {
            var qt = card.dataset.qt;
            var text = card.querySelector('.eq-question-text').value.trim();
            var marks = card.querySelector('.eq-marks-input').value || '1';
            if (!text) return;
            var meta = QTypes[qt] || QTypes.multiple_choice;

            html += '<div class="eq-preview-q">';
            html += '<span class="eq-preview-q-text">Q' + (i+1) + '. ' + text + '</span> ';
            html += '<span class="eq-preview-q-marks">(' + marks + ' mark' + (marks > 1 ? 's' : '') + ')</span>';

            if (qt === 'multiple_choice') {
                html += '<div class="eq-preview-mc-options">';
                var opts = ['A', 'B', 'C', 'D'];
                opts.forEach(function(o) {
                    var val = card.querySelector('input[name="q_opt_' + o.toLowerCase() + '[]"]')?.value?.trim();
                    if (val) html += '<div class="eq-preview-mc-opt">' + o + ') ' + val + '</div>';
                });
                html += '</div>';
                var ans = card.querySelector('select[name="q_answer[]"]')?.value;
                if (ans) html += '<div class="eq-preview-mc-answer">Answer: ' + ans + '</div>';
            } else if (qt === 'true_false') {
                var tfAns = card.querySelector('.eq-tf-hidden')?.value;
                html += '<div style="margin-top:0.25rem;">☐ True  ☐ False';
                if (tfAns) html += '<span class="eq-preview-tf-answer">Answer: ' + tfAns + '</span>';
                html += '</div>';
            } else if (qt === 'fill_blank') {
                var fbAns = card.querySelector('input[name="q_answer[]"]')?.value?.trim();
                if (fbAns) html += '<div class="eq-preview-mc-answer">Answer: ' + fbAns + '</div>';
            } else if (qt === 'short_answer') {
                var saAns = card.querySelector('input[name="q_answer[]"]')?.value?.trim();
                if (saAns) html += '<div class="eq-preview-mc-answer">Expected Answer: ' + saAns + '</div>';
                else html += '<div style="margin-top:0.25rem;color:#9ca3af;font-style:italic;">(Short answer space)</div>';
            } else if (qt === 'essay') {
                html += '<div style="margin-top:0.25rem;color:#9ca3af;font-style:italic;">(Essay answer space)</div>';
            }

            html += '</div>';
        });

        if (cards.length === 0) {
            html += '<p style="color:#9ca3af;text-align:center;padding:2rem;">No questions added yet. Use the Question Builder above to add questions.</p>';
        }
    } else {
        // Quick type or upload — show raw text
        var lines = formatted.split('\n');
        lines.forEach(function(line) {
            if (/^\d+[\.\)]\s/.test(line.trim())) {
                html += '<div class="eq-preview-q"><span class="eq-preview-q-text">' + line + '</span></div>';
            } else {
                html += '<div style="padding-left:1.5rem;color:#6b7280;font-size:0.9rem;">' + line + '</div>';
            }
        });
    }

    document.getElementById('previewContent').innerHTML = html;
}

function togglePreview() {
    var body = document.getElementById('previewBody');
    var chevron = document.getElementById('previewChevron');
    if (body.style.display === 'none') {
        body.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)';
    } else {
        body.style.display = 'none';
        chevron.style.transform = '';
    }
}

// ── Form Submit ──
document.getElementById('examQuestionForm').addEventListener('submit', function(e) {
    var questionsField = document.createElement('input');
    questionsField.type = 'hidden';
    questionsField.name = 'questions';

    if (currentMode === 'builder') {
        questionsField.value = formatQuestions();
    } else if (currentMode === 'quicktype') {
        questionsField.value = document.getElementById('quicktypeQuestions').value;
    } else {
        questionsField.value = 'See attached file';
    }

    // Remove any existing hidden questions fields to avoid duplicates
    this.querySelectorAll('input[name="questions"]').forEach(function(el) { el.remove(); });

    this.appendChild(questionsField);

    // Remove name from quicktype textarea to prevent duplicate
    document.getElementById('quicktypeQuestions').removeAttribute('name');
});

// ── AJAX: Load sections when class changes ──
$('#class_id').on('change', function() {
    var classId = $(this).val();
    var sectionSelect = $('#section_id');
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
        }
    });
});

// ── AJAX: Auto-fill academic year and term when exam is selected ──
$('#exam_id').on('change', function() {
    var examId = $(this).val();
    if (!examId) return;
    $.ajax({
        url: '{{ route("admin.api.exam-details", ["exam" => 0]) }}'.replace('/0', '/' + examId),
        dataType: 'json',
        success: function(data) {
            if (data.academic_year_id) {
                $('#academic_year_id').val(data.academic_year_id);
                if ($('#moreOptionsPanel').is(':hidden')) toggleMoreOptions();
            }
            if (data.term_id) $('#term_id').val(data.term_id);
        }
    });
});

// ── Init ──
$(function() {
    @if(auth()->user()->branch_id)
    $('#branch_id').val('{{ auth()->user()->branch_id }}');
    @endif

    var teacherSubjects = $('#subject_id option[data-teacher="1"]');
    if (teacherSubjects.length === 1) $('#subject_id').val(teacherSubjects.first().val());

    var teacherClasses = $('#class_id option[data-teacher="1"]');
    if (teacherClasses.length === 1) $('#class_id').val(teacherClasses.first().val()).trigger('change');

    @if($activeAcademicYear ?? null)
    $('#academic_year_id').val('{{ $activeAcademicYear->id }}');
    @endif

    // Init mode and add first question
    switchMode('builder');
    mainQType = $('#question_type').val() || '';
    updateBuilderForType();
    addQuestion(); // Add initial question card

    // Auto-add 4 more for MC (total 5)
    if (mainQType === 'multiple_choice' || !mainQType) {
        for (var i = 0; i < 4; i++) addQuestion();
    }
});

// ── Keyboard shortcuts ──
$(document).on('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        $('#examQuestionForm').submit();
    }
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        showPreview();
    }
});
</script>
@endpush
@endsection