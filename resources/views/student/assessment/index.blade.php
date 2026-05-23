@extends('student.layout')

@section('title', 'Self-Assessment')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h4 class="mb-1"><i class="fas fa-brain me-2" style="color: var(--primary)"></i>Self-Assessment</h4>
            <p class="text-muted mb-0">Test your knowledge and learn from detailed explanations</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('student.assessment.progress') }}" class="btn btn-outline-primary">
                <i class="fas fa-chart-line me-1"></i> My Progress
            </a>
        </div>
    </div>

    {{-- Overall Stats --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-primary mb-0">{{ $overallStats['total_answers'] }}</h3>
                    <small class="text-muted">Questions Answered</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-success mb-0">{{ $overallStats['correct_answers'] }}</h3>
                    <small class="text-muted">Correct Answers</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="mb-0 {{ $overallStats['accuracy_rate'] >= 70 ? 'text-success' : 'text-warning' }}">{{ $overallStats['accuracy_rate'] }}%</h3>
                    <small class="text-muted">Accuracy Rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-info mb-0">{{ $overallStats['unique_questions_attempted'] }}</h3>
                    <small class="text-muted">Unique Questions</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Subjects --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-book-open me-2"></i>Subjects</h5>
        </div>
        <div class="card-body">
            @forelse($subjects as $subject)
            @php $stat = $subjectStats[$subject->id] ?? ['total' => 0, 'answered' => 0, 'correct' => 0, 'remaining' => 0, 'accuracy' => 0]; @endphp
            <a href="{{ route('student.assessment.subject', $subject->id) }}" class="text-decoration-none">
                <div class="card mb-3 border" style="border-left: 4px solid var(--primary) !important;">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-dark">{{ $subject->name }}</h6>
                                <div class="text-muted small">
                                    {{ $stat['total'] }} questions &bull;
                                    {{ $stat['answered'] }} answered &bull;
                                    {{ $stat['remaining'] }} remaining
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="mb-1">
                                    @if($stat['total'] > 0)
                                    <div class="progress" style="width:120px; height:8px; display:inline-block">
                                        <div class="progress-bar {{ $stat['accuracy'] >= 70 ? 'bg-success' : 'bg-warning' }}" style="width:{{ $stat['accuracy'] }}%"></div>
                                    </div>
                                    @endif
                                </div>
                                <span class="badge {{ $stat['accuracy'] >= 70 ? 'bg-success' : 'bg-warning' }}">{{ $stat['accuracy'] }}%</span>
                                @if($stat['remaining'] > 0)
                                <span class="badge bg-info ms-1">{{ $stat['remaining'] }} new</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            @empty
            <div class="text-center py-4 text-muted">
                <i class="fas fa-book fa-2x mb-2"></i>
                <p>No subjects with assessment questions available yet.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Recent Activity --}}
    @if($recentAttempts->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Question</th>
                            <th>Subject</th>
                            <th>Result</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentAttempts as $attempt)
                        <tr>
                            <td>
                                <a href="{{ route('student.assessment.show', $attempt->assessment_question_id) }}" class="text-decoration-none">
                                    {{ Str::limit(strip_tags($attempt->question->question_text ?? ''), 50) }}
                                </a>
                            </td>
                            <td>{{ $attempt->question->subject->name ?? '-' }}</td>
                            <td>
                                @if($attempt->is_correct)
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Correct</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Wrong</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $attempt->answered_at?->format('M d, H:i') ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
