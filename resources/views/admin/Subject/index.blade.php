@extends("layouts.admin")
@section("page-title", "Subjects")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-book me-2"></i>Subjects</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-gold">Subjects</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-gold">
            <i class="bi bi-plus-lg me-1"></i>Add Subject
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-0">{{ $data->count() }}</h3>
                    <small class="text-muted">Total Subjects</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #28a745 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ $data->where("type","Core")->count() }}</h3>
                    <small class="text-muted">Core Subjects</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #0dcaf0 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-0">{{ $data->where("type","Elective")->count() }}</h3>
                    <small class="text-muted">Elective Subjects</small>
                </div>
            </div>
        </div>
    </div>

    @if($data->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>All Subjects</h6>
                <span class="badge bg-light text-dark">{{ $data->count() }} subject(s)</span>
            </div>
        </div>
        <div class="card-body p0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px">#</th>
                            <th>Subject Name</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th style="width:100px" class="text-center">Actions</th>
                       </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                       <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold">{{ $item->name }}</span></td>
                            <td><code>{{ $item->code ?? "-" }}</code></td>
                            <td>
                                @if($item->type === "Core")
                                    <span class="badge bg-success bg-opacity-10 text-success">Core</span>
                                @elseif($item->type === "Elective")
                                    <span class="badge bg-info bg-opacity-10 text-info">Elective</span>
                                @else
                                    <span class="badge bg-secondary">{{ $item->type ?? "-" }}</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ Str::limit($item->description ?? "-", 50) }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.subjects.edit', $item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.subjects.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Delete this subject?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete">
                                        <i class="bi bi-trash fs-5"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-journal-x fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No Subjects Found</h5>
            <p class="text-muted">Start by adding your first subject.</p>
            <a href="{{ route('admin.subjects.create') }}" class="btn btn-gold">
                <i class="bi bi-plus-lg me-1"></i>Add Subject
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
