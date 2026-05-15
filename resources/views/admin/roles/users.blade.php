@extends('layouts.admin')
@section('title', 'Role Users')

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

.user-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem}
.user-card{border:1px solid #e5e7eb;border-radius:10px;padding:1rem;display:flex;align-items:center;gap:.85rem;transition:all .2s}
.user-card:hover{border-color:#4361ee;box-shadow:0 2px 8px rgba(67,97,238,.1)}
.user-avatar{width:40px;height:40px;border-radius:50%;background:#4361ee;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;flex-shrink:0}
.user-info{flex:1}
.user-name{font-weight:600;color:#1a1a2e;font-size:.9rem}
.user-email{font-size:.78rem;color:#9ca3af}
.user-role-badge{font-size:.68rem;padding:.1rem .4rem;border-radius:3px;background:#f0f4ff;color:#4361ee;font-weight:500}
.user-check{width:20px;height:20px;accent-color:#4361ee}

.rp-form-actions{display:flex;justify-content:flex-end;gap:.75rem;padding:1rem 1.5rem;border-top:1px solid #f0f0f0;background:#fafbfc}

.search-box{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.6rem 1rem;font-size:.88rem;transition:all .2s;margin-bottom:1rem}
.search-box:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
</style>
@endpush

@section('content')
<div class="rp-page">
    <div class="rp-header">
        <div>
            <h1 class="rp-title">Users in "{{ $role->display_name }}"</h1>
            <p class="rp-subtitle">Assign or remove users from this role</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="rp-btn rp-btn-outline"><i class="fas fa-arrow-left"></i> Back to Roles</a>
    </div>

    @if(session('success'))
    <div style="background:#ecfdf5;color:#059669;padding:.85rem 1.25rem;border-radius:10px;margin-bottom:1.25rem;font-size:.88rem;font-weight:500;border:1px solid #a7f3d0">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.roles.assign-users', $role) }}">
        @csrf

        <div class="rp-card">
            <div class="rp-card-head">
                <div class="rp-card-icon purple"><i class="fas fa-users"></i></div>
                <h3 class="rp-card-title">Assign Users ({{ $allUsers->count() }} total)</h3>
            </div>
            <div class="rp-card-body">
                <input type="text" class="search-box" id="userSearch" placeholder="Search by name or email...">

                <div class="user-grid" id="userGrid">
                    @foreach($allUsers as $user)
                    <div class="user-card" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}">
                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-check" {{ in_array($user->id, $assignedUserIds) ? 'checked' : '' }}>
                        <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                        <div class="user-info">
                            <div class="user-name">{{ $user->name }}</div>
                            <div class="user-email">{{ $user->email }}</div>
                            <span class="user-role-badge">{{ ucfirst($user->role) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="rp-form-actions">
                <a href="{{ route('admin.roles.index') }}" class="rp-btn rp-btn-outline">Cancel</a>
                <button type="submit" class="rp-btn"><i class="fas fa-save"></i> Save User Assignments</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('userSearch')?.addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.user-card').forEach(function(card) {
        var name = card.dataset.name || '';
        var email = card.dataset.email || '';
        card.style.display = (name.includes(q) || email.includes(q)) ? '' : 'none';
    });
});
</script>
@endpush
