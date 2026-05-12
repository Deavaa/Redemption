@extends('layouts.admin')
@section('title', 'Mark Entry Details')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.mark-entries.index') }}">Mark Entries</a></li>
                    <li class="active">Entry #{{ $data->id }}</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Mark Entry Details</h1>
            <p class="modern-page-subtitle">Viewing assessment scores and mark breakdown</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.mark-entries.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
            <a href="{{ route('admin.mark-entries.edit', $data->id) }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-pen"></i>
                <span>Edit</span>
            </a>
        </div>
    </div>

    <div class="modern-detail-grid">
        {{-- Main Info Card --}}
        <div class="modern-card modern-detail-main">
            <div class="modern-detail-hero">
                <div class="modern-detail-hero-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="modern-detail-hero-info">
                    <h2 class="modern-detail-hero-title">Mark Entry #{{ $data->id }}</h2>
                    <div class="modern-detail-hero-badges">
                        @if($data->grade)
                            @php
                                $gradeClass = 'modern-badge-warning';
                                if (in_array($data->grade, ['A+', 'A', 'A-'])) $gradeClass = 'modern-badge-success';
                                elseif (in_array($data->grade, ['B+', 'B', 'B-'])) $gradeClass = 'modern-badge-blue';
                                elseif (in_array($data->grade, ['C+', 'C', 'C-'])) $gradeClass = 'modern-badge-warning';
                                elseif (in_array($data->grade, ['D'])) $gradeClass = 'modern-badge-orange';
                                else $gradeClass = 'modern-badge-danger';
                            @endphp
                            <span class="modern-badge {{ $gradeClass }}"><i class="fas fa-award"></i> Grade: {{ $data->grade }}</span>
                        @endif
                        @if($data->marks_obtained !== null && $data->max_marks !== null)
                            @php $percentage = $data->max_marks > 0 ? round(($data->marks_obtained / $data->max_marks) * 100, 1) : 0; @endphp
                            <span class="modern-badge modern-badge-blue"><i class="fas fa-percentage"></i> {{ $percentage }}%</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="modern-detail-body">
                {{-- Student & Exam Info --}}
                <div class="modern-detail-section-title">
                    <i class="fas fa-user-graduate"></i> Student & Exam Information
                </div>
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-user"></i> Student ID
                    </div>
                    <div class="modern-detail-value">{{ $data->student_id ?? '-' }}</div>
                </div>
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-file-alt"></i> Exam ID
                    </div>
                    <div class="modern-detail-value">{{ $data->exam_id ?? '-' }}</div>
                </div>
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-book"></i> Subject ID
                    </div>
                    <div class="modern-detail-value">{{ $data->subject_id ?? '-' }}</div>
                </div>
                @if($data->class_id)
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-chalkboard"></i> Class ID
                    </div>
                    <div class="modern-detail-value">{{ $data->class_id }}</div>
                </div>
                @endif
                @if($data->section_id)
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-layer-group"></i> Section ID
                    </div>
                    <div class="modern-detail-value">{{ $data->section_id }}</div>
                </div>
                @endif
                @if($data->academic_year_id)
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-calendar-alt"></i> Academic Year ID
                    </div>
                    <div class="modern-detail-value">{{ $data->academic_year_id }}</div>
                </div>
                @endif
                @if($data->term_id)
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-clock"></i> Term ID
                    </div>
                    <div class="modern-detail-value">{{ $data->term_id }}</div>
                </div>
                @endif

                {{-- Marks Breakdown --}}
                <div class="modern-detail-section-title">
                    <i class="fas fa-chart-bar"></i> Marks Breakdown
                </div>
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-star"></i> Marks Obtained
                    </div>
                    <div class="modern-detail-value">
                        @if($data->marks_obtained !== null)
                            <span class="modern-detail-highlight">{{ $data->marks_obtained }}</span>
                        @else
                            <span class="modern-muted">-</span>
                        @endif
                    </div>
                </div>
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-trophy"></i> Max Marks
                    </div>
                    <div class="modern-detail-value">{{ $data->max_marks ?? '-' }}</div>
                </div>

                {{-- CA Scores --}}
                @php $hasCa = false; foreach(['ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10','conduct','handwriting','creativity'] as $f) { if ($data->$f !== null) { $hasCa = true; break; } } @endphp
                @if($hasCa)
                <div class="modern-detail-section-title">
                    <i class="fas fa-tasks"></i> Continuous Assessment
                </div>
                <div class="modern-detail-marks-grid">
                    @foreach(['ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10'] as $field)
                        @if($data->$field !== null)
                        <div class="modern-detail-mark-item">
                            <span class="modern-detail-mark-label">{{ strtoupper($field) }}</span>
                            <span class="modern-detail-mark-value">{{ $data->$field }}</span>
                            <span class="modern-detail-mark-max">/5</span>
                        </div>
                        @endif
                    @endforeach
                    @if($data->conduct !== null)
                    <div class="modern-detail-mark-item">
                        <span class="modern-detail-mark-label">Conduct</span>
                        <span class="modern-detail-mark-value">{{ $data->conduct }}</span>
                        <span class="modern-detail-mark-max">/5</span>
                    </div>
                    @endif
                    @if($data->handwriting !== null)
                    <div class="modern-detail-mark-item">
                        <span class="modern-detail-mark-label">Handwriting</span>
                        <span class="modern-detail-mark-value">{{ $data->handwriting }}</span>
                        <span class="modern-detail-mark-max">/5</span>
                    </div>
                    @endif
                    @if($data->creativity !== null)
                    <div class="modern-detail-mark-item">
                        <span class="modern-detail-mark-label">Creativity</span>
                        <span class="modern-detail-mark-value">{{ $data->creativity }}</span>
                        <span class="modern-detail-mark-max">/10</span>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Exam Scores --}}
                @php $hasExam = false; foreach(['test1','test2','mid_term','final_exam'] as $f) { if ($data->$f !== null) { $hasExam = true; break; } } @endphp
                @if($hasExam)
                <div class="modern-detail-section-title">
                    <i class="fas fa-pen-alt"></i> Examination Scores
                </div>
                <div class="modern-detail-marks-grid">
                    @if($data->test1 !== null)
                    <div class="modern-detail-mark-item">
                        <span class="modern-detail-mark-label">Test 1</span>
                        <span class="modern-detail-mark-value">{{ $data->test1 }}</span>
                        <span class="modern-detail-mark-max">/10</span>
                    </div>
                    @endif
                    @if($data->test2 !== null)
                    <div class="modern-detail-mark-item">
                        <span class="modern-detail-mark-label">Test 2</span>
                        <span class="modern-detail-mark-value">{{ $data->test2 }}</span>
                        <span class="modern-detail-mark-max">/10</span>
                    </div>
                    @endif
                    @if($data->mid_term !== null)
                    <div class="modern-detail-mark-item">
                        <span class="modern-detail-mark-label">Mid-Term</span>
                        <span class="modern-detail-mark-value">{{ $data->mid_term }}</span>
                        <span class="modern-detail-mark-max">/20</span>
                    </div>
                    @endif
                    @if($data->final_exam !== null)
                    <div class="modern-detail-mark-item">
                        <span class="modern-detail-mark-label">Final Exam</span>
                        <span class="modern-detail-mark-value">{{ $data->final_exam }}</span>
                        <span class="modern-detail-mark-max">/30</span>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Totals --}}
                @if($data->ca_total !== null || $data->exam_total !== null || $data->grand_total !== null)
                <div class="modern-detail-section-title">
                    <i class="fas fa-calculator"></i> Totals
                </div>
                @if($data->ca_total !== null)
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-tasks"></i> CA Total
                    </div>
                    <div class="modern-detail-value">
                        <span class="modern-detail-highlight">{{ $data->ca_total }}</span>
                        <span class="modern-detail-dim">/30</span>
                    </div>
                </div>
                @endif
                @if($data->exam_total !== null)
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-pen-alt"></i> Exam Total
                    </div>
                    <div class="modern-detail-value">
                        <span class="modern-detail-highlight">{{ $data->exam_total }}</span>
                        <span class="modern-detail-dim">/70</span>
                    </div>
                </div>
                @endif
                @if($data->grand_total !== null)
                <div class="modern-detail-row modern-detail-row-total">
                    <div class="modern-detail-label">
                        <i class="fas fa-calculator"></i> Grand Total
                    </div>
                    <div class="modern-detail-value">
                        <span class="modern-detail-grand">{{ $data->grand_total }}</span>
                        <span class="modern-detail-dim">/100</span>
                    </div>
                </div>
                @endif
                @endif

                {{-- Remarks --}}
                @if($data->remarks)
                <div class="modern-detail-section-title">
                    <i class="fas fa-comment-dots"></i> Remarks
                </div>
                <div class="modern-detail-remarks">
                    {{ $data->remarks }}
                </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="modern-detail-sidebar">
            {{-- Quick Actions Card --}}
            <div class="modern-card">
                <div class="modern-card-header-simple">
                    <i class="fas fa-bolt"></i> Quick Actions
                </div>
                <div class="modern-quick-actions">
                    <a href="{{ route('admin.mark-entries.edit', $data->id) }}" class="modern-quick-action">
                        <i class="fas fa-pen"></i>
                        <span>Edit Entry</span>
                    </a>
                    <form method="POST" action="{{ route('admin.mark-entries.destroy', $data->id) }}" onsubmit="return confirm('Are you sure you want to delete this mark entry? This action cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="modern-quick-action modern-quick-action-danger">
                            <i class="fas fa-trash-alt"></i>
                            <span>Delete Entry</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Score Summary Card --}}
            @if($data->marks_obtained !== null && $data->max_marks !== null)
            <div class="modern-card">
                <div class="modern-card-header-simple">
                    <i class="fas fa-chart-pie"></i> Score Summary
                </div>
                <div class="modern-score-summary">
                    @php $pct = $data->max_marks > 0 ? round(($data->marks_obtained / $data->max_marks) * 100, 1) : 0; @endphp
                    <div class="modern-score-ring" data-percentage="{{ $pct }}">
                        <svg viewBox="0 0 36 36" class="modern-score-svg">
                            <path class="modern-score-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="modern-score-fill" stroke-dasharray="{{ $pct }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="modern-score-text">{{ $pct }}%</div>
                    </div>
                    <div class="modern-score-details">
                        <div class="modern-score-detail-row">
                            <span>Obtained</span>
                            <strong>{{ $data->marks_obtained }}</strong>
                        </div>
                        <div class="modern-score-detail-row">
                            <span>Maximum</span>
                            <strong>{{ $data->max_marks }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Timestamps Card --}}
            <div class="modern-card">
                <div class="modern-card-header-simple">
                    <i class="fas fa-clock"></i> Timestamps
                </div>
                <div class="modern-timestamps">
                    <div class="modern-timestamp">
                        <span class="modern-timestamp-label">Created</span>
                        <span class="modern-timestamp-value">{{ $data->created_at ? $data->created_at->format('M d, Y H:i') : '-' }}</span>
                    </div>
                    <div class="modern-timestamp">
                        <span class="modern-timestamp-label">Updated</span>
                        <span class="modern-timestamp-value">{{ $data->updated_at ? $data->updated_at->format('M d, Y H:i') : '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Modern Page Layout */
.modern-page { animation: fadeSlideIn 0.4s ease-out; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.modern-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-page-header-left { flex: 1; }

.modern-page-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0;
    letter-spacing: -0.5px;
}

.modern-page-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0.25rem 0 0;
}

.modern-page-header-right {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Breadcrumb */
.modern-breadcrumb ol {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0 0 0.5rem;
    gap: 0.5rem;
    font-size: 0.8rem;
    align-items: center;
}

.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Detail Grid */
.modern-detail-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 1.25rem;
    align-items: start;
}

/* Card */
.modern-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

/* Detail Hero */
.modern-detail-hero {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.75rem 2rem;
    background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
    border-bottom: 1px solid #e5e8ff;
}

.modern-detail-hero-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.modern-detail-hero-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0 0 0.5rem;
}

