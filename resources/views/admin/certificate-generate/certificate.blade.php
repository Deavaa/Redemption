<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <title>{{ __('app.certificates') }} - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            margin: 0; padding: 30px; background: #f5f5f5;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }

        .certificate {
            width: 297mm; min-height: 190mm; margin: 0 auto; background: #fff;
            position: relative; padding: 0;
        }

        /* Outer border: double line frame using lines only */
        .cert-border-outer {
            position: absolute; inset: 0;
            border: 3px solid #1a1a2e;
            border-radius: 4px;
            pointer-events: none; z-index: 1;
        }
        .cert-border-inner {
            position: absolute; inset: 6px;
            border: 1.5px solid #1a1a2e;
            border-radius: 2px;
            pointer-events: none; z-index: 1;
        }

        /* Corner ornaments - line-based patterns */
        .cert-corner {
            position: absolute; width: 50px; height: 50px; z-index: 2;
        }
        .cert-corner-tl { top: 10px; left: 10px; border-top: 2px solid #1a1a2e; border-left: 2px solid #1a1a2e; }
        .cert-corner-tr { top: 10px; right: 10px; border-top: 2px solid #1a1a2e; border-right: 2px solid #1a1a2e; }
        .cert-corner-bl { bottom: 10px; left: 10px; border-bottom: 2px solid #1a1a2e; border-left: 2px solid #1a1a2e; }
        .cert-corner-br { bottom: 10px; right: 10px; border-bottom: 2px solid #1a1a2e; border-right: 2px solid #1a1a2e; }

        /* Line-pattern decorative band at top */
        .cert-top-band {
            height: 8px; margin: 16px 24px 0;
            background: repeating-linear-gradient(
                90deg,
                #1a1a2e 0px, #1a1a2e 12px,
                transparent 12px, transparent 16px,
                #555 16px, #555 20px,
                transparent 20px, transparent 28px
            );
        }

        /* Watermark */
        .cert-watermark {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 350px; height: 350px;
            opacity: 0.035; pointer-events: none; z-index: 0;
        }
        .cert-watermark img { width: 100%; height: 100%; object-fit: contain; }

        .cert-content {
            position: relative; z-index: 2;
            padding: 20px 40px 30px;
        }

        /* Header */
        .cert-header { text-align: center; margin-bottom: 15px; }
        .cert-header-logo {
            height: 60px; object-fit: contain; margin-bottom: 6px;
        }
        .cert-header h1 {
            font-size: 1.9rem; color: #1a1a2e; margin: 0;
            letter-spacing: 4px; font-weight: 800;
        }
        .cert-header h2 {
            font-size: 1rem; color: #555; margin: 4px 0;
            font-style: italic; font-weight: 400;
        }

        /* Decorative line under title */
        .cert-divider {
            width: 280px; height: 3px; margin: 10px auto;
            background: repeating-linear-gradient(
                90deg,
                #1a1a2e 0px, #1a1a2e 20px,
                transparent 20px, transparent 24px
            );
        }

        /* Body */
        .cert-body { text-align: center; margin: 15px 0; }
        .cert-body p { font-size: 0.95rem; line-height: 1.8; color: #444; margin: 4px 0; }
        .cert-body .cert-name {
            font-size: 1.5rem; font-weight: 700; color: #1a1a2e;
            margin: 8px 0; display: inline-block;
            border-bottom: 1.5px solid #1a1a2e; padding-bottom: 2px;
        }
        .cert-body .cert-type {
            font-size: 1rem; color: #1a1a2e; font-weight: 700;
            text-transform: uppercase; letter-spacing: 3px; margin: 12px 0;
        }

        /* Details grid */
        .cert-details {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 6px 20px; max-width: 480px; margin: 15px auto; text-align: left;
        }
        .cert-detail-item { font-size: 0.85rem; color: #333; }
        .cert-detail-item strong { color: #1a1a2e; }

        /* Marks table */
        .cert-marks-table {
            width: 80%; margin: 12px auto; border-collapse: collapse;
        }
        .cert-marks-table th {
            padding: 5px 10px; font-size: 0.78rem; text-transform: uppercase;
            letter-spacing: 0.5px; color: #1a1a2e;
            border-bottom: 2px solid #1a1a2e;
        }
        .cert-marks-table td {
            padding: 4px 10px; font-size: 0.82rem; color: #333;
            border-bottom: 1px solid #ddd;
        }

        /* Footer / Signatures */
        .cert-footer {
            display: flex; justify-content: space-between;
            margin-top: 30px; padding: 0 20px;
        }
        .cert-signature { text-align: center; min-width: 160px; }
        .cert-signature-line {
            width: 140px; margin: 0 auto 4px;
            border-top: 1.5px solid #1a1a2e;
        }
        .cert-signature span { font-size: 0.75rem; color: #666; }

        /* Seal - line-based circle */
        .cert-seal {
            position: absolute; bottom: 30px; right: 50px;
            width: 80px; height: 80px;
            border: 2px solid rgba(26,26,46,0.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            z-index: 2;
        }
        .cert-seal-inner {
            width: 64px; height: 64px;
            border: 1px solid rgba(26,26,46,0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.5rem; color: rgba(26,26,46,0.25);
            text-align: center; line-height: 1.2; font-weight: 700;
            letter-spacing: 1px; text-transform: uppercase;
        }

        /* Bottom decorative band */
        .cert-bottom-band {
            height: 6px; margin: 0 24px 16px;
            background: repeating-linear-gradient(
                90deg,
                #1a1a2e 0px, #1a1a2e 12px,
                transparent 12px, transparent 16px,
                #555 16px, #555 20px,
                transparent 20px, transparent 28px
            );
        }

        .no-print { text-align: center; margin: 20px 0; }
        .no-print button {
            padding: 10px 24px; background: #1a1a2e; color: #fff;
            border: none; border-radius: 8px; cursor: pointer; font-weight: 600;
        }

        @media print {
            .no-print { display: none; }
            body { padding: 0; background: #fff; }
        }
    </style>
</head>
<body>
    @php
        $logoPath = \App\Models\Setting::get('school_logo');
        $logoUrl = \App\Models\Setting::getLogoUrl();
        $schoolName = \App\Models\Setting::get('school_name', 'School of Redemption');
    @endphp

    <div class="no-print"><button onclick="window.print()">{{ __('app.print') ?? 'Print Certificate' }}</button></div>

    <div class="certificate">
        {{-- Double border frame --}}
        <div class="cert-border-outer"></div>
        <div class="cert-border-inner"></div>

        {{-- Corner ornaments --}}
        <div class="cert-corner cert-corner-tl"></div>
        <div class="cert-corner cert-corner-tr"></div>
        <div class="cert-corner cert-corner-bl"></div>
        <div class="cert-corner cert-corner-br"></div>

        {{-- Top decorative band --}}
        <div class="cert-top-band"></div>

        {{-- Watermark --}}
        @if($logoPath && $logoUrl)
        <div class="cert-watermark">
            <img src="{{ $logoUrl }}" alt="">
        </div>
        @endif

        <div class="cert-content">
            {{-- Header --}}
            <div class="cert-header">
                @if($logoPath && $logoUrl)
                    <img src="{{ $logoUrl }}" class="cert-header-logo" alt="Logo">
                @endif
                <h1>{{ strtoupper($schoolName) }}</h1>
                <div class="cert-divider"></div>
                <h2>{{ ucfirst($cert->type) }} {{ __('app.certificates') ?? 'Certificate' }}</h2>
            </div>

            {{-- Body --}}
            <div class="cert-body">
                <p>{{ __('app.cert_this_is_to_certify') ?? 'This is to certify that' }}</p>
                <div class="cert-name">{{ $student->first_name }} {{ $student->last_name }}</div>
                <p>
                    @if($cert->type === 'completion')
                        {{ __('app.cert_has_completed') ?? 'has successfully completed the academic program.' }}
                    @elseif($cert->type === 'transfer')
                        {{ __('app.cert_has_transferred') ?? 'has been transferred from this institution.' }}
                    @elseif($cert->type === 'character')
                        {{ __('app.cert_has_character') ?? 'has demonstrated good character during the academic period.' }}
                    @else
                        {{ __('app.cert_has_achieved') ?? 'has achieved the following in the academic program.' }}
                    @endif
                </p>

                <div class="cert-details">
                    <div class="cert-detail-item"><strong>{{ __('app.admission_no') ?? 'Admission No' }}:</strong> {{ $student->admission_number }}</div>
                    <div class="cert-detail-item"><strong>{{ __('app.roll_number') ?? 'Roll No' }}:</strong> {{ $student->roll_number }}</div>
                    <div class="cert-detail-item"><strong>{{ __('app.classes') ?? 'Class' }}:</strong> {{ $student->classroom->name ?? '-' }}</div>
                    <div class="cert-detail-item"><strong>{{ __('app.section') ?? 'Section' }}:</strong> {{ $student->section->name ?? '-' }}</div>
                    <div class="cert-detail-item"><strong>{{ __('app.certificate_number') ?? 'Certificate No' }}:</strong> {{ $cert->certificate_number }}</div>
                    <div class="cert-detail-item"><strong>{{ __('app.date') ?? 'Date' }}:</strong> {{ $cert->issue_date }}</div>
                </div>

                @if($cert->type === 'academic' && $marks->count() > 0)
                <table class="cert-marks-table">
                    <thead>
                        <tr>
                            <th>{{ __('app.subjects') ?? 'Subject' }}</th>
                            <th>{{ __('app.marks') ?? 'Marks' }}</th>
                            <th>{{ __('app.grade') ?? 'Grade' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($marks as $m)
                        <tr>
                            <td>{{ $m->subject->name ?? '-' }}</td>
                            <td style="text-align:center">{{ $m->grand_total ?? '-' }}</td>
                            <td style="text-align:center">{{ $m->grade ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            {{-- Footer / Signatures --}}
            <div class="cert-footer">
                <div class="cert-signature">
                    <div class="cert-signature-line"></div>
                    <span>{{ __('app.class_teacher') ?? 'Class Teacher' }}</span>
                </div>
                <div class="cert-signature">
                    <div class="cert-signature-line"></div>
                    <span>{{ __('app.principal') ?? 'Principal' }}</span>
                </div>
            </div>
        </div>

        {{-- Bottom decorative band --}}
        <div class="cert-bottom-band"></div>

        {{-- Seal --}}
        <div class="cert-seal">
            <div class="cert-seal-inner">{{ strtoupper($schoolName) }}<br>SEAL</div>
        </div>
    </div>
</body>
</html>
