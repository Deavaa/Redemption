@extends('layouts.admin')
@section('title', __('app.generate') . ' ' . __('app.certificates'))

@section('content')
<div class="modern-page">
    <div class="modern-page-header" style="margin-bottom:0.75rem;">
        <div class="modern-page-header-left" style="display:flex;align-items:center;gap:10px;">
            <nav aria-label="breadcrumb" class="modern-breadcrumb" style="margin:0;">
                <ol style="margin:0;">
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.certificates.index') }}">{{ __('app.certificates') }}</a></li>
                    <li class="active">{{ __('app.generate') }}</li>
                </ol>
            </nav>
            <span style="color:var(--border);font-size:0.65rem;">|</span>
            <h1 style="font-size:0.85rem;font-weight:700;color:var(--text-dark);margin:0;">{{ __('app.generate') }} {{ __('app.certificates') }}</h1>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.certificates.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.7rem;padding:4px 10px;"><i class="fas fa-arrow-left"></i> {{ __('app.cancel') }}</a>
            <button type="submit" class="btn-modern btn-modern-primary" id="generateCertBtnTop" disabled form="certGenForm" style="font-size:0.7rem;padding:4px 12px;">
                <i class="fas fa-certificate"></i> {{ __('app.generate') }}
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.certificate-generate.generate') }}" target="_blank" id="certGenForm">
        @csrf

        {{-- Step 1: Select Class --}}
        <div class="modern-card" style="margin-bottom:10px;">
            <div class="certgen-toolbar">
                <span class="certgen-toolbar-label">{{ __('app.classes') }}</span>
            </div>
            <div style="padding:8px 14px;">
                <div class="gen-class-grid" id="classGrid">
                    <button type="button" class="gen-class-card" data-class-id="">
                        <i class="fas fa-th-large"></i>
                        <span>{{ __('app.all_classes') ?? 'All' }}</span>
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
                <span class="certgen-toolbar-label">{{ __('app.students') }}</span>
                <div class="gen-search-box">
                    <i class="fas fa-search gen-search-icon"></i>
                    <input type="text" id="studentSearch" class="gen-search-input" placeholder="{{ __('app.search') }}">
                </div>
            </div>
            <div style="padding:8px 14px;">
                <div id="studentListContainer" class="gen-student-grid">
                    <div class="gen-empty-state">
                        <i class="fas fa-users"></i>
                        <p>{{ __('app.select_class_to_load') ?? 'Select a class to load students' }}</p>
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

        {{-- Step 3: Certificate Type --}}
        <div class="modern-card" style="margin-bottom:10px;">
            <div class="certgen-toolbar">
                <span class="certgen-toolbar-label">{{ __('app.cert_type') ?? 'Certificate Type' }}</span>
            </div>
            <div style="padding:8px 14px;">
                <div class="gen-type-grid">
                    <label class="gen-type-card">
                        <input type="radio" name="type" value="academic" checked>
                        <div class="gen-type-card-inner">
                            <div class="gen-type-icon gen-type-icon-purple">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="gen-type-info">
                                <h4>{{ __('app.cert_academic') ?? 'Academic Certificate' }}</h4>
                                <p>{{ __('app.cert_academic_desc') ?? 'Full academic record with marks and grades' }}</p>
                            </div>
                            <div class="gen-type-check"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </label>
                    <label class="gen-type-card">
                        <input type="radio" name="type" value="completion">
                        <div class="gen-type-card-inner">
                            <div class="gen-type-icon gen-type-icon-green">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div class="gen-type-info">
                                <h4>{{ __('app.cert_completion') ?? 'Completion Certificate' }}</h4>
                                <p>{{ __('app.cert_completion_desc') ?? 'Certifies successful program completion' }}</p>
                            </div>
                            <div class="gen-type-check"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </label>
                    <label class="gen-type-card">
                        <input type="radio" name="type" value="transfer">
                        <div class="gen-type-card-inner">
                            <div class="gen-type-icon gen-type-icon-blue">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="gen-type-info">
                                <h4>{{ __('app.cert_transfer') ?? 'Transfer Certificate' }}</h4>
                                <p>{{ __('app.cert_transfer_desc') ?? 'Official transfer documentation' }}</p>
                            </div>
                            <div class="gen-type-check"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </label>
                    <label class="gen-type-card">
                        <input type="radio" name="type" value="character">
                        <div class="gen-type-card-inner">
                            <div class="gen-type-icon gen-type-icon-gold">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="gen-type-info">
                                <h4>{{ __('app.cert_character') ?? 'Character Certificate' }}</h4>
                                <p>{{ __('app.cert_character_desc') ?? 'Certifies good character and conduct' }}</p>
                            </div>
                            <div class="gen-type-check"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </label>
                    <label class="gen-type-card">
                        <input type="radio" name="type" value="foldable">
                        <div class="gen-type-card-inner">
                            <div class="gen-type-icon gen-type-icon-teal">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="gen-type-info">
                                <h4>{{ __('app.cert_foldable') ?? 'Foldable Certificate (Report Card)' }}</h4>
                                <p>{{ __('app.cert_foldable_desc') ?? 'Comprehensive report card with marks and grades' }}</p>
                            </div>
                            <div class="gen-type-check"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </label>
                    <label class="gen-type-card">
                        <input type="radio" name="type" value="transcript">
                        <div class="gen-type-card-inner">
                            <div class="gen-type-icon" style="background:rgba(139,92,246,0.12);color:#8b5cf6;">
                                <i class="fas fa-scroll"></i>
                            </div>
                            <div class="gen-type-info">
                                <h4>Academic Transcript</h4>
                                <p>Complete academic record from enrollment to date with all terms and years</p>
                            </div>
                            <div class="gen-type-check"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </label>
                    <label class="gen-type-card">
                        <input type="radio" name="type" value="leaving_certificate">
                        <div class="gen-type-card-inner">
                            <div class="gen-type-icon" style="background:rgba(220,38,38,0.12);color:#dc2626;">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <div class="gen-type-info">
                                <h4>Leaving Clearance Certificate</h4>
                                <p>Official school leaving certificate with clearance checklist and academic summary</p>
                            </div>
                            <div class="gen-type-check"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="modern-card">
            <div class="modern-form-actions">
                <a href="{{ route('admin.certificates.index') }}" class="btn-modern btn-modern-ghost">{{ __('app.cancel') }}</a>
                <button type="submit" class="btn-modern btn-modern-primary" id="generateCertBtn">
                    <i class="fas fa-certificate"></i> {{ __('app.generate') }} {{ __('app.certificates') }}
                </button>
            </div>
        </div>

        {{-- Sticky Top Generate Bar --}}
        <div class="certgen-sticky-bar" id="certGenStickyBar" style="display:none;">
            <div class="certgen-sticky-bar-left">
                <i class="fas fa-certificate"></i>
                <span id="certStickyName">{{ __('app.certificates') }}</span>
            </div>
            <div class="certgen-sticky-bar-right">
                <button type="submit" class="btn-modern btn-modern-primary" id="generateCertBtnSticky" disabled form="certGenForm">
                    <i class="fas fa-certificate"></i> {{ __('app.generate') }} {{ __('app.certificates') }}
                </button>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
