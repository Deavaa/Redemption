@extends('layouts.admin')
@section('title', 'Teacher Access Management')

@push('styles')
<style>
.ua-page{animation:uaIn .4s ease-out}
@keyframes uaIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.ua-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.ua-header-left{flex:1}
.ua-title{font-size:1.75rem;font-weight:800;color:#1a1a2e;margin:0;letter-spacing:-.5px}
.ua-subtitle{font-size:.9rem;color:#6c757d;margin:.25rem 0 0}
.ua-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}
.ua-breadcrumb li{color:#adb5bd}
.ua-breadcrumb li a{color:#6c757d;text-decoration:none;transition:color .2s}
.ua-breadcrumb li a:hover{color:#4361ee}
.ua-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}
.ua-breadcrumb li.active{color:#4361ee;font-weight:500}

.ua-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.25rem}
.ua-card-head{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.ua-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.ua-card-icon.blue{background:#eef2ff;color:#4361ee}
.ua-card-icon.green{background:#ecfdf5;color:#10b981}
.ua-card-icon.gold{background:#fefce8;color:#d97706}
.ua-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.ua-card-body{padding:1.25rem 1.5rem}

.ua-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.15rem;border-radius:10px;font-weight:600;font-size:.85rem;border:none;cursor:pointer;transition:all .25s;color:#fff;background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3);text-decoration:none}
.ua-btn:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4);color:#fff}
.ua-btn-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb;box-shadow:none}
.ua-btn-outline:hover{border-color:#4361ee;color:#4361ee;background:#f8f9ff;transform:none;box-shadow:none}
.ua-btn-sm{font-size:.78rem;padding:.4rem .85rem}
.ua-btn-success{background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 2px 8px rgba(16,185,129,.3)}
.ua-btn-success:hover{box-shadow:0 4px 16px rgba(16,185,129,.4)}
.ua-btn-warning{background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 2px 8px rgba(245,158,11,.3)}
.ua-btn-warning:hover{box-shadow:0 4px 16px rgba(245,158,11,.4)}

.ua-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem}
.ua-stat{background:#fff;border-radius:12px;padding:1.15rem 1.25rem;display:flex;align-items:center;gap:1rem;border:1px solid #f0f0f0;box-shadow:0 1px 3px rgba(0,0,0,.04);transition:transform .2s}
.ua-stat:hover{transform:translateY(-2px)}
.ua-stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.ua-stat-val{font-size:1.5rem;font-weight:800;color:#1a1a2e}
.ua-stat-lbl{font-size:.78rem;color:#6c757d;font-weight:500}

.ua-table{width:100%;border-collapse:collapse;font-size:.85rem}
.ua-table th{padding:.75rem 1rem;text-align:left;font-weight:700;color:#1a1a2e;border-bottom:2px solid #e5e7eb;background:#f8fafc;white-space:nowrap}
.ua-table td{padding:.7rem 1rem;border-bottom:1px solid #f0f0f0;vertical-align:middle}
.ua-table tbody tr:hover{background:#f8f9ff}
.ua-table tbody tr:last-child td{border-bottom:none}

.ua-badge{display:inline-flex;align-items:center;gap:.35rem;font-size:.72rem;padding:.25rem .6rem;border-radius:6px;font-weight:600}
.ua-badge-success{background:#ecfdf5;color:#059669}
.ua-badge-warning{background:#fefce8;color:#d97706}
.ua-badge-info{background:#eef2ff;color:#4361ee}

.ua-avatar{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:#fff;flex-shrink:0}

/* Permissions Modal */
.ua-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:flex;align-items:center;justify-content:center;animation:uaFadeIn .2s}
@keyframes uaFadeIn{from{opacity:0}to{opacity:1}}
.ua-modal{background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.2);max-width:600px;width:95%;max-height:85vh;overflow-y:auto;animation:uaSlideIn .3s}
@keyframes uaSlideIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.ua-modal-head{padding:1.25rem 1.5rem;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center}
.ua-modal-head h3{margin:0;font-size:1.1rem;font-weight:700;color:#1a1a2e}
.ua-modal-body{padding:1.5rem}
.ua-modal-foot{padding:1rem 1.5rem;border-top:1px solid #f0f0f0;display:flex;justify-content:flex-end;gap:.5rem}
.ua-perm-group{margin-bottom:1.25rem}
.ua-perm-group-title{font-size:.85rem;font-weight:700;color:#1a1a2e;margin-bottom:.5rem;text-transform:capitalize}
.ua-perm-item{display:flex;align-items:center;gap:.5rem;padding:.35rem 0;font-size:.82rem;color:#374151}
.ua-perm-item input[type="checkbox"]{accent-color:#4361ee;width:16px;height:16px}
.ua-close-btn{background:none;border:none;font-size:1.2rem;cursor:pointer;color:#6b7280;padding:.25rem;border-radius:6px;transition:all .2s}
.ua-close-btn:hover{background:#f3f4f6;color:#1a1a2e}
</style>
@endpush

@section('content')
<div class="ua-page">
    <div class="ua-header">
        <div class="ua-header-left">
            <nav aria-label="breadcrumb" class="ua-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li>System</li><li class="active">Teacher Access</li></ol></nav>
            <h1 class="ua-title">Teacher Access Management</h1>
            <p class="ua-subtitle">Create user accounts for teachers and manage their system permissions</p>
        </div>
    </div>

    {{-- Stats --}}
    @php
        $totalTeachers = $teachers->count();
        $withAccount = $teachers->where('user_id', '!=', null)->count();
        $withoutAccount = $totalTeachers - $withAccount;
        $activeTeachers = $teachers->where('status', 'active')->count();
    @endphp
    <div class="ua-stats">
        <div class="ua-stat">
            <div class="ua-stat-icon" style="background:#eef2ff;color:#4361ee"><i class="fas fa-chalkboard-teacher"></i></div>
            <div><div class="ua-stat-val">{{ $totalTeachers }}</div><div class="ua-stat-lbl">Total Teachers</div></div>
        </div>
        <div class="ua-stat">
            <div class="ua-stat-icon" style="background:#ecfdf5;color:#10b981"><i class="fas fa-user-check"></i></div>
            <div><div class="ua-stat-val">{{ $withAccount }}</div><div class="ua-stat-lbl">With Account</div></div>
        </div>
        <div class="ua-stat">
            <div class="ua-stat-icon" style="background:#fefce8;color:#d97706"><i class="fas fa-user-plus"></i></div>
            <div><div class="ua-stat-val">{{ $withoutAccount }}</div><div class="ua-stat-lbl">Need Account</div></div>
        </div>
        <div class="ua-stat">
            <div class="ua-stat-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fas fa-check-circle"></i></div>
            <div><div class="ua-stat-val">{{ $activeTeachers }}</div><div class="ua-stat-lbl">Active</div></div>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div style="display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;margin-bottom:1.25rem;border-radius:10px;font-size:.88rem;font-weight:500;background:#ecfdf5;color:#059669;border:1px solid #a7f3d0">
        <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div style="display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;margin-bottom:1.25rem;border-radius:10px;font-size:.88rem;font-weight:500;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5">
        <i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Teachers Table --}}
    <div class="ua-card">
        <div class="ua-card-head">
            <div style="display:flex;align-items:center;gap:.75rem">
                <div class="ua-card-icon blue"><i class="fas fa-chalkboard-teacher"></i></div>
                <h3 class="ua-card-title">Teachers</h3>
            </div>
            @if($teacherRole)
            <span class="ua-badge ua-badge-info"><i class="fas fa-shield-alt"></i> Role: {{ $teacherRole->display_name }}</span>
            @endif
        </div>
        <div class="ua-card-body" style="padding:0">
            <table class="ua-table">
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Account</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $teacher)
                    @php
                        $colors = ['#4361ee','#7c3aed','#10b981','#f59e0b','#ef4444','#06b6d4','#8b5cf6','#ec4899'];
                        $color = $colors[$teacher->id % count($colors)];
                        $initials = strtoupper(substr($teacher->first_name, 0, 1) . substr($teacher->last_name, 0, 1));
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:.75rem">
                                <div class="ua-avatar" style="background:{{ $color }}">{{ $initials }}</div>
                                <div>
                                    <div style="font-weight:600;color:#1a1a2e">{{ $teacher->full_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="color:#6b7280">{{ $teacher->email }}</td>
                        <td style="color:#6b7280">{{ $teacher->department ?? '—' }}</td>
                        <td>
                            @if($teacher->status === 'active')
                            <span class="ua-badge ua-badge-success"><i class="fas fa-circle" style="font-size:6px"></i> Active</span>
                            @else
                            <span class="ua-badge ua-badge-warning"><i class="fas fa-circle" style="font-size:6px"></i> Inactive</span>
                            @endif
                        </td>
                        <td>
                            @if($teacher->user_id)
                            <span class="ua-badge ua-badge-success"><i class="fas fa-check"></i> Linked</span>
                            @else
                            <span class="ua-badge ua-badge-warning"><i class="fas fa-times"></i> None</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            @if(!$teacher->user_id)
                            <form method="POST" action="{{ route('admin.user-access.teachers.create') }}" style="display:inline">
                                @csrf
                                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                                <button type="submit" class="ua-btn ua-btn-success ua-btn-sm"><i class="fas fa-user-plus"></i> Create Account</button>
                            </form>
                            @else
                            <button type="button" class="ua-btn ua-btn-outline ua-btn-sm" onclick="openPermModal({{ $teacher->user->id }}, '{{ $teacher->full_name }}')"><i class="fas fa-key"></i> Permissions</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Permissions Modal --}}
<div id="permModal" style="display:none">
    <div class="ua-modal-overlay" onclick="closePermModal()">
        <div class="ua-modal" onclick="event.stopPropagation()">
            <div class="ua-modal-head">
                <h3><i class="fas fa-key" style="color:#4361ee;margin-right:.5rem"></i> <span id="permModalName"></span></h3>
                <button class="ua-close-btn" onclick="closePermModal()"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.user-access.teachers.permissions') }}" id="permForm">
                @csrf
                <input type="hidden" name="user_id" id="permUserId">
                <div class="ua-modal-body">
                    @php
                        $allPermissions = \App\Models\Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
                    @endphp
                    @foreach($allPermissions as $module => $perms)
                    <div class="ua-perm-group">
                        <div class="ua-perm-group-title"><i class="fas fa-folder" style="color:#7c3aed;margin-right:.35rem"></i>{{ $module }}</div>
                        @foreach($perms as $perm)
                        <label class="ua-perm-item">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="perm-check" data-user-id="">
                            {{ $perm->display_name }}
                            <span style="color:#9ca3af;font-size:.72rem;margin-left:.25rem">({{ $perm->name }})</span>
                        </label>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                <div class="ua-modal-foot">
                    <button type="button" class="ua-btn ua-btn-outline" onclick="closePermModal()">Cancel</button>
                    <button type="submit" class="ua-btn"><i class="fas fa-save"></i> Save Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentUserPerms = {};

function openPermModal(userId, name) {
    document.getElementById('permModalName').textContent = name + ' — Permissions';
    document.getElementById('permUserId').value = userId;

    // Update data attribute on checkboxes
    document.querySelectorAll('.perm-check').forEach(cb => {
        cb.dataset.userId = userId;
        cb.checked = false;
    });

    // Fetch user's current direct permissions
    fetch('{{ route("admin.roles.index") }}?_token={{ csrf_token() }}')
        .then(() => {
            // We'll load permissions via a simple approach - check existing
            // For now, uncheck all and let admin set them
        })
        .catch(() => {});

    document.getElementById('permModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closePermModal() {
    document.getElementById('permModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePermModal();
});
</script>
@endpush
@endsection
