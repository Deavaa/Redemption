@extends('layouts.admin')
@section('title', 'Academic Transcript')

@section('content')
<div class="modern-page">
    <div class="modern-page-header" style="margin-bottom:0.75rem;">
        <div class="modern-page-header-left" style="display:flex;align-items:center;gap:10px;">
            <nav aria-label="breadcrumb" class="modern-breadcrumb" style="margin:0;">
                <ol style="margin:0;">
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.certificate-generate.index') }}">Documents</a></li>
                    <li class="active">Academic Transcript</li>
                </ol>
            </nav>
            <span style="color:var(--border);font-size:0.65rem;">|</span>
            <h1 style="font-size:0.85rem;font-weight:700;color:var(--text-dark);margin:0;">Academic Transcript</h1>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.certificate-generate.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.7rem;padding:4px 10px;"><i class="fas fa-arrow-left"></i> Back</a>
            <button type="submit" class="btn-modern btn-modern-primary" id="generateBtnTop" disabled form="transcriptForm" style="font-size:0.7rem;padding:4px 12px;">
                <i class="fas fa-scroll"></i> Generate Transcript
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.transcript.generate') }}" target="_blank" id="transcriptForm">
        @csrf

        {{-- Step 1: Select Class --}}
        <div class="modern-card" style="margin-bottom:10px;">
            <div class="certgen-toolbar">
                <span class="certgen-toolbar-label">Select Class</span>
            </div>
            <div style="padding:8px 14px;">
                <div class="gen-class-grid" id="classGrid">
                    <button type="button" class="gen-class-card" data-class-id="">
                        <i class="fas fa-th-large"></i>
                        <span>All</span>
                    </button>
                    @foreach($classes as $c)
                        <button type="button" class="gen-class-card" data-class-id="{{ $c->id }}">
                            <i class="fas fa-chalkboard"></i>
                            <span>{{ $c->name }}</span>
                        </button>
                    @endforeach
                </div>
                <input type="hidden" name="class_id" id="selectedClassId" value="">
            </div>
        </div>

        {{-- Step 2: Select Student --}}
        <div class="modern-card" style="margin-bottom:10px;">
            <div class="certgen-toolbar">
                <span class="certgen-toolbar-label">Select Student</span>
                <div class="gen-search-box">
                    <i class="fas fa-search gen-search-icon"></i>
                    <input type="text" id="studentSearch" class="gen-search-input" placeholder="Search students...">
                </div>
            </div>
            <div style="padding:8px 14px;">
                <div id="studentListContainer" class="gen-student-grid">
                    <div class="gen-empty-state">
                        <i class="fas fa-users"></i>
                        <p>Select a class to load students</p>
                    </div>
                </div>
                <input type="hidden" name="student_id" id="selectedStudentId" value="">
            </div>
        </div>

        {{-- Selected Student Preview --}}
        <div class="modern-card gen-preview-card" id="studentPreviewCard" style="margin-bottom:10px;display:none;">
            <div class="gen-preview-body" style="padding:10px 14px;">
                <div class="gen-preview-avatar" id="previewAvatar" style="width:36px;height:36px;font-size:14px;">?</div>
                <div class="gen-preview-info">
                    <h4 id="previewName" style="font-size:13px;">-</h4>
                    <div class="gen-preview-details" style="gap:10px;">
                        <span style="font-size:10px;"><i class="fas fa-hashtag" style="font-size:8px;"></i> <span id="previewRoll">-</span></span>
                        <span style="font-size:10px;"><i class="fas fa-building" style="font-size:8px;"></i> <span id="previewClass">-</span></span>
                        <span style="font-size:10px;"><i class="fas fa-layer-group" style="font-size:8px;"></i> <span id="previewSection">-</span></span>
                    </div>
                </div>
                <button type="button" class="gen-preview-remove" id="removeStudent" style="width:26px;height:26px;font-size:10px;" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Actions --}}
        <div class="modern-card">
            <div class="modern-form-actions">
                <a href="{{ route('admin.certificate-generate.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" class="btn-modern btn-modern-primary" id="generateBtn" disabled>
                    <i class="fas fa-scroll"></i> Generate Transcript
                </button>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