.modern-detail-hero-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Badges */
.modern-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.65rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}

.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-gold { background: #fefce8; color: #b45309; }
.modern-badge-blue { background: #eef2ff; color: #4361ee; }
.modern-badge-warning { background: #fefce8; color: #d97706; }
.modern-badge-orange { background: #fff7ed; color: #ea580c; }

/* Detail Body */
.modern-detail-body { padding: 0.5rem 0; }

.modern-detail-section-title {
    padding: 0.9rem 2rem 0.5rem;
    font-weight: 700;
    color: #4361ee;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.modern-detail-section-title:first-child { margin-top: 0; }

.modern-detail-section-title i { font-size: 0.8rem; }

.modern-detail-row {
    display: flex;
    padding: 0.9rem 2rem;
    border-bottom: 1px solid #f8f9fa;
    transition: background 0.15s;
}

.modern-detail-row:last-child { border-bottom: none; }
.modern-detail-row:hover { background: #fafbfc; }

.modern-detail-row-total {
    background: linear-gradient(135deg, #f8f9ff, #eef2ff);
    border-top: 2px solid #4361ee;
}

.modern-detail-label {
    width: 180px;
    flex-shrink: 0;
    font-weight: 600;
    color: #6b7280;
    font-size: 0.88rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-detail-label i { color: #9ca3af; font-size: 0.82rem; width: 16px; text-align: center; }

.modern-detail-value {
    color: #1a1a2e;
    font-size: 0.9rem;
}

.modern-detail-highlight {
    font-weight: 700;
    color: #4361ee;
    font-size: 1rem;
}

.modern-detail-grand {
    font-weight: 800;
    color: #1a1a2e;
    font-size: 1.3rem;
}

.modern-detail-dim {
    color: #9ca3af;
    font-size: 0.85rem;
    margin-left: 0.25rem;
}

.modern-muted { color: #d1d5db; }

/* Marks Grid */
.modern-detail-marks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 0.5rem;
    padding: 0.5rem 2rem 1rem;
}

.modern-detail-mark-item {
    background: #f9fafb;
    border-radius: 10px;
    padding: 0.6rem 0.75rem;
    text-align: center;
    border: 1px solid #f0f0f0;
    transition: border-color 0.2s;
}

.modern-detail-mark-item:hover { border-color: #4361ee; }

.modern-detail-mark-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 0.2rem;
}

.modern-detail-mark-value {
    display: block;
    font-size: 1.1rem;
    font-weight: 800;
    color: #1a1a2e;
}

.modern-detail-mark-max {
    display: block;
    font-size: 0.72rem;
    color: #9ca3af;
}

/* Remarks */
.modern-detail-remarks {
    padding: 0.75rem 2rem 1rem;
    color: #4b5563;
    font-size: 0.9rem;
    line-height: 1.6;
    background: #fafbfc;
    margin: 0 2rem 1rem;
    border-radius: 10px;
    border: 1px solid #f0f0f0;
}

/* Sidebar */
.modern-detail-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.modern-card-header-simple {
    padding: 1rem 1.25rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-card-header-simple i { color: #4361ee; font-size: 0.85rem; }

/* Quick Actions */
.modern-quick-actions {
    padding: 0.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.modern-quick-action {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.7rem 0.85rem;
    border-radius: 10px;
    color: #374151;
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 500;
    transition: all 0.15s;
    border: none;
    background: none;
    width: 100%;
    cursor: pointer;
    text-align: left;
}

.modern-quick-action i { color: #6b7280; width: 18px; text-align: center; }

.modern-quick-action:hover {
    background: #f3f4f6;
    color: #1a1a2e;
}

.modern-quick-action:hover i { color: #4361ee; }

.modern-quick-action-danger { color: #dc2626; }
.modern-quick-action-danger i { color: #f87171; }
.modern-quick-action-danger:hover { background: #fef2f2; color: #b91c1c; }
.modern-quick-action-danger:hover i { color: #dc2626; }

/* Score Summary */
.modern-score-summary {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.modern-score-ring {
    position: relative;
    width: 120px;
    height: 120px;
}

.modern-score-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.modern-score-bg {
    fill: none;
    stroke: #e5e7eb;
    stroke-width: 3;
}

.modern-score-fill {
    fill: none;
    stroke: #4361ee;
    stroke-width: 3;
    stroke-linecap: round;
    transition: stroke-dasharray 0.6s ease;
}

.modern-score-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 1.5rem;
    font-weight: 800;
    color: #1a1a2e;
}

.modern-score-details { width: 100%; }

.modern-score-detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.4rem 0;
    font-size: 0.85rem;
}

.modern-score-detail-row span { color: #6b7280; }
.modern-score-detail-row strong { color: #1a1a2e; }

/* Timestamps */
.modern-timestamps { padding: 0.85rem 1.25rem; }

.modern-timestamp {
    display: flex;
    justify-content: space-between;
    padding: 0.45rem 0;
}

.modern-timestamp + .modern-timestamp { border-top: 1px solid #f3f4f6; }

.modern-timestamp-label { color: #9ca3af; font-size: 0.82rem; }
.modern-timestamp-value { color: #374151; font-size: 0.82rem; font-weight: 500; }

/* Modern Button */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.35rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.25s;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
}

.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4);
    color: #fff;
}

.btn-modern-outline {
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
}

.btn-modern-outline:hover {
    border-color: #4361ee;
    color: #4361ee;
    background: #f8f9ff;
}

/* Responsive */
@media (max-width: 992px) {
    .modern-detail-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-page-title { font-size: 1.35rem; }
    .modern-detail-hero { padding: 1.25rem; flex-direction: column; text-align: center; }
    .modern-detail-hero-badges { justify-content: center; }
    .modern-detail-row { flex-direction: column; gap: 0.25rem; padding: 0.75rem 1.25rem; }
    .modern-detail-label { width: auto; }
    .modern-detail-marks-grid { padding: 0.5rem 1.25rem 1rem; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
    .modern-detail-section-title { padding: 0.75rem 1.25rem 0.5rem; }
    .modern-detail-remarks { margin: 0 1.25rem 1rem; }
}
</style>
@endpush
@endsection
