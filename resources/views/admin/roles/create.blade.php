@extends('layouts.admin')
@section('title', 'Create Role')

@push('styles')
<style>
.rp-page{animation:rpIn .4s ease-out}
@keyframes rpIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.rp-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.rp-title{font-size:1.75rem;font-weight:800;color:#1a1a2e;margin:0;letter-spacing:-.5px}
.rp-subtitle{font-size:.9rem;color:#6c757d;margin:.25rem 0 0}
.rp-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.25rem}
.rp-card-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.rp-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.rp-card-icon.blue{background:#eef2ff;color:#4361ee}
.rp-card-icon.purple{background:#f5f3ff;color:#7c3aed}
.rp-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.rp-card-body{padding:1.25rem 1.5rem}

.rp-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.15rem;border-radius:10px;font-weight:600;font-size:.85rem;border:none;cursor:pointer;transition:all .25s;color:#fff;background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3);text-decoration:none}
.rp-btn:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4);color:#fff}
.rp-btn-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb;box-shadow:none}
.rp-btn-outline:hover{border-color:#4361ee;color:#4361ee;background:#f8f9ff;transform:none;box-shadow:none}

.rp-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem}
.rp-form-group{display:flex;flex-direction:column}
.rp-label{font-weight:600;color:#374151;margin-bottom:.4rem;font-size:.85rem}
.rp-input{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.65rem .9rem;font-size:.9rem;color:#1a1a2e;transition:all .2s}
.rp-input:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.rp-textarea{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.65rem .9rem;font-size:.9rem;color:#1a1a2e;transition:all .2s;resize:vertical;font-family:inherit}
.rp-textarea:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}

/* Permission Groups */
.rp-perm-module{margin-bottom:1.25rem;border:1px solid #f0f0f0;border-radius:10px;overflow:hidden}
.rp-perm-module-head{padding:.75rem 1rem;background:#f8fafc;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;justify-content:space-between;cursor:pointer}
.rp-perm-module-title{font-weight:700;color:#1a1a2e;font-size:.9rem;text-transform:capitalize}
.rp-perm-module-body{padding:.75rem 1rem}
.rp-perm-item{display:flex;align-items:center;gap:.75rem;padding:.35rem 0}
.rp-perm-item label{display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem;color:#374151}
.rp-perm-item input[type="checkbox"]{width:18px;height:18px;accent-color:#4361ee}
.rp-select-all{font-size:.78rem;color:#4361ee;cursor:pointer;font-weight:600;background:none;border:none}
.rp-select-all:hover{text-decoration:underline}

.rp-form-actions{display:flex;justify-content:flex-end;gap:.75rem;padding:1rem 1.5rem;border-top:1px solid #f0f0f0;background:#fafbfc}

@media(max-width:768px){.rp-form-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="rp-page">
    <div class="rp-header">
        <div>
            <h1 class="rp-title">Create New Role</h1>
            <p class="rp-subtitle">Define a new role with specific permissions</p>
        </div>
    </div>

    @if($errors->any())
    <div style="background:#fee2e2;color:#991b1b;padding:.85rem 1.25rem;border-radius:10px;margin-bottom:1.25rem;font-size:.88rem;border:1px solid #fca5a5">
        <i class="fas fa-exclamation-circle"></i>
        <ul style="margin:.25rem 0 0 1rem">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.roles.store') }}">
        @csrf

        <div class="rp-card">
            <div class="rp-card-head">
                <div class="rp-card-icon blue"><i class="fas fa-user-shield"></i></div>
                <h3 class="rp-card-title">Role Details</h3>
            </div>
            <div class="rp-card-body">
                <div class="rp-form-grid">
                    <div class="rp-form-group">
                        <label class="rp-label">Role Name (slug) <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name" class="rp-input" value="{{ old('name') }}" placeholder="e.g. department_head" required>
                        <small style="color:#9ca3af;font-size:.75rem;margin-top:.25rem">Lowercase, underscores allowed. Used internally.</small>
                    </div>
                    <div class="rp-form-group">
                        <label class="rp-label">Display Name <span style="color:#ef4444">*</span></label>
                        <input type="text" name="display_name" class="rp-input" value="{{ old('display_name') }}" placeholder="e.g. Department Head" required>
                    </div>
                    <div class="rp-form-group" style="grid-column:span 2">
                        <label class="rp-label">Description</label>
                        <textarea name="description" class="rp-textarea" rows="2" placeholder="Describe what this role can do...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="rp-card">
            <div class="rp-card-head">
                <div class="rp-card-icon purple"><i class="fas fa-key"></i></div>
                <h3 class="rp-card-title">Assign Permissions</h3>
            </div>
            <div class="rp-card-body">
                @foreach($permissions as $module => $modulePerms)
                <div class="rp-perm-module">
                    <div class="rp-perm-module-head">
                        <span class="rp-perm-module-title"><i class="fas fa-folder"></i> {{ $module }}</span>
                        <button type="button" class="rp-select-all" onclick="toggleModule('{{ $module }}')">Select All</button>
                    </div>
                    <div class="rp-perm-module-body" id="module-{{ $module }}">
                        @foreach($modulePerms as $perm)
                        <div class="rp-perm-item">
                            <label>
                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="module-{{ $module }}" {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}>
                                {{ $perm->display_name }}
                                <code style="font-size:.72rem;color:#9ca3af;background:#f3f4f6;padding:.1rem .35rem;border-radius:3px">{{ $perm->name }}</code>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="rp-form-actions">
            <a href="{{ route('admin.roles.index') }}" class="rp-btn rp-btn-outline">Cancel</a>
            <button type="submit" class="rp-btn"><i class="fas fa-save"></i> Create Role</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleModule(module) {
    var boxes = document.querySelectorAll('.module-' + module);
    var allChecked = true;
    boxes.forEach(function(b) { if (!b.checked) allChecked = false; });
    boxes.forEach(function(b) { b.checked = !allChecked; });
}
</script>
@endpush
