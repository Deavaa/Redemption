@extends('layouts.admin')
@section('title', 'Exam Questions')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Academics</a></li>
                    <li class="active">Exam Questions</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            @php
                $canCreate = in_array(Auth::user()->role, ['admin', 'super_admin', 'teacher', 'department_head']);
            @endphp
            @if($canCreate)
            <a href="{{ route('admin.exam-questions.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i>
                <span>Submit Exam Question</span>
            </a>
            @endif
        </div>
    </div>

    {{-- Stats Cards --}}
    @php
        $user = Auth::user();
        $draftCount = $data->where('status', 'draft')->count();
        $pendingDeptCount = $data->where('status', 'submitted')->count();
        $pendingPrincipalCount = $data->where('status', 'dept_approved')->count();
        $approvedCount = $data->where('status', 'principal_approved')->count();
        $rejectedCount = $data->whereIn('status', ['dept_rejected', 'principal_rejected'])->count();
    @endphp

    <div class="modern-stats-row">
        @if(in_array($user->role, ['teacher', 'admin', 'super_admin']))
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gray">
                <i class="fas fa-edit"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $draftCount }}</span>
                <span class="modern-stat-label">Drafts</span>
            </div>
        </div>
        @endif
        @if(in_array($user->role, ['department_head', 'admin', 'super_admin']))
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $pendingDeptCount }}</span>
                <span class="modern-stat-label">Pending Dept. Review</span>
            </div>
        </div>
        @endif
        @if(in_array($user->role, ['branch_principal', 'admin', 'super_admin']))
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-arrow-right"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $pendingPrincipalCount }}</span>
                <span class="modern-stat-label">Pending Principal</span>
            </div>
        </div>
        @endif
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $approvedCount }}</span>
                <span class="modern-stat-label">Approved</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-red">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $rejectedCount }}</span>
                <span class="modern-stat-label">Rejected</span>
            </div>
        </div>
    </div>

    {{-- Workflow Banner --}}
    <div class="modern-info-banner">
        <i class="fas fa-route"></i>
        <span>Approval Workflow: <strong>Teacher</strong> submits → <strong>Department Head</strong> reviews → <strong>Principal</strong> gives final approval</span>
    </div>

    {{-- Questions Table Card --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">Exam Questions</h2>
                <span class="modern-badge modern-badge-light">{{ $data->total() }} records</span>
            </div>
            <div class="modern-card-header-right" style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
                <div class="modern-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="eqSearch" placeholder="Search questions..." onkeyup="filterTable()">
                </div>
                <form method="GET" action="{{ route('admin.exam-questions.index') }}" style="display:flex;gap:0.5rem;align-items:center;">
                    <select name="status" class="modern-filter-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="subject_id" class="modern-filter-select" onchange="this.form.submit()">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </form>
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
                <div class="modern-alert modern-alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if($data->count() > 0)
            <div class="modern-table-wrapper">
                <table class="modern-table" id="eqTable">
                    <thead>
                        <tr>
                            <th class="th-narrow">#</th>
                            <th>Title</th>
                            @if($user->role !== 'teacher')
                            <th>Teacher</th>
                            @endif
                            <th>Subject</th>
                            <th>Type</th>
                            <th class="th-center">Marks</th>
                            <th class="th-center">Status</th>
                            <th>Submitted</th>
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
                                <div class="modern-cell-title">{{ $item->title }}</div>
                                @if($item->classRoom)
                                <div class="modern-cell-sub">{{ $item->classRoom->name }}</div>
                                @endif
                            </td>
                            @if($user->role !== 'teacher')
                            <td>
                                <div class="modern-cell-text">{{ $item->teacher->full_name ?? '-' }}</div>
                            </td>
                            @endif
                            <td>
                                <span class="modern-badge modern-badge-light">{{ $item->subject->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="modern-badge modern-badge-light">{{ $item->question_type_label }}</span>
                            </td>
                            <td class="td-center">
                                <span class="modern-cell-marks">{{ $item->total_marks }}</span>
                            </td>
                            <td class="td-center">
                                <span class="modern-badge {{ $item->status_badge }}">
                                    <i class="{{ $item->status_icon }}"></i> {{ $item->status_label }}
                                </span>
                            </td>
                            <td>
                                @if($item->submitted_at)
                                    <div class="modern-cell-date">{{ $item->submitted_at->format('M d, Y') }}</div>
                                @else
                                    <span class="modern-cell-muted">Not submitted</span>
                                @endif
                            </td>
                            <td class="td-actions">
                                <div class="modern-action-group">
                                    <a href="{{ route('admin.exam-questions.show', $item->id) }}" class="modern-btn-icon modern-btn-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($item->canBeEdited() && ($user->role === 'teacher' || in_array($user->role, ['admin', 'super_admin'])))
                                    <a href="{{ route('admin.exam-questions.edit', $item->id) }}" class="modern-btn-icon modern-btn-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @endif
                                    @if($item->canBeSubmitted() && ($user->role === 'teacher' || in_array($user->role, ['admin', 'super_admin'])))
                                    <form method="POST" action="{{ route('admin.exam-questions.submit', $item->id) }}" style="display:inline" onsubmit="return confirm('Submit this question for department head review?')">
                                        @csrf
                                        <button type="submit" class="modern-btn-icon modern-btn-submit" title="Submit for Review" style="background:#eef2ff;color:#4361ee;">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @if(in_array($user->role, ['admin', 'super_admin', 'teacher']))
                                    <form method="POST" action="{{ route('admin.exam-questions.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Delete this exam question?')">
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

            {{-- Pagination --}}
            @if($data->hasPages())
            <div class="modern-pagination-wrapper">
                {{ $data->withQueryString()->links() }}
            </div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <h3>No Exam Questions</h3>
                <p>@if($canCreate)Submit your first exam question for review.@else No exam questions found matching your filters.@endif</p>
                @if($canCreate)
                <a href="{{ route('admin.exam-questions.create') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-plus"></i> Submit Exam Question
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.modern-page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem; }
.modern-page-header-left { flex:1; }
.modern-breadcrumb ol { display:flex; list-style:none; padding:0; margin:0 0 .5rem; gap:.5rem; font-size:.8rem; align-items:center; }
.modern-breadcrumb li { color:#adb5bd; }
.modern-breadcrumb li a { color:#6c757d; text-decoration:none; transition:color .2s; }
.modern-breadcrumb li a:hover { color:#4361ee; }
.modern-breadcrumb li+li::before { content:'/'; margin-right:.5rem; color:#dee2e6; }
.modern-breadcrumb li.active { color:#4361ee; font-weight:500; }
.modern-info-banner { display:flex; align-items:center; gap:.65rem; padding:.85rem 1.25rem; background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; margin-bottom:1.75rem; font-size:.88rem; color:#1e40af; }
.modern-info-banner i { color:#3b82f6; font-size:1rem; }
.modern-info-banner strong { color:#1e3a8a; }
.modern-stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem; margin-bottom:1.75rem; }
.modern-stat-card { background:#fff; border-radius:14px; padding:1.25rem; display:flex; align-items:center; gap:1rem; box-shadow:0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04); border:1px solid #f0f0f0; transition:transform .2s,box-shadow .2s; }
.modern-stat-card:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,.08); }
.modern-stat-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
.modern-stat-icon-blue { background:#eef2ff; color:#4361ee; }
.modern-stat-icon-green { background:#ecfdf5; color:#10b981; }
.modern-stat-icon-gold { background:#fefce8; color:#d97706; }
.modern-stat-icon-gray { background:#f3f4f6; color:#6b7280; }
.modern-stat-icon-red { background:#fef2f2; color:#dc2626; }
.modern-stat-info { display:flex; flex-direction:column; }
.modern-stat-value { font-size:1.5rem; font-weight:800; color:#1a1a2e; line-height:1.2; }
.modern-stat-label { font-size:.8rem; color:#6c757d; font-weight:500; }
.modern-card { background:#fff; border-radius:14px; box-shadow:0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04); border:1px solid #f0f0f0; overflow:hidden; margin-bottom:1.5rem; }
.modern-card-header { display:flex; justify-content:space-between; align-items:center; padding:1.25rem 1.5rem; border-bottom:1px solid #f0f0f0; flex-wrap:wrap; gap:1rem; }
.modern-card-header-left { display:flex; align-items:center; gap:.75rem; }
.modern-card-title { font-size:1.1rem; font-weight:700; color:#1a1a2e; margin:0; }
.modern-card-body { padding:0; }
.modern-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.25rem .65rem; border-radius:50px; font-size:.75rem; font-weight:600; letter-spacing:.3px; }
.modern-badge-light { background:#f3f4f6; color:#6b7280; }
.modern-badge-success { background:#ecfdf5; color:#059669; }
.modern-badge-danger { background:#fef2f2; color:#dc2626; }
.modern-badge-gold { background:#fefce8; color:#b45309; }
.modern-badge-warning { background:#fffbeb; color:#d97706; }
.modern-badge-info { background:#eff6ff; color:#2563eb; }
.modern-search-box { position:relative; display:flex; align-items:center; }
.modern-search-box i { position:absolute; left:12px; color:#adb5bd; font-size:.85rem; }
.modern-search-box input { border:1.5px solid #e5e7eb; border-radius:10px; padding:.55rem .75rem .55rem 2.25rem; font-size:.875rem; width:220px; transition:all .2s; background:#f9fafb; color:#374151; }
.modern-search-box input:focus { outline:none; border-color:#4361ee; background:#fff; box-shadow:0 0 0 3px rgba(67,97,238,.1); }
.modern-filter-select { border:1.5px solid #e5e7eb; border-radius:10px; padding:.5rem .75rem; font-size:.85rem; background:#f9fafb; color:#374151; appearance:none; cursor:pointer; background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position:right .5rem center; background-repeat:no-repeat; background-size:1.1rem; padding-right:2rem; }
.modern-filter-select:focus { outline:none; border-color:#4361ee; }
.modern-table-wrapper { overflow-x:auto; }
.modern-table { width:100%; border-collapse:collapse; font-size:.9rem; }
.modern-table thead th { background:#f9fafb; padding:.85rem 1rem; text-align:left; font-weight:600; font-size:.78rem; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; border-bottom:2px solid #e5e7eb; white-space:nowrap; }
.modern-table tbody tr { border-bottom:1px solid #f3f4f6; transition:background .15s; }
.modern-table tbody tr:hover { background:#f8f9ff; }
.modern-table td { padding:.9rem 1rem; vertical-align:middle; color:#374151; }
.th-center,.td-center { text-align:center!important; }
.th-actions,.td-actions { text-align:right!important; }
.th-narrow,.td-narrow { width:50px; }
.modern-row-number { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:8px; background:#f3f4f6; color:#6b7280; font-weight:600; font-size:.8rem; }
.modern-cell-title { font-weight:600; color:#1a1a2e; margin-bottom:2px; }
.modern-cell-sub { font-size:.8rem; color:#9ca3af; }
.modern-cell-text { color:#4b5563; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.modern-cell-marks { font-weight:700; color:#1a1a2e; font-size:.95rem; }
.modern-cell-date { font-weight:500; color:#374151; font-size:.88rem; }
.modern-cell-muted { color:#d1d5db; }
.modern-action-group { display:inline-flex; gap:.35rem; }
.modern-btn-icon { width:34px; height:34px; border-radius:9px; border:none; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:all .2s; font-size:.82rem; text-decoration:none; }
.modern-btn-view { background:#eef2ff; color:#4361ee; }
.modern-btn-view:hover { background:#4361ee; color:#fff; transform:translateY(-1px); }
.modern-btn-edit { background:#fefce8; color:#d97706; }
.modern-btn-edit:hover { background:#d97706; color:#fff; transform:translateY(-1px); }
.modern-btn-delete { background:#fef2f2; color:#dc2626; }
.modern-btn-delete:hover { background:#dc2626; color:#fff; transform:translateY(-1px); }
.modern-alert { display:flex; align-items:center; gap:.65rem; padding:.85rem 1.25rem; margin:1rem 1.5rem; border-radius:10px; font-size:.88rem; font-weight:500; animation:fadeSlideIn .3s ease; }
.modern-alert-success { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }
.modern-alert-error { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
.modern-alert-close { margin-left:auto; background:none; border:none; cursor:pointer; color:inherit; opacity:.6; transition:opacity .2s; }
.modern-alert-close:hover { opacity:1; }
.modern-empty-state { text-align:center; padding:4rem 2rem; }
.modern-empty-icon { width:80px; height:80px; border-radius:50%; background:#f3f4f6; display:inline-flex; align-items:center; justify-content:center; font-size:2rem; color:#d1d5db; margin-bottom:1.25rem; }
.modern-empty-state h3 { font-size:1.2rem; font-weight:700; color:#1a1a2e; margin:0 0 .5rem; }
.modern-empty-state p { color:#9ca3af; font-size:.9rem; margin:0 0 1.5rem; }
.modern-pagination-wrapper { padding:1rem 1.5rem; border-top:1px solid #f0f0f0; display:flex; justify-content:center; }
.btn-modern { display:inline-flex; align-items:center; gap:.5rem; padding:.65rem 1.35rem; border-radius:10px; font-weight:600; font-size:.9rem; text-decoration:none; border:none; cursor:pointer; transition:all .25s; }
.btn-modern-primary { background:linear-gradient(135deg,#4361ee,#3a0ca3); color:#fff; box-shadow:0 2px 8px rgba(67,97,238,.3); }
.btn-modern-primary:hover { transform:translateY(-2px); box-shadow:0 4px 16px rgba(67,97,238,.4); color:#fff; }
@media(max-width:768px) {
    .modern-page-header { flex-direction:column; align-items:stretch; }
    .modern-stats-row { grid-template-columns:1fr 1fr; }
    .modern-card-header { flex-direction:column; align-items:stretch; }
    .modern-search-box input { width:100%; }
    .modern-table { font-size:.82rem; }
    .modern-cell-text { max-width:120px; }
}
</style>
@endpush

@push('scripts')
<script>
function filterTable() {
    const input = document.getElementById('eqSearch');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('eqTable');
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
