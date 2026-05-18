@extends('layouts.admin')
@section('title', $training->title)

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">HR</a></li>
                    <li><a href="{{ route('admin.trainings.index') }}">Capacity Building</a></li>
                    <li class="active">{{ Str::limit($training->title, 30) }}</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.trainings.edit', $training->id) }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-pen"></i> Edit
            </a>
            <a href="{{ route('admin.trainings.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    </div>
    @endif
    @if(session('error'))
    <div class="modern-alert modern-alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
        <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    </div>
    @endif

    {{-- Overview Cards --}}
    <div class="detail-stats-row">
        <div class="detail-stat-card">
            <div class="detail-stat-icon detail-stat-icon-blue"><i class="fas fa-calendar-alt"></i></div>
            <div class="detail-stat-info">
                <span class="detail-stat-label">Schedule</span>
                <span class="detail-stat-value">
                    @if($training->start_date && $training->end_date)
                        {{ $training->start_date->format('M d') }} — {{ $training->end_date->format('M d, Y') }}
                    @else
                        Not set
                    @endif
                </span>
            </div>
        </div>
        <div class="detail-stat-card">
            <div class="detail-stat-icon detail-stat-icon-green"><i class="fas fa-users"></i></div>
            <div class="detail-stat-info">
                <span class="detail-stat-label">Participants</span>
                <span class="detail-stat-value">{{ $training->enrolled_count }} @if($training->max_participants > 0)/ {{ $training->max_participants }}@endif</span>
            </div>
        </div>
        <div class="detail-stat-card">
            <div class="detail-stat-icon detail-stat-icon-gold"><i class="fas fa-hourglass-half"></i></div>
            <div class="detail-stat-info">
                <span class="detail-stat-label">Duration</span>
                <span class="detail-stat-value">{{ $training->duration_hours }} hours</span>
            </div>
        </div>
        <div class="detail-stat-card">
            <div class="detail-stat-icon detail-stat-icon-purple"><i class="fas fa-money-bill-wave"></i></div>
            <div class="detail-stat-info">
                <span class="detail-stat-label">Cost</span>
                <span class="detail-stat-value">@if($training->cost > 0){{ number_format($training->cost, 0) }}@else Free@endif</span>
            </div>
        </div>
    </div>

    {{-- Training Details --}}
    <div class="detail-grid">
        <div class="modern-card">
            <div class="modern-card-header">
                <h2 class="modern-card-title"><i class="fas fa-info-circle me-2"></i>Program Information</h2>
                @if($training->status === 'planned')
                    <span class="modern-badge modern-badge-info"><i class="fas fa-clock"></i> Planned</span>
                @elseif($training->status === 'ongoing')
                    <span class="modern-badge modern-badge-warning"><i class="fas fa-spinner"></i> Ongoing</span>
                @elseif($training->status === 'completed')
                    <span class="modern-badge modern-badge-success"><i class="fas fa-check"></i> Completed</span>
                @elseif($training->status === 'cancelled')
                    <span class="modern-badge modern-badge-danger"><i class="fas fa-times"></i> Cancelled</span>
                @endif
            </div>
            <div class="detail-info-body">
                <div class="detail-info-row">
                    <span class="detail-info-label">Type</span>
                    <span class="detail-info-value">{{ $training->type_label }}</span>
                </div>
                <div class="detail-info-row">
                    <span class="detail-info-label">Category</span>
                    <span class="detail-info-value">{{ $training->category_label }}</span>
                </div>
                <div class="detail-info-row">
                    <span class="detail-info-label">Target Audience</span>
                    <span class="detail-info-value">{{ ucfirst($training->target_audience) }}</span>
                </div>
                @if($training->provider)
                <div class="detail-info-row">
                    <span class="detail-info-label">Provider</span>
                    <span class="detail-info-value">{{ $training->provider }}</span>
                </div>
                @endif
                @if($training->facilitator)
                <div class="detail-info-row">
                    <span class="detail-info-label">Facilitator</span>
                    <span class="detail-info-value">{{ $training->facilitator }}</span>
                </div>
                @endif
                @if($training->venue)
                <div class="detail-info-row">
                    <span class="detail-info-label">Venue</span>
                    <span class="detail-info-value">{{ $training->venue }}</span>
                </div>
                @endif
                @if($training->budget_source)
                <div class="detail-info-row">
                    <span class="detail-info-label">Budget Source</span>
                    <span class="detail-info-value">{{ $training->budget_source }}</span>
                </div>
                @endif
                @if($training->objectives)
                <div class="detail-info-row">
                    <span class="detail-info-label">Objectives</span>
                    <span class="detail-info-value">{{ $training->objectives }}</span>
                </div>
                @endif
                @if($training->outcomes)
                <div class="detail-info-row">
                    <span class="detail-info-label">Expected Outcomes</span>
                    <span class="detail-info-value">{{ $training->outcomes }}</span>
                </div>
                @endif
                @if($training->description)
                <div class="detail-info-row">
                    <span class="detail-info-label">Description</span>
                    <span class="detail-info-value">{{ $training->description }}</span>
                </div>
                @endif
                @if($training->notes)
                <div class="detail-info-row">
                    <span class="detail-info-label">Notes</span>
                    <span class="detail-info-value">{{ $training->notes }}</span>
                </div>
                @endif
                <div class="detail-info-row">
                    <span class="detail-info-label">Created By</span>
                    <span class="detail-info-value">{{ $training->creator?->name ?? 'System' }}</span>
                </div>
            </div>
        </div>

        {{-- Participants Section --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h2 class="modern-card-title"><i class="fas fa-user-graduate me-2"></i>Participants ({{ $training->enrolled_count }})</h2>
                <button type="button" class="btn-modern btn-modern-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addParticipantModal">
                    <i class="fas fa-user-plus"></i> Add
                </button>
            </div>
            <div class="modern-card-body">
                @if($training->participants->count() > 0)
                <div class="modern-table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                <th class="th-center">Status</th>
                                <th class="th-center">Score</th>
                                <th class="th-center">Certificate</th>
                                <th class="th-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($training->participants as $p)
                            <tr>
                                <td><span class="modern-row-number">{{ $loop->iteration }}</span></td>
                                <td>
                                    <div class="modern-cell-title">{{ $p->employee?->name ?? 'Unknown' }}</div>
                                    <div class="modern-cell-sub">{{ $p->employee?->role ?? '' }}</div>
                                </td>
                                <td class="td-center">{!! $p->status_badge !!}</td>
                                <td class="td-center">
                                    @if($p->score)
                                        <span class="modern-badge modern-badge-info">{{ $p->score }}</span>
                                    @else
                                        <span class="modern-cell-muted">-</span>
                                    @endif
                                </td>
                                <td class="td-center">
                                    @if($p->certificate_issued)
                                        <span class="modern-badge modern-badge-success"><i class="fas fa-certificate"></i> Issued</span>
                                    @else
                                        <span class="modern-cell-muted">-</span>
                                    @endif
                                </td>
                                <td class="td-actions">
                                    <div class="modern-action-group">
                                        <button type="button" class="modern-btn-icon modern-btn-edit" title="Update Status"
                                            onclick="openUpdateModal({{ $p->id }}, '{{ $p->status }}', '{{ $p->score }}', '{{ $p->grade }}', '{{ $p->certificate_number }}', {{ $p->certificate_issued ? 1 : 0 }}, '{{ addslashes($p->feedback ?? '') }}', '{{ addslashes($p->remarks ?? '') }}')">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.trainings.participants.remove', [$training->id, $p->id]) }}" style="display:inline" onsubmit="return confirm('Remove this participant?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="modern-btn-icon modern-btn-delete" title="Remove">
                                                <i class="fas fa-user-minus"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="modern-empty-state" style="padding:2.5rem">
                    <div class="modern-empty-icon" style="width:60px;height:60px;font-size:1.5rem"><i class="fas fa-user-graduate"></i></div>
                    <h3>No Participants</h3>
                    <p>Add employees to this training program.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Add Participant Modal --}}
