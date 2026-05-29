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

            {{-- Step 1: Basic Info --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Basic Information</h3>
                        <p class="modern-form-section-desc">Title, type, marks and duration</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label">Title <span class="modern-required">*</span></label>
                            <input type="text" name="title" class="modern-input" value="{{ old('title') }}" placeholder="e.g. Grade 10 Math Midterm Questions" required autofocus style="padding-left:0.9rem;">
                            @error('title')<span class="modern-form-error">{{ $message }}</span>@enderror
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
                </div>
            </div>

            {{-- Step 2: Academic Context --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Academic Context</h3>
                        <p class="modern-form-section-desc">Subject, class, section and exam details</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Subject <span class="modern-required">*</span></label>
                            <select name="subject_id" id="subject_id" class="modern-input modern-select" required>
                                <option value="">-- Select Subject --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Class <span class="modern-required">*</span></label>
                            <select name="class_id" id="class_id" class="modern-input modern-select" required>
                                <option value="">-- Select Class --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                @endforeach
                            </select>
                            @error('class_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Section</label>
                            <select name="section_id" id="section_id" class="modern-input modern-select">
                                <option value="">-- All Sections --</option>
                            </select>
                            @error('section_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Exam</label>
                            <select name="exam_id" id="exam_id" class="modern-input modern-select">
                                <option value="">-- Select Exam --</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ old('exam_id') == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                                @endforeach
                            </select>
                            @error('exam_id')<span class="modern-form-error">{{ $message }}</span>@enderror
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
                                @foreach($allTerms as $term)
                                    <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                                @endforeach
                            </select>
                            @error('term_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 3: Questions Content --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-gold">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Questions Content</h3>
                        <p class="modern-form-section-desc">Enter your exam questions below</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    {{-- Tab toggle: Structured vs Free Text --}}
                    <div style="display:flex;gap:0;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:1.25rem;width:fit-content;">
                        <button type="button" id="tabStructured" onclick="switchTab('structured')" class="eq-tab eq-tab-active">
                            <i class="fas fa-list-ol"></i> Structured Entry
                        </button>
                        <button type="button" id="tabFreeText" onclick="switchTab('freetext')" class="eq-tab">
                            <i class="fas fa-paragraph"></i> Free Text
                        </button>
                    </div>

                    {{-- Structured Question Entry --}}
                    <div id="structuredPanel">
                        <div id="questionsList">
                            {{-- Question 1 is always present --}}
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

                    {{-- Free Text Entry --}}
                    <div id="freetextPanel" style="display:none;">
                        <textarea name="questions_freetext" id="freetextQuestions" class="modern-input modern-textarea" placeholder="Type or paste all exam questions here...&#10;&#10;Example:&#10;1. What is the capital of Ethiopia? (2 marks)&#10;2. Explain the water cycle. (5 marks)&#10;3. Solve: 2x + 5 = 15 (3 marks)" rows="12" style="padding-left:0.9rem;font-family:inherit;">{{ old('questions') }}</textarea>
                    </div>

                    @error('questions')<span class="modern-form-error">{{ $message }}</span>@enderror

                    {{-- Notes/Description --}}
                    <div style="margin-top:1.25rem;">
                        <label class="modern-form-label">Notes / Special Instructions</label>
                        <textarea name="description" class="modern-input modern-textarea" placeholder="Additional notes or special instructions for the reviewer..." rows="2" style="padding-left:0.9rem;">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Step 4: Attachment --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-purple">
                        <i class="fas fa-paperclip"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Attachment <small style="font-weight:400;color:#9ca3af;">(optional)</small></h3>
                        <p class="modern-form-section-desc">Upload a file with the exam questions (PDF, Word, Excel, etc.)</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="eq-dropzone" id="dropzone" onclick="document.getElementById('attachmentInput').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:#9ca3af;margin-bottom:0.5rem;"></i>
                        <p style="color:#6b7280;margin:0;">Click to upload or drag and drop</p>
                        <p style="color:#9ca3af;font-size:0.8rem;margin:0.25rem 0 0;">PDF, DOC, DOCX, XLSX, PPT, TXT, JPG, PNG (max 10MB)</p>
                        <input type="file" name="attachment" id="attachmentInput" accept=".pdf,.doc,.docx,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png" style="display:none;" onchange="showFileName(this)">
                    </div>
                    <div id="fileInfo" style="display:none;margin-top:0.75rem;padding:0.5rem 0.75rem;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;display:none;">
                        <i class="fas fa-file" style="color:#10b981;"></i>
                        <span id="fileName" style="color:#065f46;font-weight:500;"></span>
                        <button type="button" onclick="removeFile()" style="margin-left:0.5rem;color:#ef4444;background:none;border:none;cursor:pointer;"><i class="fas fa-times"></i></button>
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
                        <i class="fas fa-paper-plane"></i> Submit for Review
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.eq-tab { padding:0.5rem 1rem;border:none;background:#f9fafb;color:#6b7280;font-weight:600;font-size:0.85rem;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;gap:0.4rem; }
.eq-tab:hover { background:#f3f4f6; }
.eq-tab-active { background:#4361ee;color:#fff; }
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
.eq-dropzone { border:2px dashed #d1d5db;border-radius:12px;padding:2rem;text-align:center;cursor:pointer;transition:all 0.2s; }
.eq-dropzone:hover { border-color:#4361ee;background:#f8f9ff; }
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
@media(max-width:768px) { .modern-form-grid{grid-template-columns:1fr;} .modern-form-span-2{grid-column:span 1;} .modern-form-section-body{padding:1rem 1.25rem 1.5rem;} .modern-form-section-header{padding:1.25rem 1.25rem .75rem;} .modern-form-actions{padding:1rem 1.25rem;flex-direction:column;} .btn-modern{justify-content:center;width:100%;} }
</style>
@endpush

@push('scripts')
<script>
let questionCounter = 1;

function addQuestion() {
    questionCounter++;
    const qList = document.getElementById('questionsList');
    const card = document.createElement('div');
    card.className = 'eq-question-card';
    card.dataset.qnum = questionCounter;
    card.innerHTML = `
        <div class="eq-question-header">
            <span class="eq-question-number">Q${questionCounter}</span>
            <input type="number" name="q_marks[]" class="eq-marks-input" placeholder="Marks" min="1" value="1">
            <button type="button" class="eq-remove-btn" onclick="removeQuestion(this)" title="Remove question">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <textarea name="q_text[]" class="eq-question-text" placeholder="Type question here..." rows="3"></textarea>
    `;
    qList.appendChild(card);
    card.querySelector('textarea').focus();
    updateRemoveButtons();
}

function removeQuestion(btn) {
    btn.closest('.eq-question-card').remove();
    renumberQuestions();
}

function renumberQuestions() {
    const cards = document.querySelectorAll('#questionsList .eq-question-card');
    cards.forEach((card, i) => {
        card.dataset.qnum = i + 1;
        card.querySelector('.eq-question-number').textContent = 'Q' + (i + 1);
    });
    questionCounter = cards.length;
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const cards = document.querySelectorAll('#questionsList .eq-question-card');
    cards.forEach(card => {
        const btn = card.querySelector('.eq-remove-btn');
        btn.style.display = cards.length > 1 ? 'flex' : 'none';
    });
}

function switchTab(tab) {
    const structuredPanel = document.getElementById('structuredPanel');
    const freetextPanel = document.getElementById('freetextPanel');
    const tabStructured = document.getElementById('tabStructured');
    const tabFreeText = document.getElementById('tabFreeText');

    if (tab === 'structured') {
        structuredPanel.style.display = 'block';
        freetextPanel.style.display = 'none';
        tabStructured.classList.add('eq-tab-active');
        tabFreeText.classList.remove('eq-tab-active');
    } else {
        structuredPanel.style.display = 'none';
        freetextPanel.style.display = 'block';
        tabStructured.classList.remove('eq-tab-active');
        tabFreeText.classList.add('eq-tab-active');
    }
}

// On form submit, combine structured questions into the hidden 'questions' field
document.getElementById('examQuestionForm').addEventListener('submit', function(e) {
    const activeTab = document.getElementById('tabStructured').classList.contains('eq-tab-active') ? 'structured' : 'freetext';

    if (activeTab === 'structured') {
        const cards = document.querySelectorAll('#questionsList .eq-question-card');
        let combined = '';
        cards.forEach((card, i) => {
            const text = card.querySelector('.eq-question-text').value.trim();
            const marks = card.querySelector('.eq-marks-input').value || '1';
            if (text) {
                combined += (i > 0 ? '\n\n' : '') + `Q${i+1}. ${text} (${marks} mark${marks > 1 ? 's' : ''})`;
            }
        });
        document.getElementById('structuredQuestions').value = combined;
        // Also set the freetext textarea so the server always gets 'questions' field
        // We need to set the name attribute dynamically
        document.getElementById('freetextQuestions').removeAttribute('name');
        document.getElementById('structuredQuestions').setAttribute('name', 'questions');
    } else {
        // Free text mode — the textarea already has name='questions_freetext'
        // Move its value to the hidden questions field
        const freetext = document.getElementById('freetextQuestions');
        document.getElementById('structuredQuestions').value = freetext.value;
        freetext.removeAttribute('name');
        document.getElementById('structuredQuestions').setAttribute('name', 'questions');
    }
});

function showFileName(input) {
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    if (input.files.length > 0) {
        fileName.textContent = input.files[0].name + ' (' + (input.files[0].size / 1024).toFixed(1) + ' KB)';
        fileInfo.style.display = 'flex';
        fileInfo.style.alignItems = 'center';
        fileInfo.style.gap = '0.5rem';
    }
}

function removeFile() {
    document.getElementById('attachmentInput').value = '';
    document.getElementById('fileInfo').style.display = 'none';
}

// Drag and drop
const dropzone = document.getElementById('dropzone');
dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.style.borderColor = '#4361ee'; dropzone.style.background = '#f8f9ff'; });
dropzone.addEventListener('dragleave', () => { dropzone.style.borderColor = '#d1d5db'; dropzone.style.background = ''; });
dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.style.borderColor = '#d1d5db'; dropzone.style.background = '';
    const input = document.getElementById('attachmentInput');
    input.files = e.dataTransfer.files;
    showFileName(input);
});

// AJAX: Load sections when class changes
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

// AJAX: Auto-fill academic year and term when exam is selected
$('#exam_id').on('change', function() {
    var examId = $(this).val();
    if (!examId) return;
    $.ajax({
        url: '{{ route("admin.api.exam-details", ["exam" => 0]) }}'.replace('/0', '/' + examId),
        dataType: 'json',
        success: function(data) {
            if (data.academic_year_id) $('#academic_year_id').val(data.academic_year_id);
            if (data.term_id) $('#term_id').val(data.term_id);
        }
    });
});

// Auto-select teacher's branch
@if($teacher ?? null)
@if(auth()->user()->branch_id)
$('#branch_id').val('{{ auth()->user()->branch_id }}');
@endif
@endif
</script>
@endpush
@endsection
