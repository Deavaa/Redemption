@extends("layouts.admin")
@section("page-title", "Classrooms")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-door-open me-2"></i>Classrooms</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-gold">Classrooms</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.classrooms.create') }}" class="btn btn-gold">
            <i class="bi bi-plus-lg me-1"></i>Add Classroom
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-0">{{ $totalClasses }}</h3>
                    <small class="text-muted">Total Classrooms</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #28a745 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ $totalSections }}</h3>
                    <small class="text-muted">Total Sections</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #0dcaf0 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-0">{{ $classes->count() }}</h3>
                    <small class="text-muted">This Page</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #6f42c1 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-purple mb-0">{{ $activeAcademicYear->name ?? "-" }}</h3>
                    <small class="text-muted">Active Year</small>
                </div>
            </div>
        </div>
    </div>

    @if($classes->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>All Classrooms</h6>
                <span class="badge bg-light text-dark">{{ $totalClasses }} classroom(s)</span>
            </div>
        </div>
        <div class="card-body p0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px">#</th>
                            <th>Class Name</th>
                            <th>Academic Year</th>
                            <th>Capacity</th>
                            <th>Teacher</th>
                            <th>Sections</th>
                            <th style="width:100px" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classes as $item)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold">{{ $item->name }}</span></td>
                            <td class="text-muted">{{ $item->academicYear->name ?? "-" }}</td>
                            <td class="text-muted">{{ $item->capacity ?? "-" }}</td>
                            <td class="text-muted">{{ $item->teacher->full_name ?? $item->teacher->first_name ?? "-" }}</td>
                            <td>
                                @if($item->sections && $item->sections->count() > 0)
                                    @foreach($item->sections as $sec)
                                        <span class="badge bg-info bg-opacity-10 text-info me-1">{{ $sec->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.classrooms.edit', $item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.classrooms.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Delete this classroom and all its sections?')">
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
        <div class="card-footer bg-white border-top">
            {{ $classes->links() }}
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-door-closed fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No Classrooms Found</h5>
            <p class="text-muted">Start by adding your first classroom.</p>
            <a href="{{ route('admin.classrooms.create') }}" class="btn btn-gold">
                <i class="bi bi-plus-lg me-1"></i>Add Classroom
            </a>
        </div>
    </div>
    @endif
</div>
@endsection