@extends('layouts.admin')
@section('title', __('app.generate') . ' ' . __('app.student_id_cards'))

@section('content')
<div class="modern-page">
    <div class="modern-page-header" style="margin-bottom:0.75rem;">
        <div class="modern-page-header-left" style="display:flex;align-items:center;gap:10px;">
            <nav aria-label="breadcrumb" class="modern-breadcrumb" style="margin:0;">
                <ol style="margin:0;">
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.id-cards.index') }}">{{ __('app.student_id_cards') }}</a></li>
                    <li class="active">{{ __('app.generate') }}</li>
                </ol>
            </nav>
            <span style="color:var(--border);font-size:0.65rem;">|</span>
            <h1 style="font-size:0.85rem;font-weight:700;color:var(--text-dark);margin:0;">{{ __('app.generate') }} {{ __('app.student_id_cards') }}</h1>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.id-cards.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.7rem;padding:4px 10px;"><i class="fas fa-arrow-left"></i> {{ __('app.cancel') }}</a>
            <button type="submit" class="btn-modern btn-modern-primary" id="generateIdBtnTop" disabled form="idCardForm" style="font-size:0.7rem;padding:4px 12px;">
                <i class="fas fa-print"></i> {{ __('app.generate') }}
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.id-card-generate.generate') }}" target="_blank" id="idCardForm">
        @csrf

        {{-- Filter: Class & Section (horizontal) --}}
        <div class="modern-card" style="margin-bottom:10px;">
            <div class="idgen-filter-horizontal">
                <div class="idgen-filter-group idgen-filter-inline">
                    <label class="idgen-filter-label-sm">{{ __('app.classes') }}</label>
                    <div class="idgen-class-chips" id="idClassChips">
                        <button type="button" class="idgen-chip active" data-class-id="">
                            {{ __('app.all_classes') ?? 'All' }}
                        </button>
                        @foreach($classes as $c)
                            <button type="button" class="idgen-chip" data-class-id="{{ $c->id }}">
                                {{ $c->name }}
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="filter_class" id="idClassInput" value="">
                </div>
                <div class="idgen-filter-divider">/</div>
                <div class="idgen-filter-group idgen-filter-inline">
                    <label class="idgen-filter-label-sm">{{ __('app.section') ?? 'Section' }}</label>
                    <div class="idgen-section-chips" id="idSectionChips">
                        <button type="button" class="idgen-chip active" data-section-id="">
                            {{ __('app.all_sections') ?? 'All' }}
                        </button>
                    </div>
                    <input type="hidden" name="filter_section" id="idSectionInput" value="">
                </div>
            </div>
        </div>

        {{-- Select Students --}}
        <div class="modern-card" style="margin-bottom:10px;">
            <div class="idgen-toolbar">
                <div class="idgen-toolbar-left">
                    <span class="idgen-toolbar-label">{{ __('app.select_students') ?? 'Select Students' }}</span>
                    <div class="idgen-selection-count-sm" id="selectionBar">
                        <i class="fas fa-id-card"></i>
                        <span id="selectedCount">0</span> {{ __('app.selected') ?? 'selected' }}
                    </div>
                </div>
                <div class="idgen-toolbar-right">
                    <div class="gen-search-box">
                        <i class="fas fa-search gen-search-icon"></i>
                        <input type="text" id="idStudentSearch" class="gen-search-input" placeholder="{{ __('app.search') }}">
                    </div>
                    <div class="idgen-select-actions">
                        <button type="button" class="btn-modern btn-modern-outline btn-modern-sm" id="selectAllBtn">
                            <i class="fas fa-check-double"></i>
                        </button>
                        <button type="button" class="btn-modern btn-modern-ghost btn-modern-sm" id="deselectAllBtn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="idgen-student-container">
                <div id="idStudentGrid" class="idgen-student-grid">
                    <div class="gen-empty-state">
                        <i class="fas fa-users"></i>
                        <p>{{ __('app.select_class_to_load') ?? 'Select a class to load students' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Selected Students Preview --}}
        <div class="modern-card" id="selectedPreviewCard" style="margin-bottom:10px;display:none;">
            <div class="idgen-toolbar" style="padding:8px 14px;">
                <span class="idgen-toolbar-label"><i class="fas fa-check-circle" style="color:var(--success);font-size:10px;"></i> {{ __('app.id_card_preview_title') ?? 'Selected' }}</span>
                <span class="gen-step-desc" id="previewSummaryText">-</span>
            </div>
            <div style="padding:0 14px 10px;">
                <div class="idgen-preview-grid" id="previewGrid">
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="modern-card">
            <div class="modern-form-actions">
                <a href="{{ route('admin.id-cards.index') }}" class="btn-modern btn-modern-ghost">{{ __('app.cancel') }}</a>
                <button type="submit" class="btn-modern btn-modern-primary" id="generateIdBtn" disabled>
                    <i class="fas fa-print"></i> {{ __('app.generate') }} {{ __('app.student_id_cards') }}
                </button>
            </div>
        </div>

        {{-- Sticky Top Generate Bar --}}
        <div class="idgen-sticky-bar" id="idGenStickyBar" style="display:none;">
            <div class="idgen-sticky-bar-left">
                <i class="fas fa-id-card"></i>
                <span id="stickyCount">0</span> {{ __('app.students') ?? 'students' }} {{ __('app.id_card_selected') ?? 'selected' }}
            </div>
            <div class="idgen-sticky-bar-right">
                <button type="submit" class="btn-modern btn-modern-primary" id="generateIdBtnSticky" disabled form="idCardForm">
                    <i class="fas fa-print"></i> {{ __('app.generate') }} {{ __('app.student_id_cards') }}
                </button>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
