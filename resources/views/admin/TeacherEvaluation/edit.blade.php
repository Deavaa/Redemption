@extends('layouts.admin')
@section('title', 'Edit Evaluation')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.teacher-evaluations.index') }}">Evaluations</a></li><li class="active">Edit</li></ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.teacher-evaluations.show', $teacherEvaluation->id) }}" class="btn-modern btn-modern-outline"><i class="fas fa-eye"></i><span>View</span></a>
        </div>
    </div>

    @if($errors->any())
    <div class="modern-alert modern-alert-error" style="margin-bottom:1rem;"><i class="fas fa-exclamation-circle"></i><span>Please fix the errors below.</span></div>
    @endif

    <div class="modern-card">
        <form method="POST" action="{{ route('admin.teacher-evaluations.update', $teacherEvaluation->id) }}">
            @csrf @method('PUT')
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue"><i class="fas fa-user-check"></i></div>
                    <div><h3 class="modern-form-section-title">Evaluation Details</h3></div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Teacher <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper"><i class="fas fa-user modern-input-icon"></i>
                                <select name="teacher_id" class="modern-input modern-select" required>
                                    @foreach($teachers as $t)
                                    <option value="{{ $t->id }}" {{ old('teacher_id', $teacherEvaluation->teacher_id) == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Evaluation Type <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper"><i class="fas fa-tag modern-input-icon"></i>
                                <select name="evaluation_type" class="modern-input modern-select" required>
                                    @foreach(['periodic'=>'Periodic Review','annual'=>'Annual Evaluation','observation'=>'Classroom Observation','peer_review'=>'Peer Review'] as $v=>$l)
                                    <option value="{{ $v }}" {{ old('evaluation_type', $teacherEvaluation->evaluation_type) == $v ? 'selected' : '' }}>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Evaluation Date <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper"><i class="fas fa-calendar modern-input-icon"></i><input type="date" name="evaluation_date" class="modern-input" value="{{ old('evaluation_date', $teacherEvaluation->evaluation_date->format('Y-m-d')) }}" required></div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Academic Year</label>
                            <div class="modern-input-wrapper"><i class="fas fa-calendar-alt modern-input-icon"></i>
                                <select name="academic_year_id" class="modern-input modern-select">
                                    <option value="">-- Select --</option>
                                    @foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ old('academic_year_id', $teacherEvaluation->academic_year_id) == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green"><i class="fas fa-star"></i></div>
                    <div><h3 class="modern-form-section-title">Performance Criteria (1-5)</h3></div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        @foreach(['teaching_quality'=>'Teaching Quality','student_engagement'=>'Student Engagement','classroom_management'=>'Classroom Management','lesson_preparation'=>'Lesson Preparation','professional_conduct'=>'Professional Conduct','communication_skills'=>'Communication Skills','punctuality'=>'Punctuality & Attendance','student_results'=>'Student Results'] as $field=>$label)
                        <div class="modern-form-group">
                            <label class="modern-form-label">{{ $label }} <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper"><i class="fas fa-star modern-input-icon"></i>
                                <select name="{{ $field }}" class="modern-input modern-select" required>
                                    @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ old($field, $teacherEvaluation->$field) == $i ? 'selected' : '' }}>{{ $i }} - {{ ['Poor','Below Average','Satisfactory','Good','Excellent'][$i-1] }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-purple"><i class="fas fa-comment-dots"></i></div>
                    <div><h3 class="modern-form-section-title">Comments & Feedback</h3></div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group"><label class="modern-form-label">Strengths</label><textarea name="strengths" class="modern-input modern-textarea" rows="3">{{ old('strengths', $teacherEvaluation->strengths) }}</textarea></div>
                        <div class="modern-form-group"><label class="modern-form-label">Areas for Improvement</label><textarea name="areas_for_improvement" class="modern-input modern-textarea" rows="3">{{ old('areas_for_improvement', $teacherEvaluation->areas_for_improvement) }}</textarea></div>
                        <div class="modern-form-group"><label class="modern-form-label">Recommendations</label><textarea name="recommendations" class="modern-input modern-textarea" rows="3">{{ old('recommendations', $teacherEvaluation->recommendations) }}</textarea></div>
                        <div class="modern-form-group"><label class="modern-form-label">Additional Comments</label><textarea name="comments" class="modern-input modern-textarea" rows="3">{{ old('comments', $teacherEvaluation->comments) }}</textarea></div>
                    </div>
                </div>
            </div>
            <div class="modern-form-actions">
                <a href="{{ route('admin.teacher-evaluations.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-check"></i> Update Evaluation</button>
            </div>
        </form>
    </div>
</div>
@push('styles')
<style>
.modern-page{animation:fadeSlideIn .4s ease-out}@keyframes fadeSlideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}.modern-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem}.modern-page-header-left{flex:1}.modern-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}.modern-breadcrumb li{color:#adb5bd}.modern-breadcrumb li a{color:#6c757d;text-decoration:none}.modern-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.modern-breadcrumb li.active{color:#4361ee;font-weight:500}.modern-alert{display:flex;align-items:flex-start;gap:.65rem;padding:.85rem 1.25rem;border-radius:10px;font-size:.88rem;font-weight:500}.modern-alert-error{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}.modern-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden}.modern-form-section{border-bottom:1px solid #f0f0f0}.modern-form-section:last-of-type{border-bottom:none}.modern-form-section-header{display:flex;align-items:center;gap:1rem;padding:1.5rem 2rem .75rem}.modern-form-section-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}.modern-form-section-icon-blue{background:#eef2ff;color:#4361ee}.modern-form-section-icon-green{background:#ecfdf5;color:#10b981}.modern-form-section-icon-purple{background:#f5f3ff;color:#7c3aed}.modern-form-section-title{font-size:1.05rem;font-weight:700;color:#1a1a2e;margin:0}.modern-form-section-body{padding:1.25rem 2rem 1.75rem}.modern-form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem}.modern-form-group{display:flex;flex-direction:column}.modern-form-label{font-weight:600;color:#374151;margin-bottom:.45rem;font-size:.88rem}.modern-required{color:#ef4444;font-weight:700}.modern-input-wrapper{position:relative}.modern-input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.85rem;pointer-events:none;z-index:1}.modern-input{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.7rem .9rem .7rem 2.5rem;font-size:.9rem;color:#1a1a2e;background:#fff;transition:all .2s}.modern-input:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}.modern-textarea{resize:vertical;min-height:80px}.modern-select{appearance:none;cursor:pointer;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .75rem center;background-repeat:no-repeat;background-size:1.25rem;padding-right:2.5rem}.modern-form-actions{display:flex;justify-content:flex-end;gap:.75rem;padding:1.5rem 2rem;border-top:1px solid #f0f0f0;background:#fafbfc}.btn-modern{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.35rem;border-radius:10px;font-weight:600;font-size:.9rem;text-decoration:none;border:none;cursor:pointer;transition:all .25s}.btn-modern-primary{background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;box-shadow:0 2px 8px rgba(67,97,238,.3)}.btn-modern-primary:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4);color:#fff}.btn-modern-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb}.btn-modern-outline:hover{border-color:#4361ee;color:#4361ee}.btn-modern-ghost{background:transparent;color:#6b7280;padding:.65rem 1rem}.btn-modern-ghost:hover{color:#1a1a2e;background:#f3f4f6}@media(max-width:768px){.modern-form-grid{grid-template-columns:1fr}.modern-form-actions{flex-direction:column}.btn-modern{justify-content:center;width:100%}}
</style>
@endpush
@endsection
