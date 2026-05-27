@extends('layouts.admin')

@section('title', 'Assessment Reports')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.assessment-questions.index') }}">Self-Assessment</a></li>
                <li class="active">Report</li>
            </ol></nav>
            <h1 class="modern-page-title">Assessment Reports</h1>
            <p class="modern-page-subtitle">Student performance on self-assessment questions</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.assessment-questions.index') }}" class="btn-modern btn-modern-ghost"><i class="fas fa-arrow-left"></i> Back to Questions</a>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue"><i class="fas fa-question-circle"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalQuestions }}</span>
                <span class="modern-stat-label">Total Questions</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-purple"><i class="fas fa-pen"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalAnswers }}</span>
                <span class="modern-stat-label">Total Attempts</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green"><i class="fas fa-check-circle"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0 }}%</span>
                <span class="modern-stat-label">Overall Accuracy</span>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:1.25rem">
        <div class="modern-card-header" style="padding:0.85rem 1.25rem">
            <div class="modern-card-header-left">
                <i class="fas fa-filter" style="color:var(--primary)"></i>
                <span class="modern-card-title" style="font-size:0.95rem">Filters</span>
            </div>
        </div>
        <div style="padding:1rem 1.25rem">
            <form method="GET" id="filterForm">
                <div class="modern-form-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:0.75rem">
                    <div class="modern-form-group">
                        <select name="subject_id" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="class_id" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Answers Table --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <span class="modern-card-title">Student Answers</span>
                <span class="modern-badge modern-badge-light">{{ $answers->total() }}</span>
            </div>
        </div>

        @if($answers->count() > 0)
        <div class="modern-table-wrapper">
            <table class="modern-table">
                <thead>
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
                    @foreach($answers as $answer)
                    <tr style="{{ !$answer->is_correct ? 'background:#fef2f2' : '' }}">
                        <td>{{ $answer->student->full_name ?? '-' }}</td>
                        <td>{{ $answer->student->classroom->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.assessment-questions.show', $answer->assessment_question_id) }}" style="color:inherit;text-decoration:none">
                                {{ Str::limit(strip_tags($answer->question->question_text ?? ''), 50) }}
                            </a>
                        </td>
                        <td>{{ $answer->question->subject->name ?? '-' }}</td>
                        <td class="td-center">
                            @if(($answer->question->difficulty ?? '') === 'easy')
                            <span class="modern-badge modern-badge-success">Easy</span>
                            @elseif(($answer->question->difficulty ?? '') === 'hard')
                            <span class="modern-badge modern-badge-danger">Hard</span>
                            @else
                            <span class="modern-badge modern-badge-warning">Medium</span>
                            @endif
                        </td>
                        <td>
                            @if($answer->option)
                            <span class="modern-badge modern-badge-light">{{ $answer->option->option_label }}</span> {{ Str::limit($answer->option->option_text, 30) }}
                            @else
                            {{ Str::limit($answer->student_answer, 40) }}
                            @endif
                        </td>
                        <td class="td-center">
                            @if($answer->is_correct)
                            <span class="modern-badge modern-badge-success"><i class="fas fa-check"></i> Correct</span>
                            @else
                            <span class="modern-badge modern-badge-danger"><i class="fas fa-times"></i> Wrong</span>
                            @endif
                        </td>
                        <td class="td-center">{{ $answer->attempt_number }}</td>
                        <td>{{ $answer->answered_at ? $answer->answered_at->format('M d, Y H:i') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="modern-pagination-wrapper">
            {{ $answers->withQueryString()->links() }}
        </div>
        @else
        <div class="modern-empty-state">
            <div class="modern-empty-icon"><i class="fas fa-chart-bar"></i></div>
            <h3>No answers recorded yet</h3>
            <p>Students need to answer assessment questions first.</p>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('#filterForm select').forEach(el => {
        el.addEventListener('change', () => document.getElementById('filterForm').submit());
    });
</script>
@endpush
@endsection
