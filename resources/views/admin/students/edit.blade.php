@extends('layouts.admin')
@section('title', 'Edit Student')

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
@media (max-width: 768px) { .stu-form-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="stu-page">
    <div class="stu-header">
        <div>
            <nav class="stu-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.students.index') }}">Students</a></li>
                <li class="active">Edit {{ $student->full_name }}</li>
            </ol></nav>
            <h1 class="stu-title">Edit Student</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.students.update', $student->id) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        {{-- Personal Information --}}
        <div class="modern-card" style="margin-bottom:1.25rem;">
            <div class="modern-card-header">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="width:36px;height:36px;border-radius:10px;background:#eef2ff;color:#4361ee;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user"></i></div>
                    <h3 class="modern-card-title">Personal Information</h3>
                </div>
            </div>
            <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
                <div class="stu-form-grid">
                    <div class="stu-form-group">
                        <label class="stu-form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="full_name" class="stu-form-input" value="{{ old('full_name', $student->full_name) }}" required>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Email</label>
                        <input type="email" name="email" class="stu-form-input" value="{{ old('email', $student->email) }}">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Phone</label>
                        <input type="text" name="phone" class="stu-form-input" value="{{ old('phone', $student->phone) }}">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="stu-form-input" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Gender</label>
                        <select name="gender" class="stu-form-input">
                            <option value="">-- Select --</option>
                            <option value="Male" {{ old('gender', $student->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $student->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Address</label>
                        <input type="text" name="address" class="stu-form-input" value="{{ old('address', $student->address) }}">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Status</label>
                        <select name="status" class="stu-form-input">
                            <option value="active" {{ old('status', $student->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $student->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="transferred" {{ old('status', $student->status) === 'transferred' ? 'selected' : '' }}>Transferred</option>
                            <option value="graduated" {{ old('status', $student->status) === 'graduated' ? 'selected' : '' }}>Graduated</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Academic Information --}}
        <div class="modern-card" style="margin-bottom:1.25rem;">
            <div class="modern-card-header">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="width:36px;height:36px;border-radius:10px;background:#ecfdf5;color:#10b981;display:flex;align-items:center;justify-content:center;"><i class="fas fa-graduation-cap"></i></div>
                    <h3 class="modern-card-title">Academic Information</h3>
                </div>
            </div>
            <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
                <div class="stu-form-grid">
                    <div class="stu-form-group">
                        <label class="stu-form-label">Branch</label>
                        <select name="branch_id" class="stu-form-input">
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $student->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Class</label>
                        <select name="class_id" id="classSelect" class="stu-form-input">
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Section</label>
                        <select name="section_id" id="sectionSelect" class="stu-form-input">
                            <option value="">-- Select Section --</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->id }}" {{ old('section_id', $student->section_id) == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Academic Year</label>
                        <select name="academic_year_id" class="stu-form-input">
                            @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ old('academic_year_id', $student->academic_year_id) == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
            <a href="{{ route('admin.students.show', $student->id) }}" class="btn-modern btn-modern-ghost" style="font-size:0.85rem;padding:0.55rem 1.2rem;">Cancel</a>
            <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.85rem;padding:0.55rem 1.5rem;"><i class="fas fa-check"></i> Update Student</button>
        </div>
    </form>
</div>
@push('scripts')
    <script src="{{ asset('js/client-compress.js') }}"></script>
@endpush
@endsection
