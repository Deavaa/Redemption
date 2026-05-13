<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        body { font-family: 'Georgia', 'Times New Roman', serif; margin: 0; padding: 30px; background: #f5f5f5; }
        .certificate {
            width: 297mm; min-height: 200mm; margin: 0 auto; background: #fff;
            border: 3px double #4361ee; padding: 30px; position: relative;
        }
        .certificate-inner { border: 1px solid #e5e7eb; padding: 30px; height: 100%; }
        .cert-header { text-align: center; margin-bottom: 25px; }
        .cert-header h1 { font-size: 2rem; color: #1a1a2e; margin: 0; letter-spacing: 3px; }
        .cert-header h2 { font-size: 1.2rem; color: #4361ee; margin: 5px 0; font-style: italic; }
        .cert-header .cert-line { width: 200px; height: 2px; background: linear-gradient(90deg, transparent, #4361ee, transparent); margin: 10px auto; }
        .cert-body { text-align: center; margin: 20px 0; }
        .cert-body p { font-size: 1rem; line-height: 1.8; color: #374151; margin: 5px 0; }
        .cert-body .cert-name { font-size: 1.5rem; font-weight: 700; color: #1a1a2e; margin: 10px 0; text-decoration: underline; text-underline-offset: 5px; }
        .cert-body .cert-type { font-size: 1.1rem; color: #4361ee; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin: 15px 0; }
        .cert-details { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; max-width: 500px; margin: 20px auto; text-align: left; }
        .cert-detail-item { font-size: 0.9rem; }
        .cert-detail-item strong { color: #1a1a2e; }
        .cert-footer { display: flex; justify-content: space-between; margin-top: 40px; }
        .cert-signature { text-align: center; min-width: 180px; }
        .cert-signature-line { border-top: 1px solid #333; margin-bottom: 5px; width: 150px; margin-left: auto; margin-right: auto; }
        .cert-signature span { font-size: 0.8rem; color: #6b7280; }
        .cert-seal { position: absolute; bottom: 30px; right: 40px; width: 80px; height: 80px; border: 2px solid #4361ee; border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0.3; font-size: 0.6rem; text-align: center; color: #4361ee; }
        .no-print { text-align: center; margin: 20px 0; }
        .no-print button { padding: 10px 24px; background: #4361ee; color: #fff; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; }
        @media print { .no-print { display: none; } body { padding: 0; background: #fff; } .certificate { border: none; box-shadow: none; } }
    </style>
</head>
<body>
    <div class="no-print"><button onclick="window.print()">Print Certificate</button></div>
    <div class="certificate">
        <div class="certificate-inner">
            <div class="cert-header">
                <h1>SCHOOL OF REDEMPTION</h1>
                <div class="cert-line"></div>
                <h2>{{ ucfirst($cert->type) }} Certificate</h2>
            </div>
            <div class="cert-body">
                <p>This is to certify that</p>
                <div class="cert-name">{{ $student->first_name }} {{ $student->last_name }}</div>
                <p>has successfully {{ $cert->type === 'completion' ? 'completed' : ($cert->type === 'transfer' ? 'been transferred from' : ($cert->type === 'character' ? 'demonstrated good character during' : 'achieved the following in')) }} the academic program.</p>

                <div class="cert-details">
                    <div class="cert-detail-item"><strong>Admission No:</strong> {{ $student->admission_number }}</div>
                    <div class="cert-detail-item"><strong>Roll No:</strong> {{ $student->roll_number }}</div>
                    <div class="cert-detail-item"><strong>Class:</strong> {{ $student->classroom->name ?? '-' }}</div>
                    <div class="cert-detail-item"><strong>Section:</strong> {{ $student->section->name ?? '-' }}</div>
                    <div class="cert-detail-item"><strong>Certificate No:</strong> {{ $cert->certificate_number }}</div>
                    <div class="cert-detail-item"><strong>Date:</strong> {{ $cert->issue_date }}</div>
                </div>

                @if($cert->type === 'academic' && $marks->count() > 0)
                <table style="width:80%;margin:15px auto;border-collapse:collapse">
                    <thead><tr style="background:#1a1a2e;color:#fff"><th style="padding:6px 10px">Subject</th><th style="padding:6px 10px">Marks</th><th style="padding:6px 10px">Grade</th></tr></thead>
                    <tbody>
                        @foreach($marks as $m)
                        <tr style="border-bottom:1px solid #e5e7eb"><td style="padding:5px 10px">{{ $m->subject->name ?? '-' }}</td><td style="padding:5px 10px;text-align:center">{{ $m->grand_total ?? '-' }}</td><td style="padding:5px 10px;text-align:center">{{ $m->grade ?? '-' }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            <div class="cert-footer">
                <div class="cert-signature"><div class="cert-signature-line"></div><span>Class Teacher</span></div>
                <div class="cert-signature"><div class="cert-signature-line"></div><span>Principal</span></div>
            </div>
            <div class="cert-seal">SCHOOL<br>SEAL</div>
        </div>
    </div>
</body>
</html>
