@extends('layouts.admin')
@section('title', 'Add Teacher Assignment')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li>Academics</li>
                    <li><a href="{{ route('admin.teacher-assignments.index') }}">Teacher Assignments</a></li>
                    <li class="active">Add New</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Add Teacher Assignment</h1>
            <p class="modern-page-subtitle">Assign a teacher to a class, section, and subject</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.teacher-assignments.index') }}" class="btn-modern btn-modern-ghost">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.teacher-assignments.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon modern-form-section-icon-purple">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">Assignment Details</h3>
                    <p class="modern-form-section-desc">Select the teacher, class, section, subject and academic year</p>
                </div>
            </div>
            <div class="modern-form-section-body">
                <div class="modern-form-grid">
                    <div class="modern-form-group">
                        <label class="modern-form-label">Teacher <span class="modern-required">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-user-tie modern-input-icon"></i>
                            <input type="text" name="teacher_id" class="modern-input" value="{{ old('teacher_id') }}" placeholder="Enter teacher ID" required>
                        </div>
                        @error('teacher_id')
                            <div class="modern-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label">Class <span class="modern-required">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-school modern-input-icon"></i>
                            <input type="text" name="class_id" class="modern-input" value="{{ old('class_id') }}" placeholder="Enter class ID" required>
                        </div>
                        @error('class_id')
                            <div class="modern-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label">Section <span class="modern-required">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-layer-group modern-input-icon"></i>
                            <input type="text" name="section_id" class="modern-input" value="{{ old('section_id') }}" placeholder="Enter section ID" required>
                        </div>
                        @error('section_id')
                            <div class="modern-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label">Subject <span class="modern-required">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-book modern-input-icon"></i>
                            <input type="text" name="subject_id" class="modern-input" value="{{ old('subject_id') }}" placeholder="Enter subject ID" required>
                        </div>
                        @error('subject_id')
                            <div class="modern-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modern-form-group modern-form-span-2">
                        <label class="modern-form-label">Academic Year <span class="modern-required">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-calendar-alt modern-input-icon"></i>
                            <input type="text" name="academic_year_id" class="modern-input" value="{{ old('academic_year_id') }}" placeholder="Enter academic year ID" required>
                        </div>
                        @error('academic_year_id')
                            <div class="modern-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="modern-form-actions">
            <a href="{{ route('admin.teacher-assignments.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
            <button type="submit" class="btn-modern btn-modern-primary">
                <i class="fas fa-save"></i> Save Assignment
            </button>
        </div>
    </form>
</div>

@push('styles')
<style>
@keyframes fadeSlideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.modern-page{animation:fadeSlideIn .4s ease-out;padding:1.5rem}
.modern-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;gap:1rem}
.modern-page-header-left{flex:1}
.modern-page-header-right{display:flex;align-items:center;gap:.75rem;flex-shrink:0}
.modern-page-title{font-size:1.75rem;font-weight:700;color:#1e293b;margin:0}
.modern-page-subtitle{font-size:.875rem;color:#64748b;margin:.25rem 0 0}
.modern-breadcrumb{margin-bottom:.5rem}
.modern-breadcrumb ol{display:flex;align-items:center;list-style:none;padding:0;margin:0;gap:.25rem;font-size:.8rem}
.modern-breadcrumb li{color:#94a3b8}
.modern-breadcrumb li:not(:last-child)::after{content:'/';margin-left:.25rem;color:#cbd5e1}
.modern-breadcrumb li a{color:#64748b;text-decoration:none;transition:color .2s}
.modern-breadcrumb li a:hover{color:#4361ee}
.modern-breadcrumb li.active{color:#4361ee;font-weight:600}
.modern-form-section{background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.5rem}
.modern-form-section-header{display:flex;align-items:center;gap:1rem;padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9;background:#f8fafc}
.modern-form-section-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.modern-form-section-icon-blue{background:rgba(67,97,238,.1);color:#4361ee}
.modern-form-section-icon-green{background:rgba(16,185,129,.1);color:#10b981}
.modern-form-section-icon-gold{background:rgba(245,158,11,.1);color:#f59e0b}
.modern-form-section-icon-purple{background:rgba(139,92,246,.1);color:#8b5cf6}
.modern-form-section-title{font-size:1.05rem;font-weight:600;color:#1e293b;margin:0}
.modern-form-section-desc{font-size:.8rem;color:#94a3b8;margin:.2rem 0 0}
.modern-form-section-body{padding:1.5rem}
.modern-form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem}
.modern-form-span-2{grid-column:span 2}
.modern-form-group{display:flex;flex-direction:column;gap:.375rem}
.modern-form-label{font-size:.85rem;font-weight:500;color:#475569}
.modern-required{color:#ef4444}
.modern-input-wrapper{position:relative;display:flex;align-items:center}
.modern-input-icon{position:absolute;left:.875rem;color:#94a3b8;font-size:.85rem;pointer-events:none;z-index:1}
.modern-input{width:100%;padding:.65rem .875rem .65rem 2.5rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;outline:none;transition:border-color .2s,box-shadow .2s;background:#fff;color:#1e293b}
.modern-input:focus{border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.modern-input::placeholder{color:#94a3b8}
.modern-textarea{width:100%;padding:.65rem .875rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;outline:none;transition:border-color .2s,box-shadow .2s;background:#fff;color:#1e293b;resize:vertical;font-family:inherit}
.modern-textarea:focus{border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.modern-textarea::placeholder{color:#94a3b8}
.modern-select{width:100%;padding:.65rem .875rem .65rem 2.5rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;outline:none;transition:border-color .2s,box-shadow .2s;background:#fff;color:#1e293b;appearance:none;cursor:pointer}
.modern-select:focus{border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.modern-form-error{font-size:.8rem;color:#ef4444;margin-top:.25rem}
.modern-form-actions{display:flex;justify-content:flex-end;gap:.75rem;padding-top:.5rem}
.btn-modern{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:8px;font-size:.875rem;font-weight:500;cursor:pointer;transition:all .2s;border:none;text-decoration:none;line-height:1.4}
.btn-modern-primary{background:#4361ee;color:#fff;box-shadow:0 1px 3px rgba(67,97,238,.3)}
.btn-modern-primary:hover{background:#3a0ca3;box-shadow:0 4px 12px rgba(67,97,238,.4)}
.btn-modern-outline{background:transparent;color:#4361ee;border:1px solid #4361ee}
.btn-modern-outline:hover{background:#4361ee;color:#fff}
.btn-modern-ghost{background:transparent;color:#64748b}
.btn-modern-ghost:hover{background:#f1f5f9;color:#1e293b}
@media(max-width:768px){
.modern-page{padding:1rem}
.modern-page-header{flex-direction:column;gap:.75rem}
.modern-page-header-right{width:100%;justify-content:flex-start}
.modern-page-title{font-size:1.4rem}
.modern-form-grid{grid-template-columns:1fr}
.modern-form-span-2{grid-column:span 1}
.modern-form-actions{flex-direction:column}
.modern-form-actions .btn-modern{width:100%;justify-content:center}
}
@media(max-width:480px){
.btn-modern{padding:.5rem 1rem;font-size:.8rem}
}
</style>
@endpush
@endsection
