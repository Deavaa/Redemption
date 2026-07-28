@extends('layouts.admin')
@section('title', 'Academic Transcript — Bulk Generate')

@push('styles')
<style>
.ti-page-header { display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;flex-wrap:wrap; }
.ti-page-header-left { display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
.ti-page-title { font-size:0.95rem;font-weight:700;color:var(--text-dark);margin:0; }
.ti-stats { display:flex;gap:8px;flex-wrap:wrap; }
.ti-stat-pill { background:var(--card-bg);border:1px solid var(--border);border-radius:20px;padding:4px 12px;font-size:0.78rem;color:var(--text-muted); }
.ti-stat-pill strong { color:var(--text-dark);font-weight:700; }

.ti-grade-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px; }
.ti-grade-card { position:relative;padding:10px 12px;border:2px solid var(--border);border-radius:10px;cursor:pointer;transition:all .15s;background:var(--card-bg); }
.ti-grade-card:hover { border-color:var(--primary);background:var(--primary-light); }
.ti-grade-card.active { border-color:var(--primary);background:var(--primary-light);box-shadow:0 0 0 2px rgba(99,102,241,0.15); }
.ti-grade-card .grade-num { font-size:1.4rem;font-weight:700;color:var(--text-dark);line-height:1; }
.ti-grade-card.active .grade-num { color:var(--primary); }
.ti-grade-card .grade-meta { font-size:0.72rem;color:var(--text-muted);margin-top:4px; }
.ti-grade-card .grade-check { position:absolute;top:8px;right:8px;width:18px;height:18px;border-radius:50%;border:2px solid var(--border);background:var(--card-bg);transition:all .15s;display:flex;align-items:center;justify-content:center;font-size:9px;color:transparent; }
.ti-grade-card.active .grade-check { border-color:var(--primary);background:var(--primary);color:#fff; }

.ti-student-toolbar { display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 14px;border-bottom:1px solid var(--border);flex-wrap:wrap; }
.ti-student-toolbar-left { display:flex;align-items:center;gap:10px; }
.ti-student-count { font-size:0.78rem;color:var(--text-muted); }
.ti-student-count strong { color:var(--primary);font-weight:700; }
.ti-search { position:relative;width:220px; }
.ti-search-input { width:100%;border:1px solid var(--border);border-radius:8px;padding:5px 10px 5px 28px;font-size:0.82rem;font-family:var(--font); }
.ti-search-input:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 2px var(--primary-light); }
.ti-search-icon { position:absolute;left:8px;top:50%;transform:translateY(-50%);font-size:0.7rem;color:var(--text-muted); }

