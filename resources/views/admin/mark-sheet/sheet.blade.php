@extends('layouts.admin')
@section('title', 'Mark Sheet - ' . $student->full_name)

@section('content')
<div class="modern-page mark-sheet-print">
    <div class="mark-sheet-header">
        <h1>School of Redemption</h1>
        <h2>Academic Mark Sheet</h2>
    </div>
    <div class="mark-sheet-info">
        <table class="mark-info-table">
            <tr><td><strong>Student:</strong></td><td>{{ $student->full_name }}</td><td><strong>Roll No:</strong></td><td>{{ $student->roll_number }}</td></tr>
            <tr><td><strong>Class:</strong></td><td>{{ $class->name }}</td><td><strong>Academic Year:</strong></td><td>{{ $academicYear->name }}</td></tr>
            <tr><td><strong>Term:</strong></td><td>{{ $term->name ?? 'All Terms' }}</td><td><strong>Admission No:</strong></td><td>{{ $student->admission_number }}</td></tr>
        </table>
    </div>

    <table class="mark-sheet-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>CA Total</th>
                <th>Mid Term</th>
                <th>Final Exam</th>
                <th>Grand Total</th>
                <th>Grade</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @php $sn = 0; $totalMarks = 0; $totalMax = 0; @endphp
            @foreach($marks->where('student_id', $student->id) as $m)
                @php $sn++; $totalMarks += ($m->grand_total ?? 0); @endphp
                <tr>
                    <td>{{ $sn }}</td>
                    <td>{{ $m->subject->name ?? '-' }}</td>
                    <td>{{ $m->ca_total ?? '-' }}</td>
                    <td>{{ $m->mid_term ?? '-' }}</td>
                    <td>{{ $m->final_exam ?? '-' }}</td>
                    <td><strong>{{ $m->grand_total ?? '-' }}</strong></td>
                    <td>{{ $m->grade ?? '-' }}</td>
                    <td>{{ $m->remarks ?? '-' }}</td>
                </tr>
            @endforeach
            <tr class="mark-total-row">
                <td colspan="5"><strong>Total</strong></td>
                <td><strong>{{ $totalMarks }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    @php $subjectCount = max($marks->where('student_id', $student->id)->count(), 1); $average = round($totalMarks / $subjectCount, 1); @endphp
    <div class="mark-summary">
        <div class="mark-summary-item"><span>Average:</span> <strong>{{ $average }}</strong></div>
        <div class="mark-summary-item"><span>Grade:</span> <strong>{{ $average >= 90 ? 'A+' : ($average >= 80 ? 'A' : ($average >= 70 ? 'B+' : ($average >= 60 ? 'B' : ($average >= 50 ? 'C' : ($average >= 40 ? 'D' : 'F'))))) }}</strong></div>
        <div class="mark-summary-item"><span>Result:</span> <strong class="{{ $average >= 50 ? 'text-success' : 'text-danger' }}">{{ $average >= 50 ? 'PASS' : 'FAIL' }}</strong></div>
    </div>

    <div class="mark-sheet-footer">
        <div class="mark-signature"><span>Class Teacher</span></div>
        <div class="mark-signature"><span>Principal</span></div>
        <div class="mark-signature"><span>Parent/Guardian</span></div>
    </div>

    <div class="no-print" style="margin-top:1.5rem;text-align:center">
        <button onclick="window.print()" class="btn-modern btn-modern-primary"><i class="fas fa-print"></i> Print</button>
        <a href="{{ route('admin.mark-sheet.index') }}" class="btn-modern btn-modern-outline" style="margin-left:0.5rem"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

@push('styles')
<style>
.mark-sheet-print { max-width: 900px; margin: 0 auto; padding: 2rem; background: #fff; border-radius: 14px; }
.mark-sheet-header { text-align: center; margin-bottom: 1.5rem; border-bottom: 3px double #333; padding-bottom: 1rem; }
.mark-sheet-header h1 { font-size: 1.5rem; font-weight: 800; color: #1a1a2e; margin: 0; }
.mark-sheet-header h2 { font-size: 1.1rem; color: #4361ee; margin: 0.25rem 0 0; }
.mark-info-table { width: 100%; margin-bottom: 1rem; }
.mark-info-table td { padding: 0.3rem 0.75rem; font-size: 0.88rem; }
.mark-sheet-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
.mark-sheet-table th { background: #1a1a2e; color: #fff; padding: 0.65rem 0.75rem; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px; }
.mark-sheet-table td { padding: 0.55rem 0.75rem; border-bottom: 1px solid #e5e7eb; font-size: 0.88rem; text-align: center; }
.mark-sheet-table td:nth-child(2) { text-align: left; }
.mark-total-row { background: #f0f2f5; font-weight: 700; }
.mark-summary { display: flex; gap: 2rem; padding: 1rem 0; border-top: 2px solid #e5e7eb; }
.mark-summary-item { font-size: 1rem; }
.mark-summary-item span { color: #6b7280; }
.mark-sheet-footer { display: flex; justify-content: space-between; margin-top: 3rem; padding-top: 1rem; }
.mark-signature { text-align: center; min-width: 150px; }
.mark-signature span { border-top: 1px solid #333; padding-top: 0.5rem; display: inline-block; font-size: 0.85rem; color: #6b7280; }
@media print {
    .no-print { display: none !important; }
    .mark-sheet-print { box-shadow: none; border: none; padding: 0; }
}
</style>
@endpush
@endsection
