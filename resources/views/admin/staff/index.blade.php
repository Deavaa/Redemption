@extends('layouts.admin')
@section('title', 'Staff / Teachers')
@push('styles')
<style>.role-badge-teacher{background:#0d6efd;color:white;padding:3px 10px;border-radius:12px;font-size:.75rem;font-weight:600}.role-badge-admin{background:#dc3545;color:white;padding:3px 10px;border-radius:12px;font-size:.75rem;font-weight:600}</style>
@endpush
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold">Staff &amp; Teachers</h4><p class="text-muted mb-0">Manage teachers and staff</p></div>
        <a href="{{ route('admin.staff.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Add Staff</a>
    </div>
    @if($staff->count() > 0)
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0"><thead class="table-dark"><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Gender</th><th>Qualification</th><th>Actions</th></tr></thead>
        <tbody>@foreach($staff as $s)<tr><td>{{ $staff->firstItem() + $loop->index }}</td><td class="fw-semibold">{{ $s->name }}</td><td>{{ $s->email }}</td><td>{{ $s->phone ?? '-' }}</td><td><span class="role-badge-{{ $s->role }}">{{ ucfirst($s->role) }}</span></td><td>{{ $s->gender ?? '-' }}</td><td>{{ $s->qualification ?? '-' }}</td>
        <td><div class="btn-group btn-group-sm"><a href="{{ route('admin.staff.edit', $s) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
        @if($s->email !== auth()->user()->email)<form method="POST" action="{{ route('admin.staff.destroy', $s) }}" onsubmit="return confirm('Remove this staff member?')">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button></form>@endif
        </div></td></tr>@endforeach</tbody></table>
    </div></div>
    @if($staff->hasPages())<div class="card-footer d-flex justify-content-center">{{ $staff->links() }}</div>@endif
    </div>
    @else
    <div class="card"><div class="card-body text-center py-5"><i class="bi bi-people display-1 text-muted"></i><h5 class="mt-3 text-muted">No Staff Members Yet</h5><p class="text-muted">Add teachers and staff.</p><a href="{{ route('admin.staff.create') }}" class="btn btn-primary mt-2"><i class="bi bi-person-plus me-1"></i> Add First Staff</a></div></div>
    @endif
</div>
@endsection