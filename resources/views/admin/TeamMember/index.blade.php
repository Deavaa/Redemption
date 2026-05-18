@extends('layouts.admin')
@section('title', 'Team Members')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">People</a></li>
                    <li class="active">Team Members</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.team-members.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i>
                <span>Add Member</span>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-users"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalMembers }}</span>
                <span class="modern-stat-label">Total Members</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $data->where('is_active', 1)->count() }}</span>
                <span class="modern-stat-label">Active</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gray">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $data->where('is_active', 0)->count() }}</span>
                <span class="modern-stat-label">Inactive</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-purple">
                <i class="fas fa-building"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $data->pluck('department')->filter()->unique()->count() }}</span>
                <span class="modern-stat-label">Departments</span>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">All Members</h2>
                <span class="modern-badge modern-badge-light">{{ $totalMembers }} records</span>
            </div>
            <div class="modern-card-header-right">
                <div class="modern-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="memberSearch" placeholder="Search members..." onkeyup="filterTable()">
                </div>
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

            @if($data->count() > 0)
            <div class="modern-table-wrapper">
                <table class="modern-table" id="memberTable">
                    <thead>
                        <tr>
                            <th class="th-narrow">#</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Department</th>
                            <th>Contact</th>
                            <th class="th-center">Status</th>
                            <th class="th-center">Order</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr class="modern-table-row">
                            <td class="td-narrow">
                                <span class="modern-row-number">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</span>
                            </td>
                            <td>
                                <div class="modern-cell-title">
                                    @if($item->photo)
                                        <img src="{{ asset($item->photo) }}" alt="{{ $item->name }}" class="modern-avatar-sm" style="vertical-align:middle;margin-right:6px;border-radius:8px;object-fit:cover;">
                                    @else
                                        <span class="modern-avatar-placeholder" style="width:32px;height:32px;font-size:0.75rem;display:inline-flex;vertical-align:middle;margin-right:6px;">{{ strtoupper(substr($item->name ?? '?', 0, 1)) }}</span>
                                    @endif
                                    {{ $item->name ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $item->designation ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $item->department ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="modern-cell-text">
                                    @if($item->email)
                                        <i class="fas fa-envelope" style="font-size:0.7rem;color:#4361ee;margin-right:3px;"></i> {{ $item->email }}
                                    @endif
                                    @if($item->phone)
                                        <br><i class="fas fa-phone" style="font-size:0.7rem;color:#10b981;margin-right:3px;"></i> {{ $item->phone }}
                                    @endif
                                </div>
                            </td>
                            <td class="td-center">
                                @if($item->is_active)
                                    <span class="modern-badge modern-badge-success">Active</span>
                                @else
                                    <span class="modern-badge modern-badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="td-center">
                                <span class="modern-badge modern-badge-light">{{ $item->sort_order ?? 0 }}</span>
                            </td>
                            <td class="td-actions">
                                <div class="modern-action-group">
                                    <a href="{{ route('admin.team-members.show', $item->id) }}" class="modern-btn-icon modern-btn-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.team-members.edit', $item->id) }}" class="modern-btn-icon modern-btn-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.team-members.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this team member?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="modern-btn-icon modern-btn-delete" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($data->hasPages())
            <div class="modern-pagination-wrapper">
                {{ $data->withQueryString()->links() }}
            </div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>No Team Members Yet</h3>
                <p>Add your first team member to get started.</p>
                <a href="{{ route('admin.team-members.create') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-plus"></i> Add Member
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterTable() {
    const input = document.getElementById('memberSearch');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('memberTable');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>
@endpush
@endsection