<div class="modal fade" id="addParticipantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Participant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.trainings.participants.add', $training->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Choose employee...</option>
                            @foreach($availableEmployees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ ucfirst($emp->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Initial Status</label>
                        <select name="status" class="form-select">
                            <option value="invited">Invited</option>
                            <option value="enrolled">Enrolled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Participant</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Bulk Participants Modal --}}
<div class="modal fade" id="addBulkModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Multiple Participants</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.trainings.participants.add-bulk', $training->id) }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">Select employees to add as participants:</p>
                    <div style="max-height:400px;overflow-y:auto">
                        @foreach($availableEmployees as $emp)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" id="emp_{{ $emp->id }}">
                            <label class="form-check-label" for="emp_{{ $emp->id }}">{{ $emp->name }} <span class="text-muted">({{ ucfirst($emp->role) }})</span></label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-users me-1"></i> Add Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Update Participant Modal --}}
<div class="modal fade" id="updateParticipantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Participant Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateParticipantForm" method="POST" action="">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select" id="upd_status">
                            <option value="invited">Invited</option>
                            <option value="enrolled">Enrolled</option>
                            <option value="attended">Attended</option>
                            <option value="completed">Completed</option>
                            <option value="absent">Absent</option>
                            <option value="dropped">Dropped</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Completion Date</label>
                        <input type="date" name="completion_date" class="form-control" id="upd_completion_date">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Score</label>
                            <input type="number" name="score" class="form-control" id="upd_score" min="0" max="100" step="0.01">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Grade</label>
                            <input type="text" name="grade" class="form-control" id="upd_grade" maxlength="10">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Certificate Number</label>
                        <input type="text" name="certificate_number" class="form-control" id="upd_cert_number">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="certificate_issued" value="1" id="upd_cert_issued">
                            <label class="form-check-label" for="upd_cert_issued">Certificate Issued</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Feedback</label>
                        <textarea name="feedback" class="form-control" id="upd_feedback" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Remarks</label>
                        <textarea name="remarks" class="form-control" id="upd_remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openUpdateModal(id, status, score, grade, certNumber, certIssued, feedback, remarks) {
    document.getElementById('updateParticipantForm').action =
        '{{ route('admin.trainings.participants.update', [$training->id, '__ID__']) }}'.replace('__ID__', id);
    document.getElementById('upd_status').value = status;
    document.getElementById('upd_score').value = score || '';
    document.getElementById('upd_grade').value = grade || '';
    document.getElementById('upd_cert_number').value = certNumber || '';
    document.getElementById('upd_cert_issued').checked = certIssued == 1;
    document.getElementById('upd_completion_date').value = '';
    document.getElementById('upd_feedback').value = feedback || '';
    document.getElementById('upd_remarks').value = remarks || '';
    new bootstrap.Modal(document.getElementById('updateParticipantModal')).show();
}
</script>
@endpush

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.modern-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.modern-page-header-left { flex: 1; }
.modern-page-header-right { display: flex; gap: 0.75rem; }
.modern-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.modern-alert { display: flex; align-items: center; gap: 0.65rem; padding: 0.85rem 1.25rem; margin-bottom: 1.25rem; border-radius: 10px; font-size: 0.88rem; font-weight: 500; }
.modern-alert-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.modern-alert-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.modern-alert-close { margin-left: auto; background: none; border: none; cursor: pointer; color: inherit; opacity: 0.6; }
.modern-alert-close:hover { opacity: 1; }
.modern-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
.modern-card-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0; }
.modern-card-title { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-card-body { padding: 0; }
.modern-badge { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.65rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
.modern-badge-light { background: #f3f4f6; color: #6b7280; }
.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-warning { background: #fefce8; color: #b45309; }
.modern-badge-info { background: #eff6ff; color: #2563eb; }
.btn-modern { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; }
.btn-modern-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.3); }
.btn-modern-primary:hover { transform: translateY(-2px); color: #fff; }
.btn-modern-outline { background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb; }
.btn-modern-outline:hover { border-color: #4361ee; color: #4361ee; }
.btn-sm { padding: 0.45rem 0.9rem; font-size: 0.82rem; }
/* Detail Stats */
.detail-stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.75rem; }
.detail-stat-card { background: #fff; border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
.detail-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.detail-stat-icon-blue { background: #eef2ff; color: #4361ee; }
.detail-stat-icon-green { background: #ecfdf5; color: #10b981; }
.detail-stat-icon-gold { background: #fff7ed; color: #f59e0b; }
.detail-stat-icon-purple { background: #f5f3ff; color: #7c3aed; }
.detail-stat-info { display: flex; flex-direction: column; }
.detail-stat-label { font-size: 0.78rem; color: #6c757d; font-weight: 500; }
.detail-stat-value { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; }
/* Detail Grid */
.detail-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem; }
.detail-info-body { padding: 1.25rem 1.5rem; }
.detail-info-row { display: flex; padding: 0.75rem 0; border-bottom: 1px solid #f3f4f6; }
.detail-info-row:last-child { border-bottom: none; }
.detail-info-label { width: 140px; flex-shrink: 0; font-weight: 600; color: #6b7280; font-size: 0.88rem; }
.detail-info-value { color: #1a1a2e; font-size: 0.9rem; }
/* Table */
.modern-table-wrapper { overflow-x: auto; }
.modern-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.modern-table thead th { background: #f9fafb; padding: 0.85rem 1rem; text-align: left; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 2px solid #e5e7eb; }
.th-center, .td-center { text-align: center !important; }
.th-actions, .td-actions { text-align: right !important; }
.modern-table tbody tr { border-bottom: 1px solid #f3f4f6; }
.modern-table tbody tr:hover { background: #f8f9ff; }
.modern-table td { padding: 0.9rem 1rem; vertical-align: middle; }
.modern-row-number { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; background: #f3f4f6; color: #6b7280; font-weight: 600; font-size: 0.8rem; }
.modern-cell-title { font-weight: 600; color: #1a1a2e; margin-bottom: 2px; }
.modern-cell-sub { font-size: 0.8rem; color: #6b7280; }
.modern-cell-muted { color: #d1d5db; font-size: 0.8rem; }
.modern-action-group { display: inline-flex; gap: 0.35rem; }
.modern-btn-icon { width: 34px; height: 34px; border-radius: 9px; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 0.82rem; text-decoration: none; }
.modern-btn-edit { background: #fefce8; color: #d97706; }
.modern-btn-edit:hover { background: #d97706; color: #fff; }
.modern-btn-delete { background: #fef2f2; color: #dc2626; }
.modern-btn-delete:hover { background: #dc2626; color: #fff; }
.modern-empty-state { text-align: center; padding: 4rem 2rem; }
.modern-empty-icon { width: 80px; height: 80px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; color: #d1d5db; margin-bottom: 1.25rem; }
.modern-empty-state h3 { font-size: 1.2rem; font-weight: 700; color: #1a1a2e; margin: 0 0 0.5rem; }
.modern-empty-state p { color: #9ca3af; font-size: 0.9rem; margin: 0; }
@media (max-width: 992px) { .detail-grid { grid-template-columns: 1fr; } }
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; }
    .detail-stats-row { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush
@endsection
