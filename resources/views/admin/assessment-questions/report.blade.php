@extends('layouts.admin')

@section('title', 'Assessment Reports')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Assessment Reports</h3>
            <p class="page-subtitle">Student performance on self-assessment questions</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.assessment-questions.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Questions
            </a>
        </div>
    </div>
</div>

{{-- Summary Stats --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h2 class="text-primary">{{ $totalQuestions }}</h2>
                <div class="text-muted">Total Questions</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h2 class="text-info">{{ $totalAnswers }}</h2>
                <div class="text-muted">Total Attempts</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h2 class="text-success">{{ $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0 }}%</h2>
                <div class="text-muted">Overall Accuracy</div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="subject_id" class="form-select form-select-sm">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="class_id" class="form-select form-select-sm">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- Answers Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Question</th>
                        <th>Subject</th>
                        <th>Difficulty</th>
                        <th>Answer</th>
                        <th>Result</th>
                        <th>Attempt</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($answers as $answer)
                    <tr class="{{ $answer->is_correct ? '' : 'table-danger' }}">
                        <td>{{ $answer->student->full_name ?? '-' }}</td>
                        <td>{{ $answer->student->classroom->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.assessment-questions.show', $answer->assessment_question_id) }}" class="text-decoration-none">
                                {{ Str::limit(strip_tags($answer->question->question_text ?? ''), 50) }}
                            </a>
                        </td>
                        <td>{{ $answer->question->subject->name ?? '-' }}</td>
                        <td>
                            @if(($answer->question->difficulty ?? '') === 'easy')
                                <span class="badge bg-success">Easy</span>
                            @elseif(($answer->question->difficulty ?? '') === 'hard')
                                <span class="badge bg-danger">Hard</span>
                            @else
                                <span class="badge bg-warning text-dark">Medium</span>
                            @endif
                        </td>
                        <td>
                            @if($answer->option)
                                <span class="badge bg-light text-dark">{{ $answer->option->option_label }}</span> {{ Str::limit($answer->option->option_text, 30) }}
                            @else
                                {{ Str::limit($answer->student_answer, 40) }}
                            @endif
                        </td>
                        <td>
                            @if($answer->is_correct)
                                <span class="badge bg-success"><i class="fas fa-check"></i> Correct</span>
                            @else
                                <span class="badge bg-danger"><i class="fas fa-times"></i> Wrong</span>
                            @endif
                        </td>
                        <td>{{ $answer->attempt_number }}</td>
                        <td>{{ $answer->answered_at ? $answer->answered_at->format('M d, Y H:i') : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">No answers recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $answers->withQueryString()->links() }}</div>
</div>
@endsection
