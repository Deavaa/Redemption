@extends("layouts.admin")
@section("page-title", "Class Assets")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-box-seam me-2"></i>Class Assets</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-gold">Class Assets</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.class-assets.create') }}" class="btn btn-gold">
            <i class="bi bi-plus-lg me-1"></i>Register Asset
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-0">{{ $totalAssets }}</h3>
                    <small class="text-muted">Total Assets</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #28a745 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ number_format($totalValue, 2) }}</h3>
                    <small class="text-muted">Total Value (ETB)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #0dcaf0 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-0">{{ $classrooms->count() }}</h3>
                    <small class="text-muted">Classrooms</small>
                </div>
            </div>
        </div>
    </div>

    @if(session("success"))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session("success") }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($data->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>All Assets</h6>
                <span class="badge bg-light text-dark">{{ $data->count() }} asset(s)</span>
            </div>
        </div>
        <div class="card-body p0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px">#</th>
                            <th>Asset Name</th>
                            <th>Classroom</th>
                            <th>Section</th>
                            <th>Quantity</th>
                            <th>Condition</th>
                            <th>Purchase Date</th>
                            <th>Price</th>
                            <th>Description</th>
                            <th style="width:100px" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold">{{ $item->name }}</span></td>
                            <td class="text-muted">{{ $item->classroom->name ?? "-" }}</td>
                            <td>
                                @if($item->section_id)
                                    <span class="badge bg-info bg-opacity-10 text-info">{{ $item->section->name ?? "-" }}</span>
                                @else
                                    <span class="text-muted">All Sections</span>
                                @endif
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                @php
                                    $condColor = match($item->condition) {
                                        "new" => "success",
                                        "good" => "info",
                                        "fair" => "warning",
                                        "poor" => "danger",
                                        "damaged" => "secondary",
                                        default => "secondary"
                                    };
                                @endphp
                                <span class="badge bg-{{ $condColor }} bg-opacity-10 text-{{ $condColor }}">{{ ucfirst($item->condition) }}</span>
                            </td>
                            <td class="text-muted">{{ $item->purchase_date ?? "-" }}</td>
                            <td class="text-muted">{{ $item->purchase_price ? number_format($item->purchase_price, 2) : "-" }}</td>
                            <td class="text-muted small">{{ Str::limit($item->description ?? "-", 40) }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.class-assets.edit', $item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.class-assets.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Delete this asset?')">
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
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-box-seam fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No Assets Found</h5>
            <p class="text-muted">Start by registering your first class asset.</p>
            <a href="{{ route('admin.class-assets.create') }}" class="btn btn-gold">
                <i class="bi bi-plus-lg me-1"></i>Register Asset
            </a>
        </div>
    </div>
    @endif
</div>
@endsection