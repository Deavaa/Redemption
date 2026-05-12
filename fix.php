<?php
echo "=== School of Redemption - View Fix Script ===\n\n";

// ---- FIX 1: .env CACHE_DRIVER ----
if (file_exists('.env')) {
    $env = file_get_contents('.env');
    if (strpos($env, 'CACHE_DRIVER=database') !== false) {
        $env = str_replace('CACHE_DRIVER=database', 'CACHE_DRIVER=file', $env);
        file_put_contents('.env', $env);
        echo "[OK] .env CACHE_DRIVER changed to 'file'\n";
    } else {
        echo "[SKIP] .env CACHE_DRIVER already fixed or not 'database'\n";
    }
} else {
    echo "[WARN] .env file not found in current directory\n";
}

// ---- FIX 2: Academic Year index ----
 $ay = <<<'BLADE'
@extends("layouts.admin")
@section("page-title", "Academic Years")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-calendar3 me-2"></i>Academic Years</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-gold">Academic Years</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.academic-years.create') }}" class="btn btn-gold">
            <i class="bi bi-plus-lg me-1"></i>Add Academic Year
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-0">{{ $data->count() }}</h3>
                    <small class="text-muted">Total Academic Years</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #28a745 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ $data->where('is_active', 1)->count() }}</h3>
                    <small class="text-muted">Active Years</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #dc3545 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-danger mb-0">{{ $data->where('is_active', 0)->count() }}</h3>
                    <small class="text-muted">Inactive Years</small>
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
                            <td>{{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('M d, Y') : '-' }}</td>
                            <td>{{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('M d, Y') : '-' }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.academic-years.edit', $item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.academic-years.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Delete this academic year?')">
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
            <i class="bi bi-calendar-x fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No Academic Years Found</h5>
            <p class="text-muted">Start by adding your first academic year.</p>
            <a href="{{ route('admin.academic-years.create') }}" class="btn btn-gold">
                <i class="bi bi-plus-lg me-1"></i>Add Academic Year
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
BLADE;

 $ayPath = 'resources/views/admin/academic-years/index.blade.php';
if (is_dir(dirname($ayPath))) {
    file_put_contents($ayPath, $ay);
    echo "[OK] Academic Year index updated\n";
} else {
    echo "[WARN] Directory not found: " . dirname($ayPath) . "\n";
}

// ---- FIX 3: Term index ----
 $term = <<<'BLADE'
@extends("layouts.admin")
@section("page-title", "Terms")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-journal-text me-2"></i>Terms</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-gold">Terms</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.terms.create') }}" class="btn btn-gold">
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
                    <h3 class="fw-bold text-success mb-0">{{ $data->where('is_active', 1)->count() }}</h3>
                    <small class="text-muted">Active Terms</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #dc3545 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-danger mb-0">{{ $data->where('is_active', 0)->count() }}</h3>
                    <small class="text-muted">Inactive Terms</small>
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
                            <td>{{ $item->academic_year->name ?? '-' }}</td>
                            <td>{{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('M d, Y') : '-' }}</td>
                            <td>{{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('M d, Y') : '-' }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.terms.edit', $item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.terms.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Delete this term?')">
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
            <h5 class="mt-3 text-muted">No Terms Found</h5>
            <p class="text-muted">Start by adding your first term.</p>
            <a href="{{ route('admin.terms.create') }}" class="btn btn-gold">
                <i class="bi bi-plus-lg me-1"></i>Add Term
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
BLADE;

 $termPath = 'resources/views/admin/terms/index.blade.php';
if (is_dir(dirname($termPath))) {
    file_put_contents($termPath, $term);
    echo "[OK] Term index updated\n";
} else {
    echo "[WARN] Directory not found: " . dirname($termPath) . "\n";
}

// ---- FIX 4: Classroom index ----
 $classroom = <<<'BLADE'
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
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-0">{{ $data->count() }}</h3>
                    <small class="text-muted">Total Classrooms</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #28a745 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ $data->where('is_active', 1)->count() }}</h3>
                    <small class="text-muted">Active Classrooms</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #0dcaf0 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-0">{{ $data->where('is_active', 0)->count() }}</h3>
                    <small class="text-muted">Inactive Classrooms</small>
                </div>
            </div>
        </div>
    </div>

    @if($data->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>All Classrooms</h6>
                <span class="badge bg-light text-dark">{{ $data->count() }} classroom(s)</span>
            </div>
        </div>
        <div class="card-body p0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px">#</th>
                            <th>Class Name</th>
                            <th>Section</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th style="width:100px" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold">{{ $item->name }}</span></td>
                            <td>{{ $item->section ?? '-' }}</td>
                            <td>{{ $item->capacity ?? '-' }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.classrooms.edit', $item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.classrooms.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Delete this classroom?')">
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
BLADE;

// Try both possible folder names for Classroom
 $crPaths = [
    'resources/views/admin/Classroom/index.blade.php',
    'resources/views/admin/classrooms/index.blade.php',
];
 $crDone = false;
foreach ($crPaths as $p) {
    if (is_dir(dirname($p))) {
        file_put_contents($p, $classroom);
        echo "[OK] Classroom index updated at: $p\n";
        $crDone = true;
        break;
    }
}
if (!$crDone) {
    echo "[WARN] Classroom directory not found. Tried:\n";
    foreach ($crPaths as $p) echo "  - $p\n";
}

echo "\n=== All fixes applied! ===\n";
