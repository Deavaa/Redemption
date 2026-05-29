@extends('layouts.admin')

@section('title', 'View Content Note')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.content-notes.index') }}">Content Note Bank</a></li>
                <li class="active">{{ Str::limit($note->title, 30) }}</li>
            </ol></nav>
            <h1 class="modern-page-title">{{ $note->title }}</h1>
            <p class="modern-page-subtitle">
                {{ $note->subject->name ?? '' }} — {{ $note->classroom->name ?? '' }}
                @if($note->topic) &middot; {{ $note->topic }} @endif
            </p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.content-notes.edit', $note->id) }}" class="btn-modern btn-modern-outline"><i class="fas fa-edit"></i> Edit</a>
            <a href="{{ route('admin.content-notes.index') }}" class="btn-modern btn-modern-ghost"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    {{-- Meta Info --}}
    <div class="modern-card" style="margin-bottom:1.25rem">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <i class="fas fa-info-circle" style="color:#4361ee"></i>
                <span class="modern-card-title">Note Information</span>
            </div>
            <div class="modern-card-header-right">
                @if($note->is_shared)
                <span class="modern-badge modern-badge-success"><i class="fas fa-share-alt" style="font-size:0.65rem"></i> Shared</span>
                @else
                <span class="modern-badge modern-badge-light">Private</span>
                @endif
                @if($note->is_active)
                <span class="modern-badge modern-badge-success">Active</span>
                @else
                <span class="modern-badge modern-badge-light">Inactive</span>
                @endif
            </div>
        </div>
        <div style="padding:1.5rem">
            <div class="modern-detail-grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:0">
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Subject</div>
                    <div class="modern-detail-value">{{ $note->subject->name ?? '-' }}</div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Class</div>
                    <div class="modern-detail-value">{{ $note->classroom->name ?? '-' }}</div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Sections</div>
                    <div class="modern-detail-value">
                        @if($note->sections->count() > 0)
                            {{ $note->sections->pluck('name')->join(', ') }}
                        @elseif($note->is_shared)
                            <span style="color:#10b981">All Sections (shared)</span>
                        @else
                            <span style="color:#9ca3af">None assigned</span>
                        @endif
                    </div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Branch</div>
                    <div class="modern-detail-value">{{ $note->branch->name ?? 'All Branches' }}</div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Note Type</div>
                    <div class="modern-detail-value">
                        @php $typeBadge = \App\Models\SubjectContentNote::noteTypeBadgeClass(); @endphp
                        <span class="modern-badge {{ $typeBadge[$note->note_type] ?? 'modern-badge-light' }}">
                            {{ \App\Models\SubjectContentNote::noteTypeOptions()[$note->note_type] ?? $note->note_type }}
                        </span>
                    </div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Difficulty</div>
                    <div class="modern-detail-value">
                        @if($note->difficulty === 'easy')
                        <span class="modern-badge modern-badge-success">Easy</span>
                        @elseif($note->difficulty === 'medium')
                        <span class="modern-badge modern-badge-warning">Medium</span>
                        @else
                        <span class="modern-badge modern-badge-danger">Hard</span>
                        @endif
                    </div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Teacher</div>
                    <div class="modern-detail-value">{{ $note->teacher->name ?? 'Admin' }}</div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Academic Year</div>
                    <div class="modern-detail-value">{{ $note->academicYear->name ?? '-' }}</div>
                </div>
                @if($note->topic)
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Topic</div>
                    <div class="modern-detail-value">{{ $note->topic }}</div>
                </div>
                @endif
                @if($note->chapter)
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Chapter</div>
                    <div class="modern-detail-value">{{ $note->chapter }}</div>
                </div>
                @endif
                @if($note->lessonPlan)
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Linked Lesson Plan</div>
                    <div class="modern-detail-value">
                        <a href="{{ route('admin.lesson-plans.show', $note->lesson_plan_id) }}" style="color:#4361ee;text-decoration:none">
                            <i class="fas fa-link"></i> {{ $note->lessonPlan->title }}
                        </a>
                    </div>
                </div>
                @endif
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Created</div>
                    <div class="modern-detail-value">{{ $note->created_at->format('M d, Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Description --}}
    @if($note->description)
    <div class="modern-card" style="margin-bottom:1.25rem">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <i class="fas fa-align-left" style="color:#7c3aed"></i>
                <span class="modern-card-title">Description</span>
            </div>
        </div>
        <div style="padding:1.5rem">
            <p style="color:#374151;line-height:1.7">{{ $note->description }}</p>
        </div>
    </div>
    @endif

    {{-- Content --}}
    <div class="modern-card" style="margin-bottom:1.25rem">
        <div class="modern-card-header" style="background:linear-gradient(135deg,#4361ee,#3a0ca3)">
            <div class="modern-card-header-left">
                <i class="fas fa-file-alt" style="color:#fff"></i>
                <span class="modern-card-title" style="color:#fff">Note Content</span>
            </div>
        </div>
        <div style="padding:1.5rem">
            <div style="color:#374151;line-height:1.8;font-size:0.95rem;white-space:pre-wrap">{{ $note->content }}</div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="modern-card">
        <div style="padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem">
            <div style="display:flex;gap:0.5rem">
                <form method="POST" action="{{ route('admin.content-notes.toggle-shared', $note->id) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn-modern {{ $note->is_shared ? 'btn-modern-outline' : 'btn-modern-success' }}" style="font-size:0.85rem">
                        <i class="fas fa-{{ $note->is_shared ? 'unlink' : 'share-alt' }}"></i> {{ $note->is_shared ? 'Unshare' : 'Share' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.content-notes.toggle-active', $note->id) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn-modern btn-modern-outline" style="font-size:0.85rem">
                        <i class="fas fa-{{ $note->is_active ? 'pause' : 'play' }}"></i> {{ $note->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
            </div>
            <div style="display:flex;gap:0.5rem">
                <a href="{{ route('admin.content-notes.edit', $note->id) }}" class="btn-modern btn-modern-primary"><i class="fas fa-edit"></i> Edit</a>
                <form method="POST" action="{{ route('admin.content-notes.destroy', $note->id) }}" style="display:inline" onsubmit="return confirm('Delete this note permanently?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-modern btn-modern-danger"><i class="fas fa-trash"></i> Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
