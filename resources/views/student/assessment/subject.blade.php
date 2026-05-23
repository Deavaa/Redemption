@extends('student.layout')

@section('title', $subject->name . ' - Self-Assessment')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('student.assessment.index') }}">Self-Assessment</a></li>
                    <li class="breadcrumb-item active">{{ $subject->name }}</li>
                </ol>
            </nav>
            <h4 class="mb-0">{{ $subject->name }} <small class="text-muted">Questions</small></h4>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <select name="difficulty" class="form-select form-select-sm">
                        <option value="">All Difficulties</option>
                        <option value="easy" {{ request('difficulty') === 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ request('difficulty') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ request('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="topic" class="form-select form-select-sm">
                        <option value="">All Topics</option>
                        @foreach($topics as $topic)
                        <option value="{{ $topic }}" {{ request('topic') === $topic ? 'selected' : '' }}>{{ $topic }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="unanswered" {{ request('status') === 'unanswered' ? 'selected' : '' }}>Unanswered</option>
                        <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Answered</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Questions List --}}
    <div class="row">
        @forelse($questions as $question)
        @php
            $answered = $answeredIds->contains($question->id);
            $prevAnswer = $previousAnswers->get($question->id);
        @endphp
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 {{ $answered ? '' : 'border-start border-4' }}" style="{{ $answered ? '' : 'border-left-color: var(--primary) !important;' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            @if($question->difficulty === 'easy')
                                <span class="badge bg-success">Easy</span>
                            @elseif($question->difficulty === 'medium')
                                <span class="badge bg-warning text-dark">Medium</span>
                            @else
                                <span class="badge bg-danger">Hard</span>
                            @endif
                            @if($question->question_type === 'multiple_choice')
                                <span class="badge bg-primary">MCQ</span>
                            @elseif($question->question_type === 'true_false')
                                <span class="badge bg-info">T/F</span>
                            @else
                                <span class="badge bg-secondary">Short</span>
                            @endif
                            @if($question->topic)
                                <span class="badge bg-light text-dark">{{ $question->topic }}</span>
                            @endif
                        </div>
                        @if($answered)
                            @if($prevAnswer && $prevAnswer->is_correct)
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Correct</span>
                            @else
                                <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Wrong</span>
                            @endif
                        @else
                            <span class="badge bg-info">New</span>
                        @endif
                    </div>
                    <p class="mb-2">{{ Str::limit(strip_tags($question->question_text), 120) }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">{{ $question->marks }} mark(s)</small>
                        @if($answered)
                            <a href="{{ route('student.assessment.show', $question->id) }}" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-eye me-1"></i> Review
                            </a>
                        @else
                            <a href="{{ route('student.assessment.show', $question->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-play me-1"></i> Answer
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="fas fa-question-circle fa-3x mb-3"></i>
            <p>No questions available for this subject yet.</p>
            <a href="{{ route('student.assessment.index') }}" class="btn btn-outline-primary">Back to Subjects</a>
        </div>
        @endforelse
    </div>

    <div class="mt-3">{{ $questions->withQueryString()->links() }}</div>
</div>
@endsection
