@extends('student.layout')

@section('title', 'Answer Question')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('student.assessment.index') }}">Self-Assessment</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.assessment.subject', $question->subject_id) }}">{{ $question->subject->name }}</a></li>
                    <li class="breadcrumb-item active">Question</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Question Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--primary); color: white;">
                    <span>
                        @if($question->difficulty === 'easy')
                            <span class="badge bg-light text-success">Easy</span>
                        @elseif($question->difficulty === 'medium')
                            <span class="badge bg-light text-warning">Medium</span>
                        @else
                            <span class="badge bg-light text-danger">Hard</span>
                        @endif
                        <span class="badge bg-light text-dark ms-1">{{ $question->marks }} mark(s)</span>
                    </span>
                    <span class="small">{{ $question->subject->name }}</span>
                </div>
                <div class="card-body">
                    @if($question->title)
                        <h5 class="mb-3">{{ $question->title }}</h5>
                    @endif

                    <div class="fs-5 mb-4">{!! nl2br(e($question->question_text)) !!}</div>

                    @if($question->hint)
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-lightbulb me-1"></i> <strong>Hint:</strong> {!! nl2br(e($question->hint)) !!}
                        </div>
                    @endif

                    {{-- Answer Form --}}
                    <form method="POST" action="{{ route('student.assessment.submit', $question->id) }}" id="answerForm">
                        @csrf

                        @if($question->question_type === 'multiple_choice')
                            <div class="mb-3">
                                <label class="form-label fw-bold">Choose your answer:</label>
                                @foreach($question->options as $option)
                                <div class="form-check mb-2 p-3 rounded border option-choice" style="cursor:pointer; transition: all 0.2s;">
                                    <input type="radio" name="option_id" value="{{ $option->id }}" id="opt_{{ $option->id }}" class="form-check-input" required>
                                    <label class="form-check-label w-100" for="opt_{{ $option->id }}" style="cursor:pointer;">
                                        <span class="badge bg-secondary me-2">{{ $option->option_label }}</span>
                                        {{ $option->option_text }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        @elseif($question->question_type === 'true_false')
                            <div class="mb-3">
                                <label class="form-label fw-bold">True or False?</label>
                                @foreach($question->options as $option)
                                <div class="form-check mb-2 p-3 rounded border option-choice" style="cursor:pointer; transition: all 0.2s;">
                                    <input type="radio" name="option_id" value="{{ $option->id }}" id="opt_{{ $option->id }}" class="form-check-input" required>
                                    <label class="form-check-label w-100" for="opt_{{ $option->id }}" style="cursor:pointer;">
                                        <strong>{{ $option->option_text }}</strong>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label fw-bold">Your Answer:</label>
                                <textarea name="student_answer" class="form-control" rows="4" required placeholder="Type your answer here..."></textarea>
                            </div>
                        @endif

                        <input type="hidden" name="time_spent" id="timeSpent" value="0">

                        <div class="d-grid">
                            <button type="submit" class="btn btn-lg btn-primary">
                                <i class="fas fa-paper-plane me-2"></i> Submit Answer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- If previously answered, show option to retake --}}
            @if($previousAnswer)
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i>
                You have already answered this question.
                <a href="{{ route('student.assessment.retake', $question->id) }}" class="btn btn-sm btn-outline-primary ms-2">
                    <i class="fas fa-redo me-1"></i> Try Again
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@section('scripts')
<script>
$(function() {
    // Timer
    var startTime = Date.now();
    setInterval(function() {
        var elapsed = Math.floor((Date.now() - startTime) / 1000);
        $('#timeSpent').val(elapsed);
    }, 1000);

    // Highlight selected option
    $('.option-choice').on('click', function() {
        $('.option-choice').removeClass('border-primary bg-primary bg-opacity-10');
        $(this).addClass('border-primary bg-primary bg-opacity-10');
        $(this).find('input[type=radio]').prop('checked', true);
    });
});
</script>
@endsection
