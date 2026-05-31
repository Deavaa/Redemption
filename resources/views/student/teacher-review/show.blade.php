@extends('student.layout')
@section('title', 'Review Details')

@section('content')
<div style="padding:1.5rem 2rem;">
    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('student.teacher-review.index') }}" style="color:var(--primary);text-decoration:none;font-weight:600;">
            <i class="fas fa-arrow-left"></i> Back to Reviews
        </a>
    </div>

    @php $gradeInfo = $gradeOptions[$teacher_review->grade] ?? ['label' => ucfirst($teacher_review->grade), 'color' => '#6b7280', 'icon' => ''] @endphp

    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;">
        <div>
            <h2 style="font-size:1.5rem;font-weight:700;color:var(--text-dark);margin:0;">
                Review of {{ $teacher_review->teacher->full_name }}
            </h2>
            <p style="color:var(--text-muted);margin:0.25rem 0 0;">
                {{ $teacher_review->term->name }} &middot; {{ $teacher_review->academicYear->name }}
            </p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:2rem;font-weight:800;color:{{ $gradeInfo['color'] }};">{{ $teacher_review->overall_score }}%</div>
            <span style="background:{{ $gradeInfo['color'] }}20;color:{{ $gradeInfo['color'] }};padding:0.2rem 0.75rem;border-radius:99px;font-size:0.85rem;font-weight:600;">{{ $gradeInfo['label'] }}</span>
        </div>
    </div>

    {{-- Rating Details --}}
    <div style="background:var(--card-bg);border-radius:var(--radius);padding:1.25rem;margin-bottom:1.5rem;box-shadow:var(--shadow);">
        <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-dark);margin:0 0 1rem;">
            <i class="fas fa-chart-bar" style="color:var(--primary);"></i> Rating Breakdown
        </h3>

        @foreach($criteriaOptions as $field => $label)
        @php $value = $teacher_review->$field; @endphp
        <div style="margin-bottom:0.75rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.25rem;">
                <span style="font-weight:500;color:var(--text-dark);font-size:0.9rem;">{{ $label }}</span>
                <span style="font-weight:700;color:var(--primary);">{{ $value }}/5</span>
            </div>
            <div style="background:#e5e7eb;border-radius:99px;height:8px;overflow:hidden;">
                <div style="background:var(--primary);height:100%;border-radius:99px;width:{{ $value * 20 }}%;transition:width 0.3s;"></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Written Feedback --}}
    <div style="background:var(--card-bg);border-radius:var(--radius);padding:1.25rem;margin-bottom:1.5rem;box-shadow:var(--shadow);">
        <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-dark);margin:0 0 1rem;">
            <i class="fas fa-comment-dots" style="color:var(--accent);"></i> Written Feedback
        </h3>

        @if($teacher_review->strengths)
        <div style="margin-bottom:1rem;">
            <div style="font-weight:600;color:var(--success);margin-bottom:0.25rem;"><i class="fas fa-plus-circle"></i> Strengths</div>
            <p style="color:var(--text);margin:0;padding:0.5rem 0.75rem;background:var(--success-light);border-radius:var(--radius-sm);white-space:pre-wrap;">{{ $teacher_review->strengths }}</p>
        </div>
        @endif

        @if($teacher_review->areas_for_improvement)
        <div style="margin-bottom:1rem;">
            <div style="font-weight:600;color:var(--warning);margin-bottom:0.25rem;"><i class="fas fa-exclamation-circle"></i> Areas for Improvement</div>
            <p style="color:var(--text);margin:0;padding:0.5rem 0.75rem;background:var(--warning-light);border-radius:var(--radius-sm);white-space:pre-wrap;">{{ $teacher_review->areas_for_improvement }}</p>
        </div>
        @endif

        @if($teacher_review->additional_comments)
        <div style="margin-bottom:1rem;">
            <div style="font-weight:600;color:var(--accent);margin-bottom:0.25rem;"><i class="fas fa-comment"></i> Additional Comments</div>
            <p style="color:var(--text);margin:0;padding:0.5rem 0.75rem;background:var(--accent-light);border-radius:var(--radius-sm);white-space:pre-wrap;">{{ $teacher_review->additional_comments }}</p>
        </div>
        @endif

        @if(!$teacher_review->strengths && !$teacher_review->areas_for_improvement && !$teacher_review->additional_comments)
        <p style="color:var(--text-muted);font-style:italic;">No written feedback provided.</p>
        @endif
    </div>

    {{-- Review Meta --}}
    <div style="background:var(--card-bg);border-radius:var(--radius);padding:1.25rem;box-shadow:var(--shadow);">
        <div style="display:flex;gap:2rem;flex-wrap:wrap;font-size:0.85rem;color:var(--text-muted);">
            <div><i class="fas fa-calendar"></i> Submitted: {{ $teacher_review->submitted_at?->format('M d, Y H:i') ?? 'N/A' }}</div>
            <div><i class="fas fa-user{{ $teacher_review->is_anonymous ? '-secret' : '' }}"></i> {{ $teacher_review->is_anonymous ? 'Anonymous' : 'Identified' }}</div>
        </div>
    </div>
</div>
@endsection
