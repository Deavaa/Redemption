@extends('layouts.admin')
@section('title', 'Assessment Detail')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.teacher-efficiency.index') }}">Teacher Efficiency</a></li><li class="active">{{ $teacherEfficiencyAssessment->teacher->full_name ?? 'Detail' }}</li></ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.teacher-efficiency.index') }}" class="btn-modern btn-modern-outline"><i class="fas fa-arrow-left"></i><span>Back</span></a>
        </div>
    </div>

    {{-- Score Banner --}}
    <div class="eq-status-banner" style="background:{{ $teacherEfficiencyAssessment->overall_score >= 75 ? '#ecfdf5' : ($teacherEfficiencyAssessment->overall_score >= 50 ? '#fffbeb' : '#fef2f2') }};color:{{ $teacherEfficiencyAssessment->overall_score >= 75 ? '#065f46' : ($teacherEfficiencyAssessment->overall_score >= 50 ? '#92400e' : '#991b1b') }};">
        <div class="eq-status-icon" style="background:{{ $teacherEfficiencyAssessment->overall_score >= 75 ? '#a7f3d0' : ($teacherEfficiencyAssessment->overall_score >= 50 ? '#fde68a' : '#fecaca') }};">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="eq-status-info">
            <h3>Overall Score: {{ $teacherEfficiencyAssessment->overall_score }}/100 — {{ $teacherEfficiencyAssessment->grade_label }}</h3>
            <p>Assessed by {{ $teacherEfficiencyAssessment->assessor->name ?? 'Unknown' }} on {{ $teacherEfficiencyAssessment->created_at->format('M d, Y') }}
            @if($teacherEfficiencyAssessment->term)
            &bull; {{ $teacherEfficiencyAssessment->term->name }}
            @endif
            </p>
        </div>
        <div style="margin-left:auto;display:flex;gap:.5rem;">
            @if($teacherEfficiencyAssessment->is_locked)
            <span class="modern-badge modern-badge-warning"><i class="fas fa-lock me-1"></i>Locked</span>
            @endif
            @if($teacherEfficiencyAssessment->status === 'draft')
            <span class="modern-badge modern-badge-light">Draft</span>
            @elseif($teacherEfficiencyAssessment->status === 'completed')
            <span class="modern-badge modern-badge-success">Completed</span>
            @elseif($teacherEfficiencyAssessment->status === 'acknowledged')
            <span class="modern-badge modern-badge-info">Acknowledged</span>
            @endif
        </div>
    </div>

    <div class="eq-detail-grid" style="display:grid;grid-template-columns:1fr 360px;gap:1.5rem;">
        <div>
            {{-- Criteria Breakdown --}}
            <div class="modern-card">
                <div class="modern-card-header"><h2 class="modern-card-title">Criteria Breakdown</h2></div>
                <div style="padding:1.25rem 1.5rem;">
                    @foreach($criteriaScores as $field => $data)
                    <div style="display:flex;align-items:center;gap:1rem;padding:.6rem 0;border-bottom:1px solid #f3f4f6;">
                        <span style="flex:1;font-size:.88rem;font-weight:500;color:#374151;">{{ $data['label'] }}</span>
                        <div style="width:120px;height:8px;background:#f3f4f6;border-radius:4px;overflow:hidden;">
                            <div style="width:{{ $data['percentage'] }}%;height:100%;background:{{ $data['score'] >= 4 ? '#10b981' : ($data['score'] >= 3 ? '#d97706' : '#dc2626') }};border-radius:4px;transition:width .5s ease;"></div>
                        </div>
                        <span style="font-weight:700;font-size:.95rem;color:#1a1a2e;min-width:30px;text-align:right;">{{ $data['score'] }}/5</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Comments --}}
            @if($teacherEfficiencyAssessment->strengths || $teacherEfficiencyAssessment->areas_for_improvement || $teacherEfficiencyAssessment->action_plan || $teacherEfficiencyAssessment->comments)
            <div class="modern-card">
                <div class="modern-card-header"><h2 class="modern-card-title">Feedback & Comments</h2></div>
                <div style="padding:1.25rem 1.5rem;">
                    @if($teacherEfficiencyAssessment->strengths)
                    <div style="margin-bottom:1rem;"><strong style="color:#059669;"><i class="fas fa-check-circle me-1"></i>Strengths:</strong><p style="margin:.35rem 0 0;color:#374151;font-size:.88rem;line-height:1.6;">{{ $teacherEfficiencyAssessment->strengths }}</p></div>
                    @endif
                    @if($teacherEfficiencyAssessment->areas_for_improvement)
                    <div style="margin-bottom:1rem;"><strong style="color:#d97706;"><i class="fas fa-exclamation-circle me-1"></i>Areas for Improvement:</strong><p style="margin:.35rem 0 0;color:#374151;font-size:.88rem;line-height:1.6;">{{ $teacherEfficiencyAssessment->areas_for_improvement }}</p></div>
                    @endif
                    @if($teacherEfficiencyAssessment->action_plan)
                    <div style="margin-bottom:1rem;"><strong style="color:#4361ee;"><i class="fas fa-tasks me-1"></i>Action Plan:</strong><p style="margin:.35rem 0 0;color:#374151;font-size:.88rem;line-height:1.6;">{{ $teacherEfficiencyAssessment->action_plan }}</p></div>
                    @endif
                    @if($teacherEfficiencyAssessment->comments)
                    <div><strong style="color:#6b7280;"><i class="fas fa-comment me-1"></i>General Comments:</strong><p style="margin:.35rem 0 0;color:#374151;font-size:.88rem;line-height:1.6;">{{ $teacherEfficiencyAssessment->comments }}</p></div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div>
            <div class="modern-card">
                <div class="modern-card-header"><h2 class="modern-card-title">Assessment Info</h2></div>
                <div style="padding:1.25rem 1.5rem;">
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f3f4f6;font-size:.88rem;"><span style="color:#6b7280;">Teacher</span><span style="font-weight:600;color:#1a1a2e;">{{ $teacherEfficiencyAssessment->teacher->full_name ?? '-' }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f3f4f6;font-size:.88rem;"><span style="color:#6b7280;">Branch</span><span style="font-weight:600;">{{ $teacherEfficiencyAssessment->branch->name ?? '-' }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f3f4f6;font-size:.88rem;"><span style="color:#6b7280;">Academic Year</span><span style="font-weight:600;">{{ $teacherEfficiencyAssessment->academicYear->name ?? '-' }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f3f4f6;font-size:.88rem;"><span style="color:#6b7280;">Term</span><span style="font-weight:600;">{{ $teacherEfficiencyAssessment->term->name ?? '-' }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f3f4f6;font-size:.88rem;"><span style="color:#6b7280;">Assessor</span><span style="font-weight:600;">{{ $teacherEfficiencyAssessment->assessor->name ?? '-' }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f3f4f6;font-size:.88rem;"><span style="color:#6b7280;">Date</span><span style="font-weight:600;">{{ $teacherEfficiencyAssessment->created_at->format('M d, Y') }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f3f4f6;font-size:.88rem;"><span style="color:#6b7280;">Score</span><span style="font-weight:700;color:#4361ee;">{{ $teacherEfficiencyAssessment->overall_score }}/100</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;font-size:.88rem;"><span style="color:#6b7280;">Grade</span><span class="modern-badge {{ $teacherEfficiencyAssessment->grade_badge_class }}">{{ $teacherEfficiencyAssessment->grade_label }}</span></div>
                    @if($teacherEfficiencyAssessment->acknowledged_at)
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-top:1px solid #f3f4f6;margin-top:.5rem;font-size:.82rem;"><span style="color:#6b7280;">Acknowledged</span><span style="font-weight:500;color:#2563eb;">{{ $teacherEfficiencyAssessment->acknowledged_at->format('M d, Y H:i') }}</span></div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="modern-card" style="margin-top:1rem;">
                <div style="padding:1.25rem 1.5rem;">
                    @if($teacherEfficiencyAssessment->status === 'draft' && !$teacherEfficiencyAssessment->is_locked)
                    <a href="{{ route('admin.teacher-efficiency.edit', $teacherEfficiencyAssessment->id) }}" class="btn-modern btn-modern-outline" style="width:100%;justify-content:center;margin-bottom:.5rem;"><i class="fas fa-pen"></i> Edit Draft</a>
                    <form method="POST" action="{{ route('admin.teacher-efficiency.destroy', $teacherEfficiencyAssessment->id) }}" onsubmit="return confirm('Delete this assessment?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-modern btn-modern-ghost" style="width:100%;justify-content:center;color:#dc2626;"><i class="fas fa-trash-alt"></i> Delete</button>
                    </form>
                    @endif

                    @if($teacherEfficiencyAssessment->status === 'completed' && !$teacherEfficiencyAssessment->is_locked)
                    <form method="POST" action="{{ route('admin.teacher-efficiency.acknowledge', $teacherEfficiencyAssessment->id) }}" style="margin-bottom:.5rem;">
                        @csrf
                        <button type="submit" class="btn-modern btn-modern-primary" style="width:100%;justify-content:center;"><i class="fas fa-check-circle"></i> Acknowledge</button>
                    </form>
                    @endif

                    @if(!$teacherEfficiencyAssessment->is_locked && in_array(auth()->user()->role, ['admin', 'super_admin', 'branch_principal']))
                    <form method="POST" action="{{ route('admin.teacher-efficiency.lock', $teacherEfficiencyAssessment->id) }}" onsubmit="return confirm('Lock this assessment? The teacher will only be able to view it.')">
                        @csrf
                        <button type="submit" class="btn-modern btn-modern-outline" style="width:100%;justify-content:center;margin-top:.5rem;"><i class="fas fa-lock"></i> Lock Assessment</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.modern-page{animation:fadeSlideIn .4s ease-out}@keyframes fadeSlideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}.modern-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem}.modern-page-header-left{flex:1}.modern-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}.modern-breadcrumb li{color:#adb5bd}.modern-breadcrumb li a{color:#6c757d;text-decoration:none}.modern-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.modern-breadcrumb li.active{color:#4361ee;font-weight:500}.eq-status-banner{display:flex;align-items:center;gap:1rem;padding:1.25rem 1.5rem;border-radius:14px;margin-bottom:1.5rem;flex-wrap:wrap}.eq-status-icon{width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}.eq-status-info h3{margin:0;font-size:1.1rem;font-weight:700}.eq-status-info p{margin:.25rem 0 0;font-size:.85rem;opacity:.8}.modern-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.25rem}.modern-card-header{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid #f0f0f0}.modern-card-title{font-size:1.1rem;font-weight:700;color:#1a1a2e;margin:0}.modern-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;border-radius:50px;font-size:.75rem;font-weight:600}.modern-badge-success{background:#ecfdf5;color:#059669}.modern-badge-warning{background:#fffbeb;color:#d97706}.modern-badge-danger{background:#fef2f2;color:#dc2626}.modern-badge-info{background:#eff6ff;color:#2563eb}.modern-badge-light{background:#f3f4f6;color:#6b7280}.btn-modern{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.35rem;border-radius:10px;font-weight:600;font-size:.9rem;text-decoration:none;border:none;cursor:pointer;transition:all .25s}.btn-modern-primary{background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;box-shadow:0 2px 8px rgba(67,97,238,.3)}.btn-modern-primary:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4);color:#fff}.btn-modern-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb}.btn-modern-outline:hover{border-color:#4361ee;color:#4361ee}.btn-modern-ghost{background:transparent;color:#6b7280;padding:.65rem 1rem}.btn-modern-ghost:hover{background:#f3f4f6}@media(max-width:768px){.eq-detail-grid{grid-template-columns:1fr!important}.eq-status-banner{flex-direction:column;text-align:center}}
</style>
@endpush
@endsection
