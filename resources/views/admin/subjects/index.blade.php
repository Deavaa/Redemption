@extends('layouts.admin')
@section('title', 'Subjects')
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold">Subjects</h4><p class="text-muted mb-0">Manage school subjects</p></div>
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Add Subject</a>
    </div>
    @if($subjects->count() > 0)
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0"><thead class="table-dark"><tr><th>#</th><th>Name</th><th>Code</th><th>Type</th><th>Description</th><th>Actions</th></tr></thead>
        <tbody>@foreach($subjects as $subject)<tr><td>{{ $loop->iteration }}</td><td class="fw-semibold">{{ $subject->name }}</td><td><code>{{ $subject->code ?? '-' }}</code></td><td>{{ $subject->type ?? '-' }}</td><td class="text-truncate" style="max-width:300px;">{{ $subject->description ?? '-' }}</td>
        <td><div class="btn-group btn-group-sm"><a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a><form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button></form></div></td></tr>@endforeach</tbody></table>
    </div></div></div>
    @else
    <div class="card"><div class="card-body text-center py-5"><i class="bi bi-book display-1 text-muted"></i><h5 class="mt-3 text-muted">No Subjects Yet</h5><a href="{{ route('admin.subjects.create') }}" class="btn btn-primary mt-2">Add First Subject</a></div></div>
    @endif
</div>
@endsection