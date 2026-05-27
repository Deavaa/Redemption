@extends('layouts.admin')

@section('title', 'Self-Assessment Questions')

@section('content')
<div class="modern-page">
    {{-- Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="#">Academic</a></li>
                <li class="active">Self-Assessment</li>
            </ol></nav>
            <h1 class="modern-page-title">Self-Assessment Questions</h1>
            <p class="modern-page-subtitle">Create and manage questions for student self-assessment</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.assessment-questions.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i> Add Question
            </a>
            <a href="{{ route('admin.assessment-questions.bulk-create') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-list-ol"></i> Bulk Add
            </a>
            <a href="{{ route('admin.assessment-questions.report') }}" class="btn-modern btn-modern-ghost">
                <i class="fas fa-chart-bar"></i> Report
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

    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="modern-alert modern-alert-danger" style="max-height:200px;overflow-y:auto">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Import Warnings:</strong>
        <ul style="margin:0.5rem 0 0 1rem;padding:0;font-size:0.82rem">
            @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button class="modern-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    </div>
    @endif

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:1.25rem">
        <div class="modern-card-header" style="padding:0.85rem 1.25rem">
            <div class="modern-card-header-left">
                <i class="fas fa-filter" style="color:var(--primary)"></i>
                <span class="modern-card-title" style="font-size:0.95rem">Filters</span>
            </div>
        </div>
        <div style="padding:1rem 1.25rem">
            <form method="GET" action="{{ route('admin.assessment-questions.index') }}" id="filterForm">
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
                        <select name="difficulty" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Difficulty</option>
                            <option value="easy" {{ request('difficulty') === 'easy' ? 'selected' : '' }}>Easy</option>
                            <option value="medium" {{ request('difficulty') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="hard" {{ request('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="question_type" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Types</option>
                            <option value="multiple_choice" {{ request('question_type') === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                            <option value="true_false" {{ request('question_type') === 'true_false' ? 'selected' : '' }}>True / False</option>
                            <option value="short_answer" {{ request('question_type') === 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <input type="text" name="search" class="modern-input" style="padding-left:0.75rem" placeholder="Search questions..." value="{{ request('search') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <span class="modern-card-title">All Questions</span>
                <span class="modern-badge modern-badge-light">{{ $questions->total() }}</span>
            </div>
        </div>

        @if($questions->count() > 0)
        <div class="modern-table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="th-narrow">#</th>
                        <th>Question</th>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Type</th>
                        <th>Difficulty</th>
                        <th>Attempts</th>
                        <th>Accuracy</th>
                        <th class="th-center">Status</th>
                        <th class="th-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($questions as $idx => $q)
                    <tr>
                        <td class="td-narrow"><span class="modern-row-number">{{ $questions->firstItem() + $idx }}</span></td>
                        <td>
                            <a href="{{ route('admin.assessment-questions.show', $q->id) }}" class="text-decoration-none" style="color:inherit">
                                @if($q->title)
                                <div class="modern-cell-title">{{ Str::limit($q->title, 40) }}</div>
                                @endif
                                <div class="modern-cell-sub">{{ Str::limit(strip_tags($q->question_text), 60) }}</div>
                                @if($q->topic)
                                <span class="modern-badge modern-badge-light" style="font-size:0.65rem;margin-top:2px">{{ $q->topic }}</span>
                                @endif
                            </a>
                        </td>
                        <td>{{ $q->subject->name ?? '-' }}</td>
                        <td>{{ $q->classroom->name ?? '-' }}</td>
                        <td class="td-center">
                            @if($q->question_type === 'multiple_choice')
                            <span class="modern-badge modern-badge-info">MCQ</span>
                            @elseif($q->question_type === 'true_false')
                            <span class="modern-badge modern-badge-info" style="background:#e0e7ff;color:#3730a3">T/F</span>
                            @else
                            <span class="modern-badge modern-badge-light">Short</span>
                            @endif
                        </td>
                        <td class="td-center">
                            @if($q->difficulty === 'easy')
                            <span class="modern-badge modern-badge-success">Easy</span>
                            @elseif($q->difficulty === 'medium')
                            <span class="modern-badge modern-badge-warning">Medium</span>
                            @else
                            <span class="modern-badge modern-badge-danger">Hard</span>
                            @endif
                        </td>
                        <td class="td-center">{{ $q->answers()->count() }}</td>
                        <td class="td-center">
                            @php $stats = $q->getStudentAnswerStats(); @endphp
                            <span style="color:{{ $stats['accuracy_rate'] >= 70 ? '#10b981' : ($stats['accuracy_rate'] >= 40 ? '#f59e0b' : '#ef4444') }}">
                                {{ $stats['accuracy_rate'] }}%
                            </span>
                        </td>
                        <td class="td-center">
                            @if($q->is_active)
                            <span class="modern-badge modern-badge-success">Active</span>
                            @else
                            <span class="modern-badge modern-badge-light">Inactive</span>
                            @endif
                        </td>
                        <td class="td-actions">
                            <div class="modern-action-group">
                                <a href="{{ route('admin.assessment-questions.show', $q->id) }}" class="modern-btn-icon modern-btn-view" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.assessment-questions.edit', $q->id) }}" class="modern-btn-icon modern-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('admin.assessment-questions.toggle-active', $q->id) }}" style="display:inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="modern-btn-icon" style="background:{{ $q->is_active ? '#fef3c7' : '#d1fae5' }};color:{{ $q->is_active ? '#92400e' : '#065f46' }}" title="{{ $q->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $q->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.assessment-questions.destroy', $q->id) }}" style="display:inline" onsubmit="return confirm('Delete this question?')">
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
            {{ $questions->withQueryString()->links() }}
        </div>
        @else
        <div class="modern-empty-state">
            <div class="modern-empty-icon"><i class="fas fa-brain"></i></div>
            <h3>No questions found</h3>
            <p>Start by creating your first self-assessment question.</p>
            <a href="{{ route('admin.assessment-questions.create') }}" class="btn-modern btn-modern-primary"><i class="fas fa-plus"></i> Create Question</a>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('#filterForm select, #filterForm input').forEach(el => {
        el.addEventListener('change', () => document.getElementById('filterForm').submit());
    });
</script>
@endpush
@endsection
