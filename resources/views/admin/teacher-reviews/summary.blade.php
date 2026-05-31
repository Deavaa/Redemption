@extends('layouts.admin')
@section('title', 'Teacher Review Summary')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.teacher-reviews.index') }}">Teacher Reviews</a></li>
                <li class="active">Summary by Teacher</li>
            </ol></nav>
            <h1 class="modern-page-title">Teacher Review Summary</h1>
            <p class="modern-page-subtitle">Aggregate ratings per teacher for the selected term</p>
        </div>
    </div>

    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Term Selector --}}
    <div class="modern-card" style="padding:1rem 1.25rem;margin-bottom:1.5rem;">
        <form method="GET" action="{{ route('admin.teacher-reviews.summary') }}" style="display:flex;gap:0.75rem;align-items:flex-end;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <label style="font-size:0.8rem;font-weight:600;color:#374151;display:block;margin-bottom:0.25rem;">Term</label>
                <select name="term_id" class="modern-input modern-select" style="padding:0.4rem 0.6rem;font-size:0.85rem;">
                    @foreach($terms as $term)
                    <option value="{{ $term->id }}" {{ $term->id == $selectedTermId ? 'selected' : '' }}>{{ $term->name }} ({{ $term->academicYear->name ?? '' }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-modern btn-modern-primary" style="padding:0.4rem 1rem;font-size:0.85rem;">
                <i class="fas fa-filter"></i> Filter
            </button>
        </form>
    </div>

    {{-- Teacher Summary Cards --}}
    @if($teachers->count() > 0)
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(380px,1fr));gap:1.25rem;">
        @foreach($teachers as $teacher)
        @php $gradeInfo = $gradeOptions[$teacher->avg_grade] ?? ['label' => 'N/A', 'color' => '#6b7280', 'icon' => ''] @endphp
        <div class="modern-card" style="padding:1.25rem;">
            {{-- Teacher Header --}}
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;padding-bottom:0.75rem;border-bottom:1px solid #e5e7eb;">
                <div style="width:48px;height:48px;border-radius:50%;background:#eef2ff;color:#4361ee;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.2rem;flex-shrink:0;">
                    {{ strtoupper(substr($teacher->full_name, 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;color:#1a1a2e;font-size:1rem;">{{ $teacher->full_name }}</div>
                    <div style="font-size:0.8rem;color:#6b7280;">{{ $teacher->review_count }} review(s)</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:1.5rem;font-weight:800;color:{{ $gradeInfo['color'] }};">{{ $teacher->avg_overall }}%</div>
                    <span style="background:{{ $gradeInfo['color'] }}20;color:{{ $gradeInfo['color'] }};padding:0.15rem 0.5rem;border-radius:99px;font-size:0.75rem;font-weight:600;">{{ $gradeInfo['label'] }}</span>
                </div>
            </div>

            {{-- Criteria Bars --}}
            @foreach($criteriaOptions as $field => $label)
            @php $avgValue = $teacher->avg_scores[$field] ?? 0 @endphp
            <div style="margin-bottom:0.5rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:0.15rem;">
                    <span style="font-size:0.8rem;color:#374151;">{{ $label }}</span>
                    <span style="font-size:0.8rem;font-weight:600;color:#4361ee;">{{ $avgValue }}/5</span>
                </div>
                <div style="background:#e5e7eb;border-radius:99px;height:6px;overflow:hidden;">
                    <div style="background:#4361ee;height:100%;border-radius:99px;width:{{ $avgValue * 20 }}%;"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
    @else
    <div class="modern-card" style="padding:2rem;text-align:center;">
        <i class="fas fa-chart-bar" style="font-size:3rem;color:#d1d5db;"></i>
        <p style="color:#6b7280;margin-top:0.75rem;">No teacher reviews found for the selected term.</p>
    </div>
    @endif
</div>
@endsection