/* ===== Certificate Generate - Compact Layout ===== */
.certgen-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 6px 14px; border-bottom: 1px solid var(--border);
    gap: 8px; flex-wrap: wrap;
}
.certgen-toolbar-label {
    font-size: 10px; font-weight: 700; color: var(--text-dark);
    text-transform: uppercase; letter-spacing: 0.3px;
}
.gen-step-desc { font-size: 9px; color: var(--text-muted); margin: 1px 0 0; }

/* Class Selection Grid */
.gen-class-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 6px;
}
.gen-class-card {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 8px 6px; border: 1.5px solid var(--border); border-radius: var(--radius);
    background: var(--card-bg); cursor: pointer; transition: var(--transition);
    font-family: var(--font); font-size: 11px; font-weight: 600; color: var(--text);
}
.gen-class-card i { font-size: 16px; color: var(--text-muted); transition: var(--transition); }
.gen-class-card:hover { border-color: var(--primary); background: var(--primary-light); color: var(--primary); }
.gen-class-card:hover i { color: var(--primary); }
.gen-class-card.active {
    border-color: var(--primary); background: var(--primary-light); color: var(--primary);
    box-shadow: 0 0 0 2px rgba(99,102,241,0.12);
}
.gen-class-card.active i { color: var(--primary); }

/* Search */
.gen-search-box { position: relative; width: 180px; }
.gen-search-icon { position: absolute; left: 8px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 10px; }
.gen-search-input {
    width: 100%; border: 1px solid var(--border); border-radius: var(--radius-sm);
    padding: 4px 8px 4px 26px; font-size: 11px; font-family: var(--font);
    color: var(--text-dark); background: var(--card-bg); transition: var(--transition);
}
.gen-search-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 2px var(--primary-light); }

