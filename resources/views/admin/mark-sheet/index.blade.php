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
                                <select name="academic_year_id" class="modern-input modern-select" required {{ $isTeacher ?? false ? 'disabled' : '' }}>
                                    <option value="">-- Select --</option>
                                    @foreach($academicYears as $ay)
                                        <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                                    @endforeach
                                </select>
                                @if($isTeacher ?? false)<input type="hidden" name="academic_year_id" value="{{ $academicYears->first()->id ?? '' }}">@endif
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Term</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-bookmark modern-input-icon"></i>
                                <select name="term_id" class="modern-input modern-select" {{ $isTeacher ?? false ? 'disabled' : '' }}>
                                    <option value="">-- All Terms --</option>
                                    @foreach($terms as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                                @if($isTeacher ?? false)<input type="hidden" name="term_id" value="{{ $terms->first()->id ?? '' }}">@endif
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
                {{-- Export buttons — when a class is selected, the user can
                     export marks as PDF (print) or Excel (CSV) using the
                     /api/export/marks endpoint. --}}
                <div class="dropdown d-inline-block">
                    <button class="btn-modern btn-modern-outline dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download"></i> Export Marks
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width:200px;">
                        <li><h6 class="dropdown-header">Select a class above first, then click an option below.</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header"><i class="fas fa-file-pdf me-1"></i>PDF</h6></li>
                        <li><a class="dropdown-item export-marks-link" href="#" data-format="pdf" target="_blank">
                            <i class="fas fa-print me-2"></i>Print / Save as PDF
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header"><i class="fas fa-file-excel me-1"></i>Excel / CSV</h6></li>
                        <li><a class="dropdown-item export-marks-link" href="#" data-format="csv">
                            <i class="fas fa-file-csv me-2"></i>Download CSV
                        </a></li>
                    </ul>
                </div>
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
            data.forEach(s => sel.innerHTML += `<option value="${s.id}">${s.full_name || s.first_name + ' ' + s.last_name} (${s.roll_number})</option>`);
        });
});

// ── Export Marks link builder ──────────────────────────────────────
// When the user clicks an "Export Marks" link, we build the URL with the
// current form values (academic_year_id, term_id, exam_id, class_id,
// section_id, subject_id) and the chosen format (pdf or csv), then open
// the URL in a new tab (for PDF, which auto-triggers print) or as a
// download (for CSV).
document.querySelectorAll('.export-marks-link').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        var format = this.getAttribute('data-format');

        var form = document.querySelector('form');
        if (!form) return;

        var params = new URLSearchParams();
        params.append('format', format);

        // Pull current values from the form's named selects
        ['academic_year_id', 'term_id', 'exam_id', 'class_id', 'section_id', 'subject_id'].forEach(function(name) {
            var el = form.querySelector('[name="' + name + '"]');
            if (el && el.value) params.append(name, el.value);
        });

        // Validate: class_id is required by the API
        if (!params.get('class_id')) {
            alert('Please select a Class before exporting.');
            return;
        }
        if (!params.get('academic_year_id')) {
            alert('Please select an Academic Year before exporting.');
            return;
        }
        if (!params.get('term_id')) {
            alert('Please select a Term before exporting.');
            return;
        }

        var url = '{{ url("/api/export/marks") }}?' + params.toString();
        if (format === 'pdf') {
            window.open(url, '_blank');
        } else {
            window.location.href = url;
        }
    });
});
</script>
@endpush
@endsection
