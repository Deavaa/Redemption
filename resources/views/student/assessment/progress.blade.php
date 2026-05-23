@extends('student.layout')

@section('title', 'My Assessment Progress')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('student.assessment.index') }}">Self-Assessment</a></li>
                    <li class="breadcrumb-item active">My Progress</li>
                </ol>
            </nav>
            <h4 class="mb-0"><i class="fas fa-chart-line me-2" style="color: var(--primary)"></i>My Progress</h4>
        </div>
    </div>

    {{-- Overall Stats --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ $overallStats['total_answers'] }}</h3>
                    <small class="text-muted">Total Attempts</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ $overallStats['correct_answers'] }}</h3>
                    <small class="text-muted">Correct</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-danger">{{ $overallStats['incorrect_answers'] }}</h3>
                    <small class="text-muted">Incorrect</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="{{ $overallStats['accuracy_rate'] >= 70 ? 'text-success' : 'text-warning' }}">{{ $overallStats['accuracy_rate'] }}%</h3>
                    <small class="text-muted">Accuracy</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Per-Subject Progress --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-book me-2"></i>Subject Performance</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Attempted</th>
                            <th>Correct</th>
                            <th>Accuracy</th>
                            <th>Easy</th>
                            <th>Medium</th>
                            <th>Hard</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjectProgress as $sp)
                        <tr>
                            <td><strong>{{ $sp['subject']->name }}</strong></td>
                            <td>{{ $sp['stats']['total_answers'] }}</td>
                            <td>{{ $sp['stats']['correct_answers'] }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height:10px">
                                        <div class="progress-bar {{ $sp['stats']['accuracy_rate'] >= 70 ? 'bg-success' : 'bg-warning' }}" style="width:{{ $sp['stats']['accuracy_rate'] }}%"></div>
                                    </div>
                                    <span class="small">{{ $sp['stats']['accuracy_rate'] }}%</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ ($sp['difficulty']['easy']['rate'] ?? 0) >= 70 ? 'bg-success' : 'bg-warning' }}">
                                    {{ $sp['difficulty']['easy']['rate'] ?? 0 }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ ($sp['difficulty']['medium']['rate'] ?? 0) >= 70 ? 'bg-success' : 'bg-warning' }}">
                                    {{ $sp['difficulty']['medium']['rate'] ?? 0 }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ ($sp['difficulty']['hard']['rate'] ?? 0) >= 70 ? 'bg-success' : 'bg-warning' }}">
                                    {{ $sp['difficulty']['hard']['rate'] ?? 0 }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-3 text-muted">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
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
                            <th>Difficulty</th>
                            <th>Result</th>
                            <th>Attempt</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivity as $activity)
                        <tr class="{{ $activity->is_correct ? '' : 'table-danger' }}">
                            <td>
                                <a href="{{ route('student.assessment.show', $activity->assessment_question_id) }}" class="text-decoration-none">
                                    {{ Str::limit(strip_tags($activity->question->question_text ?? ''), 50) }}
                                </a>
                            </td>
                            <td>{{ $activity->question->subject->name ?? '-' }}</td>
                            <td>
                                @if(($activity->question->difficulty ?? '') === 'easy')
                                    <span class="badge bg-success">Easy</span>
                                @elseif(($activity->question->difficulty ?? '') === 'hard')
                                    <span class="badge bg-danger">Hard</span>
                                @else
                                    <span class="badge bg-warning text-dark">Medium</span>
                                @endif
                            </td>
                            <td>
                                @if($activity->is_correct)
                                    <span class="text-success"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="text-danger"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                            <td>#{{ $activity->attempt_number }}</td>
                            <td class="text-muted small">{{ $activity->answered_at?->format('M d, H:i') ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
