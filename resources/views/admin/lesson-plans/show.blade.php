@extends('layouts.admin')
@section('title', 'Lesson Plan Details')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.lesson-plans.index') }}">Lesson Plans</a></li>
                <li class="active">{{ $lessonPlan->title }}</li>
            </ol></nav>
            <h1 class="modern-page-title">{{ $lessonPlan->title }}</h1>
            <p class="modern-page-subtitle">
                <span class="modern-badge {{ \App\Models\LessonPlan::statusBadgeClass($lessonPlan->status) }}">
                    {{ \App\Models\LessonPlan::statusOptions()[$lessonPlan->status] ?? $lessonPlan->status }}
                </span>
            </p>
        </div>
        <div class="modern-page-header-right">
            @if(auth()->user()->role !== 'teacher' || in_array($lessonPlan->status, ['draft','revision']))
            <a href="{{ route('admin.lesson-plans.edit', $lessonPlan->id) }}" class="btn-modern btn-modern-outline"><i class="fas fa-edit"></i> Edit</a>
            @endif
            <a href="{{ route('admin.lesson-plans.index') }}" class="btn-modern btn-modern-ghost"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button class="modern-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    </div>
    @endif

    {{-- Revision Banner --}}
    @if($lessonPlan->status === 'revision' && $lessonPlan->reviewer_comment)
    <div class="modern-alert modern-alert-warning" style="margin-bottom:1.25rem">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Revision Requested by {{ $lessonPlan->reviewer?->name ?? 'Reviewer' }}</strong>
            <br><em>{{ $lessonPlan->reviewer_comment }}</em>
            <br><small style="color:#92400e">{{ $lessonPlan->reviewed_at?->format('M d, Y H:i') }}</small>
        </div>
    </div>
    @endif

    {{-- Details Grid --}}
    <div class="modern-card" style="margin-bottom:1.25rem">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <i class="fas fa-info-circle" style="color:#4361ee"></i>
                <span class="modern-card-title">Plan Information</span>
            </div>
        </div>
        <div class="modern-detail-grid">
            <div class="modern-detail-item">
                <div class="modern-detail-label">Teacher</div>
                <div class="modern-detail-value">{{ $lessonPlan->teacher?->full_name ?? '-' }}</div>
            </div>
            <div class="modern-detail-item">
                <div class="modern-detail-label">Subject</div>
                <div class="modern-detail-value">{{ $lessonPlan->subject?->name ?? '-' }}</div>
            </div>
            <div class="modern-detail-item">
                <div class="modern-detail-label">Class / Section</div>
                <div class="modern-detail-value">{{ $lessonPlan->classRoom?->name ?? '-' }}{{ $lessonPlan->section ? ' / '.$lessonPlan->section->name : '' }}</div>
            </div>
            <div class="modern-detail-item">
                <div class="modern-detail-label">Academic Year / Term</div>
                <div class="modern-detail-value">{{ $lessonPlan->academicYear?->name ?? '-' }} &mdash; {{ $lessonPlan->term?->name ?? '-' }}</div>
            </div>
            <div class="modern-detail-item">
                <div class="modern-detail-label">Week</div>
                <div class="modern-detail-value">Week {{ $lessonPlan->week_number }}</div>
            </div>
            <div class="modern-detail-item">
                <div class="modern-detail-label">Lesson Date</div>
                <div class="modern-detail-value">{{ $lessonPlan->lesson_date?->format('F d, Y') ?? 'Not set' }}</div>
            </div>
            <div class="modern-detail-item">
                <div class="modern-detail-label">Duration</div>
                <div class="modern-detail-value">{{ $lessonPlan->duration_minutes }} minutes</div>
            </div>
            <div class="modern-detail-item">
                <div class="modern-detail-label">Status</div>
                <div class="modern-detail-value">
                    <span class="modern-badge {{ \App\Models\LessonPlan::statusBadgeClass($lessonPlan->status) }}">
                        {{ \App\Models\LessonPlan::statusOptions()[$lessonPlan->status] ?? $lessonPlan->status }}
                    </span>
                </div>
            </div>
            @if($lessonPlan->reviewed_at)
            <div class="modern-detail-item modern-detail-full">
                <div class="modern-detail-label">Reviewed By</div>
                <div class="modern-detail-value">{{ $lessonPlan->reviewer?->name ?? '-' }} on {{ $lessonPlan->reviewed_at->format('M d, Y H:i') }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Lesson Content --}}
    <div class="modern-card" style="margin-bottom:1.25rem">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <i class="fas fa-book-open" style="color:#7c3aed"></i>
                <span class="modern-card-title">Lesson Content</span>
            </div>
        </div>
        <div style="padding:1.5rem">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
                <div>
                    <h4 style="font-size:0.85rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem">
                        <i class="fas fa-bullseye" style="color:#4361ee"></i> Learning Objectives
                    </h4>
                    <div style="color:#374151;white-space:pre-wrap;line-height:1.7">{{ $lessonPlan->objectives ?: '—' }}</div>
                </div>
                <div>
                    <h4 style="font-size:0.85rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem">
                        <i class="fas fa-tools" style="color:#f59e0b"></i> Teaching Materials
                    </h4>
                    <div style="color:#374151;white-space:pre-wrap;line-height:1.7">{{ $lessonPlan->materials ?: '—' }}</div>
                </div>
                <div style="grid-column:span 2">
                    <h4 style="font-size:0.85rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem">
                        <i class="fas fa-tasks" style="color:#10b981"></i> Lesson Activities
                    </h4>
                    <div style="color:#374151;white-space:pre-wrap;line-height:1.7">{{ $lessonPlan->activities ?: '—' }}</div>
                </div>
                <div>
                    <h4 style="font-size:0.85rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem">
                        <i class="fas fa-clipboard-check" style="color:#3b82f6"></i> Assessment Methods
                    </h4>
                    <div style="color:#374151;white-space:pre-wrap;line-height:1.7">{{ $lessonPlan->assessment ?: '—' }}</div>
                </div>
                <div>
                    <h4 style="font-size:0.85rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem">
                        <i class="fas fa-pencil-alt" style="color:#ef4444"></i> Homework / Assignment
                    </h4>
                    <div style="color:#374151;white-space:pre-wrap;line-height:1.7">{{ $lessonPlan->homework ?: '—' }}</div>
                </div>
                @if($lessonPlan->notes)
                <div style="grid-column:span 2">
                    <h4 style="font-size:0.85rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem">
                        <i class="fas fa-sticky-note" style="color:#6b7280"></i> Additional Notes
                    </h4>
                    <div style="color:#374151;white-space:pre-wrap;line-height:1.7">{{ $lessonPlan->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Review Section (admin/bp/gm only) --}}
    @if(auth()->user()->role !== 'teacher' && in_array($lessonPlan->status, ['submitted','reviewed']))
    <div class="modern-card" style="margin-bottom:1.25rem" id="review">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <i class="fas fa-clipboard-check" style="color:#f59e0b"></i>
                <span class="modern-card-title">Review Lesson Plan</span>
            </div>
        </div>
        <div style="padding:1.5rem">
            <form method="POST" action="{{ route('admin.lesson-plans.review', $lessonPlan->id) }}">
                @csrf @method('POST')
                <div class="modern-form-group" style="margin-bottom:1rem">
                    <label class="modern-form-label">Action</label>
                    <div style="display:flex;gap:0.75rem;flex-wrap:wrap">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:8px 16px;border-radius:10px;border:2px solid #e5e7eb;transition:all 0.2s" class="review-option">
                            <input type="radio" name="status" value="approved" style="accent-color:#10b981">
                            <i class="fas fa-check-circle" style="color:#10b981"></i> Approve
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:8px 16px;border-radius:10px;border:2px solid #e5e7eb;transition:all 0.2s" class="review-option">
                            <input type="radio" name="status" value="reviewed" style="accent-color:#3b82f6">
                            <i class="fas fa-eye" style="color:#3b82f6"></i> Reviewed
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:8px 16px;border-radius:10px;border:2px solid #e5e7eb;transition:all 0.2s" class="review-option">
                            <input type="radio" name="status" value="revision" style="accent-color:#ef4444">
                            <i class="fas fa-undo" style="color:#ef4444"></i> Needs Revision
                        </label>
                    </div>
                </div>
                <div class="modern-form-group">
                    <label class="modern-form-label">Reviewer Comment</label>
                    <textarea name="reviewer_comment" class="modern-input modern-textarea" rows="3" placeholder="Optional feedback for the teacher..." style="padding-left:1rem"></textarea>
                </div>
                <div style="margin-top:1rem">
                    <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-paper-plane"></i> Submit Review</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Follow-ups Section --}}
    <div class="modern-card" style="margin-bottom:1.25rem">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <i class="fas fa-clipboard-list" style="color:#10b981"></i>
                <span class="modern-card-title">Follow-ups</span>
                <span class="modern-badge modern-badge-info">{{ $lessonPlan->followUps->count() }}</span>
            </div>
            <div class="modern-card-header-right">
                <button type="button" class="btn-modern btn-modern-outline btn-modern-sm" onclick="document.getElementById('followUpForm').classList.toggle('d-none')">
                    <i class="fas fa-plus"></i> Add Follow-up
                </button>
            </div>
        </div>

        {{-- Add Follow-up Form (hidden by default) --}}
        <div id="followUpForm" class="d-none" style="padding:1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc">
            <form method="POST" action="{{ route('admin.lesson-plans.follow-ups.store', $lessonPlan->id) }}">
                @csrf
                <div class="modern-form-grid" style="grid-template-columns:1fr 1fr">
                    <div class="modern-form-group">
                        <label class="modern-form-label">Follow-up Date <span class="modern-required">*</span></label>
                        <input type="date" name="follow_up_date" value="{{ old('follow_up_date', now()->format('Y-m-d')) }}" class="modern-input" style="padding-left:0.75rem">
                        @error('follow_up_date')<span class="modern-form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Completion Status <span class="modern-required">*</span></label>
                        <select name="completion_status" class="modern-input modern-select" style="padding-left:0.75rem">
                            @foreach(\App\Models\LessonPlanFollowUp::completionStatusOptions() as $key => $label)
                            <option value="{{ $key }}" {{ old('completion_status','not_started') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('completion_status')<span class="modern-form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="modern-form-group modern-form-span-2">
                        <label class="modern-form-label">Objectives Achieved</label>
                        <textarea name="objectives_achieved" class="modern-input modern-textarea" rows="2" placeholder="Which objectives were met?">{{ old('objectives_achieved') }}</textarea>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Challenges Faced</label>
                        <textarea name="challenges" class="modern-input modern-textarea" rows="2" placeholder="Any difficulties encountered...">{{ old('challenges') }}</textarea>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Adjustments Made</label>
                        <textarea name="adjustments" class="modern-input modern-textarea" rows="2" placeholder="Changes from original plan...">{{ old('adjustments') }}</textarea>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Student Engagement</label>
                        <textarea name="student_engagement" class="modern-input modern-textarea" rows="2" placeholder="How engaged were students?">{{ old('student_engagement') }}</textarea>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-form-label">Remarks</label>
                        <textarea name="remarks" class="modern-input modern-textarea" rows="2" placeholder="Additional remarks...">{{ old('remarks') }}</textarea>
                    </div>
                </div>
                <div style="margin-top:1rem;display:flex;gap:0.5rem;justify-content:flex-end">
                    <button type="button" class="btn-modern btn-modern-ghost" onclick="document.getElementById('followUpForm').classList.add('d-none')">Cancel</button>
                    <button type="submit" class="btn-modern btn-modern-success"><i class="fas fa-check"></i> Save Follow-up</button>
                </div>
            </form>
        </div>

        {{-- Follow-up List --}}
        @if($lessonPlan->followUps->count() > 0)
        <div style="padding:0">
            @foreach($lessonPlan->followUps as $fu)
            <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f3f4f6;{{ $loop->last ? 'border-bottom:none' : '' }}">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem">
                    <div style="display:flex;align-items:center;gap:0.75rem">
                        <span style="font-weight:600;color:#1a1a2e">{{ $fu->follow_up_date?->format('M d, Y') }}</span>
                        <span class="modern-badge {{ \App\Models\LessonPlanFollowUp::completionBadgeClass($fu->completion_status) }}">
                            {{ \App\Models\LessonPlanFollowUp::completionStatusOptions()[$fu->completion_status] ?? $fu->completion_status }}
                        </span>
                    </div>
                    <div style="display:flex;gap:0.35rem">
                        <form method="POST" action="{{ route('admin.lesson-plans.follow-ups.destroy', [$lessonPlan->id, $fu->id]) }}" onsubmit="return confirm('Delete this follow-up?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="modern-btn-icon modern-btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                <div style="font-size:0.85rem;color:#6b7280">By {{ $fu->followedUpBy?->name ?? '-' }}</div>
                @if($fu->objectives_achieved)
                <div style="margin-top:0.5rem;font-size:0.88rem"><strong style="color:#475569">Objectives Achieved:</strong> {{ $fu->objectives_achieved }}</div>
                @endif
                @if($fu->challenges)
                <div style="margin-top:0.25rem;font-size:0.88rem"><strong style="color:#475569">Challenges:</strong> {{ $fu->challenges }}</div>
                @endif
                @if($fu->adjustments)
                <div style="margin-top:0.25rem;font-size:0.88rem"><strong style="color:#475569">Adjustments:</strong> {{ $fu->adjustments }}</div>
                @endif
                @if($fu->student_engagement)
                <div style="margin-top:0.25rem;font-size:0.88rem"><strong style="color:#475569">Student Engagement:</strong> {{ $fu->student_engagement }}</div>
                @endif
                @if($fu->remarks)
                <div style="margin-top:0.25rem;font-size:0.88rem"><strong style="color:#475569">Remarks:</strong> {{ $fu->remarks }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div style="padding:2rem;text-align:center;color:#9ca3af">
            <i class="fas fa-clipboard-list" style="font-size:2rem;margin-bottom:0.5rem;display:block;opacity:0.3"></i>
            No follow-ups recorded yet
        </div>
        @endif
    </div>

    {{-- Delete --}}
    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin' || (auth()->user()->role === 'teacher' && in_array($lessonPlan->status, ['draft','revision'])))
    <div class="modern-card">
        <div style="padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center">
            <div>
                <strong style="color:#ef4444">Danger Zone</strong>
                <p style="font-size:0.82rem;color:#9ca3af;margin:0">Permanently delete this lesson plan</p>
            </div>
            <form method="POST" action="{{ route('admin.lesson-plans.destroy', $lessonPlan->id) }}" onsubmit="return confirm('Are you sure you want to delete this lesson plan?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-modern btn-modern-danger btn-modern-sm"><i class="fas fa-trash"></i> Delete</button>
            </form>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .review-option:has(input:checked) { border-color: #4361ee; background: #f8f9ff; }
    .d-none { display: none !important; }
</style>
@endpush
@endsection
