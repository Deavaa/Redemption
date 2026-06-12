@extends('layouts.admin')
@section('title', 'Print on Pre-printed Certificate')

@section('content')
<div class="modern-page">
    <div class="modern-page-header" style="margin-bottom:0.75rem;">
        <div class="modern-page-header-left" style="display:flex;align-items:center;gap:10px;">
            <nav aria-label="breadcrumb" class="modern-breadcrumb" style="margin:0;">
                <ol style="margin:0;">
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li class="active">Print on Certificate</li>
                </ol>
            </nav>
            <span style="color:var(--border);font-size:0.65rem;">|</span>
            <h1 style="font-size:0.85rem;font-weight:700;color:var(--text-dark);margin:0;">Print on Pre-printed Certificate</h1>
        </div>
        <div class="modern-page-header-right">
            <button type="submit" class="btn-modern btn-modern-primary" id="printCertBtnTop" disabled form="certPrintForm" style="font-size:0.7rem;padding:4px 12px;">
                <i class="fas fa-print"></i> Print Certificate
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.certificate-print.print') }}" id="certPrintForm" target="_blank">
        @csrf

        {{-- Step 1: Academic Year --}}
        <div class="modern-card" style="margin-bottom:10px;">
            <div class="certgen-toolbar">
                <span class="certgen-toolbar-label">Academic Year</span>
            </div>
            <div style="padding:8px 14px;">
                <select name="academic_year_id" id="academicYearSelect" class="modern-input" style="max-width:300px;font-size:0.8rem;">
                    <option value="">Current / Active Year</option>
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ $ay->is_current ? 'selected' : '' }}>{{ $ay->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Step 2: Select Class --}}
        <div class="modern-card" style="margin-bottom:10px;">
            <div class="certgen-toolbar">
                <span class="certgen-toolbar-label">Class</span>
            </div>
            <div style="padding:8px 14px;">
                <div class="gen-class-grid" id="classGrid">
                    @foreach($classes as $c)
                        <button type="button" class="gen-class-card" data-class-id="{{ $c->id }}" data-numeric="{{ $c->numeric_name }}">
                            <i class="fas fa-chalkboard"></i>
                            <span>{{ $c->name }}</span>
                        </button>
                    @endforeach
                </div>
                <input type="hidden" name="class_id" id="selectedClassId" value="">
            </div>
        </div>

        {{-- Step 3: Select Student --}}
        <div class="modern-card" style="margin-bottom:10px;">
            <div class="certgen-toolbar">
                <span class="certgen-toolbar-label">Student</span>
                <div class="gen-search-box">
                    <i class="fas fa-search gen-search-icon"></i>
                    <input type="text" id="studentSearch" class="gen-search-input" placeholder="Search student...">
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

        {{-- Step 4: Certificate Template Type --}}
        <div class="modern-card" style="margin-bottom:10px;">
            <div class="certgen-toolbar">
                <span class="certgen-toolbar-label">Certificate Template</span>
                <span id="autoDetectedLabel" style="font-size:0.65rem;color:var(--text-muted);margin-left:auto;">Auto-detected from student class</span>
            </div>
            <div style="padding:8px 14px;">
                <div class="gen-type-grid" id="templateTypeGrid">
                    @foreach($templateTypes as $key => $tpl)
                        <label class="gen-type-card" data-template="{{ $key }}" data-grades="{{ implode(',', $tpl['grades']) }}" data-stream="{{ $tpl['stream'] ?? '' }}">
                            <input type="radio" name="template_type" value="{{ $key }}" {{ $key === 'g3-6' ? 'checked' : '' }}>
                            <div class="gen-type-card-inner">
                                <div class="gen-type-icon" style="background:rgba(16,185,129,0.12);color:#10b981;">
                                    <i class="fas fa-print"></i>
                                </div>
                                <div class="gen-type-info">
                                    <h4>{{ $tpl['label'] }}</h4>
                                    <p>Grades: {{ implode(', ', $tpl['grades']) }}@if(!empty($tpl['stream'])) &mdash; {{ ucfirst($tpl['stream']) }} @endif</p>
                                </div>
                                <div class="gen-type-check"><i class="fas fa-check-circle"></i></div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Step 5: Stream selector for Grade 11-12 --}}
        <div class="modern-card" id="streamSelector" style="margin-bottom:10px;display:none;">
            <div class="certgen-toolbar">
                <span class="certgen-toolbar-label">Stream</span>
                <span style="font-size:0.65rem;color:var(--text-muted);margin-left:auto;">Select the stream for Grade 11-12</span>
            </div>
            <div style="padding:8px 14px;display:flex;gap:10px;">
                <label class="gen-type-card" style="flex:1;">
                    <input type="radio" name="stream_override" value="natural">
                    <div class="gen-type-card-inner">
                        <div class="gen-type-icon" style="background:rgba(59,130,246,0.12);color:#3b82f6;">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="gen-type-info">
                            <h4>Natural Science</h4>
                            <p>Physics, Chemistry, Biology, Mathematics</p>
                        </div>
                        <div class="gen-type-check"><i class="fas fa-check-circle"></i></div>
                    </div>
                </label>
                <label class="gen-type-card" style="flex:1;">
                    <input type="radio" name="stream_override" value="social">
                    <div class="gen-type-card-inner">
                        <div class="gen-type-icon" style="background:rgba(245,158,11,0.12);color:#f59e0b;">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <div class="gen-type-info">
                            <h4>Social Science</h4>
                            <p>History, Geography, Civics, Economics</p>
                        </div>
                        <div class="gen-type-check"><i class="fas fa-check-circle"></i></div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Actions --}}
        <div class="modern-card">
            <div class="modern-form-actions">
                <a href="{{ route('admin.dashboard') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" class="btn-modern btn-modern-primary" id="printCertBtn" disabled>
                    <i class="fas fa-print"></i> Print Certificate
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.gen-class-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(100px,1fr)); gap:6px; }
.gen-class-card {
    display:flex; flex-direction:column; align-items:center; gap:4px;
    padding:8px 6px; border:1.5px solid var(--border); border-radius:8px;
    background:var(--bg-card); cursor:pointer; transition:all .15s;
    font-size:0.72rem; color:var(--text-dark);
}
.gen-class-card:hover { border-color:var(--primary); background:var(--primary-light); }
.gen-class-card.active { border-color:var(--primary); background:var(--primary-light); color:var(--primary); font-weight:600; }
.gen-class-card i { font-size:1rem; }