/* Student Grid */
.gen-student-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 6px; max-height: 280px; overflow-y: auto; scrollbar-width: thin;
}
.gen-student-card {
    display: flex; align-items: center; gap: 8px; padding: 7px 10px;
    border: 1.5px solid var(--border); border-radius: var(--radius-sm);
    cursor: pointer; transition: var(--transition); background: var(--card-bg);
}
.gen-student-card:hover { border-color: var(--primary); background: var(--primary-light); }
.gen-student-card.active {
    border-color: var(--primary); background: var(--primary-light);
    box-shadow: 0 0 0 2px rgba(99,102,241,0.1);
}
.gen-student-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-light), #e0e7ff);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 10px; color: var(--primary); flex-shrink: 0; overflow: hidden;
}
.gen-student-avatar img { width: 100%; height: 100%; object-fit: cover; }
.gen-student-info { flex: 1; min-width: 0; }
.gen-student-name { font-size: 11px; font-weight: 600; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gen-student-meta { font-size: 9px; color: var(--text-muted); }
.gen-student-check {
    width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--border);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    transition: var(--transition); font-size: 9px; color: transparent;
}
.gen-student-card.active .gen-student-check { border-color: var(--primary); background: var(--primary); color: #fff; }

/* Empty State */
.gen-empty-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 24px; color: var(--text-muted); grid-column: 1 / -1;
}
.gen-empty-state i { font-size: 24px; opacity: 0.3; margin-bottom: 6px; }
.gen-empty-state p { font-size: 12px; }

/* Student Preview */
.gen-preview-card {
    border-color: var(--success) !important;
    box-shadow: 0 0 0 2px rgba(16,185,129,0.08) !important;
}
.gen-preview-body {
    display: flex; align-items: center; gap: 10px;
}
.gen-preview-avatar {
    border-radius: 50%; background: linear-gradient(135deg, var(--success), #34d399);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-weight: 700; flex-shrink: 0;
}
.gen-preview-info { flex: 1; min-width: 0; }
.gen-preview-info h4 { font-weight: 700; color: var(--text-dark); margin: 0 0 2px; }
.gen-preview-details { display: flex; gap: 10px; flex-wrap: wrap; }
.gen-preview-details span { color: var(--text-muted); display: flex; align-items: center; gap: 3px; }
.gen-preview-details span i { color: var(--success); }
.gen-preview-remove {
    border-radius: 50%; border: 1px solid var(--border); background: var(--card-bg);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--text-muted); transition: var(--transition); flex-shrink: 0;
}
.gen-preview-remove:hover { background: var(--danger-light); border-color: var(--danger); color: var(--danger); }

