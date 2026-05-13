@extends('layouts.admin')
@section('title', 'Report Card')

@push('styles')
<style>
@media print {
    body * { visibility: hidden; }
    .rc-print-area, .rc-print-area * { visibility: visible; }
    .rc-print-area { position: absolute; left: 0; top: 0; width: 100%; }
    .rc-no-print { display: none !important; }
    .rc-card-sheet { page-break-after: always; margin: 0 !important; box-shadow: none !important; border: none !important; }
    .rc-card-sheet:last-child { page-break-after: auto; }
}

.rc-no-print { margin-bottom: 1.5rem; }
.rc-print-toolbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding: 1rem 1.5rem; background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
.rc-print-toolbar-title { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; }
.rc-print-toolbar-info { font-size: 0.85rem; color: #6b7280; }
.rc-print-toolbar-actions { display: flex; gap: 0.75rem; }

.btn-rc { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem; border-radius: 10px; font-weight: 600; font-size: 0.88rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.btn-rc-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.3); }
.btn-rc-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.4); color: #fff; }
.btn-rc-outline { background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb; }
.btn-rc-outline:hover { border-color: #4361ee; color: #4361ee; }

/* Card Sheet - A4 Landscape split into 4 faces */
.rc-card-sheet {
    width: 297mm;
    height: 210mm;
    margin: 0 auto 2rem;
    background: #fff;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    overflow: hidden;
    position: relative;
}

/* Fold lines */
.rc-card-sheet::before {
    content: '';
    position: absolute;
    left: 50%;
    top: 0;
    bottom: 0;
    width: 0;
    border-left: 2px dashed #c7d2fe;
    z-index: 10;
    pointer-events: none;
}
.rc-card-sheet::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 0;
    border-top: 2px dashed #c7d2fe;
    z-index: 10;
    pointer-events: none;
}

/* Each face */
.rc-face {
    padding: 18px;
    display: flex;
    flex-direction: column;
    font-family: 'Inter', 'Noto Sans SC', sans-serif;
    overflow: hidden;
    position: relative;
}

/* Face 1 - Front Cover (top-right in grid, becomes outside when folded) */
.rc-face-cover {
    background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
    color: #fff;
    align-items: center;
    justify-content: center;
    text-align: center;
}
.rc-cover-logo { max-height: 80px; max-width: 120px; margin-bottom: 12px; filter: brightness(0) invert(1); }
.rc-cover-school { font-size: 18px; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 4px; }
.rc-cover-motto { font-size: 10px; font-weight: 400; opacity: 0.8; margin-bottom: 14px; font-style: italic; }
.rc-cover-divider { width: 50px; height: 2px; background: rgba(255,255,255,0.5); margin: 0 auto 14px; }
.rc-cover-title { font-size: 22px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 14px; }
.rc-cover-student { font-size: 14px; font-weight: 600; margin-bottom: 2px; }
.rc-cover-class { font-size: 11px; opacity: 0.85; }
.rc-cover-year { font-size: 10px; opacity: 0.7; margin-top: 6px; }
.rc-cover-watermark { position: absolute; bottom: 10px; right: 14px; font-size: 60px; opacity: 0.06; font-weight: 900; }

