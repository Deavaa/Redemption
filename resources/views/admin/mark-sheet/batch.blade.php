@extends('layouts.admin')
@section('title', 'Mark Roster')

@section('content')
<div class="modern-page mark-sheet-print">
    <div class="mark-sheet-header">
        <h1>School of Redemption</h1>
        <h2>Mark Roster - {{ $class->name }} {{ $term ? '- ' . $term->name : '' }}</h2>
        <p class="text-muted">{{ $academicYear->name }}</p>
    </div>

    @foreach($students as $studentId => $studentMarks)
        @php $student = $studentMarks->first()->student; @endphp
        <div class="mark-roster-student">
            <h3>{{ $student->full_name ?? '' }} <small class="text-muted">({{ $student->roll_number ?? '' }})</small></h3>
            <table class="mark-sheet-table">
                <thead><tr><th>#</th><th>Subject</th><th>CA</th><th>Mid</th><th>Final</th><th>Total</th><th>Grade</th></tr></thead>
                <tbody>
                    @php $sn = 0; $total = 0; @endphp
                    @foreach($studentMarks as $m)
                        @php $sn++; $total += ($m->grand_total ?? 0); @endphp
                        <tr>
                            <td>{{ $sn }}</td>
                            <td style="text-align:left">{{ $m->subject->name ?? '-' }}</td>
                            <td>{{ $m->ca_total ?? '-' }}</td>
                            <td>{{ $m->mid_term ?? '-' }}</td>
                            <td>{{ $m->final_exam ?? '-' }}</td>
                            <td><strong>{{ $m->grand_total ?? '-' }}</strong></td>
                            <td>{{ $m->grade ?? '-' }}</td>
                        </tr>
                    @endforeach
                    <tr class="mark-total-row"><td colspan="5"><strong>Total</strong></td><td><strong>{{ $total }}</strong></td><td></td></tr>
                </tbody>
            </table>
        </div>
        @if(!$loop->last)<div class="page-break"></div>@endif
    @endforeach

    <div class="no-print" style="margin-top:1.5rem;text-align:center">
        <button onclick="window.print()" class="btn-modern btn-modern-primary"><i class="fas fa-print"></i> Print All</button>
        <a href="{{ route('admin.mark-sheet.index') }}" class="btn-modern btn-modern-outline" style="margin-left:0.5rem"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

@push('styles')
<style>
.mark-sheet-print { max-width: 900px; margin: 0 auto; padding: 2rem; background: #fff; border-radius: 14px; }
.mark-sheet-header { text-align: center; margin-bottom: 1.5rem; border-bottom: 3px double #333; padding-bottom: 1rem; }
.mark-sheet-header h1 { font-size: 1.5rem; font-weight: 800; margin: 0; }
.mark-sheet-header h2 { font-size: 1.1rem; color: #4361ee; margin: 0.25rem 0 0; }
.mark-roster-student { margin-bottom: 2rem; }
.mark-roster-student h3 { font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; }
.mark-sheet-table { width: 100%; border-collapse: collapse; margin: 0.5rem 0; }
.mark-sheet-table th { background: #1a1a2e; color: #fff; padding: 0.5rem 0.6rem; font-size: 0.78rem; }
.mark-sheet-table td { padding: 0.4rem 0.6rem; border-bottom: 1px solid #e5e7eb; font-size: 0.82rem; text-align: center; }
.mark-total-row { background: #f0f2f5; font-weight: 700; }
.page-break { page-break-after: always; }
@media print {
    .no-print { display: none !important; }
    .mark-sheet-print { box-shadow: none; padding: 0; }
}
</style>
@endpush
@endsection
