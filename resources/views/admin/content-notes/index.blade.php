@extends('layouts.admin')

@section('title', 'Content Note Bank')

@section('content')
<div class="modern-page">
    {{-- Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="#">Academic</a></li>
                <li class="active">Content Note Bank</li>
            </ol></nav>
            <h1 class="modern-page-title">Content Note Bank</h1>
            <p class="modern-page-subtitle">Reusable teaching notes linked to lesson plans — share across sections & branches</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.content-notes.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i> Add Note
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

    @if(session('error'))
    <div class="modern-alert modern-alert-danger">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button class="modern-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    </div>
    @endif

    {{-- Stats --}}
    <div class="modern-stats-row" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.25rem">
        <div class="modern-stat-card">
            <div class="modern-stat-icon" style="background:linear-gradient(135deg,#4361ee,#3a0ca3)"><i class="fas fa-sticky-note"></i></div>
            <div class="modern-stat-body">
                <div class="modern-stat-value">{{ $totalNotes }}</div>
                <div class="modern-stat-label">Total Notes</div>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-share-alt"></i></div>
            <div class="modern-stat-body">
                <div class="modern-stat-value">{{ $sharedNotes }}</div>
                <div class="modern-stat-label">Shared Notes</div>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-link"></i></div>
            <div class="modern-stat-body">
                <div class="modern-stat-value">{{ $linkedNotes }}</div>
                <div class="modern-stat-label">Linked to Lesson Plan</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:1.25rem">
        <div class="modern-card-header" style="padding:0.85rem 1.25rem">
            <div class="modern-card-header-left">
                <i class="fas fa-filter" style="color:var(--primary)"></i>
                <span class="modern-card-title" style="font-size:0.95rem">Filters</span>
            </div>
        </div>
        <div style="padding:1rem 1.25rem">
            <form method="GET" action="{{ route('admin.content-notes.index') }}" id="filterForm">
                <div class="modern-form-grid" style="grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:0.75rem">
                    <div class="modern-form-group">
                        <select name="subject_id" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="class_id" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="note_type" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Types</option>
                            @foreach(\App\Models\SubjectContentNote::noteTypeOptions() as $key => $label)
                            <option value="{{ $key }}" {{ request('note_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="difficulty" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Difficulty</option>
                            <option value="easy" {{ request('difficulty') === 'easy' ? 'selected' : '' }}>Easy</option>
                            <option value="medium" {{ request('difficulty') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="hard" {{ request('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <input type="text" name="search" class="modern-input" style="padding-left:0.75rem" placeholder="Search notes..." value="{{ request('search') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <span class="modern-card-title">All Notes</span>
                <span class="modern-badge modern-badge-light">{{ $notes->total() }}</span>
            </div>
        </div>

        @if($notes->count() > 0)
        <div class="modern-table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="th-narrow">#</th>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Sections</th>
                        <th>Type</th>
                        <th>Lesson Plan</th>
                        <th>Shared</th>
                        <th class="th-center">Status</th>
                        <th class="th-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notes as $idx => $note)
                    <tr>
                        <td class="td-narrow"><span class="modern-row-number">{{ $notes->firstItem() + $idx }}</span></td>
                        <td>
                            <a href="{{ route('admin.content-notes.show', $note->id) }}" style="color:inherit;text-decoration:none">
                                <div class="modern-cell-title">{{ Str::limit($note->title, 40) }}</div>
                                @if($note->topic)
                                <span class="modern-badge modern-badge-light" style="font-size:0.65rem;margin-top:2px">{{ $note->topic }}</span>
                                @endif
                            </a>
                        </td>
                        <td>{{ $note->subject->name ?? '-' }}</td>
                        <td>{{ $note->classroom->name ?? '-' }}</td>
                        <td>
                            @if($note->sections->count() > 0)
                                <span class="modern-badge modern-badge-info">{{ $note->sections->count() }} section{{ $note->sections->count() > 1 ? 's' : '' }}</span>
                            @elseif($note->is_shared)
                                <span class="modern-badge modern-badge-success">All</span>
                            @else
                                <span class="modern-badge modern-badge-light">None</span>
                            @endif
                        </td>
                        <td class="td-center">
                            @php $typeBadge = \App\Models\SubjectContentNote::noteTypeBadgeClass(); @endphp
                            <span class="modern-badge {{ $typeBadge[$note->note_type] ?? 'modern-badge-light' }}">
                                {{ \App\Models\SubjectContentNote::noteTypeOptions()[$note->note_type] ?? $note->note_type }}
                            </span>
                        </td>
                        <td class="td-center">
                            @if($note->lessonPlan)
                                <a href="{{ route('admin.lesson-plans.show', $note->lesson_plan_id) }}" style="color:#4361ee;font-size:0.8rem" title="{{ $note->lessonPlan->title }}">
                                    <i class="fas fa-link"></i> Linked
                                </a>
                            @else
                                <span style="color:#9ca3af;font-size:0.8rem">—</span>
                            @endif
                        </td>
                        <td class="td-center">
                            @if($note->is_shared)
                            <span class="modern-badge modern-badge-success"><i class="fas fa-share-alt" style="font-size:0.65rem"></i> Shared</span>
                            @else
                            <span class="modern-badge modern-badge-light">Private</span>
                            @endif
                        </td>
                        <td class="td-center">
                            @if($note->is_active)
                            <span class="modern-badge modern-badge-success">Active</span>
                            @else
                            <span class="modern-badge modern-badge-light">Inactive</span>
                            @endif
                        </td>
                        <td class="td-actions">
                            <div class="modern-action-group">
                                <a href="{{ route('admin.content-notes.show', $note->id) }}" class="modern-btn-icon modern-btn-view" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.content-notes.edit', $note->id) }}" class="modern-btn-icon modern-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('admin.content-notes.toggle-shared', $note->id) }}" style="display:inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="modern-btn-icon" style="background:{{ $note->is_shared ? '#fef3c7' : '#d1fae5' }};color:{{ $note->is_shared ? '#92400e' : '#065f46' }}" title="{{ $note->is_shared ? 'Unshare' : 'Share' }}">
                                        <i class="fas fa-{{ $note->is_shared ? 'unlink' : 'share-alt' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.content-notes.destroy', $note->id) }}" style="display:inline" onsubmit="return confirm('Delete this note?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="modern-btn-icon modern-btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="modern-pagination-wrapper">
            {{ $notes->withQueryString()->links() }}
        </div>
        @else
        <div class="modern-empty-state">
            <div class="modern-empty-icon"><i class="fas fa-sticky-note"></i></div>
            <h3>No content notes found</h3>
            <p>Start by creating your first content note for your subject.</p>
            <a href="{{ route('admin.content-notes.create') }}" class="btn-modern btn-modern-primary"><i class="fas fa-plus"></i> Create Note</a>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('#filterForm select, #filterForm input').forEach(function(el) {
        el.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
</script>
@endpush
@endsection
