<?php
echo "=== Fix v4 - AcademicYear & Term ===\n\n";
 $b = __DIR__;

// 1. Academic Year
echo "[1/2] Writing AcademicYear...\n";
 $ay = '@extends("layouts.admin")
@section("page-title", "Academic Years")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-calendar3 me-2"></i>Academic Years</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-gold">Academic Years</li>
            </ol></nav>
        </div>
        <a href="{{ route(\'admin.academic-years.create\') }}" class="btn btn-gold">
            <i class="bi bi-plus-lg me-1"></i>Add Academic Year
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-0">{{ $data->count() }}</h3>
                    <small class="text-muted">Total Years</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #28a745 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ $data->where("is_current", 1)->count() }}</h3>
                    <small class="text-muted">Current</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #0dcaf0 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-0">{{ $data->where("is_current", 0)->count() }}</h3>
                    <small class="text-muted">Inactive</small>
                </div>
            </div>
        </div>
    </div>

    @if($data->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>All Academic Years</h6>
                <span class="badge bg-light text-dark">{{ $data->count() }} year(s)</span>
            </div>
        </div>
        <div class="card-body p0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px">#</th>
                            <th>Year Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th style="width:100px" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold">{{ $item->name }}</span></td>
                            <td class="text-muted">{{ $item->start_date ?? "-" }}</td>
                            <td class="text-muted">{{ $item->end_date ?? "-" }}</td>
                            <td>
                                @if($item->is_current == 1)
                                    <span class="badge bg-success bg-opacity-10 text-success">Current</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route(\'admin.academic-years.edit\', $item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </a>
                                <form method="POST" action="{{ route(\'admin.academic-years.destroy\', $item->id) }}" style="display:inline" onsubmit="return confirm(\'Delete this academic year?\')">
                                    @csrf @method(\'DELETE\')
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
            {{ $data->links() }}
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar-x fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No Academic Years Found</h5>
            <p class="text-muted">Start by adding your first academic year.</p>
            <a href="{{ route(\'admin.academic-years.create\') }}" class="btn btn-gold">
                <i class="bi bi-plus-lg me-1"></i>Add Academic Year
            </a>
        </div>
    </div>
    @endif
</div>
@endsection';

file_put_contents($b . '/resources/views/admin/AcademicYear/index.blade.php', $ay);
echo "  [OK] AcademicYear/index.blade.php written\n";

// 2. Terms
echo "[2/2] Writing Term...\n";
 $tm = '@extends("layouts.admin")
@section("page-title", "Terms")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-calendar-range me-2"></i>Terms / Semesters</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-gold">Terms</li>
            </ol></nav>
        </div>
        <a href="{{ route(\'admin.terms.create\') }}" class="btn btn-gold">
            <i class="bi bi-plus-lg me-1"></i>Add Term
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-0">{{ $data->count() }}</h3>
                    <small class="text-muted">Total Terms</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #28a745 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ $data->where("is_current", 1)->count() }}</h3>
                    <small class="text-muted">Current</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #0dcaf0 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-0">{{ $data->where("is_current", 0)->count() }}</h3>
                    <small class="text-muted">Inactive</small>
                </div>
            </div>
        </div>
    </div>

    @if($data->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>All Terms</h6>
                <span class="badge bg-light text-dark">{{ $data->count() }} term(s)</span>
            </div>
        </div>
        <div class="card-body p0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px">#</th>
                            <th>Term Name</th>
                            <th>Academic Year</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th style="width:100px" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold">{{ $item->name }}</span></td>
                            <td class="text-muted">{{ $item->academic_year_id ? \App\Models\AcademicYear::find($item->academic_year_id)->name ?? "-" : "-" }}</td>
                            <td class="text-muted">{{ $item->start_date ?? "-" }}</td>
                            <td class="text-muted">{{ $item->end_date ?? "-" }}</td>
                            <td>
                                @if($item->is_current == 1)
                                    <span class="badge bg-success bg-opacity-10 text-success">Current</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route(\'admin.terms.edit\', $item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </a>
                                <form method="POST" action="{{ route(\'admin.terms.destroy\', $item->id) }}" style="display:inline" onsubmit="return confirm(\'Delete this term?\')">
                                    @csrf @method(\'DELETE\')
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
            {{ $data->links() }}
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar-x fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No Terms Found</h5>
            <p class="text-muted">Start by adding your first term.</p>
            <a href="{{ route(\'admin.terms.create\') }}" class="btn btn-gold">
                <i class="bi bi-plus-lg me-1"></i>Add Term
            </a>
        </div>
    </div>
    @endif
</div>
@endsection';

file_put_contents($b . '/resources/views/admin/Term/index.blade.php', $tm);
echo "  [OK] Term/index.blade.php written\n";

// 3. Clear caches
echo "\n[3/3] Clearing caches...\n";
foreach(['view:clear','config:clear','cache:clear','route:clear'] as $cmd){
    $o=shell_exec('php artisan '.$cmd.' 2>&1'); echo "  ".trim($o)."\n";
}
echo "\n=== Done! Check Academic Year and Terms pages. ===\n";
