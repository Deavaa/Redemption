<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Transcript - {{ $student->full_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { size: A4 portrait; margin: 12mm 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; font-size: 9px; color: #1e293b; line-height: 1.4; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .page { page-break-after: always; position: relative; min-height: 275mm; }
        .page:last-child { page-break-after: auto; }

        /* Header */
        .transcript-header { display: flex; align-items: center; gap: 14px; border-bottom: 3px double #6366f1; padding-bottom: 8px; margin-bottom: 10px; }
        .header-logo { flex-shrink: 0; }
        .header-logo img { width: 60px; height: 60px; object-fit: contain; border-radius: 4px; }
        .header-center { flex: 1; text-align: center; }
        .school-name { font-size: 16px; font-weight: 800; color: #1e1b4b; letter-spacing: 1px; text-transform: uppercase; }
        .school-sub { font-size: 8px; color: #64748b; margin-top: 2px; }
        .doc-title { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; font-size: 12px; font-weight: 700; padding: 4px 22px; border-radius: 4px; margin: 5px 0 3px; letter-spacing: 2px; text-transform: uppercase; }
        .doc-number { font-size: 7.5px; color: #94a3b8; margin-top: 2px; }
        .header-photo { flex-shrink: 0; }
        .student-photo { width: 72px; height: 86px; object-fit: cover; border: 2px solid #6366f1; border-radius: 6px; background: #f1f5f9; }
        .student-photo-placeholder { width: 72px; height: 86px; border: 2px solid #c7d2fe; border-radius: 6px; background: #eef2ff; display: flex; align-items: center; justify-content: center; }
        .student-photo-placeholder svg { width: 32px; height: 32px; fill: #a5b4fc; }

        /* Student Info */
        .student-info { display: grid; grid-template-columns: 1fr 1fr; gap: 2px 20px; margin-bottom: 10px; border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden; padding: 5px 10px; background: #fafbfc; }
        .info-row { display: flex; padding: 2px 4px; }
        .info-label { font-weight: 600; color: #475569; min-width: 110px; font-size: 8px; }
        .info-value { color: #1e293b; font-size: 8px; }

        /* Year Section */
        .year-section { margin-bottom: 12px; }
        .year-section-title { background: linear-gradient(135deg, #1e1b4b, #312e81); color: #fff; padding: 5px 10px; font-size: 10px; font-weight: 700; border-radius: 4px 4px 0 0; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px; }
        .year-section-title .class-badge { background: rgba(255,255,255,0.15); padding: 2px 8px; border-radius: 3px; font-size: 8px; font-weight: 600; }
        .year-section-title .rank-badge { margin-left: auto; background: #c9a84c; color: #1e1b4b; padding: 2px 8px; border-radius: 3px; font-size: 8px; font-weight: 700; }

        /* Transcript Table */
        .transcript-table { width: 100%; border-collapse: collapse; font-size: 8.5px; margin-bottom: 0; }
        .transcript-table thead th { background: #4338ca; color: #fff; padding: 4px 6px; font-weight: 600; font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.3px; border: 1px solid #3730a3; text-align: center; }
        .transcript-table thead th.subject-col { text-align: left; min-width: 130px; background: #1e1b4b; }
        .transcript-table thead th.annual-header-cell { background: #312e81; font-weight: 700; }

        /* Body */
        .transcript-table tbody td { padding: 3px 6px; border: 1px solid #e2e8f0; text-align: center; }
        .transcript-table tbody td.subject-col { text-align: left; font-weight: 600; background: #f8fafc; min-width: 130px; }
        .transcript-table tbody tr:nth-child(even) td { background: #fafbfc; }
        .transcript-table tbody tr:nth-child(even) td.subject-col { background: #f1f5f9; }
        .transcript-table tbody tr:hover td { background: #eef2ff; }
        .transcript-table tbody tr:hover td.subject-col { background: #eef2ff; }
        .transcript-table tbody tr.total-row td { background: linear-gradient(135deg, #eef2ff, #f5f3ff) !important; font-weight: 700; border-top: 2px solid #6366f1; }
        .transcript-table tbody tr.total-row td.subject-col { background: linear-gradient(135deg, #eef2ff, #f5f3ff) !important; }
        .transcript-table tbody tr.average-row td { background: #f0fdf4 !important; font-weight: 700; border-top: 1px solid #86efac; }
        .transcript-table tbody tr.average-row td.subject-col { background: #f0fdf4 !important; }

        /* Score highlights */
        .score-high { color: #059669; font-weight: 700; }
        .score-low { color: #dc2626; font-weight: 600; }
        .score-null { color: #cbd5e1; }

        /* Fee Summary */
        .fee-section { margin-top: 10px; border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden; }
        .fee-header { background: linear-gradient(135deg, #f0fdf4, #ecfdf5); padding: 4px 10px; font-weight: 700; font-size: 8.5px; color: #166534; border-bottom: 1px solid #bbf7d0; }
        .fee-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1px; background: #e2e8f0; }
        .fee-item { background: #fff; padding: 4px 6px; text-align: center; }
        .fee-item-label { font-size: 7px; color: #64748b; text-transform: uppercase; font-weight: 600; }
        .fee-item-value { font-size: 10px; font-weight: 700; color: #1e293b; margin-top: 1px; }
        .fee-item-value.clear { color: #059669; }
        .fee-item-value.outstanding { color: #dc2626; }

        /* Grading Scale */
        .grading-section { margin-top: 8px; padding: 5px 10px; border: 1px solid #e2e8f0; border-radius: 4px; }
        .grading-title { font-size: 8px; font-weight: 700; color: #475569; margin-bottom: 3px; }
        .grading-scale { display: flex; flex-wrap: wrap; gap: 3px; }
        .grading-item { font-size: 7.5px; padding: 2px 6px; border-radius: 3px; border: 1px solid #e2e8f0; }
        .grading-item span { font-weight: 700; }
        .grade-a { background: #dcfce7; color: #166534; }
        .grade-b { background: #dbeafe; color: #1e40af; }
        .grade-c { background: #fef9c3; color: #854d0e; }
        .grade-d { background: #fed7aa; color: #9a3412; }
        .grade-f { background: #fecaca; color: #991b1b; }
        .grade-i { background: #e5e7eb; color: #6b7280; }

        /* Footer / Signatures */
        .footer-section { margin-top: 16px; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; text-align: center; margin-top: 40px; }
        .sig-line { border-top: 1px solid #94a3b8; padding-top: 4px; }
        .sig-title { font-size: 8px; font-weight: 700; color: #1e293b; text-transform: uppercase; }
        .sig-name { font-size: 7px; color: #64748b; }

        .official-stamp { text-align: center; margin-top: 10px; font-size: 7px; color: #94a3b8; border: 1px dashed #cbd5e1; padding: 4px; border-radius: 3px; }

        /* Watermark */
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 60px; font-weight: 800; color: rgba(99,102,241,0.03); text-transform: uppercase; letter-spacing: 10px; z-index: -1; pointer-events: none; }

        .print-btn { position: fixed; top: 10px; right: 10px; z-index: 999; background: #6366f1; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-size: 12px; cursor: pointer; font-family: 'Inter', sans-serif; font-weight: 600; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(99,102,241,0.3); transition: all 0.2s; }
        .print-btn:hover { background: #4f46e5; transform: translateY(-1px); }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
<button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Transcript</button>
<div class="watermark">TRANSCRIPT</div>

@php
    $schoolLogoUrl = \App\Models\Setting::getLogoUrl();
@endphp

<div class="page">
    {{-- Header with logo, school info, and student photo --}}
    <div class="transcript-header">
        <div class="header-logo">
            @if($schoolLogoUrl)
                <img src="{{ $schoolLogoUrl }}" alt="School Logo">
            @else
                <div style="width:60px;height:60px;background:#1e1b4b;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    <span style="color:#fff;font-size:20px;font-weight:800;">R</span>
                </div>
            @endif
        </div>
        <div class="header-center">
            <div class="school-name">{{ \App\Models\Setting::get('school_name', 'School of Redemption') }}</div>
            <div class="school-name-am" style="font-size:0.85rem;font-weight:600;color:#4338ca;margin-top:2px;">{{ \App\Models\Setting::get('school_name_am', 'ስኩል ኦፍ ሪደምሽን') }}</div>
            <div class="school-sub">{{ \App\Models\Setting::get('school_address', '') }} &bull; {{ \App\Models\Setting::get('school_phone', '') }} &bull; {{ \App\Models\Setting::get('school_email', '') }}</div>
            <div class="doc-title">Official Academic Transcript</div>
            <div class="doc-number">Certificate No: {{ $cert->certificate_number }} &bull; Issued: {{ $cert->issue_date->format('F d, Y') }}</div>
        </div>
        <div class="header-photo">
            @if($student->photo)
                <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->full_name }}" class="student-photo">
            @else
                <div class="student-photo-placeholder">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
            @endif
        </div>
    </div>

    {{-- Student Info --}}
    <div class="student-info">
        <div class="info-row"><span class="info-label">Full Name</span><span class="info-value">{{ $student->full_name }}</span></div>
        <div class="info-row"><span class="info-label">Admission No.</span><span class="info-value">{{ $student->admission_number ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Date of Birth</span><span class="info-value">{{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : '-' }}</span></div>
        <div class="info-row"><span class="info-label">Gender</span><span class="info-value">{{ ucfirst($student->gender ?? '-') }}</span></div>
        <div class="info-row"><span class="info-label">Nationality</span><span class="info-value">{{ $student->nationality ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Admission Date</span><span class="info-value">{{ $student->admission_date ? $student->admission_date->format('M d, Y') : '-' }}</span></div>
        <div class="info-row"><span class="info-label">Current Class</span><span class="info-value">{{ $student->classroom?->name ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Roll Number</span><span class="info-value">{{ $student->roll_number ?? '-' }}</span></div>
        @if($student->previous_school)
        <div class="info-row"><span class="info-label">Previous School</span><span class="info-value">{{ $student->previous_school }}</span></div>
        @endif
    </div>

    {{-- Academic Record: One table per year (portrait-friendly) --}}
    @foreach($yearColumns as $yi => $yc)
    <div class="year-section">
        <div class="year-section-title">
            <span>{{ $yc['year_name'] }}</span>
            <span class="class-badge">{{ $yc['class_name'] }}</span>
            @if($yearRanks[$yi])
            <span class="rank-badge"><i class="fas fa-trophy" style="font-size:7px;margin-right:3px;"></i>Rank: {{ $yearRanks[$yi] }}</span>
            @endif
        </div>
        <table class="transcript-table">
            <thead>
                <tr>
                    <th class="subject-col">Subject</th>
                    @foreach($allTermNames as $tName)
                        <th class="term-header-cell">{{ $tName }}</th>
                    @endforeach
                    <th class="annual-header-cell">Annual</th>
                </tr>
            </thead>
            <tbody>
                {{-- Subject rows for this year --}}
                @foreach($subjectRows as $subjectName => $yearData)
                    @php
                        $cellData = $yearData[$yi] ?? array_fill_keys(array_merge($allTermNames, ['annual']), null);
                        $hasAnyScore = false;
                        foreach ($allTermNames as $tName) {
                            if ($cellData[$tName] !== null) { $hasAnyScore = true; break; }
                        }
                        if (!$hasAnyScore && $cellData['annual'] !== null) $hasAnyScore = true;
                    @endphp
                    @if($hasAnyScore || $cellData['annual'] !== null)
                    <tr>
                        <td class="subject-col">{{ $subjectName }}</td>
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
                    </tr>
                    @endif
                @endforeach

                {{-- Total row --}}
                @php $yt = $yearTotals[$yi] ?? []; @endphp
                <tr class="total-row">
                    <td class="subject-col">Total</td>
                    @foreach($allTermNames as $tName)
                        <td>{{ $yt[$tName] ?? 0 }}</td>
                    @endforeach
                    <td>{{ $yt['annual'] ?? 0 }}</td>
                </tr>

                {{-- Average row --}}
                @php
                    $count = $yt['count'] ?? 1;
                @endphp
                <tr class="average-row">
                    <td class="subject-col">Average</td>
                    @foreach($allTermNames as $tName)
                        @php $tTotal = $yt[$tName] ?? 0; @endphp
                        <td>{{ $count > 0 ? round($tTotal / $count, 1) : 0 }}</td>
                    @endforeach
                    <td>{{ $yearAverages[$yi] ?? 0 }}</td>
                </tr>
            </tbody>
        </table>
    </div>
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
            <div class="grading-item grade-a"><span>A</span> 80-100</div>
            <div class="grading-item grade-b"><span>B</span> 60-79</div>
            <div class="grading-item grade-c"><span>C</span> 50-59</div>
            <div class="grading-item grade-d"><span>D</span> 40-49</div>
            <div class="grading-item grade-f"><span>F</span> &lt;40</div>
            <div class="grading-item grade-i"><span>I</span> Incomplete</div>
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
