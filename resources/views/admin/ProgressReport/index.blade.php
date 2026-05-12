@extends("layouts.admin")
@section("page-title","Progress Reports")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph me-2"></i>Progress Reports</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active text-gold">Progress Reports</li>
        </ol></nav></div>
        <a href="{{ route('admin.progress-reports.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Create Report</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important"><div class="card-body text-center"><h3 class="fw-bold text-primary mb-0">{{ $totalReports }}</h3><small class="text-muted">Total Reports</small></div></div></div>
        <div class="col-md-6"><div class="card border-0 shadow-sm h-100" style="border-top:3px solid #0dcaf0 !important"><div class="card-body text-center"><h3 class="fw-bold text-info mb-0">{{ $data->count() }}</h3><small class="text-muted">This Page</small></div></div></div>
    </div>
    @if(session("success"))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session("success") }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if($data->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3"><div class="d-flex justify-content-between align-items-center"><h6 class="fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>All Reports</h6><span class="badge bg-light text-dark">{{ $totalReports }}</span></div></div>
        <div class="card-body p0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0"><thead class="table-light"><tr>
            <th class="ps-3" style="width:40px">#</th><th>Student</th><th>Year</th><th>Term</th><th>Class</th><th>Marks</th><th>%</th><th>Grade</th><th>Rank</th><th style="width:100px" class="text-center">Actions</th>
        </tr></thead><tbody>
        @foreach($data as $item)
        <tr><td class="ps-3 text-muted">{{ $loop->iteration }}</td>
            <td><span class="fw-semibold">{{ $item->student->first_name ?? "-" }} {{ $item->student->last_name ?? "" }}</span></td>
            <td class="text-muted">{{ $item->academicYear->name ?? "-" }}</td>
            <td class="text-muted">{{ $item->term->name ?? "-" }}</td>
            <td class="text-muted">{{ $item->classroom->name ?? "-" }}</td>
            <td>{{ $item->total_marks }}</td>
            <td><strong>{{ $item->percentage }}%</strong></td>
            <td><span class="badge bg-{{ in_array($item->grade,["A","A+"]) ? "success" : (in_array($item->grade,["B","B+"]) ? "info" : ($item->grade==="C" ? "warning" : "danger")) }} bg-opacity-10 text-{{ in_array($item->grade,["A","A+"]) ? "success" : (in_array($item->grade,["B","B+"]) ? "info" : ($item->grade==="C" ? "warning" : "danger")) }}">{{ $item->grade }}</span></td>
            <td>{{ $item->rank ?? "-" }}</td>
            <td class="text-center">
                <a href="{{ route('admin.progress-reports.edit',$item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit"><i class="bi bi-pencil-square fs-5"></i></a>
                <form method="POST" action="{{ route('admin.progress-reports.destroy',$item->id) }}" style="display:inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete"><i class="bi bi-trash fs-5"></i></button></form>
            </td></tr>
        @endforeach</tbody></table></div></div>
        <div class="card-footer bg-white border-top">{{ $data->links() }}</div>
    </div>
    @else
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bi bi-file-earmark-bar-graph fs-1 text-muted"></i><h5 class="mt-3 text-muted">No Reports Found</h5><a href="{{ route('admin.progress-reports.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Create Report</a></div></div>
    @endif
</div>
@endsection