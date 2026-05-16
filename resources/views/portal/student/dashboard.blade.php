@extends('layouts.portal')

@section('home_route', route('portal.dashboard'))

@section('title', 'Dashboard')

@section('topbar_title', 'Dashboard')

@section('sidebar_menu')
    <a href="{{ route('portal.dashboard') }}" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="{{ route('portal.marks') }}"><i class="fas fa-chart-bar"></i> My Marks</a>
    <a href="{{ route('portal.fees') }}"><i class="fas fa-wallet"></i> Fee Progress</a>
    <a href="{{ route('portal.profile') }}"><i class="fas fa-user"></i> My Profile</a>
@endsection

@section('content')
@php
    function getGradeClass($grandTotal) {
        if ($grandTotal >= 80) return 'grade-A';       // A+, A, A-
        if ($grandTotal >= 60) return 'grade-B';       // B+, B, B-
        if ($grandTotal >= 45) return 'grade-C';       // C+, C, C-
        if ($grandTotal >= 40) return 'grade-D';       // D
        return 'grade-F';                              // F
    }

    $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
    $className = $student->classroom->name ?? 'N/A';
    $avgScore = $recentMarks->count() > 0 ? round($recentMarks->avg('grand_total'), 1) : '—';
    $termName = $activeTerm->name ?? 'N/A';
@endphp

{{-- Welcome Banner --}}
<div class="portal-card" style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); border: none;">
    <div class="portal-card-body" style="color: #fff; padding: 1.5rem 1.75rem;">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:800;">
                {{ strtoupper(substr($studentName, 0, 1)) }}
            </div>
            <div>
                <h4 class="mb-1" style="font-weight:800; font-size:1.25rem;">Welcome back, {{ $student->first_name ?? 'Student' }}!</h4>
                <p class="mb-0" style="opacity:0.85; font-size:0.9rem;">
                    @if($currentAy) {{ $currentAy->name ?? $currentAy->year }} &mdash; @endif
                    {{ $termName }} &bull; {{ $className }}
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div style="font-size:1.5rem; margin-bottom:0.35rem;"><i class="fas fa-chalkboard" style="color:#4361ee;"></i></div>
            <div class="stat-value">{{ $className }}</div>
            <div class="stat-label">Current Class</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div style="font-size:1.5rem; margin-bottom:0.35rem;"><i class="fas fa-chart-line" style="color:#10b981;"></i></div>
            <div class="stat-value" style="color:#10b981;">{{ $avgScore }}</div>
            <div class="stat-label">Average Score</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div style="font-size:1.5rem; margin-bottom:0.35rem;"><i class="fas fa-wallet" style="color:{{ $balance > 0 ? '#f59e0b' : '#10b981' }};"></i></div>
            <div class="stat-value" style="color:{{ $balance > 0 ? '#f59e0b' : '#10b981' }};">{{ number_format($balance, 2) }}</div>
            <div class="stat-label">Fee Balance</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div style="font-size:1.5rem; margin-bottom:0.35rem;"><i class="fas fa-calendar-alt" style="color:#8b5cf6;"></i></div>
            <div class="stat-value" style="color:#8b5cf6; font-size:1.25rem;">{{ $termName }}</div>
            <div class="stat-label">Active Term</div>
        </div>
    </div>
</div>

{{-- Recent Marks --}}
<div class="portal-card">
    <div class="portal-card-header">
        <i class="fas fa-clipboard-list" style="color:#4361ee;"></i>
        Recent Marks &mdash; {{ $termName }}
    </div>
    <div class="portal-card-body p-0">
        @if($recentMarks->count() > 0)
            <div class="table-responsive">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th class="text-center">CA Total</th>
                            <th class="text-center">Exam Total</th>
                            <th class="text-center">Grand Total</th>
                            <th class="text-center">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentMarks as $mark)
                            <tr>
                                <td class="fw-semibold">{{ $mark->subject->name ?? 'N/A' }}</td>
                                <td class="text-center">{{ $mark->ca_total ?? '—' }}</td>
                                <td class="text-center">{{ $mark->exam_total ?? '—' }}</td>
                                <td class="text-center fw-bold">{{ $mark->grand_total ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="grade-badge {{ getGradeClass($mark->grand_total ?? 0) }}">
                                        {{ $mark->grade ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5 px-3">
                <div style="font-size:3rem; color:#d1d5db; margin-bottom:0.75rem;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h6 style="color:#6b7280; font-weight:700;">No Marks Available Yet</h6>
                <p class="mb-0" style="color:#9ca3af; font-size:0.88rem;">
                    Your marks for the current term will appear here once they are entered.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
