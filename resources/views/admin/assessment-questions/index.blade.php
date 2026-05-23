@extends('layouts.admin')

@section('title', 'Self-Assessment Questions')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Self-Assessment Questions</h3>
            <p class="page-subtitle">Create and manage questions for student self-assessment</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.assessment-questions.create') }}" class="btn btn-primary me-2">
                <i class="fas fa-plus me-1"></i> Add Question
            </a>
            <a href="{{ route('admin.assessment-questions.bulk-create') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-list-ol me-1"></i> Bulk Add
            </a>
            <a href="{{ route('admin.assessment-questions.report') }}" class="btn btn-outline-info">
                <i class="fas fa-chart-bar me-1"></i> Report
            </a>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.assessment-questions.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select form-select-sm">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Class</label>
                <select name="class_id" class="form-select form-select-sm">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Difficulty</label>
                <select name="difficulty" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="easy" {{ request('difficulty') === 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="medium" {{ request('difficulty') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="hard" {{ request('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="question_type" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="multiple_choice" {{ request('question_type') === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                    <option value="true_false" {{ request('question_type') === 'true_false' ? 'selected' : '' }}>True / False</option>
                    <option value="short_answer" {{ request('question_type') === 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search questions..." value="{{ request('search') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

{{-- Questions List --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Question</th>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Type</th>
                        <th>Difficulty</th>
                        <th>Attempts</th>
                        <th>Accuracy</th>
                        <th>Status</th>
                        <th style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $idx => $q)
                    <tr>
                        <td>{{ $questions->firstItem() + $idx }}</td>
                        <td>
                            <a href="{{ route('admin.assessment-questions.show', $q->id) }}" class="text-decoration-none">
                                @if($q->title)
                                    <strong>{{ Str::limit($q->title, 40) }}</strong><br>
                                @endif
                                <span class="text-muted small">{{ Str::limit(strip_tags($q->question_text), 60) }}</span>
                            </a>
                            @if($q->topic)
                                <br><span class="badge bg-light text-dark">{{ $q->topic }}</span>
                            @endif
                        </td>
                        <td>{{ $q->subject->name ?? '-' }}</td>
                        <td>{{ $q->classroom->name ?? '-' }}</td>
                        <td>
                            @if($q->question_type === 'multiple_choice')
                                <span class="badge bg-primary">MCQ</span>
                            @elseif($q->question_type === 'true_false')
                                <span class="badge bg-info">T/F</span>
                            @else
                                <span class="badge bg-secondary">Short</span>
                            @endif
                        </td>
                        <td>
                            @if($q->difficulty === 'easy')
                                <span class="badge bg-success">Easy</span>
                            @elseif($q->difficulty === 'medium')
                                <span class="badge bg-warning text-dark">Medium</span>
                            @else
                                <span class="badge bg-danger">Hard</span>
                            @endif
                        </td>
                        <td>{{ $q->answers()->count() }}</td>
                        <td>
                            @php $stats = $q->getStudentAnswerStats(); @endphp
                            <span class="{{ $stats['accuracy_rate'] >= 70 ? 'text-success' : ($stats['accuracy_rate'] >= 40 ? 'text-warning' : 'text-danger') }}">
                                {{ $stats['accuracy_rate'] }}%
                            </span>
                        </td>
                        <td>
                            @if($q->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.assessment-questions.show', $q->id) }}" class="btn btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.assessment-questions.edit', $q->id) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.assessment-questions.toggle-active', $q->id) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-outline-{{ $q->is_active ? 'warning' : 'success' }}" title="{{ $q->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $q->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.assessment-questions.destroy', $q->id) }}" class="d-inline" onsubmit="return confirm('Delete this question?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            No questions found. <a href="{{ route('admin.assessment-questions.create') }}">Create your first question</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $questions->withQueryString()->links() }}
    </div>
</div>
@endsection
