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
                    {{-- Row 1: Title (full width) --}}
                    <div class="modern-form-grid" style="margin-bottom:1rem;">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label">Title <span class="modern-required">*</span></label>
                            <input type="text" name="title" class="modern-input" value="{{ old('title') }}" placeholder="e.g. Grade 10 Math Midterm Questions" required autofocus style="padding-left:0.9rem;">
                            @error('title')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- Row 2: Key dropdowns --}}
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

                    {{-- Row 3: Marks & Duration --}}
                    <div class="modern-form-grid" style="margin-bottom:0;">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Total Marks <span class="modern-required">*</span></label>
                            <input type="number" name="total_marks" class="modern-input" value="{{ old('total_marks', 100) }}" min="1" required style="padding-left:0.9rem;">
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
                                    @error('academic_year_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="modern-form-group">
                                    <label class="modern-form-label">Term</label>
                                    <select name="term_id" id="term_id" class="modern-input modern-select">
                                        <option value="">-- Select Term --</option>
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('term_id')<span class="modern-form-error">{{ $message }}</span>@enderror
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
                    <div>
                        <h3 class="modern-form-section-title">Questions</h3>
                        <p class="modern-form-section-desc">Choose how you want to submit your exam questions</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    {{-- Mode Selector --}}
                    <div class="eq-mode-selector">
                        <button type="button" class="eq-mode-btn eq-mode-active" data-mode="upload" onclick="switchMode('upload')">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Upload File</span>
                            <small>Easiest</small>
                        </button>
                        <button type="button" class="eq-mode-btn" data-mode="quicktype" onclick="switchMode('quicktype')">
                            <i class="fas fa-keyboard"></i>
                            <span>Quick Type</span>
                            <small>Paste or type</small>
                        </button>
                        <button type="button" class="eq-mode-btn" data-mode="structured" onclick="switchMode('structured')">
                            <i class="fas fa-list-ol"></i>
                            <span>Structured</span>
                            <small>One by one</small>
                        </button>
                    </div>

                    {{-- Mode 1: Upload File --}}
                    <div id="modeUpload" class="eq-mode-panel">
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
                            <span>When uploading a file, you can leave the text area below empty. The uploaded document will serve as the exam questions.</span>
                        </div>
                        {{-- Hidden questions field for upload mode --}}
                        <input type="hidden" name="questions" id="uploadQuestions" value="See attached file">
                    </div>

                    {{-- Mode 2: Quick Type --}}
                    <div id="modeQuicktype" class="eq-mode-panel" style="display:none;">
                        <div class="eq-quicktype-wrapper">
                            <div class="eq-quicktype-toolbar">
                                <span class="eq-quicktype-hint"><i class="fas fa-info-circle"></i> Type each question on a new line. Add marks in parentheses.</span>
                                <button type="button" class="eq-toolbar-btn" onclick="insertTemplate()" title="Insert example template">
                                    <i class="fas fa-magic"></i> Template
                                </button>
                            </div>
                            <textarea name="questions" id="quicktypeQuestions" class="eq-quicktype-textarea" placeholder="1. What is the capital of Ethiopia? (2 marks)&#10;2. Explain the water cycle in detail. (5 marks)&#10;3. Solve: 2x + 5 = 15 (3 marks)&#10;4. Define photosynthesis. (4 marks)&#10;5. Compare and contrast mitosis and meiosis. (6 marks)" rows="14">{{ old('questions') }}</textarea>
                            <div class="eq-quicktype-footer">
                                <span id="questionCount">0 questions</span>
                                <span id="totalMarksPreview">0 marks total</span>
                            </div>
                        </div>
                    </div>

                    {{-- Mode 3: Structured Entry --}}
                    <div id="modeStructured" class="eq-mode-panel" style="display:none;">
                        <div id="questionsList">
                            <div class="eq-question-card" data-qnum="1">
                                <div class="eq-question-header">
                                    <span class="eq-question-number">Q1</span>
                                    <input type="number" name="q_marks[]" class="eq-marks-input" placeholder="Marks" min="1" value="1">
                                    <button type="button" class="eq-remove-btn" onclick="removeQuestion(this)" title="Remove question" style="display:none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <textarea name="q_text[]" class="eq-question-text" placeholder="Type question here..." rows="3"></textarea>
                            </div>
                        </div>
                        <button type="button" onclick="addQuestion()" class="eq-add-btn">
                            <i class="fas fa-plus"></i> Add Question
                        </button>
                        <input type="hidden" name="questions" id="structuredQuestions">
                    </div>

                    @error('questions')<span class="modern-form-error">{{ $message }}</span>@enderror

                    {{-- Notes/Description --}}
                    <div style="margin-top:1.25rem;">
                        <label class="modern-form-label">Notes / Special Instructions <small>(optional)</small></label>
                        <textarea name="description" class="modern-input modern-textarea" placeholder="Any additional notes for the reviewer..." rows="2" style="padding-left:0.9rem;font-family:inherit;">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions" style="justify-content:space-between;">
                <a href="{{ route('admin.exam-questions.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <div style="display:flex;gap:0.75rem;">
                    <button type="submit" name="action" value="submit" class="btn-modern btn-modern-primary" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Submit for Review
                    </button>
                </div>
            </div>
        </form>
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
    background:#f9fafb;border-bottom:1px solid #e5e7eb;
}
.eq-quicktype-hint { font-size:0.78rem;color:#6b7280;display:flex;align-items:center;gap:0.35rem; }
.eq-toolbar-btn {
    padding:0.3rem 0.7rem;border:1px solid #e5e7eb;border-radius:6px;background:#fff;
    color:#4361ee;font-size:0.78rem;font-weight:600;cursor:pointer;transition:all 0.2s;
    display:flex;align-items:center;gap:0.3rem;
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

/* ── Structured Entry ── */
.eq-question-card { border:1.5px solid #e5e7eb;border-radius:10px;padding:0.75rem 1rem;margin-bottom:0.75rem;background:#fafbfc;transition:all 0.2s; }
.eq-question-card:hover { border-color:#c7d2fe;background:#f8f9ff; }
.eq-question-header { display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem; }
.eq-question-number { background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;font-weight:700;font-size:0.8rem;padding:0.25rem 0.6rem;border-radius:6px;min-width:32px;text-align:center; }
.eq-marks-input { width:80px;padding:0.3rem 0.5rem;border:1.5px solid #e5e7eb;border-radius:6px;font-size:0.85rem;text-align:center; }
.eq-marks-input:focus { outline:none;border-color:#4361ee; }
.eq-remove-btn { margin-left:auto;background:#fef2f2;color:#ef4444;border:none;width:28px;height:28px;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s; }
.eq-remove-btn:hover { background:#ef4444;color:#fff; }
.eq-question-text { width:100%;border:1.5px solid #e5e7eb;border-radius:8px;padding:0.6rem 0.75rem;font-size:0.9rem;resize:vertical;min-height:60px;font-family:inherit; }
.eq-question-text:focus { outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,0.1); }
.eq-add-btn { display:inline-flex;align-items:center;gap:0.5rem;padding:0.5rem 1rem;background:#f0f0ff;color:#4361ee;border:1.5px dashed #c7d2fe;border-radius:8px;font-weight:600;font-size:0.85rem;cursor:pointer;transition:all 0.2s; }
.eq-add-btn:hover { background:#eef2ff;border-color:#4361ee; }

/* ── More Options Toggle ── */
.eq-more-toggle {
    display:flex;align-items:center;gap:0.5rem;padding:0.45rem 0;background:none;
    border:none;cursor:pointer;font-size:0.88rem;font-weight:600;color:#6b7280;
    transition:color 0.2s;width:100%;
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
    .modern-form-section-body{padding:1rem 1.25rem 1.5rem;} .modern-form-section-header{padding:1.25rem 1.25rem .75rem;}
    .modern-form-actions{padding:1rem 1.25rem;flex-direction:column;} .btn-modern{justify-content:center;width:100%;}
    .eq-mode-selector{flex-direction:column;} .eq-mode-btn{min-width:auto;flex-direction:row;gap:0.75rem;}
    .eq-mode-btn i{font-size:1rem;} .eq-mode-btn small{margin-left:auto;}
}
</style>
@endpush

@push('scripts')
<script>
let currentMode = 'upload';
let questionCounter = 1;

// ── Mode Switching ──
function switchMode(mode) {
    currentMode = mode;
    // Update buttons
    document.querySelectorAll('.eq-mode-btn').forEach(btn => {
        btn.classList.toggle('eq-mode-active', btn.dataset.mode === mode);
    });
    // Show/hide panels
    document.getElementById('modeUpload').style.display = mode === 'upload' ? 'block' : 'none';
    document.getElementById('modeQuicktype').style.display = mode === 'quicktype' ? 'block' : 'none';
    document.getElementById('modeStructured').style.display = mode === 'structured' ? 'block' : 'none';

    // Manage which questions field gets submitted
    // Disable all questions fields first
    document.getElementById('uploadQuestions').disabled = true;
    var qtTextarea = document.getElementById('quicktypeQuestions');
    var stInput = document.getElementById('structuredQuestions');

    if (qtTextarea) qtTextarea.removeAttribute('name');
    if (stInput) { stInput.removeAttribute('name'); stInput.disabled = true; }

    if (mode === 'upload') {
        document.getElementById('uploadQuestions').disabled = false;
        document.getElementById('uploadQuestions').setAttribute('name', 'questions');
    } else if (mode === 'quicktype') {
        qtTextarea.setAttribute('name', 'questions');
    } else if (mode === 'structured') {
        // Structured will be compiled on submit
    }
}

// ── Quick Type: Live counter ──
var qtTextarea = document.getElementById('quicktypeQuestions');
if (qtTextarea) {
    qtTextarea.addEventListener('input', function() {
        var text = this.value.trim();
        var lines = text.split('\n').filter(l => l.trim().length > 0);
        var qCount = lines.filter(l => /^\d+[\.\)]\s/.test(l.trim())).length || (lines.length > 0 ? lines.length : 0);
        document.getElementById('questionCount').textContent = qCount + ' question' + (qCount !== 1 ? 's' : '');

        // Try to sum marks
        var marksRegex = /\((\d+)\s*mark/i;
        var totalM = 0;
        lines.forEach(l => {
            var m = l.match(marksRegex);
            if (m) totalM += parseInt(m[1]);
        });
        document.getElementById('totalMarksPreview').textContent = totalM > 0 ? totalM + ' marks total' : '';
    });
}

// ── Quick Type: Template ──
function insertTemplate() {
    var template =
        "1. Define the term 'photosynthesis'. (2 marks)\n" +
        "2. Explain the difference between mitosis and meiosis. (5 marks)\n" +
        "3. Solve the equation: 3x - 7 = 14. (3 marks)\n" +
        "4. Describe the water cycle with a diagram. (6 marks)\n" +
        "5. Compare and contrast renewable and non-renewable energy sources. (4 marks)";
    var qt = document.getElementById('quicktypeQuestions');
    if (!qt.value.trim()) {
        qt.value = template;
        qt.dispatchEvent(new Event('input'));
    }
}

// ── Structured Entry ──
function addQuestion() {
    questionCounter++;
    var qList = document.getElementById('questionsList');
    var card = document.createElement('div');
    card.className = 'eq-question-card';
    card.dataset.qnum = questionCounter;
    card.innerHTML =
        '<div class="eq-question-header">' +
            '<span class="eq-question-number">Q' + questionCounter + '</span>' +
            '<input type="number" name="q_marks[]" class="eq-marks-input" placeholder="Marks" min="1" value="1">' +
            '<button type="button" class="eq-remove-btn" onclick="removeQuestion(this)" title="Remove question">' +
                '<i class="fas fa-times"></i>' +
            '</button>' +
        '</div>' +
        '<textarea name="q_text[]" class="eq-question-text" placeholder="Type question here..." rows="3"></textarea>';
    qList.appendChild(card);
    card.querySelector('textarea').focus();
    updateRemoveButtons();
}

function removeQuestion(btn) {
    btn.closest('.eq-question-card').remove();
    renumberQuestions();
}

function renumberQuestions() {
    var cards = document.querySelectorAll('#questionsList .eq-question-card');
    cards.forEach(function(card, i) {
        card.dataset.qnum = i + 1;
        card.querySelector('.eq-question-number').textContent = 'Q' + (i + 1);
    });
    questionCounter = cards.length;
    updateRemoveButtons();
}

function updateRemoveButtons() {
    var cards = document.querySelectorAll('#questionsList .eq-question-card');
    cards.forEach(function(card) {
        var btn = card.querySelector('.eq-remove-btn');
        btn.style.display = cards.length > 1 ? 'flex' : 'none';
    });
}

// ── More Options Toggle ──
function toggleMoreOptions() {
    var panel = document.getElementById('moreOptionsPanel');
    var toggle = document.getElementById('moreOptionsToggle');
    var icon = document.getElementById('moreOptionsIcon');
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

// Drag and drop
var dropzone = document.getElementById('dropzone');
if (dropzone) {
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#4361ee';
        this.style.background = '#f0f2ff';
    });
    dropzone.addEventListener('dragleave', function() {
        this.style.borderColor = '#d1d5db';
        this.style.background = '';
    });
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#d1d5db';
        this.style.background = '';
        var input = document.getElementById('attachmentInput');
        input.files = e.dataTransfer.files;
        showFileName(input);
    });
}

// ── Form Submit: Compile structured questions ──
document.getElementById('examQuestionForm').addEventListener('submit', function(e) {
    if (currentMode === 'structured') {
        var cards = document.querySelectorAll('#questionsList .eq-question-card');
        var combined = '';
        cards.forEach(function(card, i) {
            var text = card.querySelector('.eq-question-text').value.trim();
            var marks = card.querySelector('.eq-marks-input').value || '1';
            if (text) {
                combined += (i > 0 ? '\n\n' : '') + 'Q' + (i+1) + '. ' + text + ' (' + marks + ' mark' + (marks > 1 ? 's' : '') + ')';
            }
        });
        var stInput = document.getElementById('structuredQuestions');
        stInput.value = combined;
        stInput.setAttribute('name', 'questions');
        stInput.disabled = false;
        // Disable other question fields
        document.getElementById('uploadQuestions').disabled = true;
        var qt = document.getElementById('quicktypeQuestions');
        if (qt) qt.removeAttribute('name');
    } else if (currentMode === 'upload') {
        // Already handled by switchMode
    } else if (currentMode === 'quicktype') {
        // Already handled by switchMode
    }
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
                // Show more options panel if year gets auto-filled
                if ($('#moreOptionsPanel').is(':hidden')) {
                    toggleMoreOptions();
                }
            }
            if (data.term_id) {
                $('#term_id').val(data.term_id);
            }
        }
    });
});

// ── Auto-fill: Teacher's branch, subject, class, academic year ──
$(function() {
    // Auto-select teacher's branch
    @if(auth()->user()->branch_id)
    $('#branch_id').val('{{ auth()->user()->branch_id }}');
    @endif

    // Auto-select teacher's first subject if only one
    var teacherSubjects = $('#subject_id option[data-teacher="1"]');
    if (teacherSubjects.length === 1) {
        $('#subject_id').val(teacherSubjects.first().val());
    }

    // Auto-select teacher's first class if only one
    var teacherClasses = $('#class_id option[data-teacher="1"]');
    if (teacherClasses.length === 1) {
        $('#class_id').val(teacherClasses.first().val()).trigger('change');
    }

    // Auto-select active academic year
    @if($activeAcademicYear ?? null)
    $('#academic_year_id').val('{{ $activeAcademicYear->id }}');
    @endif

    // Initialize mode
    switchMode('upload');
});

// ── Keyboard shortcut: Ctrl+Enter to submit ──
$(document).on('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        $('#examQuestionForm').submit();
    }
});
</script>
@endpush
@endsection