@extends('layouts.portal')

@section('home_route', route('portal.dashboard'))

@section('title', 'My Marks')

@section('topbar_title', 'My Marks')

@section('sidebar_menu')
    <a href="{{ route('portal.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="{{ route('portal.marks') }}" class="active"><i class="fas fa-chart-bar"></i> My Marks</a>
    <a href="{{ route('portal.fees') }}"><i class="fas fa-wallet"></i> Fee Progress</a>
    <a href="{{ route('portal.profile') }}"><i class="fas fa-user"></i> My Profile</a>
@endsection

@section('content')
@php
    function getGradeClass($grandTotal) {
        if ($grandTotal >= 80) return 'grade-A';
        if ($grandTotal >= 60) return 'grade-B';
        if ($grandTotal >= 45) return 'grade-C';
        if ($grandTotal >= 40) return 'grade-D';
        return 'grade-F';
    }
@endphp

{{-- Filter Row --}}
<div class="portal-card">
    <div class="portal-card-body">
        <form method="GET" action="{{ route('portal.marks') }}" id="filterForm" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <label class="form-label fw-semibold" style="font-size:0.82rem;">Academic Year</label>
                <select name="academic_year_id" id="academicYearSelect" class="form-select form-select-sm">
                    <option value="">All Academic Years</option>
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ $selectedAyId == $ay->id ? 'selected' : '' }}>
                            {{ $ay->name ?? $ay->year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold" style="font-size:0.82rem;">Term</label>
                <select name="term_id" id="termSelect" class="form-select form-select-sm">
                    <option value="">All Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $selectedTermId == $term->id ? 'selected' : '' }}>
                            {{ $term->name ?? 'Term '.$term->term_number }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4">
                <button type="submit" class="btn btn-sm btn-primary px-3">
                    <i class="fas fa-filter me-1"></i> Apply
                </button>
                <a href="{{ route('portal.marks') }}" class="btn btn-sm btn-outline-secondary px-3 ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Marks by Term --}}
@if($marksByTerm->count() > 0)
    @foreach($marksByTerm as $termId => $termMarks)
        @php
            $termLabel = $termMarks->first()->term->name ?? ('Term ' . ($termMarks->first()->term->term_number ?? $termId));
            $termAvg = round($termMarks->avg('grand_total'), 1);
        @endphp

        <div class="portal-card">
            <div class="portal-card-header">
                <i class="fas fa-book-open" style="color:#4361ee;"></i>
                {{ $termLabel }}
                <span class="ms-auto" style="font-size:0.82rem; font-weight:600; color:#6b7280;">
                    {{ $termMarks->first()->academicYear->name ?? $termMarks->first()->academicYear->year ?? '' }}
                </span>
            </div>
            <div class="portal-card-body p-0">
                <div class="table-responsive">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th class="text-center">CA (30%)</th>
                                <th class="text-center">Exam (70%)</th>
                                <th class="text-center">Total (100%)</th>
                                <th class="text-center">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($termMarks as $mark)
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
                        <tfoot>
                            <tr style="background:#f8fafc;">
                                <td class="fw-bold" colspan="3">
                                    <i class="fas fa-calculator me-1" style="color:#4361ee;"></i>
                                    Term Average
                                </td>
                                <td class="text-center fw-bold" style="color:#4361ee;">{{ $termAvg }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="portal-card">
        <div class="portal-card-body text-center py-5">
            <div style="font-size:3rem; color:#d1d5db; margin-bottom:0.75rem;">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h6 style="color:#6b7280; font-weight:700;">No Marks Found</h6>
            <p class="mb-0" style="color:#9ca3af; font-size:0.88rem;">
                No marks are available for the selected academic year and term. Try adjusting your filters.
            </p>
        </div>
    </div>
@endif

{{-- JS for auto-reload on dropdown change --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const aySelect = document.getElementById('academicYearSelect');
    const termSelect = document.getElementById('termSelect');
    const form = document.getElementById('filterForm');

    if (aySelect) {
        aySelect.addEventListener('change', function() {
            // Reset term when academic year changes, then submit
            termSelect.value = '';
            form.submit();
        });
    }

    if (termSelect) {
        termSelect.addEventListener('change', function() {
            form.submit();
        });
    }
});
</script>
@endsection
