@extends('layouts.admin')
@section('title', 'Promotion Preview')

@push('styles')
<style>
/* ===== PROMOTION PREVIEW - MODERN DESIGN ===== */
.prev-page { animation: prevFadeIn 0.4s ease-out; }
@keyframes prevFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

/* Page Header */
.prev-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem; }
.prev-header-left { flex: 1; }
.prev-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark, #1a1a2e); margin: 0; letter-spacing: -0.5px; }
.prev-subtitle { font-size: 0.88rem; color: var(--text-muted, #6c757d); margin: 0.25rem 0 0; }

/* Breadcrumb */
.prev-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.prev-breadcrumb li { color: #adb5bd; }
.prev-breadcrumb li a { color: var(--text-muted, #6c757d); text-decoration: none; transition: color 0.2s; }
.prev-breadcrumb li a:hover { color: #4361ee; }
.prev-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.prev-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Info Banner */
.prev-banner { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.75rem; color: #1e40af; font-weight: 500; font-size: 0.9rem; }
.prev-banner i { font-size: 1.2rem; flex-shrink: 0; }

/* Context Bar */
.prev-context { display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
.prev-context-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: var(--card-bg, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: 10px; font-size: 0.82rem; font-weight: 600; color: var(--text-dark, #1f2937); }
.prev-context-chip i { font-size: 0.85rem; color: #6366f1; }

/* Preview Table */
.prev-table-wrapper { overflow-x: auto; }
.prev-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
.prev-table thead th { padding: 10px 14px; text-align: left; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.3px; color: var(--text-muted, #6b7280); background: var(--bg, #f9fafb); border-bottom: 2px solid var(--border, #e5e7eb); white-space: nowrap; }
.prev-table tbody td { padding: 10px 14px; border-bottom: 1px solid var(--border, #f0f0f0); color: var(--text-dark, #1f2937); vertical-align: middle; }
.prev-table tbody tr { transition: background 0.15s; }
.prev-table tbody tr:hover { background: rgba(67,97,238,0.03); }

/* Student name cell */
.prev-student-cell { display: flex; align-items: center; gap: 8px; }
.prev-student-avatar { width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg,#6366f1,#818cf8); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; flex-shrink: 0; }

/* Status Badges */
.prev-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; gap: 4px; white-space: nowrap; }
.prev-badge-promoted { background: rgba(16,185,129,0.12); color: #10b981; }
.prev-badge-detained { background: rgba(239,68,68,0.12); color: #ef4444; }
.prev-badge-conditional { background: rgba(245,158,11,0.12); color: #f59e0b; }

/* Failing Subjects */
.prev-failing-subjects { display: flex; flex-wrap: wrap; gap: 4px; }
.prev-failing-tag { display: inline-block; padding: 2px 8px; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2); color: #ef4444; border-radius: 5px; font-size: 0.72rem; font-weight: 600; }

/* Summary Stats */
.prev-summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 1.25rem; }
.prev-summary-card { display: flex; flex-direction: column; padding: 16px 18px; background: var(--card-bg, #fff); border-radius: 14px; border: 1px solid var(--border, #e5e7eb); text-align: center; transition: all 0.2s; }
.prev-summary-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
.prev-summary-value { font-size: 1.5rem; font-weight: 800; }
.prev-summary-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }
.prev-summary-green .prev-summary-value { color: #10b981; }
.prev-summary-green .prev-summary-label { color: #34d399; }
.prev-summary-red .prev-summary-value { color: #ef4444; }
.prev-summary-red .prev-summary-label { color: #f87171; }
.prev-summary-yellow .prev-summary-value { color: #f59e0b; }
.prev-summary-yellow .prev-summary-label { color: #fbbf24; }
.prev-summary-blue .prev-summary-value { color: #6366f1; }
.prev-summary-blue .prev-summary-label { color: #818cf8; }

/* Action Bar */
.prev-action-bar { display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding: 1.25rem; background: var(--card-bg, #fff); border-radius: 14px; border: 1px solid var(--border, #e5e7eb); flex-wrap: wrap; gap: 1rem; }
.prev-action-bar-info { display: flex; align-items: center; gap: 0.5rem; font-size: 0.88rem; color: var(--text-muted, #6c757d); }
.prev-action-bar-info i { color: #f59e0b; }
.prev-action-buttons { display: flex; gap: 0.75rem; }

/* Conduct Score */
.prev-conduct { font-weight: 600; font-size: 0.85rem; }
.prev-conduct-good { color: #10b981; }
.prev-conduct-fair { color: #f59e0b; }
.prev-conduct-poor { color: #ef4444; }

/* Responsive */
@media (max-width: 768px) {
    .prev-header { flex-direction: column; align-items: stretch; }
    .prev-title { font-size: 1.25rem; }
    .prev-summary-grid { grid-template-columns: repeat(2, 1fr); }
    .prev-action-bar { flex-direction: column; align-items: stretch; }
    .prev-action-buttons { justify-content: stretch; }
}
</style>
@endpush

@section('content')
<div class="prev-page">
    {{-- Page Header --}}
    <div class="prev-header">
        <div class="prev-header-left">
            <nav aria-label="breadcrumb" class="prev-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.promotion.index') }}">Promotion</a></li>
                    <li class="active">Preview</li>
                </ol>
            </nav>
            <h1 class="prev-title">Promotion Preview</h1>
            <p class="prev-subtitle">Review recommendations before finalizing promotion decisions</p>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="prev-banner">
        <i class="fas fa-info-circle"></i>
        <span>Review the following promotion recommendations before processing. These are auto-generated based on your current promotion settings.</span>
    </div>

    {{-- Context Chips --}}
    <div class="prev-context">
        @if($class)
        <div class="prev-context-chip">
            <i class="fas fa-chalkboard"></i> {{ $class->name }}
        </div>
        @endif
        @if($academicYear)
        <div class="prev-context-chip">
            <i class="fas fa-calendar-alt"></i> {{ $academicYear->name }}
        </div>
        @endif
        @if($term)
        <div class="prev-context-chip">
            <i class="fas fa-list-ol"></i> {{ $term->name }}
        </div>
        @endif
        @if($promotionSetting)
        <div class="prev-context-chip">
            <i class="fas fa-cog"></i> {{ $promotionSetting->name }}
        </div>
        @endif
    </div>

    {{-- Preview Table --}}
    @if(count($previews) > 0)
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <div class="modern-form-section-icon modern-form-section-icon-blue" style="width:36px;height:36px;border-radius:10px;font-size:0.95rem;">
                    <i class="fas fa-eye"></i>
                </div>
                <h3 class="modern-card-title">Promotion Recommendations</h3>
            </div>
            <div style="font-size:0.82rem;color:var(--text-muted);">
                {{ count($previews) }} student(s)
            </div>
        </div>
        <div class="modern-card-body" style="padding:0;">
            <div class="prev-table-wrapper">
                <table class="prev-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Average %</th>
                            <th>Subjects Passed/Failed</th>
                            <th>Attendance %</th>
                            <th>Conduct</th>
                            <th>Recommended Status</th>
                            <th>Failing Subjects</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previews as $index => $preview)
                            @php
                                $student = $preview['student'];
                                $perf = $preview['performance'];
                                $status = $perf['recommended_status'] ?? 'pending';
                                $badgeClass = match($status) {
                                    'promoted' => 'prev-badge-promoted',
                                    'detained' => 'prev-badge-detained',
                                    'conditional' => 'prev-badge-conditional',
                                    default => 'prev-badge-conditional',
                                };
                                $statusIcon = match($status) {
                                    'promoted' => 'fa-check-circle',
                                    'detained' => 'fa-times-circle',
                                    'conditional' => 'fa-exclamation-circle',
                                    default => 'fa-question-circle',
                                };
                                $conduct = $perf['conduct'] ?? 'N/A';
                                $conductClass = match(true) {
                                    $conduct === 'Good' || $conduct === 'Excellent' => 'prev-conduct-good',
                                    $conduct === 'Fair' || $conduct === 'Average' => 'prev-conduct-fair',
                                    $conduct === 'Poor' => 'prev-conduct-poor',
                                    default => '',
                                };
                            @endphp
                            <tr>
                                <td style="font-weight:600;color:var(--text-muted);">{{ $index + 1 }}</td>
                                <td>
                                    <div class="prev-student-cell">
                                        <div class="prev-student-avatar">
                                            {{ strtoupper(substr($student->full_name ?? 'S', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight:600;">{{ $student->full_name ?? '' }}</div>
                                            <div style="font-size:0.72rem;color:var(--text-muted);">{{ $student->roll_number ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight:700;color:{{ ($perf['average'] ?? 0) >= 50 ? '#10b981' : '#ef4444' }};">
                                        {{ number_format($perf['average'] ?? 0, 1) }}%
                                    </span>
                                </td>
                                <td>
                                    <span style="color:#10b981;font-weight:600;">{{ $perf['subjects_passed'] ?? 0 }}</span>
                                    <span style="color:var(--text-muted);margin:0 2px;">/</span>
                                    <span style="color:#ef4444;font-weight:600;">{{ $perf['subjects_failed'] ?? 0 }}</span>
                                </td>
                                <td>
                                    @php
                                        $att = $perf['attendance_percentage'] ?? 0;
                                        $attColor = $att >= 80 ? '#10b981' : ($att >= 60 ? '#f59e0b' : '#ef4444');
                                    @endphp
                                    <span style="font-weight:600;color:{{ $attColor }};">{{ number_format($att, 1) }}%</span>
                                </td>
                                <td>
                                    <span class="prev-conduct {{ $conductClass }}">{{ $conduct }}</span>
                                </td>
                                <td>
                                    <span class="prev-badge {{ $badgeClass }}">
                                        <i class="fas {{ $statusIcon }}"></i>
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td>
                                    @if(!empty($perf['failing_subjects']))
                                        <div class="prev-failing-subjects">
                                            @foreach($perf['failing_subjects'] as $subject)
                                                <span class="prev-failing-tag">{{ $subject }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="color:var(--text-muted);font-size:0.82rem;">None</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    @php
        $promotedCount = collect($previews)->where('performance.recommended_status', 'promoted')->count();
        $detainedCount = collect($previews)->where('performance.recommended_status', 'detained')->count();
        $conditionalCount = collect($previews)->where('performance.recommended_status', 'conditional')->count();
        $totalStudents = count($previews);
    @endphp
    <div class="prev-summary-grid">
        <div class="prev-summary-card prev-summary-green">
            <div class="prev-summary-value">{{ $promotedCount }}</div>
            <div class="prev-summary-label">Promoted</div>
        </div>
        <div class="prev-summary-card prev-summary-red">
            <div class="prev-summary-value">{{ $detainedCount }}</div>
            <div class="prev-summary-label">Detained</div>
        </div>
        <div class="prev-summary-card prev-summary-yellow">
            <div class="prev-summary-value">{{ $conditionalCount }}</div>
            <div class="prev-summary-label">Conditional</div>
        </div>
        <div class="prev-summary-card prev-summary-blue">
            <div class="prev-summary-value">{{ $totalStudents }}</div>
            <div class="prev-summary-label">Total Students</div>
        </div>
    </div>

    {{-- Confirm Form --}}
    <div class="prev-action-bar">
        <div class="prev-action-bar-info">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Processing will finalize the promotion results. This action cannot be easily undone.</span>
        </div>
        <div class="prev-action-buttons">
            <a href="{{ route('admin.promotion.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.85rem;padding:0.55rem 1.2rem;">
                <i class="fas fa-times"></i> Cancel
            </a>
            <form method="POST" action="{{ route('admin.promotion.process') }}" id="confirmProcessForm" style="display:inline;">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $academicYear->id ?? '' }}">
                <input type="hidden" name="term_id" value="{{ $term->id ?? '' }}">
                <input type="hidden" name="class_id" value="{{ $class->id ?? '' }}">
                <input type="hidden" name="confirmed" value="1">
                <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.85rem;padding:0.55rem 1.5rem;" onclick="return confirm('Are you sure you want to confirm and process promotion for {{ $totalStudents }} students?')">
                    <i class="fas fa-check-circle"></i> Confirm & Process Promotion
                </button>
            </form>
        </div>
    </div>
    @else
    {{-- Empty State --}}
    <div style="text-align:center;padding:3rem 1.5rem;background:var(--card-bg,#fff);border-radius:14px;border:1px solid var(--border,#f0f0f0);">
        <i class="fas fa-clipboard-list" style="font-size:3rem;color:#d1d5db;margin-bottom:1rem;display:block;"></i>
        <p style="color:var(--text-muted,#9ca3af);font-size:0.95rem;margin:0;">No preview data available for the selected criteria.</p>
        <p style="color:#b0b8c4;font-size:0.82rem;margin-top:0.5rem;">Please go back and select a valid class and academic period.</p>
        <a href="{{ route('admin.promotion.index') }}" class="btn-modern btn-modern-outline" style="margin-top:1rem;font-size:0.82rem;">
            <i class="fas fa-arrow-left"></i> Back to Promotion
        </a>
    </div>
    @endif
</div>
@endsection
