@extends('layouts.admin')
@section('title', 'Student Details')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.students.index') }}">Students</a></li>
                    <li class="active">{{ $data->full_name }}</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.students.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
            <a href="{{ route('admin.students.edit', $data->id) }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-pen"></i>
                <span>Edit</span>
            </a>
        </div>
    </div>

    @php
        $enrollment = $data->currentEnrollment;
        $enrollmentBranch = $enrollment?->branch ?? $data->branch;
        $enrollmentClass = $enrollment?->classroom ?? $data->classroom;
        $enrollmentSection = $enrollment?->section ?? $data->section;
        $enrollmentAY = $enrollment?->academicYear ?? $data->academicYear;
    @endphp

    <div class="modern-detail-grid">
        {{-- Main Info Card --}}
        <div class="modern-card modern-detail-main">
            {{-- Hero Section --}}
            <div class="modern-detail-hero">
                @if($data->photo)
                    <img src="{{ asset('storage/' . $data->photo) }}" alt="{{ $data->full_name }}" class="modern-detail-hero-avatar">
                @else
                    <div class="modern-detail-hero-icon">
                        {{ strtoupper(substr($data->full_name, 0, 1)) }}
                    </div>
                @endif
                <div class="modern-detail-hero-info">
                    <h2 class="modern-detail-hero-title">{{ $data->full_name }}</h2>
                    <div class="modern-detail-hero-badges">
                        @php
                            $statusBadge = match($data->status ?? '') {
                                'active' => 'modern-badge-success',
                                'inactive' => 'modern-badge-danger',
                                'graduated' => 'modern-badge-info',
                                'transferred' => 'modern-badge-warning',
                                default => 'modern-badge-light'
                            };
                            $statusIcon = match($data->status ?? '') {
                                'active' => 'fa-check-circle',
                                'inactive' => 'fa-times-circle',
                                'graduated' => 'fa-graduation-cap',
                                'transferred' => 'fa-exchange-alt',
                                default => 'fa-question-circle'
                            };
                        @endphp
                        <span class="modern-badge {{ $statusBadge }}"><i class="fas {{ $statusIcon }}"></i> {{ ucfirst($data->status ?? 'N/A') }}</span>
                        @if($data->admission_number)
                            <span class="modern-badge modern-badge-light"><i class="fas fa-id-badge"></i> {{ $data->admission_number }}</span>
                        @endif
                        @if($enrollment)
                            <span class="modern-badge modern-badge-info"><i class="fas fa-clipboard-check"></i> Enrolled</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Personal Information --}}
            <div class="modern-detail-section">
                <div class="modern-detail-section-header">
                    <i class="fas fa-user"></i> Personal Information
                </div>
                <div class="modern-detail-body">
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-user"></i> Full Name
                        </div>
                        <div class="modern-detail-value">{{ $data->full_name }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-envelope"></i> Email
                        </div>
                        <div class="modern-detail-value">
                            @if($data->email)
                                <a href="mailto:{{ $data->email }}" class="modern-link">{{ $data->email }}</a>
                            @else
                                <span class="modern-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-phone"></i> Phone
                        </div>
                        <div class="modern-detail-value">
                            @if($data->phone)
                                <a href="tel:{{ $data->phone }}" class="modern-link">{{ $data->phone }}</a>
                            @else
                                <span class="modern-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-birthday-cake"></i> Date of Birth
                        </div>
                        <div class="modern-detail-value">{{ $data->date_of_birth ? \Carbon\Carbon::parse($data->date_of_birth)->format('M d, Y') : '-' }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-venus-mars"></i> Gender
                        </div>
                        <div class="modern-detail-value">{{ ucfirst($data->gender ?? '-') }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-map-marker-alt"></i> Address
                        </div>
                        <div class="modern-detail-value">{{ $data->address ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Academic Information (from Enrollment) --}}
            <div class="modern-detail-section">
                <div class="modern-detail-section-header">
                    <i class="fas fa-graduation-cap"></i> Academic Information
                    @if($enrollment)
                        <span class="modern-badge modern-badge-info" style="font-size:0.7rem;margin-left:0.5rem;">From Enrollment</span>
                    @endif
                </div>
                <div class="modern-detail-body">
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-building"></i> Branch
                        </div>
                        <div class="modern-detail-value">{{ $enrollmentBranch?->name ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-chalkboard"></i> Class
                        </div>
                        <div class="modern-detail-value">{{ $enrollmentClass?->name ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-layer-group"></i> Section
                        </div>
                        <div class="modern-detail-value">{{ $enrollmentSection?->name ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-calendar-alt"></i> Academic Year
                        </div>
                        <div class="modern-detail-value">{{ $enrollmentAY?->name ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-hashtag"></i> Roll Number
                        </div>
                        <div class="modern-detail-value">{{ $enrollment?->roll_number ?? $data->roll_number ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-id-badge"></i> Admission Number
                        </div>
                        <div class="modern-detail-value">{{ $data->admission_number ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-calendar-check"></i> Admission Date
                        </div>
                        <div class="modern-detail-value">{{ $data->admission_date ? \Carbon\Carbon::parse($data->admission_date)->format('M d, Y') : '-' }}</div>
                    </div>
                    @if($enrollment)
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-calendar-plus"></i> Enrollment Date
                        </div>
                        <div class="modern-detail-value">{{ $enrollment->enrollment_date ? \Carbon\Carbon::parse($enrollment->enrollment_date)->format('M d, Y') : '-' }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-tag"></i> Enrollment Type
                        </div>
                        <div class="modern-detail-value">{{ ucfirst($enrollment->enrollment_type ?? '-') }}</div>
                    </div>
                    @endif
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-info-circle"></i> Status
                        </div>
                        <div class="modern-detail-value">
                            <span class="modern-badge {{ $statusBadge }}">{{ ucfirst($data->status ?? 'N/A') }}</span>
                            @if($enrollment)
                                <span class="modern-badge modern-badge-light" style="margin-left:0.25rem;">{{ ucfirst($enrollment->status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Guardian Information (from Parent Relationship) --}}
            <div class="modern-detail-section">
                <div class="modern-detail-section-header">
                    <i class="fas fa-shield-alt"></i> Guardian Information
                </div>
                <div class="modern-detail-body">
                    @if($data->parents->count() > 0)
                        @foreach($data->parents as $parent)
                            <div class="modern-guardian-card">
                                <div class="modern-detail-row">
                                    <div class="modern-detail-label">
                                        <i class="fas fa-user-shield"></i> {{ ucfirst($parent->pivot->relation ?? 'Guardian') }}
                                    </div>
                                    <div class="modern-detail-value">
                                        {{ $parent->father_name ?? $parent->guardian_name ?? $parent->mother_name ?? '-' }}
                                    </div>
                                </div>
                                @if($parent->father_phone || $parent->guardian_phone || $parent->mother_phone)
                                <div class="modern-detail-row">
                                    <div class="modern-detail-label">
                                        <i class="fas fa-phone"></i> Phone
                                    </div>
                                    <div class="modern-detail-value">
                                        <a href="tel:{{ $parent->father_phone ?? $parent->guardian_phone ?? $parent->mother_phone }}" class="modern-link">
                                            {{ $parent->father_phone ?? $parent->guardian_phone ?? $parent->mother_phone }}
                                        </a>
                                    </div>
                                </div>
                                @endif
                                @if($parent->father_occupation)
                                <div class="modern-detail-row">
                                    <div class="modern-detail-label">
                                        <i class="fas fa-briefcase"></i> Occupation
                                    </div>
                                    <div class="modern-detail-value">{{ $parent->father_occupation }}</div>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="modern-detail-row">
                            <div class="modern-detail-label">
                                <i class="fas fa-user-shield"></i> Guardian Name
                            </div>
                            <div class="modern-detail-value">{{ $data->guardian_name ?? '-' }}</div>
                        </div>
                        <div class="modern-detail-row">
                            <div class="modern-detail-label">
                                <i class="fas fa-phone"></i> Guardian Phone
                            </div>
                            <div class="modern-detail-value">
                                @if($data->guardian_phone)
                                    <a href="tel:{{ $data->guardian_phone }}" class="modern-link">{{ $data->guardian_phone }}</a>
                                @else
                                    <span class="modern-muted">-</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Comments Section --}}
            <div class="modern-detail-section">
                <div class="modern-detail-section-header">
                    <i class="fas fa-comment-alt"></i> Comments & Notes
                    <button type="button" class="btn-modern btn-modern-sm btn-modern-primary" onclick="openAddCommentModal()" style="margin-left:auto;">
                        <i class="fas fa-plus"></i> Add Comment
                    </button>
                </div>
                <div class="modern-detail-body" id="commentsContainer">
                    <div class="modern-comments-loading">
                        <i class="fas fa-spinner fa-spin"></i> Loading comments...
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="modern-detail-sidebar">
            {{-- Quick Actions Card --}}
            <div class="modern-card">
                <div class="modern-card-header-simple">
                    <i class="fas fa-bolt"></i> Quick Actions
                </div>
                <div class="modern-quick-actions">
                    <a href="{{ route('admin.students.edit', $data->id) }}" class="modern-quick-action">
                        <i class="fas fa-pen"></i>
                        <span>Edit Student</span>
                    </a>
                    <a href="#" class="modern-quick-action" onclick="openAddCommentModal(); return false;">
                        <i class="fas fa-comment"></i>
                        <span>Add Comment</span>
                    </a>
                    @if($data->phone)
                    <a href="tel:{{ $data->phone }}" class="modern-quick-action">
                        <i class="fas fa-phone"></i>
                        <span>Call Student</span>
                    </a>
                    @endif
                    @if($data->email)
                    <a href="mailto:{{ $data->email }}" class="modern-quick-action">
                        <i class="fas fa-envelope"></i>
                        <span>Send Email</span>
                    </a>
                    @endif
                    @php
                        $chatRecipientId = $data->user_id;
                        $chatRecipientType = 'student';
                        if (!$chatRecipientId) {
                            $chatParent = $data->parents()->first();
                            if ($chatParent && $chatParent->user_id) {
                                $chatRecipientId = $chatParent->user_id;
                                $chatRecipientType = 'parent';
                            } else {
                                $chatRecipientId = $data->id;
                            }
                        }
                    @endphp
                    <a href="{{ route('admin.chat.index') }}?recipient_id={{ $chatRecipientId }}&recipient_type={{ $chatRecipientType }}" class="modern-quick-action">
                        <i class="fas fa-paper-plane"></i>
                        <span>Send Message</span>
                    </a>
                    <a href="{{ route('admin.id-card-generate.index') }}?student_id={{ $data->id }}" class="modern-quick-action">
                        <i class="fas fa-id-card"></i>
                        <span>ID Card</span>
                    </a>
                    <a href="{{ route('admin.certificate-generate.index') }}?student_id={{ $data->id }}" class="modern-quick-action">
                        <i class="fas fa-certificate"></i>
                        <span>Certificate</span>
                    </a>
                    <form method="POST" action="{{ route('admin.students.destroy', $data->id) }}" onsubmit="return confirm('Are you sure you want to delete this student? This action cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="modern-quick-action modern-quick-action-danger">
                            <i class="fas fa-trash-alt"></i>
                            <span>Delete Student</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Report Comments Summary Card --}}
            <div class="modern-card" id="reportCommentsCard" style="display:none;">
                <div class="modern-card-header-simple">
                    <i class="fas fa-file-alt"></i> Report Card Comments
                </div>
                <div class="modern-report-comments-body" id="reportCommentsBody">
                </div>
            </div>

            {{-- Student Photo Card --}}
            @if($data->photo)
            <div class="modern-card">
                <div class="modern-card-header-simple">
                    <i class="fas fa-camera"></i> Student Photo
                </div>
                <div class="modern-photo-card">
                    <img src="{{ asset('storage/' . $data->photo) }}" alt="{{ $data->full_name }}" class="modern-photo-img">
                </div>
            </div>
            @endif

            {{-- Timestamps Card --}}
            <div class="modern-card">
                <div class="modern-card-header-simple">
                    <i class="fas fa-clock"></i> Timestamps
                </div>
                <div class="modern-timestamps">
                    <div class="modern-timestamp">
                        <span class="modern-timestamp-label">Created</span>
                        <span class="modern-timestamp-value">{{ $data->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="modern-timestamp">
                        <span class="modern-timestamp-label">Updated</span>
                        <span class="modern-timestamp-value">{{ $data->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Comment Modal --}}
<div class="modal fade" id="addCommentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modern-modal-content">
            <div class="modern-modal-header">
                <h5 class="modern-modal-title"><i class="fas fa-comment-alt"></i> Add Comment for {{ $data->full_name }}</h5>
                <button type="button" class="modern-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modern-modal-body">
                <form id="addCommentForm">
                    @csrf
                    <div class="modern-form-group">
                        <label class="modern-form-label">Comment Type <span class="modern-required">*</span></label>
                        <select name="comment_type" class="modern-input modern-select" id="commentType" required>
                            <option value="general">General Comment</option>
                            <option value="academic">Academic</option>
                            <option value="behavior">Behavior</option>
                            <option value="attendance">Attendance</option>
                            <option value="progress">Progress</option>
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Visibility <span class="modern-required">*</span></label>
                        <select name="visibility" class="modern-input modern-select" id="commentVisibility" required>
                            <option value="staff">Staff Only</option>
                            <option value="private">Private (only you)</option>
                            <option value="public">Public (visible to parents)</option>
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">
                            <input type="checkbox" name="is_report_comment" id="isReportComment" value="1">
                            Show on Report Card
                        </label>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Comment <span class="modern-required">*</span></label>
                        <textarea name="comment" id="commentText" class="modern-input modern-textarea" rows="4"
                            placeholder="Write your comment here..." required maxlength="2000"></textarea>
                        <div class="modern-input-hint"><span id="charCount">0</span>/2000 characters</div>
                    </div>
                </form>
            </div>
            <div class="modern-modal-footer">
                <button type="button" class="btn-modern btn-modern-ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-modern btn-modern-primary" id="submitCommentBtn">
                    <i class="fas fa-check"></i> Save Comment
                </button>
            </div>
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
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-page-header-left { flex: 1; }

.modern-page-header-right {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Breadcrumb */
.modern-breadcrumb ol {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0 0 0.5rem;
    gap: 0.5rem;
    font-size: 0.8rem;
    align-items: center;
}

.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Detail Grid */
.modern-detail-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 1.25rem;
    align-items: start;
}

/* Card */
.modern-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

/* Detail Hero */
.modern-detail-hero {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.75rem 2rem;
    background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
    border-bottom: 1px solid #e5e8ff;
}

.modern-detail-hero-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    font-weight: 800;
}

.modern-detail-hero-avatar {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    object-fit: cover;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.modern-detail-hero-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0 0 0.5rem;
}

.modern-detail-hero-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Badges */
.modern-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.65rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}

.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-info { background: #eff6ff; color: #2563eb; }
.modern-badge-warning { background: #fefce8; color: #b45309; }
.modern-badge-light { background: #f3f4f6; color: #6b7280; }

/* Detail Sections */
.modern-detail-section {
    border-bottom: 1px solid #f0f0f0;
}

.modern-detail-section:last-child {
    border-bottom: none;
}

.modern-detail-section-header {
    padding: 0.85rem 2rem;
    font-weight: 600;
    color: #4361ee;
    font-size: 0.88rem;
    background: #fafbfc;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-detail-section-header i {
    font-size: 0.82rem;
}

/* Detail Body */
.modern-detail-body { padding: 0.5rem 0; }

.modern-detail-row {
    display: flex;
    padding: 0.9rem 2rem;
    border-bottom: 1px solid #f8f9fa;
    transition: background 0.15s;
}

.modern-detail-row:last-child { border-bottom: none; }
.modern-detail-row:hover { background: #fafbfc; }

.modern-detail-label {
    width: 180px;
    flex-shrink: 0;
    font-weight: 600;
    color: #6b7280;
    font-size: 0.88rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-detail-label i { color: #9ca3af; font-size: 0.82rem; width: 16px; text-align: center; }

.modern-detail-value {
    color: #1a1a2e;
    font-size: 0.9rem;
}

.modern-link { color: #4361ee; text-decoration: none; font-weight: 500; }
.modern-link:hover { text-decoration: underline; }

.modern-muted { color: #d1d5db; }

/* Sidebar */
.modern-detail-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.modern-card-header-simple {
    padding: 1rem 1.25rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-card-header-simple i { color: #4361ee; font-size: 0.85rem; }

/* Quick Actions */
.modern-quick-actions {
    padding: 0.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.modern-quick-action {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.7rem 0.85rem;
    border-radius: 10px;
    color: #374151;
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 500;
    transition: all 0.15s;
    border: none;
    background: none;
    width: 100%;
    cursor: pointer;
    text-align: left;
}

.modern-quick-action i { color: #6b7280; width: 18px; text-align: center; }

.modern-quick-action:hover {
    background: #f3f4f6;
    color: #1a1a2e;
}

.modern-quick-action:hover i { color: #4361ee; }

.modern-quick-action-danger { color: #dc2626; }
.modern-quick-action-danger i { color: #f87171; }
.modern-quick-action-danger:hover { background: #fef2f2; color: #b91c1c; }
.modern-quick-action-danger:hover i { color: #dc2626; }

/* Photo Card */
.modern-photo-card {
    padding: 1rem 1.25rem;
    text-align: center;
}

.modern-photo-img {
    width: 100%;
    border-radius: 10px;
    object-fit: cover;
    max-height: 250px;
}

/* Timestamps */
.modern-timestamps { padding: 0.85rem 1.25rem; }

.modern-timestamp {
    display: flex;
    justify-content: space-between;
    padding: 0.45rem 0;
}

.modern-timestamp + .modern-timestamp { border-top: 1px solid #f3f4f6; }

.modern-timestamp-label { color: #9ca3af; font-size: 0.82rem; }
.modern-timestamp-value { color: #374151; font-size: 0.82rem; font-weight: 500; }

/* Modern Button */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.35rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.25s;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
}

.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4);
    color: #fff;
}

.btn-modern-outline {
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
}

.btn-modern-outline:hover {
    border-color: #4361ee;
    color: #4361ee;
    background: #f8f9ff;
}

/* Responsive */
@media (max-width: 992px) {
    .modern-detail-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-detail-hero { padding: 1.25rem; flex-direction: column; text-align: center; }
    .modern-detail-hero-badges { justify-content: center; }
    .modern-detail-row { flex-direction: column; gap: 0.25rem; padding: 0.75rem 1.25rem; }
    .modern-detail-label { width: auto; }
    .modern-detail-section-header { padding: 0.75rem 1.25rem; }
}

/* Comments */
.btn-modern-sm { padding: 0.4rem 0.85rem; font-size: 0.8rem; }
.modern-comments-loading { padding: 2rem; text-align: center; color: #9ca3af; }
.modern-comment-item { padding: 1rem 2rem; border-bottom: 1px solid #f3f4f6; transition: background 0.15s; }
.modern-comment-item:hover { background: #fafbfc; }
.modern-comment-item:last-child { border-bottom: none; }
.modern-comment-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem; }
.modern-comment-meta { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.modern-comment-author { font-weight: 600; color: #1a1a2e; font-size: 0.88rem; }
.modern-comment-date { font-size: 0.78rem; color: #9ca3af; }
.modern-comment-badges { display: flex; gap: 0.35rem; flex-wrap: wrap; }
.modern-comment-text { color: #374151; font-size: 0.88rem; line-height: 1.6; margin-top: 0.25rem; }
.modern-comment-actions { display: flex; gap: 0.5rem; }
.modern-comment-actions button { background: none; border: none; cursor: pointer; color: #9ca3af; font-size: 0.82rem; padding: 0.2rem 0.4rem; border-radius: 4px; transition: all 0.15s; }
.modern-comment-actions button:hover { color: #dc2626; background: #fef2f2; }
.modern-no-comments { padding: 2rem; text-align: center; color: #9ca3af; font-size: 0.88rem; }
.modern-badge-general { background: #f3f4f6; color: #6b7280; }
.modern-badge-academic { background: #eff6ff; color: #2563eb; }
.modern-badge-behavior { background: #fefce8; color: #b45309; }
.modern-badge-attendance { background: #fef2f2; color: #dc2626; }
.modern-badge-progress { background: #ecfdf5; color: #059669; }
.modern-badge-report { background: #faf5ff; color: #7c3aed; }
.modern-guardian-card { padding: 0; }
.modern-guardian-card + .modern-guardian-card { border-top: 1px dashed #e5e7eb; padding-top: 0.5rem; }
.modern-report-comments-body { padding: 0.75rem 1.25rem; }
.modern-report-comment-item { padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; font-size: 0.82rem; }
.modern-report-comment-item:last-child { border-bottom: none; }
.modern-report-comment-type { font-weight: 600; color: #4361ee; font-size: 0.75rem; }
.modern-report-comment-text { color: #374151; margin-top: 0.15rem; }
.modern-modal-content { background: #fff; border-radius: 14px; overflow: hidden; }
.modern-modal-header { padding: 1.25rem 1.5rem; background: #fafbfc; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
.modern-modal-title { font-weight: 700; font-size: 1.05rem; color: #1a1a2e; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.modern-modal-close { background: none; border: none; cursor: pointer; color: #6b7280; font-size: 1rem; padding: 0.25rem; }
.modern-modal-close:hover { color: #dc2626; }
.modern-modal-body { padding: 1.5rem; }
.modern-modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: flex-end; gap: 0.75rem; }
.btn-modern-ghost { background: transparent; color: #6b7280; border: 1px solid #e5e7eb; }
.btn-modern-ghost:hover { background: #f9fafb; border-color: #d1d5db; }
</style>
@endpush

@push('scripts')
<script>
const STUDENT_ID = {{ $data->id }};

// Load comments on page load
document.addEventListener('DOMContentLoaded', loadComments);

function loadComments() {
    const container = document.getElementById('commentsContainer');
    fetch(`/admin/students/${STUDENT_ID}/comments`)
        .then(r => r.json())
        .then(data => {
            if (data.comments && data.comments.length > 0) {
                container.innerHTML = data.comments.map(c => renderComment(c)).join('');
            } else {
                container.innerHTML = '<div class="modern-no-comments"><i class="fas fa-comment-slash" style="font-size:1.5rem;margin-bottom:0.5rem;display:block;"></i>No comments yet. Click "Add Comment" to write one.</div>';
            }
            // Load report comments in sidebar
            loadReportComments();
        })
        .catch(err => {
            container.innerHTML = '<div class="modern-no-comments" style="color:#dc2626;">Failed to load comments.</div>';
        });
}

function loadReportComments() {
    fetch(`/admin/students/${STUDENT_ID}/report-comments`)
        .then(r => r.json())
        .then(data => {
            const card = document.getElementById('reportCommentsCard');
            const body = document.getElementById('reportCommentsBody');
            if (data.comments && data.comments.length > 0) {
                card.style.display = 'block';
                body.innerHTML = data.comments.map(c =>
                    `<div class="modern-report-comment-item">
                        <div class="modern-report-comment-type">${c.comment_type_label}</div>
                        <div class="modern-report-comment-text">${escapeHtml(c.comment)}</div>
                    </div>`
                ).join('');
            }
        })
        .catch(() => {});
}

function renderComment(c) {
    const typeBadge = `modern-badge modern-badge-${c.comment_type}`;
    const reportBadge = c.is_report_comment ? '<span class="modern-badge modern-badge-report"><i class="fas fa-file-alt"></i> Report</span>' : '';
    const visIcon = c.visibility === 'private' ? '<i class="fas fa-lock" title="Private"></i>' : (c.visibility === 'public' ? '<i class="fas fa-globe" title="Public"></i>' : '');
    const deleteBtn = c.can_delete ? `<button onclick="deleteComment(${c.id})" title="Delete"><i class="fas fa-trash-alt"></i></button>` : '';

    return `<div class="modern-comment-item" id="comment-${c.id}">
        <div class="modern-comment-header">
            <div class="modern-comment-meta">
                <span class="modern-comment-author">${escapeHtml(c.author_name)}</span>
                <span class="modern-comment-date">${c.created_at}</span>
                ${visIcon}
            </div>
            <div class="modern-comment-badges">
                <span class="${typeBadge}">${c.comment_type_label}</span>
                ${reportBadge}
                <div class="modern-comment-actions">${deleteBtn}</div>
            </div>
        </div>
        <div class="modern-comment-text">${escapeHtml(c.comment)}</div>
    </div>`;
}

function openAddCommentModal() {
    document.getElementById('addCommentForm').reset();
    document.getElementById('charCount').textContent = '0';
    new bootstrap.Modal(document.getElementById('addCommentModal')).show();
}

// Submit comment
document.addEventListener('DOMContentLoaded', () => {
    const commentText = document.getElementById('commentText');
    if (commentText) {
        commentText.addEventListener('input', () => {
            document.getElementById('charCount').textContent = commentText.value.length;
        });
    }

    document.getElementById('submitCommentBtn')?.addEventListener('click', submitComment);
});

function submitComment() {
    const form = document.getElementById('addCommentForm');
    const data = {
        comment_type: document.getElementById('commentType').value,
        visibility: document.getElementById('commentVisibility').value,
        is_report_comment: document.getElementById('isReportComment').checked,
        comment: document.getElementById('commentText').value,
    };

    if (!data.comment.trim()) { alert('Please enter a comment.'); return; }

    fetch(`/admin/students/${STUDENT_ID}/comments`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('addCommentModal'))?.hide();
            loadComments();
        } else {
            alert(res.message || 'Failed to add comment.');
        }
    })
    .catch(err => alert('Error adding comment.'));
}

function deleteComment(id) {
    if (!confirm('Delete this comment?')) return;

    fetch(`/admin/students/${STUDENT_ID}/comments/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value }
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const el = document.getElementById('comment-' + id);
            if (el) el.remove();
            loadReportComments();
            // If no comments left, show placeholder
            const container = document.getElementById('commentsContainer');
            if (!container.querySelector('.modern-comment-item')) {
                container.innerHTML = '<div class="modern-no-comments"><i class="fas fa-comment-slash" style="font-size:1.5rem;margin-bottom:0.5rem;display:block;"></i>No comments yet. Click "Add Comment" to write one.</div>';
            }
        }
    })
    .catch(() => alert('Error deleting comment.'));
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
@endsection
