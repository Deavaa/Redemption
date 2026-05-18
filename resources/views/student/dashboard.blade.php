@extends('student.layout')

@section('title', 'Dashboard')

@section('content')
<div class="dash-welcome">
    <h2>Welcome, {{ $student->full_name }}!</h2>
    <p>Here's an overview of your academic progress and activities.</p>
</div>

{{-- Summary Stats --}}
<div class="dash-stats">
    <div class="dash-stat-card">
        <div class="dash-stat-icon teal"><i class="fas fa-school"></i></div>
        <div class="dash-stat-info">
            <h3>{{ $student->classroom ? $student->classroom->name : 'N/A' }}</h3>
            <p>Current Class</p>
        </div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon blue"><i class="fas fa-bookmark"></i></div>
        <div class="dash-stat-info">
            <h3>{{ $activeTerm ? $activeTerm->name : 'N/A' }}</h3>
            <p>Current Term</p>
        </div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon green"><i class="fas fa-chart-bar"></i></div>
        <div class="dash-stat-info">
            <h3>{{ $averageScore }}</h3>
            <p>Average Score</p>
        </div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon {{ $feeBalance > 0 ? 'red' : 'gold' }}">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="dash-stat-info">
            <h3>{{ number_format($feeBalance, 2) }}</h3>
            <p>Fee Balance</p>
        </div>
    </div>
</div>

{{-- Latest Marks --}}
<div class="student-card">
    <div class="student-card-header">
        <h5><i class="fas fa-pen-alt me-2" style="color: var(--primary);"></i>Latest Marks
            @if($activeAy && $activeTerm)
                <small class="text-muted ms-2" style="font-size:12px;">— {{ $activeTerm->name }}, {{ $activeAy->name }}</small>
            @endif
        </h5>
        <a href="{{ route('student.marks') }}" class="btn btn-sm" style="background: var(--primary-light); color: var(--primary); font-weight: 600; font-size: 12px; border-radius: var(--radius-sm);">
            <i class="fas fa-arrow-right me-1"></i>View All Marks
        </a>
    </div>
    <div class="student-card-body" style="padding: 0;">
        @if($latestMarks->count() > 0)
            <div class="table-responsive">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>CA Total</th>
                            <th>Exam Total</th>
                            <th>Grand Total</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latestMarks as $mark)
                            <tr>
                                <td class="fw-semibold">{{ $mark->subject ? $mark->subject->name : 'N/A' }}</td>
                                <td>{{ $mark->ca_total ?? '-' }}</td>
                                <td>{{ $mark->exam_total ?? '-' }}</td>
                                <td class="fw-bold">{{ $mark->grand_total ?? '-' }}</td>
                                <td>
                                    @php
                                        $gradeClass = 'grade-c';
                                        $g = $mark->grade ?? '';
                                        if (in_array($g, ['A+', 'A', 'A-'])) $gradeClass = 'grade-a';
                                        elseif (in_array($g, ['B+', 'B', 'B-'])) $gradeClass = 'grade-b';
                                        elseif (in_array($g, ['C+', 'C', 'C-'])) $gradeClass = 'grade-c';
                                        elseif ($g === 'D') $gradeClass = 'grade-d';
                                        elseif ($g === 'F') $gradeClass = 'grade-f';
                                    @endphp
                                    <span class="grade-badge {{ $gradeClass }}">{{ $g }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <p>No marks available for the current term yet.</p>
            </div>
        @endif
    </div>
</div>

{{-- Quick Links --}}
<div class="row g-3">
    <div class="col-md-4">
        <a href="{{ route('student.marks') }}" class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--card-bg); border: 1px solid var(--border); box-shadow: var(--shadow); transition: var(--transition);">
            <div class="dash-stat-icon blue" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-pen-alt"></i></div>
            <div>
                <div class="fw-semibold" style="color: var(--text-dark); font-size:14px;">My Marks</div>
                <div style="font-size:12px; color: var(--text-muted);">View your test scores & grades</div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('student.progress') }}" class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--card-bg); border: 1px solid var(--border); box-shadow: var(--shadow); transition: var(--transition);">
            <div class="dash-stat-icon green" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-chart-line"></i></div>
            <div>
                <div class="fw-semibold" style="color: var(--text-dark); font-size:14px;">My Progress</div>
                <div style="font-size:12px; color: var(--text-muted);">Track your academic growth</div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('student.fees') }}" class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--card-bg); border: 1px solid var(--border); box-shadow: var(--shadow); transition: var(--transition);">
            <div class="dash-stat-icon gold" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-wallet"></i></div>
            <div>
                <div class="fw-semibold" style="color: var(--text-dark); font-size:14px;">My Fees</div>
                <div style="font-size:12px; color: var(--text-muted);">Check fee payments & balance</div>
            </div>
        </a>
    </div>
</div>
@endsection
