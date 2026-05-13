@extends('layouts.admin')
@section('title', 'Mark Sheet')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li class="active">Mark Sheet</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Mark Sheet</h1>
            <p class="modern-page-subtitle">Generate student mark sheets and report cards</p>
        </div>
    </div>

    <div class="modern-card">
        <form method="POST" action="{{ route('admin.mark-sheet.generate') }}" target="_blank">
            @csrf
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Generate Mark Sheet</h3>
                        <p class="modern-form-section-desc">Select filters to generate the mark sheet</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Academic Year <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar modern-input-icon"></i>
                                <select name="academic_year_id" class="modern-input modern-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach($academicYears as $ay)
                                        <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Term</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-bookmark modern-input-icon"></i>
                                <select name="term_id" class="modern-input modern-select">
                                    <option value="">-- All Terms --</option>
                                    @foreach($terms as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Class <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-building modern-input-icon"></i>
                                <select name="class_id" class="modern-input modern-select" id="classSelect" required>
                                    <option value="">-- Select --</option>
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
                                <select name="section_id" class="modern-input modern-select" id="sectionSelect">
                                    <option value="">-- All Sections --</option>
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Student</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-user modern-input-icon"></i>
                                <select name="student_id" class="modern-input modern-select" id="studentSelect">
                                    <option value="">-- All Students --</option>
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Exam</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-pen modern-input-icon"></i>
                                <select name="exam_id" class="modern-input modern-select">
                                    <option value="">-- All Exams --</option>
                                    @foreach($exams as $e)
                                        <option value="{{ $e->id }}">{{ $e->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modern-form-actions">
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-print"></i> Generate Mark Sheet
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('classSelect')?.addEventListener('change', function() {
    const cid = this.value;
    fetch('{{ route("admin.mark-sheet.sections") }}?class_id=' + cid)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('sectionSelect');
            sel.innerHTML = '<option value="">-- All Sections --</option>';
            data.forEach(s => sel.innerHTML += `<option value="${s.id}">${s.name}</option>`);
        });
    fetch('{{ route("admin.mark-sheet.students") }}?class_id=' + cid)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('studentSelect');
            sel.innerHTML = '<option value="">-- All Students --</option>';
            data.forEach(s => sel.innerHTML += `<option value="${s.id}">${s.first_name} ${s.last_name} (${s.roll_number})</option>`);
        });
});
</script>
@endpush
@endsection
