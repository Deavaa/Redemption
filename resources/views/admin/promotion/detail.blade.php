@extends('layouts.admin')
@section('title', 'Promotion Result Detail')

@push('styles')
<style>
.stu-page { animation: stuFadeIn 0.4s ease-out; }
@keyframes stuFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.stu-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin: 0; }
.stu-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.stu-breadcrumb li a { color: var(--text-muted); text-decoration: none; }
.stu-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.stu-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.stu-info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem 1.5rem; }
.stu-info-label { font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; }
.stu-info-value { font-size: 0.92rem; font-weight: 600; color: var(--text-dark); }
@media (max-width: 768px) { .stu-info-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
@endpush

@section('content')
<div class="stu-page">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;flex-wrap:wrap;gap:1rem;">
        <div>
            <nav class="stu-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.promotion.index') }}">Promotion</a></li>
                <li class="active">Result Detail</li>
            </ol></nav>
            <h1 class="stu-title">Promotion Result Detail</h1>
        </div>
        <a href="{{ route('admin.promotion.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.82rem;padding:0.45rem 1rem;">
            <i class="fas fa-arrow-left"></i> Back to Results
        </a>
    </div>

    @if($promotion)
    {{-- Student Info --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-card-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:36px;height:36px;border-radius:10px;background:#eef2ff;color:#4361ee;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user-graduate"></i></div>
                <h3 class="modern-card-title">Student Information</h3>
            </div>
            @php
                $statusBadge = match($promotion->status ?? 'pending') {
                    'promoted' => 'modern-badge-success',
                    'detained' => 'modern-badge-danger',
                    'conditional' => 'modern-badge-warning',
                    default => 'modern-badge-light',
                };
            @endphp
            <span class="modern-badge {{ $statusBadge }}" style="font-size:0.85rem;padding:0.3rem 0.8rem;">{{ ucfirst($promotion->status ?? 'pending') }}</span>
        </div>
        <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
            <div class="stu-info-grid">
                <div><div class="stu-info-label">Student</div><div class="stu-info-value">{{ $promotion->student->first_name ?? '' }} {{ $promotion->student->last_name ?? '' }}</div></div>
                <div><div class="stu-info-label">From Class</div><div class="stu-info-value">{{ $promotion->fromClass->name ?? '-' }}</div></div>
                <div><div class="stu-info-label">To Class</div><div class="stu-info-value">{{ $promotion->toClass->name ?? '-' }}</div></div>
                <div><div class="stu-info-label">Academic Year</div><div class="stu-info-value">{{ $promotion->academicYear->name ?? '-' }}</div></div>
                <div><div class="stu-info-label">Term</div><div class="stu-info-value">{{ $promotion->term->name ?? '-' }}</div></div>
                <div><div class="stu-info-label">Class Rank</div><div class="stu-info-value">{{ $promotion->class_rank ?? '-' }} / {{ $promotion->total_students ?? '-' }}</div></div>
            </div>
        </div>
    </div>

    {{-- Performance Details --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-card-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:36px;height:36px;border-radius:10px;background:#ecfdf5;color:#10b981;display:flex;align-items:center;justify-content:center;"><i class="fas fa-chart-bar"></i></div>
                <h3 class="modern-card-title">Performance Summary</h3>
            </div>
        </div>
        <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
            <div class="stu-info-grid">
                <div><div class="stu-info-label">Average Score</div><div class="stu-info-value">{{ number_format($promotion->average_score ?? 0, 1) }}%</div></div>
                <div><div class="stu-info-label">Overall Percentage</div><div class="stu-info-value">{{ number_format($promotion->overall_percentage ?? 0, 1) }}%</div></div>
                <div><div class="stu-info-label">Overall Grade</div><div class="stu-info-value">{{ $promotion->overall_grade ?? '-' }}</div></div>
                <div><div class="stu-info-label">GPA</div><div class="stu-info-value">{{ number_format($promotion->grade_point_average ?? 0, 2) }}</div></div>
                <div><div class="stu-info-label">Subjects Passed</div><div class="stu-info-value" style="color:#10b981;">{{ $promotion->subjects_passed ?? 0 }}</div></div>
                <div><div class="stu-info-label">Subjects Failed</div><div class="stu-info-value" style="color:#ef4444;">{{ $promotion->subjects_failed ?? 0 }}</div></div>
                <div><div class="stu-info-label">Total Subjects</div><div class="stu-info-value">{{ $promotion->total_subjects ?? 0 }}</div></div>
                <div><div class="stu-info-label">Attendance %</div><div class="stu-info-value">{{ number_format($promotion->attendance_percentage ?? 0, 1) }}%</div></div>
                <div><div class="stu-info-label">Processed By</div><div class="stu-info-value">{{ $promotion->processedBy->name ?? '-' }}</div></div>
            </div>
        </div>
    </div>

    {{-- Failure Reasons --}}
    @if(!empty($promotion->failure_reasons))
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-card-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:36px;height:36px;border-radius:10px;background:#fef2f2;color:#ef4444;display:flex;align-items:center;justify-content:center;"><i class="fas fa-exclamation-triangle"></i></div>
                <h3 class="modern-card-title">Failure Reasons</h3>
            </div>
        </div>
        <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
            <ul style="list-style:none;padding:0;margin:0;">
                @foreach($promotion->failure_reasons as $reason)
                <li style="padding:0.5rem 0;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:0.5rem;">
                    <i class="fas fa-times-circle" style="color:#ef4444;font-size:0.85rem;"></i>
                    <span style="font-size:0.88rem;color:var(--text-dark);">{{ $reason }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Remarks --}}
    @if($promotion->remarks)
    <div class="modern-card">
        <div class="modern-card-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:36px;height:36px;border-radius:10px;background:#fefce8;color:#f59e0b;display:flex;align-items:center;justify-content:center;"><i class="fas fa-comment"></i></div>
                <h3 class="modern-card-title">Remarks</h3>
            </div>
        </div>
        <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
            <p style="font-size:0.9rem;color:var(--text-dark);">{{ $promotion->remarks }}</p>
        </div>
    </div>
    @endif
    @else
    <div style="text-align:center;padding:3rem 1.5rem;background:var(--card-bg);border-radius:14px;border:1px solid var(--border);">
        <i class="fas fa-clipboard-list" style="font-size:3rem;color:#d1d5db;margin-bottom:1rem;display:block;"></i>
        <p style="color:var(--text-muted);">Promotion result not found.</p>
        <a href="{{ route('admin.promotion.index') }}" class="btn-modern btn-modern-outline" style="margin-top:1rem;font-size:0.82rem;">
            <i class="fas fa-arrow-left"></i> Back to Promotion
        </a>
    </div>
    @endif
</div>
@endsection