.ti-student-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:6px;max-height:480px;overflow-y:auto;padding:8px 14px; }
.ti-student-card { display:flex;align-items:center;gap:8px;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;transition:all .12s;background:var(--card-bg); }
.ti-student-card:hover { border-color:var(--primary);background:var(--primary-light); }
.ti-student-card.active { border-color:var(--primary);background:var(--primary-light); }
.ti-student-card.has-no-marks { opacity:0.5;cursor:not-allowed;background:#f9fafb; }
.ti-student-avatar { width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--primary-light),#e0e7ff);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;color:var(--primary);flex-shrink:0;overflow:hidden; }
.ti-student-info { flex:1;min-width:0; }
.ti-student-name { font-size:0.82rem;font-weight:600;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.ti-student-meta { font-size:0.7rem;color:var(--text-muted);display:flex;gap:6px;flex-wrap:wrap; }
.ti-student-check { width:18px;height:18px;accent-color:var(--primary); }

.ti-empty { display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px;color:var(--text-muted);grid-column:1/-1; }
.ti-empty i { font-size:28px;opacity:0.3;margin-bottom:8px; }

.ti-actions { display:flex;gap:8px;align-items:center;flex-wrap:wrap; }
.ti-btn-primary { background:var(--primary);color:#fff;border:none;padding:8px 18px;border-radius:8px;font-weight:600;font-size:0.85rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all .15s; }
.ti-btn-primary:hover { background:var(--primary-dark); }
.ti-btn-primary:disabled { background:var(--text-muted);cursor:not-allowed;opacity:0.6; }
.ti-btn-outline { background:transparent;color:var(--text);border:1px solid var(--border);padding:8px 14px;border-radius:8px;font-weight:600;font-size:0.85rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px; }
.ti-btn-outline:hover { background:var(--bg-hover); }
</style>
@endpush

@section('content')
<div class="modern-page">
    <div class="ti-page-header">
        <div class="ti-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb" style="margin:0;">
                <ol style="margin:0;">
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.certificate-generate.index') }}">Documents</a></li>
                    <li class="active">Academic Transcript</li>
                </ol>
            </nav>
            <span style="color:var(--border);">|</span>
            <h1 class="ti-page-title">Academic Transcript — Bulk Generate</h1>
        </div>
        <div class="ti-stats">
            <span class="ti-stat-pill"><strong>{{ $classes->count() }}</strong> classes</span>
            <span class="ti-stat-pill"><strong>{{ $classes->sum('students_count') }}</strong> students</span>
            <span class="ti-stat-pill"><strong id="selectedCount">0</strong> selected</span>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.transcript.bulk-generate') }}" id="transcriptForm" target="_blank">
        @csrf

        {{-- Step 1: Select Grades/Classes (multi-select) --}}
        <div class="modern-card" style="margin-bottom:12px;">
            <div class="ti-student-toolbar">
                <div class="ti-student-toolbar-left">
                    <span style="font-size:0.72rem;font-weight:700;color:var(--text-dark);text-transform:uppercase;letter-spacing:0.4px;">
                        <i class="fas fa-graduation-cap me-1"></i>Step 1: Select Classes / Grades
                    </span>
                </div>
                <div class="ti-actions">
                    <button type="button" class="ti-btn-outline" id="selectAllGrades" style="padding:4px 10px;font-size:0.78rem;">
                        <i class="fas fa-check-double"></i> Select All
                    </button>
                    <button type="button" class="ti-btn-outline" id="clearGrades" style="padding:4px 10px;font-size:0.78rem;">
                        <i class="fas fa-times-circle"></i> Clear
                    </button>
                </div>
            </div>
            <div style="padding:12px 14px;">
                <div class="ti-grade-grid" id="gradeGrid">
                    @foreach($classesByGrade as $gradeNum => $gradeClasses)
                        @php
                            $gradeStudentCount = $gradeClasses->sum('students_count');
                            $firstClass = $gradeClasses->first();
                        @endphp
                        @foreach($gradeClasses as $idx => $class)
                            <div class="ti-grade-card {{ $gradeClasses->count() === 1 ? 'active' : '' }}"
                                 data-class-id="{{ $class->id }}"
                                 data-grade="{{ $gradeNum }}"
                                 data-student-count="{{ $class->students_count }}"
                                 data-class-name="{{ $class->name }}"
                                 data-branch-name="{{ $class->branch?->name ?? '' }}"
                                 onclick="toggleClass({{ $class->id }})">
                                <div class="grade-check"><i class="fas fa-check"></i></div>
                                <div class="grade-num">Grade {{ $gradeNum }}</div>
                                <div class="grade-meta">
                                    {{ $class->name }}
                                    @if($class->branch) · {{ $class->branch->name }}@endif
                                    <br>{{ $class->students_count }} student(s)
                                </div>
                            </div>
                        @endforeach
                    @endforeach

                    @if($classes->isEmpty())
                        <div class="ti-empty">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>No classes found. Please create classes first.</p>
                        </div>
                    @endif
                </div>
                <input type="hidden" name="class_ids" id="selectedClassIds" value="">
            </div>
        </div>

        {{-- Step 2: Students (loaded dynamically via AJAX) --}}
        <div class="modern-card" style="margin-bottom:12px;">
            <div class="ti-student-toolbar">
                <div class="ti-student-toolbar-left">
                    <span style="font-size:0.72rem;font-weight:700;color:var(--text-dark);text-transform:uppercase;letter-spacing:0.4px;">
                        <i class="fas fa-users me-1"></i>Step 2: Select Students
                    </span>
                    <span class="ti-student-count"><strong id="visibleCount">0</strong> shown · <strong id="totalStudentCount">0</strong> total</span>
                </div>
                <div class="ti-actions">
                    <div class="ti-search">
                        <i class="fas fa-search ti-search-icon"></i>
                        <input type="text" id="studentSearch" class="ti-search-input" placeholder="Search students...">
                    </div>
                    <button type="button" class="ti-btn-outline" id="selectAllStudents" style="padding:4px 10px;font-size:0.78rem;">
                        <i class="fas fa-check-double"></i> Select All Visible
                    </button>
                    <button type="button" class="ti-btn-outline" id="clearStudents" style="padding:4px 10px;font-size:0.78rem;">
                        <i class="fas fa-times-circle"></i> Clear
                    </button>
                </div>
            </div>
            <div id="studentGrid" class="ti-student-grid">
                <div class="ti-empty">
                    <i class="fas fa-arrow-up"></i>
                    <p>Select classes above to load students</p>
                </div>
            </div>
        </div>

        {{-- Step 3: Actions --}}
        <div class="modern-card">
            <div class="ti-student-toolbar" style="border-bottom:none;">
                <div class="ti-student-toolbar-left">
                    <span style="font-size:0.85rem;font-weight:600;color:var(--text-dark);">
                        <i class="fas fa-scroll me-1 text-primary"></i>
                        Ready to generate <strong id="readyCount" class="text-primary">0</strong> transcript(s)
                    </span>
                </div>
                <div class="ti-actions">
                    <a href="{{ route('admin.certificate-generate.index') }}" class="ti-btn-outline">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="ti-btn-primary" id="generateBtn" disabled>
                        <i class="fas fa-scroll"></i> Generate Selected Transcripts
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function() {
    let selectedClassIds = new Set();
    let allStudents = [];
    let selectedStudentIds = new Set();
    let filteredStudents = [];

    const gradeGrid = document.getElementById('gradeGrid');
    const studentGrid = document.getElementById('studentGrid');
    const studentSearch = document.getElementById('studentSearch');
    const generateBtn = document.getElementById('generateBtn');
    const selectedCountEl = document.getElementById('selectedCount');
    const visibleCountEl = document.getElementById('visibleCount');
    const totalStudentCountEl = document.getElementById('totalStudentCount');
    const readyCountEl = document.getElementById('readyCount');

    // Pre-select all single-grade classes by default
    document.querySelectorAll('.ti-grade-card.active').forEach(card => {
        selectedClassIds.add(card.dataset.classId);
    });

    // If any pre-selected, load students for them
    if (selectedClassIds.size > 0) {
        loadStudents();
    }

    window.toggleClass = function(classId) {
        const card = document.querySelector(`.ti-grade-card[data-class-id="${classId}"]`);
        if (!card) return;

        if (selectedClassIds.has(classId)) {
            selectedClassIds.delete(classId);
            card.classList.remove('active');
        } else {
            selectedClassIds.add(classId);
            card.classList.add('active');
        }

        // Deselect students from classes no longer selected
        const studentsToRemove = allStudents.filter(s => s.class_id == classId);
        studentsToRemove.forEach(s => selectedStudentIds.delete(s.id));

        loadStudents();
    };

    document.getElementById('selectAllGrades')?.addEventListener('click', function() {
        document.querySelectorAll('.ti-grade-card').forEach(card => {
            selectedClassIds.add(card.dataset.classId);
            card.classList.add('active');
        });
        loadStudents();
    });

    document.getElementById('clearGrades')?.addEventListener('click', function() {
        selectedClassIds.clear();
        document.querySelectorAll('.ti-grade-card').forEach(card => card.classList.remove('active'));
        selectedStudentIds.clear();
        allStudents = [];
        renderStudents();
        updateUI();
    });

    function loadStudents() {
        if (selectedClassIds.size === 0) {
            allStudents = [];
            renderStudents();
            updateUI();
            return;
        }

        studentGrid.innerHTML = '<div class="ti-empty"><i class="fas fa-spinner fa-spin"></i><p>Loading students...</p></div>';

        const classIds = Array.from(selectedClassIds).join(',');
        fetch('{{ route("admin.transcript.students") }}?class_ids=' + classIds + '&include_marks_check=1')
            .then(r => { if (!r.ok) throw new Error('Network error'); return r.json(); })
            .then(data => {
                allStudents = Array.isArray(data) ? data : [];
                renderStudents();
                updateUI();
            })
            .catch(err => {
                studentGrid.innerHTML = '<div class="ti-empty"><i class="fas fa-exclamation-triangle"></i><p>Error loading students: ' + err.message + '</p></div>';
            });
    }

    function renderStudents() {
        filteredStudents = allStudents;

        // Apply search filter
        const q = studentSearch.value.toLowerCase().trim();
        if (q) {
            filteredStudents = allStudents.filter(s =>
                (s.full_name || '').toLowerCase().includes(q) ||
                (s.roll_number || '').toLowerCase().includes(q) ||
                (s.admission_number || '').toLowerCase().includes(q)
            );
        }

        if (filteredStudents.length === 0) {
            studentGrid.innerHTML = '<div class="ti-empty"><i class="fas fa-user-slash"></i><p>' + (allStudents.length === 0 ? 'Select classes above to load students' : 'No students match your search') + '</p></div>';
            return;
        }

        studentGrid.innerHTML = '';
        filteredStudents.forEach(s => {
            const card = document.createElement('div');
            const isSelected = selectedStudentIds.has(s.id);
            const hasMarks = s.has_grades_9_12_marks !== false;
            card.className = 'ti-student-card' + (isSelected ? ' active' : '') + (!hasMarks ? ' has-no-marks' : '');
            card.dataset.studentId = s.id;
            const initials = ((s.full_name || '?')[0]).toUpperCase();
            let avatarContent = initials;
            if (s.photo) avatarContent = '<img src="{{ asset("storage/") }}/' + s.photo + '" alt="" style="width:100%;height:100%;object-fit:cover;">';

            card.innerHTML = `
                <input type="checkbox" class="ti-student-check" name="student_ids[]" value="${s.id}" id="stu_${s.id}" ${isSelected ? 'checked' : ''} ${!hasMarks ? 'disabled' : ''}>
                <div class="ti-student-avatar">${avatarContent}</div>
                <div class="ti-student-info">
                    <div class="ti-student-name">${s.full_name}</div>
                    <div class="ti-student-meta">
                        ${s.roll_number ? '#' + s.roll_number : ''}
                        ${s.classroom ? '· ' + s.classroom.name : ''}
                        ${s.status === 'graduated' ? '· <span style="color:#7c3aed;font-weight:600;">Graduated</span>' : ''}
                        ${!hasMarks ? '· <span style="color:#dc2626;">no G9-12 marks</span>' : ''}
                    </div>
                </div>
            `;
            card.addEventListener('click', function(e) {
                if (e.target.tagName === 'INPUT') return;
                if (!hasMarks) return;
                const cb = card.querySelector('.ti-student-check');
                cb.checked = !cb.checked;
                cb.dispatchEvent(new Event('change'));
            });
            card.querySelector('.ti-student-check').addEventListener('change', function() {
                if (this.checked) {
                    selectedStudentIds.add(s.id);
                    card.classList.add('active');
                } else {
                    selectedStudentIds.delete(s.id);
                    card.classList.remove('active');
                }
                updateUI();
            });
            studentGrid.appendChild(card);
        });
    }

    document.getElementById('selectAllStudents')?.addEventListener('click', function() {
        filteredStudents.forEach(s => {
            if (s.has_grades_9_12_marks !== false) {
                selectedStudentIds.add(s.id);
            }
        });
        renderStudents();
        updateUI();
    });

    document.getElementById('clearStudents')?.addEventListener('click', function() {
        selectedStudentIds.clear();
        renderStudents();
        updateUI();
    });

    studentSearch?.addEventListener('input', renderStudents);

    function updateUI() {
        const selected = selectedStudentIds.size;
        selectedCountEl.textContent = selected;
        visibleCountEl.textContent = filteredStudents.length;
        totalStudentCountEl.textContent = allStudents.length;
        readyCountEl.textContent = selected;
        generateBtn.disabled = selected === 0;
    }

    document.getElementById('transcriptForm')?.addEventListener('submit', function(e) {
        if (selectedStudentIds.size === 0) {
            e.preventDefault();
            alert('Please select at least one student.');
            return;
        }
        // Confirmation
        if (!confirm('Generate transcripts for ' + selectedStudentIds.size + ' student(s)? This will create certificate records for each.')) {
            e.preventDefault();
        }
    });

    updateUI();
})();
</script>
@endpush
