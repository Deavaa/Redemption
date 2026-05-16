@extends('layouts.admin')
@section('title', 'Staff & Users')
@push('styles')
<style>
.role-badge { padding: 3px 10px; border-radius: 12px; font-size: .72rem; font-weight: 600; display: inline-block; }
.role-badge-admin { background: #dc2626; color: white; }
.role-badge-teacher { background: #0d6efd; color: white; }
.role-badge-general_manager { background: #7c3aed; color: white; }
.role-badge-branch_principal { background: #0891b2; color: white; }
.role-badge-registrar { background: #059669; color: white; }
.role-badge-finance { background: #d97706; color: white; }
.role-badge-hr { background: #e11d48; color: white; }
.role-badge-cashier { background: #ea580c; color: white; }
.role-badge-librarian { background: #4f46e5; color: white; }
.role-badge-staff { background: #6b7280; color: white; }
.branch-tag { font-size: .7rem; background: #f0f4ff; color: #3b82f6; padding: 2px 8px; border-radius: 8px; font-weight: 500; }
.status-active { color: #16a34a; }
.status-inactive { color: #dc2626; }
</style>
@endpush
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Staff & Users</h4>
            <p class="text-muted mb-0">Manage all staff members and their roles</p>
        </div>
        <a href="{{ route('admin.staff.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Add User</a>
    </div>

    {{-- Role Filter --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="fw-semibold text-muted small">Filter:</span>
                <a href="{{ route('admin.staff.index') }}" class="btn btn-sm {{ request()->missing('role') ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
                @php
                    $filterRoles = [
                        'admin' => 'Admin', 'teacher' => 'Teacher', 'general_manager' => 'GM',
                        'branch_principal' => 'Branch Principal', 'registrar' => 'Registrar',
                        'finance' => 'Finance', 'hr' => 'HR', 'cashier' => 'Cashier',
                        'librarian' => 'Librarian', 'staff' => 'Staff'
                    ];
                @endphp
                @foreach($filterRoles as $val => $label)
                <a href="{{ route('admin.staff.index', ['role' => $val]) }}" class="btn btn-sm {{ request('role') === $val ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </div>

    @if($staff->count() > 0)
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staff as $s)
                        <tr>
                            <td>{{ $staff->firstItem() + $loop->index }}</td>
                            <td class="fw-semibold">
                                {{ $s->name }}
                                <div class="text-muted small">{{ $s->phone ?? '' }}</div>
                            </td>
                            <td>{{ $s->email }}</td>
                            <td><span class="role-badge role-badge-{{ $s->role }}">{{ $filterRoles[$s->role] ?? ucfirst(str_replace('_', ' ', $s->role)) }}</span></td>
                            <td>
                                @if($s->branch)
                                    <span class="branch-tag">{{ $s->branch->name }}</span>
                                @else
                                    <span class="text-muted small">All</span>
                                @endif
                            </td>
                            <td>
                                @if($s->is_active)
                                    <span class="status-active"><i class="fas fa-circle" style="font-size:.5rem"></i> Active</span>
                                @else
                                    <span class="status-inactive"><i class="fas fa-circle" style="font-size:.5rem"></i> Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.staff.edit', $s) }}" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                    @if($s->email !== auth()->user()->email)
                                    <form method="POST" action="{{ route('admin.staff.destroy', $s) }}" onsubmit="return confirm('Remove this staff member?')">@csrf @method('DELETE')
                                        <button class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($staff->hasPages())
        <div class="card-footer d-flex justify-content-center">{{ $staff->links() }}</div>
        @endif
    </div>
    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-people display-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No Staff Members Found</h5>
            <p class="text-muted">Add your first staff member to get started.</p>
            <a href="{{ route('admin.staff.create') }}" class="btn btn-primary mt-2"><i class="bi bi-person-plus me-1"></i> Add First User</a>
        </div>
    </div>
    @endif
</div>
@endsection