/* ===== ID Card Generate - Compact Layout ===== */
.gen-step-desc { font-size: 9px; color: var(--text-muted); margin: 1px 0 0; }

/* Horizontal Filter Bar */
.idgen-filter-horizontal {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 14px; flex-wrap: wrap;
}
.idgen-filter-inline {
    display: flex; align-items: center; gap: 6px; flex-direction: row !important;
}
.idgen-filter-label-sm {
    font-size: 9px; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: 0.5px; margin: 0; white-space: nowrap;
}
.idgen-filter-divider {
    color: var(--border); font-size: 10px; font-weight: 300;
}
.idgen-filter-group { gap: 4px; }

/* Toolbar (compact header for student selection) */
.idgen-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 6px 14px; border-bottom: 1px solid var(--border);
    gap: 8px; flex-wrap: wrap;
}
.idgen-toolbar-left { display: flex; align-items: center; gap: 10px; }
.idgen-toolbar-right { display: flex; align-items: center; gap: 6px; }
.idgen-toolbar-label {
    font-size: 10px; font-weight: 700; color: var(--text-dark);
    text-transform: uppercase; letter-spacing: 0.3px;
}
.idgen-selection-count-sm {
    font-size: 10px; font-weight: 600; color: var(--primary);
    display: flex; align-items: center; gap: 4px;
    background: var(--primary-light); padding: 2px 8px; border-radius: 10px;
}
.idgen-selection-count-sm i { font-size: 10px; }

/* Search */
.gen-search-box { position: relative; width: 180px; }
.gen-search-icon { position: absolute; left: 8px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 10px; }
.gen-search-input {
    width: 100%; border: 1px solid var(--border); border-radius: var(--radius-sm);
    padding: 4px 8px 4px 26px; font-size: 11px; font-family: var(--font);
    color: var(--text-dark); background: var(--card-bg); transition: var(--transition);
}
.gen-search-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 2px var(--primary-light); }

/* Chips */
.idgen-class-chips, .idgen-section-chips {
    display: flex; flex-wrap: wrap; gap: 4px;
}
.idgen-chip {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 3px 10px; border: 1.5px solid var(--border); border-radius: 14px;
    background: var(--card-bg); font-size: 11px; font-weight: 500; color: var(--text);
    cursor: pointer; transition: var(--transition); font-family: var(--font);
}
.idgen-chip i { font-size: 9px; }
.idgen-chip:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
.idgen-chip.active {
    border-color: var(--primary); background: var(--primary); color: #fff;
}

/* Select Actions */
.idgen-select-actions { display: flex; gap: 3px; }

/* Student Container */
.idgen-student-container { padding: 8px 14px; }

