@extends('layouts.admin')
@section('title', 'Evaluation Detail')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.teacher-evaluations.index') }}">Evaluations</a></li><li class="active">{{ $teacherEvaluation->teacher->full_name ?? 'Detail' }}</li></ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.teacher-evaluations.index') }}" class="btn-modern btn-modern-outline"><i class="fas fa-arrow-left"></i><span>Back</span></a>
        </div>
    </div>

    {{-- Score Banner --}}
    <div class="eq-status-banner" style="background:{{ $teacherEvaluation->overall_score >= 75 ? '#ecfdf5' : ($teacherEvaluation->overall_score >= 50 ? '#fffbeb' : '#fef2f2') }};color:{{ $teacherEvaluation->overall_score >= 75 ? '#065f46' : ($teacherEvaluation->overall_score >= 50 ? '#92400e' : '#991b1b') }};">
        <div class="eq-status-icon" style="background:{{ $teacherEvaluation->overall_score >= 75 ? '#a7f3d0' : ($teacherEvaluation->overall_score >= 50 ? '#fde68a' : '#fecaca') }};">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="eq-status-info">
            <h3>Overall Score: {{ $teacherEvaluation->overall_score }}/100 — {{ $teacherEvaluation->grade_label }}</h3>
            <p>{{ $teacherEvaluation->evaluation_type_label }} on {{ $teacherEvaluation->evaluation_date->format('M d, Y') }} by {{ $teacherEvaluation->evaluator->name ?? 'Unknown' }}</p>
        </div>
    </div>

    <div class="eq-detail-grid" style="display:grid;grid-template-columns:1fr 360px;gap:1.5rem;">
        <div>
            {{-- Criteria Breakdown --}}
            <div class="modern-card">
                <div class="modern-card-header"><h2 class="modern-card-title">Performance Breakdown</h2></div>
                <div style="padding:1.25rem 1.5rem;">
                    @php
                        $criteria = [
                            'teaching_quality' => 'Teaching Quality',
                            'student_engagement' => 'Student Engagement',
                            'classroom_management' => 'Classroom Management',
                            'lesson_preparation' => 'Lesson Preparation',
                            'professional_conduct' => 'Professional Conduct',
                            'communication_skills' => 'Communication Skills',
                            'punctuality' => 'Punctuality & Attendance',
                            'student_results' => 'Student Results',
                        ];
                    @endphp
                    @foreach($criteria as $field => $label)
                    <div style="display:flex;align-items:center;gap:1rem;padding:.6rem 0;border-bottom:1px solid #f3f4f6;">
                        <span style="flex:1;font-size:.88rem;font-weight:500;color:#374151;">{{ $label }}</span>
                        <div style="width:120px;height:8px;background:#f3f4f6;border-radius:4px;overflow:hidden;">
                            <div style="width:{{ ($teacherEvaluation->$field / 5) * 100 }}%;height:100%;background:{{ $teacherEvaluation->$field >= 4 ? '#10b981' : ($teacherEvaluation->$field >= 3 ? '#d97706' : '#dc2626') }};border-radius:4px;"></div>
                        </div>
                        <span style="font-weight:700;font-size:.95rem;color:#1a1a2e;min-width:30px;text-align:right;">{{ $teacherEvaluation->$field }}/5</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Comments --}}
            @if($teacherEvaluation->strengths || $teacherEvaluation->areas_for_improvement || $teacherEvaluation->recommendations || $teacherEvaluation->comments)
            <div class="modern-card">
                <div class="modern-card-header"><h2 class="modern-card-title">Feedback</h2></div>
                <div style="padding:1.25rem 1.5rem;">
                    @if($teacherEvaluation->strengths)
                    <div style="margin-bottom:1rem;"><strong style="color:#059669;">Strengths:</strong><p style="margin:.35rem 0 0;color:#374151;font-size:.88rem;line-height:1.6;">{{ $teacherEvaluation->strengths }}</p></div>
                    @endif
                    @if($teacherEvaluation->areas_for_improvement)
                    <div style="margin-bottom:1rem;"><strong style="color:#d97706;">Areas for Improvement:</strong><p style="margin:.35rem 0 0;color:#374151;font-size:.88rem;line-height:1.6;">{{ $teacherEvaluation->areas_for_improvement }}</p></div>
                    @endif
                    @if($teacherEvaluation->recommendations)
                    <div style="margin-bottom:1rem;"><strong style="color:#4361ee;">Recommendations:</strong><p style="margin:.35rem 0 0;color:#374151;font-size:.88rem;line-height:1.6;">{{ $teacherEvaluation->recommendations }}</p></div>
                    @endif
                    @if($teacherEvaluation->comments)
                    <div><strong style="color:#6b7280;">Additional Comments:</strong><p style="margin:.35rem 0 0;color:#374151;font-size:.88rem;line-height:1.6;">{{ $teacherEvaluation->comments }}</p></div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div>
            <div class="modern-card">
                <div class="modern-card-header"><h2 class="modern-card-title">Quick Info</h2></div>
                <div style="padding:1.25rem 1.5rem;">
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f3f4f6;font-size:.88rem;"><span style="color:#6b7280;">Teacher</span><span style="font-weight:600;color:#1a1a2e;">{{ $teacherEvaluation->teacher->full_name ?? '-' }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f3f4f6;font-size:.88rem;"><span style="color:#6b7280;">Type</span><span style="font-weight:600;">{{ $teacherEvaluation->evaluation_type_label }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f3f4f6;font-size:.88rem;"><span style="color:#6b7280;">Date</span><span style="font-weight:600;">{{ $teacherEvaluation->evaluation_date->format('M d, Y') }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f3f4f6;font-size:.88rem;"><span style="color:#6b7280;">Evaluator</span><span style="font-weight:600;">{{ $teacherEvaluation->evaluator->name ?? '-' }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;font-size:.88rem;"><span style="color:#6b7280;">Grade</span><span class="modern-badge {{ $teacherEvaluation->grade_badge }}">{{ $teacherEvaluation->grade_label }}</span></div>
                </div>
            </div>
            <div class="modern-card" style="margin-top:1rem;">
                <div style="padding:1.25rem 1.5rem;">
                    <a href="{{ route('admin.teacher-evaluations.edit', $teacherEvaluation->id) }}" class="btn-modern btn-modern-outline" style="width:100%;justify-content:center;margin-bottom:.5rem;"><i class="fas fa-pen"></i> Edit</a>
                    <form method="POST" action="{{ route('admin.teacher-evaluations.destroy', $teacherEvaluation->id) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-modern btn-modern-ghost" style="width:100%;justify-content:center;color:#dc2626;"><i class="fas fa-trash-alt"></i> Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.modern-page{animation:fadeSlideIn .4s ease-out}@keyframes fadeSlideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}.modern-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem}.modern-page-header-left{flex:1}.modern-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}.modern-breadcrumb li{color:#adb5bd}.modern-breadcrumb li a{color:#6c757d;text-decoration:none}.modern-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.modern-breadcrumb li.active{color:#4361ee;font-weight:500}.eq-status-banner{display:flex;align-items:center;gap:1rem;padding:1.25rem 1.5rem;border-radius:14px;margin-bottom:1.5rem}.eq-status-icon{width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}.eq-status-info h3{margin:0;font-size:1.1rem;font-weight:700}.eq-status-info p{margin:.25rem 0 0;font-size:.85rem;opacity:.8}.modern-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.25rem}.modern-card-header{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid #f0f0f0}.modern-card-title{font-size:1.1rem;font-weight:700;color:#1a1a2e;margin:0}.modern-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;border-radius:50px;font-size:.75rem;font-weight:600}.modern-badge-success{background:#ecfdf5;color:#059669}.modern-badge-warning{background:#fffbeb;color:#d97706}.modern-badge-danger{background:#fef2f2;color:#dc2626}.modern-badge-info{background:#eff6ff;color:#2563eb}.modern-badge-light{background:#f3f4f6;color:#6b7280}.btn-modern{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.35rem;border-radius:10px;font-weight:600;font-size:.9rem;text-decoration:none;border:none;cursor:pointer;transition:all .25s}.btn-modern-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb}.btn-modern-outline:hover{border-color:#4361ee;color:#4361ee}.btn-modern-ghost{background:transparent;color:#6b7280;padding:.65rem 1rem}.btn-modern-ghost:hover{background:#f3f4f6}@media(max-width:768px){.eq-detail-grid{grid-template-columns:1fr!important}.eq-status-banner{flex-direction:column;text-align:center}}
</style>
@endpush
@endsection
