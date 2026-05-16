@extends('layouts.admin')
@section('title', 'Capacity Building & Training')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">HR</a></li>
                    <li class="active">Capacity Building</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Capacity Building & Training</h1>
            <p class="modern-page-subtitle">Manage employee training programs, workshops, and professional development</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.trainings.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i>
                <span>New Training</span>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalTrainings }}</span>
                <span class="modern-stat-label">Total Programs</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold">
                <i class="fas fa-clock"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $plannedCount }}</span>
                <span class="modern-stat-label">Planned</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-purple">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $ongoingCount }}</span>
                <span class="modern-stat-label">Ongoing</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $completedCount }}</span>
                <span class="modern-stat-label">Completed</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-teal">
                <i class="fas fa-users"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $completedParticipants }}</span>
                <span class="modern-stat-label">Participants Trained</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-red">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ number_format($totalBudget, 0) }}</span>
                <span class="modern-stat-label">Total Budget</span>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:1.25rem">
        <div class="modern-card-body" style="padding:1rem 1.5rem">
            <form method="GET" action="{{ route('admin.trainings.index') }}" class="modern-filter-row">
                <div class="modern-filter-group">
                    <label class="modern-filter-label">Search</label>
                    <div class="modern-search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search trainings...">
                    </div>
                </div>
                <div class="modern-filter-group">
                    <label class="modern-filter-label">Status</label>
                    <select name="status" class="modern-select-sm">
                        <option value="">All Status</option>
                        <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Planned</option>
                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="modern-filter-group">
                    <label class="modern-filter-label">Type</label>
                    <select name="type" class="modern-select-sm">
                        <option value="">All Types</option>
                        <option value="workshop" {{ request('type') == 'workshop' ? 'selected' : '' }}>Workshop</option>
                        <option value="seminar" {{ request('type') == 'seminar' ? 'selected' : '' }}>Seminar</option>
                        <option value="online_course" {{ request('type') == 'online_course' ? 'selected' : '' }}>Online Course</option>
                        <option value="on_the_job" {{ request('type') == 'on_the_job' ? 'selected' : '' }}>On-the-Job</option>
                        <option value="certification" {{ request('type') == 'certification' ? 'selected' : '' }}>Certification</option>
                        <option value="conference" {{ request('type') == 'conference' ? 'selected' : '' }}>Conference</option>
                        <option value="mentorship" {{ request('type') == 'mentorship' ? 'selected' : '' }}>Mentorship</option>
                        <option value="induction" {{ request('type') == 'induction' ? 'selected' : '' }}>Induction</option>
                    </select>
                </div>
                <div class="modern-filter-group">
                    <label class="modern-filter-label">Category</label>
                    <select name="category" class="modern-select-sm">
                        <option value="">All Categories</option>
                        <option value="pedagogical" {{ request('category') == 'pedagogical' ? 'selected' : '' }}>Pedagogical</option>
                        <option value="administrative" {{ request('category') == 'administrative' ? 'selected' : '' }}>Administrative</option>
                        <option value="technical" {{ request('category') == 'technical' ? 'selected' : '' }}>Technical / ICT</option>
                        <option value="leadership" {{ request('category') == 'leadership' ? 'selected' : '' }}>Leadership</option>
                        <option value="safety" {{ request('category') == 'safety' ? 'selected' : '' }}>Safety & Compliance</option>
                        <option value="curriculum" {{ request('category') == 'curriculum' ? 'selected' : '' }}>Curriculum</option>
                        <option value="pastoral" {{ request('category') == 'pastoral' ? 'selected' : '' }}>Pastoral Care</option>
                        <option value="general" {{ request('category') == 'general' ? 'selected' : '' }}>General</option>
                    </select>
                </div>
                <div class="modern-filter-actions">
                    <button type="submit" class="btn-modern btn-modern-primary btn-sm">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.trainings.index') }}" class="btn-modern btn-modern-ghost btn-sm">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Trainings Table Card --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">All Training Programs</h2>
                <span class="modern-badge modern-badge-light">{{ $data->total() }} records</span>
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

            @if($data->count() > 0)
            <div class="modern-table-wrapper">
                <table class="modern-table" id="trainingTable">
                    <thead>
                        <tr>
                            <th class="th-narrow">#</th>
                            <th>Training Title</th>
                            <th>Type / Category</th>
                            <th>Duration</th>
                            <th class="th-center">Participants</th>
                            <th class="th-center">Cost</th>
                            <th class="th-center">Status</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td class="td-narrow">
                                <span class="modern-row-number">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</span>
                            </td>
                            <td>
                                <div class="modern-cell-title">{{ $item->title }}</div>
                                <div class="modern-cell-sub">
                                    @if($item->provider){{ $item->provider }}@endif
                                    @if($item->facilitator) &bull; Facilitator: {{ $item->facilitator }}@endif
                                </div>
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $item->type_label }}</div>
                                <div class="modern-cell-sub">{{ $item->category_label }}</div>
                            </td>
                            <td>
                                <div class="modern-cell-text">
                                    @if($item->start_date && $item->end_date)
                                        {{ $item->start_date->format('M d') }} — {{ $item->end_date->format('M d, Y') }}
                                    @else
                                        -
                                    @endif
                                </div>
                                <div class="modern-cell-sub">{{ $item->duration_hours }} hrs</div>
                            </td>
                            <td class="td-center">
                                <span class="modern-badge modern-badge-info">{{ $item->enrolled_count }}</span>
                                @if($item->max_participants > 0)
                                    <span class="modern-cell-muted">/ {{ $item->max_participants }}</span>
                                @endif
                            </td>
                            <td class="td-center">
                                @if($item->cost > 0)
                                    <span class="modern-cell-text">{{ number_format($item->cost, 0) }}</span>
                                @else
                                    <span class="modern-cell-muted">Free</span>
                                @endif
                            </td>
                            <td class="td-center">
                                @if($item->status === 'planned')
                                    <span class="modern-badge modern-badge-info"><i class="fas fa-clock"></i> Planned</span>
                                @elseif($item->status === 'ongoing')
                                    <span class="modern-badge modern-badge-warning"><i class="fas fa-spinner"></i> Ongoing</span>
                                @elseif($item->status === 'completed')
                                    <span class="modern-badge modern-badge-success"><i class="fas fa-check"></i> Completed</span>
                                @elseif($item->status === 'cancelled')
                                    <span class="modern-badge modern-badge-danger"><i class="fas fa-times"></i> Cancelled</span>
                                @endif
                            </td>
                            <td class="td-actions">
                                <div class="modern-action-group">
                                    <a href="{{ route('admin.trainings.show', $item->id) }}" class="modern-btn-icon modern-btn-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.trainings.edit', $item->id) }}" class="modern-btn-icon modern-btn-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.trainings.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this training?')">
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

            @if($data->hasPages())
            <div class="modern-pagination-wrapper">
                {{ $data->withQueryString()->links() }}
            </div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>No Training Programs</h3>
                <p>Get started by creating your first training or capacity building program.</p>
                <a href="{{ route('admin.trainings.create') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-plus"></i> New Training
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.modern-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.modern-page-header-left { flex: 1; }
.modern-page-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.modern-page-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }
.modern-page-header-right { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.modern-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.modern-stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem; margin-bottom: 1.75rem; }
.modern-stat-card { background: #fff; border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; transition: transform 0.2s, box-shadow 0.2s; }
.modern-stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.modern-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.modern-stat-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-stat-icon-green { background: #ecfdf5; color: #10b981; }
.modern-stat-icon-gold { background: #fff7ed; color: #f59e0b; }
.modern-stat-icon-red { background: #fef2f2; color: #ef4444; }
.modern-stat-icon-purple { background: #f5f3ff; color: #7c3aed; }
.modern-stat-icon-teal { background: #f0fdfa; color: #14b8a6; }
.modern-stat-info { display: flex; flex-direction: column; }
.modern-stat-value { font-size: 1.5rem; font-weight: 800; color: #1a1a2e; line-height: 1.2; }
.modern-stat-label { font-size: 0.8rem; color: #6c757d; font-weight: 500; }
.modern-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.5rem; }
.modern-card-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0; flex-wrap: wrap; gap: 1rem; }
.modern-card-header-left { display: flex; align-items: center; gap: 0.75rem; }
.modern-card-title { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-card-body { padding: 0; }
.modern-badge { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.65rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
.modern-badge-light { background: #f3f4f6; color: #6b7280; }
.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-warning { background: #fefce8; color: #b45309; }
.modern-badge-info { background: #eff6ff; color: #2563eb; }
.modern-alert { display: flex; align-items: center; gap: 0.65rem; padding: 0.85rem 1.25rem; margin: 1rem 1.5rem; border-radius: 10px; font-size: 0.88rem; font-weight: 500; }
.modern-alert-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.modern-alert-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.modern-alert-close { margin-left: auto; background: none; border: none; cursor: pointer; color: inherit; opacity: 0.6; }
.modern-alert-close:hover { opacity: 1; }
.modern-table-wrapper { overflow-x: auto; }
.modern-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.modern-table thead th { background: #f9fafb; padding: 0.85rem 1rem; text-align: left; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 2px solid #e5e7eb; white-space: nowrap; }
.th-center, .td-center { text-align: center !important; }
.th-actions, .td-actions { text-align: right !important; }
.th-narrow, .td-narrow { width: 50px; }
.modern-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background 0.15s; }
.modern-table tbody tr:hover { background: #f8f9ff; }
.modern-table td { padding: 0.9rem 1rem; vertical-align: middle; color: #374151; }
.modern-row-number { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; background: #f3f4f6; color: #6b7280; font-weight: 600; font-size: 0.8rem; }
.modern-cell-title { font-weight: 600; color: #1a1a2e; margin-bottom: 2px; }
.modern-cell-sub { font-size: 0.8rem; color: #6b7280; }
.modern-cell-text { color: #4b5563; }
.modern-cell-muted { color: #d1d5db; font-size: 0.8rem; }
.modern-action-group { display: inline-flex; gap: 0.35rem; }
.modern-btn-icon { width: 34px; height: 34px; border-radius: 9px; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 0.82rem; text-decoration: none; }
.modern-btn-view { background: #eff6ff; color: #2563eb; }
.modern-btn-view:hover { background: #2563eb; color: #fff; }
.modern-btn-edit { background: #fefce8; color: #d97706; }
.modern-btn-edit:hover { background: #d97706; color: #fff; }
.modern-btn-delete { background: #fef2f2; color: #dc2626; }
.modern-btn-delete:hover { background: #dc2626; color: #fff; }
.btn-modern { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.btn-modern-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3); }
.btn-modern-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4); color: #fff; }
.btn-modern-outline { background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb; }
.btn-modern-outline:hover { border-color: #4361ee; color: #4361ee; background: #f8f9ff; }
.btn-modern-ghost { background: transparent; color: #6b7280; padding: 0.65rem 1rem; }
.btn-modern-ghost:hover { color: #1a1a2e; background: #f3f4f6; }
.btn-sm { padding: 0.45rem 0.9rem; font-size: 0.82rem; }
.modern-empty-state { text-align: center; padding: 4rem 2rem; }
.modern-empty-icon { width: 80px; height: 80px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; color: #d1d5db; margin-bottom: 1.25rem; }
.modern-empty-state h3 { font-size: 1.2rem; font-weight: 700; color: #1a1a2e; margin: 0 0 0.5rem; }
.modern-empty-state p { color: #9ca3af; font-size: 0.9rem; margin: 0 0 1.5rem; }
.modern-pagination-wrapper { padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: center; }
/* Filter Row */
.modern-filter-row { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; }
.modern-filter-group { display: flex; flex-direction: column; gap: 0.3rem; }
.modern-filter-label { font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; }
.modern-select-sm { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 0.45rem 0.7rem; font-size: 0.85rem; color: #374151; background: #fff; }
.modern-filter-actions { display: flex; gap: 0.5rem; align-items: flex-end; }
.modern-search-box { position: relative; display: flex; align-items: center; }
.modern-search-box i { position: absolute; left: 12px; color: #adb5bd; font-size: 0.85rem; }
.modern-search-box input { border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.55rem 0.75rem 0.55rem 2.25rem; font-size: 0.875rem; width: 220px; background: #f9fafb; color: #374151; }
.modern-search-box input:focus { outline: none; border-color: #4361ee; background: #fff; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-page-title { font-size: 1.35rem; }
    .modern-stats-row { grid-template-columns: repeat(2, 1fr); }
    .modern-filter-row { flex-direction: column; }
    .modern-search-box input { width: 100%; }
}
</style>
@endpush
@endsection
