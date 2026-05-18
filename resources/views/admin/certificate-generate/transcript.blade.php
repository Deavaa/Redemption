<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Transcript - {{ $student->first_name }} {{ $student->last_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { size: A4; margin: 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; font-size: 9.5px; color: #1e293b; line-height: 1.4; background: #fff; }
        .page { page-break-after: always; position: relative; min-height: 270mm; }
        .page:last-child { page-break-after: auto; }

        /* Header */
        .transcript-header { text-align: center; border-bottom: 3px double #6366f1; padding-bottom: 10px; margin-bottom: 14px; }
        .school-name { font-size: 16px; font-weight: 800; color: #1e1b4b; letter-spacing: 1px; text-transform: uppercase; }
        .school-sub { font-size: 9px; color: #64748b; margin-top: 2px; }
        .doc-title { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; font-size: 13px; font-weight: 700; padding: 4px 24px; border-radius: 4px; margin: 8px 0 4px; letter-spacing: 2px; text-transform: uppercase; }
        .doc-number { font-size: 8px; color: #94a3b8; margin-top: 2px; }

        /* Student Info Grid */
        .student-info { display: grid; grid-template-columns: 1fr 1fr; gap: 2px 20px; margin-bottom: 14px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .info-row { display: flex; padding: 3px 10px; border-bottom: 1px solid #f1f5f9; }
        .info-row:nth-child(odd) { background: #fafafe; }
        .info-label { font-weight: 600; color: #475569; min-width: 130px; font-size: 8.5px; }
        .info-value { color: #1e293b; font-size: 8.5px; }

        /* Academic Year Section */
        .year-section { margin-bottom: 14px; }
        .year-header { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; padding: 4px 10px; background: linear-gradient(135deg, #eef2ff, #f5f3ff); border-left: 3px solid #6366f1; border-radius: 0 4px 4px 0; }
        .year-title { font-size: 10px; font-weight: 700; color: #312e81; }
        .year-class { font-size: 8.5px; color: #6366f1; font-weight: 600; background: rgba(99,102,241,0.1); padding: 1px 8px; border-radius: 10px; }
        .year-rank { font-size: 8.5px; color: #059669; font-weight: 600; margin-left: auto; }

        /* Term Section */
        .term-section { margin-bottom: 8px; }
        .term-header { font-size: 9px; font-weight: 700; color: #475569; padding: 2px 8px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; margin-bottom: 3px; }

        /* Marks Table */
        .marks-table { width: 100%; border-collapse: collapse; font-size: 8.5px; margin-bottom: 4px; }
        .marks-table thead th { background: #1e1b4b; color: #fff; padding: 3px 6px; font-weight: 600; font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.3px; border: 1px solid #312e81; text-align: center; }
        .marks-table thead th:first-child { text-align: left; width: 30%; }
        .marks-table tbody td { padding: 2.5px 6px; border: 1px solid #e2e8f0; text-align: center; }
        .marks-table tbody td:first-child { text-align: left; font-weight: 500; }
        .marks-table tbody tr:nth-child(even) { background: #fafbfc; }
        .marks-table tbody tr:hover { background: #f0f0ff; }
        .grade-badge { display: inline-block; min-width: 22px; text-align: center; font-weight: 700; font-size: 8px; padding: 0 3px; border-radius: 3px; }
        .grade-a { background: #dcfce7; color: #166534; }
        .grade-b { background: #dbeafe; color: #1e40af; }
        .grade-c { background: #fef9c3; color: #854d0e; }
        .grade-d { background: #fed7aa; color: #9a3412; }
        .grade-f { background: #fecaca; color: #991b1b; }

        /* Term Summary */
        .term-summary { display: flex; gap: 12px; justify-content: flex-end; padding: 2px 6px; font-size: 8px; }
        .term-summary span { color: #64748b; }
        .term-summary strong { color: #1e1b4b; }

        /* Year Summary */
        .year-summary { display: flex; gap: 14px; justify-content: center; padding: 4px 10px; background: linear-gradient(135deg, #eef2ff, #faf5ff); border-radius: 4px; margin-top: 4px; border: 1px solid #e0e7ff; }
        .year-summary-item { font-size: 8.5px; color: #475569; }
        .year-summary-item strong { color: #1e1b4b; }

        /* Fee Summary */
        .fee-section { margin-top: 12px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .fee-header { background: linear-gradient(135deg, #f0fdf4, #ecfdf5); padding: 4px 10px; font-weight: 700; font-size: 9px; color: #166534; border-bottom: 1px solid #bbf7d0; }
        .fee-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1px; background: #e2e8f0; }
        .fee-item { background: #fff; padding: 4px 8px; text-align: center; }
        .fee-item-label { font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: 600; }
        .fee-item-value { font-size: 10px; font-weight: 700; color: #1e293b; margin-top: 1px; }
        .fee-item-value.clear { color: #059669; }
        .fee-item-value.outstanding { color: #dc2626; }

        /* Grading Scale */
        .grading-section { margin-top: 10px; padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 6px; }
        .grading-title { font-size: 8.5px; font-weight: 700; color: #475569; margin-bottom: 3px; }
        .grading-scale { display: flex; flex-wrap: wrap; gap: 4px; }
        .grading-item { font-size: 7.5px; padding: 1px 6px; border-radius: 3px; border: 1px solid #e2e8f0; }
        .grading-item span { font-weight: 700; }

        /* Footer / Signatures */
        .footer-section { margin-top: 20px; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; text-align: center; margin-top: 40px; }
        .sig-line { border-top: 1px solid #94a3b8; padding-top: 4px; }
        .sig-title { font-size: 8px; font-weight: 700; color: #1e293b; text-transform: uppercase; }
        .sig-name { font-size: 7.5px; color: #64748b; }

        .official-stamp { text-align: center; margin-top: 10px; font-size: 7.5px; color: #94a3b8; border: 1px dashed #cbd5e1; padding: 4px; border-radius: 4px; }

        /* Watermark */
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 60px; font-weight: 800; color: rgba(99,102,241,0.03); text-transform: uppercase; letter-spacing: 10px; z-index: -1; pointer-events: none; }

        .print-btn { position: fixed; top: 10px; right: 10px; z-index: 999; background: #6366f1; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-size: 12px; cursor: pointer; font-family: 'Inter', sans-serif; font-weight: 600; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(99,102,241,0.3); transition: all 0.2s; }
        .print-btn:hover { background: #4f46e5; transform: translateY(-1px); }
        @media print { .print-btn { display: none; } .watermark { color: rgba(99,102,241,0.03); } }
    </style>
</head>
<body>
<button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Transcript</button>
<div class="watermark">TRANSCRIPT</div>

<div class="page">
    {{-- Header --}}
    <div class="transcript-header">
        <div class="school-name">{{ \App\Models\Setting::get('school_name', 'School of Redemption') }}</div>
        <div class="school-sub">{{ \App\Models\Setting::get('school_address', '') }} &bull; {{ \App\Models\Setting::get('school_phone', '') }} &bull; {{ \App\Models\Setting::get('school_email', '') }}</div>
        <div class="doc-title">Official Academic Transcript</div>
        <div class="doc-number">Certificate No: {{ $cert->certificate_number }} &bull; Issued: {{ $cert->issue_date->format('F d, Y') }}</div>
    </div>

    {{-- Student Info --}}
    <div class="student-info">
        <div class="info-row"><span class="info-label">Full Name</span><span class="info-value">{{ $student->first_name }} {{ $student->last_name }}</span></div>
        <div class="info-row"><span class="info-label">Admission Number</span><span class="info-value">{{ $student->admission_number ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Date of Birth</span><span class="info-value">{{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : '-' }}</span></div>
        <div class="info-row"><span class="info-label">Gender</span><span class="info-value">{{ ucfirst($student->gender ?? '-') }}</span></div>
        <div class="info-row"><span class="info-label">Nationality</span><span class="info-value">{{ $student->nationality ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Admission Date</span><span class="info-value">{{ $student->admission_date ? $student->admission_date->format('M d, Y') : '-' }}</span></div>
        <div class="info-row"><span class="info-label">Current Class</span><span class="info-value">{{ $student->classroom?->name ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Roll Number</span><span class="info-value">{{ $student->roll_number ?? '-' }}</span></div>
        @if($student->parents && $student->parents->count() > 0)
        <div class="info-row"><span class="info-label">Parent/Guardian</span><span class="info-value">{{ $student->parents->first()->father_name ?? $student->parents->first()->guardian_name ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Parent Phone</span><span class="info-value">{{ $student->parents->first()->father_phone ?? $student->parents->first()->guardian_phone ?? '-' }}</span></div>
        @else
        <div class="info-row"><span class="info-label">Guardian Name</span><span class="info-value">{{ $student->guardian_name ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Guardian Phone</span><span class="info-value">{{ $student->guardian_phone ?? '-' }}</span></div>
        @endif
        <div class="info-row"><span class="info-label">Previous School</span><span class="info-value">{{ $student->previous_school ?? '-' }}</span></div>
    </div>

    {{-- Academic Years --}}
    @foreach($yearsData as $yi => $yearData)
    <div class="year-section">
        <div class="year-header">
            <span class="year-title">{{ $yearData['year_name'] }}</span>
            <span class="year-class">Class: {{ $yearData['class_name'] }}</span>
            @if($yearData['class_rank'])
            <span class="year-rank"><i class="fas fa-trophy" style="font-size:8px;"></i> Rank: {{ $yearData['class_rank'] }}</span>
            @endif
        </div>

        @foreach($yearData['terms'] as $termData)
        <div class="term-section">
            <div class="term-header">{{ $termData['term_name'] }}</div>
            <table class="marks-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>CA (30%)</th>
                        <th>Exam (70%)</th>
                        <th>Total (100)</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($termData['subjects'] as $subj)
                    <tr>
                        <td>{{ $subj['name'] }}</td>
                        <td>{{ $subj['ca_total'] ?? '-' }}</td>
                        <td>{{ $subj['exam_total'] ?? '-' }}</td>
                        <td><strong>{{ $subj['grand_total'] ?? '-' }}</strong></td>
                        <td>
                            @php
                                $gClass = 'grade-c';
                                $g = $subj['grade'] ?? '';
                                if (in_array($g, ['A+','A','A-'])) $gClass = 'grade-a';
                                elseif (in_array($g, ['B+','B','B-'])) $gClass = 'grade-b';
                                elseif (in_array($g, ['C+','C','C-'])) $gClass = 'grade-c';
                                elseif ($g === 'D') $gClass = 'grade-d';
                                elseif ($g === 'F') $gClass = 'grade-f';
                            @endphp
                            <span class="grade-badge {{ $gClass }}">{{ $g }}</span>
                        </td>
                        <td>{{ $subj['remarks'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="term-summary">
                <span>Total: <strong>{{ $termData['total'] }}</strong></span>
                <span>Average: <strong>{{ $termData['average'] }}</strong></span>
                <span>Subjects: <strong>{{ $termData['subject_count'] }}</strong></span>
            </div>
        </div>
        @endforeach

        <div class="year-summary">
            <span class="year-summary-item">Year Average: <strong>{{ $yearData['year_average'] }}</strong></span>
            <span class="year-summary-item">Terms: <strong>{{ count($yearData['terms']) }}</strong></span>
        </div>
    </div>

    {{-- Page break after every 2 years to avoid overflow --}}
    @if($yi % 2 === 1 && !$loop->last)
</div>
<div class="page">
    <div class="transcript-header" style="border-bottom:2px solid #6366f1;padding-bottom:6px;margin-bottom:10px;">
        <div class="school-name" style="font-size:12px;">{{ \App\Models\Setting::get('school_name', 'School of Redemption') }}</div>
        <div class="doc-title" style="font-size:10px;padding:2px 16px;">Academic Transcript (Continued)</div>
        <div class="doc-number">{{ $student->first_name }} {{ $student->last_name }} &bull; {{ $student->admission_number ?? '' }}</div>
    </div>
    @endif
    @endforeach

    {{-- Fee Summary --}}
    <div class="fee-section">
        <div class="fee-header"><i class="fas fa-receipt" style="margin-right:4px;"></i> Fee Payment Summary</div>
        <div class="fee-grid">
            <div class="fee-item">
                <div class="fee-item-label">Total Payments</div>
                <div class="fee-item-value">{{ $feeSummary->total_payments ?? 0 }}</div>
            </div>
            <div class="fee-item">
                <div class="fee-item-label">Total Paid</div>
                <div class="fee-item-value clear">{{ number_format($feeSummary->paid_amount ?? 0, 2) }}</div>
            </div>
            <div class="fee-item">
                <div class="fee-item-label">Outstanding</div>
                <div class="fee-item-value outstanding">{{ number_format(max(0, $feeSummary->outstanding ?? 0), 2) }}</div>
            </div>
            <div class="fee-item">
                <div class="fee-item-label">Status</div>
                <div class="fee-item-value {{ ($feeSummary->outstanding ?? 0) <= 0 ? 'clear' : 'outstanding' }}">{{ ($feeSummary->outstanding ?? 0) <= 0 ? 'CLEAR' : 'DUE' }}</div>
            </div>
        </div>
    </div>

    {{-- Grading Scale --}}
    <div class="grading-section">
        <div class="grading-title">Grading Scale</div>
        <div class="grading-scale">
            <div class="grading-item grade-a"><span>A+</span> 90-100</div>
            <div class="grading-item grade-a"><span>A</span> 80-89</div>
            <div class="grading-item grade-a"><span>A-</span> 75-79</div>
            <div class="grading-item grade-b"><span>B+</span> 70-74</div>
            <div class="grading-item grade-b"><span>B</span> 65-69</div>
            <div class="grading-item grade-b"><span>B-</span> 60-64</div>
            <div class="grading-item grade-c"><span>C+</span> 55-59</div>
            <div class="grading-item grade-c"><span>C</span> 50-54</div>
            <div class="grading-item grade-c"><span>C-</span> 45-49</div>
            <div class="grading-item grade-d"><span>D</span> 40-44</div>
            <div class="grading-item grade-f"><span>F</span> &lt;40</div>
        </div>
    </div>

    {{-- Signatures --}}
    <div class="footer-section">
        <div class="signatures">
            <div>
                <div class="sig-line">
                    <div class="sig-title">Principal</div>
                    <div class="sig-name">Signature & Stamp</div>
                </div>
            </div>
            <div>
                <div class="sig-line">
                    <div class="sig-title">Registrar</div>
                    <div class="sig-name">Signature & Stamp</div>
                </div>
            </div>
            <div>
                <div class="sig-line">
                    <div class="sig-title">Date Issued</div>
                    <div class="sig-name">{{ $cert->issue_date->format('M d, Y') }}</div>
                </div>
            </div>
        </div>
        <div class="official-stamp">OFFICIAL TRANSCRIPT &bull; This document is issued under the authority of {{ \App\Models\Setting::get('school_name', 'the School') }} &bull; Certificate No: {{ $cert->certificate_number }}</div>
    </div>
</div>
</body>
</html>
