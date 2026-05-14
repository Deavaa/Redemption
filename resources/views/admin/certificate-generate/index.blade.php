@extends('layouts.admin')
@section('title', __('app.generate') . ' ' . __('app.certificates'))

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.certificates.index') }}">{{ __('app.certificates') }}</a></li>
                    <li class="active">{{ __('app.generate') }}</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">{{ __('app.generate') }} {{ __('app.certificates') }}</h1>
            <p class="modern-page-subtitle">{{ __('app.cert_generate_subtitle') ?? 'Create academic certificates for students' }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.certificate-generate.generate') }}" target="_blank" id="certGenForm">
        @csrf

        {{-- Step 1: Select Class --}}
        <div class="modern-card" style="margin-bottom:16px;">
            <div class="gen-step-header">
                <div class="gen-step-number">1</div>
                <div>
                    <h3 class="gen-step-title">{{ __('app.classes') }}</h3>
                    <p class="gen-step-desc">{{ __('app.cert_step1_desc') ?? 'Select a class to load students' }}</p>
                </div>
            </div>
            <div class="gen-step-body">
                <div class="gen-class-grid" id="classGrid">
                    <button type="button" class="gen-class-card" data-class-id="">
                        <i class="fas fa-th-large"></i>
                        <span>{{ __('app.all_classes') ?? 'All Classes' }}</span>
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
        <div class="modern-card" style="margin-bottom:16px;">
            <div class="gen-step-header">
                <div class="gen-step-number">2</div>
                <div>
                    <h3 class="gen-step-title">{{ __('app.students') }}</h3>
                    <p class="gen-step-desc">{{ __('app.cert_step2_desc') ?? 'Select the student for the certificate' }}</p>
                </div>
                <div class="gen-step-action">
                    <div class="gen-search-box">
                        <i class="fas fa-search gen-search-icon"></i>
                        <input type="text" id="studentSearch" class="gen-search-input" placeholder="{{ __('app.search') }}">
                    </div>
                </div>
            </div>
            <div class="gen-step-body">
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
        <div class="modern-card gen-preview-card" id="studentPreviewCard" style="margin-bottom:16px;display:none;">
            <div class="gen-step-header">
                <div class="gen-step-number gen-step-number-green"><i class="fas fa-user-check"></i></div>
                <div>
                    <h3 class="gen-step-title">{{ __('app.cert_selected_student') ?? 'Selected Student' }}</h3>
                    <p class="gen-step-desc">{{ __('app.cert_selected_desc') ?? 'Student details for certificate generation' }}</p>
                </div>
            </div>
            <div class="gen-preview-body">
                <div class="gen-preview-avatar" id="previewAvatar">?</div>
                <div class="gen-preview-info">
                    <h4 id="previewName">-</h4>
                    <div class="gen-preview-details">
                        <span><i class="fas fa-hashtag"></i> <span id="previewRoll">-</span></span>
                        <span><i class="fas fa-building"></i> <span id="previewClass">-</span></span>
                        <span><i class="fas fa-layer-group"></i> <span id="previewSection">-</span></span>
                    </div>
                </div>
                <button type="button" class="gen-preview-remove" id="removeStudent" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Step 3: Certificate Type --}}
        <div class="modern-card" style="margin-bottom:16px;">
            <div class="gen-step-header">
                <div class="gen-step-number">3</div>
                <div>
                    <h3 class="gen-step-title">{{ __('app.cert_type') ?? 'Certificate Type' }}</h3>
                    <p class="gen-step-desc">{{ __('app.cert_step3_desc') ?? 'Choose the type of certificate to generate' }}</p>
                </div>
            </div>
            <div class="gen-step-body">
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
    </form>
</div>

@push('styles')
<style>
/* ===== Certificate Generate - Modern Steps ===== */
.gen-step-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
}
.gen-step-number {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), #8b5cf6);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 15px;
    flex-shrink: 0;
}
.gen-step-number-green {
    background: linear-gradient(135deg, var(--success), #34d399);
}
.gen-step-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0;
}
.gen-step-desc {
    font-size: 12px;
    color: var(--text-muted);
    margin: 2px 0 0;
}
.gen-step-action {
    margin-left: auto;
}
.gen-step-body {
    padding: 16px 20px;
}

/* Class Selection Grid */
.gen-class-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
}
.gen-class-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 14px 10px;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    background: var(--card-bg);
    cursor: pointer;
    transition: var(--transition);
    font-family: var(--font);
    font-size: 12px;
    font-weight: 600;
    color: var(--text);
}
.gen-class-card i {
    font-size: 20px;
    color: var(--text-muted);
    transition: var(--transition);
}
.gen-class-card:hover {
    border-color: var(--primary);
    background: var(--primary-light);
    color: var(--primary);
}
.gen-class-card:hover i { color: var(--primary); }
.gen-class-card.active {
    border-color: var(--primary);
    background: var(--primary-light);
    color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
}
.gen-class-card.active i { color: var(--primary); }

