@extends('layouts.admin')
@section('title', 'Review Details')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.teacher-reviews.index') }}">Teacher Reviews</a></li>
                <li class="active">Review Details</li>
            </ol></nav>
            <h1 class="modern-page-title">Review Details</h1>
        </div>
    </div>

    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @php $gradeInfo = $gradeOptions[$teacher_review->grade] ?? ['label' => ucfirst($teacher_review->grade), 'color' => '#6b7280', 'icon' => ''] @endphp

    {{-- Header Info --}}
    <div class="modern-card" style="padding:1.25rem;margin-bottom:1.5rem;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
            <div>
                <h3 style="font-size:1.2rem;font-weight:700;color:#1a1a2e;margin:0;">
                    Review of <strong>{{ $teacher_review->teacher->full_name }}</strong>
                </h3>
                <p style="color:#6b7280;margin:0.25rem 0 0;">
                    {{ $teacher_review->term->name }} &middot; {{ $teacher_review->academicYear->name }}
                </p>
                <p style="color:#6b7280;margin:0.25rem 0 0;font-size:0.85rem;">
                    @if($teacher_review->is_anonymous)
                        <i class="fas fa-user-secret" style="color:#9ca3af;"></i> Submitted Anonymously
                    @else
                        <i class="fas fa-user" style="color:#10b981;"></i> By: {{ $teacher_review->student->full_name ?? 'Unknown' }}
                    @endif
                    &middot; Submitted: {{ $teacher_review->submitted_at?->format('M d, Y H:i') ?? 'N/A' }}
                </p>
            </div>
            <div style="text-align:right;">
                <div style="font-size:2.5rem;font-weight:800;color:{{ $gradeInfo['color'] }};">{{ $teacher_review->overall_score }}%</div>
                <span style="background:{{ $gradeInfo['color'] }}20;color:{{ $gradeInfo['color'] }};padding:0.25rem 1rem;border-radius:99px;font-size:0.9rem;font-weight:600;">{{ $gradeInfo['label'] }}</span>
                @if($teacher_review->status === 'flagged')
                    <span class="modern-badge modern-badge-danger" style="margin-left:0.5rem;">Flagged</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Rating Breakdown --}}
    <div class="modern-card" style="padding:1.25rem;margin-bottom:1.5rem;">
        <h3 style="font-size:1rem;font-weight:700;color:#1a1a2e;margin:0 0 1rem;">
            <i class="fas fa-chart-bar" style="color:#4361ee;"></i> Rating Breakdown
        </h3>
        @foreach($criteriaOptions as $field => $label)
        @php $value = $teacher_review->$field; @endphp
        <div style="margin-bottom:0.75rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.25rem;">
                <span style="font-weight:500;color:#374151;font-size:0.9rem;">{{ $label }}</span>
                <span style="font-weight:700;color:#4361ee;">{{ $value }}/5</span>
            </div>
            <div style="background:#e5e7eb;border-radius:99px;height:10px;overflow:hidden;">
                <div style="background:#4361ee;height:100%;border-radius:99px;width:{{ $value * 20 }}%;transition:width 0.3s;"></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Written Feedback --}}
    <div class="modern-card" style="padding:1.25rem;margin-bottom:1.5rem;">
        <h3 style="font-size:1rem;font-weight:700;color:#1a1a2e;margin:0 0 1rem;">
            <i class="fas fa-comment-dots" style="color:#0ea5e9;"></i> Written Feedback
        </h3>

        @if($teacher_review->strengths)
        <div style="margin-bottom:1rem;">
            <div style="font-weight:600;color:#10b981;margin-bottom:0.25rem;"><i class="fas fa-plus-circle"></i> Strengths</div>
            <p style="color:#374151;margin:0;padding:0.5rem 0.75rem;background:#ecfdf5;border-radius:8px;white-space:pre-wrap;">{{ $teacher_review->strengths }}</p>
        </div>
        @endif

        @if($teacher_review->areas_for_improvement)
        <div style="margin-bottom:1rem;">
            <div style="font-weight:600;color:#f59e0b;margin-bottom:0.25rem;"><i class="fas fa-exclamation-circle"></i> Areas for Improvement</div>
            <p style="color:#374151;margin:0;padding:0.5rem 0.75rem;background:#fef3c7;border-radius:8px;white-space:pre-wrap;">{{ $teacher_review->areas_for_improvement }}</p>
        </div>
        @endif

        @if($teacher_review->additional_comments)
        <div style="margin-bottom:1rem;">
            <div style="font-weight:600;color:#0ea5e9;margin-bottom:0.25rem;"><i class="fas fa-comment"></i> Additional Comments</div>
            <p style="color:#374151;margin:0;padding:0.5rem 0.75rem;background:#f0f9ff;border-radius:8px;white-space:pre-wrap;">{{ $teacher_review->additional_comments }}</p>
        </div>
        @endif

        @if(!$teacher_review->strengths && !$teacher_review->areas_for_improvement && !$teacher_review->additional_comments)
        <p style="color:#9ca3af;font-style:italic;">No written feedback provided.</p>
        @endif
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:0.75rem;">
        <a href="{{ route('admin.teacher-reviews.index') }}" class="btn-modern btn-modern-outline">
            <i class="fas fa-arrow-left"></i> Back to Reviews
        </a>
        @if($teacher_review->status !== 'flagged')
        <form method="POST" action="{{ route('admin.teacher-reviews.flag', $teacher_review) }}">
            @csrf
            <button type="submit" class="btn-modern btn-modern-outline" style="color:#f59e0b;" onclick="return confirm('Flag this review as inappropriate?')">
                <i class="fas fa-flag"></i> Flag Review
            </button>
        </form>
        @else
        <form method="POST" action="{{ route('admin.teacher-reviews.unflag', $teacher_review) }}">
            @csrf
            <button type="submit" class="btn-modern btn-modern-outline" style="color:#10b981;">
                <i class="fas fa-check-circle"></i> Unflag Review
            </button>
        </form>
        @endif
        <form method="POST" action="{{ route('admin.teacher-reviews.destroy', $teacher_review) }}" onsubmit="return confirm('Delete this review permanently?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-modern btn-modern-danger">
                <i class="fas fa-trash"></i> Delete Review
            </button>
        </form>
    </div>
</div>
@endsection