.gen-student-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:6px; max-height:260px; overflow-y:auto; }
.gen-student-card {
    display:flex; align-items:center; gap:8px; padding:6px 10px;
    border:1.5px solid var(--border); border-radius:8px; cursor:pointer;
    transition:all .15s; font-size:0.75rem;
}
.gen-student-card:hover { border-color:var(--primary); }
.gen-student-card.active { border-color:var(--primary); background:var(--primary-light); }
.gen-student-avatar {
    width:28px; height:28px; border-radius:50%; display:flex;
    align-items:center; justify-content:center; font-size:0.7rem;
    font-weight:600; color:#fff; flex-shrink:0;
}
.gen-student-info { flex:1; min-width:0; }
.gen-student-info .name { font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.gen-student-info .meta { font-size:0.65rem; color:var(--text-muted); }

.gen-type-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:8px; }
.gen-type-card { cursor:pointer; }
.gen-type-card input[type="radio"] { display:none; }
.gen-type-card-inner {
    display:flex; align-items:center; gap:10px; padding:10px 12px;
    border:1.5px solid var(--border); border-radius:10px; transition:all .15s;
}
.gen-type-card:hover .gen-type-card-inner { border-color:var(--primary); }
.gen-type-card input:checked + .gen-type-card-inner { border-color:var(--primary); background:var(--primary-light); }
.gen-type-icon {
    width:36px; height:36px; border-radius:8px; display:flex;
    align-items:center; justify-content:center; font-size:1rem; flex-shrink:0;
}
.gen-type-info { flex:1; min-width:0; }
.gen-type-info h4 { font-size:0.78rem; font-weight:600; margin:0 0 2px; color:var(--text-dark); }
.gen-type-info p { font-size:0.65rem; color:var(--text-muted); margin:0; }
.gen-type-check { color:var(--primary); font-size:1rem; opacity:0; transition:opacity .15s; }
.gen-type-card input:checked + .gen-type-card-inner .gen-type-check { opacity:1; }