/* Search */
.gen-search-box {
    position: relative;
    width: 220px;
}
.gen-search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 12px;
}
.gen-search-input {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 6px 10px 6px 30px;
    font-size: 12px;
    font-family: var(--font);
    color: var(--text-dark);
    background: var(--card-bg);
    transition: var(--transition);
}
.gen-search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-light);
}

/* Student Grid */
.gen-student-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 8px;
    max-height: 300px;
    overflow-y: auto;
    scrollbar-width: thin;
}
.gen-student-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border: 2px solid var(--border);
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: var(--transition);
    background: var(--card-bg);
}
.gen-student-card:hover {
    border-color: var(--primary);
    background: var(--primary-light);
}
.gen-student-card.active {
    border-color: var(--primary);
    background: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
}
.gen-student-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-light), #e0e7ff);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    color: var(--primary);
    flex-shrink: 0;
    overflow: hidden;
}
.gen-student-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.gen-student-info {
    flex: 1;
    min-width: 0;
}
.gen-student-name {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.gen-student-meta {
    font-size: 10px;
    color: var(--text-muted);
}
.gen-student-check {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: var(--transition);
    font-size: 10px;
    color: transparent;
}
.gen-student-card.active .gen-student-check {
    border-color: var(--primary);
    background: var(--primary);
    color: #fff;
}

/* Empty State */
.gen-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px;
    color: var(--text-muted);
    grid-column: 1 / -1;
}
.gen-empty-state i {
    font-size: 28px;
    opacity: 0.3;
    margin-bottom: 8px;
}
.gen-empty-state p {
    font-size: 13px;
}

/* Student Preview */
.gen-preview-card {
    border-color: var(--success) !important;
    box-shadow: 0 0 0 3px rgba(16,185,129,0.1) !important;
}
.gen-preview-body {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 20px;
}
.gen-preview-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--success), #34d399);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
    flex-shrink: 0;
}
.gen-preview-info {
    flex: 1;
    min-width: 0;
}
.gen-preview-info h4 {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0 0 4px;
}
.gen-preview-details {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}
.gen-preview-details span {
    font-size: 12px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 4px;
}
.gen-preview-details span i {
    font-size: 10px;
    color: var(--success);
}
.gen-preview-remove {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1px solid var(--border);
    background: var(--card-bg);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 12px;
    transition: var(--transition);
    flex-shrink: 0;
}
.gen-preview-remove:hover {
    background: var(--danger-light);
    border-color: var(--danger);
    color: var(--danger);
}

/* Type Selection */
.gen-type-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}
.gen-type-card {
    cursor: pointer;
    display: block;
}
.gen-type-card input[type="radio"] {
    display: none;
}
.gen-type-card-inner {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    transition: var(--transition);
    position: relative;
}
.gen-type-card-inner:hover {
    border-color: var(--primary);
    background: #f8f9ff;
}
.gen-type-card input:checked + .gen-type-card-inner {
    border-color: var(--primary);
    background: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
}
.gen-type-icon {
    width: 44px;
    height: 44px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.gen-type-icon-purple { background: rgba(99,102,241,0.12); color: #6366f1; }
.gen-type-icon-green { background: rgba(16,185,129,0.12); color: #10b981; }
.gen-type-icon-blue { background: rgba(59,130,246,0.12); color: #3b82f6; }
.gen-type-icon-gold { background: rgba(245,158,11,0.12); color: #f59e0b; }
.gen-type-icon-teal { background: rgba(20,184,166,0.12); color: #14b8a6; }
.gen-type-info { flex: 1; }
.gen-type-info h4 {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0 0 2px;
}
.gen-type-info p {
    font-size: 11px;
    color: var(--text-muted);
    margin: 0;
}
.gen-type-check {
    color: transparent;
    font-size: 18px;
    transition: var(--transition);
}
.gen-type-card input:checked + .gen-type-card-inner .gen-type-check {
    color: var(--primary);
}

@media (max-width: 768px) {
    .gen-class-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
    .gen-student-grid { grid-template-columns: 1fr; }
    .gen-search-box { width: 100%; }
    .gen-step-action { margin-left: 0; width: 100%; }
    .gen-preview-details { flex-direction: column; gap: 4px; }
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

    let allStudents = [];
    let selectedClassId = '';
    let selectedStudentId = '';

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
            const initials = ((s.first_name || '?')[0] + (s.last_name || '?')[0]).toUpperCase();

            let avatarContent = initials;
            if (s.photo) {
                avatarContent = '<img src="' + '{{ asset("storage/") }}' + '/' + s.photo + '" alt="">';
            }

            card.innerHTML = `
                <div class="gen-student-avatar">${avatarContent}</div>
                <div class="gen-student-info">
                    <div class="gen-student-name">${s.first_name} ${s.last_name}</div>
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

        // Show preview
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
            (s.first_name + ' ' + s.last_name).toLowerCase().includes(q) ||
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
})();
</script>
@endpush
@endsection
