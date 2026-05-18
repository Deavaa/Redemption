@extends('layouts.admin')
@section('title', 'Report Card')

@push('styles')
<style>
@media print {
    body * { visibility: hidden; }
    .rc-print-area, .rc-print-area * { visibility: visible; }
    .rc-print-area { position: absolute; left: 0; top: 0; width: 100%; }
    .rc-no-print { display: none !important; }
    .rc-sheet { page-break-after: always; margin: 0 !important; box-shadow: none !important; border: none !important; border-radius: 0 !important; }
    .rc-sheet:last-child { page-break-after: auto; }
    @page { size: landscape; margin: 0; }
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

/* Sheet: A4 Landscape with two equal columns */
.rc-sheet {
    width: 297mm;
    min-height: 210mm;
    margin: 0 auto 2rem;
    background: #fff;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    overflow: hidden;
}

/* Center divider */
.rc-sheet::before {
    content: '';
    position: absolute;
    left: 50%;
    top: 0;
    bottom: 0;
    width: 0;
    border-left: 2px dashed #d1d5db;
    z-index: 10;
    pointer-events: none;
}

/* Each column */
.rc-col {
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    font-family: 'Inter', 'Noto Sans SC', sans-serif;
    overflow: hidden;
    position: relative;
}

/* School Header */
.rc-school-header { text-align: center; margin-bottom: 10px; }
.rc-school-logo { max-height: 50px; max-width: 80px; margin-bottom: 4px; }
.rc-school-name { font-size: 14px; font-weight: 800; color: #1a1a2e; letter-spacing: 0.5px; }
.rc-school-motto { font-size: 8px; color: #6b7280; font-style: italic; }
.rc-school-divider { width: 60px; height: 2px; background: #4361ee; margin: 4px auto; }
.rc-report-title { font-size: 12px; font-weight: 800; color: #4361ee; letter-spacing: 1px; text-transform: uppercase; }

/* Student Info Bar */
.rc-stu-info { display: flex; flex-wrap: wrap; gap: 4px 12px; margin-bottom: 8px; font-size: 9px; }
.rc-stu-info-item { display: flex; gap: 2px; }
.rc-stu-info-label { color: #6b7280; font-weight: 600; }
.rc-stu-info-value { color: #1a1a2e; font-weight: 700; }

/* Subject Marks Table */
.rc-marks { width: 100%; border-collapse: collapse; font-size: 8.5px; }
.rc-marks th { padding: 4px 3px; font-weight: 700; text-align: center; border: 1px solid #d1d5db; white-space: nowrap; }
.rc-marks td { padding: 3px; text-align: center; border: 1px solid #e5e7eb; }
.rc-marks .subj-name { text-align: left; font-weight: 600; color: #1a1a2e; white-space: nowrap; }

/* Group headers */
.rc-marks .grp-t1 { background: #dbeafe; color: #1e40af; }
.rc-marks .grp-t2 { background: #ede9fe; color: #5b21b6; }
.rc-marks .grp-ann { background: #d1fae5; color: #065f46; }

.rc-marks .row-t1 { background: #f8fbff; }
.rc-marks .row-t2 { background: #faf8ff; }
.rc-marks .row-ann { background: #f0fdf4; font-weight: 700; }

.rc-marks .total-row td { background: #eef2ff !important; font-weight: 800; border-top: 2px solid #4361ee; }
.rc-marks .grade-fail { color: #dc2626; font-weight: 800; }

/* Right Column Styles */
.rc-section-title { font-size: 10px; font-weight: 700; color: #4361ee; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; padding-bottom: 4px; border-bottom: 2px solid #4361ee; }

/* Performance Stats */
.rc-perf-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px; margin-bottom: 10px; }
.rc-perf-item { background: #f8f9ff; border-radius: 8px; padding: 6px; text-align: center; border: 1px solid #e5e7eb; }
.rc-perf-label { font-size: 7px; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.rc-perf-value { font-size: 16px; font-weight: 800; color: #4361ee; }

/* Grade Scale */
.rc-grade-scale { width: 100%; border-collapse: collapse; font-size: 8px; }
.rc-grade-scale th { background: #f3f4f6; color: #374151; padding: 3px 4px; font-weight: 700; text-align: center; }
.rc-grade-scale td { padding: 2px 4px; text-align: center; border-bottom: 1px solid #f0f0f0; }

/* Comment Box */
.rc-comment-box { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 6px 8px; margin-top: 6px; min-height: 40px; background: #fafbfc; }
.rc-comment-label { font-size: 8px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 2px; }
.rc-comment-text { font-size: 9px; color: #1a1a2e; font-style: italic; line-height: 1.4; }

/* Signature Area */
.rc-signature-area { display: flex; justify-content: space-between; margin-top: auto; padding-top: 8px; }
.rc-sig-block { text-align: center; }
.rc-sig-line { width: 90px; border-bottom: 1px solid #374151; margin: 0 auto 2px; }
.rc-sig-label { font-size: 8px; font-weight: 600; color: #6b7280; }

/* Stamp */
.rc-stamp-area { position: absolute; bottom: 12px; right: 16px; width: 50px; height: 50px; border: 1.5px dashed #d1d5db; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 7px; color: #d1d5db; font-weight: 600; }

@media (max-width: 1200px) {
    .rc-sheet { width: 100%; height: auto; min-height: auto; }
}
</style>
@endpush

@section('content')
{{-- Print Toolbar --}}
<div class="rc-no-print rc-print-toolbar">
    <div>
        <div class="rc-print-toolbar-title"><i class="fas fa-id-card me-2" style="color:#4361ee"></i>Annual Report Cards Generated</div>
        <div class="rc-print-toolbar-info">{{ count($students) }} student(s) &middot; {{ $class->name ?? '' }} &middot; {{ $academicYear->name ?? '' }}</div>
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
    $annualGrandTotal = $s['annualGrandTotal'];
    $maxPossible = $s['maxPossible'];
    $percentage = $s['percentage'];
    $grade = $s['grade'];
    $rank = $s['rank'];
    $totalStudents = count($students);

    $t1Name = $term1 ? $term1->name : 'Term 1';
    $t2Name = $term2 ? $term2->name : 'Term 2';

    // Grade color helper
    function gradeColor($g) {
        if (in_array($g, ['A+','A','A-'])) return 'color:#059669;';
        if (in_array($g, ['B+','B','B-'])) return 'color:#2563eb;';
        if (in_array($g, ['C+','C','C-'])) return 'color:#d97706;';
        if (in_array($g, ['D','D-'])) return 'color:#ea580c;';
        if ($g === 'F') return 'color:#dc2626;';
        return '';
    }

    $totalGStyle = '';
    if ($percentage >= 80) $totalGStyle = 'color:#059669;';
    elseif ($percentage >= 60) $totalGStyle = 'color:#2563eb;';
    elseif ($percentage >= 40) $totalGStyle = 'color:#d97706;';
    else $totalGStyle = 'color:#dc2626;';
?>
<div class="rc-sheet">
    {{-- LEFT COLUMN: School header + Subject marks table --}}
    <div class="rc-col" style="border-right:1px solid #e5e7eb">
        {{-- School Header --}}
        <div class="rc-school-header">
            @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="Logo" class="rc-school-logo">
            @else
            <div style="width:40px;height:40px;background:#eef2ff;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:4px;">
                <i class="fas fa-school" style="font-size:18px;color:#4361ee;"></i>
            </div>
            @endif
            <div class="rc-school-name">{{ $schoolName }}</div>
            @if($schoolMotto)<div class="rc-school-motto">{{ $schoolMotto }}</div>@endif
            <div class="rc-school-divider"></div>
            <div class="rc-report-title">Annual Report Card</div>
        </div>

        {{-- Student Info --}}
        <div class="rc-stu-info">
            <div class="rc-stu-info-item"><span class="rc-stu-info-label">Name:</span> <span class="rc-stu-info-value">{{ $student->full_name ?? '' }}</span></div>
            <div class="rc-stu-info-item"><span class="rc-stu-info-label">Roll:</span> <span class="rc-stu-info-value">{{ $student->roll_number ?? '-' }}</span></div>
            <div class="rc-stu-info-item"><span class="rc-stu-info-label">Class:</span> <span class="rc-stu-info-value">{{ $class->name ?? '-' }}@if($section) / {{ $section->name }}@endif</span></div>
            <div class="rc-stu-info-item"><span class="rc-stu-info-label">Year:</span> <span class="rc-stu-info-value">{{ $academicYear->name ?? '-' }}</span></div>
            @if($student->admission_number)<div class="rc-stu-info-item"><span class="rc-stu-info-label">Adm:</span> <span class="rc-stu-info-value">{{ $student->admission_number }}</span></div>@endif
        </div>

        {{-- Subject Marks Table --}}
        <table class="rc-marks">
            <thead>
                <tr>
                    <th rowspan="2" style="width:28%;text-align:left;background:#f8fafc">Subject</th>
                    @if($term1)
                    <th colspan="3" class="grp-t1">{{ $t1Name }}</th>
                    @endif
                    @if($term2)
                    <th colspan="3" class="grp-t2">{{ $t2Name }}</th>
                    @endif
                    <th colspan="2" class="grp-ann">Annual</th>
                </tr>
                <tr>
                    @if($term1)
                    <th class="grp-t1" style="font-size:7.5px">CA</th>
                    <th class="grp-t1" style="font-size:7.5px">Exam</th>
                    <th class="grp-t1" style="font-size:7.5px">Total</th>
                    @endif
                    @if($term2)
                    <th class="grp-t2" style="font-size:7.5px">CA</th>
                    <th class="grp-t2" style="font-size:7.5px">Exam</th>
                    <th class="grp-t2" style="font-size:7.5px">Total</th>
                    @endif
                    <th class="grp-ann" style="font-size:7.5px">Avg</th>
                    <th class="grp-ann" style="font-size:7.5px">Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subj)
                <tr>
                    <td class="subj-name">{{ $subj['name'] }}</td>
                    @if($term1)
                    <td class="row-t1">{{ $subj['t1_ca'] ?? '-' }}</td>
                    <td class="row-t1">{{ $subj['t1_exam'] ?? '-' }}</td>
                    <td class="row-t1" style="font-weight:700">{{ $subj['t1_total'] ?? '-' }}</td>
                    @endif
                    @if($term2)
                    <td class="row-t2">{{ $subj['t2_ca'] ?? '-' }}</td>
                    <td class="row-t2">{{ $subj['t2_exam'] ?? '-' }}</td>
                    <td class="row-t2" style="font-weight:700">{{ $subj['t2_total'] ?? '-' }}</td>
                    @endif
                    <td class="row-ann" style="font-weight:800">{{ $subj['ann_total'] ?? '-' }}</td>
                    <td class="row-ann {{ $subj['ann_grade'] === 'F' ? 'grade-fail' : '' }}" style="{{ gradeColor($subj['ann_grade'] ?? '') }}">{{ $subj['ann_grade'] ?? '-' }}</td>
                </tr>
                @endforeach

                {{-- Total Row --}}
                <tr class="total-row">
                    <td class="subj-name" style="font-weight:800">TOTAL</td>
                    @if($term1)
                    <td colspan="2"></td>
                    <td style="font-weight:800">{{ collect($subjects)->sum(function($s) { return floatval($s['t1_total'] ?? 0); }) }}</td>
                    @endif
                    @if($term2)
                    <td colspan="2"></td>
                    <td style="font-weight:800">{{ collect($subjects)->sum(function($s) { return floatval($s['t2_total'] ?? 0); }) }}</td>
                    @endif
                    <td style="font-weight:900;font-size:10px">{{ $annualGrandTotal }}</td>
                    <td style="font-weight:900;font-size:10px;{{ $totalGStyle }}">{{ $grade }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Term Summary --}}
        <div style="display:flex;gap:6px;margin-top:auto;padding-top:6px;">
            @if($term1)
            <div style="flex:1;background:#eff6ff;border-radius:6px;padding:4px 6px;text-align:center;border:1px solid #bfdbfe">
                <div style="font-size:7px;color:#1e40af;font-weight:700">{{ $t1Name }}</div>
                <div style="font-size:11px;font-weight:800;color:#1e40af">{{ collect($subjects)->sum(function($s) { return floatval($s['t1_total'] ?? 0); }) }}</div>
            </div>
            @endif
            @if($term2)
            <div style="flex:1;background:#f5f3ff;border-radius:6px;padding:4px 6px;text-align:center;border:1px solid #ddd6fe">
                <div style="font-size:7px;color:#5b21b6;font-weight:700">{{ $t2Name }}</div>
                <div style="font-size:11px;font-weight:800;color:#5b21b6">{{ collect($subjects)->sum(function($s) { return floatval($s['t2_total'] ?? 0); }) }}</div>
            </div>
            @endif
            <div style="flex:1;background:#ecfdf5;border-radius:6px;padding:4px 6px;text-align:center;border:1px solid #a7f3d0">
                <div style="font-size:7px;color:#065f46;font-weight:700">ANNUAL</div>
                <div style="font-size:11px;font-weight:800;color:#065f46">{{ $annualGrandTotal }}</div>
            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN: Performance summary + Grading scale + Comments + Signatures --}}
    <div class="rc-col">
        {{-- Student Info Extended --}}
        <div class="rc-section-title">Student Details</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:3px 10px;margin-bottom:10px;font-size:9px;">
            <div><span style="color:#6b7280;font-weight:600">Full Name:</span> <strong>{{ $student->full_name ?? '' }}</strong></div>
            <div><span style="color:#6b7280;font-weight:600">Roll No:</span> <strong>{{ $student->roll_number ?? '-' }}</strong></div>
            <div><span style="color:#6b7280;font-weight:600">Class:</span> <strong>{{ $class->name ?? '-' }}@if($section) / {{ $section->name }}@endif</strong></div>
            <div><span style="color:#6b7280;font-weight:600">Year:</span> <strong>{{ $academicYear->name ?? '-' }}</strong></div>
            @if($student->date_of_birth)<div><span style="color:#6b7280;font-weight:600">DOB:</span> <strong>{{ $student->date_of_birth->format('M d, Y') }}</strong></div>@endif
            @if($student->guardian_name)<div><span style="color:#6b7280;font-weight:600">Guardian:</span> <strong>{{ $student->guardian_name }}</strong></div>@endif
            @if($student->gender)<div><span style="color:#6b7280;font-weight:600">Gender:</span> <strong>{{ ucfirst($student->gender) }}</strong></div>@endif
        </div>

        {{-- Performance Summary --}}
        <div class="rc-section-title">Performance Summary</div>
        <div class="rc-perf-grid">
            <div class="rc-perf-item">
                <div class="rc-perf-label">Percentage</div>
                <div class="rc-perf-value">{{ $percentage }}%</div>
            </div>
            <div class="rc-perf-item">
                <div class="rc-perf-label">Grade</div>
                <div class="rc-perf-value" style="{{ $totalGStyle }}">{{ $grade }}</div>
            </div>
            <div class="rc-perf-item">
                <div class="rc-perf-label">Rank</div>
                <div class="rc-perf-value">{{ $rank }}/{{ $totalStudents }}</div>
            </div>
        </div>

        {{-- Grading Scale --}}
        <div class="rc-section-title">Grading Scale</div>
        <table class="rc-grade-scale">
            <thead><tr><th>Range</th><th>Grade</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>90-100</td><td style="font-weight:800;color:#059669">A+</td><td>Outstanding</td></tr>
                <tr><td>80-89</td><td style="font-weight:700;color:#059669">A</td><td>Excellent</td></tr>
                <tr><td>75-79</td><td style="font-weight:700;color:#10b981">A-</td><td>Very Good</td></tr>
                <tr><td>70-74</td><td style="font-weight:700;color:#2563eb">B+</td><td>Good</td></tr>
                <tr><td>65-69</td><td style="font-weight:700;color:#2563eb">B</td><td>Above Average</td></tr>
                <tr><td>60-64</td><td style="font-weight:700;color:#3b82f6">B-</td><td>Satisfactory</td></tr>
                <tr><td>55-59</td><td style="font-weight:700;color:#d97706">C+</td><td>Average</td></tr>
                <tr><td>50-54</td><td style="font-weight:700;color:#d97706">C</td><td>Below Average</td></tr>
                <tr><td>45-49</td><td style="font-weight:700;color:#ea580c">C-</td><td>Poor</td></tr>
                <tr><td>40-44</td><td style="font-weight:700;color:#ea580c">D</td><td>Very Poor</td></tr>
                <tr><td>&lt;40</td><td style="font-weight:800;color:#dc2626">F</td><td>Fail</td></tr>
            </tbody>
        </table>

        {{-- Mark Distribution --}}
        <div class="rc-section-title" style="margin-top:8px;">Mark Distribution</div>
        <div style="font-size:8px;color:#6b7280;line-height:1.6;">
            <div style="display:flex;gap:4px;margin-bottom:2px;"><span style="background:#dbeafe;color:#1e40af;padding:1px 6px;border-radius:3px;font-weight:700;flex-shrink:0">CA /30</span> Continuous Assessment (raw /70 scaled to /30)</div>
            <div style="display:flex;gap:4px;"><span style="background:#fef3c7;color:#92400e;padding:1px 6px;border-radius:3px;font-weight:700;flex-shrink:0">Exam /70</span> Test1(/10) + Test2(/10) + MidTerm(/20) + Final(/30)</div>
        </div>

        {{-- Comments --}}
        <div class="rc-section-title" style="margin-top:8px;">Teacher's Remark</div>
        <div class="rc-comment-box">
            <div class="rc-comment-text">
                @if($percentage >= 80)
                    Outstanding performance this year! Keep up the excellent work and continue to excel in all subjects.
                @elseif($percentage >= 60)
                    Good performance this year. Continue working hard to improve further in the coming year.
                @elseif($percentage >= 40)
                    Satisfactory performance. More effort is needed in weaker subjects to achieve better results.
                @else
                    Needs significant improvement. Please focus on studies and seek extra help in weak areas.
                @endif
            </div>
        </div>

        {{-- Signatures --}}
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
    <p style="color:#9ca3af;">No mark entries found for the selected filters. Please ensure marks have been entered for the selected academic year and class.</p>
    <a href="{{ route('admin.report-card.index') }}" class="btn-rc btn-rc-outline" style="margin-top:1rem;"><i class="fas fa-arrow-left"></i> Go Back</a>
</div>
@endif
@endsection
