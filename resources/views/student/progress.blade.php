@extends('student.layout')

@section('title', 'My Progress')

@section('content')
<div class="dash-welcome">
    <h2><i class="fas fa-chart-line me-2" style="color: var(--primary);"></i>My Progress</h2>
    <p>Track your academic performance and growth over time.</p>
</div>

{{-- Term-by-Term Average Trend --}}
<div class="student-card">
    <div class="student-card-header">
        <h5><i class="fas fa-signal me-2" style="color: var(--primary);"></i>Term Performance Trend
            @if($activeAy)
                <small class="text-muted ms-2" style="font-size:12px;">— {{ $activeAy->name }}</small>
            @endif
        </h5>
    </div>
    <div class="student-card-body">
        @if(count($termAverages) > 0)
            @foreach($termAverages as $ta)
                <div class="term-bar">
                    <div class="term-bar-label">
                        <span>{{ $ta['term']->name }}</span>
                        <small>Avg: {{ $ta['average'] }} / 100 &nbsp;|&nbsp; Highest: {{ $ta['highest'] }} &nbsp;|&nbsp; Lowest: {{ $ta['lowest'] }} &nbsp;|&nbsp; Subjects: {{ $ta['count'] }}</small>
                    </div>
                    <div class="term-bar-track">
                        <div class="term-bar-fill" style="width: {{ min($ta['average'], 100) }}%;"></div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="empty-state">
                <i class="fas fa-chart-line"></i>
                <p>No term performance data available yet. Check back after your marks are entered.</p>
            </div>
        @endif
    </div>
</div>

{{-- Subject Performance Breakdown for Active Term --}}
@php
    $activeAy = \App\Models\AcademicYear::where('is_current', true)->first();
    $activeTerm = $activeAy ? \App\Models\Term::where('academic_year_id', $activeAy->id)->where('is_active', true)->first() : null;
    $currentMarks = collect();
    if ($activeAy && $activeTerm) {
        $currentMarks = \App\Models\MarkEntry::with('subject')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $activeAy->id)
            ->where('term_id', $activeTerm->id)
            ->orderBy('grand_total', 'desc')
            ->get();
    }
@endphp

@if($currentMarks->count() > 0)
<div class="student-card">
    <div class="student-card-header">
        <h5><i class="fas fa-list-ol me-2" style="color: var(--accent);"></i>Subject Breakdown
            @if($activeTerm)
                <small class="text-muted ms-2" style="font-size:12px;">— {{ $activeTerm->name }}</small>
            @endif
        </h5>
    </div>
    <div class="student-card-body" style="padding:0;">
        <div class="table-responsive">
            <table class="student-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>CA Total</th>
                        <th>Exam Total</th>
                        <th>Grand Total</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rank = 1; @endphp
                    @foreach($currentMarks as $mark)
                        <tr>
                            <td class="text-muted">{{ $rank++ }}</td>
                            <td class="fw-semibold">{{ $mark->subject ? $mark->subject->name : 'N/A' }}</td>
                            <td>{{ $mark->ca_total ?? '-' }}</td>
                            <td>{{ $mark->exam_total ?? '-' }}</td>
                            <td class="fw-bold">{{ $mark->grand_total ?? '-' }}</td>
                            <td>
                                <div style="width: 100px; display: inline-block; vertical-align: middle;">
                                    <div class="term-bar-track" style="height: 6px;">
                                        <div class="term-bar-fill" style="width: {{ min($mark->grand_total ?? 0, 100) }}%; height: 100%;"></div>
                                    </div>
                                </div>
                                <span style="font-size:11px; color: var(--text-muted); margin-left: 6px;">{{ $mark->grand_total ?? 0 }}%</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Progress Reports --}}
@if($progressReports->count() > 0)
<div class="student-card">
    <div class="student-card-header">
        <h5><i class="fas fa-file-alt me-2" style="color: var(--success);"></i>Progress Reports</h5>
    </div>
    <div class="student-card-body" style="padding:0;">
        <div class="table-responsive">
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Term</th>
                        <th>Date</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($progressReports as $report)
                        <tr>
                            <td class="fw-semibold">{{ $report->title ?? 'Report #' . $report->id }}</td>
                            <td>{{ $report->term->name ?? 'N/A' }}</td>
                            <td>{{ $report->created_at->format('M d, Y') }}</td>
                            <td>{{ Str::limit($report->remarks ?? $report->teacher_comments ?? '-', 80) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
