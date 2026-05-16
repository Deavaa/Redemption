@extends('layouts.admin')
@section('title', 'Mark Entry')

@push('styles')
<style>
/* ===== MARK ENTRY - MODERN DESIGN ===== */
.me-page { animation: meFadeIn 0.4s ease-out; }
@keyframes meFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

/* Page Header */
.me-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.me-header-left { flex: 1; }
.me-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.me-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }

/* Breadcrumb */
.me-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.me-breadcrumb li { color: #adb5bd; }
.me-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.me-breadcrumb li a:hover { color: #4361ee; }
.me-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.me-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Filter Panel */
.me-filter-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.25rem; }
.me-filter-header { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: #fafbfc; }
.me-filter-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; background: #eef2ff; color: #4361ee; }
.me-filter-title { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.me-filter-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.1rem 0 0; }
.me-filter-body { padding: 1.25rem 1.5rem; }
.me-filter-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.me-filter-group { display: flex; flex-direction: column; }
.me-filter-label { font-weight: 600; color: #374151; margin-bottom: 0.4rem; font-size: 0.85rem; }
.me-filter-select { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.6rem 2.2rem 0.6rem 0.8rem; font-size: 0.88rem; color: #1a1a2e; background: #fff; appearance: none; cursor: pointer; transition: all 0.2s; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.6rem center; background-repeat: no-repeat; background-size: 1.15rem; }
.me-filter-select:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.me-filter-select:disabled { background: #f9fafb; color: #9ca3af; cursor: not-allowed; }

/* Info Banner */
.me-banner { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 0.75rem 1.25rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.75rem; color: #1e40af; font-weight: 500; font-size: 0.9rem; }
.me-banner i { font-size: 1.1rem; }

/* Student Entry Card */
.me-entry-card { background: #fff; border-radius: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden; }

/* Student Header Bar */
.me-student-header { background: linear-gradient(135deg, #1e3a5f 0%, #264b73 100%); color: #fff; padding: 1rem 1.5rem; position: relative; }
.me-student-header-row { display: flex; align-items: center; gap: 0.75rem; }
.me-nav-btn { width: 36px; height: 36px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.25); background: rgba(255,255,255,0.1); color: #fff; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 0.9rem; flex-shrink: 0; }
.me-nav-btn:hover { background: rgba(255,255,255,0.22); border-color: rgba(255,255,255,0.4); }
.me-nav-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.me-student-info { flex: 1; min-width: 0; }
.me-student-name { font-size: 1.15rem; font-weight: 700; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.me-student-counter { font-size: 0.8rem; opacity: 0.75; display: flex; align-items: center; gap: 0.75rem; margin-top: 0.15rem; }
.me-save-badge { font-size: 0.7rem; padding: 0.1rem 0.5rem; border-radius: 6px; font-weight: 600; white-space: nowrap; }
.me-save-badge.saving { background: rgba(251,191,36,0.25); color: #fbbf24; }
.me-save-badge.saved { background: rgba(52,211,153,0.25); color: #6ee7b7; }
.me-save-badge.error { background: rgba(248,113,113,0.25); color: #fca5a5; }
.me-save-badge.idle { background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.7); }
.me-student-meta { display: flex; gap: 0.75rem; margin-top: 0.5rem; }
.me-meta-chip { font-size: 0.75rem; background: rgba(255,255,255,0.13); padding: 0.15rem 0.6rem; border-radius: 6px; color: rgba(255,255,255,0.88); }

/* Mark Sections */
.me-marks-body { padding: 1.25rem 1.5rem; }
.me-marks-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
.me-section { border: 1px solid #f0f0f0; border-radius: 12px; overflow: hidden; }
.me-section-header { display: flex; justify-content: space-between; align-items: center; padding: 0.65rem 1rem; }
.me-section-header.ca-header { background: linear-gradient(135deg, #eff6ff, #dbeafe); border-bottom: 1px solid #bfdbfe; }
.me-section-header.exam-header { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-bottom: 1px solid #a7f3d0; }
.me-section-title { font-size: 0.9rem; font-weight: 700; margin: 0; }
.me-section-title.ca-title { color: #1d4ed8; }
.me-section-title.exam-title { color: #059669; }
.me-section-max { font-size: 0.78rem; font-weight: 500; color: #6b7280; }
.me-section-body { padding: 0.75rem 1rem 1rem; }

/* CA Grid */
.me-ca-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.5rem; }
.me-ca-item { display: flex; align-items: center; border: 1.5px solid #e5e7eb; border-radius: 8px; overflow: hidden; transition: border-color 0.2s; }
.me-ca-item:focus-within { border-color: #4361ee; box-shadow: 0 0 0 2px rgba(67,97,238,0.08); }
.me-ca-badge { min-width: 1.6rem; height: 100%; display: flex; align-items: center; justify-content: center; background: #4361ee; color: #fff; font-size: 0.72rem; font-weight: 700; padding: 0.4rem 0; flex-shrink: 0; }
.me-ca-input { width: 100%; border: none; outline: none; text-align: center; padding: 0.4rem 0.15rem; font-size: 0.85rem; font-weight: 600; color: #1a1a2e; background: transparent; }

/* Extra CA Row */
.me-ca-extra { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-top: 0.65rem; }
.me-extra-item { display: flex; flex-direction: column; }
.me-extra-label { font-size: 0.72rem; font-weight: 600; color: #6b7280; margin-bottom: 0.2rem; text-align: center; }
.me-extra-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; outline: none; text-align: center; padding: 0.4rem; font-size: 0.85rem; font-weight: 600; color: #1a1a2e; transition: all 0.2s; }
.me-extra-input:focus { border-color: #4361ee; box-shadow: 0 0 0 2px rgba(67,97,238,0.08); }

/* Exam Fields */
.me-exam-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem; }
.me-exam-item { display: flex; flex-direction: column; }
.me-exam-label { font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 0.2rem; display: flex; justify-content: space-between; }
.me-exam-max { font-weight: 400; color: #9ca3af; font-size: 0.72rem; }
.me-exam-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; outline: none; text-align: center; padding: 0.5rem; font-size: 0.9rem; font-weight: 600; color: #1a1a2e; transition: all 0.2s; }
.me-exam-input:focus { border-color: #10b981; box-shadow: 0 0 0 2px rgba(16,185,129,0.08); }

/* Totals Bar */
.me-totals-bar { background: linear-gradient(135deg, #1e293b, #334155); color: #fff; padding: 1rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
.me-total-item { text-align: center; }
.me-total-label { font-size: 0.7rem; opacity: 0.6; display: block; margin-bottom: 0.1rem; }
.me-total-value { font-size: 1.35rem; font-weight: 800; }
.me-total-unit { font-size: 0.7rem; opacity: 0.5; }
.me-total-arrow { font-size: 1.2rem; opacity: 0.35; }
.me-total-plus { font-size: 1.4rem; opacity: 0.35; }
.me-total-equals { font-size: 1.5rem; opacity: 0.35; }
.me-grade-badge { min-width: 50px; padding: 0.25rem 0.6rem; border-radius: 8px; font-size: 1.5rem; font-weight: 800; text-align: center; }
.me-grade-A { background: rgba(52,211,153,0.25); color: #6ee7b7; }
.me-grade-B { background: rgba(96,165,250,0.25); color: #93c5fd; }
.me-grade-C { background: rgba(251,191,36,0.25); color: #fcd34d; }
.me-grade-D { background: rgba(251,146,60,0.25); color: #fdba74; }
.me-grade-F { background: rgba(248,113,113,0.25); color: #fca5a5; }

/* Empty State */
.me-empty { text-align: center; padding: 3rem 1.5rem; background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
.me-empty i { font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
.me-empty p { color: #9ca3af; font-size: 0.95rem; margin: 0; }

/* Keyboard hint */
.me-keyboard-hint { text-align: center; padding: 0.5rem; font-size: 0.75rem; color: #9ca3af; }
.me-keyboard-hint kbd { background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 4px; padding: 0.1rem 0.4rem; font-size: 0.7rem; font-family: inherit; }

/* Responsive */
@media (max-width: 992px) {
    .me-marks-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .me-header { flex-direction: column; align-items: stretch; }
    .me-title { font-size: 1.35rem; }
    .me-filter-grid { grid-template-columns: 1fr 1fr; }
    .me-ca-grid { grid-template-columns: repeat(5, 1fr); }
    .me-exam-grid { grid-template-columns: 1fr 1fr; }
    .me-totals-bar { justify-content: center; }
    .me-marks-body { padding: 1rem; }
}
@media (max-width: 480px) {
    .me-filter-grid { grid-template-columns: 1fr; }
    .me-ca-grid { grid-template-columns: repeat(3, 1fr); }
    .me-ca-extra { grid-template-columns: repeat(3, 1fr); }
}
</style>
@endpush

@section('content')
<div class="me-page">
    {{-- Page Header --}}
    <div class="me-header">
        <div class="me-header-left">
            <nav aria-label="breadcrumb" class="me-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li class="active">Mark Entry</li>
                </ol>
            </nav>
            <h1 class="me-title">Mark Entry</h1>
            <p class="me-subtitle">Select filters below, then enter marks for each student</p>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="me-filter-card" id="filterPanel">
        <div class="me-filter-header">
            <div class="me-filter-icon"><i class="fas fa-filter"></i></div>
            <div>
                <h3 class="me-filter-title">Select Class & Subject</h3>
                <p class="me-filter-desc">Choose academic year, term, section, and subject to load students</p>
            </div>
        </div>
        <div class="me-filter-body">
            <div class="me-filter-grid">
                <div class="me-filter-group">
                    <label class="me-filter-label">Academic Year</label>
                    <select id="filterAy" class="me-filter-select" {{ $isTeacher ? 'disabled' : '' }}>
                        @foreach ($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $currentAy && $currentAy->id == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="me-filter-group">
                    <label class="me-filter-label">Term</label>
                    <select id="filterTerm" class="me-filter-select" {{ $isTeacher ? 'disabled' : '' }}>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" {{ $currentTerm && $currentTerm->id == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="me-filter-group">
                    <label class="me-filter-label">Class - Section</label>
                    <select id="filterClassSection" class="me-filter-select">
                        <option value="">-- Select Class - Section --</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->class_id }}-{{ $section->id }}">{{ $section->classRoom->name }} - {{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="me-filter-group">
                    <label class="me-filter-label">Subject</label>
                    <select id="filterSubject" class="me-filter-select" disabled>
                        <option value="">-- Select Subject --</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Info Banner --}}
    <div id="infoBanner" class="me-banner d-none">
        <i class="fas fa-info-circle"></i>
        <span id="bannerText"></span>
    </div>

    {{-- Empty State --}}
    <div id="emptyState" class="me-empty">
        <i class="fas fa-hand-pointer"></i>
        <p>Select academic year, term, class-section, and subject above to begin entering marks</p>
    </div>

    {{-- Mark Entry Card (hidden until students load) --}}
    <div id="markEntryArea" class="d-none">
        <div class="me-entry-card">
            {{-- Student Header --}}
            <div class="me-student-header">
                <div class="me-student-header-row">
                    <button type="button" class="me-nav-btn" id="btnUndo" onclick="undoLastChange()" disabled aria-label="Undo" title="Undo last change">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="button" class="me-nav-btn" id="btnPrev" onclick="navigateStudent(-1)" aria-label="Previous Student">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="me-student-info">
                        <h3 class="me-student-name" id="studentName">--</h3>
                        <div class="me-student-counter">
                            <span id="navCounter">1 / 1</span>
                            <span class="me-save-badge idle" id="saveStatus">Not saved</span>
                        </div>
                    </div>
                    <button type="button" class="me-nav-btn" id="btnNext" onclick="navigateStudent(1)" aria-label="Next Student">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="me-student-meta">
                    <span class="me-meta-chip" id="metaSubject">--</span>
                    <span class="me-meta-chip" id="metaTerm">--</span>
                    <span class="me-meta-chip" id="metaClass">--</span>
                    <span class="me-meta-chip" id="metaYear">--</span>
                </div>
            </div>

            {{-- Mark Input Body --}}
            <div class="me-marks-body">
                <div class="me-marks-grid">
                    {{-- CA Section --}}
                    <div class="me-section">
                        <div class="me-section-header ca-header">
                            <h4 class="me-section-title ca-title"><i class="fas fa-clipboard-list me-1"></i> Continuous Assessment</h4>
                            <span class="me-section-max">Raw /70 → Scaled /30</span>
                        </div>
                        <div class="me-section-body">
                            <div class="me-ca-grid">
                                @for ($i = 1; $i <= 10; $i++)
                                <div class="me-ca-item">
                                    <span class="me-ca-badge">{{ $i }}</span>
                                    <input type="text" inputmode="decimal" class="me-ca-input mark-input" data-type="ca" data-number="{{ $i }}" data-max="5" placeholder="/5">
                                </div>
                                @endfor
                            </div>
                            <div class="me-ca-extra">
                                <div class="me-extra-item">
                                    <label class="me-extra-label">Conduct /5</label>
                                    <input type="text" inputmode="decimal" class="me-extra-input mark-input" data-type="ca" data-number="conduct" data-max="5" placeholder="/5">
                                </div>
                                <div class="me-extra-item">
                                    <label class="me-extra-label">Handwriting /5</label>
                                    <input type="text" inputmode="decimal" class="me-extra-input mark-input" data-type="ca" data-number="handwriting" data-max="5" placeholder="/5">
                                </div>
                                <div class="me-extra-item">
                                    <label class="me-extra-label">Creativity /10</label>
                                    <input type="text" inputmode="decimal" class="me-extra-input mark-input" data-type="ca" data-number="creativity" data-max="10" placeholder="/10">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Exam Section --}}
                    <div class="me-section">
                        <div class="me-section-header exam-header">
                            <h4 class="me-section-title exam-title"><i class="fas fa-file-alt me-1"></i> Examination</h4>
                            <span class="me-section-max">/70</span>
                        </div>
                        <div class="me-section-body">
                            <div class="me-exam-grid">
                                <div class="me-exam-item">
                                    <label class="me-exam-label">Test 1 <span class="me-exam-max">/10</span></label>
                                    <input type="text" inputmode="decimal" class="me-exam-input mark-input" data-type="exam" data-exam="test1" data-max="10" placeholder="0">
                                </div>
                                <div class="me-exam-item">
                                    <label class="me-exam-label">Test 2 <span class="me-exam-max">/10</span></label>
                                    <input type="text" inputmode="decimal" class="me-exam-input mark-input" data-type="exam" data-exam="test2" data-max="10" placeholder="0">
                                </div>
                                <div class="me-exam-item">
                                    <label class="me-exam-label">Mid-Term <span class="me-exam-max">/20</span></label>
                                    <input type="text" inputmode="decimal" class="me-exam-input mark-input" data-type="exam" data-exam="mid_term" data-max="20" placeholder="0">
                                </div>
                                <div class="me-exam-item">
                                    <label class="me-exam-label">Final Exam <span class="me-exam-max">/30</span></label>
                                    <input type="text" inputmode="decimal" class="me-exam-input mark-input" data-type="exam" data-exam="final_exam" data-max="30" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Totals Bar --}}
            <div class="me-totals-bar">
                <div class="me-total-item">
                    <span class="me-total-label">CA Raw</span>
                    <span class="me-total-value" id="tCaRaw">0</span><span class="me-total-unit">/70</span>
                </div>
                <div class="me-total-arrow"><i class="fas fa-arrow-right"></i></div>
                <div class="me-total-item">
                    <span class="me-total-label">CA Scaled</span>
                    <span class="me-total-value" id="tCaScaled">0</span><span class="me-total-unit">/30</span>
                </div>
                <div class="me-total-plus">+</div>
                <div class="me-total-item">
                    <span class="me-total-label">Exam Total</span>
                    <span class="me-total-value" id="tExam">0</span><span class="me-total-unit">/70</span>
                </div>
                <div class="me-total-equals">=</div>
                <div class="me-total-item">
                    <span class="me-total-label">Term Total</span>
                    <span class="me-total-value" id="tTotal">0</span><span class="me-total-unit">/100</span>
                </div>
                <div class="me-total-item">
                    <span class="me-total-label">Grade</span>
                    <span class="me-grade-badge me-grade-F" id="tGrade">-</span>
                </div>
            </div>
        </div>

        {{-- Keyboard Hint --}}
        <div class="me-keyboard-hint">
            Use <kbd>&larr;</kbd> <kbd>&rarr;</kbd> arrow keys or swipe to navigate students
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    let students = [];
    let currentIndex = 0;
    let saveTimer = null;
    let undoStack = [];
    const btnUndo = document.getElementById('btnUndo');
    const API_BASE = '{{ request()->root() }}/admin/mark-entries/api';

    // --- Filter handlers ---
    document.getElementById('filterAy').addEventListener('change', () => { resetSubjectSelect(); loadTerms(); });
    document.getElementById('filterTerm').addEventListener('change', () => { resetSubjectSelect(); });
    document.getElementById('filterClassSection').addEventListener('change', () => { resetSubjectSelect(); loadSubjects(); });
    document.getElementById('filterSubject').addEventListener('change', loadStudents);

    function loadTerms() {
        const ayId = document.getElementById('filterAy').value;
        fetch(`${API_BASE}/terms?academic_year_id=${ayId}`, { credentials: 'same-origin' })
            .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then(data => {
                const sel = document.getElementById('filterTerm');
                sel.innerHTML = '<option value="">-- Select Term --</option>';
                data.forEach(t => { sel.innerHTML += `<option value="${t.id}">${t.name}</option>`; });
            });
    }

    function resetSubjectSelect() {
        const sel = document.getElementById('filterSubject');
        sel.innerHTML = '<option value="">-- Select Subject --</option>';
        sel.disabled = true;
        showFilterState();
    }

    function showFilterState() {
        document.getElementById('filterPanel').classList.remove('d-none');
        document.getElementById('markEntryArea').classList.add('d-none');
        document.getElementById('infoBanner').classList.add('d-none');
        document.getElementById('emptyState').classList.remove('d-none');
    }

    function showEntryState() {
        document.getElementById('filterPanel').classList.add('d-none');
        document.getElementById('markEntryArea').classList.remove('d-none');
        document.getElementById('infoBanner').classList.add('d-none');
        document.getElementById('emptyState').classList.add('d-none');
    }

    function showBanner(msg) {
        document.getElementById('bannerText').textContent = msg;
        document.getElementById('infoBanner').classList.remove('d-none');
        document.getElementById('markEntryArea').classList.add('d-none');
        document.getElementById('emptyState').classList.add('d-none');
    }

    function loadSubjects() {
        const ayId = document.getElementById('filterAy').value;
        const csVal = document.getElementById('filterClassSection').value;
        if (!ayId || !csVal) return;
        const [classId, sectionId] = csVal.split('-');
        fetch(`${API_BASE}/subjects?class_id=${classId}&section_id=${sectionId}&academic_year_id=${ayId}`, { credentials: 'same-origin' })
            .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then(data => {
                const sel = document.getElementById('filterSubject');
                sel.innerHTML = '<option value="">-- Select Subject --</option>';
                data.forEach(s => { sel.innerHTML += `<option value="${s.id}">${s.name}</option>`; });
                sel.disabled = data.length === 0;
                if (data.length === 1) { sel.value = data[0].id; loadStudents(); }
            });
    }

    function loadStudents() {
        const ayId = document.getElementById('filterAy').value;
        const termId = document.getElementById('filterTerm').value;
        const csVal = document.getElementById('filterClassSection').value;
        const subjectId = document.getElementById('filterSubject').value;
        if (!ayId || !termId || !csVal || !subjectId) return;
        const [classId, sectionId] = csVal.split('-');

        fetch(`${API_BASE}/load-students?academic_year_id=${ayId}&term_id=${termId}&class_id=${classId}&section_id=${sectionId}&subject_id=${subjectId}`, { credentials: 'same-origin' })
            .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then(data => {
                if (data.error) throw new Error(data.error);
                const responseStudents = Array.isArray(data.students) ? data.students : [];
                students = responseStudents.map(s => ({
                    ...s,
                    id: s.student_id || s.id,
                    student_name: s.student_name || s.name || 'Student',
                    marks: {}
                }));
                // Populate marks from response
                students.forEach(s => {
                    const markFields = ['ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10','conduct','handwriting','creativity','test1','test2','mid_term','final_exam'];
                    markFields.forEach(f => { s.marks[f] = s[f] !== null && s[f] !== undefined ? s[f] : null; });
                });

                if (students.length > 0) {
                    currentIndex = 0;
                    undoStack = [];
                    btnUndo.disabled = true;
                    showEntryState();
                    displayStudent(0);
                } else {
                    showBanner('No students found for the selected filters.');
                }
            })
            .catch(err => {
                console.error('Failed to load students:', err);
                showBanner('Unable to load students: ' + err.message);
            });
    }

    // --- Display student ---
    function displayStudent(index) {
        const s = students[index];
        if (!s) return;

        // Name & counter
        document.getElementById('studentName').textContent = s.student_name || 'Student';
        document.getElementById('navCounter').textContent = `${index + 1} / ${students.length}`;

        // Meta chips
        const subjectSel = document.getElementById('filterSubject');
        const termSel = document.getElementById('filterTerm');
        const csSel = document.getElementById('filterClassSection');
        const aySel = document.getElementById('filterAy');
        document.getElementById('metaSubject').textContent = subjectSel.selectedOptions[0]?.textContent || '--';
        document.getElementById('metaTerm').textContent = termSel.selectedOptions[0]?.textContent || '--';
        document.getElementById('metaClass').textContent = csSel.selectedOptions[0]?.textContent || '--';
        document.getElementById('metaYear').textContent = aySel.selectedOptions[0]?.textContent || '--';

        // Nav buttons
        document.getElementById('btnPrev').disabled = index === 0;
        document.getElementById('btnNext').disabled = index === students.length - 1;

        // Populate mark inputs
        document.querySelectorAll('.mark-input').forEach(inp => {
            const type = inp.dataset.type;
            let key;
            if (type === 'ca') {
                key = inp.dataset.number === 'conduct' || inp.dataset.number === 'handwriting' || inp.dataset.number === 'creativity'
                    ? inp.dataset.number : `ca${inp.dataset.number}`;
            } else {
                key = inp.dataset.exam;
            }
            inp.value = (s.marks && s.marks[key] !== null && s.marks[key] !== undefined) ? s.marks[key] : '';
        });

        // Save status
        setSaveStatus('idle', 'Not saved');
        recalc();
    }

    // --- Undo logic ---
    window.undoLastChange = function() {
        if (undoStack.length === 0) return;
        const last = undoStack.pop();
        currentIndex = last.idx;
        displayStudent(currentIndex);

        // Find the input for this key and set the old value
        document.querySelectorAll('.mark-input').forEach(inp => {
            const k = getFieldKey(inp);
            if (k === last.key) {
                inp.value = last.oldVal !== null ? last.oldVal : '';
            }
        });

        // Update local data
        if (students[last.idx]) {
            students[last.idx].marks[last.key] = last.oldVal;
        }

        // Save the reverted value
        saveMark(last.key, last.oldVal);
        recalc();

        btnUndo.disabled = undoStack.length === 0;
    };

    // --- Navigation ---
    window.navigateStudent = function(dir) {
        const newIdx = currentIndex + dir;
        if (newIdx < 0 || newIdx >= students.length) return;
        currentIndex = newIdx;
        displayStudent(currentIndex);
    };

    // --- Attach input validation & auto-save ---
    function attachAutoSave() {
        document.querySelectorAll('.mark-input').forEach(inp => {
            inp.addEventListener('input', function() {
                // Clean value: allow only digits and one decimal point with max 1 decimal place
                var raw = this.value;
                // Remove everything except digits and dots
                var cleaned = raw.replace(/[^0-9.]/g, '');
                // Keep only the first dot
                var parts = cleaned.split('.');
                if (parts.length > 2) {
                    cleaned = parts[0] + '.' + parts.slice(1).join('');
                }
                // Limit to 1 decimal place
                if (cleaned.indexOf('.') !== -1) {
                    var dp = cleaned.split('.');
                    if (dp[1].length > 1) {
                        cleaned = dp[0] + '.' + dp[1].substring(0, 1);
                    }
                }
                // Only update if changed (prevent cursor jump)
                if (cleaned !== raw) {
                    var selStart = this.selectionStart;
                    this.value = cleaned;
                    this.setSelectionRange(selStart, selStart);
                }
                enforceMaxValue(this);
                recalc();
                const key = getFieldKey(this);
                const value = this.value;
                // Track change for undo before updating local data
                if (students[currentIndex]) {
                    const oldVal = students[currentIndex].marks[key] ?? null;
                    undoStack.push({ idx: currentIndex, key, oldVal });
                    btnUndo.disabled = false;
                    students[currentIndex].marks = students[currentIndex].marks || {};
                    students[currentIndex].marks[key] = value;
                }
                if (saveTimer) clearTimeout(saveTimer);
                saveTimer = setTimeout(() => saveMark(key, value), 900);
            });
            inp.addEventListener('blur', function() {
                enforceMaxValue(this);
                recalc();
                if (saveTimer) { clearTimeout(saveTimer); saveTimer = null; }
                const key = getFieldKey(this);
                saveMark(key, this.value);
            });
        });
    }

    function getFieldKey(inp) {
        const type = inp.dataset.type;
        if (type === 'ca') {
            const num = inp.dataset.number;
            return (num === 'conduct' || num === 'handwriting' || num === 'creativity') ? num : `ca${num}`;
        }
        return inp.dataset.exam;
    }

    function enforceMaxValue(inp) {
        const max = parseFloat(inp.dataset.max);
        if (inp.value === '') return;
        let v = parseFloat(inp.value);
        if (isNaN(v)) { inp.value = ''; return; }
        if (!isNaN(max) && v > max) v = max;
        if (v < 0) v = 0;
        inp.value = Math.round(v * 10) / 10; // Round to 1 decimal place
    }

    function saveMark(key, value) {
        const ayId = document.getElementById('filterAy').value;
        const termId = document.getElementById('filterTerm').value;
        const csVal = document.getElementById('filterClassSection').value;
        const [classId, sectionId] = csVal.split('-');
        const subjectId = document.getElementById('filterSubject').value;
        const student = students[currentIndex];

        setSaveStatus('saving', 'Saving...');

        // Convert empty string to null for proper DB handling
        const markValue = (value === '' || value === undefined || value === null) ? null : value;

        fetch(`${API_BASE}/save`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                student_id: student.id,
                academic_year_id: ayId,
                term_id: termId,
                class_id: classId,
                section_id: sectionId,
                subject_id: subjectId,
                mark_key: key,
                mark_value: markValue
            })
        })
        .then(r => {
            if (!r.ok) {
                return r.json().then(e => { throw new Error(e.error || 'Server error ' + r.status); })
                    .catch(err => { throw err.message ? err : new Error('Server error ' + r.status); });
            }
            return r.json();
        })
        .then(res => {
            if (res.success) {
                setSaveStatus('saved', 'Saved');
                // Update local totals from server response
                if (res.entry && students[currentIndex]) {
                    students[currentIndex].marks = students[currentIndex].marks || {};
                    students[currentIndex].marks.ca_total = res.ca_total;
                    students[currentIndex].marks.exam_total = res.exam_total;
                    students[currentIndex].marks.grand_total = res.grand_total;
                }
                setTimeout(() => setSaveStatus('idle', 'Saved'), 2000);
            } else {
                setSaveStatus('error', res.error || 'Failed');
                console.error('Save failed:', res);
            }
        })
        .catch(err => {
            setSaveStatus('error', err.message || 'Error');
            console.error('Save error:', err);
        });
    }

    function setSaveStatus(state, text) {
        const el = document.getElementById('saveStatus');
        el.className = `me-save-badge ${state}`;
        el.textContent = text;
    }

    // --- Calculation (must match PHP MarkEntry::calcTotals exactly) ---
    function recalc() {
        let caRaw = 0, examRaw = 0;
        document.querySelectorAll('.mark-input').forEach(inp => {
            const v = parseFloat(inp.value) || 0;
            if (inp.dataset.type === 'ca') caRaw += v;
            else examRaw += v;
        });
        // CA scaled: round to 2 decimals like PHP round(($caRaw / 70) * 30, 2)
        const caScaled = Math.round((caRaw / 70) * 30 * 100) / 100;
        // Exam total: cap at 70 like PHP min($examRaw, 70)
        const examTotal = Math.min(examRaw, 70);
        // Grand total: round to 2 decimals like PHP
        const grandTotal = Math.round((caScaled + examTotal) * 100) / 100;

        document.getElementById('tCaRaw').textContent = caRaw.toFixed(1);
        document.getElementById('tCaScaled').textContent = caScaled.toFixed(2);
        document.getElementById('tExam').textContent = examTotal.toFixed(1);
        document.getElementById('tTotal').textContent = grandTotal.toFixed(2);

        // Grade
        let g = 'F', gClass = 'me-grade-F';
        if (grandTotal >= 90) { g = 'A+'; gClass = 'me-grade-A'; }
        else if (grandTotal >= 80) { g = 'A'; gClass = 'me-grade-A'; }
        else if (grandTotal >= 75) { g = 'A-'; gClass = 'me-grade-A'; }
        else if (grandTotal >= 70) { g = 'B+'; gClass = 'me-grade-B'; }
        else if (grandTotal >= 65) { g = 'B'; gClass = 'me-grade-B'; }
        else if (grandTotal >= 60) { g = 'B-'; gClass = 'me-grade-B'; }
        else if (grandTotal >= 55) { g = 'C+'; gClass = 'me-grade-C'; }
        else if (grandTotal >= 50) { g = 'C'; gClass = 'me-grade-C'; }
        else if (grandTotal >= 45) { g = 'C-'; gClass = 'me-grade-C'; }
        else if (grandTotal >= 40) { g = 'D'; gClass = 'me-grade-D'; }

        const gradeEl = document.getElementById('tGrade');
        gradeEl.textContent = g;
        gradeEl.className = `me-grade-badge ${gClass}`;
    }

    // --- Keyboard navigation ---
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') return;
        if (e.key === 'ArrowLeft') navigateStudent(-1);
        if (e.key === 'ArrowRight') navigateStudent(1);
    });

    // --- Touch swipe ---
    let touchStartX = 0;
    const entryArea = document.getElementById('markEntryArea');
    if (entryArea) {
        entryArea.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; });
        entryArea.addEventListener('touchend', e => {
            const diff = e.changedTouches[0].clientX - touchStartX;
            if (Math.abs(diff) > 60) { diff > 0 ? navigateStudent(-1) : navigateStudent(1); }
        });
    }

    // --- Init ---
    attachAutoSave();
    loadTerms();
})();
</script>
@endpush
