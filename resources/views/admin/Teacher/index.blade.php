@extends('layouts.admin')
@section('page-title', 'Teachers')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-person-workspace me-2"></i>Teachers</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-gold">Teachers</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.teachers.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Add Teacher</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-0">{{ $data->count() }}</h3>
                    <small class="text-muted">Total Teachers</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #28a745 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ $data->where('status','Active')->count() }}</h3>
                    <small class="text-muted">Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #17a2b8 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-0">{{ $data->pluck('department')->unique()->filter()->count() }}</h3>
                    <small class="text-muted">Departments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #dc3545 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-danger mb-0">{{ $data->where('status','!=','Active')->count() }}</h3>
                    <small class="text-muted">Inactive / On Leave</small>
                </div>
            </div>
    </div>

    @if($data->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="bi bi-people me-2"></i>All Teachers</h6>
                <span class="badge bg-light text-dark">{{ $data->count() }} teacher(s)</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                           <th>Department</th>
                            <th>Qualification</th>
                            <th>Status</th>
                            <th style="width:100px" class="text-center">Actions</th>
                        </tr>
                    </thead>
                   <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold">{{ $item->first_name }} {{ $item->last_name }}</span></td>
                            <td><span class="text-muted">{{ $item->email ?? '-' }}</span></td>
                            <td>{{ $item->phone ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $item->department ?? '-' }}</span></td>
                            <td>{{ $item->qualification ?? '-' }}</td>
                            <td>
                                @if($item->status === 'Active')
                                     <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                @elseif($item->status === 'On Leave')
                                    <span class="badge bg-warning bg-opacity-10 text-warning">On Leave</span>
                                @else
                                    <span class="badge bg-secondary">{{ $item->status ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.teachers.edit', $item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit"><i class="bi bi-pencil-square fs-5"></i></a>
                                <form method="POST" action="{{ route('admin.teachers.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Delete this teacher?')">
                                     @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete"><i class="bi bi-trash fs-5"></i></button>
                                </form>
                            </td>
                        </tr>
                       @endforeach
                    </tbody>
           </div>
        @if($data->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing {{ $data->firstItem() }} to {{ $data->lastItem() }} of {{ $data->total() }}</small>
            {{ $data->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-person-plus fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No Teachers Found</h5>
            <p class="text-muted">Start by adding your first teacher.</p>
            <a href="{{ route('admin.teachers.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Add Teacher</a>
        </div>
    </div>
    @endif
</div>
@endsection