.certgen-toolbar { display:flex;align-items:center;justify-content:space-between;padding:6px 14px;border-bottom:1px solid var(--border);gap:8px;flex-wrap:wrap; }
.certgen-toolbar-label { font-size:10px;font-weight:700;color:var(--text-dark);text-transform:uppercase;letter-spacing:0.3px; }
.gen-class-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:6px; }
.gen-class-card { display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px 6px;border:1.5px solid var(--border);border-radius:var(--radius);background:var(--card-bg);cursor:pointer;transition:var(--transition);font-family:var(--font);font-size:11px;font-weight:600;color:var(--text); }
.gen-class-card i { font-size:16px;color:var(--text-muted);transition:var(--transition); }
.gen-class-card:hover { border-color:var(--primary);background:var(--primary-light);color:var(--primary); }
.gen-class-card:hover i { color:var(--primary); }
.gen-class-card.active { border-color:var(--primary);background:var(--primary-light);color:var(--primary);box-shadow:0 0 0 2px rgba(99,102,241,0.12); }
.gen-class-card.active i { color:var(--primary); }
.gen-search-box { position:relative;width:180px; }
.gen-search-icon { position:absolute;left:8px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:10px; }
.gen-search-input { width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:4px 8px 4px 26px;font-size:11px;font-family:var(--font);color:var(--text-dark);background:var(--card-bg);transition:var(--transition); }
.gen-search-input:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 2px var(--primary-light); }
.gen-student-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:6px;max-height:280px;overflow-y:auto;scrollbar-width:thin; }
.gen-student-card { display:flex;align-items:center;gap:8px;padding:7px 10px;border:1.5px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;transition:var(--transition);background:var(--card-bg); }
.gen-student-card:hover { border-color:var(--primary);background:var(--primary-light); }
.gen-student-card.active { border-color:var(--primary);background:var(--primary-light);box-shadow:0 0 0 2px rgba(99,102,241,0.1); }
.gen-student-avatar { width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--primary-light),#e0e7ff);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:10px;color:var(--primary);flex-shrink:0;overflow:hidden; }
.gen-student-avatar img { width:100%;height:100%;object-fit:cover; }
.gen-student-info { flex:1;min-width:0; }
.gen-student-name { font-size:11px;font-weight:600;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.gen-student-meta { font-size:9px;color:var(--text-muted); }
.gen-student-check { width:18px;height:18px;border-radius:50%;border:2px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:var(--transition);font-size:9px;color:transparent; }
.gen-student-card.active .gen-student-check { border-color:var(--primary);background:var(--primary);color:#fff; }
.gen-empty-state { display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;color:var(--text-muted);grid-column:1/-1; }
.gen-empty-state i { font-size:24px;opacity:0.3;margin-bottom:6px; }
.gen-empty-state p { font-size:12px; }
.gen-preview-card { border-color:var(--success)!important;box-shadow:0 0 0 2px rgba(16,185,129,0.08)!important; }
.gen-preview-body { display:flex;align-items:center;gap:10px; }
.gen-preview-avatar { border-radius:50%;background:linear-gradient(135deg,var(--success),#34d399);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0; }
.gen-preview-info { flex:1;min-width:0; }
.gen-preview-info h4 { font-weight:700;color:var(--text-dark);margin:0 0 2px; }
.gen-preview-details { display:flex;gap:10px;flex-wrap:wrap; }
.gen-preview-details span { color:var(--text-muted);display:flex;align-items:center;gap:3px; }
.gen-preview-details span i { color:var(--success); }
.gen-preview-remove { border-radius:50%;border:1px solid var(--border);background:var(--card-bg);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-muted);transition:var(--transition);flex-shrink:0; }
.gen-preview-remove:hover { background:var(--danger-light);border-color:var(--danger);color:var(--danger); }
.modern-page-header-right { display:flex;align-items:center;gap:6px;flex-shrink:0; }
</style>
@endpush

@push('scripts')
<script>
(function() {
    const classGrid = document.getElementById('classGrid');
    const classInput = document.getElementById('selectedClassId');
    const studentContainer = document.getElementById('studentListContainer');
    const studentInput = document.getElementById('selectedStudentId');
    const studentSearch = document.getElementById('studentSearch');
    const previewCard = document.getElementById('studentPreviewCard');
    const previewAvatar = document.getElementById('previewAvatar');
    const previewName = document.getElementById('previewName');
    const previewRoll = document.getElementById('previewRoll');
    const previewClass = document.getElementById('previewClass');
    const previewSection = document.getElementById('previewSection');
    const removeStudentBtn = document.getElementById('removeStudent');
    const genBtn = document.getElementById('generateBtn');
    const genBtnTop = document.getElementById('generateBtnTop');

    let allStudents = [];
    let selectedClassId = '';
    let selectedStudentId = '';

    classGrid?.addEventListener('click', function(e) {
        const card = e.target.closest('.gen-class-card');
        if (!card) return;
        classGrid.querySelectorAll('.gen-class-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        selectedClassId = card.dataset.classId;
        classInput.value = selectedClassId;
        clearStudentSelection();
        loadStudents();
    });

    function loadStudents() {
        studentContainer.innerHTML = '<div class="gen-empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>';
        const params = new URLSearchParams();
        if (selectedClassId) params.set('class_id', selectedClassId);
        fetch('{{ route("admin.transcript.students") }}?' + params)
            .then(r => { if (!r.ok) throw new Error('Network error'); return r.json(); })
            .then(data => { allStudents = Array.isArray(data) ? data : []; renderStudents(allStudents); })
            .catch(err => { studentContainer.innerHTML = '<div class="gen-empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading students</p></div>'; });
    }

    function renderStudents(students) {
        if (!students.length) { studentContainer.innerHTML = '<div class="gen-empty-state"><i class="fas fa-user-slash"></i><p>No students found</p></div>'; return; }
        studentContainer.innerHTML = '';
        students.forEach(s => {
            const card = document.createElement('div');
            card.className = 'gen-student-card' + (selectedStudentId == s.id ? ' active' : '');
            card.dataset.studentId = s.id;
            const initials = ((s.first_name || '?')[0] + (s.last_name || '?')[0]).toUpperCase();
            let avatarContent = initials;
            if (s.photo) avatarContent = '<img src="{{ asset("storage/") }}/' + s.photo + '" alt="">';
            card.innerHTML = `<div class="gen-student-avatar">${avatarContent}</div><div class="gen-student-info"><div class="gen-student-name">${s.first_name} ${s.last_name}</div><div class="gen-student-meta">${s.roll_number ? '#' + s.roll_number : ''} ${s.classroom ? s.classroom.name : ''}</div></div><div class="gen-student-check"><i class="fas fa-check"></i></div>`;
            card.addEventListener('click', () => selectStudent(s, card));
            studentContainer.appendChild(card);
        });
    }

    function selectStudent(student, card) {
        studentContainer.querySelectorAll('.gen-student-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        selectedStudentId = student.id;
        studentInput.value = student.id;
        genBtn.disabled = false;
        if (genBtnTop) genBtnTop.disabled = false;
        const initials = ((student.first_name || '?')[0] + (student.last_name || '?')[0]).toUpperCase();
        previewAvatar.textContent = student.photo ? '' : initials;
        previewName.textContent = student.first_name + ' ' + student.last_name;
        previewRoll.textContent = student.roll_number || '-';
        previewClass.textContent = student.classroom?.name || '-';
        previewSection.textContent = student.section?.name || '-';
        previewCard.style.display = '';
    }

    function clearStudentSelection() {
        selectedStudentId = '';
        studentInput.value = '';
        previewCard.style.display = 'none';
        studentContainer.querySelectorAll('.gen-student-card').forEach(c => c.classList.remove('active'));
        genBtn.disabled = true;
        if (genBtnTop) genBtnTop.disabled = true;
    }

    removeStudentBtn?.addEventListener('click', clearStudentSelection);

    studentSearch?.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        if (!q) { renderStudents(allStudents); return; }
        const filtered = allStudents.filter(s => (s.first_name + ' ' + s.last_name).toLowerCase().includes(q) || (s.roll_number || '').toLowerCase().includes(q) || (s.admission_number || '').toLowerCase().includes(q));
        renderStudents(filtered);
    });

    document.getElementById('transcriptForm')?.addEventListener('submit', function(e) {
        if (!selectedStudentId) { e.preventDefault(); alert('Please select a student first'); }
    });
})();
</script>
@endpush
@endsection
