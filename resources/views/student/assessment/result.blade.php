@extends('student.layout')

@section('title', 'Answer Result')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Result Banner --}}
            @if(!empty($examMode))
                {{-- Exam mode: don't show correct/incorrect --}}
                <div class="card border-0 shadow-sm mb-4 border-start border-4 border-info">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-check fa-4x text-info"></i>
                        </div>
                        <h3 class="text-info">Answer Submitted</h3>
                        <p class="text-muted">Your answer has been recorded. Results will be available after the exam is reviewed by your teacher.</p>
                    </div>
                </div>
            @else
            <div class="card border-0 shadow-sm mb-4 {{ $isCorrect ? 'border-start border-4 border-success' : 'border-start border-4 border-danger' }}">
                <div class="card-body text-center py-5">
                    @if($isCorrect)
                        <div class="mb-3">
                            <i class="fas fa-check-circle fa-4x text-success"></i>
                        </div>
                        <h3 class="text-success">Correct!</h3>
                        <p class="text-muted">Great job! You got this question right.</p>
                    @else
                        <div class="mb-3">
                            <i class="fas fa-times-circle fa-4x text-danger"></i>
                        </div>
                        <h3 class="text-danger">Incorrect</h3>
                        <p class="text-muted">Don't worry! Read the explanation below to understand why.</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Question Review --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header" style="background: var(--primary); color: white;">
                    <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Question</h5>
                </div>
                <div class="card-body">
                    @if($question->title)
                        <h5 class="mb-2">{{ $question->title }}</h5>
                    @endif
                    <div class="fs-5 mb-3">{!! nl2br(e($question->question_text)) !!}</div>

                    {{-- Show all options with correct/incorrect marking (hidden in exam mode) --}}
                    @if(empty($examMode))
                    @if($question->question_type !== 'short_answer')
                    @foreach($question->options as $option)
                    <div class="d-flex align-items-start mb-2 p-3 rounded
                        {{ $option->is_correct ? 'bg-success bg-opacity-10 border border-success' : '' }}
                        {{ $selectedOptionId == $option->id && !$option->is_correct ? 'bg-danger bg-opacity-10 border border-danger' : '' }}">
                        <span class="badge {{ $option->is_correct ? 'bg-success' : ($selectedOptionId == $option->id ? 'bg-danger' : 'bg-secondary') }} me-2 mt-1">
                            {{ $option->option_label }}
                        </span>
                        <span>{{ $option->option_text }}</span>
                        @if($option->is_correct)
                            <span class="ms-auto"><i class="fas fa-check text-success"></i> Correct Answer</span>
                        @elseif($selectedOptionId == $option->id)
                            <span class="ms-auto"><i class="fas fa-times text-danger"></i> Your Answer</span>
                        @endif
                    </div>
                    @endforeach
                    @else
                    @if($studentAnswer)
                    <div class="p-3 rounded bg-light mb-2">
                        <strong>Your Answer:</strong> {{ $studentAnswer }}
                    </div>
                    @endif
                    @endif
                    @else
                    {{-- Exam mode: just show the student's answer, not which is correct --}}
                    @if($selectedOptionId)
                        @php $selOpt = $question->options->firstWhere('id', $selectedOptionId); @endphp
                        <div class="p-3 rounded bg-light mb-2">
                            <strong>Your Answer:</strong> {{ $selOpt?->option_text ?? '—' }}
                        </div>
                    @elseif($studentAnswer)
                        <div class="p-3 rounded bg-light mb-2">
                            <strong>Your Answer:</strong> {{ $studentAnswer }}
                        </div>
                    @endif
                    @endif
                </div>
            </div>

            {{-- Explanation (hidden in exam mode) --}}
            @if(empty($examMode))
            <div class="card border-0 shadow-sm mb-4 border border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Explanation</h5>
                </div>
                <div class="card-body">
                    @if($question->explanation)
                        <div class="mb-4">
                            <h6><i class="fas fa-book-reader me-1 text-info"></i> Why is this the correct answer?</h6>
                            <div class="p-3 bg-light rounded">
                                {!! nl2br(e($question->explanation)) !!}
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            No detailed explanation was provided for this question.
                        </div>
                    @endif

                    @if($question->worked_out_solution)
                        <div class="mt-3">
                            <h6><i class="fas fa-pencil-alt me-1 text-info"></i> Step-by-Step Worked Solution</h6>
                            <div class="p-3 bg-white border rounded" style="border-left: 4px solid #0dcaf0 !important;">
                                <pre style="white-space:pre-wrap; font-family:inherit; margin:0;">{!! nl2br(e($question->worked_out_solution)) !!}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif {{-- end examMode hide explanation --}}

            {{-- Action Buttons --}}
            <div class="d-flex justify-content-between">
                <a href="{{ route('student.assessment.subject', $question->subject_id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to {{ $question->subject->name }}
                </a>
                <div>
                    @if(empty($examMode))
                    <a href="{{ route('student.assessment.retake', $question->id) }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-redo me-1"></i> Try Again
                    </a>
                    @endif
                    <a href="{{ route('student.assessment.index') }}" class="btn btn-primary">
                        <i class="fas fa-home me-1"></i> All Subjects
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
