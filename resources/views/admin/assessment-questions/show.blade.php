@extends('layouts.admin')

@section('title', 'Question Details')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.assessment-questions.index') }}">Self-Assessment</a></li>
                <li class="active">{{ $question->title ?? 'Question #'.$question->id }}</li>
            </ol></nav>
            <h1 class="modern-page-title">{{ $question->title ?? 'Question Details' }}</h1>
            <p class="modern-page-subtitle">
                @if($question->question_type === 'multiple_choice')
                    <span class="modern-badge modern-badge-info">Multiple Choice</span>
                @elseif($question->question_type === 'true_false')
                    <span class="modern-badge modern-badge-info" style="background:#e0e7ff;color:#3730a3">True / False</span>
                @else
                    <span class="modern-badge modern-badge-light">Short Answer</span>
                @endif
                @if($question->difficulty === 'easy')
                    <span class="modern-badge modern-badge-success">Easy</span>
                @elseif($question->difficulty === 'medium')
                    <span class="modern-badge modern-badge-warning">Medium</span>
                @else
                    <span class="modern-badge modern-badge-danger">Hard</span>
                @endif
                <span class="modern-badge modern-badge-light">{{ $question->marks }} mark(s)</span>
            </p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.assessment-questions.edit', $question->id) }}" class="btn-modern btn-modern-outline"><i class="fas fa-edit"></i> Edit</a>
            <a href="{{ route('admin.assessment-questions.index') }}" class="btn-modern btn-modern-ghost"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.25rem">
        {{-- Left Column --}}
        <div>
            {{-- Question Card --}}
            <div class="modern-card" style="margin-bottom:1.25rem">
                <div class="modern-card-header">
                    <div class="modern-card-header-left">
                        <i class="fas fa-question-circle" style="color:#7c3aed"></i>
                        <span class="modern-card-title">Question</span>
                    </div>
                </div>
                <div style="padding:1.5rem">
                    <div style="font-size:1.05rem;line-height:1.7;color:#1a1a2e;white-space:pre-wrap">{!! nl2br(e($question->question_text)) !!}</div>

                    @if($question->hint)
                    <div style="margin-top:1rem;padding:0.75rem 1rem;background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;font-size:0.9rem">
                        <strong style="color:#92400e"><i class="fas fa-lightbulb me-1"></i> Hint:</strong> {!! nl2br(e($question->hint)) !!}
                    </div>
                    @endif

                    {{-- Options --}}
                    @if($question->question_type !== 'short_answer')
                    <div style="margin-top:1.25rem">
                        @foreach($question->options as $option)
                        <div style="display:flex;align-items:center;gap:0.75rem;padding:0.6rem 0.75rem;margin-bottom:0.4rem;border-radius:8px;{{ $option->is_correct ? 'background:#d1fae5;border:1px solid #6ee7b7' : 'background:#f9fafb;border:1px solid #e5e7eb' }}">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;font-weight:700;font-size:0.8rem;{{ $option->is_correct ? 'background:#10b981;color:#fff' : 'background:#e5e7eb;color:#6b7280' }}">{{ $option->option_label }}</span>
                            <span style="flex:1;font-size:0.9rem">{{ $option->option_text }}</span>
                            @if($option->is_correct)
                            <span class="modern-badge modern-badge-success"><i class="fas fa-check me-1"></i>Correct</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Explanation Card --}}
            <div class="modern-card" style="margin-bottom:1.25rem">
                <div class="modern-card-header" style="background:linear-gradient(135deg,#0ea5e9,#2563eb)">
                    <div class="modern-card-header-left">
                        <i class="fas fa-lightbulb" style="color:#fff"></i>
                        <span class="modern-card-title" style="color:#fff">Post-Answer Explanation</span>
                    </div>
                </div>
                <div style="padding:1.5rem">
                    @if($question->explanation)
                    <h4 style="font-size:0.85rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem">Detailed Explanation</h4>
                    <div style="color:#374151;white-space:pre-wrap;line-height:1.7">{!! nl2br(e($question->explanation)) !!}</div>
                    @else
                    <p style="color:#9ca3af">No explanation provided.</p>
                    @endif

                    @if($question->worked_out_solution)
                    <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #e5e7eb">
                        <h4 style="font-size:0.85rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem">Worked-Out Solution</h4>
                        <div style="padding:1rem;background:#f9fafb;border-radius:8px;white-space:pre-wrap;font-size:0.9rem;line-height:1.7;color:#374151">{!! nl2br(e($question->worked_out_solution)) !!}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div>
            {{-- Details --}}
            <div class="modern-card" style="margin-bottom:1.25rem">
                <div class="modern-card-header">
                    <div class="modern-card-header-left">
                        <i class="fas fa-info-circle" style="color:#4361ee"></i>
                        <span class="modern-card-title">Details</span>
                    </div>
                </div>
                <div class="modern-detail-grid" style="padding:1rem 1.25rem">
                    <div class="modern-detail-item">
                        <div class="modern-detail-label">Subject</div>
                        <div class="modern-detail-value">{{ $question->subject->name ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-item">
                        <div class="modern-detail-label">Class</div>
                        <div class="modern-detail-value">{{ $question->classroom->name ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-item">
                        <div class="modern-detail-label">Section</div>
                        <div class="modern-detail-value">{{ $question->section->name ?? 'All' }}</div>
                    </div>
                    <div class="modern-detail-item">
                        <div class="modern-detail-label">Topic</div>
                        <div class="modern-detail-value">{{ $question->topic ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-item">
                        <div class="modern-detail-label">Created by</div>
                        <div class="modern-detail-value">{{ $question->teacher->full_name ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-item">
                        <div class="modern-detail-label">Created</div>
                        <div class="modern-detail-value">{{ $question->created_at->format('M d, Y') }}</div>
                    </div>
                    <div class="modern-detail-item">
                        <div class="modern-detail-label">Status</div>
                        <div class="modern-detail-value">
                            @if($question->is_active)
                            <span class="modern-badge modern-badge-success">Active</span>
                            @else
                            <span class="modern-badge modern-badge-light">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistics --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <div class="modern-card-header-left">
                        <i class="fas fa-chart-bar" style="color:#f59e0b"></i>
                        <span class="modern-card-title">Student Performance</span>
                    </div>
                </div>
                <div style="padding:1.25rem">
                    <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;font-size:0.9rem">
                        <span style="color:#6b7280">Total Attempts</span><strong>{{ $answerStats['total_attempts'] }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;font-size:0.9rem">
                        <span style="color:#6b7280">Correct Answers</span><strong style="color:#10b981">{{ $answerStats['correct_attempts'] }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:1rem;font-size:0.9rem">
                        <span style="color:#6b7280">Accuracy Rate</span>
                        <strong style="color:{{ $answerStats['accuracy_rate'] >= 70 ? '#10b981' : '#f59e0b' }}">{{ $answerStats['accuracy_rate'] }}%</strong>
                    </div>

                    {{-- Option Distribution --}}
                    @if(!empty($optionDistribution))
                    <div style="border-top:1px solid #e5e7eb;padding-top:0.75rem">
                        <h4 style="font-size:0.8rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem">Option Distribution</h4>
                        @foreach($optionDistribution as $label => $dist)
                        <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.4rem">
                            <span class="modern-badge {{ $dist['is_correct'] ? 'modern-badge-success' : 'modern-badge-light' }}" style="width:25px;justify-content:center">{{ $label }}</span>
                            <div style="flex:1;background:#e5e7eb;border-radius:4px;height:20px;overflow:hidden">
                                @php $pct = $answerStats['total_attempts'] > 0 ? ($dist['count'] / $answerStats['total_attempts']) * 100 : 0; @endphp
                                <div style="height:100%;border-radius:4px;background:{{ $dist['is_correct'] ? '#10b981' : '#9ca3af' }};width:{{ $pct }}%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.65rem;font-weight:600">{{ $dist['count'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media (max-width: 768px) {
        .modern-page > div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush
@endsection