/* Face 2 - Inside Left (bottom-left in grid) */
.rc-face-inside-left {
    border-right: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
}
.rc-section-title { font-size: 12px; font-weight: 700; color: #4361ee; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 2px solid #4361ee; display: inline-block; }

.rc-info-row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #f3f4f6; font-size: 11px; }
.rc-info-label { color: #6b7280; font-weight: 600; }
.rc-info-value { color: #1a1a2e; font-weight: 700; }

.rc-conduct-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-top: 8px; }
.rc-conduct-item { background: #f8f9fa; border-radius: 6px; padding: 6px 8px; text-align: center; }
.rc-conduct-label { font-size: 9px; color: #6b7280; font-weight: 600; text-transform: uppercase; }
.rc-conduct-value { font-size: 14px; font-weight: 800; color: #4361ee; }

/* Face 3 - Inside Right (top-left in grid) */
.rc-face-inside-right {
    border-right: 1px solid #e5e7eb;
}
.rc-marks-table { width: 100%; border-collapse: collapse; font-size: 10px; }
.rc-marks-table th { background: #4361ee; color: #fff; padding: 6px 4px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; text-align: center; }
.rc-marks-table td { padding: 5px 4px; text-align: center; border-bottom: 1px solid #f0f0f0; }
.rc-marks-table tr:nth-child(even) { background: #f8f9ff; }
.rc-marks-table .rc-subject-name { text-align: left; font-weight: 600; color: #1a1a2e; }
.rc-marks-table .rc-grade-cell { font-weight: 800; }
.rc-marks-table .rc-total-row { background: #eef2ff !important; font-weight: 700; }

/* Face 4 - Back Cover (bottom-right in grid) */
.rc-face-back {
    border-left: 1px solid #e5e7eb;
}
.rc-grade-scale { width: 100%; border-collapse: collapse; font-size: 9px; margin-top: 6px; }
.rc-grade-scale th { background: #f3f4f6; color: #374151; padding: 4px; font-weight: 700; text-align: center; }
.rc-grade-scale td { padding: 3px 4px; text-align: center; border-bottom: 1px solid #f0f0f0; }

.rc-comment-box { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 8px; margin-top: 8px; min-height: 50px; background: #fafbfc; }
.rc-comment-label { font-size: 9px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 3px; }
.rc-comment-text { font-size: 10px; color: #1a1a2e; font-style: italic; }

.rc-signature-area { display: flex; justify-content: space-between; margin-top: auto; padding-top: 8px; }
.rc-sig-block { text-align: center; }
.rc-sig-line { width: 100px; border-bottom: 1px solid #374151; margin: 0 auto 3px; }
.rc-sig-label { font-size: 9px; font-weight: 600; color: #6b7280; }

.rc-stamp-area { position: absolute; bottom: 14px; right: 18px; width: 60px; height: 60px; border: 1.5px dashed #d1d5db; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 8px; color: #d1d5db; font-weight: 600; }

@media (max-width: 1200px) {
    .rc-card-sheet { width: 100%; height: auto; min-height: 600px; }
}
</style>
@endpush

@section('content')
{{-- Print Toolbar --}}
<div class="rc-no-print rc-print-toolbar">
    <div>
        <div class="rc-print-toolbar-title"><i class="fas fa-id-card me-2" style="color:#4361ee"></i>Report Cards Generated</div>
        <div class="rc-print-toolbar-info">{{ count($students) }} student(s) &middot; {{ $class->name ?? '' }} &middot; {{ $term->name ?? '' }} &middot; {{ $academicYear->name ?? '' }}</div>
    </div>
    <div class="rc-print-toolbar-actions">
        <a href="{{ route('admin.report-card.index') }}" class="btn-rc btn-rc-outline"><i class="fas fa-arrow-left"></i> Back</a>
        <button onclick="window.print()" class="btn-rc btn-rc-primary"><i class="fas fa-print"></i> Print All Cards</button>
    </div>
</div>

<div class="rc-print-area">
@foreach($students as $idx => $s)
<?php
    $student = $s['student'];
    $subjects = $s['subjects'];
    $grandTotal = $s['grandTotal'];
    $maxPossible = $s['maxPossible'];
    $percentage = $s['percentage'];
    $grade = $s['grade'];
    $rank = $s['rank'];
    $totalStudents = count($students);

    // Compute attendance stats (placeholder - can be enhanced later)
    $daysPresent = 0;
    $daysAbsent = 0;
    $totalDays = 0;

    // Get conduct marks average from CA fields
    $conductAvg = collect($subjects)->avg('ca_total');
?>
<div class="rc-card-sheet">
    {{-- FACE 3: Inside Right (top-left quadrant - marks table) --}}
    <div class="rc-face rc-face-inside-right">
        <div class="rc-section-title">Subject Performance</div>
        <table class="rc-marks-table">
            <thead>
                <tr>
                    <th style="width:35%;text-align:left;">Subject</th>
                    <th style="width:15%;">CA (30%)</th>
                    <th style="width:15%;">Exam (70%)</th>
                    <th style="width:15%;">Total</th>
                    <th style="width:10%;">Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subj)
                <tr>
                    <td class="rc-subject-name">{{ $subj['name'] }}</td>
                    <td>{{ $subj['ca_total'] ?? '-' }}</td>
                    <td>{{ $subj['exam_total'] ?? '-' }}</td>
                    <td style="font-weight:700;">{{ $subj['grand_total'] ?? '-' }}</td>
                    @php
                        $gClass = '';
                        if (in_array($subj['grade'], ['A+', 'A', 'A-'])) $gClass = 'color:#059669;';
                        elseif (in_array($subj['grade'], ['B+', 'B', 'B-'])) $gClass = 'color:#2563eb;';
                        elseif (in_array($subj['grade'], ['C+', 'C', 'C-'])) $gClass = 'color:#d97706;';
                        elseif (in_array($subj['grade'], ['D', 'D-'])) $gClass = 'color:#ea580c;';
                        elseif ($subj['grade'] === 'F') $gClass = 'color:#dc2626;';
                    @endphp
                    <td class="rc-grade-cell" style="{{ $gClass }}">{{ $subj['grade'] ?? '-' }}</td>
                </tr>
                @endforeach
                <tr class="rc-total-row">
                    <td class="rc-subject-name" style="font-weight:800;">TOTAL</td>
                    <td colspan="2"></td>
                    <td style="font-weight:800;font-size:12px;">{{ $grandTotal }} / {{ $maxPossible }}</td>
                    @php
                        $totalGClass = '';
                        if ($percentage >= 80) $totalGClass = 'color:#059669;';
                        elseif ($percentage >= 60) $totalGClass = 'color:#2563eb;';
                        elseif ($percentage >= 40) $totalGClass = 'color:#d97706;';
                        else $totalGClass = 'color:#dc2626;';
                    @endphp
                    <td class="rc-grade-cell" style="font-size:12px;{{ $totalGClass }}">{{ $grade }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Summary Stats --}}
        <div style="display:flex;gap:10px;margin-top:auto;padding-top:8px;">
            <div style="flex:1;background:#f8f9ff;border-radius:8px;padding:6px 10px;text-align:center;">
                <div style="font-size:9px;color:#6b7280;font-weight:600;">PERCENTAGE</div>
                <div style="font-size:16px;font-weight:800;color:#4361ee;">{{ $percentage }}%</div>
            </div>
            <div style="flex:1;background:#f8f9ff;border-radius:8px;padding:6px 10px;text-align:center;">
                <div style="font-size:9px;color:#6b7280;font-weight:600;">RANK</div>
                <div style="font-size:16px;font-weight:800;color:#4361ee;">{{ $rank }} / {{ $totalStudents }}</div>
            </div>
            <div style="flex:1;background:#f8f9ff;border-radius:8px;padding:6px 10px;text-align:center;">
                <div style="font-size:9px;color:#6b7280;font-weight:600;">GRADE</div>
                <div style="font-size:16px;font-weight:800;{{ $totalGClass }}">{{ $grade }}</div>
            </div>
        </div>
    </div>

    {{-- FACE 1: Front Cover (top-right quadrant) --}}
    <div class="rc-face rc-face-cover">
        @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="Logo" class="rc-cover-logo">
        @else
        <div style="width:70px;height:70px;background:rgba(255,255,255,0.15);border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
            <i class="fas fa-school" style="font-size:28px;color:rgba(255,255,255,0.6);"></i>
        </div>
        @endif
        <div class="rc-cover-school">{{ $schoolName }}</div>
        @if($schoolMotto)<div class="rc-cover-motto">{{ $schoolMotto }}</div>@endif
        <div class="rc-cover-divider"></div>
        <div class="rc-cover-title">Report Card</div>
        <div class="rc-cover-student">{{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}</div>
        <div class="rc-cover-class">{{ $class->name ?? '' }} @if($section) &middot; {{ $section->name }}@endif</div>
        <div class="rc-cover-year">{{ $academicYear->name ?? '' }} &middot; {{ $term->name ?? '' }}</div>
        @if($student->roll_number)
        <div style="margin-top:6px;font-size:9px;opacity:0.7;">Roll No: {{ $student->roll_number }}</div>
        @endif
        <div class="rc-cover-watermark">A+</div>
    </div>

    {{-- FACE 2: Inside Left (bottom-left quadrant) --}}
    <div class="rc-face rc-face-inside-left">
        <div class="rc-section-title">Student Information</div>
        <div class="rc-info-row">
            <span class="rc-info-label">Full Name</span>
            <span class="rc-info-value">{{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}</span>
        </div>
        <div class="rc-info-row">
            <span class="rc-info-label">Roll Number</span>
            <span class="rc-info-value">{{ $student->roll_number ?? '-' }}</span>
        </div>
        <div class="rc-info-row">
            <span class="rc-info-label">Admission No.</span>
            <span class="rc-info-value">{{ $student->admission_number ?? '-' }}</span>
        </div>
        <div class="rc-info-row">
            <span class="rc-info-label">Class &amp; Section</span>
            <span class="rc-info-value">{{ $class->name ?? '-' }} @if($section) / {{ $section->name }}@endif</span>
        </div>
        <div class="rc-info-row">
            <span class="rc-info-label">Academic Year</span>
            <span class="rc-info-value">{{ $academicYear->name ?? '-' }}</span>
        </div>
        <div class="rc-info-row">
            <span class="rc-info-label">Term</span>
            <span class="rc-info-value">{{ $term->name ?? '-' }}</span>
        </div>
        @if($student->guardian_name)
        <div class="rc-info-row">
            <span class="rc-info-label">Guardian</span>
            <span class="rc-info-value">{{ $student->guardian_name }}</span>
        </div>
        @endif
        @if($student->date_of_birth)
        <div class="rc-info-row">
            <span class="rc-info-label">Date of Birth</span>
            <span class="rc-info-value">{{ $student->date_of_birth->format('M d, Y') }}</span>
        </div>
        @endif

        <div class="rc-section-title" style="margin-top:12px;">Performance Summary</div>
        <div class="rc-conduct-grid">
            <div class="rc-conduct-item">
                <div class="rc-conduct-label">Total</div>
                <div class="rc-conduct-value">{{ $grandTotal }}</div>
            </div>
            <div class="rc-conduct-item">
                <div class="rc-conduct-label">Percentage</div>
                <div class="rc-conduct-value">{{ $percentage }}%</div>
            </div>
            <div class="rc-conduct-item">
                <div class="rc-conduct-label">Grade</div>
                <div class="rc-conduct-value">{{ $grade }}</div>
            </div>
            <div class="rc-conduct-item">
                <div class="rc-conduct-label">Rank</div>
                <div class="rc-conduct-value">{{ $rank }}</div>
            </div>
        </div>

        @if($student->gender)
        <div class="rc-section-title" style="margin-top:12px;">Additional Info</div>
        <div class="rc-info-row">
            <span class="rc-info-label">Gender</span>
            <span class="rc-info-value">{{ ucfirst($student->gender) }}</span>
        </div>
        @if($student->branch)
        <div class="rc-info-row">
            <span class="rc-info-label">Branch</span>
            <span class="rc-info-value">{{ $student->branch->name }}</span>
        </div>
        @endif
        @endif
    </div>

    {{-- FACE 4: Back Cover (bottom-right quadrant) --}}
    <div class="rc-face rc-face-back">
        <div class="rc-section-title">Grading Scale</div>
        <table class="rc-grade-scale">
            <thead>
                <tr><th>Range</th><th>Grade</th><th>Description</th></tr>
            </thead>
            <tbody>
                <tr><td>90-100</td><td style="font-weight:800;color:#059669;">A+</td><td>Outstanding</td></tr>
                <tr><td>85-89</td><td style="font-weight:700;color:#059669;">A</td><td>Excellent</td></tr>
                <tr><td>80-84</td><td style="font-weight:700;color:#10b981;">A-</td><td>Very Good</td></tr>
                <tr><td>75-79</td><td style="font-weight:700;color:#2563eb;">B+</td><td>Good</td></tr>
                <tr><td>70-74</td><td style="font-weight:700;color:#2563eb;">B</td><td>Above Average</td></tr>
                <tr><td>65-69</td><td style="font-weight:700;color:#3b82f6;">B-</td><td>Satisfactory</td></tr>
                <tr><td>60-64</td><td style="font-weight:700;color:#d97706;">C+</td><td>Average</td></tr>
                <tr><td>55-59</td><td style="font-weight:700;color:#d97706;">C</td><td>Below Average</td></tr>
                <tr><td>50-54</td><td style="font-weight:700;color:#ea580c;">C-</td><td>Poor</td></tr>
                <tr><td>40-49</td><td style="font-weight:700;color:#ea580c;">D</td><td>Very Poor</td></tr>
                <tr><td>Below 40</td><td style="font-weight:800;color:#dc2626;">F</td><td>Fail</td></tr>
            </tbody>
        </table>

        <div class="rc-section-title" style="margin-top:10px;">Comments</div>
        <div class="rc-comment-box">
            <div class="rc-comment-label">Class Teacher's Remark</div>
            <div class="rc-comment-text">
                @if($percentage >= 80)
                    Excellent performance! Keep up the great work.
                @elseif($percentage >= 60)
                    Good performance. Continue working hard to improve further.
                @elseif($percentage >= 40)
                    Satisfactory performance. More effort is needed in weaker subjects.
                @else
                    Needs significant improvement. Please focus on studies and seek extra help.
                @endif
            </div>
        </div>

        <div class="rc-signature-area">
            <div class="rc-sig-block">
                <div class="rc-sig-line"></div>
                <div class="rc-sig-label">Class Teacher</div>
            </div>
            <div class="rc-sig-block">
                <div class="rc-sig-line"></div>
                <div class="rc-sig-label">Parent/Guardian</div>
            </div>
            <div class="rc-sig-block">
                <div class="rc-sig-line"></div>
                <div class="rc-sig-label">Principal</div>
            </div>
        </div>

        <div class="rc-stamp-area">STAMP</div>
    </div>
</div>
@endforeach
</div>

@if(count($students) === 0)
<div class="rc-no-print" style="text-align:center;padding:3rem;background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,0.06);border:1px solid #f0f0f0;">
    <i class="fas fa-search" style="font-size:2rem;color:#d1d5db;margin-bottom:1rem;"></i>
    <h3 style="color:#6b7280;font-weight:700;">No Students Found</h3>
    <p style="color:#9ca3af;">No mark entries found for the selected filters. Please ensure marks have been entered for the selected academic year, term, and class.</p>
    <a href="{{ route('admin.report-card.index') }}" class="btn-rc btn-rc-outline" style="margin-top:1rem;"><i class="fas fa-arrow-left"></i> Go Back</a>
</div>
@endif
@endsection
