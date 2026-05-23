@extends('layouts.admin')

@section('title', 'Question Details')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Question Details</h3>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.assessment-questions.edit', $question->id) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <a href="{{ route('admin.assessment-questions.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

{{-- Question Info --}}
<div class="row">
    <div class="col-lg-8">
        {{-- Question --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Question</h5>
                <div>
                    @if($question->question_type === 'multiple_choice')
                        <span class="badge bg-primary">Multiple Choice</span>
                    @elseif($question->question_type === 'true_false')
                        <span class="badge bg-info">True / False</span>
                    @else
                        <span class="badge bg-secondary">Short Answer</span>
                    @endif
                    @if($question->difficulty === 'easy')
                        <span class="badge bg-success">Easy</span>
                    @elseif($question->difficulty === 'medium')
                        <span class="badge bg-warning text-dark">Medium</span>
                    @else
                        <span class="badge bg-danger">Hard</span>
                    @endif
                    <span class="badge bg-light text-dark">{{ $question->marks }} mark(s)</span>
                </div>
            </div>
            <div class="card-body">
                @if($question->title)
                    <h5>{{ $question->title }}</h5>
                @endif
                <div class="mb-3 fs-5">{!! nl2br(e($question->question_text)) !!}</div>

                @if($question->hint)
                    <div class="alert alert-warning">
                        <strong><i class="fas fa-lightbulb me-1"></i> Hint:</strong> {!! nl2br(e($question->hint)) !!}
                    </div>
                @endif

                {{-- Options --}}
                @if($question->question_type !== 'short_answer')
                <div class="mt-3">
                    @foreach($question->options as $option)
                    <div class="d-flex align-items-start mb-2 p-2 rounded {{ $option->is_correct ? 'bg-success bg-opacity-10 border border-success' : 'bg-light' }}">
                        <span class="badge {{ $option->is_correct ? 'bg-success' : 'bg-secondary' }} me-2 mt-1">{{ $option->option_label }}</span>
                        <span>{{ $option->option_text }}</span>
                        @if($option->is_correct)
                            <span class="ms-auto badge bg-success"><i class="fas fa-check me-1"></i>Correct</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Explanation --}}
        <div class="card mb-4 border-info">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Post-Answer Explanation</h5>
            </div>
            <div class="card-body">
                @if($question->explanation)
                    <h6>Detailed Explanation</h6>
                    <div class="mb-3">{!! nl2br(e($question->explanation)) !!}</div>
                @else
                    <p class="text-muted">No explanation provided.</p>
                @endif

                @if($question->worked_out_solution)
                    <hr>
                    <h6>Worked-Out Solution</h6>
                    <div class="p-3 bg-light rounded">
                        <pre class="mb-0" style="white-space:pre-wrap; font-family:inherit;">{!! nl2br(e($question->worked_out_solution)) !!}</pre>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Metadata --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Details</h5></div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Subject</td><td>{{ $question->subject->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Class</td><td>{{ $question->classroom->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Section</td><td>{{ $question->section->name ?? 'All' }}</td></tr>
                    <tr><td class="text-muted">Topic</td><td>{{ $question->topic ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Created by</td><td>{{ $question->teacher->full_name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Created</td><td>{{ $question->created_at->format('M d, Y') }}</td></tr>
                    <tr><td class="text-muted">Status</td><td>{{ $question->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' }}</td></tr>
                </table>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Student Performance</h5></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Attempts</span><strong>{{ $answerStats['total_attempts'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Correct Answers</span><strong class="text-success">{{ $answerStats['correct_attempts'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Accuracy Rate</span>
                    <strong class="{{ $answerStats['accuracy_rate'] >= 70 ? 'text-success' : 'text-warning' }}">
                        {{ $answerStats['accuracy_rate'] }}%
                    </strong>
                </div>

                {{-- Option Distribution --}}
                @if(!empty($optionDistribution))
                <hr>
                <h6>Option Distribution</h6>
                @foreach($optionDistribution as $label => $dist)
                <div class="d-flex align-items-center mb-1">
                    <span class="badge {{ $dist['is_correct'] ? 'bg-success' : 'bg-secondary' }} me-2" style="width:25px">{{ $label }}</span>
                    <div class="flex-grow-1">
                        <div class="progress" style="height:18px">
                            @php $pct = $answerStats['total_attempts'] > 0 ? ($dist['count'] / $answerStats['total_attempts']) * 100 : 0; @endphp
                            <div class="progress-bar {{ $dist['is_correct'] ? 'bg-success' : 'bg-secondary' }}" style="width:{{ $pct }}%">
                                {{ $dist['count'] }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
