@extends('layouts.admin')
@section('title', 'Enter Marks')

@push('styles')
<style>
/* ===== MARK ENTRY CREATE - MODERN DESIGN ===== */
.mc-page { animation: mcFadeIn 0.4s ease-out; }
@keyframes mcFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

/* Page Header */
.mc-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.mc-header-left { flex: 1; }
.mc-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.mc-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }

/* Breadcrumb */
.mc-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.mc-breadcrumb li { color: #adb5bd; }
.mc-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.mc-breadcrumb li a:hover { color: #4361ee; }
.mc-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.mc-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Filter Panel */
.mc-filter-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.25rem; }
.mc-filter-header { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: #fafbfc; }
.mc-filter-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; background: #eef2ff; color: #4361ee; }
.mc-filter-title { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.mc-filter-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.1rem 0 0; }
.mc-filter-body { padding: 1.25rem 1.5rem; }
.mc-filter-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.75rem; }
.mc-filter-group { display: flex; flex-direction: column; }
.mc-filter-label { font-weight: 600; color: #374151; margin-bottom: 0.35rem; font-size: 0.82rem; }
.mc-filter-select { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.55rem 2rem 0.55rem 0.75rem; font-size: 0.85rem; color: #1a1a2e; background: #fff; appearance: none; cursor: pointer; transition: all 0.2s; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.1rem; }
.mc-filter-select:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }

/* Empty State */
.mc-empty { text-align: center; padding: 3rem 1.5rem; background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
.mc-empty i { font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
.mc-empty p { color: #9ca3af; font-size: 0.95rem; margin: 0; }

/* Nav Bar */
.mc-nav { display: none; background: linear-gradient(135deg, #1e3a5f, #264b73); color: #fff; border-radius: 10px; padding: 0.6rem 1rem; margin-bottom: 1rem; align-items: center; justify-content: space-between; }
.mc-nav-btn { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); color: #fff; border-radius: 8px; padding: 0.35rem 0.9rem; cursor: pointer; font-size: 0.8rem; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.4rem; }
.mc-nav-btn:hover { background: rgba(255,255,255,0.22); }
.mc-nav-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.mc-nav-counter { font-weight: 700; font-size: 0.9rem; }

/* Student Header */
.mc-student-header { display: flex; justify-content: space-between; align-items: flex-start; background: linear-gradient(135deg, #1e3a5f, #264b73); color: #fff; padding: 1.1rem 1.5rem; border-radius: 12px; margin-bottom: 1rem; }
.mc-student-name { font-size: 1.3rem; font-weight: 800; margin: 0; letter-spacing: 0.01em; }
.mc-student-meta { margin-top: 0.35rem; display: flex; gap: 0.6rem; flex-wrap: wrap; }
.mc-student-meta span { font-size: 0.78rem; opacity: 0.85; background: rgba(255,255,255,0.12); padding: 0.1rem 0.55rem; border-radius: 6px; }
.mc-save-area { display: flex; align-items: center; gap: 0.75rem; }
.mc-save-btn { background: #10b981; color: #fff; border: none; padding: 0.55rem 1.1rem; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 0.88rem; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.4rem; }
.mc-save-btn:hover { background: #059669; transform: translateY(-1px); }
.mc-save-badge { font-size: 0.72rem; padding: 0.2rem 0.6rem; border-radius: 6px; font-weight: 600; }
.mc-save-badge.saving { background: #fef3c7; color: #d97706; }
.mc-save-badge.saved { background: #d1fae5; color: #059669; }
.mc-save-badge.error { background: #fee2e2; color: #dc2626; }

/* Mark Card */
.mc-mark-card { background: #f0f7ff; border-radius: 12px; padding: 1.25rem; }
.mc-marks-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }

/* Sections */
.mc-section { border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; background: #fff; }
.mc-section-head { display: flex; justify-content: space-between; align-items: center; padding: 0.55rem 1rem; }
.mc-section-head.ca { background: linear-gradient(135deg, #eff6ff, #dbeafe); border-bottom: 1px solid #bfdbfe; }
.mc-section-head.exam { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-bottom: 1px solid #a7f3d0; }
.mc-section-title { font-size: 0.88rem; font-weight: 700; margin: 0; }
.mc-section-title.ca { color: #1d4ed8; }
.mc-section-title.exam { color: #059669; }
.mc-section-max { font-size: 0.75rem; color: #6b7280; font-weight: 500; }
.mc-section-body { padding: 0.75rem 1rem 1rem; }

/* CA Items */
.mc-ca-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.4rem; }
.mc-ca-item { display: flex; align-items: center; border: 1.5px solid #e5e7eb; border-radius: 8px; overflow: hidden; transition: border-color 0.2s; }
.mc-ca-item:focus-within { border-color: #4361ee; box-shadow: 0 0 0 2px rgba(67,97,238,0.08); }
.mc-ca-badge { min-width: 1.5rem; display: flex; align-items: center; justify-content: center; background: #4361ee; color: #fff; font-size: 0.7rem; font-weight: 700; padding: 0.35rem 0; flex-shrink: 0; }
.mc-ca-input { width: 100%; border: none; outline: none; text-align: center; padding: 0.35rem 0.1rem; font-size: 0.82rem; font-weight: 600; color: #1a1a2e; background: transparent; }

.mc-ca-extra { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-top: 0.6rem; }
.mc-extra-item { display: flex; flex-direction: column; }
.mc-extra-label { font-size: 0.7rem; font-weight: 600; color: #6b7280; margin-bottom: 0.15rem; text-align: center; }
.mc-extra-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; outline: none; text-align: center; padding: 0.35rem; font-size: 0.82rem; font-weight: 600; color: #1a1a2e; transition: all 0.2s; }
.mc-extra-input:focus { border-color: #4361ee; box-shadow: 0 0 0 2px rgba(67,97,238,0.08); }

/* Exam Fields */
.mc-exam-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem; }
.mc-exam-item { display: flex; flex-direction: column; }
.mc-exam-label { font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 0.15rem; display: flex; justify-content: space-between; }
.mc-exam-max { font-weight: 400; color: #9ca3af; font-size: 0.7rem; }
.mc-exam-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; outline: none; text-align: center; padding: 0.45rem; font-size: 0.88rem; font-weight: 600; color: #1a1a2e; transition: all 0.2s; }
.mc-exam-input:focus { border-color: #10b981; box-shadow: 0 0 0 2px rgba(16,185,129,0.08); }

/* Totals Bar */
.mc-totals { margin-top: 1rem; background: linear-gradient(135deg, #1e293b, #334155); color: #fff; border-radius: 10px; padding: 0.9rem 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; }
.mc-total-item { text-align: center; }
.mc-total-label { font-size: 0.68rem; opacity: 0.55; display: block; margin-bottom: 0.05rem; }
.mc-total-value { font-size: 1.2rem; font-weight: 800; }
.mc-total-unit { font-size: 0.68rem; opacity: 0.45; }
.mc-total-sep { font-size: 1rem; opacity: 0.3; }
.mc-grade { min-width: 44px; padding: 0.15rem 0.5rem; border-radius: 7px; font-size: 1.3rem; font-weight: 800; text-align: center; }
.mc-grade-A { background: rgba(52,211,153,0.25); color: #6ee7b7; }
.mc-grade-B { background: rgba(96,165,250,0.25); color: #93c5fd; }
.mc-grade-C { background: rgba(251,191,36,0.25); color: #fcd34d; }
.mc-grade-D { background: rgba(251,146,60,0.25); color: #fdba74; }
.mc-grade-F { background: rgba(248,113,113,0.25); color: #fca5a5; }

/* Responsive */
@media (max-width: 992px) { .mc-marks-grid { grid-template-columns: 1fr; } }
@media (max-width: 768px) {
    .mc-filter-grid { grid-template-columns: 1fr 1fr; }
    .mc-student-header { flex-direction: column; gap: 0.75rem; }
}
@media (max-width: 480px) {
    .mc-filter-grid { grid-template-columns: 1fr; }
    .mc-ca-grid { grid-template-columns: repeat(3, 1fr); }
}
</style>
@endpush

@section('content')
<div class="mc-page">
    {{-- Header --}}
    <div class="mc-header">
        <div class="mc-header-left">
            <nav aria-label="breadcrumb" class="mc-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.mark-entries.index') }}">Mark Entry</a></li>
                    <li class="active">Enter Marks</li>
                </ol>
            </nav>
            <h1 class="mc-title">Enter Marks</h1>
            <p class="mc-subtitle">Select filters then enter marks for each student</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="mc-filter-card">
        <div class="mc-filter-header">
            <div class="mc-filter-icon"><i class="fas fa-filter"></i></div>
            <div>
                <h3 class="mc-filter-title">Select Filters</h3>
                <p class="mc-filter-desc">Choose academic year, term, class, section, and subject</p>
            </div>
        </div>
        <div class="mc-filter-body">
            <div class="mc-filter-grid">
                <div class="mc-filter-group">
                    <label class="mc-filter-label">Academic Year</label>
                    <select id="sel_ay" class="mc-filter-select">
                        <option value="">-- Select --</option>
                        @foreach ($academicYears as $ay)
                            <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mc-filter-group">
                    <label class="mc-filter-label">Term</label>
                    <select id="sel_term" class="mc-filter-select">
                        <option value="">-- Year First --</option>
                    </select>
                </div>
                <div class="mc-filter-group">
                    <label class="mc-filter-label">Subject</label>
                    <select id="sel_subject" class="mc-filter-select">
                        <option value="">-- Select --</option>
                        @foreach ($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mc-filter-group">
                    <label class="mc-filter-label">Class</label>
                    <select id="sel_grade" class="mc-filter-select">
                        <option value="">-- Select --</option>
                        @foreach ($classGrades as $g)
                            <option value="{{ $g }}">{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mc-filter-group">
                    <label class="mc-filter-label">Section</label>
                    <select id="sel_section" class="mc-filter-select">
                        <option value="">-- Class First --</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <div class="mc-nav" id="navBar">
        <button class="mc-nav-btn" onclick="goPrev()" id="btnPrev"><i class="fas fa-chevron-left"></i> Prev</button>
        <span class="mc-nav-counter" id="navCounter">1 / 1</span>
        <button class="mc-nav-btn" onclick="goNext()" id="btnNext">Next <i class="fas fa-chevron-right"></i></button>
    </div>

    {{-- Mark Card --}}
    <div id="markCard" style="display:none;">
        <div class="mc-student-header">
            <div>
                <h3 class="mc-student-name" id="studentName">--</h3>
                <div class="mc-student-meta">
                    <span id="studentAdm">--</span>
                    <span id="studentSubject">--</span>
                    <span id="studentYear">--</span>
                </div>
            </div>
            <div class="mc-save-area">
                <button class="mc-save-btn" onclick="saveMarks()"><i class="fas fa-check"></i> Save All</button>
                <span class="mc-save-badge" id="saveStatus" style="display:none;"></span>
            </div>
        </div>

        <div class="mc-mark-card">
            <div class="mc-marks-grid">
                {{-- CA Section --}}
                <div class="mc-section">
                    <div class="mc-section-head ca">
                        <h4 class="mc-section-title ca"><i class="fas fa-clipboard-list me-1"></i> Continuous Assessment</h4>
                        <span class="mc-section-max">Raw /70 → Scaled /30</span>
                    </div>
                    <div class="mc-section-body">
                        <div class="mc-ca-grid">
                            @for ($i = 1; $i <= 10; $i++)
                            <div class="mc-ca-item">
                                <span class="mc-ca-badge">{{ $i }}</span>
                                <input type="text" inputmode="decimal" data-field="ca{{ $i }}" data-group="ca" data-max="5" class="mc-ca-input mi" placeholder="/5">
                            </div>
                            @endfor
                        </div>
                        <div class="mc-ca-extra">
                            <div class="mc-extra-item">
                                <label class="mc-extra-label">Conduct /5</label>
                                <input type="text" inputmode="decimal" data-field="conduct" data-group="ca" data-max="5" class="mc-extra-input mi" placeholder="/5">
                            </div>
                            <div class="mc-extra-item">
                                <label class="mc-extra-label">Handwriting /5</label>
                                <input type="text" inputmode="decimal" data-field="handwriting" data-group="ca" data-max="5" class="mc-extra-input mi" placeholder="/5">
                            </div>
                            <div class="mc-extra-item">
                                <label class="mc-extra-label">Creativity /10</label>
                                <input type="text" inputmode="decimal" data-field="creativity" data-group="ca" data-max="10" class="mc-extra-input mi" placeholder="/10">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Exam Section --}}
                <div class="mc-section">
                    <div class="mc-section-head exam">
                        <h4 class="mc-section-title exam"><i class="fas fa-file-alt me-1"></i> Examination</h4>
                        <span class="mc-section-max">/70</span>
                    </div>
                    <div class="mc-section-body">
                        <div class="mc-exam-grid">
                            <div class="mc-exam-item">
                                <label class="mc-exam-label">Test 1 <span class="mc-exam-max">/10</span></label>
                                <input type="text" inputmode="decimal" data-field="test1" data-group="exam" data-max="10" class="mc-exam-input mi" placeholder="0">
                            </div>
                            <div class="mc-exam-item">
                                <label class="mc-exam-label">Test 2 <span class="mc-exam-max">/10</span></label>
                                <input type="text" inputmode="decimal" data-field="test2" data-group="exam" data-max="10" class="mc-exam-input mi" placeholder="0">
                            </div>
                            <div class="mc-exam-item">
                                <label class="mc-exam-label">Mid-Term <span class="mc-exam-max">/20</span></label>
                                <input type="text" inputmode="decimal" data-field="mid_term" data-group="exam" data-max="20" class="mc-exam-input mi" placeholder="0">
                            </div>
                            <div class="mc-exam-item">
                                <label class="mc-exam-label">Final Exam <span class="mc-exam-max">/30</span></label>
                                <input type="text" inputmode="decimal" data-field="final_exam" data-group="exam" data-max="30" class="mc-exam-input mi" placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Totals Bar --}}
            <div class="mc-totals">
                <div class="mc-total-item">
                    <span class="mc-total-label">CA Raw</span>
                    <span class="mc-total-value" id="tCaRaw">0</span><span class="mc-total-unit">/70</span>
                </div>
                <div class="mc-total-sep"><i class="fas fa-arrow-right"></i></div>
                <div class="mc-total-item">
                    <span class="mc-total-label">CA Scaled</span>
                    <span class="mc-total-value" id="tCaScaled">0</span><span class="mc-total-unit">/30</span>
                </div>
                <div class="mc-total-sep">+</div>
                <div class="mc-total-item">
                    <span class="mc-total-label">Exam Total</span>
                    <span class="mc-total-value" id="tExam">0</span><span class="mc-total-unit">/70</span>
                </div>
                <div class="mc-total-sep">=</div>
                <div class="mc-total-item">
                    <span class="mc-total-label">Term Total</span>
                    <span class="mc-total-value" id="tTotal">0</span><span class="mc-total-unit">/100</span>
                </div>
                <div class="mc-total-item">
                    <span class="mc-total-label">Grade</span>
                    <span class="mc-grade mc-grade-F" id="tGrade">-</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Empty State --}}
    <div id="emptyState" class="mc-empty">
        <i class="fas fa-hand-pointer"></i>
        <p>Select academic year, term, subject, class, and section above to begin entering marks</p>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('scripts')
<script>
(function() {
    var students = [], marksData = {}, curIdx = 0, saveTimer = null;
    var csrf = document.querySelector('meta[name="csrf-token"]').content;

    document.getElementById('sel_ay').addEventListener('change', function() {
        if (!this.value) {
            document.getElementById('sel_term').innerHTML = '<option value="">-- Year First --</option>';
            return;
        }
        fetch('/admin/mark-entries/api/terms?academic_year_id=' + this.value).then(function(r) { return r.json(); }).then(function(terms) {
            var s = document.getElementById('sel_term');
            s.innerHTML = '<option value="">-- Select Term --</option>';
            terms.forEach(function(t) { s.innerHTML += '<option value="' + t.id + '">' + t.name + '</option>'; });
        });
    });

    document.getElementById('sel_grade').addEventListener('change', function() {
        if (!this.value) {
            document.getElementById('sel_section').innerHTML = '<option value="">-- Class First --</option>';
            return;
        }
        fetch('/admin/mark-entries/api/sections?class_grade=' + encodeURIComponent(this.value)).then(function(r) { return r.json(); }).then(function(secs) {
            var s = document.getElementById('sel_section');
            s.innerHTML = '<option value="">-- Select --</option>';
            secs.forEach(function(v) { s.innerHTML += '<option value="' + v + '">' + v + '</option>'; });
            tryAutoLoad();
        });
    });

    ['sel_ay','sel_term','sel_section','sel_subject'].forEach(function(id) {
        document.getElementById(id).addEventListener('change', tryAutoLoad);
    });

    function tryAutoLoad() {
        var ay = document.getElementById('sel_ay').value,
            tm = document.getElementById('sel_term').value,
            su = document.getElementById('sel_subject').value,
            gr = document.getElementById('sel_grade').value,
            se = document.getElementById('sel_section').value;
        if (ay && tm && su && gr && se) loadStudents();
    }

    function loadStudents() {
        var ay = document.getElementById('sel_ay').value,
            tm = document.getElementById('sel_term').value,
            su = document.getElementById('sel_subject').value,
            gr = document.getElementById('sel_grade').value,
            se = document.getElementById('sel_section').value;
        if (!ay || !tm || !su || !gr || !se) return;
        fetch('/admin/mark-entries/api/students?academic_year_id=' + ay + '&term_id=' + tm + '&subject_id=' + su + '&class_grade=' + encodeURIComponent(gr) + '&section=' + encodeURIComponent(se))
            .then(function(r) { return r.json(); })
            .then(function(d) {
                students = d.students || [];
                marksData = d.marks || {};
                if (!students.length) { alert('No students found for this selection.'); return; }
                curIdx = 0;
                showStudent(0);
                document.getElementById('navBar').style.display = 'flex';
                document.getElementById('markCard').style.display = 'block';
                document.getElementById('emptyState').style.display = 'none';
            });
    }

    function showStudent(i) {
        var s = students[i];
        var studentName = [s.first_name || s.student_name || s.name, s.last_name].filter(Boolean).join(' ') || 'Student';
        var subjectText = document.getElementById('sel_subject').selectedOptions[0]?.text || '--';
        var yearText = document.getElementById('sel_ay').selectedOptions[0]?.text || '--';
        document.getElementById('studentName').textContent = studentName;
        document.getElementById('studentAdm').textContent = s.admission_number || '';
        document.getElementById('studentSubject').textContent = 'Subject: ' + subjectText;
        document.getElementById('studentYear').textContent = 'Year: ' + yearText;
        document.getElementById('navCounter').textContent = (i + 1) + ' / ' + students.length;
        document.getElementById('btnPrev').disabled = i === 0;
        document.getElementById('btnNext').disabled = i === students.length - 1;
        document.querySelectorAll('.mi').forEach(function(inp) { inp.value = ''; });
        var m = marksData[s.id];
        if (m) {
            document.querySelectorAll('.mi').forEach(function(inp) {
                var f = inp.dataset.field;
                if (m[f] !== null && m[f] !== undefined) inp.value = m[f];
            });
        }
        recalc();
    }

    window.goPrev = function() { if (curIdx > 0) { curIdx--; showStudent(curIdx); } };
    window.goNext = function() { if (curIdx < students.length - 1) { curIdx++; showStudent(curIdx); } };

    function recalc() {
        var caR = 0, exR = 0;
        document.querySelectorAll('.mi').forEach(function(inp) {
            var v = parseFloat(inp.value) || 0;
            if (inp.dataset.group === 'ca') caR += v;
            else exR += v;
        });
        // CA scaled: round to 2 decimals like PHP round(($caRaw / 70) * 30, 2)
        var caS = Math.round((caR / 70) * 30 * 100) / 100;
        // Exam total: cap at 70 like PHP min($examRaw, 70)
        var exT = Math.min(exR, 70);
        // Grand total: round to 2 decimals like PHP
        var tot = Math.round((caS + exT) * 100) / 100;
        var g = 'F', gClass = 'mc-grade-F';
        if (tot >= 90) { g = 'A+'; gClass = 'mc-grade-A'; }
        else if (tot >= 80) { g = 'A'; gClass = 'mc-grade-A'; }
        else if (tot >= 75) { g = 'A-'; gClass = 'mc-grade-A'; }
        else if (tot >= 70) { g = 'B+'; gClass = 'mc-grade-B'; }
        else if (tot >= 65) { g = 'B'; gClass = 'mc-grade-B'; }
        else if (tot >= 60) { g = 'B-'; gClass = 'mc-grade-B'; }
        else if (tot >= 55) { g = 'C+'; gClass = 'mc-grade-C'; }
        else if (tot >= 50) { g = 'C'; gClass = 'mc-grade-C'; }
        else if (tot >= 45) { g = 'C-'; gClass = 'mc-grade-C'; }
        else if (tot >= 40) { g = 'D'; gClass = 'mc-grade-D'; }
        document.getElementById('tCaRaw').textContent = caR.toFixed(1);
        document.getElementById('tCaScaled').textContent = caS.toFixed(2);
        document.getElementById('tExam').textContent = exT.toFixed(1);
        document.getElementById('tTotal').textContent = tot.toFixed(2);
        var ge = document.getElementById('tGrade');
        ge.textContent = g;
        ge.className = 'mc-grade ' + gClass;
    }

    function saveField(field, value) {
        if (!students.length) return;
        var s = students[curIdx];
        var st = document.getElementById('saveStatus');
        var d = {
            student_id: s.id, subject_id: document.getElementById('sel_subject').value,
            academic_year_id: document.getElementById('sel_ay').value,
            term_id: document.getElementById('sel_term').value,
            class_grade: document.getElementById('sel_grade').value,
            section: document.getElementById('sel_section').value,
            mark_key: field, mark_value: (value === '' || value === undefined) ? null : value
        };
        marksData[s.id] = marksData[s.id] || {};
        marksData[s.id][field] = (value === '' || value === undefined) ? null : value;
        st.style.display = 'inline-block';
        st.className = 'mc-save-badge saving';
        st.textContent = 'Saving...';
        fetch('/admin/mark-entries/api/save', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(d)
        }).then(function(r) {
            if (!r.ok) return r.json().then(function(e) { throw new Error(e.error || 'Server error ' + r.status); });
            return r.json();
        }).then(function(res) {
            if (res.success) {
                st.className = 'mc-save-badge saved'; st.textContent = 'Saved!';
                marksData[s.id] = res.entry || marksData[s.id];
                setTimeout(function() { st.style.display = 'none'; }, 1500);
            } else {
                st.className = 'mc-save-badge error'; st.textContent = res.error || 'Error';
            }
        }).catch(function(err) {
            st.className = 'mc-save-badge error'; st.textContent = err.message || 'Network Error';
            console.error('Save error:', err);
        });
    }

    window.saveMarks = function() {
        if (!students.length) return;
        var s = students[curIdx], d = {};
        d.student_id = s.id;
        d.subject_id = document.getElementById('sel_subject').value;
        d.academic_year_id = document.getElementById('sel_ay').value;
        d.term_id = document.getElementById('sel_term').value;
        d.class_grade = document.getElementById('sel_grade').value;
        d.section = document.getElementById('sel_section').value;
        document.querySelectorAll('.mi').forEach(function(inp) {
            var v = inp.value;
            d[inp.dataset.field] = (v === '' || v === undefined) ? null : v;
        });
        var st = document.getElementById('saveStatus');
        marksData[s.id] = d;
        st.style.display = 'inline-block';
        st.className = 'mc-save-badge saving';
        st.textContent = 'Saving...';
        fetch('/admin/mark-entries/api/save', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(d)
        }).then(function(r) {
            if (!r.ok) return r.json().then(function(e) { throw new Error(e.error || 'Server error ' + r.status); });
            return r.json();
        }).then(function(res) {
            if (res.success) {
                st.className = 'mc-save-badge saved'; st.textContent = 'Saved!';
                marksData[s.id] = res.entry || d;
                setTimeout(function() { st.style.display = 'none'; }, 1500);
            } else {
                st.className = 'mc-save-badge error'; st.textContent = res.error || 'Error';
            }
        }).catch(function(err) {
            st.className = 'mc-save-badge error'; st.textContent = err.message || 'Network Error';
            console.error('Save error:', err);
        });
    };

    function initMarkEntry() {
        document.querySelectorAll('.mi').forEach(function(inp) {
            inp.addEventListener('input', function() {
                // Clean value: allow only digits and one decimal point with max 1 decimal place
                var raw = this.value;
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
                recalc();
                var self = this;
                if (saveTimer) clearTimeout(saveTimer);
                saveTimer = setTimeout(function() { saveField(self.dataset.field, self.value); }, 800);
            });
            inp.addEventListener('blur', function() {
                var mx = parseFloat(this.dataset.max), v = parseFloat(this.value);
                if (this.value === '') { recalc(); return; }
                if (!isNaN(v) && v > mx) v = mx;
                if (!isNaN(v) && v < 0) v = 0;
                if (!isNaN(v)) this.value = Math.round(v * 10) / 10; // Round to 1 decimal
                else this.value = '';
                recalc();
                if (saveTimer) { clearTimeout(saveTimer); saveTimer = null; }
                saveField(this.dataset.field, this.value);
            });
        });
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT') return;
            if (e.key === 'ArrowLeft') goPrev();
            if (e.key === 'ArrowRight') goNext();
        });
        var txS = 0;
        var mc = document.getElementById('markCard');
        if (mc) {
            mc.addEventListener('touchstart', function(e) { txS = e.touches[0].clientX; });
            mc.addEventListener('touchend', function(e) {
                var diff = e.changedTouches[0].clientX - txS;
                if (Math.abs(diff) > 60) { diff > 0 ? goPrev() : goNext(); }
            });
        }
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initMarkEntry); }
    else { initMarkEntry(); }
})();
</script>
@endsection
