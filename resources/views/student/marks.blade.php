@extends('student.layout')

@section('title', 'My Marks')

@section('content')
<div class="dash-welcome">
    <h2><i class="fas fa-pen-alt me-2" style="color: var(--primary);"></i>My Marks</h2>
    <p>View your examination scores and grades by academic year and term.</p>
</div>

{{-- Filters --}}
<div class="student-card">
    <div class="student-card-header">
        <h5><i class="fas fa-filter me-2" style="color: var(--primary);"></i>Filter Marks</h5>
    </div>
    <div class="student-card-body">
        <form method="GET" action="{{ route('student.marks') }}" class="filter-bar">
            <div>
                <label class="form-label mb-1" style="font-size:12px;font-weight:600;color:var(--text-muted);">Academic Year</label>
                <select name="academic_year_id" onchange="this.form.submit()">
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ $selectedAy && $selectedAy->id == $ay->id ? 'selected' : '' }}>
                            {{ $ay->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label mb-1" style="font-size:12px;font-weight:600;color:var(--text-muted);">Term</label>
                <select name="term_id" onchange="this.form.submit()">
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $selectedTerm && $selectedTerm->id == $term->id ? 'selected' : '' }}>
                            {{ $term->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

{{-- Marks Table --}}
<div class="student-card">
    <div class="student-card-header">
        <h5>
            <i class="fas fa-table me-2" style="color: var(--primary);"></i>Marks Report
            @if($selectedAy && $selectedTerm)
                <small class="text-muted ms-2" style="font-size:12px;">— {{ $selectedTerm->name }}, {{ $selectedAy->name }}</small>
            @endif
        </h5>
        @if($marks->count() > 0)
            <span class="badge rounded-pill" style="background: var(--primary-light); color: var(--primary); font-weight: 600; font-size: 11px;">
                {{ $marks->count() }} Subject{{ $marks->count() > 1 ? 's' : '' }}
            </span>
        @endif
    </div>
    <div class="student-card-body" style="padding: 0;">
        @if($marks->count() > 0)
            <div class="table-responsive">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>CA Total</th>
                            <th>Exam Total</th>
                            <th>Grand Total</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $sn = 1; @endphp
                        @foreach($marks as $mark)
                            <tr>
                                <td class="text-muted">{{ $sn++ }}</td>
                                <td class="fw-semibold">{{ $mark->subject ? $mark->subject->name : 'N/A' }}</td>
                                <td>{{ $mark->ca_total ?? '-' }}</td>
                                <td>{{ $mark->exam_total ?? '-' }}</td>
                                <td class="fw-bold" style="color: var(--text-dark);">{{ $mark->grand_total ?? '-' }}</td>
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
                    <tfoot>
                        <tr style="background: #f0fdfa;">
                            <td colspan="2" class="fw-bold" style="color: var(--primary);">Average</td>
                            <td class="fw-semibold">{{ number_format($marks->avg('ca_total'), 2) }}</td>
                            <td class="fw-semibold">{{ number_format($marks->avg('exam_total'), 2) }}</td>
                            <td class="fw-bold" style="color: var(--primary);">{{ number_format($marks->avg('grand_total'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <p>No marks found for the selected term. Please select a different academic year or term.</p>
            </div>
        @endif
    </div>
</div>
@endsection