.certgen-toolbar {
    display:flex; align-items:center; gap:8px; padding:6px 14px;
    border-bottom:1px solid var(--border); background:var(--bg-card-header, rgba(0,0,0,.02));
}
.certgen-toolbar-label { font-size:0.72rem; font-weight:700; color:var(--text-dark); }
.gen-search-box { position:relative; margin-left:auto; }
.gen-search-icon { position:absolute; left:8px; top:50%; transform:translateY(-50%); font-size:0.7rem; color:var(--text-muted); }
.gen-search-input {
    padding:4px 8px 4px 28px; border:1px solid var(--border); border-radius:6px;
    font-size:0.72rem; width:180px; outline:none;
}
.gen-search-input:focus { border-color:var(--primary); }

.gen-empty-state { grid-column:1/-1; text-align:center; padding:30px 10px; color:var(--text-muted); }
.gen-empty-state i { font-size:1.5rem; margin-bottom:6px; display:block; }
.gen-empty-state p { font-size:0.75rem; margin:0; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classGrid = document.getElementById('classGrid');
    const studentContainer = document.getElementById('studentListContainer');
    const studentSearch = document.getElementById('studentSearch');
    const previewCard = document.getElementById('studentPreviewCard');
    const streamSelector = document.getElementById('streamSelector');
    const autoLabel = document.getElementById('autoDetectedLabel');
    let allStudents = [];
    let selectedNumericGrade = null;

    // ---- Class selection ----
    classGrid.addEventListener('click', function(e) {
        const btn = e.target.closest('.gen-class-card');
        if (!btn) return;

        document.querySelectorAll('.gen-class-card').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const classId = btn.dataset.classId;
        selectedNumericGrade = parseInt(btn.dataset.numeric) || 0;
        document.getElementById('selectedClassId').value = classId;

        // Auto-detect template type
        autoDetectTemplate(selectedNumericGrade);

        // Show/hide stream selector for grade 11-12
        if (selectedNumericGrade >= 11) {
            streamSelector.style.display = '';
        } else {
            streamSelector.style.display = 'none';
        }

        loadStudents(classId);
    });

    // ---- Load students ----
    function loadStudents(classId) {
        const ayId = document.getElementById('academicYearSelect').value;
        const url = '{{ route("admin.certificate-print.students") }}?class_id=' + classId + (ayId ? '&academic_year_id=' + ayId : '');

        fetch(url)
            .then(r => r.json())
            .then(data => {
                allStudents = data;
                renderStudents(data);
            })
            .catch(() => {
                studentContainer.innerHTML = '<div class="gen-empty-state"><i class="fas fa-exclamation-triangle"></i><p>Failed to load students</p></div>';
            });
    }

    function renderStudents(students) {
        if (!students.length) {
            studentContainer.innerHTML = '<div class="gen-empty-state"><i class="fas fa-user-slash"></i><p>No students found</p></div>';
            return;
        }
        studentContainer.innerHTML = students.map(s => {
            const initials = (s.full_name || '?').split(' ').map(w => w[0]).join('').substring(0,2).toUpperCase();
            const colors = ['#6366f1','#8b5cf6','#ec4899','#f43f5e','#f97316','#eab308','#22c55e','#14b8a6','#3b82f6'];
            const color = colors[s.id % colors.length];
            return `<div class="gen-student-card" data-id="${s.id}" data-name="${(s.full_name||'').toLowerCase()}">
                <div class="gen-student-avatar" style="background:${color}">${initials}</div>
                <div class="gen-student-info">
                    <div class="name">${s.full_name || '-'}</div>
                    <div class="meta">${s.roll_number || ''} &middot; ${s.classroom?.name || ''} &middot; ${s.section?.name || ''}</div>
                </div>
            </div>`;
        }).join('');

        studentContainer.querySelectorAll('.gen-student-card').forEach(card => {
            card.addEventListener('click', () => selectStudent(card));
        });
    }

    // ---- Student search ----
    studentSearch.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        const filtered = allStudents.filter(s => (s.full_name||'').toLowerCase().includes(q) || (s.roll_number||'').toLowerCase().includes(q));
        renderStudents(filtered);
    });

    // ---- Select student ----
    function selectStudent(card) {
        document.querySelectorAll('.gen-student-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');

        const id = card.dataset.id;
        document.getElementById('selectedStudentId').value = id;

        // Update preview
        const student = allStudents.find(s => s.id == id);
        if (student) {
            document.getElementById('previewName').textContent = student.full_name || '-';
            document.getElementById('previewRoll').textContent = student.roll_number || '-';
            document.getElementById('previewClass').textContent = student.classroom?.name || '-';
            document.getElementById('previewSection').textContent = student.section?.name || '-';
            const initials = (student.full_name || '?').split(' ').map(w => w[0]).join('').substring(0,2).toUpperCase();
            document.getElementById('previewAvatar').textContent = initials;
            previewCard.style.display = '';
        }

        enableButtons();
    }

    // ---- Remove student ----
    document.getElementById('removeStudent').addEventListener('click', function() {
        document.querySelectorAll('.gen-student-card').forEach(c => c.classList.remove('active'));
        document.getElementById('selectedStudentId').value = '';
        previewCard.style.display = 'none';
        disableButtons();
    });

    // ---- Auto-detect template type ----
    function autoDetectTemplate(numericGrade) {
        const gradeToTemplate = {
            0: 'kg',
            1: 'g1-2', 2: 'g1-2',
            3: 'g3-6', 4: 'g3-6', 5: 'g3-6', 6: 'g3-6',
            7: 'g7-8', 8: 'g7-8',
            9: 'g9-10', 10: 'g9-10',
            11: 'g11-12-nat', 12: 'g11-12-nat',
        };

        const template = gradeToTemplate[numericGrade] || 'g3-6';
        const radio = document.querySelector(`input[name="template_type"][value="${template}"]`);
        if (radio) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        }

        autoLabel.textContent = `Auto-detected: ${numericGrade === 0 ? 'KG' : 'Grade ' + numericGrade}`;
    }

    // ---- Stream override for 11-12 ----
    document.querySelectorAll('input[name="stream_override"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'natural') {
                const nat = document.querySelector('input[name="template_type"][value="g11-12-nat"]');
                if (nat) { nat.checked = true; nat.dispatchEvent(new Event('change')); }
            } else if (this.value === 'social') {
                const soc = document.querySelector('input[name="template_type"][value="g11-12-social"]');
                if (soc) { soc.checked = true; soc.dispatchEvent(new Event('change')); }
            }
        });
    });

    // ---- Button state ----
    function enableButtons() {
        document.getElementById('printCertBtn').disabled = false;
        document.getElementById('printCertBtnTop').disabled = false;
    }
    function disableButtons() {
        document.getElementById('printCertBtn').disabled = true;
        document.getElementById('printCertBtnTop').disabled = true;
    }

    // ---- Form submit: add template_type from selected radio ----
    document.getElementById('certPrintForm').addEventListener('submit', function() {
        // template_type is already a radio input in the form, so it submits automatically
    });
});
</script>
@endsection
