@extends('layouts.admin')
@section('title', 'Lesson Plans')
@section('content')
<div class="modern-page">
    {{-- Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="#">Academic</a></li>
                <li class="active">Lesson Plans</li>
            </ol></nav>
            <h1 class="modern-page-title">Lesson Plans</h1>
            <p class="modern-page-subtitle">Manage lesson plans &amp; follow-ups</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.lesson-plans.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i> New Plan
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button class="modern-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    </div>
    @endif

    {{-- Stats --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue"><i class="fas fa-file-alt"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalPlans }}</span>
                <span class="modern-stat-label">Total Plans</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gray"><i class="fas fa-pencil-alt"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $draftCount }}</span>
                <span class="modern-stat-label">Drafts</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-purple"><i class="fas fa-paper-plane"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $submittedCount }}</span>
                <span class="modern-stat-label">Submitted</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green"><i class="fas fa-check-double"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $approvedCount }}</span>
                <span class="modern-stat-label">Approved</span>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="modern-card" style="margin-bottom:1.25rem">
        <div class="modern-card-header" style="padding:0.85rem 1.25rem">
            <div class="modern-card-header-left">
                <i class="fas fa-filter" style="color:var(--primary)"></i>
                <span class="modern-card-title" style="font-size:0.95rem">Filters</span>
            </div>
        </div>
        <div style="padding:1rem 1.25rem">
            <form method="GET" action="{{ route('admin.lesson-plans.index') }}" id="filterForm">
                <div class="modern-form-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:0.75rem">
                    <div class="modern-form-group">
                        <select name="academic_year_id" class="modern-input modern-select" style="padding-left:0.75rem" {{ $isTeacher ? 'disabled' : '' }}>
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ request('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="term_id" class="modern-input modern-select" style="padding-left:0.75rem" {{ $isTeacher ? 'disabled' : '' }}>
                            <option value="">All Terms</option>
                            @foreach($terms as $t)
                            <option value="{{ $t->id }}" {{ request('term_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="class_id" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Classes</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="subject_id" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="status" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Statuses</option>
                            @foreach(\App\Models\LessonPlan::statusOptions() as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <span class="modern-card-title">All Lesson Plans</span>
                <span class="modern-badge modern-badge-light">{{ $lessonPlans->total() }}</span>
            </div>
        </div>

        @if($lessonPlans->count() > 0)
        <div class="modern-table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="th-narrow">#</th>
                        <th>Title</th>
                        @unless($isTeacher)<th>Teacher</th>@endunless
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Week</th>
                        <th>Date</th>
                        <th class="th-center">Status</th>
                        <th class="th-center">Follow-ups</th>
                        <th class="th-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lessonPlans as $plan)
                    <tr>
                        <td class="td-narrow"><span class="modern-row-number">{{ $loop->iteration + ($lessonPlans->currentPage() - 1) * $lessonPlans->perPage() }}</span></td>
                        <td>
                            <div class="modern-cell-title">{{ $plan->title }}</div>
                            @if($plan->objectives)
                            <div class="modern-cell-sub">{{ Str::limit($plan->objectives, 60) }}</div>
                            @endif
                        </td>
                        @unless($isTeacher)
                        <td>{{ $plan->teacher?->full_name ?? '-' }}</td>
                        @endunless
                        <td>{{ $plan->subject?->name ?? '-' }}</td>
                        <td>{{ $plan->classRoom?->name ?? '-' }}{{ $plan->section ? ' / '.$plan->section->name : '' }}</td>
                        <td class="td-center">W{{ $plan->week_number }}</td>
                        <td>{{ $plan->lesson_date?->format('M d') ?? '-' }}</td>
                        <td class="td-center">
                            <span class="modern-badge {{ \App\Models\LessonPlan::statusBadgeClass($plan->status) }}">
                                {{ \App\Models\LessonPlan::statusOptions()[$plan->status] ?? $plan->status }}
                            </span>
                        </td>
                        <td class="td-center">
                            @if($plan->followUps->count() > 0)
                            <span class="modern-badge modern-badge-info">{{ $plan->followUps->count() }}</span>
                            @else
                            <span class="modern-badge modern-badge-light">0</span>
                            @endif
                        </td>
                        <td class="td-actions">
                            <div class="modern-action-group">
                                <a href="{{ route('admin.lesson-plans.show', $plan->id) }}" class="modern-btn-icon modern-btn-view" title="View"><i class="fas fa-eye"></i></a>
                                @if(!$isTeacher || in_array($plan->status, ['draft','revision']))
                                <a href="{{ route('admin.lesson-plans.edit', $plan->id) }}" class="modern-btn-icon modern-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                @endif
                                @if(!$isTeacher && in_array($plan->status, ['submitted','reviewed']))
                                <a href="{{ route('admin.lesson-plans.show', $plan->id) }}#review" class="modern-btn-icon" style="background:#fef3c7;color:#92400e" title="Review"><i class="fas fa-clipboard-check"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="modern-pagination-wrapper">
            {{ $lessonPlans->withQueryString()->links() }}
        </div>
        @else
        <div class="modern-empty-state">
            <div class="modern-empty-icon"><i class="fas fa-file-alt"></i></div>
            <h3>No lesson plans yet</h3>
            <p>Start by creating your first lesson plan.</p>
            <a href="{{ route('admin.lesson-plans.create') }}" class="btn-modern btn-modern-primary"><i class="fas fa-plus"></i> Create Plan</a>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .modern-form-grid { gap: 0.75rem; }
    select:disabled { opacity: 0.7; cursor: not-allowed; background: #f3f4f6; }
</style>
@endpush

@push('scripts')
<script>
    // Auto-submit filter form on change
    document.querySelectorAll('#filterForm select').forEach(sel => {
        sel.addEventListener('change', () => document.getElementById('filterForm').submit());
    });
</script>
@endpush
@endsection
