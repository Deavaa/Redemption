<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Leaving Clearance Certificate - {{ $student->full_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { size: A4; margin: 15mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; font-size: 10px; color: #1e293b; line-height: 1.5; background: #fff; }
        .page { page-break-after: always; position: relative; }
        .page:last-child { page-break-after: auto; }

        /* Header */
        .lc-header { text-align: center; border-bottom: 3px double #dc2626; padding-bottom: 10px; margin-bottom: 16px; }
        .school-name { font-size: 17px; font-weight: 800; color: #1e1b4b; letter-spacing: 1px; text-transform: uppercase; }
        .school-sub { font-size: 9px; color: #64748b; margin-top: 2px; }
        .doc-title { display: inline-block; background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; font-size: 14px; font-weight: 700; padding: 5px 28px; border-radius: 4px; margin: 8px 0 4px; letter-spacing: 2px; text-transform: uppercase; }
        .doc-number { font-size: 8px; color: #94a3b8; margin-top: 2px; }

        /* Clearance Status Banner */
        .clearance-banner { text-align: center; padding: 8px; border-radius: 6px; margin-bottom: 14px; font-weight: 700; font-size: 11px; letter-spacing: 1px; }
        .clearance-banner.cleared { background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #166534; border: 1px solid #86efac; }
        .clearance-banner.not-cleared { background: linear-gradient(135deg, #fef2f2, #fecaca); color: #991b1b; border: 1px solid #fca5a5; }

        /* Student Info Grid */
        .student-info { display: grid; grid-template-columns: 1fr 1fr; gap: 2px 20px; margin-bottom: 14px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .info-row { display: flex; padding: 3px 10px; border-bottom: 1px solid #f1f5f9; }
        .info-row:nth-child(odd) { background: #fafafe; }
        .info-label { font-weight: 600; color: #475569; min-width: 140px; font-size: 9px; }
        .info-value { color: #1e293b; font-size: 9px; }

        /* Formal Text */
        .formal-text { font-size: 10px; line-height: 1.7; margin-bottom: 14px; text-align: justify; }
        .formal-text strong { color: #1e1b4b; }

        /* Clearance Checklist */
        .clearance-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .clearance-table thead th { background: #1e1b4b; color: #fff; padding: 5px 10px; font-weight: 600; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #312e81; text-align: left; }
        .clearance-table thead th:last-child { text-align: center; width: 100px; }
        .clearance-table tbody td { padding: 5px 10px; border: 1px solid #e2e8f0; font-size: 9px; }
        .clearance-table tbody td:last-child { text-align: center; }
        .clearance-table tbody tr:nth-child(even) { background: #fafbfc; }
        .status-badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
        .status-cleared { background: #dcfce7; color: #166534; }
        .status-outstanding { background: #fecaca; color: #991b1b; }
        .status-pending { background: #fef9c3; color: #854d0e; }

        /* Academic Summary */
        .academic-section { margin-bottom: 14px; }
        .section-title { font-size: 10.5px; font-weight: 700; color: #1e1b4b; padding: 4px 10px; background: linear-gradient(135deg, #eef2ff, #f5f3ff); border-left: 3px solid #6366f1; border-radius: 0 4px 4px 0; margin-bottom: 6px; }
        .academic-table { width: 100%; border-collapse: collapse; }
        .academic-table thead th { background: #312e81; color: #fff; padding: 4px 8px; font-weight: 600; font-size: 8px; text-transform: uppercase; letter-spacing: 0.3px; border: 1px solid #4338ca; }
        .academic-table tbody td { padding: 3px 8px; border: 1px solid #e2e8f0; text-align: center; font-size: 9px; }
        .academic-table tbody td:first-child { text-align: left; font-weight: 500; }
        .academic-table tbody tr:nth-child(even) { background: #fafbfc; }

        /* Conduct Badge */
        .conduct-badge { display: inline-block; padding: 2px 12px; border-radius: 12px; font-size: 9px; font-weight: 700; text-transform: capitalize; }
        .conduct-excellent { background: #dcfce7; color: #166534; }
        .conduct-very_good { background: #dbeafe; color: #1e40af; }
        .conduct-good { background: #e0e7ff; color: #3730a3; }
        .conduct-satisfactory { background: #fef9c3; color: #854d0e; }
        .conduct-fair { background: #fed7aa; color: #9a3412; }
        .conduct-poor { background: #fecaca; color: #991b1b; }

        /* Declaration */
        .declaration { border: 1.5px solid #e2e8f0; border-radius: 6px; padding: 10px 12px; margin-top: 14px; background: #fafbfc; }
        .declaration-title { font-size: 9px; font-weight: 700; color: #1e1b4b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .declaration-text { font-size: 8.5px; color: #475569; line-height: 1.5; }

        /* Signatures */
        .signatures { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; text-align: center; margin-top: 40px; }
        .sig-line { border-top: 1px solid #94a3b8; padding-top: 4px; }
        .sig-title { font-size: 8px; font-weight: 700; color: #1e293b; text-transform: uppercase; }
        .sig-name { font-size: 7.5px; color: #64748b; }

        .official-stamp { text-align: center; margin-top: 12px; font-size: 7.5px; color: #94a3b8; border: 1px dashed #cbd5e1; padding: 4px; border-radius: 4px; }

        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 50px; font-weight: 800; color: rgba(220,38,38,0.03); text-transform: uppercase; letter-spacing: 8px; z-index: -1; pointer-events: none; }

        .print-btn { position: fixed; top: 10px; right: 10px; z-index: 999; background: #dc2626; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-size: 12px; cursor: pointer; font-family: 'Inter', sans-serif; font-weight: 600; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(220,38,38,0.3); transition: all 0.2s; }
        .print-btn:hover { background: #b91c1c; transform: translateY(-1px); }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
<button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Certificate</button>
<a href="{{ route('admin.certificate-generate.index') }}" style="position:fixed;top:10px;right:130px;z-index:999;background:#555;color:#fff;border:none;padding:8px 18px;border-radius:6px;font-size:12px;text-decoration:none;font-family:'Inter',sans-serif;font-weight:600;display:flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(0,0,0,0.2);transition:all 0.2s;"><i class="fas fa-arrow-left"></i> Back</a>
<div class="watermark">LEAVING CERTIFICATE</div>

<div class="page">
    {{-- Header --}}
    <div class="lc-header">
        <div class="school-name">{{ \App\Models\Setting::get('school_name', 'School of Redemption') }}</div>
        <div class="school-name-am" style="font-size:0.85rem;font-weight:600;color:#4338ca;margin-top:2px;">{{ \App\Models\Setting::get('school_name_am', 'ስኩል ኦፍ ሪደምሽን') }}</div>
        <div class="school-sub">{{ \App\Models\Setting::get('school_address', '') }} &bull; {{ \App\Models\Setting::get('school_phone', '') }} &bull; {{ \App\Models\Setting::get('school_email', '') }}</div>
        <div class="doc-title">School Leaving Clearance Certificate</div>
        <div class="doc-number">Certificate No: {{ $cert->certificate_number }} &bull; Date of Issue: {{ $cert->issue_date->format('F d, Y') }}</div>
    </div>

    {{-- Clearance Status --}}
    <div class="clearance-banner {{ $allClear ? 'cleared' : 'not-cleared' }}">
        <i class="fas fa-{{ $allClear ? 'check-circle' : 'exclamation-triangle' }}"></i>
        {{ $allClear ? 'ALL CLEARANCES PASSED - STUDENT CLEARED FOR DISCHARGE' : 'CLEARANCE PENDING - OUTSTANDING ITEMS REQUIRE RESOLUTION' }}
    </div>

    {{-- Formal Declaration --}}
    <div class="formal-text">
        This is to certify that <strong>{{ $student->full_name }}</strong>,
        @if($student->admission_number)
        Admission Number <strong>{{ $student->admission_number }}</strong>,
        @endif
        @if($student->date_of_birth)
        born on <strong>{{ $student->date_of_birth->format('F d, Y') }}</strong>,
        @endif
        @if($student->gender)
        {{ strtolower($student->gender) }},
        @endif
        was duly admitted to this institution on <strong>{{ $student->admission_date ? $student->admission_date->format('F d, Y') : 'N/A' }}</strong>
        @if($duration)
        and has been a student here for a period of <strong>{{ $duration }}</strong>.
        @else
        .
        @endif
        The student is now being discharged on <strong>{{ $leavingDate->format('F d, Y') }}</strong>
        on account of <strong>{{ $reason }}</strong>.
        <span style="color:#dc2626;font-weight:700;">The student's enrollment status has been updated to INACTIVE and the student record has been marked as having left the school.</span>
    </div>

    {{-- Student Info --}}
    <div class="student-info">
        <div class="info-row"><span class="info-label">Full Name</span><span class="info-value">{{ $student->full_name }}</span></div>
        <div class="info-row"><span class="info-label">Admission Number</span><span class="info-value">{{ $student->admission_number ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Date of Birth</span><span class="info-value">{{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : '-' }}</span></div>
        <div class="info-row"><span class="info-label">Gender</span><span class="info-value">{{ ucfirst($student->gender ?? '-') }}</span></div>
        <div class="info-row"><span class="info-label">Nationality</span><span class="info-value">{{ $student->nationality ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Religion</span><span class="info-value">{{ $student->religion ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Last Class Attended</span><span class="info-value">{{ $student->classroom?->name ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Roll Number</span><span class="info-value">{{ $student->roll_number ?? '-' }}</span></div>
        @if($student->parents && $student->parents->count() > 0)
        <div class="info-row"><span class="info-label">Father's Name</span><span class="info-value">{{ $student->parents->first()->father_name ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Mother's Name</span><span class="info-value">{{ $student->parents->first()->mother_name ?? '-' }}</span></div>
        @else
        <div class="info-row"><span class="info-label">Guardian Name</span><span class="info-value">{{ $student->guardian_name ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Guardian Phone</span><span class="info-value">{{ $student->guardian_phone ?? '-' }}</span></div>
        @endif
        <div class="info-row"><span class="info-label">Previous School</span><span class="info-value">{{ $student->previous_school ?? '-' }}</span></div>
    </div>

    {{-- Clearance Checklist --}}
    <div class="academic-section">
        <div class="section-title"><i class="fas fa-clipboard-check" style="margin-right:4px;"></i> Clearance Checklist</div>
        <table class="clearance-table">
            <thead>
                <tr>
                    <th>Department / Item</th>
                    <th>Details</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clearanceItems as $item)
                <tr>
                    <td><strong>{{ $item['name'] }}</strong></td>
                    <td>{{ $item['detail'] }}</td>
                    <td>
                        <span class="status-badge status-{{ $item['status'] }}">
                            @if($item['status'] === 'cleared') <i class="fas fa-check"></i> CLEARED
                            @elseif($item['status'] === 'outstanding') <i class="fas fa-exclamation-circle"></i> OUTSTANDING
                            @else <i class="fas fa-clock"></i> PENDING
                            @endif
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Academic Performance Summary --}}
    @if(count($academicSummary) > 0)
    <div class="academic-section">
        <div class="section-title"><i class="fas fa-graduation-cap" style="margin-right:4px;"></i> Academic Performance Summary</div>
        <table class="academic-table">
            <thead>
                <tr>
                    <th style="text-align:left;">Academic Year</th>
                    <th>Class</th>
                    <th>Average Score</th>
                    <th>Overall Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($academicSummary as $summary)
                <tr>
                    <td>{{ $summary['year_name'] }}</td>
                    <td>{{ $summary['class_name'] }}</td>
                    <td><strong>{{ $summary['average'] }}</strong></td>
                    <td><strong>{{ $summary['grade'] }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Conduct Assessment --}}
    <div class="academic-section">
        <div class="section-title"><i class="fas fa-user-shield" style="margin-right:4px;"></i> Conduct & Character Assessment</div>
        <div style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;background:#fafbfc;">
            <p style="font-size:9px;color:#475569;line-height:1.6;">
                During the student's tenure at this institution, the general conduct and character have been assessed as:
                <span class="conduct-badge conduct-{{ $conduct }}">
                    {{ str_replace('_', ' ', ucfirst($conduct)) }}
                </span>
            </p>
            @if($lastYearSummary)
            <p style="font-size:8.5px;color:#64748b;margin-top:4px;">
                Last academic performance: Average <strong>{{ $lastYearSummary['average'] }}</strong> (Grade {{ $lastYearSummary['grade'] }}) in {{ $lastYearSummary['year_name'] }}.
            </p>
            @endif
        </div>
    </div>

    {{-- Declaration --}}
    <div class="declaration">
        <div class="declaration-title"><i class="fas fa-gavel" style="margin-right:4px;"></i> Declaration</div>
        <div class="declaration-text">
            This certificate is issued upon the request of the student / parent / guardian. The information contained herein is true and correct to the best of our knowledge and based on the records available at this institution. This certificate does not guarantee admission to any other institution. Any alteration or falsification of this document will render it null and void. The school reserves the right to recall this certificate if any information is found to be incorrect.
        </div>
    </div>

    {{-- Signatures --}}
    <div class="signatures">
        <div>
            <div class="sig-line">
                <div class="sig-title">Class Teacher</div>
                <div class="sig-name">Signature</div>
            </div>
        </div>
        <div>
            <div class="sig-line">
                <div class="sig-title">Librarian</div>
                <div class="sig-name">Signature</div>
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
                <div class="sig-title">Principal</div>
                <div class="sig-name">Signature & Official Seal</div>
            </div>
        </div>
    </div>

    <div class="official-stamp">OFFICIAL SCHOOL LEAVING CLEARANCE CERTIFICATE &bull; {{ \App\Models\Setting::get('school_name', 'the School') }} &bull; Certificate No: {{ $cert->certificate_number }} &bull; Date: {{ $cert->issue_date->format('M d, Y') }}</div>
</div>
</body>
</html>