/* Type Selection */
.gen-type-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 6px;
}
@media (max-width: 576px) {
    .gen-type-grid { grid-template-columns: 1fr; }
}
.gen-type-card { cursor: pointer; display: block; }
.gen-type-card input[type="radio"] { display: none; }
.gen-type-card-inner {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 12px; border: 1.5px solid var(--border); border-radius: var(--radius);
    transition: var(--transition); position: relative;
}
.gen-type-card-inner:hover { border-color: var(--primary); background: #f8f9ff; }
.gen-type-card input:checked + .gen-type-card-inner {
    border-color: var(--primary); background: var(--primary-light);
    box-shadow: 0 0 0 2px rgba(99,102,241,0.1);
}
.gen-type-icon {
    width: 32px; height: 32px; border-radius: var(--radius);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; flex-shrink: 0;
}
.gen-type-icon-purple { background: rgba(99,102,241,0.12); color: #6366f1; }
.gen-type-icon-green { background: rgba(16,185,129,0.12); color: #10b981; }
.gen-type-icon-blue { background: rgba(59,130,246,0.12); color: #3b82f6; }
.gen-type-icon-gold { background: rgba(245,158,11,0.12); color: #f59e0b; }
.gen-type-icon-teal { background: rgba(20,184,166,0.12); color: #14b8a6; }
.gen-type-info { flex: 1; }
.gen-type-info h4 { font-size: 11px; font-weight: 700; color: var(--text-dark); margin: 0 0 1px; }
.gen-type-info p { font-size: 9px; color: var(--text-muted); margin: 0; }
.gen-type-check { color: transparent; font-size: 14px; transition: var(--transition); }
.gen-type-card input:checked + .gen-type-card-inner .gen-type-check { color: var(--primary); }

/* Header Right */
.modern-page-header-right { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }

/* Sticky Generate Bar */
.certgen-sticky-bar {
    position: fixed; bottom: 0; left: 0; right: 0; z-index: 999;
    background: linear-gradient(135deg, #7c3aed, #a855f7);
    color: #fff; display: flex; align-items: center; justify-content: space-between;
    padding: 10px 24px; box-shadow: 0 -4px 20px rgba(124, 58, 237, 0.3);
}
.certgen-sticky-bar-left { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; }
.certgen-sticky-bar-left i { font-size: 14px; }
.certgen-sticky-bar-right { display: flex; gap: 8px; }
.certgen-sticky-bar .btn-modern-primary {
    background: #fff; color: #7c3aed; border: none; font-weight: 700;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.certgen-sticky-bar .btn-modern-primary:hover { background: #f5f0ff; transform: translateY(-1px); }
.certgen-sticky-bar .btn-modern-primary:disabled {
    background: rgba(255,255,255,0.4); color: rgba(124,58,237,0.5);
    cursor: not-allowed; box-shadow: none; transform: none;
}

@media (max-width: 768px) {
    .gen-class-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
    .gen-student-grid { grid-template-columns: 1fr; }
    .gen-search-box { width: 100%; }
    .gen-step-action { margin-left: 0; width: 100%; }
    .gen-preview-details { flex-direction: column; gap: 4px; }
    .modern-page-header { flex-wrap: wrap; }
    .modern-page-header-right { width: 100%; justify-content: flex-end; margin-top: 8px; }
    .certgen-sticky-bar { padding: 10px 16px; }
}
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
    const genBtn = document.getElementById('generateCertBtn');
    const genBtnTop = document.getElementById('generateCertBtnTop');
    const genBtnSticky = document.getElementById('generateCertBtnSticky');
    const stickyBar = document.getElementById('certGenStickyBar');
    const certStickyName = document.getElementById('certStickyName');

    let allStudents = [];
    let selectedClassId = '';
    let selectedStudentId = '';

    // ---- Preselected student from URL param ----
    const preselectedStudent = {{ $preselectedStudent ? json_encode([
        'id' => $preselectedStudent->id,
        'full_name' => $preselectedStudent->full_name,
        'roll_number' => $preselectedStudent->roll_number,
        'class_id' => $preselectedStudent->class_id,
        'section_id' => $preselectedStudent->section_id,
        'photo' => $preselectedStudent->photo,
        'classroom' => $preselectedStudent->classroom ? ['name' => $preselectedStudent->classroom->name] : null,
        'section' => $preselectedStudent->section ? ['name' => $preselectedStudent->section->name] : null,
    ]) : 'null' }};

    // ---- Class Selection ----
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

    // ---- Load Students ----
    function loadStudents() {
        studentContainer.innerHTML = '<div class="gen-empty-state"><i class="fas fa-spinner fa-spin"></i><p>{{ __("app.loading") ?? "Loading..." }}</p></div>';

        const params = new URLSearchParams();
        if (selectedClassId) params.set('class_id', selectedClassId);

        fetch('{{ route("admin.certificate-generate.students") }}?' + params)
            .then(r => { if (!r.ok) throw new Error('Network error'); return r.json(); })
            .then(data => {
                allStudents = Array.isArray(data) ? data : [];
                renderStudents(allStudents);
            })
            .catch(err => {
                console.error('Error loading students:', err);
                studentContainer.innerHTML = '<div class="gen-empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading students</p></div>';
            });
    }

    function renderStudents(students) {
        if (!students.length) {
            studentContainer.innerHTML = '<div class="gen-empty-state"><i class="fas fa-user-slash"></i><p>{{ __("app.no_students_found") ?? "No students found" }}</p></div>';
            return;
        }
        studentContainer.innerHTML = '';
        students.forEach(s => {
            const card = document.createElement('div');
            card.className = 'gen-student-card' + (selectedStudentId == s.id ? ' active' : '');
            card.dataset.studentId = s.id;
            const initials = ((s.full_name || s.first_name || '?')[0]).toUpperCase();

            let avatarContent = initials;
            if (s.photo) {
                avatarContent = '<img src="' + '{{ asset("storage/") }}' + '/' + s.photo + '" alt="">';
            }

            card.innerHTML = `
                <div class="gen-student-avatar">${avatarContent}</div>
                <div class="gen-student-info">
                    <div class="gen-student-name">${s.full_name || s.first_name + ' ' + s.last_name}</div>
                    <div class="gen-student-meta">${s.roll_number ? '#' + s.roll_number : ''} ${s.classroom ? s.classroom.name : ''}</div>
                </div>
                <div class="gen-student-check"><i class="fas fa-check"></i></div>
            `;
            card.addEventListener('click', () => selectStudent(s, card));
            studentContainer.appendChild(card);
        });
    }

    // ---- Select Student ----
    function selectStudent(student, card) {
        // Deselect previous
        studentContainer.querySelectorAll('.gen-student-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');

        selectedStudentId = student.id;
        studentInput.value = student.id;

        // Enable all generate buttons
        genBtn.disabled = false;
        if (genBtnTop) genBtnTop.disabled = false;
        if (genBtnSticky) genBtnSticky.disabled = false;

        // Show sticky bar
        if (stickyBar) stickyBar.style.display = 'flex';
        if (certStickyName) certStickyName.textContent = student.full_name || student.first_name + ' ' + student.last_name;

        // Show preview
        const initials = ((student.full_name || student.first_name || '?')[0]).toUpperCase();
        previewAvatar.textContent = student.photo ? '' : initials;
        previewName.textContent = student.full_name || student.first_name + ' ' + student.last_name;
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

        // Disable all generate buttons
        genBtn.disabled = true;
        if (genBtnTop) genBtnTop.disabled = true;
        if (genBtnSticky) genBtnSticky.disabled = true;

        // Hide sticky bar
        if (stickyBar) stickyBar.style.display = 'none';
    }

    // ---- Remove Student ----
    removeStudentBtn?.addEventListener('click', clearStudentSelection);

    // ---- Search Students ----
    studentSearch?.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        if (!q) {
            renderStudents(allStudents);
            return;
        }
        const filtered = allStudents.filter(s =>
            (s.full_name || s.first_name + ' ' + s.last_name).toLowerCase().includes(q) ||
            (s.roll_number || '').toLowerCase().includes(q) ||
            (s.admission_number || '').toLowerCase().includes(q)
        );
        renderStudents(filtered);
    });

    // ---- Form Validation ----
    document.getElementById('certGenForm')?.addEventListener('submit', function(e) {
        if (!selectedStudentId) {
            e.preventDefault();
            alert('{{ __("app.cert_select_student_alert") ?? "Please select a student first" }}');
            return;
        }
    });

    // ---- Auto-select preselected student from URL param ----
    if (preselectedStudent) {
        // Step 1: Select the student's class card
        const targetClassId = String(preselectedStudent.class_id);
        const classCard = classGrid?.querySelector(`.gen-class-card[data-class-id="${targetClassId}"]`);
        if (classCard) {
            classGrid.querySelectorAll('.gen-class-card').forEach(c => c.classList.remove('active'));
            classCard.classList.add('active');
            selectedClassId = targetClassId;
            classInput.value = targetClassId;
        }

        // Step 2: Load students and auto-select (chained to avoid race conditions)
        studentContainer.innerHTML = '<div class="gen-empty-state"><i class="fas fa-spinner fa-spin"></i><p>{{ __("app.loading") ?? "Loading..." }}</p></div>';

        const params = new URLSearchParams();
        if (selectedClassId) params.set('class_id', selectedClassId);

        fetch('{{ route("admin.certificate-generate.students") }}?' + params)
            .then(r => { if (!r.ok) throw new Error('Network error'); return r.json(); })
            .then(data => {
                allStudents = Array.isArray(data) ? data : [];
                renderStudents(allStudents);

                // Auto-select the preselected student
                const studentId = preselectedStudent.id;
                const studentData = allStudents.find(s => String(s.id) === String(studentId));
                if (studentData) {
                    const card = studentContainer.querySelector(`[data-student-id="${studentId}"]`);
                    if (card) {
                        selectStudent(studentData, card);

                        // Auto-submit the form to directly generate the certificate
                        setTimeout(function() {
                            document.getElementById('certGenForm').submit();
                        }, 300);
                    }
                }
            })
            .catch(err => {
                console.error('Error loading students:', err);
                studentContainer.innerHTML = '<div class="gen-empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading students</p></div>';
            });
    }
})();
</script>
@endpush
@endsection
