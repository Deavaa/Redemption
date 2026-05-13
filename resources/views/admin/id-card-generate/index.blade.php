@extends('layouts.admin')
@section('title', 'Generate ID Cards')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.id-cards.index') }}">ID Cards</a></li>
                    <li class="active">Generate</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Generate ID Cards</h1>
            <p class="modern-page-subtitle">Select students and generate printable ID cards</p>
        </div>
    </div>

    <div class="modern-card">
        <form method="POST" action="{{ route('admin.id-card-generate.generate') }}" target="_blank" id="idCardForm">
            @csrf
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Select Students</h3>
                        <p class="modern-form-section-desc">Filter by class and section, then select students</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Class</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-building modern-input-icon"></i>
                                <select name="filter_class" class="modern-input modern-select" id="classSelect">
                                    <option value="">-- All Classes --</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Section</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-layer-group modern-input-icon"></i>
                                <select name="filter_section" class="modern-input modern-select" id="sectionSelect">
                                    <option value="">-- All Sections --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Students</strong>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="toggleAll">Select All</button>
                        </div>
                        <div class="student-check-list" id="studentList">
                            <p class="text-muted">Select a class to load students</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modern-form-actions">
                <a href="{{ route('admin.id-cards.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" class="btn-modern btn-modern-primary" id="generateBtn" disabled>
                    <i class="fas fa-print"></i> Generate ID Cards
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const classSelect = document.getElementById('classSelect');
const sectionSelect = document.getElementById('sectionSelect');
const studentList = document.getElementById('studentList');
const generateBtn = document.getElementById('generateBtn');

function loadStudents() {
    const params = new URLSearchParams();
    if (classSelect.value) params.set('class_id', classSelect.value);
    if (sectionSelect.value) params.set('section_id', sectionSelect.value);
    fetch('{{ route("admin.id-card-generate.students") }}?' + params)
        .then(r => r.json())
        .then(data => {
            studentList.innerHTML = '';
            if (data.length === 0) { studentList.innerHTML = '<p class="text-muted">No students found</p>'; return; }
            data.forEach(s => {
                studentList.innerHTML += `<label class="d-flex align-items-center gap-2 p-2 border rounded mb-1 cursor-pointer">
                    <input type="checkbox" name="student_ids[]" value="${s.id}" class="form-check-input student-check">
                    <span>${s.first_name} ${s.last_name} (${s.roll_number})</span>
                </label>`;
            });
            updateGenerateBtn();
        });
}

classSelect?.addEventListener('change', function() {
    const cid = this.value;
    fetch('{{ route("admin.id-card-generate.sections") }}?class_id=' + cid)
        .then(r => r.json())
        .then(data => {
            sectionSelect.innerHTML = '<option value="">-- All Sections --</option>';
            data.forEach(s => sectionSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`);
        });
    loadStudents();
});

sectionSelect?.addEventListener('change', loadStudents);

document.getElementById('toggleAll')?.addEventListener('click', function() {
    const checks = document.querySelectorAll('.student-check');
    const allChecked = Array.from(checks).every(c => c.checked);
    checks.forEach(c => c.checked = !allChecked);
    this.textContent = allChecked ? 'Select All' : 'Deselect All';
    updateGenerateBtn();
});

function updateGenerateBtn() {
    const checked = document.querySelectorAll('.student-check:checked').length;
    generateBtn.disabled = checked === 0;
    generateBtn.querySelector('span')?.remove();
}

studentList?.addEventListener('change', updateGenerateBtn);
</script>
@endpush
@endsection
