<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Transcript - {{ $student->first_name }} {{ $student->last_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; font-size: 8px; color: #1e293b; line-height: 1.35; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .page { page-break-after: always; position: relative; min-height: 190mm; }
        .page:last-child { page-break-after: auto; }

        /* Header */
        .transcript-header { text-align: center; border-bottom: 3px double #6366f1; padding-bottom: 6px; margin-bottom: 8px; }
        .school-name { font-size: 14px; font-weight: 800; color: #1e1b4b; letter-spacing: 1px; text-transform: uppercase; }
        .school-sub { font-size: 8px; color: #64748b; margin-top: 1px; }
        .doc-title { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; font-size: 11px; font-weight: 700; padding: 3px 20px; border-radius: 4px; margin: 5px 0 3px; letter-spacing: 2px; text-transform: uppercase; }
        .doc-number { font-size: 7px; color: #94a3b8; margin-top: 1px; }

        /* Student Info */
        .student-info { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1px 16px; margin-bottom: 8px; border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden; padding: 3px 8px; background: #fafbfc; }
        .info-row { display: flex; padding: 1px 4px; }
        .info-label { font-weight: 600; color: #475569; min-width: 90px; font-size: 7px; }
        .info-value { color: #1e293b; font-size: 7px; }

        /* Transcript Table */
        .transcript-table { width: 100%; border-collapse: collapse; font-size: 7.5px; margin-bottom: 6px; }
        .transcript-table thead th { background: #1e1b4b; color: #fff; padding: 3px 4px; font-weight: 600; font-size: 6.5px; text-transform: uppercase; letter-spacing: 0.2px; border: 1px solid #312e81; text-align: center; }
        .transcript-table thead th.subject-col { text-align: left; min-width: 100px; position: sticky; left: 0; z-index: 2; background: #1e1b4b; }

        /* Year header row - spans termCount+1 (terms + annual) */
        .year-header-cell { background: #312e81 !important; font-size: 7.5px !important; font-weight: 700 !important; letter-spacing: 0.5px; padding: 3px 6px !important; border-bottom: 2px solid #c9a84c !important; }

        /* Term sub-header row */
        .term-header-cell { background: #4338ca !important; font-size: 6.5px !important; }
        .annual-header-cell { background: #3730a3 !important; font-weight: 700 !important; }

        /* Body */
        .transcript-table tbody td { padding: 2px 4px; border: 1px solid #e2e8f0; text-align: center; }
        .transcript-table tbody td.subject-col { text-align: left; font-weight: 600; background: #f8fafc; position: sticky; left: 0; z-index: 1; min-width: 100px; }
        .transcript-table tbody tr:nth-child(even) td { background: #fafbfc; }
        .transcript-table tbody tr:nth-child(even) td.subject-col { background: #f1f5f9; }
        .transcript-table tbody tr:hover td { background: #eef2ff; }
        .transcript-table tbody tr:hover td.subject-col { background: #eef2ff; }
        .transcript-table tbody tr.total-row td { background: linear-gradient(135deg, #eef2ff, #f5f3ff) !important; font-weight: 700; border-top: 2px solid #6366f1; }
        .transcript-table tbody tr.total-row td.subject-col { background: linear-gradient(135deg, #eef2ff, #f5f3ff) !important; }
        .transcript-table tbody tr.average-row td { background: #f0fdf4 !important; font-weight: 700; border-top: 1px solid #86efac; }
        .transcript-table tbody tr.average-row td.subject-col { background: #f0fdf4 !important; }
        .transcript-table tbody tr.rank-row td { background: #fffbeb !important; font-weight: 600; border-top: 1px solid #fde68a; }
        .transcript-table tbody tr.rank-row td.subject-col { background: #fffbeb !important; }

        /* Score highlights */
        .score-high { color: #059669; font-weight: 700; }
        .score-low { color: #dc2626; font-weight: 600; }
        .score-null { color: #cbd5e1; }

        /* Fee Summary */
        .fee-section { margin-top: 8px; border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden; }
        .fee-header { background: linear-gradient(135deg, #f0fdf4, #ecfdf5); padding: 3px 8px; font-weight: 700; font-size: 7.5px; color: #166534; border-bottom: 1px solid #bbf7d0; }
        .fee-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1px; background: #e2e8f0; }
        .fee-item { background: #fff; padding: 3px 6px; text-align: center; }
        .fee-item-label { font-size: 6.5px; color: #64748b; text-transform: uppercase; font-weight: 600; }
        .fee-item-value { font-size: 9px; font-weight: 700; color: #1e293b; margin-top: 1px; }
        .fee-item-value.clear { color: #059669; }
        .fee-item-value.outstanding { color: #dc2626; }

        /* Grading Scale */
        .grading-section { margin-top: 6px; padding: 4px 8px; border: 1px solid #e2e8f0; border-radius: 4px; }
        .grading-title { font-size: 7px; font-weight: 700; color: #475569; margin-bottom: 2px; }
        .grading-scale { display: flex; flex-wrap: wrap; gap: 3px; }
        .grading-item { font-size: 6.5px; padding: 1px 5px; border-radius: 2px; border: 1px solid #e2e8f0; }
        .grading-item span { font-weight: 700; }
        .grade-a { background: #dcfce7; color: #166534; }
        .grade-b { background: #dbeafe; color: #1e40af; }
        .grade-c { background: #fef9c3; color: #854d0e; }
        .grade-d { background: #fed7aa; color: #9a3412; }
        .grade-f { background: #fecaca; color: #991b1b; }

        /* Footer / Signatures */
        .footer-section { margin-top: 14px; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; text-align: center; margin-top: 30px; }
        .sig-line { border-top: 1px solid #94a3b8; padding-top: 3px; }
        .sig-title { font-size: 7px; font-weight: 700; color: #1e293b; text-transform: uppercase; }
        .sig-name { font-size: 6.5px; color: #64748b; }

        .official-stamp { text-align: center; margin-top: 8px; font-size: 6.5px; color: #94a3b8; border: 1px dashed #cbd5e1; padding: 3px; border-radius: 3px; }

        /* Watermark */
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 50px; font-weight: 800; color: rgba(99,102,241,0.03); text-transform: uppercase; letter-spacing: 8px; z-index: -1; pointer-events: none; }

        .print-btn { position: fixed; top: 10px; right: 10px; z-index: 999; background: #6366f1; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-size: 12px; cursor: pointer; font-family: 'Inter', sans-serif; font-weight: 600; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(99,102,241,0.3); transition: all 0.2s; }
        .print-btn:hover { background: #4f46e5; transform: translateY(-1px); }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
<button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Transcript</button>
<div class="watermark">TRANSCRIPT</div>

@php
    $yearCount = count($yearColumns);
    // Determine how many years fit per page (landscape A4 ~ 270mm usable width)
    // Subject col ~100px, each year needs (termCount + 1 for annual) * ~36px
    // With 2 terms: 3 cols * 36 = 108px per year + 100px subject = ~208px for 1 year
    // Available width ~960px, so max ~7-8 years with 2 terms on landscape
    $yearsPerPage = max(1, $yearCount); // All years in one table if possible
@endphp

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
        <div class="info-row"><span class="info-label">Admission No.</span><span class="info-value">{{ $student->admission_number ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Date of Birth</span><span class="info-value">{{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : '-' }}</span></div>
        <div class="info-row"><span class="info-label">Gender</span><span class="info-value">{{ ucfirst($student->gender ?? '-') }}</span></div>
        <div class="info-row"><span class="info-label">Nationality</span><span class="info-value">{{ $student->nationality ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Admission Date</span><span class="info-value">{{ $student->admission_date ? $student->admission_date->format('M d, Y') : '-' }}</span></div>
        <div class="info-row"><span class="info-label">Current Class</span><span class="info-value">{{ $student->classroom?->name ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Roll Number</span><span class="info-value">{{ $student->roll_number ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Previous School</span><span class="info-value">{{ $student->previous_school ?? '-' }}</span></div>
    </div>

    {{-- Main Transcript Table: Subjects as rows, Years as column groups --}}
    <table class="transcript-table">
        <thead>
            {{-- Row 1: Year headers (each spans termCount + 1 for annual) --}}
            <tr>
                <th class="subject-col" rowspan="2">Subject</th>
                @foreach($yearColumns as $yi => $yc)
                    <th class="year-header-cell" colspan="{{ $termCount + 1 }}">
                        {{ $yc['year_name'] }} &mdash; {{ $yc['class_name'] }}
                    </th>
                @endforeach
            </tr>
            {{-- Row 2: Term sub-headers + Annual --}}
            <tr>
                @foreach($yearColumns as $yi => $yc)
                    @foreach($allTermNames as $tName)
                        <th class="term-header-cell">{{ $tName }}</th>
                    @endforeach
                    <th class="term-header-cell annual-header-cell">Annual</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{-- Subject rows --}}
            @foreach($subjectRows as $subjectName => $yearData)
            <tr>
                <td class="subject-col">{{ $subjectName }}</td>
                @foreach($yearColumns as $yi => $yc)
                    @php $cellData = $yearData[$yi] ?? array_fill_keys(array_merge($allTermNames, ['annual']), null); @endphp
                    @foreach($allTermNames as $tName)
                        @php
                            $val = $cellData[$tName] ?? null;
                            $cls = '';
                            if ($val === null) $cls = 'score-null';
                            elseif ($val >= 80) $cls = 'score-high';
                            elseif ($val < 50) $cls = 'score-low';
                        @endphp
                        <td class="{{ $cls }}">{{ $val !== null ? $val : '&mdash;' }}</td>
                    @endforeach
                    @php
                        $annualVal = $cellData['annual'] ?? null;
                        $annualCls = '';
                        if ($annualVal === null) $annualCls = 'score-null';
                        elseif ($annualVal >= 80) $annualCls = 'score-high';
                        elseif ($annualVal < 50) $annualCls = 'score-low';
                    @endphp
                    <td class="{{ $annualCls }}" style="font-weight:700;">{{ $annualVal !== null ? $annualVal : '&mdash;' }}</td>
                @endforeach
            </tr>
            @endforeach

            {{-- Total row --}}
            <tr class="total-row">
                <td class="subject-col">Total</td>
                @foreach($yearColumns as $yi => $yc)
                    @php $yt = $yearTotals[$yi] ?? []; @endphp
                    @foreach($allTermNames as $tName)
                        <td>{{ $yt[$tName] ?? 0 }}</td>
                    @endforeach
                    <td>{{ $yt['annual'] ?? 0 }}</td>
                @endforeach
            </tr>

            {{-- Average row --}}
            <tr class="average-row">
                <td class="subject-col">Average</td>
                @foreach($yearColumns as $yi => $yc)
                    @php
                        $yt = $yearTotals[$yi] ?? [];
                        $count = $yt['count'] ?? 1;
                    @endphp
                    @foreach($allTermNames as $tName)
                        @php $tTotal = $yt[$tName] ?? 0; @endphp
                        <td>{{ $count > 0 ? round($tTotal / $count, 1) : 0 }}</td>
                    @endforeach
                    <td>{{ $yearAverages[$yi] ?? 0 }}</td>
                @endforeach
            </tr>

            {{-- Rank row --}}
            <tr class="rank-row">
                <td class="subject-col">Class Rank</td>
                @foreach($yearColumns as $yi => $yc)
                    <td colspan="{{ $termCount + 1 }}">
                        @if($yearRanks[$yi])
                            <i class="fas fa-trophy" style="font-size:6px;color:#d97706;"></i> {{ $yearRanks[$yi] }}
                        @else
                            &mdash;
                        @endif
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>

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
        <div class="official-stamp">OFFICIAL TRANSCRIPT &bull; Issued under the authority of {{ \App\Models\Setting::get('school_name', 'the School') }} &bull; Certificate No: {{ $cert->certificate_number }}</div>
    </div>
</div>
</body>
</html>
