@extends('parent.layout')

@section('title', 'Marks - ' . $student->full_name)

@section('content')
<div class="page-header">
    <div>
        <h4><i class="fas fa-pen me-2" style="color: var(--primary);"></i> Marks Report</h4>
        <div class="page-header-sub">
            {{ $student->full_name }}
            &bull; {{ $student->classroom->name ?? 'No Class' }}
            @if($student->section) &mdash; {{ $student->section->name }}@endif
            &bull; Roll: {{ $student->roll_number ?? 'N/A' }}
        </div>
    </div>
    <a href="{{ route('parent.dashboard') }}" class="btn-modern btn-modern-outline">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

{{-- Current Term Banner --}}
@php
    $currentAy = \App\Models\AcademicYear::where('is_current', true)->first();
    $currentTerm = $currentAy ? \App\Models\Term::where('academic_year_id', $currentAy->id)->where('is_active', true)->first() : null;
    $isViewingCurrentTerm = $selectedTerm && $currentTerm && $selectedTerm->id == $currentTerm->id;
@endphp
@if($currentAy && $currentTerm)
<div style="background:{{ $isViewingCurrentTerm ? '#ecfdf5' : '#eff6ff' }};border:1px solid {{ $isViewingCurrentTerm ? '#a7f3d0' : '#bfdbfe' }};border-radius:12px;padding:0.75rem 1.25rem;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
    <i class="fas {{ $isViewingCurrentTerm ? 'fa-check-circle' : 'fa-info-circle' }}" style="color:{{ $isViewingCurrentTerm ? '#10b981' : '#3b82f6' }};font-size:1.1rem;"></i>
    <span style="font-size:0.88rem;font-weight:500;color:{{ $isViewingCurrentTerm ? '#065f46' : '#1e40af' }};">
        Current Academic Year: <strong>{{ $currentAy->name }}</strong> &bull; Current Term: <strong>{{ $currentTerm->name }}</strong>
        @if($isViewingCurrentTerm)
        <span style="margin-left:6px;padding:2px 8px;background:#d1fae5;border-radius:5px;font-size:0.75rem;font-weight:700;color:#059669;">VIEWING</span>
        @endif
    </span>
</div>
@endif

{{-- Filter Bar --}}
<div class="info-card" style="margin-bottom: 20px;">
    <div class="info-card-body" style="padding: 14px 18px;">
        <form method="GET" action="{{ route('parent.child.marks', $student->id) }}" class="filter-bar" style="margin-bottom:0;">
            <div style="display:flex; align-items:center; gap:8px;">
                <label style="font-size:12px; font-weight:600; color:var(--text-dark); white-space:nowrap;">
                    <i class="fas fa-calendar me-1"></i> Academic Year:
                </label>
                <select name="academic_year_id" onchange="this.form.submit()">
                    @foreach($academicYears as $ay)
                    <option value="{{ $ay->id }}" {{ $selectedAy && $selectedAy->id == $ay->id ? 'selected' : '' }}>
                        {{ $ay->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <label style="font-size:12px; font-weight:600; color:var(--text-dark); white-space:nowrap;">
                    <i class="fas fa-bookmark me-1"></i> Term:
                </label>
                <select name="term_id" onchange="this.form.submit()">
                    @foreach($terms as $term)
                    <option value="{{ $term->id }}" {{ $selectedTerm && $selectedTerm->id == $term->id ? 'selected' : '' }}>
                        {{ $term->name }}
                        @if($currentTerm && $term->id == $currentTerm->id) (Current)@endif
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

{{-- Marks Table --}}
@if($marks->count() > 0)
<div class="info-card">
    <div class="info-card-header">
        <h5><i class="fas fa-table me-2" style="color: var(--primary);"></i> Marks Details
            @if($selectedAy && $selectedTerm)
                <span style="font-weight:400; color:var(--text-muted); font-size:12px; margin-left:8px;">
                    {{ $selectedAy->name }} &mdash; {{ $selectedTerm->name }}
                </span>
            @endif
        </h5>
        <span class="modern-badge modern-badge-orange">
            Average: {{ round($marks->avg('grand_total'), 1) }}
        </span>
    </div>
    <div class="info-card-body" style="padding:0; overflow-x:auto;">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th style="text-align:center;">CA Total</th>
                    <th style="text-align:center;">Exam Total</th>
                    <th style="text-align:center;">Grand Total</th>
                    <th style="text-align:center;">Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($marks as $i => $mark)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $mark->subject->name ?? 'N/A' }}</strong>
                        @if($mark->subject && $mark->subject->code)
                            <small style="color:var(--text-muted); margin-left:4px;">({{ $mark->subject->code }})</small>
                        @endif
                    </td>
                    <td style="text-align:center;">{{ $mark->ca_total ?? '—' }}</td>
                    <td style="text-align:center;">{{ $mark->exam_total ?? '—' }}</td>
                    <td style="text-align:center;">
                        <strong>{{ $mark->grand_total ?? '—' }}</strong>
                    </td>
                    <td style="text-align:center;">
                        @php
                            $grade = $mark->grade ?? '';
                            $badgeClass = 'modern-badge-light';
                            if (in_array($grade, ['A+', 'A', 'A-'])) $badgeClass = 'modern-badge-green';
                            elseif (in_array($grade, ['B+', 'B', 'B-'])) $badgeClass = 'modern-badge-blue';
                            elseif (in_array($grade, ['C+', 'C', 'C-'])) $badgeClass = 'modern-badge-amber';
                            elseif (in_array($grade, ['D'])) $badgeClass = 'modern-badge-orange';
                            elseif ($grade === 'F') $badgeClass = 'modern-badge-red';
                        @endphp
                        <span class="modern-badge {{ $badgeClass }}">{{ $grade }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#fafaf9;">
                    <td colspan="2" style="font-weight:700; color:var(--text-dark);">Summary</td>
                    <td style="text-align:center; font-weight:600;">{{ round($marks->avg('ca_total'), 1) }}</td>
                    <td style="text-align:center; font-weight:600;">{{ round($marks->avg('exam_total'), 1) }}</td>
                    <td style="text-align:center; font-weight:700; color:var(--primary);">{{ round($marks->avg('grand_total'), 1) }}</td>
                    <td style="text-align:center;">
                        <span class="modern-badge modern-badge-orange">{{ round($marks->avg('grand_total'), 1) }}%</span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@else
<div class="empty-state">
    <i class="fas fa-file-alt"></i>
    <h5>No Marks Available</h5>
    <p>No marks have been recorded for this term yet.</p>
</div>
@endif
@endsection
