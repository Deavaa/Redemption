@extends('layouts.admin')
@section('title', 'New Student Admission')

@push('styles')
<style>
.stu-page { animation: stuFadeIn 0.4s ease-out; }
@keyframes stuFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.stu-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem; }
.stu-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark, #1a1a2e); margin: 0; }
.stu-subtitle { font-size: 0.88rem; color: var(--text-muted); margin: 0.25rem 0 0; }
.stu-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.stu-breadcrumb li { color: #adb5bd; }
.stu-breadcrumb li a { color: var(--text-muted); text-decoration: none; }
.stu-breadcrumb li a:hover { color: #4361ee; }
.stu-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.stu-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.stu-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
.stu-form-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.stu-form-group { display: flex; flex-direction: column; }
.stu-form-label { font-weight: 600; color: #374151; margin-bottom: 0.4rem; font-size: 0.85rem; }
.stu-form-label .required { color: #ef4444; }
.stu-form-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.6rem 0.8rem; font-size: 0.88rem; color: #1a1a2e; transition: all 0.2s; }
.stu-form-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.stu-form-input[readonly] { background: #f3f4f6; color: #6b7280; }
.stu-section-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
.stu-section-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.stu-section-title { font-size: 1rem; font-weight: 700; color: var(--text-dark); margin: 0; }
@media (max-width: 768px) { .stu-form-grid, .stu-form-grid-3 { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="stu-page">
    <div class="stu-header">
        <div>
            <nav class="stu-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.students.index') }}">Students</a></li>
                <li class="active">New Admission</li>
            </ol></nav>
            <h1 class="stu-title">New Student Admission</h1>
            <p class="stu-subtitle">Register a new student into the school system</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Student Information --}}
        <div class="modern-card" style="margin-bottom:1.25rem;">
            <div class="modern-card-header">
                <div class="stu-section-header">
                    <div class="stu-section-icon" style="background:#eef2ff;color:#4361ee;"><i class="fas fa-user"></i></div>
                    <h3 class="stu-section-title">Personal Information</h3>
                </div>
            </div>
            <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
                <div class="stu-form-grid">
                    <div class="stu-form-group">
                        <label class="stu-form-label">First Name <span class="required">*</span></label>
                        <input type="text" name="first_name" class="stu-form-input" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Last Name <span class="required">*</span></label>
                        <input type="text" name="last_name" class="stu-form-input" value="{{ old('last_name') }}" required>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Email</label>
                        <input type="email" name="email" class="stu-form-input" value="{{ old('email') }}">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Phone</label>
                        <input type="text" name="phone" class="stu-form-input" value="{{ old('phone') }}">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Date of Birth <span class="required">*</span></label>
                        <input type="date" name="date_of_birth" class="stu-form-input" value="{{ old('date_of_birth') }}" required>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Gender <span class="required">*</span></label>
                        <select name="gender" class="stu-form-input" required>
                            <option value="">-- Select --</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Blood Group</label>
                        <select name="blood_group" class="stu-form-input">
                            <option value="">-- Select --</option>
                            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Religion</label>
                        <input type="text" name="religion" class="stu-form-input" value="{{ old('religion') }}">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Nationality</label>
                        <input type="text" name="nationality" class="stu-form-input" value="{{ old('nationality', 'Ethiopian') }}">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Place of Birth</label>
                        <input type="text" name="place_of_birth" class="stu-form-input" value="{{ old('place_of_birth') }}">
                    </div>
                    <div class="stu-form-group" style="grid-column:1/-1;">
                        <label class="stu-form-label">Address</label>
                        <input type="text" name="address" class="stu-form-input" value="{{ old('address') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Admission Information --}}
        <div class="modern-card" style="margin-bottom:1.25rem;">
            <div class="modern-card-header">
                <div class="stu-section-header">
                    <div class="stu-section-icon" style="background:#ecfdf5;color:#10b981;"><i class="fas fa-clipboard-list"></i></div>
                    <h3 class="stu-section-title">Admission Information</h3>
                </div>
            </div>
            <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
                <div class="stu-form-grid">
                    <div class="stu-form-group">
                        <label class="stu-form-label">Admission Number</label>
                        <input type="text" name="admission_number" class="stu-form-input" value="{{ old('admission_number', $nextAdmissionNumber ?? '') }}" readonly>
                        <small style="color:var(--text-muted);font-size:0.75rem;">Auto-generated</small>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Admission Date <span class="required">*</span></label>
                        <input type="date" name="admission_date" class="stu-form-input" value="{{ old('admission_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Branch <span class="required">*</span></label>
                        <select name="branch_id" class="stu-form-input" required>
                            <option value="">-- Select Branch --</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Academic Year</label>
                        <select name="academic_year_id" class="stu-form-input">
                            @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ ($currentAy && $ay->id == $currentAy->id) || old('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Class <span class="required">*</span></label>
                        <select name="class_id" id="classSelect" class="stu-form-input" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Section <span class="required">*</span></label>
                        <select name="section_id" id="sectionSelect" class="stu-form-input" required>
                            <option value="">-- Select Section --</option>
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Roll Number</label>
                        <input type="text" name="roll_number" class="stu-form-input" value="{{ old('roll_number') }}" placeholder="Auto-assigned if empty">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Previous School</label>
                        <input type="text" name="previous_school" class="stu-form-input" value="{{ old('previous_school') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Parent/Guardian Information --}}
        <div class="modern-card" style="margin-bottom:1.25rem;">
            <div class="modern-card-header">
                <div class="stu-section-header">
                    <div class="stu-section-icon" style="background:#fefce8;color:#f59e0b;"><i class="fas fa-user-friends"></i></div>
                    <h3 class="stu-section-title">Parent/Guardian Information</h3>
                </div>
            </div>
            <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
                <div class="stu-form-grid">
                    <div class="stu-form-group">
                        <label class="stu-form-label">Guardian Name</label>
                        <input type="text" name="guardian_name" class="stu-form-input" value="{{ old('guardian_name') }}">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Guardian Phone</label>
                        <input type="text" name="guardian_phone" class="stu-form-input" value="{{ old('guardian_phone') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Medical & Photo --}}
        <div class="modern-card" style="margin-bottom:1.25rem;">
            <div class="modern-card-header">
                <div class="stu-section-header">
                    <div class="stu-section-icon" style="background:#fef2f2;color:#ef4444;"><i class="fas fa-heartbeat"></i></div>
                    <h3 class="stu-section-title">Medical & Photo</h3>
                </div>
            </div>
            <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
                <div class="stu-form-grid">
                    <div class="stu-form-group">
                        <label class="stu-form-label">Medical Conditions</label>
                        <input type="text" name="medical_conditions" class="stu-form-input" value="{{ old('medical_conditions') }}" placeholder="e.g., Asthma, Diabetes...">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Allergies</label>
                        <input type="text" name="allergies" class="stu-form-input" value="{{ old('allergies') }}" placeholder="e.g., Peanut allergy...">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Student Photo</label>
                        <input type="file" name="photo" class="stu-form-input" accept="image/*">
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
            <a href="{{ route('admin.students.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.85rem;padding:0.55rem 1.2rem;">Cancel</a>
            <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.85rem;padding:0.55rem 1.5rem;">
                <i class="fas fa-check"></i> Admit Student
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
