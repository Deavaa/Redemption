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
            {{-- SEB Status Banner --}}
            @if($question->isSebEnabled())
            <div class="alert {{ $question->isSebRequired() ? 'alert-warning' : 'alert-info' }} d-flex align-items-center mb-3" id="sebBanner">
                <i class="fas fa-shield-alt me-2"></i>
                <div>
                    @if($question->isSebRequired())
                        <strong>Safe Exam Browser Mode Active</strong> — This assessment is secured. Tab switching, copy/paste, and right-click are disabled.
                    @else
                        <strong>SEB Optional</strong> — Enhanced security is available. For the best experience, open this in Safe Exam Browser.
                        <a href="{{ route('assessment.seb-config', $question->id) }}" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="fas fa-download me-1"></i> Download SEB Config
                        </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Tab-switch warning overlay (for SEB-enabled questions) --}}
            @if($question->isSebEnabled())
            <div id="sebViolationOverlay" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(220,38,38,0.95); color:#fff; display:none; align-items:center; justify-content:center; flex-direction:column; text-align:center;">
                <i class="fas fa-exclamation-triangle fa-4x mb-3"></i>
                <h2>Assessment Violation Detected</h2>
                <p class="fs-5">You switched away from the assessment tab. This activity has been recorded.</p>
                <p id="violationCount" class="fs-4 fw-bold"></p>
                <button class="btn btn-lg btn-light mt-3" onclick="dismissViolation()">Return to Assessment</button>
            </div>
            @endif

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
                        @if($question->isSebEnabled())
                            <span class="badge bg-warning text-dark ms-1"><i class="fas fa-shield-alt me-1"></i>{{ $question->getSebModeLabel() }}</span>
                        @endif
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
                    <form method="POST" action="{{ route('student.assessment.submit', $question->id) }}" id="answerForm" autocomplete="off">
                        @csrf

                        @if($question->question_type === 'multiple_choice')
                            <div class="mb-3">
                                <label class="form-label fw-bold">Choose your answer:</label>
                                @foreach($question->options as $option)
                                <div class="form-check mb-2 p-3 rounded border option-choice" style="cursor:pointer; transition: all 0.2s;">
                                    <input type="radio" name="option_id" value="{{ $option->id }}" id="opt_{{ $option->id }}" class="form-check-input" required autocomplete="off">
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
                                    <input type="radio" name="option_id" value="{{ $option->id }}" id="opt_{{ $option->id }}" class="form-check-input" required autocomplete="off">
                                    <label class="form-check-label w-100" for="opt_{{ $option->id }}" style="cursor:pointer;">
                                        <strong>{{ $option->option_text }}</strong>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label fw-bold">Your Answer:</label>
                                <textarea name="student_answer" class="form-control" rows="4" required placeholder="Type your answer here..." autocomplete="off" spellcheck="{{ $question->seb_allow_spell_check ? 'true' : 'false' }}"></textarea>
                            </div>
                        @endif

                        <input type="hidden" name="time_spent" id="timeSpent" value="0">
                        <input type="hidden" name="seb_violations" id="sebViolations" value="0">

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

@endsection

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

    @if($question->isSebEnabled())
    // ===== SAFE EXAM BROWSER ANTI-CHEAT MEASURES =====

    var sebViolations = 0;

    // 1. Detect tab/window switching (visibility change)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            sebViolations++;
            $('#sebViolations').val(sebViolations);
            $('#violationCount').text('Violations: ' + sebViolations);
            if (sebViolations >= 3) {
                // After 3 violations, show persistent warning
                $('#sebViolationOverlay').css('display', 'flex');
            }
            // Log violation to server
            logSebViolation('tab_switch', sebViolations);
        }
    });

    // 2. Disable right-click context menu
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });

    // 3. Disable copy/paste/cut keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+A, Ctrl+P, Ctrl+S, Ctrl+U
        if (e.ctrlKey && [67, 86, 88, 65, 80, 83, 85].includes(e.keyCode)) {
            e.preventDefault();
            return false;
        }
        // F12 (DevTools)
        if (e.keyCode === 123) {
            e.preventDefault();
            return false;
        }
        // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C (DevTools)
        if (e.ctrlKey && e.shiftKey && [73, 74, 67].includes(e.keyCode)) {
            e.preventDefault();
            return false;
        }
    });

    // 4. Disable copy/paste events
    document.addEventListener('copy', function(e) { e.preventDefault(); });
    document.addEventListener('paste', function(e) { e.preventDefault(); });
    document.addEventListener('cut', function(e) { e.preventDefault(); });

    // 5. Disable text selection on non-input elements (CSS-based)
    document.body.style.userSelect = 'none';
    document.body.style.webkitUserSelect = 'none';
    document.body.style.mozUserSelect = 'none';
    document.body.style.msUserSelect = 'none';
    // Re-enable for form inputs
    $('input, textarea').css('user-select', 'text');

    // 6. Detect print attempt
    window.addEventListener('beforeprint', function(e) {
        e.preventDefault();
        alert('Printing is not allowed during this assessment.');
    });

    // 7. Detect window blur (losing focus to another app)
    window.addEventListener('blur', function() {
        sebViolations++;
        $('#sebViolations').val(sebViolations);
        $('#violationCount').text('Violations: ' + sebViolations);
        logSebViolation('window_blur', sebViolations);
        if (sebViolations >= 3) {
            $('#sebViolationOverlay').css('display', 'flex');
        }
    });

    // 8. Try to request full-screen mode for SEB-required questions
    @if($question->isSebRequired())
    try {
        var elem = document.documentElement;
        if (elem.requestFullscreen) {
            // Don't auto-request — require user gesture. Show a button instead.
            if (!document.fullscreenElement) {
                var fsBtn = document.createElement('button');
                fsBtn.innerHTML = '<i class="fas fa-expand me-2"></i> Enter Fullscreen Mode (Required)';
                fsBtn.className = 'btn btn-warning btn-lg w-100 mb-3';
                fsBtn.onclick = function() {
                    elem.requestFullscreen().then(function() {
                        fsBtn.remove();
                    }).catch(function() {});
                };
                $('#sebBanner').after(fsBtn);
                // Auto-enter fullscreen on first click anywhere (user gesture required)
                document.addEventListener('click', function autoFS() {
                    if (!document.fullscreenElement) {
                        elem.requestFullscreen().catch(function() {});
                    }
                    document.removeEventListener('click', autoFS);
                }, { once: true });
            }
        }
    } catch(e) {}

    // Re-enter fullscreen if exited accidentally
    document.addEventListener('fullscreenchange', function() {
        if (!document.fullscreenElement && !document.exitTriggered) {
            sebViolations++;
            $('#sebViolations').val(sebViolations);
            logSebViolation('fullscreen_exit', sebViolations);
        }
    });
    @endif

    @endif
});

// Log SEB violation to server
function logSebViolation(type, count) {
    try {
        fetch('{{ route("student.assessment.show", $question->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-SEB-Violation': 'true'
            },
            body: JSON.stringify({
                violation_type: type,
                violation_count: count,
                question_id: {{ $question->id }},
                timestamp: Date.now()
            })
        }).catch(function() {});
    } catch(e) {}
}

// Dismiss violation overlay
function dismissViolation() {
    document.getElementById('sebViolationOverlay').style.display = 'none';
    try {
        var elem = document.documentElement;
        if (elem.requestFullscreen && !document.fullscreenElement) {
            elem.requestFullscreen().catch(function() {});
        }
    } catch(e) {}
    document.body.style.overflow = '';
}
</script>
@endsection
