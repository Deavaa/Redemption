@extends('layouts.admin')
@section('title', 'Readmit Student')

@push('styles')
<style>
.stu-page { animation: stuFadeIn 0.4s ease-out; }
@keyframes stuFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.stu-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem; }
.stu-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin: 0; }
.stu-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.stu-breadcrumb li a { color: var(--text-muted); text-decoration: none; }
.stu-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.stu-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.stu-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
.stu-form-group { display: flex; flex-direction: column; }
.stu-form-label { font-weight: 600; color: #374151; margin-bottom: 0.4rem; font-size: 0.85rem; }
.stu-form-label .required { color: #ef4444; }
.stu-form-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.6rem 0.8rem; font-size: 0.88rem; color: #1a1a2e; transition: all 0.2s; }
.stu-form-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.stu-info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem 1.5rem; }
.stu-info-label { font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; }
.stu-info-value { font-size: 0.92rem; font-weight: 600; color: var(--text-dark); }
</style>
@endpush

@section('content')
<div class="stu-page">
    <div class="stu-header">
        <div>
            <nav class="stu-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.students.index') }}">Students</a></li>
                <li class="active">Readmit Student</li>
            </ol></nav>
            <h1 class="stu-title">Readmit Student</h1>
        </div>
    </div>

    {{-- Info Banner --}}
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:1rem 1.5rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:0.75rem;color:#1e40af;font-weight:500;font-size:0.9rem;">
        <i class="fas fa-info-circle" style="font-size:1.2rem;flex-shrink:0;"></i>
        <span>This student previously left the school. Fill in the new placement details to readmit them.</span>
    </div>

    {{-- Previous Student Info --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-card-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:36px;height:36px;border-radius:10px;background:#fef2f2;color:#ef4444;display:flex;align-items:center;justify-content:center;"><i class="fas fa-history"></i></div>
                <h3 class="modern-card-title">Previous Information</h3>
            </div>
        </div>
        <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
            <div class="stu-info-grid">
                <div><div class="stu-info-label">Student Name</div><div class="stu-info-value">{{ $student->first_name }} {{ $student->last_name }}</div></div>
                <div><div class="stu-info-label">Admission Number</div><div class="stu-info-value">{{ $student->admission_number }}</div></div>
                <div><div class="stu-info-label">Previous Class</div><div class="stu-info-value">{{ $student->previousClassroom->name ?? $student->classroom->name ?? '-' }}</div></div>
                <div><div class="stu-info-label">Leave Date</div><div class="stu-info-value">{{ $student->leave_date ? $student->leave_date->format('M d, Y') : '-' }}</div></div>
                <div><div class="stu-info-label">Leave Reason</div><div class="stu-info-value">{{ $student->leave_reason ?? '-' }}</div></div>
                <div><div class="stu-info-label">Readmission Count</div><div class="stu-info-value">{{ $student->readmission_count ?? 0 }}</div></div>
            </div>
        </div>
    </div>

    {{-- Readmission Form --}}
    <form method="POST" action="{{ route('admin.students.readmit-store', $student->id) }}">
        @csrf

        <div class="modern-card" style="margin-bottom:1.25rem;">
            <div class="modern-card-header">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="width:36px;height:36px;border-radius:10px;background:#ecfdf5;color:#10b981;display:flex;align-items:center;justify-content:center;"><i class="fas fa-redo"></i></div>
                    <h3 class="modern-card-title">New Placement</h3>
                </div>
            </div>
            <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
                <div class="stu-form-grid">
                    <div class="stu-form-group">
                        <label class="stu-form-label">Academic Year <span class="required">*</span></label>
                        <select name="academic_year_id" class="stu-form-input" required>
                            @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ ($currentAy && $ay->id == $currentAy->id) ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">New Class <span class="required">*</span></label>
                        <select name="class_id" id="classSelect" class="stu-form-input" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">New Section <span class="required">*</span></label>
                        <select name="section_id" id="sectionSelect" class="stu-form-input" required>
                            <option value="">-- Select Section --</option>
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Readmission Remarks</label>
                        <textarea name="remarks" rows="3" class="stu-form-input" placeholder="Reason for readmission or any notes..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
            <a href="{{ route('admin.students.inactive') }}" class="btn-modern btn-modern-ghost" style="font-size:0.85rem;padding:0.55rem 1.2rem;">Cancel</a>
            <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.85rem;padding:0.55rem 1.5rem;background:linear-gradient(135deg,#10b981,#059669);border:none;">
                <i class="fas fa-redo"></i> Readmit Student
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('classSelect').addEventListener('change', function() {
    const classId = this.value;
    const sectionSelect = document.getElementById('sectionSelect');
    sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
    if (!classId) return;
    fetch('{{ route('admin.students.api.sections', '') }}/' + classId)
        .then(r => r.json())
        .then(data => {
            (data.sections || data || []).forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name;
                sectionSelect.appendChild(opt);
            });
        })
        .catch(() => {});
});
</script>
@endpush
