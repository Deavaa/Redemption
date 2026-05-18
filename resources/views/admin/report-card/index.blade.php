@extends('layouts.admin')
@section('title', 'Report Cards')

@push('styles')
<style>
.rc-page { animation: rcFadeIn 0.4s ease-out; }
@keyframes rcFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

.rc-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.rc-header-left { flex: 1; }
.rc-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.rc-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }

.rc-layout { display: grid; grid-template-columns: 1fr 340px; gap: 1.25rem; align-items: start; }
.rc-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
.rc-card-header { padding: 1rem 1.25rem; border-bottom: 1px solid #f0f0f0; background: #fafbfc; font-weight: 700; color: #1a1a2e; font-size: 0.92rem; display: flex; align-items: center; gap: 0.5rem; }
.rc-card-body { padding: 1.25rem; }

.rc-form-group { margin-bottom: 1rem; }
.rc-form-label { display: block; font-weight: 600; color: #374151; margin-bottom: 0.35rem; font-size: 0.85rem; }
.rc-form-control { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.6rem 0.85rem; font-size: 0.88rem; color: #1a1a2e; transition: all 0.2s; }
.rc-form-control:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }

.rc-info-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.25rem; }
.rc-info-title { font-size: 0.95rem; font-weight: 700; color: #1e40af; margin-bottom: 0.5rem; }
.rc-info-text { font-size: 0.82rem; color: #3b82f6; line-height: 1.6; }

.rc-face-diagram { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem; }
.rc-face { background: #fff; border: 1.5px solid #93c5fd; border-radius: 8px; padding: 0.65rem; text-align: center; font-size: 0.75rem; color: #1e40af; font-weight: 600; }
.rc-face-num { font-size: 0.9rem; font-weight: 800; color: #4361ee; }

@media (max-width: 992px) { .rc-layout { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="rc-page">
    <div class="rc-header">
        <div class="rc-header-left">
            <h1 class="rc-title">Report Cards</h1>
            <p class="rc-subtitle">Generate annual report cards with all terms, landscape layout with two columns</p>
        </div>
    </div>

    <div class="rc-layout">
        {{-- Filter Form --}}
        <div class="rc-card">
            <div class="rc-card-header"><i class="fas fa-filter" style="color:#4361ee"></i> Select Students</div>
            <div class="rc-card-body">
                <form method="POST" action="{{ route('admin.report-card.generate') }}" id="rcForm">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="rc-form-group">
                            <label class="rc-form-label">Academic Year *</label>
                            <select name="academic_year_id" class="rc-form-control" required id="rcAcademicYearId">
                                <option value="">-- Select --</option>
                                @foreach($academicYears as $ay)
                                    <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="rc-form-group">
                            <label class="rc-form-label">Class *</label>
                            <select name="class_id" class="rc-form-control" required id="rcClassId">
                                <option value="">-- Select --</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="rc-form-group">
                            <label class="rc-form-label">Section</label>
                            <select name="section_id" class="rc-form-control" id="rcSectionId">
                                <option value="">-- All Sections --</option>
                            </select>
                        </div>
                        <div class="rc-form-group">
                            <label class="rc-form-label">Student</label>
                            <select name="student_id" class="rc-form-control" id="rcStudentId">
                                <option value="">-- All Students --</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" style="margin-top:0.5rem;">
                        <i class="fas fa-id-card me-1"></i> Generate Annual Report Cards
                    </button>
                </form>
            </div>
        </div>

        {{-- Info Card --}}
        <div>
            <div class="rc-info-box">
                <div class="rc-info-title"><i class="fas fa-info-circle me-1"></i> Annual Report Card</div>
                <div class="rc-info-text">
                    The report card shows marks for <strong>all terms</strong> of the academic year plus annual averages. Layout is A4 landscape with two equal columns:
                </div>
                <div class="rc-face-diagram">
                    <div class="rc-face"><div class="rc-face-num">Left</div>Subject Marks<br>Term 1 + Term 2 + Annual</div>
                    <div class="rc-face"><div class="rc-face-num">Right</div>Summary + Grading<br>Comments + Signatures</div>
                </div>
            </div>

            <div class="rc-card">
                <div class="rc-card-header"><i class="fas fa-print" style="color:#4361ee"></i> Print Tips</div>
                <div class="rc-card-body" style="font-size:0.85rem;color:#6b7280;line-height:1.7;">
                    <ul style="margin:0;padding-left:1.25rem;">
                        <li>Use <strong>A4 landscape</strong> paper</li>
                        <li>Set margins to <strong>None</strong> in print dialog</li>
                        <li>Enable <strong>Background Graphics</strong></li>
                        <li>Each student gets a full page</li>
                        <li>Annual = Average of Term 1 + Term 2</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('rcClassId').addEventListener('change', function() {
    const classId = this.value;
    const sectionSel = document.getElementById('rcSectionId');
    const studentSel = document.getElementById('rcStudentId');
    sectionSel.innerHTML = '<option value="">-- All Sections --</option>';
    studentSel.innerHTML = '<option value="">-- All Students --</option>';
    if (!classId) return;

    fetch('{{ route("admin.report-card.sections") }}?class_id=' + classId, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        data.forEach(s => {
            sectionSel.innerHTML += '<option value="' + s.id + '">' + s.name + '</option>';
        });
    });

    fetch('{{ route("admin.report-card.students") }}?class_id=' + classId, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        data.forEach(s => {
            studentSel.innerHTML += '<option value="' + s.id + '">' + (s.roll_number ? s.roll_number + ' - ' : '') + (s.full_name || s.first_name + ' ' + s.last_name) + '</option>';
        });
    });
});

document.getElementById('rcSectionId').addEventListener('change', function() {
    const classId = document.getElementById('rcClassId').value;
    const sectionId = this.value;
    const studentSel = document.getElementById('rcStudentId');
    studentSel.innerHTML = '<option value="">-- All Students --</option>';
    if (!classId) return;

    let url = '{{ route("admin.report-card.students") }}?class_id=' + classId;
    if (sectionId) url += '&section_id=' + sectionId;

    fetch(url, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        data.forEach(s => {
            studentSel.innerHTML += '<option value="' + s.id + '">' + (s.roll_number ? s.roll_number + ' - ' : '') + (s.full_name || s.first_name + ' ' + s.last_name) + '</option>';
        });
    });
});
</script>
@endpush
