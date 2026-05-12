@extends('layouts.admin')
@section('page-title', 'Terms')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-calendar-alt me-2"></i>Terms</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-gold">Terms</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.terms.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Add Term</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="bi bi-calendar-alt me-2"></i>All Terms</h6>
                <span class="badge bg-light text-dark">{{ $data->count() }} term(s)</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px">#</th>
                            <th>Term Name</th>
                            <th>Academic Year</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th class="text-center" style="width:120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold">{{ $item->name }}</span></td>
                            <td>{{ $item->academicYear->name ?? '-' }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">{{ 'Inactive' }}</span>
                                @endif
                            </td>
                            <td>{{ $item->start_date ? $item->start_date->format('M d, Y') : '-' }}</td>
                            <td>{{ $item->end_date ? $item->end_date->format('M d, Y') : '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.terms.edit', $item->id) }}" class="btn btn-sm btn-link text-primary p-0 me-2" title="Edit"><i class="bi bi-pencil-square fs-5"></i></a>
                                <form action="{{ route('admin.terms.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this term?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete"><i class="bi bi-trash fs-5"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($data->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing {{ $data->firstItem() }} to {{ $data->lastItem() }} of {{ $data->total() }}</small>
            {{ $data->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
