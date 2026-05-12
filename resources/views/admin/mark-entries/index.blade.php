@extends('layouts.admin')
@section('title', 'Mark Entry')
@push('styles')
    <style>
        .mark-carousel .carousel-item {
            min-height: 300px;
        }

        .mark-input {
            width: 100%;
            max-width: 100%;
            text-align: center;

        }

        .mark-entry-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: nowrap;
        }

        .student-name-container {
            flex: 1 1 auto;
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .student-name-container h5 {
            margin-bottom: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.9rem;
            font-weight: 600;
            color: #ffffff;
        }

        .student-nav-group {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .student-counter {
            font-size: 0.35rem;
            opacity: 0.9;
            white-space: nowrap;
        }

        .mark-input::-webkit-outer-spin-button,
        .mark-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .mark-input[type='number'] {
            -moz-appearance: textfield;
        }

        .mark-entry-card {
            border: none;
            overflow: hidden;
            margin: 0;
        }

        .mark-entry-card .card-body {
            padding: 0;
        }

        .mark-entry-header {
            background: linear-gradient(135deg, #1f496d 0%, #214f74 100%);
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .mark-page-title {
            font-size: 0.35rem;
        }

        .nav-button {
            width: 2rem;
            min-width: 2rem;
            height: 2rem;
            padding: 0;
            font-size: 0.45rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.3);
            color: #ffffff;
        }

        .nav-button .bi {
            color: #ffffff;
        }

        .nav-button:hover,
        .nav-button:focus {
            background-color: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.45);
            color: #ffffff;
        }

        .mark-entry-header h5 {
            font-size: 1rem;
            margin-bottom: 0.1rem;
            font-weight: 700;
        }

        .save-status {
            font-size: 0.65rem;
            opacity: 0.9;
            padding: 0.15rem 0.4rem;
            border-radius: 0.35rem;
            background: rgba(255, 255, 255, 0.12);
            color: #f4f7fb;
            white-space: nowrap;
        }

        .mark-section label.form-label {
            font-size: 0.5rem;
        }

        .mark-entry-meta {
            background: rgba(255, 255, 255, 0.16);
            padding: 0.1rem 0.5rem;
            display: block;
            overflow: hidden;
            border-radius: 0.35rem;
        }

        .mark-entry-meta span {
            font-size: 0.65rem;
            color: #f4f7fb;
            display: block;
            line-height: 1.1;
        }

        .mark-entry-meta span:first-child {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mark-entry-meta span:last-child {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mark-section {
            border: 1px solid #e9ecef;
            padding: 0.1rem;
            background: #fff;
        }

        .mark-section .section-title {
            margin-bottom: 0.15rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mark-section .section-title h6 {
            margin: 0;
            font-size: 0.6rem;
            font-weight: 600;
        }

        .mark-section .section-title span {
            font-size: 0.5rem;
            color: #6c757d;
        }

        .mark-entry-card .card-body .text-end span,
        .mark-entry-card .card-body .text-end strong {
            font-size: 0.5rem;
        }

        @media (max-width: 768px) {
            .admin-topbar .topbar-left span {
                display: none !important;
            }

            .mark-entry-header h5 {
                font-size: 0.9rem;
            }

            .mark-entry-meta {
                flex-direction: column;
                gap: 0.5rem;
            }

            .mark-input {
                padding: 0.05rem 0.05rem;
            }

            .mark-section .row.g-1>.col-6,
            .mark-section .row.g-1>.col-md-3 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }
    </style>
@endpush
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h4 class="mb-1 fw-bold mark-page-title">Mark Entry</h4>
            </div>
        </div>
        <div class="card mb-3" id="filterPanel">
            <div class="card-body" style="padding:0.25rem 0.75rem;">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2"><label class="form-label fw-semibold small" style="display:none;">Academic
                            Year</label>
                        <select id="filterAy" class="form-select form-select-sm" aria-label="Academic Year">
                            @foreach ($academicYears as $ay)
                                <option value="{{ $ay->id }}"
                                    {{ $currentAy && $currentAy->id == $ay->id ? 'selected' : '' }}>{{ $ay->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><label class="form-label fw-semibold small" style="display:none;">Term</label>
                        <select id="filterTerm" class="form-select form-select-sm" aria-label="Term">
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}"
                                    {{ $currentTerm && $currentTerm->id == $term->id ? 'selected' : '' }}>
                                    {{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label fw-semibold small" style="display:none;">Class -
                            Section</label>
                        <select id="filterClassSection" class="form-select form-select-sm" aria-label="Class and Section">
                            <option value="">-- Select Class - Section --</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->class_id }}-{{ $section->id }}">{{ $section->classRoom->name }}
                                    - {{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label fw-semibold small" style="display:none;">Subject</label>
                        <select id="filterSubject" class="form-select form-select-sm" disabled aria-label="Subject">
                            <option value="">-- Select Subject --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div id="infoBanner" class="alert alert-info d-none mb-2 py-2"><strong id="bannerText"></strong></div>
        <div id="markCarousel" class="d-none">
            <div class="card">
                <div class="card-body p-0">
                    <div id="studentCarousel" class="carousel slide mark-carousel" data-bs-ride="false">
                        <div class="carousel-inner" id="carouselInner">
                            <!-- Students will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        let students = [];
        let currentIndex = 0;
        let saveTimer = null;
        const API_BASE = '{{ request()->root() }}/admin/mark-entries/api';

        document.getElementById('filterAy').addEventListener('change', () => {
            resetSubjectSelect();
            loadTerms();
        });
        document.getElementById('filterTerm').addEventListener('change', () => {
            resetSubjectSelect();
            enableClassSection();
        });
        document.getElementById('filterClassSection').addEventListener('change', () => {
            resetSubjectSelect();
            loadSubjects();
        });
        document.getElementById('filterSubject').addEventListener('change', loadStudents);

        function loadTerms() {
            const ayId = document.getElementById('filterAy').value;
            fetch(`${API_BASE}/terms?academic_year_id=${ayId}`, {
                    credentials: 'same-origin'
                })
                .then(r => {
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    return r.json();
                })
                .then(data => {
                    const termSelect = document.getElementById('filterTerm');
                    termSelect.innerHTML = '<option value="">-- Select --</option>';
                    data.forEach(term => {
                        termSelect.innerHTML += `<option value="${term.id}">${term.name}</option>`;
                    });
                    termSelect.disabled = false;
                });
        }

        function enableClassSection() {
            document.getElementById('filterClassSection').disabled = false;
        }

        function resetSubjectSelect() {
            const subjectSelect = document.getElementById('filterSubject');
            subjectSelect.innerHTML = '<option value="">-- Select --</option>';
            subjectSelect.disabled = true;
            showFilterPanel();
            document.getElementById('markCarousel').classList.add('d-none');
            document.getElementById('infoBanner').classList.add('d-none');
        }

        function hideFilterPanel() {
            document.getElementById('filterPanel').classList.add('d-none');
        }

        function showFilterPanel() {
            document.getElementById('filterPanel').classList.remove('d-none');
        }

        function loadSubjects() {
            const ayId = document.getElementById('filterAy').value;
            const classSectionValue = document.getElementById('filterClassSection').value;
            if (!ayId || !classSectionValue) return;
            const [classId, sectionId] = classSectionValue.split('-');
            fetch(`${API_BASE}/subjects?class_id=${classId}&section_id=${sectionId}&academic_year_id=${ayId}`, {
                    credentials: 'same-origin'
                })
                .then(r => {
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    return r.json();
                })
                .then(data => {
                    const subjectSelect = document.getElementById('filterSubject');
                    subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
                    data.forEach(subject => {
                        subjectSelect.innerHTML += `<option value="${subject.id}">${subject.name}</option>`;
                    });
                    subjectSelect.disabled = data.length === 0;
                    if (data.length === 1) {
                        subjectSelect.value = data[0].id;
                        loadStudents();
                    }
                });
        }

        function loadStudents() {
            const ayId = document.getElementById('filterAy').value;
            const termId = document.getElementById('filterTerm').value;
            const classSectionValue = document.getElementById('filterClassSection').value;
            const subjectId = document.getElementById('filterSubject').value;

            if (!ayId || !termId || !classSectionValue || !subjectId) {
                return;
            }

            const [classId, sectionId] = classSectionValue.split('-');

            fetch(
                    `${API_BASE}/load-students?academic_year_id=${ayId}&term_id=${termId}&class_id=${classId}&section_id=${sectionId}&subject_id=${subjectId}`, {
                        credentials: 'same-origin'
                    }
                )
                .then(r => {
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    return r.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    const responseStudents = Array.isArray(data.students) ? data.students : (Array.isArray(data) ?
                        data : []);
                    students = responseStudents.map(student => ({
                        ...student,
                        id: student.student_id || student.id,
                        student_name: student.student_name || student.name || student.student_name ||
                            'Student',
                        marks: {
                            ca1: student.ca1,
                            ca2: student.ca2,
                            ca3: student.ca3,
                            ca4: student.ca4,
                            ca5: student.ca5,
                            ca6: student.ca6,
                            ca7: student.ca7,
                            ca8: student.ca8,
                            ca9: student.ca9,
                            ca10: student.ca10,
                            conduct: student.conduct,
                            handwriting: student.handwriting,
                            creativity: student.creativity,
                            test1: student.test1,
                            test2: student.test2,
                            mid_term: student.mid_term,
                            final_exam: student.final_exam,
                        }
                    }));
                    currentIndex = 0;

                    if (students.length > 0) {
                        renderCarousel();
                        hideFilterPanel();
                        document.getElementById('markCarousel').classList.remove('d-none');
                        document.getElementById('infoBanner').classList.add('d-none');
                        document.getElementById('bannerText').textContent = '';
                    } else {
                        showFilterPanel();
                        document.getElementById('infoBanner').classList.remove('d-none');
                        document.getElementById('bannerText').textContent =
                            'No students found for the selected filters.';
                        document.getElementById('markCarousel').classList.add('d-none');
                    }
                })
                .catch(error => {
                    console.error('Failed to load students:', error);
                    showFilterPanel();
                    document.getElementById('markCarousel').classList.add('d-none');
                    document.getElementById('infoBanner').classList.remove('d-none');
                    document.getElementById('bannerText').textContent =
                        `Unable to load students: ${error.message}`;
                });
        }

        function renderCarousel() {
            const carouselInner = document.getElementById('carouselInner');
            const subjectName = document.getElementById('filterSubject').selectedOptions[0]?.textContent || '';
            const academicYear = document.getElementById('filterAy').selectedOptions[0]?.textContent || '';
            const termName = document.getElementById('filterTerm').selectedOptions[0]?.textContent || '';
            const classSection = document.getElementById('filterClassSection').selectedOptions[0]?.textContent || '';
            carouselInner.innerHTML = '';
            students.forEach((student, index) => {
                const active = index === 0 ? 'active' : '';
                carouselInner.innerHTML += `
            <div class="carousel-item ${active}" data-student-id="${student.id}">
                <div class="card mark-entry-card shadow-sm">
                    <div class="mark-entry-header">
                        <div class="mark-entry-header-row">
                            <button type="button" class="btn btn-sm btn-outline-secondary nav-button student-prev-btn" onclick="navigateStudent(-1)" aria-label="Previous">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <div class="student-name-container">
                                <h5 class="student-name-header mb-0 text-truncate">${student.student_name || student.name || 'Student'}</h5>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="student-counter text-white-50 small">${index + 1} / ${students.length}</div>
                                    <div class="save-status" id="save-status-${student.id}">Not saved</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary nav-button student-next-btn" onclick="navigateStudent(1)" aria-label="Next">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                        <div class="mark-entry-meta mt-1">
                            <span>${subjectName} - ${academicYear}</span>
                            <span>${termName} - ${classSection}</span>
                        </div>
                    </div>
                    <div class="card-body px-0 py-0">
                        <div class="row g-2 mx-0 px-0 py-0">
                            <div class="col-12 col-lg-6">
                                <div class="mark-section">
                                    <div class="section-title">
                                        <h6>Continuous Assessment</h6>
                                        <span>30 marks</span>
                                    </div>
                                    <div class="row g-1">
                                        ${[1,2,3,4,5,6,7,8,9,10].map(ca => `
                                                                                                                    <div class="col-3">
                                                                                                                        <div class="input-group input-group-sm">
                                                                                                                            <span class="input-group-text bg-primary text-white">${ca}</span>
                                                                                                                            <input type="number" class="form-control form-control-sm mark-input" data-type="ca" data-number="${ca}" value="${student.marks?.[`ca${ca}`] || ''}" min="0" max="5" placeholder="/5">
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                `).join('')}
                                    </div>
                                    <div class="row g-1 mt-1">
                                        <div class="col-4">
                                            <label class="form-label small">Conduct</label>
                                            <input type="number" class="form-control form-control-sm mark-input" data-type="ca" data-number="conduct" value="${student.marks?.conduct || ''}" min="0" max="5" placeholder="/5">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">Handwriting</label>
                                            <input type="number" class="form-control form-control-sm mark-input" data-type="ca" data-number="handwriting" value="${student.marks?.handwriting || ''}" min="0" max="5" placeholder="/5">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">Creativity</label>
                                            <input type="number" class="form-control form-control-sm mark-input" data-type="ca" data-number="creativity" value="${student.marks?.creativity || ''}" min="0" max="10" placeholder="/10">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="mark-section">
                                    <div class="section-title">
                                        <h6>Examination</h6>
                                        <span>70 marks</span>
                                    </div>
                                    <div class="row g-1">
                                        <div class="col-3">
                                            <label class="form-label small">Test 1</label>
                                            <input type="number" class="form-control form-control-sm mark-input" data-type="exam" data-exam="test1" value="${student.marks?.test1 || ''}" min="0" max="10">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Test 2</label>
                                            <input type="number" class="form-control form-control-sm mark-input" data-type="exam" data-exam="test2" value="${student.marks?.test2 || ''}" min="0" max="10">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Mid-Term</label>
                                            <input type="number" class="form-control form-control-sm mark-input" data-type="exam" data-exam="mid_term" value="${student.marks?.mid_term || ''}" min="0" max="20">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Final</label>
                                            <input type="number" class="form-control form-control-sm mark-input" data-type="exam" data-exam="final_exam" value="${student.marks?.final_exam || ''}" min="0" max="30">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-3 pb-3 pt-2 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 border-top">
                                <div class="text-end">
                                <span class="me-3"><strong>CA:</strong> <span id="ca-total-${student.id}">0</span></span>
                                <span class="me-3"><strong>Exam:</strong> <span id="exam-total-${student.id}">0</span></span>
                                <span><strong>Total:</strong> <span id="grand-total-${student.id}">0</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
            });
            attachAutoSave();
            students.forEach(student => calculateTotal(student.student_id || student.id));
            updateNavigation();
        }

        function updateNavigation() {
            document.querySelectorAll('.student-counter').forEach(el => el.textContent =
                `${currentIndex + 1} / ${students.length}`);
            document.querySelectorAll('.student-prev-btn').forEach(btn => btn.disabled = currentIndex === 0);
            document.querySelectorAll('.student-next-btn').forEach(btn => btn.disabled = currentIndex === students.length -
                1);
        }

        function navigateStudent(direction) {
            currentIndex += direction;
            if (currentIndex < 0) currentIndex = 0;
            if (currentIndex >= students.length) currentIndex = students.length - 1;
            const carousel = new bootstrap.Carousel(document.getElementById('studentCarousel'));
            carousel.to(currentIndex);
            updateNavigation();
        }

        function attachAutoSave() {
            document.querySelectorAll('.mark-input').forEach(input => {
                input.addEventListener('input', function() {
                    enforceMaxValue(this);
                    const studentId = this.closest('.carousel-item').dataset.studentId;
                    calculateTotal(studentId);
                    const type = this.dataset.type;
                    const key = type === 'ca' ? (this.dataset.number === 'conduct' || this.dataset
                        .number === 'handwriting' || this.dataset.number === 'creativity' ? this.dataset
                        .number : `ca${this.dataset.number}`) : this.dataset.exam;
                    const value = this.value;
                    if (saveTimer) clearTimeout(saveTimer);
                    saveTimer = setTimeout(() => saveMark(studentId, key, value), 900);
                });

                input.addEventListener('blur', function() {
                    const studentId = this.closest('.carousel-item').dataset.studentId;
                    const type = this.dataset.type;
                    const key = type === 'ca' ? (this.dataset.number === 'conduct' || this.dataset
                        .number === 'handwriting' || this.dataset.number === 'creativity' ? this.dataset
                        .number : `ca${this.dataset.number}`) : this.dataset.exam;
                    const value = this.value;
                    if (saveTimer) {
                        clearTimeout(saveTimer);
                        saveTimer = null;
                    }
                    saveMark(studentId, key, value);
                });
            });
        }

        function enforceMaxValue(input) {
            const min = parseFloat(input.min);
            const max = parseFloat(input.max);
            if (input.value === '') return;
            let value = parseFloat(input.value);
            if (Number.isNaN(value)) {
                input.value = '';
                return;
            }
            if (!Number.isNaN(max) && value > max) {
                value = max;
            }
            if (!Number.isNaN(min) && value < min) {
                value = min;
            }
            input.value = value;
        }

        function saveMark(studentId, key, value) {
            const ayId = document.getElementById('filterAy').value;
            const termId = document.getElementById('filterTerm').value;
            const classSectionValue = document.getElementById('filterClassSection').value;
            const [classId, sectionId] = classSectionValue.split('-');
            const subjectId = document.getElementById('filterSubject').value;

            const student = students.find(s => String(s.id) === String(studentId));
            if (student) {
                student.marks = student.marks || {};
                student.marks[key] = value;
            }

            const statusEl = document.getElementById(`save-status-${studentId}`);
            if (statusEl) {
                statusEl.textContent = 'Saving...';
                statusEl.style.background = 'rgba(255,255,255,0.25)';
            }
            fetch(`${API_BASE}/save`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    student_id: studentId,
                    academic_year_id: ayId,
                    term_id: termId,
                    class_id: classId,
                    section_id: sectionId,
                    subject_id: subjectId,
                    mark_key: key,
                    mark_value: value
                })
            }).then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            }).then(res => {
                if (statusEl) {
                    statusEl.textContent = res.success ? 'Saved' : 'Save failed';
                    statusEl.style.background = res.success ? 'rgba(46,204,113,0.25)' : 'rgba(231,76,60,0.25)';
                }
            }).catch(() => {
                if (statusEl) {
                    statusEl.textContent = 'Save error';
                    statusEl.style.background = 'rgba(231,76,60,0.25)';
                }
            });
        }

        function calculateTotal(studentId) {
            const item = document.querySelector(`[data-student-id="${studentId}"]`);
            const caInputs = item.querySelectorAll('[data-type="ca"]');
            const examInputs = item.querySelectorAll('[data-type="exam"]');
            let caTotal = 0;
            caInputs.forEach(input => caTotal += parseFloat(input.value) || 0);
            let examTotal = 0;
            examInputs.forEach(input => examTotal += parseFloat(input.value) || 0);
            const maxCaTotal = 70; // 10 CA inputs * 5 + Conduct 5 + Handwriting 5 + Creativity 10
            const scaledCa = maxCaTotal ? (caTotal / maxCaTotal) * 30 : 0;
            const grandTotal = scaledCa + examTotal;
            document.getElementById(`ca-total-${studentId}`).textContent = scaledCa.toFixed(1);
            document.getElementById(`exam-total-${studentId}`).textContent = examTotal.toFixed(1);
            document.getElementById(`grand-total-${studentId}`).textContent = grandTotal.toFixed(1);
        }

        // Load initial terms
        loadTerms();
    </script>
@endpush
