<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Foldable Certificate - {{ $student->first_name }}</title>
    <style>
        @page { size: A4 portrait; margin: 5mm; }
        body { font-family: 'Inter', Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .foldable-cert {
            width: 210mm; min-height: 297mm; margin: 0 auto; background: #fff;
            border: 2px solid #4361ee; display: flex; flex-direction: column;
        }
        .fold-header {
            background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff;
            padding: 20px 30px; text-align: center;
        }
        .fold-header h1 { font-size: 1.4rem; margin: 0; letter-spacing: 2px; }
        .fold-header h2 { font-size: 0.9rem; font-weight: 400; margin: 5px 0 0; opacity: 0.9; }
        .fold-student-info {
            display: grid; grid-template-columns: 1fr auto; gap: 20px;
            padding: 20px 30px; border-bottom: 1px solid #e5e7eb;
        }
        .fold-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .fold-info-item label { font-size: 0.68rem; color: #9ca3af; text-transform: uppercase; display: block; }
        .fold-info-item span { font-size: 0.88rem; font-weight: 600; color: #1a1a2e; }
        .fold-photo {
            width: 80px; height: 100px; border: 2px solid #e5e7eb; border-radius: 8px;
            overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f8f9fa;
        }
        .fold-photo img { width: 100%; height: 100%; object-fit: cover; }
        .fold-photo i { font-size: 2rem; color: #d1d5db; }
        .fold-marks { padding: 15px 30px; flex: 1; }
        .fold-marks table { width: 100%; border-collapse: collapse; }
        .fold-marks th { background: #1a1a2e; color: #fff; padding: 8px 12px; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .fold-marks td { padding: 7px 12px; border-bottom: 1px solid #f0f0f0; font-size: 0.85rem; text-align: center; }
        .fold-marks td:first-child { text-align: left; }
        .fold-marks tr.total-row { background: #f8f9fa; font-weight: 700; }
        .fold-conduct { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; padding: 15px 30px; border-top: 1px solid #e5e7eb; }
        .fold-conduct-item { text-align: center; }
        .fold-conduct-item label { font-size: 0.72rem; color: #9ca3af; text-transform: uppercase; }
        .fold-conduct-item span { display: block; font-size: 1rem; font-weight: 700; color: #1a1a2e; }
        .fold-comments { padding: 15px 30px; border-top: 1px solid #e5e7eb; }
        .fold-comments textarea { width: 100%; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; font-size: 0.85rem; min-height: 60px; resize: none; }
        .fold-footer { display: flex; justify-content: space-between; padding: 20px 30px; border-top: 2px solid #1a1a2e; }
        .fold-signature { text-align: center; }
        .fold-signature-line { width: 150px; border-top: 1px solid #333; margin: 0 auto 5px; }
        .fold-signature span { font-size: 0.78rem; color: #6b7280; }
        .no-print { text-align: center; margin: 20px 0; }
        .no-print button { padding: 10px 24px; background: #4361ee; color: #fff; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; }
        @media print { .no-print { display: none; } body { padding: 0; background: #fff; } .foldable-cert { border: none; } }
    </style>
</head>
<body>
    <div class="no-print"><button onclick="window.print()">Print Foldable Certificate</button></div>
    <div class="foldable-cert">
        <div class="fold-header">
            <h1>SCHOOL OF REDEMPTION</h1>
            <h2>Academic Report Card - {{ $cert->certificate_number }}</h2>
        </div>
        <div class="fold-student-info">
            <div class="fold-info-grid">
                <div class="fold-info-item"><label>Student Name</label><span>{{ $student->first_name }} {{ $student->last_name }}</span></div>
                <div class="fold-info-item"><label>Roll Number</label><span>{{ $student->roll_number }}</span></div>
                <div class="fold-info-item"><label>Class / Section</label><span>{{ $student->classroom->name ?? '-' }} / {{ $student->section->name ?? '-' }}</span></div>
                <div class="fold-info-item"><label>Admission No</label><span>{{ $student->admission_number }}</span></div>
                <div class="fold-info-item"><label>Academic Year</label><span>{{ $student->academicYear->name ?? '-' }}</span></div>
                <div class="fold-info-item"><label>Date of Birth</label><span>{{ $student->date_of_birth ?? '-' }}</span></div>
            </div>
            <div class="fold-photo">
                @if($student->photo)<img src="{{ asset('storage/' . $student->photo) }}">@else<i class="fas fa-user"></i>@endif
            </div>
        </div>
        <div class="fold-marks">
            <table>
                <thead><tr><th>Subject</th><th>CA</th><th>Mid Term</th><th>Final</th><th>Total</th><th>Grade</th><th>Remarks</th></tr></thead>
                <tbody>
                    @php $totalMarks = 0; @endphp
                    @foreach($marks as $m)
                        @php $totalMarks += ($m->grand_total ?? 0); @endphp
                        <tr>
                            <td>{{ $m->subject->name ?? '-' }}</td>
                            <td>{{ $m->ca_total ?? '-' }}</td>
                            <td>{{ $m->mid_term ?? '-' }}</td>
                            <td>{{ $m->final_exam ?? '-' }}</td>
                            <td><strong>{{ $m->grand_total ?? '-' }}</strong></td>
                            <td>{{ $m->grade ?? '-' }}</td>
                            <td>{{ $m->remarks ?? '-' }}</td>
                        </tr>
                    @endforeach
                    @php $subCount = max($marks->count(), 1); $avg = round($totalMarks / $subCount, 1); @endphp
                    <tr class="total-row">
                        <td>Total</td><td colspan="3"></td>
                        <td>{{ $totalMarks }}</td>
                        <td>{{ $avg >= 90 ? 'A+' : ($avg >= 80 ? 'A' : ($avg >= 70 ? 'B+' : ($avg >= 60 ? 'B' : ($avg >= 50 ? 'C' : 'D')))) }}</td>
                        <td>{{ $avg >= 50 ? 'PASS' : 'FAIL' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="fold-conduct">
            <div class="fold-conduct-item"><label>Conduct</label><span>{{ $marks->first()->conduct ?? '-' }}/5</span></div>
            <div class="fold-conduct-item"><label>Handwriting</label><span>{{ $marks->first()->handwriting ?? '-' }}/5</span></div>
            <div class="fold-conduct-item"><label>Creativity</label><span>{{ $marks->first()->creativity ?? '-' }}/10</span></div>
        </div>
        <div class="fold-comments">
            <label style="font-size:0.78rem;color:#9ca3af;text-transform:uppercase">Teacher's Comment</label>
            <textarea readonly>Good performance. Keep it up!</textarea>
        </div>
        <div class="fold-footer">
            <div class="fold-signature"><div class="fold-signature-line"></div><span>Class Teacher</span></div>
            <div class="fold-signature"><div class="fold-signature-line"></div><span>Principal</span></div>
            <div class="fold-signature"><div class="fold-signature-line"></div><span>Parent/Guardian</span></div>
        </div>
    </div>
</body>
</html>
