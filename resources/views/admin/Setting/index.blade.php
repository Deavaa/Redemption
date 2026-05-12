@extends("layouts.admin")
@section("page-title","Settings")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-gear me-2"></i>School Settings</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active text-gold">Settings</li>
        </ol></nav></div>
    </div>
    @if(session("success"))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session("success") }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    <form method="POST" action="{{ route('admin.settings.updateAll') }}">@csrf
    @foreach($settings as $group => $items)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-folder2-open me-2"></i>{{ ucfirst($group) }} Settings</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
            @foreach($items as $item)
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ ucfirst(str_replace("_"," ",$item->key)) }}</label>
                    @if($item->type === "boolean")
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="{{ $item->key }}" value="1" {{ $item->value ? "checked" : "" }}>
                        </div>
                    @elseif($item->type === "number")
                        <input type="number" name="{{ $item->key }}" class="form-control" value="{{ $item->value }}">
                    @else
                        <input type="text" name="{{ $item->key }}" class="form-control" value="{{ $item->value }}">
                    @endif
                    @if($item->description)<small class="text-muted d-block">{{ $item->description }}</small>@endif
                </div>
            @endforeach
            </div>
        </div>
    </div>
    @endforeach
    <button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Save All Settings</button>
    </form>
</div>
@endsection