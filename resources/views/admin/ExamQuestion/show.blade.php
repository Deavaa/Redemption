@extends('layouts.admin')
@section('title', 'Exam Question Detail')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.exam-questions.index') }}">Exam Questions</a></li>
                    <li class="active">{{ $examQuestion->title }}</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.exam-questions.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Status Banner --}}
    <div class="eq-status-banner eq-status-{{ $examQuestion->status }}">
        <div class="eq-status-icon">
            <i class="{{ $examQuestion->status_icon }}"></i>
        </div>
        <div class="eq-status-info">
            <h3>{{ $examQuestion->status_label }}</h3>
            <p>
                @if($examQuestion->isDraft())
                    This question is still a draft. You can edit and submit it for review.
                @elseif($examQuestion->isSubmitted())
                    Waiting for the Department Head to review and approve/reject this question.
                @elseif($examQuestion->isDeptApproved())
                    Approved by Department Head. Waiting for the Principal's final approval.
                @elseif($examQuestion->isDeptRejected())
                    Rejected by Department Head. You can revise and resubmit.
                @elseif($examQuestion->isPrincipalApproved())
                    This question has been fully approved and can be used for exams.
                @elseif($examQuestion->isPrincipalRejected())
                    Rejected by Principal. You can revise and resubmit.
                @endif
            </p>
        </div>
    </div>

    {{-- Workflow Progress --}}
    <div class="eq-workflow-card">
        <div class="eq-workflow-steps">
            <div class="eq-step {{ in_array($examQuestion->status, ['submitted','dept_approved','principal_approved','dept_rejected','principal_rejected']) ? 'eq-step-done' : ($examQuestion->isDraft() ? 'eq-step-active' : '') }}">
                <div class="eq-step-circle"><i class="fas fa-pen"></i></div>
                <div class="eq-step-label">Created by Teacher</div>
            </div>
            <div class="eq-step-line {{ in_array($examQuestion->status, ['submitted','dept_approved','principal_approved']) ? 'eq-step-line-done' : '' }}"></div>
            <div class="eq-step {{ in_array($examQuestion->status, ['submitted']) ? 'eq-step-active' : (in_array($examQuestion->status, ['dept_approved','principal_approved']) ? 'eq-step-done' : '') }}">
                <div class="eq-step-circle"><i class="fas fa-user-tie"></i></div>
                <div class="eq-step-label">Dept. Head Review</div>
            </div>
            <div class="eq-step-line {{ in_array($examQuestion->status, ['dept_approved','principal_approved']) ? 'eq-step-line-done' : '' }}"></div>
            <div class="eq-step {{ $examQuestion->status === 'dept_approved' ? 'eq-step-active' : ($examQuestion->status === 'principal_approved' ? 'eq-step-done' : '') }}">
                <div class="eq-step-circle"><i class="fas fa-user-shield"></i></div>
                <div class="eq-step-label">Principal Approval</div>
            </div>
        </div>
    </div>

    <div class="eq-detail-grid">
        {{-- Main Content --}}
        <div class="eq-detail-main">
            {{-- Question Info Card --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <div class="modern-card-header-left">
                        <h2 class="modern-card-title">Question Details</h2>
                    </div>
                </div>
                <div class="eq-detail-body">
                    <div class="eq-info-row">
                        <span class="eq-info-label">Title</span>
                        <span class="eq-info-value">{{ $examQuestion->title }}</span>
                    </div>
                    <div class="eq-info-row">
                        <span class="eq-info-label">Subject</span>
                        <span class="eq-info-value"><span class="modern-badge modern-badge-light">{{ $examQuestion->subject->name ?? '-' }}</span></span>
                    </div>
                    <div class="eq-info-row">
                        <span class="eq-info-label">Class</span>
                        <span class="eq-info-value">{{ $examQuestion->classRoom->name ?? 'All Classes' }}</span>
                    </div>
                    <div class="eq-info-row">
                        <span class="eq-info-label">Question Type</span>
                        <span class="eq-info-value">{{ $examQuestion->question_type_label }}</span>
                    </div>
                    <div class="eq-info-row">
                        <span class="eq-info-label">Total Marks</span>
                        <span class="eq-info-value eq-marks">{{ $examQuestion->total_marks }}</span>
                    </div>
                    <div class="eq-info-row">
                        <span class="eq-info-label">Duration</span>
                        <span class="eq-info-value">{{ $examQuestion->duration_minutes ? $examQuestion->duration_minutes . ' minutes' : 'Not specified' }}</span>
                    </div>
                    <div class="eq-info-row">
                        <span class="eq-info-label">Academic Year</span>
                        <span class="eq-info-value">{{ $examQuestion->academicYear->name ?? '-' }}</span>
                    </div>
                    <div class="eq-info-row">
                        <span class="eq-info-label">Term</span>
                        <span class="eq-info-value">{{ $examQuestion->term->name ?? '-' }}</span>
                    </div>

                    @if($examQuestion->content)
                    <div class="eq-info-block">
                        <span class="eq-info-label">Questions Content</span>
                        <div class="eq-content-box">{!! nl2br(e($examQuestion->content)) !!}</div>
                    </div>
                    @endif

                    @if($examQuestion->attachment)
                    <div class="eq-info-row">
                        <span class="eq-info-label">Attachment</span>
                        <span class="eq-info-value">
                            <a href="{{ Storage::url($examQuestion->attachment) }}" target="_blank" class="eq-attachment-link">
                                <i class="fas fa-paperclip"></i> View Attachment
                            </a>
                        </span>
                    </div>
                    @endif

                    @if($examQuestion->notes)
                    <div class="eq-info-block">
                        <span class="eq-info-label">Notes</span>
                        <div class="eq-notes-box">{!! nl2br(e($examQuestion->notes)) !!}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Review History --}}
            @if($examQuestion->dept_reviewed_by || $examQuestion->principal_reviewed_by)
            <div class="modern-card">
                <div class="modern-card-header">
                    <div class="modern-card-header-left">
                        <h2 class="modern-card-title">Review History</h2>
                    </div>
                </div>
                <div class="eq-detail-body">
                    @if($examQuestion->dept_reviewed_by)
                    <div class="eq-review-entry">
                        <div class="eq-review-header">
                            <div class="eq-review-badge {{ $examQuestion->isDeptApproved() || $examQuestion->isPrincipalApproved() ? 'eq-badge-approved' : 'eq-badge-rejected' }}">
                                <i class="{{ $examQuestion->isDeptApproved() || $examQuestion->isPrincipalApproved() ? 'fas fa-check' : 'fas fa-times' }}"></i>
                                Department Head {{ $examQuestion->isDeptApproved() || $examQuestion->isPrincipalApproved() ? 'Approved' : 'Rejected' }}
                            </div>
                            <span class="eq-review-date">{{ $examQuestion->dept_reviewed_at?->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="eq-review-reviewer">Reviewed by: {{ $examQuestion->deptReviewer->name ?? '-' }}</div>
                        @if($examQuestion->dept_comments)
                        <div class="eq-review-comments">{{ $examQuestion->dept_comments }}</div>
                        @endif
                    </div>
                    @endif

                    @if($examQuestion->principal_reviewed_by)
                    <div class="eq-review-entry">
                        <div class="eq-review-header">
                            <div class="eq-review-badge {{ $examQuestion->isPrincipalApproved() ? 'eq-badge-approved' : 'eq-badge-rejected' }}">
                                <i class="{{ $examQuestion->isPrincipalApproved() ? 'fas fa-check' : 'fas fa-times' }}"></i>
                                Principal {{ $examQuestion->isPrincipalApproved() ? 'Approved' : 'Rejected' }}
                            </div>
                            <span class="eq-review-date">{{ $examQuestion->principal_reviewed_at?->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="eq-review-reviewer">Reviewed by: {{ $examQuestion->principalReviewer->name ?? '-' }}</div>
                        @if($examQuestion->principal_comments)
                        <div class="eq-review-comments">{{ $examQuestion->principal_comments }}</div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="eq-detail-sidebar">
            {{-- Quick Info --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <div class="modern-card-header-left">
                        <h2 class="modern-card-title">Quick Info</h2>
                    </div>
                </div>
                <div class="eq-detail-body">
                    <div class="eq-info-row">
                        <span class="eq-info-label">Teacher</span>
                        <span class="eq-info-value">{{ $examQuestion->teacher->full_name ?? '-' }}</span>
                    </div>
                    <div class="eq-info-row">
                        <span class="eq-info-label">Created</span>
                        <span class="eq-info-value">{{ $examQuestion->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="eq-info-row">
                        <span class="eq-info-label">Submitted</span>
                        <span class="eq-info-value">{{ $examQuestion->submitted_at?->format('M d, Y H:i') ?? 'Not yet' }}</span>
                    </div>
                    <div class="eq-info-row">
                        <span class="eq-info-label">Status</span>
                        <span class="eq-info-value"><span class="modern-badge {{ $examQuestion->status_badge }}">{{ $examQuestion->status_label }}</span></span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <div class="modern-card-header-left">
                        <h2 class="modern-card-title">Actions</h2>
                    </div>
                </div>
                <div class="eq-detail-body">
                    @php $user = Auth::user(); @endphp

                    {{-- Teacher Actions --}}
                    @if($examQuestion->canBeEdited() && ($user->role === 'teacher' || in_array($user->role, ['admin', 'super_admin'])))
                    <a href="{{ route('admin.exam-questions.edit', $examQuestion->id) }}" class="eq-action-btn eq-action-edit">
                        <i class="fas fa-pen"></i> Edit Question
                    </a>
                    @endif

                    @if($examQuestion->canBeSubmitted() && ($user->role === 'teacher' || in_array($user->role, ['admin', 'super_admin'])))
                    <form method="POST" action="{{ route('admin.exam-questions.submit', $examQuestion->id) }}" onsubmit="return confirm('Submit for department head review?')">
                        @csrf
                        <button type="submit" class="eq-action-btn eq-action-submit">
                            <i class="fas fa-paper-plane"></i> Submit for Review
                        </button>
                    </form>
                    @endif

                    {{-- Department Head Review --}}
                    @if($examQuestion->canBeReviewedByDept() && in_array($user->role, ['department_head', 'admin', 'super_admin']))
                    <div class="eq-review-section">
                        <h4><i class="fas fa-user-tie"></i> Department Head Review</h4>
                        <form method="POST" action="{{ route('admin.exam-questions.dept-review', $examQuestion->id) }}">
                            @csrf
                            <div class="eq-review-form-group">
                                <label>Comments (optional)</label>
                                <textarea name="dept_comments" class="eq-review-textarea" placeholder="Add review comments..."></textarea>
                            </div>
                            <div class="eq-review-btns">
                                <button type="submit" name="action" value="approve" class="eq-action-btn eq-action-approve" onclick="return confirm('Approve and forward to principal?')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button type="submit" name="action" value="reject" class="eq-action-btn eq-action-reject" onclick="return confirm('Reject this question?')">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif

                    {{-- Principal Review --}}
                    @if($examQuestion->canBeReviewedByPrincipal() && in_array($user->role, ['branch_principal', 'admin', 'super_admin']))
                    <div class="eq-review-section">
                        <h4><i class="fas fa-user-shield"></i> Principal Review</h4>
                        <form method="POST" action="{{ route('admin.exam-questions.principal-review', $examQuestion->id) }}">
                            @csrf
                            <div class="eq-review-form-group">
                                <label>Comments (optional)</label>
                                <textarea name="principal_comments" class="eq-review-textarea" placeholder="Add review comments..."></textarea>
                            </div>
                            <div class="eq-review-btns">
                                <button type="submit" name="action" value="approve" class="eq-action-btn eq-action-approve" onclick="return confirm('Give final approval?')">
                                    <i class="fas fa-check-double"></i> Approve
                                </button>
                                <button type="submit" name="action" value="reject" class="eq-action-btn eq-action-reject" onclick="return confirm('Reject this question?')">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif

                    {{-- Delete --}}
                    @if(in_array($user->role, ['admin', 'super_admin']))
                    <form method="POST" action="{{ route('admin.exam-questions.destroy', $examQuestion->id) }}" onsubmit="return confirm('Delete this exam question?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="eq-action-btn eq-action-delete">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
.modern-page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem; }
.modern-page-header-left { flex:1; }
.modern-breadcrumb ol { display:flex; list-style:none; padding:0; margin:0 0 .5rem; gap:.5rem; font-size:.8rem; align-items:center; }
.modern-breadcrumb li { color:#adb5bd; }
.modern-breadcrumb li a { color:#6c757d; text-decoration:none; }
.modern-breadcrumb li a:hover { color:#4361ee; }
.modern-breadcrumb li+li::before { content:'/'; margin-right:.5rem; color:#dee2e6; }
.modern-breadcrumb li.active { color:#4361ee; font-weight:500; }
.modern-card { background:#fff; border-radius:14px; box-shadow:0 1px 3px rgba(0,0,0,.06); border:1px solid #f0f0f0; overflow:hidden; margin-bottom:1.25rem; }
.modern-card-header { display:flex; justify-content:space-between; align-items:center; padding:1.25rem 1.5rem; border-bottom:1px solid #f0f0f0; }
.modern-card-header-left { display:flex; align-items:center; gap:.75rem; }
.modern-card-title { font-size:1.1rem; font-weight:700; color:#1a1a2e; margin:0; }
.modern-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.25rem .65rem; border-radius:50px; font-size:.75rem; font-weight:600; }
.modern-badge-light { background:#f3f4f6; color:#6b7280; }
.modern-badge-success { background:#ecfdf5; color:#059669; }
.modern-badge-danger { background:#fef2f2; color:#dc2626; }
.modern-badge-warning { background:#fffbeb; color:#d97706; }
.modern-badge-info { background:#eff6ff; color:#2563eb; }

/* Status Banner */
.eq-status-banner { display:flex; align-items:center; gap:1rem; padding:1.25rem 1.5rem; border-radius:14px; margin-bottom:1.5rem; }
.eq-status-icon { width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
.eq-status-info h3 { margin:0; font-size:1.1rem; font-weight:700; }
.eq-status-info p { margin:.25rem 0 0; font-size:.85rem; opacity:.8; }
.eq-status-draft { background:#f3f4f6; color:#4b5563; }
.eq-status-draft .eq-status-icon { background:#e5e7eb; }
.eq-status-submitted { background:#fffbeb; color:#92400e; }
.eq-status-submitted .eq-status-icon { background:#fef3c7; }
.eq-status-dept_approved { background:#eff6ff; color:#1e40af; }
.eq-status-dept_approved .eq-status-icon { background:#dbeafe; }
.eq-status-dept_rejected { background:#fef2f2; color:#991b1b; }
.eq-status-dept_rejected .eq-status-icon { background:#fecaca; }
.eq-status-principal_approved { background:#ecfdf5; color:#065f46; }
.eq-status-principal_approved .eq-status-icon { background:#a7f3d0; }
.eq-status-principal_rejected { background:#fef2f2; color:#991b1b; }
.eq-status-principal_rejected .eq-status-icon { background:#fecaca; }

/* Workflow Progress */
.eq-workflow-card { background:#fff; border-radius:14px; box-shadow:0 1px 3px rgba(0,0,0,.06); border:1px solid #f0f0f0; padding:1.5rem; margin-bottom:1.5rem; }
.eq-workflow-steps { display:flex; align-items:center; justify-content:center; gap:0; }
.eq-step { display:flex; flex-direction:column; align-items:center; gap:.5rem; }
.eq-step-circle { width:44px; height:44px; border-radius:50%; background:#f3f4f6; color:#9ca3af; display:flex; align-items:center; justify-content:center; font-size:1rem; transition:all .3s; }
.eq-step-active .eq-step-circle { background:#4361ee; color:#fff; box-shadow:0 0 0 4px rgba(67,97,238,.2); }
.eq-step-done .eq-step-circle { background:#10b981; color:#fff; }
.eq-step-label { font-size:.78rem; color:#6b7280; font-weight:600; text-align:center; }
.eq-step-active .eq-step-label { color:#4361ee; }
.eq-step-done .eq-step-label { color:#10b981; }
.eq-step-line { width:80px; height:3px; background:#e5e7eb; border-radius:3px; margin-bottom:1.5rem; }
.eq-step-line-done { background:#10b981; }

/* Detail Grid */
.eq-detail-grid { display:grid; grid-template-columns:1fr 360px; gap:1.5rem; }
.eq-detail-body { padding:1.25rem 1.5rem; }
.eq-info-row { display:flex; justify-content:space-between; align-items:center; padding:.6rem 0; border-bottom:1px solid #f3f4f6; }
.eq-info-row:last-child { border-bottom:none; }
.eq-info-label { font-size:.85rem; color:#6b7280; font-weight:500; }
.eq-info-value { font-size:.88rem; color:#1a1a2e; font-weight:600; text-align:right; }
.eq-marks { font-size:1.1rem; color:#4361ee; }
.eq-info-block { margin-top:.75rem; }
.eq-info-block .eq-info-label { display:block; margin-bottom:.5rem; }
.eq-content-box { background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:1rem; font-size:.88rem; line-height:1.7; color:#374151; white-space:pre-wrap; max-height:400px; overflow-y:auto; }
.eq-notes-box { background:#fefce8; border:1px solid #fde68a; border-radius:10px; padding:.75rem 1rem; font-size:.85rem; color:#92400e; line-height:1.6; white-space:pre-wrap; }
.eq-attachment-link { display:inline-flex; align-items:center; gap:.4rem; color:#4361ee; text-decoration:none; font-weight:600; font-size:.88rem; }
.eq-attachment-link:hover { text-decoration:underline; }

/* Review Entries */
.eq-review-entry { padding:1rem 0; border-bottom:1px solid #f3f4f6; }
.eq-review-entry:last-child { border-bottom:none; }
.eq-review-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:.5rem; }
.eq-review-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.25rem .65rem; border-radius:50px; font-size:.78rem; font-weight:600; }
.eq-badge-approved { background:#ecfdf5; color:#059669; }
.eq-badge-rejected { background:#fef2f2; color:#dc2626; }
.eq-review-date { font-size:.78rem; color:#9ca3af; }
.eq-review-reviewer { font-size:.85rem; color:#6b7280; margin-bottom:.5rem; }
.eq-review-comments { background:#f9fafb; border-radius:8px; padding:.75rem; font-size:.85rem; color:#374151; line-height:1.6; }

/* Review Section */
.eq-review-section { margin-top:.75rem; padding-top:.75rem; border-top:1px solid #f0f0f0; }
.eq-review-section h4 { font-size:.9rem; font-weight:700; color:#1a1a2e; margin:0 0 .75rem; display:flex; align-items:center; gap:.5rem; }
.eq-review-form-group { margin-bottom:.75rem; }
.eq-review-form-group label { display:block; font-size:.82rem; color:#6b7280; font-weight:500; margin-bottom:.35rem; }
.eq-review-textarea { width:100%; border:1.5px solid #e5e7eb; border-radius:8px; padding:.6rem .8rem; font-size:.85rem; resize:vertical; min-height:60px; transition:all .2s; }
.eq-review-textarea:focus { outline:none; border-color:#4361ee; box-shadow:0 0 0 3px rgba(67,97,238,.1); }
.eq-review-btns { display:flex; gap:.5rem; }

/* Action Buttons */
.eq-action-btn { display:flex; align-items:center; justify-content:center; gap:.5rem; width:100%; padding:.65rem 1rem; border-radius:10px; font-weight:600; font-size:.88rem; border:none; cursor:pointer; text-decoration:none; transition:all .2s; margin-bottom:.5rem; }
.eq-action-edit { background:#fefce8; color:#d97706; }
.eq-action-edit:hover { background:#d97706; color:#fff; }
.eq-action-submit { background:#eef2ff; color:#4361ee; }
.eq-action-submit:hover { background:#4361ee; color:#fff; }
.eq-action-approve { background:#ecfdf5; color:#059669; }
.eq-action-approve:hover { background:#059669; color:#fff; }
.eq-action-reject { background:#fef2f2; color:#dc2626; }
.eq-action-reject:hover { background:#dc2626; color:#fff; }
.eq-action-delete { background:#fef2f2; color:#dc2626; }
.eq-action-delete:hover { background:#dc2626; color:#fff; }
.btn-modern { display:inline-flex; align-items:center; gap:.5rem; padding:.65rem 1.35rem; border-radius:10px; font-weight:600; font-size:.9rem; text-decoration:none; border:none; cursor:pointer; transition:all .25s; }
.btn-modern-outline { background:transparent; color:#6b7280; border:1.5px solid #e5e7eb; }
.btn-modern-outline:hover { border-color:#4361ee; color:#4361ee; background:#f8f9ff; }

@media(max-width:768px) {
    .eq-detail-grid { grid-template-columns:1fr; }
    .eq-workflow-steps { flex-direction:column; }
    .eq-step-line { width:3px; height:30px; margin-bottom:0; }
    .eq-status-banner { flex-direction:column; text-align:center; }
}
</style>
@endpush
@endsection
