@extends('layouts.admin')
@section('title', 'Staff & Users')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">People</a></li>
                    <li class="active">Staff & Users</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.staff.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-user-plus"></i>
                <span>Add User</span>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    @php
        $roleCounts = [];
        $allRoles = ['admin'=>'Admin','teacher'=>'Teacher','general_manager'=>'General Manager','branch_principal'=>'Branch Principal','registrar'=>'Registrar','finance'=>'Finance','hr'=>'HR','cashier'=>'Cashier','librarian'=>'Librarian','staff'=>'Staff'];
        $restrictedRoles = ['admin', 'general_manager', 'finance', 'hr'];
        $isBranchPrincipal = auth()->user()->role === 'branch_principal';
        foreach($allRoles as $key => $label) {
            $roleCounts[$key] = $staff->where('role', $key)->count();
        }
    @endphp
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-users"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $staff->total() }}</span>
                <span class="modern-stat-label">Total Staff</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ ($roleCounts['admin'] ?? 0) + ($roleCounts['general_manager'] ?? 0) }}</span>
                <span class="modern-stat-label">Admin & GM</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-purple">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ ($roleCounts['branch_principal'] ?? 0) + ($roleCounts['registrar'] ?? 0) }}</span>
                <span class="modern-stat-label">Academic Staff</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-orange">
                <i class="fas fa-coins"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ ($roleCounts['finance'] ?? 0) + ($roleCounts['cashier'] ?? 0) + ($roleCounts['hr'] ?? 0) }}</span>
                <span class="modern-stat-label">Finance & HR</span>
            </div>
        </div>
    </div>

    {{-- Staff Table Card --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">All Staff Members</h2>
                <span class="modern-badge modern-badge-light">{{ $staff->total() }} records</span>
            </div>
            <div class="modern-card-header-right">
                <div class="modern-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="staffSearch" placeholder="Search staff..." onkeyup="filterTable()">
                </div>
            </div>
        </div>

        {{-- Role Filter Pills --}}
        <div class="modern-filter-bar">
            <div class="modern-filter-pills">
                <a href="{{ route('admin.staff.index') }}" class="modern-filter-pill {{ request()->missing('role') ? 'active' : '' }}">All</a>
                @foreach($allRoles as $val => $label)
                <a href="{{ route('admin.staff.index', ['role' => $val]) }}" class="modern-filter-pill {{ request('role') === $val ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        <div class="modern-card-body">
            @if(session('success'))
                <div class="modern-alert modern-alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="modern-alert modern-alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if($staff->count() > 0)
            {{-- Desktop Table View --}}
            <div class="modern-table-wrapper has-mobile-cards">
                <table class="modern-table" id="staffTable">
                    <thead>
                        <tr>
                            <th class="th-narrow">#</th>
                            <th>Name</th>
                            <th>Employee ID</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Branch</th>
                            <th class="th-center">Status</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staff as $s)
                        @php
                            $roleColors = [
                                'admin' => '#dc2626',
                                'teacher' => '#4361ee',
                                'general_manager' => '#7c3aed',
                                'branch_principal' => '#0891b2',
                                'registrar' => '#059669',
                                'finance' => '#d97706',
                                'hr' => '#e11d48',
                                'cashier' => '#ea580c',
                                'librarian' => '#4f46e5',
                                'staff' => '#6b7280',
                            ];
                            $rc = $roleColors[$s->role] ?? '#6b7280';
                        @endphp
                        <tr class="modern-table-row">
                            <td class="td-narrow">
                                <span class="modern-row-number">{{ $staff->firstItem() + $loop->index }}</span>
                            </td>
                            <td>
                                <div class="modern-cell-user">
                                    <div class="modern-avatar modern-avatar-placeholder" style="background: linear-gradient(135deg, {{ $rc }}, {{ $rc }}cc);">
                                        {{ strtoupper(substr($s->name, 0, 1)) }}
                                    </div>
                                    <div class="modern-cell-user-info">
                                        <div class="modern-cell-title">{{ $s->name }}</div>
                                        @if($s->phone)
                                            <div class="modern-cell-sub"><i class="fas fa-phone" style="font-size:.65rem;margin-right:3px;"></i>{{ $s->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="modern-cell-emp-id">{{ $s->employee_id ?? '-' }}</span>
                            </td>
                            <td>
                                <div class="modern-cell-contact">
                                    <i class="fas fa-envelope" style="color:#4361ee;font-size:.75rem;"></i> {{ $s->email }}
                                </div>
                            </td>
                            <td>
                                <span class="modern-role-chip" style="background:{{ $rc }}15;color:{{ $rc }};border:1px solid {{ $rc }}30;">
                                    {{ $allRoles[$s->role] ?? ucfirst(str_replace('_', ' ', $s->role)) }}
                                </span>
                            </td>
                            <td>
                                @if($s->branch)
                                    <span class="modern-branch-chip"><i class="fas fa-building" style="font-size:.6rem;"></i> {{ $s->branch->name }}</span>
                                @else
                                    <span class="modern-cell-muted">All Branches</span>
                                @endif
                            </td>
                            <td class="td-center">
                                @if($s->is_active)
                                    <span class="modern-badge modern-badge-success"><i class="fas fa-circle" style="font-size:.4rem;margin-right:4px;"></i> Active</span>
                                @else
                                    <span class="modern-badge modern-badge-danger"><i class="fas fa-circle" style="font-size:.4rem;margin-right:4px;"></i> Inactive</span>
                                @endif
                            </td>
                            <td class="td-actions">
                                <div class="modern-action-group">
                                    @if(!$isBranchPrincipal || !in_array($s->role, $restrictedRoles))
                                    <a href="{{ route('admin.staff.edit', ['staff' => $s->id]) }}" class="modern-btn-icon modern-btn-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @endif
                                    @if($s->id !== auth()->id() && (!$isBranchPrincipal || !in_array($s->role, $restrictedRoles)))
                                    <form method="POST" action="{{ route('admin.staff.destroy', ['staff' => $s->id]) }}" style="display:inline" onsubmit="return confirm('Remove this staff member?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="modern-btn-icon modern-btn-delete" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card View --}}
            <div class="mobile-card-view" id="mobileCardView">
                @foreach($staff as $s)
                @php
                    $roleColors = [
                        'admin' => '#dc2626', 'teacher' => '#4361ee', 'general_manager' => '#7c3aed',
                        'branch_principal' => '#0891b2', 'registrar' => '#059669', 'finance' => '#d97706',
                        'hr' => '#e11d48', 'cashier' => '#ea580c', 'librarian' => '#4f46e5', 'staff' => '#6b7280',
                    ];
                    $rc = $roleColors[$s->role] ?? '#6b7280';
                @endphp
                <div class="mobile-card-item">
                    <div class="mobile-card-item-header">
                        <div class="mobile-card-item-avatar" style="background: linear-gradient(135deg, {{ $rc }}, {{ $rc }}cc);">
                            {{ strtoupper(substr($s->name, 0, 1)) }}
                        </div>
                        <div class="mobile-card-item-title">
                            <div class="mobile-card-item-name">{{ $s->name }}</div>
                            <div class="mobile-card-item-sub">{{ $s->email }}</div>
                        </div>
                        <div class="mobile-card-item-actions">
                            @if(!$isBranchPrincipal || !in_array($s->role, $restrictedRoles))
                            <a href="{{ route('admin.staff.edit', ['staff' => $s->id]) }}" class="modern-btn-icon modern-btn-edit" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            @endif
                            @if($s->id !== auth()->id() && (!$isBranchPrincipal || !in_array($s->role, $restrictedRoles)))
                            <form method="POST" action="{{ route('admin.staff.destroy', ['staff' => $s->id]) }}" style="display:inline" onsubmit="return confirm('Remove this staff member?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="modern-btn-icon modern-btn-delete" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    <div class="mobile-card-item-body">
                        <div class="mobile-card-field">
                            <span class="mobile-card-field-label">Employee ID</span>
                            <span class="modern-cell-emp-id">{{ $s->employee_id ?? '-' }}</span>
                        </div>
                        <div class="mobile-card-field">
                            <span class="mobile-card-field-label">Role</span>
                            <span class="modern-role-chip" style="background:{{ $rc }}15;color:{{ $rc }};border:1px solid {{ $rc }}30;">
                                {{ $allRoles[$s->role] ?? ucfirst(str_replace('_', ' ', $s->role)) }}
                            </span>
                        </div>
                        <div class="mobile-card-field">
                            <span class="mobile-card-field-label">Branch</span>
                            <span class="mobile-card-field-value">
                                @if($s->branch)
                                    <i class="fas fa-building" style="font-size:.6rem;color:#3b82f6;"></i> {{ $s->branch->name }}
                                @else
                                    All Branches
                                @endif
                            </span>
                        </div>
                        @if($s->phone)
                        <div class="mobile-card-field">
                            <span class="mobile-card-field-label">Phone</span>
                            <span class="mobile-card-field-value"><i class="fas fa-phone" style="font-size:.6rem;color:#9ca3af;"></i> {{ $s->phone }}</span>
                        </div>
                        @endif
                        <div class="mobile-card-field">
                            <span class="mobile-card-field-label">Status</span>
                            @if($s->is_active)
                                <span class="modern-badge modern-badge-success"><i class="fas fa-circle" style="font-size:.4rem;margin-right:4px;"></i> Active</span>
                            @else
                                <span class="modern-badge modern-badge-danger"><i class="fas fa-circle" style="font-size:.4rem;margin-right:4px;"></i> Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($staff->hasPages())
            <div class="modern-pagination-wrapper">
                {{ $staff->withQueryString()->links() }}
            </div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3>No Staff Members Yet</h3>
                <p>Get started by adding your first staff member.</p>
                <a href="{{ route('admin.staff.create') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-plus"></i> Add First User
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
/* Modern Page Layout */
.modern-page { animation: fadeSlideIn 0.4s ease-out; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.modern-page-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;
}
.modern-page-header-left { flex: 1; }
/* Breadcrumb */
.modern-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Stats Row */
.modern-stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.75rem; }
.modern-stat-card { background: #fff; border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; transition: transform 0.2s, box-shadow 0.2s; }
.modern-stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.modern-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.modern-stat-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-stat-icon-green { background: #ecfdf5; color: #10b981; }
.modern-stat-icon-purple { background: #faf5ff; color: #8b5cf6; }
.modern-stat-icon-orange { background: #fff7ed; color: #ea580c; }
.modern-stat-icon-red { background: #fef2f2; color: #ef4444; }
.modern-stat-icon-gray { background: #f3f4f6; color: #6b7280; }
.modern-stat-info { display: flex; flex-direction: column; }
.modern-stat-value { font-size: 1.5rem; font-weight: 800; color: #1a1a2e; line-height: 1.2; }
.modern-stat-label { font-size: 0.8rem; color: #6c757d; font-weight: 500; }

/* Card */
.modern-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.5rem; }
.modern-card-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0; flex-wrap: wrap; gap: 1rem; }
.modern-card-header-left { display: flex; align-items: center; gap: 0.75rem; }
.modern-card-title { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-card-body { padding: 0; }

/* Filter Bar */
.modern-filter-bar { padding: 0.75rem 1.5rem; border-bottom: 1px solid #f0f0f0; }
.modern-filter-pills { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.modern-filter-pill { padding: 0.35rem 0.85rem; border-radius: 50px; font-size: 0.78rem; font-weight: 600; color: #6b7280; background: #f3f4f6; text-decoration: none; transition: all 0.2s; white-space: nowrap; }
.modern-filter-pill:hover { background: #e5e7eb; color: #374151; }
.modern-filter-pill.active { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 6px rgba(67,97,238,0.3); }

/* Badges */
.modern-badge { display: inline-flex; align-items: center; padding: 0.25rem 0.65rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.3px; }
.modern-badge-light { background: #f3f4f6; color: #6b7280; }
.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-info { background: #eff6ff; color: #2563eb; }
.modern-badge-warning { background: #fefce8; color: #b45309; }

/* Role Chip */
.modern-role-chip { padding: 0.25rem 0.7rem; border-radius: 50px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.3px; white-space: nowrap; }

/* Branch Chip */
.modern-branch-chip { display: inline-flex; align-items: center; gap: 4px; font-size: 0.78rem; background: #f0f4ff; color: #3b82f6; padding: 3px 10px; border-radius: 8px; font-weight: 500; }

/* Search Box */
.modern-search-box { position: relative; display: flex; align-items: center; }
.modern-search-box i { position: absolute; left: 12px; color: #adb5bd; font-size: 0.85rem; }
.modern-search-box input { border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.55rem 0.75rem 0.55rem 2.25rem; font-size: 0.875rem; width: 220px; transition: all 0.2s; background: #f9fafb; color: #374151; }
.modern-search-box input:focus { outline: none; border-color: #4361ee; background: #fff; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.modern-search-box input::placeholder { color: #9ca3af; }

/* Table */
.modern-table-wrapper { overflow-x: auto; }
.modern-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.modern-table thead th { background: #f9fafb; padding: 0.85rem 1rem; text-align: left; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 2px solid #e5e7eb; white-space: nowrap; }
.modern-table.th-center, .modern-table thead th.th-center { text-align: center; }
.modern-table.th-actions, .modern-table thead th.th-actions { text-align: right; }
.modern-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background 0.15s; }
.modern-table tbody tr:hover { background: #f8f9ff; }
.modern-table td { padding: 0.9rem 1rem; vertical-align: middle; color: #374151; }
.th-center, .td-center { text-align: center !important; }
.th-actions, .td-actions { text-align: right !important; }
.th-narrow, .td-narrow { width: 50px; }
.modern-row-number { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; background: #f3f4f6; color: #6b7280; font-weight: 600; font-size: 0.8rem; }

/* Cell User */
.modern-cell-user { display: flex; align-items: center; gap: 0.75rem; }
.modern-avatar { width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0; }
.modern-avatar-placeholder { color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; }
.modern-cell-user-info { display: flex; flex-direction: column; }
.modern-cell-title { font-weight: 600; color: #1a1a2e; margin-bottom: 2px; }
.modern-cell-sub { font-size: 0.8rem; color: #9ca3af; }
.modern-cell-contact { display: inline-flex; align-items: center; gap: 0.4rem; color: #4b5563; font-size: 0.88rem; }
.modern-cell-muted { color: #d1d5db; font-size: 0.85rem; }
.modern-cell-emp-id { display: inline-block; padding: 2px 8px; border-radius: 5px; background: #eef2ff; color: #4361ee; font-size: 0.78rem; font-weight: 600; font-family: 'Courier New', monospace; letter-spacing: 0.5px; }

/* Action Buttons */
.modern-action-group { display: inline-flex; gap: 0.35rem; }
.modern-btn-icon { width: 34px; height: 34px; border-radius: 9px; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 0.82rem; text-decoration: none; }
.modern-btn-edit { background: #fefce8; color: #d97706; }
.modern-btn-edit:hover { background: #d97706; color: #fff; transform: translateY(-1px); }
.modern-btn-delete { background: #fef2f2; color: #dc2626; }
.modern-btn-delete:hover { background: #dc2626; color: #fff; transform: translateY(-1px); }

/* Buttons */
.btn-modern { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.btn-modern-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.3); }
.btn-modern-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.4); color: #fff; }
.btn-modern-outline { background: #fff; color: #4361ee; border: 1.5px solid #4361ee; }
.btn-modern-outline:hover { background: #4361ee; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.3); }
.btn-modern-ghost { background: transparent; color: #6b7280; padding: 0.65rem 1rem; }
.btn-modern-ghost:hover { background: #f3f4f6; color: #374151; }

/* Alert */
.modern-alert { display: flex; align-items: center; gap: 0.65rem; padding: 0.85rem 1.25rem; margin: 1rem 1.5rem; border-radius: 10px; font-size: 0.88rem; font-weight: 500; animation: fadeSlideIn 0.3s ease; }
.modern-alert-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.modern-alert-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.modern-alert-close { margin-left: auto; background: none; border: none; cursor: pointer; color: inherit; opacity: 0.6; transition: opacity 0.2s; }
.modern-alert-close:hover { opacity: 1; }

/* Empty State */
.modern-empty-state { text-align: center; padding: 4rem 2rem; }
.modern-empty-icon { width: 80px; height: 80px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; color: #d1d5db; margin-bottom: 1.25rem; }
.modern-empty-state h3 { font-size: 1.2rem; font-weight: 700; color: #1a1a2e; margin: 0 0 0.5rem; }
.modern-empty-state p { color: #9ca3af; font-size: 0.9rem; margin: 0 0 1.5rem; }

/* Pagination */
.modern-pagination-wrapper { padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: center; }

/* Responsive */
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-stats-row { grid-template-columns: 1fr 1fr; }
    .modern-card-header { flex-direction: column; align-items: stretch; }
    .modern-search-box input { width: 100%; }
    .modern-table { font-size: 0.82rem; }
    .modern-cell-user-info .modern-cell-sub { display: none; }
    .modern-filter-pills { gap: 0.3rem; }
    .modern-filter-pill { font-size: 0.7rem; padding: 0.3rem 0.6rem; }
}
@media (max-width: 480px) {
    .modern-stats-row { grid-template-columns: 1fr 1fr; gap: 0.5rem; }
    .modern-stat-card { padding: 0.85rem; }
    .modern-stat-icon { width: 38px; height: 38px; font-size: 1rem; }
    .modern-stat-value { font-size: 1.15rem; }
}
</style>
@endpush

@push('scripts')
<script>
function filterTable() {
    const input = document.getElementById('staffSearch');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('staffTable');
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
    // Also filter mobile card view
    const mobileCards = document.querySelectorAll('.mobile-card-item');
    mobileCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
