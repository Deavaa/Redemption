@extends('layouts.admin')
@section('title', 'Report Document Details')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1"><i class="fas fa-file-alt me-2"></i>{{ $report_exchange->title }}</h4>
            <p class="text-muted mb-0">Report Document Details & Review</p>
        </div>
        <a href="{{ route('admin.report-exchange.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        {{-- Document Info --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">{{ $report_exchange->title }}</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-light text-dark">{{ $report_exchange->typeLabel }}</span>
                            {!! $report_exchange->priorityBadge !!}
                            {!! $report_exchange->statusBadge !!}
                        </div>
                    </div>
                    @if($report_exchange->file_path)
                    <a href="{{ route('admin.report-exchange.download', $report_exchange->id) }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-download me-1"></i> {{ $report_exchange->file_name }}
                    </a>
                    @endif
                </div>
                @if($report_exchange->description)
                <div class="mb-3">
                    <label class="text-muted small fw-semibold">Description</label>
                    <p class="mb-0">{{ $report_exchange->description }}</p>
                </div>
                @endif
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="text-muted small">From</label>
                        <div>{{ $report_exchange->fromBranch->name ?? 'Headquarters' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">To</label>
                        <div>{{ $report_exchange->toBranch->name ?? 'Headquarters' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Created By</label>
                        <div>{{ $report_exchange->creator->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Academic Year</label>
                        <div>{{ $report_exchange->academicYear->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Term</label>
                        <div>{{ $report_exchange->term->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Created</label>
                        <div>{{ $report_exchange->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    @if($report_exchange->file_name)
                    <div class="col-md-6">
                        <label class="text-muted small">Attached File</label>
                        <div><i class="fas fa-paperclip me-1"></i>{{ $report_exchange->file_name }} ({{ $report_exchange->fileSizeFormatted }})</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recipients --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-users me-2"></i>Recipients</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Name</th><th>Email</th><th>Status</th><th>Read At</th></tr>
                    </thead>
                    <tbody>
                        @foreach($report_exchange->recipients as $recipient)
                        <tr>
                            <td>{{ $recipient->user->name }}</td>
                            <td>{{ $recipient->user->email }}</td>
                            <td>
                                @if($recipient->is_read)
                                    <span class="badge bg-success">Read</span>
                                @else
                                    <span class="badge bg-warning text-dark">Unread</span>
                                @endif
                            </td>
                            <td>{{ $recipient->read_at?->format('M d, Y H:i') ?? '-' }}</td>
                        </tr>
                        @endforeach
                        @if($report_exchange->recipients->isEmpty())
                        <tr><td colspan="4" class="text-center text-muted py-3">No recipients (draft)</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Comments / Review History --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-comments me-2"></i>Review Comments & Actions</h6></div>
            <div class="card-body">
                @foreach($report_exchange->comments as $comment)
                <div class="d-flex gap-3 mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:40px;height:40px;font-size:14px;font-weight:700;">
                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $comment->user->name }}</strong>
                                {!! $comment->actionBadge !!}
                            </div>
                            <small class="text-muted">{{ $comment->created_at->format('M d, Y H:i') }}</small>
                        </div>
                        <p class="mb-0 mt-1">{{ $comment->comment }}</p>
                    </div>
                </div>
                @endforeach

                @if($report_exchange->comments->isEmpty())
                <p class="text-muted text-center py-3">No comments yet</p>
                @endif

                {{-- Add Comment Form --}}
                @if($report_exchange->status !== 'archived')
                <hr>
                <form action="{{ route('admin.report-exchange.comment', $report_exchange->id) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Add Comment / Take Action</label>
                        <textarea name="comment" class="form-control" rows="3" required placeholder="Enter your comment or review notes..."></textarea>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="comment" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-comment me-1"></i> Comment
                        </button>
                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success"
                            onclick="return confirm('Approve this document?')">
                            <i class="fas fa-check me-1"></i> Approve
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger"
                            onclick="return confirm('Reject this document?')">
                            <i class="fas fa-times me-1"></i> Reject
                        </button>
                        <button type="submit" name="action" value="request_revision" class="btn btn-sm btn-warning"
                            onclick="return confirm('Request revision for this document?')">
                            <i class="fas fa-edit me-1"></i> Request Revision
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Action Panel --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-cog me-2"></i>Actions</h6></div>
            <div class="card-body">
                @if($report_exchange->created_by === auth()->id() && in_array($report_exchange->status, ['draft', 'rejected']))
                <a href="{{ route('admin.report-exchange.edit', $report_exchange->id) }}" class="btn btn-outline-primary w-100 mb-2">
                    <i class="fas fa-edit me-1"></i> Edit Document
                </a>
                @endif
                @if($report_exchange->file_path)
                <a href="{{ route('admin.report-exchange.download', $report_exchange->id) }}" class="btn btn-outline-success w-100 mb-2">
                    <i class="fas fa-download me-1"></i> Download File
                </a>
                @endif
                @if($report_exchange->created_by === auth()->id())
                <form action="{{ route('admin.report-exchange.destroy', $report_exchange->id) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Document Timeline --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-history me-2"></i>Status Timeline</h6></div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0 py-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold small">Created</span>
                            <small class="text-muted">{{ $report_exchange->created_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @foreach($report_exchange->comments as $comment)
                    <div class="list-group-item border-0 px-0 py-2">
                        <div class="d-flex justify-content-between">
                            <span class="small">{{ ucfirst($comment->action) }} by {{ $comment->user->name }}</span>
                            <small class="text-muted">{{ $comment->created_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