/* Student Grid */
.idgen-student-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 6px; max-height: 320px; overflow-y: auto; scrollbar-width: thin;
}
.idgen-student-row {
    display: flex; align-items: center; gap: 8px;
    padding: 7px 10px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
    cursor: pointer; transition: var(--transition); background: var(--card-bg);
}
.idgen-student-row:hover { border-color: rgba(99,102,241,0.4); background: #f8f9ff; }
.idgen-student-row.checked {
    border-color: var(--primary); background: var(--primary-light);
}
.idgen-student-check {
    width: 18px; height: 18px; border-radius: 4px; border: 2px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: var(--transition); font-size: 9px; color: transparent;
}
.idgen-student-row.checked .idgen-student-check {
    border-color: var(--primary); background: var(--primary); color: #fff;
}
.idgen-student-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #ede9fe);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 10px; color: var(--primary); flex-shrink: 0;
    overflow: hidden;
}
.idgen-student-avatar img { width: 100%; height: 100%; object-fit: cover; }
.idgen-student-info { flex: 1; min-width: 0; }
.idgen-student-name {
    font-size: 11px; font-weight: 600; color: var(--text-dark);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.idgen-student-meta { font-size: 9px; color: var(--text-muted); }

/* Preview Grid */
.idgen-preview-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 6px; max-height: 200px; overflow-y: auto; scrollbar-width: thin;
}
.idgen-preview-item {
    display: flex; align-items: center; gap: 6px; padding: 5px 8px;
    background: var(--primary-light); border-radius: var(--radius-sm);
    border: 1px solid rgba(99,102,241,0.2);
}
.idgen-preview-item-avatar {
    width: 24px; height: 24px; border-radius: 50%;
    background: var(--primary); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 9px; flex-shrink: 0;
}
.idgen-preview-item-name {
    font-size: 10px; font-weight: 600; color: var(--text-dark);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.idgen-preview-item-remove {
    margin-left: auto; width: 18px; height: 18px; border-radius: 50%;
    border: none; background: transparent; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: var(--text-muted); font-size: 9px; transition: var(--transition); flex-shrink: 0;
}
.idgen-preview-item-remove:hover { background: var(--danger-light); color: var(--danger); }

/* Empty State */
.gen-empty-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 24px; color: var(--text-muted); grid-column: 1 / -1;
}
.gen-empty-state i { font-size: 24px; opacity: 0.3; margin-bottom: 6px; }
.gen-empty-state p { font-size: 12px; }

/* Header Right */
.modern-page-header-right {
    display: flex; align-items: center; gap: 6px; flex-shrink: 0;
}

