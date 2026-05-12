@extends('layouts.admin')
@section('title', 'Exams')
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold">Exams</h4><p class="text-muted mb-0">Manage school examinations</p></div>
        <a href="{{ route('admin.exams.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Create Exam</a>
    </div>
    @if($exams->count() > 0)
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0"><thead class="table-dark"><tr><th>#</th><th>Exam Name</th><th>Academic Year</th><th>Term</th><th>Type</th><th>Start Date</th><th>End Date</th><th>Time</th><th>Total</th><th>Pass</th><th>Actions</th></tr></thead>
        <tbody>@foreach($exams as $exam)<tr><td>{{ $loop->iteration }}</td><td class="fw-semibold">{{ $exam->name }}</td><td>{{ $exam->academicYear->name ?? '-' }}</td><td>{{ $exam->term->name ?? '-' }}</td><td>{{ $exam->type ?? '-' }}</td><td>{{ $exam->start_date ? $exam->start_date->format('M d, Y') : '-' }}</td><td>{{ $exam->end_date ? $exam->end_date->format('M d, Y') : '-' }}</td><td>@if($exam->start_time){{ $exam->start_time }}{{ $exam->end_time ? ' - '.$exam->end_time : '' }}@else -@endif</td><td>{{ $exam->total_marks ?? '-' }}</td><td>{{ $exam->passing_marks ?? '-' }}</td>
        <td><div class="btn-group btn-group-sm"><a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a><form method="POST" action="{{ route('admin.exams.destroy', $exam) }}" onsubmit="return confirm('Delete this exam?')">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button></form></div></td></tr>@endforeach</tbody></table>
    </div></div></div>
    @else
    <div class="card"><div class="card-body text-center py-5"><i class="bi bi-journal-text display-1 text-muted"></i><h5 class="mt-3 text-muted">No Exams Yet</h5><a href="{{ route('admin.exams.create') }}" class="btn btn-primary mt-2"><i class="bi bi-plus-circle me-1"></i> Create First Exam</a></div></div>
    @endif
</div>
@endsection