@extends("layouts.admin")
@section("page-title","Settings")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-gear me-2"></i>Settings</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route("admin.dashboard") }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active text-gold">Settings</li>
        </ol></nav></div>
    </div>
    @if(session("success"))
    <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session("success") }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    <form method="POST" action="{{ route("admin.settings.update") }}">@csrf @method("PUT")
    @foreach($settings as $group => $items)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3" style="border-top:3px solid #c9a84c;"><h5 class="mb-0 fw-semibold"><i class="bi bi-sliders me-2"></i>{{ $groupLabels[$group] ?? ucfirst($group) }}</h5></div>
        <div class="card-body"><div class="row g-3">
            @foreach($items as $s)
            <div class="col-md-6">
                <label class="form-label fw-medium">{{ ucfirst(str_replace("_"," ",$s->key)) }}</label>
                <input type="text" name="settings[{{ $group }}__{{ $s->key }}]" class="form-control form-control-sm" value="{{ old($group."__".$s->key, $s->value) }}">
            </div>
            @endforeach
        </div></div>
    </div>
    @endforeach
    @if($settings->isEmpty())
    <div class="card border-0 shadow-sm mb-4"><div class="card-body text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size:3rem;"></i>
        <h5 class="mt-3 text-muted">No Settings Found</h5>
        <p class="text-muted">Add settings to the database to configure them here.</p>
    </div></div>
    @endif
    <button type="submit" class="btn btn-gold mb-4"><i class="bi bi-check-lg me-1"></i>Save All Settings</button>
    </form>
</div>
@endsection