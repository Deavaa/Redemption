@extends("layouts.admin")
@section("page-title","Parents")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-people me-2"></i>Parents</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-gold">Parents</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.parents.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Add Parent</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important"><div class="card-body text-center"><h3 class="fw-bold text-primary mb-0">{{ $totalParents }}</h3><small class="text-muted">Total Parents</small></div></div></div>
        <div class="col-md-6"><div class="card border-0 shadow-sm h-100" style="border-top:3px solid #28a745 !important"><div class="card-body text-center"><h3 class="fw-bold text-success mb-0">{{ $data->count() }}</h3><small class="text-muted">This Page</small></div></div></div>
    </div>
    @if(session("success"))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session("success") }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if($data->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3"><div class="d-flex justify-content-between align-items-center"><h6 class="fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>All Parents</h6><span class="badge bg-light text-dark">{{ $totalParents }} parent(s)</span></div></div>
        <div class="card-body p0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0"><thead class="table-light"><tr>
            <th class="ps-3" style="width:40px">#</th><th>Father Name</th><th>Father Phone</th><th>Mother Name</th><th>Guardian</th><th style="width:100px" class="text-center">Actions</th>
        </tr></thead><tbody>
        @foreach($data as $item)
        <tr><td class="ps-3 text-muted">{{ $loop->iteration }}</td>
            <td><span class="fw-semibold">{{ $item->father_name }}</span></td>
            <td class="text-muted">{{ $item->father_phone ?? "-" }}</td>
            <td>{{ $item->mother_name ?? "-" }}</td>
            <td>{{ $item->guardian_name ?? "-" }}</td>
            <td class="text-center">
                <a href="{{ route('admin.parents.edit',$item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit"><i class="bi bi-pencil-square fs-5"></i></a>
                <form method="POST" action="{{ route('admin.parents.destroy',$item->id) }}" style="display:inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete"><i class="bi bi-trash fs-5"></i></button></form>
            </td></tr>
        @endforeach
        </tbody></table></div></div>
        <div class="card-footer bg-white border-top">{{ $data->appends(request()->query())->links() }}</div>
    </div>
    @else
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bi bi-people fs-1 text-muted"></i><h5 class="mt-3 text-muted">No Parents Found</h5><a href="{{ route('admin.parents.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Add Parent</a></div></div>
    @endif
</div>
@endsection