/* Sticky Generate Bar */
.idgen-sticky-bar {
    position: fixed; bottom: 0; left: 0; right: 0; z-index: 999;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #fff; display: flex; align-items: center; justify-content: space-between;
    padding: 10px 24px; box-shadow: 0 -4px 20px rgba(79, 70, 229, 0.3);
}
.idgen-sticky-bar-left { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; }
.idgen-sticky-bar-left i { font-size: 14px; }
.idgen-sticky-bar-right { display: flex; gap: 8px; }
.idgen-sticky-bar .btn-modern-primary {
    background: #fff; color: #4f46e5; border: none; font-weight: 700;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.idgen-sticky-bar .btn-modern-primary:hover { background: #f0f0ff; transform: translateY(-1px); }
.idgen-sticky-bar .btn-modern-primary:disabled {
    background: rgba(255,255,255,0.4); color: rgba(79,70,229,0.5);
    cursor: not-allowed; box-shadow: none; transform: none;
}

@media (max-width: 768px) {
    .idgen-filter-horizontal { flex-direction: column; align-items: flex-start; }
    .idgen-filter-inline { width: 100%; }
    .gen-search-box { width: 100%; }
    .idgen-student-grid { grid-template-columns: 1fr; }
    .idgen-preview-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
    .idgen-toolbar { flex-direction: column; align-items: flex-start; }
    .idgen-toolbar-right { width: 100%; flex-wrap: wrap; }
    .modern-page-header { flex-wrap: wrap; }
    .modern-page-header-right { width: 100%; justify-content: flex-end; margin-top: 8px; }
    .idgen-sticky-bar { padding: 10px 16px; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    const classChips = document.getElementById('idClassChips');
    const sectionChips = document.getElementById('idSectionChips');
    const classInput = document.getElementById('idClassInput');
    const sectionInput = document.getElementById('idSectionInput');
    const studentGrid = document.getElementById('idStudentGrid');
    const studentSearch = document.getElementById('idStudentSearch');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const selectionBar = document.getElementById('selectionBar');
    const selectedCount = document.getElementById('selectedCount');
    const previewCard = document.getElementById('selectedPreviewCard');
    const previewGrid = document.getElementById('previewGrid');
    const previewSummary = document.getElementById('previewSummaryText');
    const genBtn = document.getElementById('generateIdBtn');
    const genBtnTop = document.getElementById('generateIdBtnTop');
    const genBtnSticky = document.getElementById('generateIdBtnSticky');
    const stickyBar = document.getElementById('idGenStickyBar');
    const stickyCount = document.getElementById('stickyCount');

    let allStudents = [];
    let selectedIds = new Set();
    let currentClassId = '';
    let currentSectionId = '';

    // ---- Preselected student from URL param ----
    const preselectedStudent = {{ $preselectedStudent ? json_encode([
        'id' => $preselectedStudent->id,
        'full_name' => $preselectedStudent->full_name,
        'roll_number' => $preselectedStudent->roll_number,
        'class_id' => $preselectedStudent->class_id,
        'section_id' => $preselectedStudent->section_id,
        'photo' => $preselectedStudent->photo,
    ]) : 'null' }};

    // ---- Class Chips ----
    classChips?.addEventListener('click', function(e) {
        const chip = e.target.closest('.idgen-chip');
        if (!chip) return;
        classChips.querySelectorAll('.idgen-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        currentClassId = chip.dataset.classId;
        classInput.value = currentClassId;
        loadSections();
        loadStudents();
    });

    // ---- Section Chips ----
    sectionChips?.addEventListener('click', function(e) {
        const chip = e.target.closest('.idgen-chip');
        if (!chip) return;
        sectionChips.querySelectorAll('.idgen-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        currentSectionId = chip.dataset.sectionId;
        sectionInput.value = currentSectionId;
        loadStudents();
    });

    // ---- Load Sections ----
    function loadSections() {
        if (!currentClassId) {
            sectionChips.innerHTML = '<button type="button" class="idgen-chip active" data-section-id=""><i class="fas fa-th-large"></i> {{ __("app.all_sections") ?? "All" }}</button>';
            currentSectionId = '';
            sectionInput.value = '';
            return;
        }
        fetch('{{ route("admin.id-card-generate.sections") }}?class_id=' + encodeURIComponent(currentClassId))
            .then(r => r.json())
            .then(data => {
                sectionChips.innerHTML = '<button type="button" class="idgen-chip active" data-section-id=""><i class="fas fa-th-large"></i> {{ __("app.all_sections") ?? "All" }}</button>';
                if (Array.isArray(data)) {
                    data.forEach(s => {
                        const chip = document.createElement('button');
                        chip.type = 'button';
                        chip.className = 'idgen-chip';
                        chip.dataset.sectionId = s.id;
                        chip.textContent = s.name;
                        sectionChips.appendChild(chip);
                    });
                }
                currentSectionId = '';
                sectionInput.value = '';
            });
    }

    // ---- Load Students ----
    function loadStudents() {
        studentGrid.innerHTML = '<div class="gen-empty-state"><i class="fas fa-spinner fa-spin"></i><p>{{ __("app.loading") ?? "Loading..." }}</p></div>';

        const params = new URLSearchParams();
        if (currentClassId) params.set('class_id', currentClassId);
        if (currentSectionId) params.set('section_id', currentSectionId);

        fetch('{{ route("admin.id-card-generate.students") }}?' + params)
            .then(r => { if (!r.ok) throw new Error('Network error'); return r.json(); })
            .then(data => {
                allStudents = Array.isArray(data) ? data : [];
                renderStudents(allStudents);
            })
            .catch(err => {
                console.error('Error loading students:', err);
                studentGrid.innerHTML = '<div class="gen-empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading students</p></div>';
            });
    }

    function renderStudents(students) {
        if (!students.length) {
            studentGrid.innerHTML = '<div class="gen-empty-state"><i class="fas fa-user-slash"></i><p>{{ __("app.no_students_found") ?? "No students found" }}</p></div>';
            updateSelection();
            return;
        }
        studentGrid.innerHTML = '';
        students.forEach(s => {
            const row = document.createElement('div');
            row.className = 'idgen-student-row' + (selectedIds.has(String(s.id)) ? ' checked' : '');
            row.dataset.studentId = s.id;
            const initials = ((s.full_name || s.first_name || '?')[0]).toUpperCase();

            let avatarContent = initials;
            if (s.photo) {
                avatarContent = '<img src="{{ asset("storage/") }}/' + s.photo + '" alt="">';
            }

            row.innerHTML = `
                <div class="idgen-student-check"><i class="fas fa-check"></i></div>
                <div class="idgen-student-avatar">${avatarContent}</div>
                <div class="idgen-student-info">
                    <div class="idgen-student-name">${s.full_name || s.first_name + ' ' + s.last_name}</div>
                    <div class="idgen-student-meta">${s.roll_number ? 'Roll: ' + s.roll_number : ''} ${s.classroom ? '| ' + s.classroom.name : ''} ${s.section ? '| ' + s.section.name : ''}</div>
                </div>
            `;
            row.addEventListener('click', () => toggleStudent(String(s.id), row));
            studentGrid.appendChild(row);
        });
        updateSelection();
    }

    // ---- Toggle Student Selection ----
    function toggleStudent(id, row) {
        if (selectedIds.has(id)) {
            selectedIds.delete(id);
            row.classList.remove('checked');
        } else {
            selectedIds.add(id);
            row.classList.add('checked');
        }
        updateSelection();
    }

    // ---- Update Selection ----
    function updateSelection() {
        const count = selectedIds.size;
        selectedCount.textContent = count;
        genBtn.disabled = count === 0;
        if (genBtnTop) genBtnTop.disabled = count === 0;
        if (genBtnSticky) genBtnSticky.disabled = count === 0;
        if (stickyCount) stickyCount.textContent = count;

        // Show sticky bar when students selected
        if (stickyBar) {
            stickyBar.style.display = count > 0 ? 'flex' : 'none';
        }

        // Update preview
        if (count > 0) {
            previewCard.style.display = '';
            previewSummary.textContent = count + ' {{ __("app.students") ?? "students" }} {{ __("app.id_card_selected") ?? "selected" }}';
            renderPreview();
        } else {
            previewCard.style.display = 'none';
        }

        // Update hidden inputs - remove old ones, add new
        const form = document.getElementById('idCardForm');
        form.querySelectorAll('input[name="student_ids[]"]').forEach(i => i.remove());
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = id;
            form.appendChild(input);
        });
    }

    function renderPreview() {
        previewGrid.innerHTML = '';
        allStudents.filter(s => selectedIds.has(String(s.id))).forEach(s => {
            const item = document.createElement('div');
            item.className = 'idgen-preview-item';
            const initials = ((s.full_name || s.first_name || '?')[0]).toUpperCase();
            item.innerHTML = `
                <div class="idgen-preview-item-avatar">${initials}</div>
                <div class="idgen-preview-item-name">${s.full_name || s.first_name + ' ' + s.last_name}</div>
                <button type="button" class="idgen-preview-item-remove" data-id="${s.id}"><i class="fas fa-times"></i></button>
            `;
            item.querySelector('.idgen-preview-item-remove').addEventListener('click', (e) => {
                e.stopPropagation();
                selectedIds.delete(String(s.id));
                // Update row in grid
                const row = studentGrid.querySelector(`[data-student-id="${s.id}"]`);
                if (row) row.classList.remove('checked');
                updateSelection();
            });
            previewGrid.appendChild(item);
        });
    }

    // ---- Select All / Deselect All ----
    selectAllBtn?.addEventListener('click', function() {
        allStudents.forEach(s => selectedIds.add(String(s.id)));
        studentGrid.querySelectorAll('.idgen-student-row').forEach(r => r.classList.add('checked'));
        updateSelection();
    });
    deselectAllBtn?.addEventListener('click', function() {
        selectedIds.clear();
        studentGrid.querySelectorAll('.idgen-student-row').forEach(r => r.classList.remove('checked'));
        updateSelection();
    });

    // ---- Search ----
    studentSearch?.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        if (!q) { renderStudents(allStudents); return; }
        const filtered = allStudents.filter(s =>
            (s.full_name || s.first_name + ' ' + s.last_name).toLowerCase().includes(q) ||
            (s.roll_number || '').toLowerCase().includes(q)
        );
        renderStudents(filtered);
    });

    // ---- Form Validation ----
    document.getElementById('idCardForm')?.addEventListener('submit', function(e) {
        if (selectedIds.size === 0) {
            e.preventDefault();
            alert('{{ __("app.id_card_select_alert") ?? "Please select at least one student" }}');
        }
    });

    // ---- Auto-select preselected student from URL param ----
    if (preselectedStudent) {
        // Step 1: Select the student's class chip
        const targetClassId = String(preselectedStudent.class_id);
        const classChip = classChips?.querySelector(`.idgen-chip[data-class-id="${targetClassId}"]`);
        if (classChip) {
            classChips.querySelectorAll('.idgen-chip').forEach(c => c.classList.remove('active'));
            classChip.classList.add('active');
            currentClassId = targetClassId;
            classInput.value = targetClassId;
        }

        // Step 2: Load sections, then select section, then load students & auto-select
        // Use a chained approach to avoid race conditions
        const preselectedSectionId = preselectedStudent.section_id ? String(preselectedStudent.section_id) : '';

        // Load sections first
        if (currentClassId) {
            fetch('{{ route("admin.id-card-generate.sections") }}?class_id=' + encodeURIComponent(currentClassId))
                .then(r => r.json())
                .then(data => {
                    sectionChips.innerHTML = '<button type="button" class="idgen-chip active" data-section-id=""><i class="fas fa-th-large"></i> {{ __("app.all_sections") ?? "All" }}</button>';
                    if (Array.isArray(data)) {
                        data.forEach(s => {
                            const chip = document.createElement('button');
                            chip.type = 'button';
                            chip.className = 'idgen-chip';
                            chip.dataset.sectionId = s.id;
                            chip.textContent = s.name;
                            sectionChips.appendChild(chip);
                        });
                    }

                    // Select the student's section chip
                    if (preselectedSectionId) {
                        const sectionChip = sectionChips?.querySelector(`.idgen-chip[data-section-id="${preselectedSectionId}"]`);
                        if (sectionChip) {
                            sectionChips.querySelectorAll('.idgen-chip').forEach(c => c.classList.remove('active'));
                            sectionChip.classList.add('active');
                            currentSectionId = preselectedSectionId;
                            sectionInput.value = preselectedSectionId;
                        }
                    } else {
                        currentSectionId = '';
                        sectionInput.value = '';
                    }

                    // Now load students and auto-select the preselected one
                    loadStudentsAndAutoSelect();
                });
        } else {
            // No class filter - load all students
            currentSectionId = '';
            sectionInput.value = '';
            loadStudentsAndAutoSelect();
        }

        function loadStudentsAndAutoSelect() {
            studentGrid.innerHTML = '<div class="gen-empty-state"><i class="fas fa-spinner fa-spin"></i><p>{{ __("app.loading") ?? "Loading..." }}</p></div>';

            const params = new URLSearchParams();
            if (currentClassId) params.set('class_id', currentClassId);
            if (currentSectionId) params.set('section_id', currentSectionId);

            fetch('{{ route("admin.id-card-generate.students") }}?' + params)
                .then(r => { if (!r.ok) throw new Error('Network error'); return r.json(); })
                .then(data => {
                    allStudents = Array.isArray(data) ? data : [];
                    renderStudents(allStudents);

                    // Auto-select the preselected student
                    if (preselectedStudent) {
                        const studentId = String(preselectedStudent.id);
                        if (allStudents.some(s => String(s.id) === studentId)) {
                            selectedIds.add(studentId);
                            const row = studentGrid.querySelector(`[data-student-id="${studentId}"]`);
                            if (row) row.classList.add('checked');
                            updateSelection();
                        }
                    }
                })
                .catch(err => {
                    console.error('Error loading students:', err);
                    studentGrid.innerHTML = '<div class="gen-empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading students</p></div>';
                });
        }
    }
})();
</script>
@endpush
@endsection
