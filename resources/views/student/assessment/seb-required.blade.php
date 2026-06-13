@extends('student.layout')

@section('title', 'Safe Exam Browser Required')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #047857, #065F46);">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-shield-alt fa-2x"></i>
                        <div>
                            <h4 class="mb-0">Safe Exam Browser Required</h4>
                            <small>This assessment must be taken in Safe Exam Browser</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-warning mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>You cannot access this assessment from a regular browser.</strong>
                        Safe Exam Browser locks down your computer to ensure a fair testing environment.
                    </div>

                    <h5 class="mb-3"><i class="fas fa-book-open me-2 text-primary"></i> What is Safe Exam Browser?</h5>
                    <p>Safe Exam Browser (SEB) is a secure browser application that prevents cheating during online exams. It:</p>
                    <ul class="mb-4">
                        <li>Blocks access to other applications, websites, and files during the exam</li>
                        <li>Prevents copying, pasting, and screen capturing</li>
                        <li>Disables keyboard shortcuts and right-click menus</li>
                        <li>Runs in full-screen mode that cannot be exited without permission</li>
                    </ul>

                    <h5 class="mb-3"><i class="fas fa-download me-2 text-success"></i> How to Take This Assessment</h5>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="card border h-100 text-center p-3">
                                <div class="display-6 text-primary mb-2">1</div>
                                <h6>Download SEB</h6>
                                <p class="small text-muted">Install Safe Exam Browser on your computer if you haven't already.</p>
                                <a href="https://safeexambrowser.org/download.html" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i> Download SEB
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border h-100 text-center p-3">
                                <div class="display-6 text-success mb-2">2</div>
                                <h6>Download Config</h6>
                                <p class="small text-muted">Download the exam configuration file for this assessment.</p>
                                <a href="{{ route('assessment.seb-config', $question->id) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-download me-1"></i> Download .seb File
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border h-100 text-center p-3">
                                <div class="display-6 text-warning mb-2">3</div>
                                <h6>Open in SEB</h6>
                                <p class="small text-muted">Double-click the downloaded .seb file to open it in Safe Exam Browser.</p>
                                <span class="badge bg-info"><i class="fas fa-lock me-1"></i> Exam starts automatically</span>
                            </div>
                        </div>
                    </div>

                    @if($question->subject)
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-4"><strong>Subject:</strong></div>
                                <div class="col-sm-8">{{ $question->subject->name }}</div>
                            </div>
                            @if($question->difficulty)
                            <div class="row mt-2">
                                <div class="col-sm-4"><strong>Difficulty:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge {{ $question->difficulty === 'easy' ? 'bg-success' : ($question->difficulty === 'medium' ? 'bg-warning' : 'bg-danger') }}">
                                        {{ ucfirst($question->difficulty) }}
                                    </span>
                                </div>
                            </div>
                            @endif
                            <div class="row mt-2">
                                <div class="col-sm-4"><strong>Marks:</strong></div>
                                <div class="col-sm-8">{{ $question->marks }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="d-flex gap-2">
                        <a href="{{ route('assessment.seb-config', $question->id) }}" class="btn btn-lg btn-primary">
                            <i class="fas fa-download me-2"></i> Download SEB Config File
                        </a>
                        <a href="{{ route('student.assessment.index') }}" class="btn btn-lg btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Assessments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
