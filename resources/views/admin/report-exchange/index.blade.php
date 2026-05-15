@extends('layouts.admin')
@section('title', 'Report Exchange')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-1"><i class="fas fa-exchange-alt me-2"></i>Report Document Exchange</h4>
            <p class="text-muted mb-0">Exchange reports and documents between management levels</p>
        </div>
        <a href="{{ route('admin.report-exchange.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Report
        </a>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Total</div>
            <div class="fs-4 fw-bold text-dark">{{ $stats['total'] }}</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Drafts</div>
            <div class="fs-4 fw-bold text-secondary">{{ $stats['draft'] }}</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Submitted</div>
            <div class="fs-4 fw-bold text-info">{{ $stats['submitted'] }}</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Approved</div>
            <div class="fs-4 fw-bold text-success">{{ $stats['approved'] }}</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Rejected</div>
            <div class="fs-4 fw-bold text-danger">{{ $stats['rejected'] }}</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Unread</div>
            <div class="fs-4 fw-bold text-warning">{{ $stats['unread'] }}</div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ ($tab ?? 'all') === 'all' ? 'active' : '' }}" href="{{ route('admin.report-exchange.index', ['tab' => 'all']) }}">All</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($tab ?? '') === 'sent' ? 'active' : '' }}" href="{{ route('admin.report-exchange.index', ['tab' => 'sent']) }}">
            Sent @if($stats['total'] > 0)<span class="badge bg-primary ms-1">{{ \App\Models\ReportDocument::sentBy(auth()->id())->count() }}</span>@endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($tab ?? '') === 'received' ? 'active' : '' }}" href="{{ route('admin.report-exchange.index', ['tab' => 'received']) }}">
            Received @if($stats['unread'] > 0)<span class="badge bg-danger ms-1">{{ $stats['unread'] }}</span>@endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($tab ?? '') === 'draft' ? 'active' : '' }}" href="{{ route('admin.report-exchange.index', ['tab' => 'draft']) }}">Drafts</a>
    </li>
</ul>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.report-exchange.index') }}" class="row g-2 align-items-end">
            <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="report" {{ request('type') === 'report' ? 'selected' : '' }}>General Report</option>
                    <option value="memo" {{ request('type') === 'memo' ? 'selected' : '' }}>Memo</option>
                    <option value="proposal" {{ request('type') === 'proposal' ? 'selected' : '' }}>Proposal</option>
                    <option value="financial" {{ request('type') === 'financial' ? 'selected' : '' }}>Financial Report</option>
                    <option value="academic" {{ request('type') === 'academic' ? 'selected' : '' }}>Academic Report</option>
                    <option value="inspection" {{ request('type') === 'inspection' ? 'selected' : '' }}>Inspection Report</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Priority</label>
                <select name="priority" class="form-select form-select-sm">
                    <option value="">All Priority</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">From Branch</label>
                <select name="from_branch" class="form-select form-select-sm">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('from_branch') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">To Branch</label>
                <select name="to_branch" class="form-select form-select-sm">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('to_branch') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- Documents Table --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:35%">Title</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>From / To</th>
                    <th>Date</th>
                    <th style="width:100px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                <tr>
                    <td>
                        <a href="{{ route('admin.report-exchange.show', $doc->id) }}" class="fw-semibold text-dark text-decoration-none">
                            @if(!$doc->recipients()->where('user_id', auth()->id())->where('is_read', false)->exists() === false)
                                <span class="badge bg-danger me-1" style="font-size:8px;">NEW</span>
                            @endif
                            {{ $doc->title }}
                        </a>
                        @if($doc->file_name)
                        <br><small class="text-muted"><i class="fas fa-paperclip me-1"></i>{{ $doc->file_name }} ({{ $doc->fileSizeFormatted }})</small>
                        @endif
                    </td>
                    <td><span class="badge bg-light text-dark">{{ $doc->typeLabel }}</span></td>
                    <td>{!! $doc->priorityBadge !!}</td>
                    <td>{!! $doc->statusBadge !!}</td>
                    <td>
                        <small>{{ $doc->fromBranch->name ?? 'HQ' }} <i class="fas fa-arrow-right mx-1"></i> {{ $doc->toBranch->name ?? 'HQ' }}</small>
                    </td>
                    <td><small>{{ $doc->created_at->format('M d, Y') }}</small></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.report-exchange.show', $doc->id) }}" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                            @if($doc->created_by === auth()->id() && in_array($doc->status, ['draft', 'rejected']))
                            <a href="{{ route('admin.report-exchange.edit', $doc->id) }}" class="btn btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                            @endif
                            @if($doc->file_path)
                            <a href="{{ route('admin.report-exchange.download', $doc->id) }}" class="btn btn-outline-success" title="Download"><i class="fas fa-download"></i></a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No documents found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $documents->withQueryString()->links() }}
    </div>
</div>
@endsection
