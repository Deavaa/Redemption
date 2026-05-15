@extends('layouts.admin')
@section('title', 'Roles & Permissions')

@push('styles')
<style>
.rp-page{animation:rpIn .4s ease-out}
@keyframes rpIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.rp-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.rp-header-left{flex:1}
.rp-title{font-size:1.75rem;font-weight:800;color:#1a1a2e;margin:0;letter-spacing:-.5px}
.rp-subtitle{font-size:.9rem;color:#6c757d;margin:.25rem 0 0}
.rp-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}
.rp-breadcrumb li{color:#adb5bd}
.rp-breadcrumb li a{color:#6c757d;text-decoration:none;transition:color .2s}
.rp-breadcrumb li a:hover{color:#4361ee}
.rp-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}
.rp-breadcrumb li.active{color:#4361ee;font-weight:500}

.rp-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.25rem}
.rp-card-head{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.rp-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.rp-card-icon.blue{background:#eef2ff;color:#4361ee}
.rp-card-icon.purple{background:#f5f3ff;color:#7c3aed}
.rp-card-icon.green{background:#ecfdf5;color:#10b981}
.rp-card-icon.gold{background:#fefce8;color:#d97706}
.rp-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.rp-card-body{padding:1.25rem 1.5rem}

.rp-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.15rem;border-radius:10px;font-weight:600;font-size:.85rem;border:none;cursor:pointer;transition:all .25s;color:#fff;background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3);text-decoration:none}
.rp-btn:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4);color:#fff}
.rp-btn-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb;box-shadow:none}
.rp-btn-outline:hover{border-color:#4361ee;color:#4361ee;background:#f8f9ff;transform:none;box-shadow:none}
.rp-btn-danger{background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 2px 8px rgba(239,68,68,.3)}
.rp-btn-danger:hover{box-shadow:0 4px 16px rgba(239,68,68,.4)}

/* Stats */
.rp-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem}
.rp-stat{background:#fff;border-radius:12px;padding:1.15rem 1.25rem;display:flex;align-items:center;gap:1rem;border:1px solid #f0f0f0;box-shadow:0 1px 3px rgba(0,0,0,.04);transition:transform .2s}
.rp-stat:hover{transform:translateY(-2px)}
.rp-stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.rp-stat-val{font-size:1.5rem;font-weight:800;color:#1a1a2e}
.rp-stat-lbl{font-size:.78rem;color:#6c757d;font-weight:500}

/* Role Cards */
.rp-role-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1.25rem}
.rp-role-card{background:#fff;border-radius:14px;border:1px solid #f0f0f0;overflow:hidden;transition:all .25s;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.rp-role-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.08);transform:translateY(-2px)}
.rp-role-head{padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f0f0f0}
.rp-role-name{font-size:1.05rem;font-weight:700;color:#1a1a2e;margin:0}
.rp-role-badge{font-size:.72rem;padding:.2rem .55rem;border-radius:6px;font-weight:600}
.rp-role-badge.system{background:#fee2e2;color:#991b1b}
.rp-role-badge.custom{background:#ecfdf5;color:#065f46}
.rp-role-body{padding:1rem 1.25rem}
.rp-role-desc{font-size:.82rem;color:#6b7280;margin:0 0 .75rem}
.rp-role-meta{display:flex;gap:1rem;font-size:.8rem;color:#9ca3af}
.rp-role-meta i{margin-right:.25rem}
.rp-perm-chips{display:flex;flex-wrap:wrap;gap:.35rem;margin-top:.75rem}
.rp-perm-chip{font-size:.68rem;padding:.15rem .5rem;border-radius:4px;background:#f0f4ff;color:#4361ee;font-weight:500}
.rp-perm-chip.module-academic{background:#dbeafe;color:#1d4ed8}
.rp-perm-chip.module-people{background:#d1fae5;color:#065f46}
.rp-perm-chip.module-finance{background:#fef3c7;color:#92400e}
.rp-perm-chip.module-website{background:#ede9fe;color:#5b21b6}
.rp-perm-chip.module-communication{background:#fce7f3;color:#9d174d}
.rp-perm-chip.module-system{background:#fee2e2;color:#991b1b}
.rp-role-foot{padding:.75rem 1.25rem;border-top:1px solid #f0f0f0;background:#fafbfc;display:flex;gap:.5rem;flex-wrap:wrap}
</style>
@endpush

@section('content')
<div class="rp-page">
    <div class="rp-header">
        <div class="rp-header-left">
            <nav aria-label="breadcrumb" class="rp-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li>System</li><li class="active">Roles & Permissions</li></ol></nav>
            <h1 class="rp-title">Roles & Permissions</h1>
            <p class="rp-subtitle">Manage user roles and their system permissions</p>
        </div>
        <div>
            <a href="{{ route('admin.roles.create') }}" class="rp-btn"><i class="fas fa-plus"></i> New Role</a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="rp-stats">
        <div class="rp-stat">
            <div class="rp-stat-icon" style="background:#eef2ff;color:#4361ee"><i class="fas fa-user-shield"></i></div>
            <div><div class="rp-stat-val">{{ $roles->count() }}</div><div class="rp-stat-lbl">Total Roles</div></div>
        </div>
        <div class="rp-stat">
            <div class="rp-stat-icon" style="background:#ecfdf5;color:#10b981"><i class="fas fa-key"></i></div>
            <div><div class="rp-stat-val">{{ $permissions->sum(fn($g) => $g->count()) }}</div><div class="rp-stat-lbl">Total Permissions</div></div>
        </div>
        <div class="rp-stat">
            <div class="rp-stat-icon" style="background:#fefce8;color:#d97706"><i class="fas fa-layer-group"></i></div>
            <div><div class="rp-stat-val">{{ $modules->count() }}</div><div class="rp-stat-lbl">Modules</div></div>
        </div>
        <div class="rp-stat">
            <div class="rp-stat-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fas fa-users"></i></div>
            <div><div class="rp-stat-val">{{ $roles->sum('users_count') }}</div><div class="rp-stat-lbl">Users with Roles</div></div>
        </div>
    </div>

    {{-- Success --}}
    @if(session('success'))
    <div class="modern-alert modern-alert-success" style="display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;margin-bottom:1.25rem;border-radius:10px;font-size:.88rem;font-weight:500;background:#ecfdf5;color:#059669;border:1px solid #a7f3d0">
        <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="modern-alert modern-alert-danger" style="display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;margin-bottom:1.25rem;border-radius:10px;font-size:.88rem;font-weight:500;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5">
        <i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Role Cards --}}
    <div class="rp-role-grid">
        @foreach($roles as $role)
        <div class="rp-role-card">
            <div class="rp-role-head">
                <h3 class="rp-role-name">{{ $role->display_name }}</h3>
                @if($role->is_system)
                <span class="rp-role-badge system">SYSTEM</span>
                @else
                <span class="rp-role-badge custom">CUSTOM</span>
                @endif
            </div>
            <div class="rp-role-body">
                @if($role->description)
                <p class="rp-role-desc">{{ $role->description }}</p>
                @endif
                <div class="rp-role-meta">
                    <span><i class="fas fa-users"></i> {{ $role->users_count }} user{{ $role->users_count !== 1 ? 's' : '' }}</span>
                    <span><i class="fas fa-key"></i> {{ $role->permissions->count() }} permission{{ $role->permissions->count() !== 1 ? 's' : '' }}</span>
                </div>
                <div class="rp-perm-chips">
                    @foreach($role->permissions->groupBy('module') as $module => $perms)
                    <span class="rp-perm-chip module-{{ $module }}">{{ $module }} ({{ $perms->count() }})</span>
                    @endforeach
                </div>
            </div>
            <div class="rp-role-foot">
                <a href="{{ route('admin.roles.edit', $role) }}" class="rp-btn rp-btn-outline" style="font-size:.78rem;padding:.4rem .85rem"><i class="fas fa-edit"></i> Edit</a>
                <a href="{{ route('admin.roles.users', $role) }}" class="rp-btn rp-btn-outline" style="font-size:.78rem;padding:.4rem .85rem"><i class="fas fa-users"></i> Users</a>
                @if(!$role->is_system)
                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" style="display:inline" onsubmit="return confirm('Delete this role?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="rp-btn rp-btn-danger" style="font-size:.78rem;padding:.4rem .85rem"><i class="fas fa-trash"></i> Delete</button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Permissions Table --}}
    <div class="rp-card" style="margin-top:1.5rem">
        <div class="rp-card-head">
            <div style="display:flex;align-items:center;gap:.75rem">
                <div class="rp-card-icon purple"><i class="fas fa-key"></i></div>
                <h3 class="rp-card-title">All Permissions by Module</h3>
            </div>
        </div>
        <div class="rp-card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse;font-size:.85rem">
                @foreach($permissions as $module => $modulePerms)
                <thead>
                    <tr style="background:#f8fafc">
                        <th colspan="3" style="padding:.75rem 1.25rem;text-align:left;font-weight:700;color:#1a1a2e;border-bottom:2px solid #e5e7eb;text-transform:capitalize">
                            <i class="fas fa-folder" style="color:#7c3aed;margin-right:.5rem"></i>{{ $module }} Module
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modulePerms as $perm)
                    <tr style="border-bottom:1px solid #f0f0f0">
                        <td style="padding:.5rem 1.25rem;width:40%;font-weight:500;color:#1a1a2e">{{ $perm->display_name }}</td>
                        <td style="padding:.5rem 1rem;width:35%;font-family:monospace;font-size:.78rem;color:#6b7280">{{ $perm->name }}</td>
                        <td style="padding:.5rem 1rem;width:25%">
                            <span style="font-size:.72rem;padding:.15rem .5rem;border-radius:4px;background:#f0f4ff;color:#4361ee;font-weight:500">
                                {{ $roles->filter(fn($r) => $r->permissions->contains('id', $perm->id))->count() }} role(s)
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection
