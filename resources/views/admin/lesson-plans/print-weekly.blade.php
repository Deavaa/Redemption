<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Lesson Plan{{ $weekNumber ? ' — Week ' . $weekNumber : '' }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 11px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif; color: #1a1a2e; background: #fff; line-height: 1.4; }

        .page { max-width: 210mm; margin: 0 auto; padding: 10mm 12mm; }

        .header { text-align: center; border-bottom: 3px solid #4361ee; padding-bottom: 8px; margin-bottom: 12px; }
        .header-inner { display: flex; align-items: center; justify-content: center; gap: 12px; }
        .header-logo { height: 45px; width: auto; }
        .school-name { font-size: 16px; font-weight: 800; color: #1e40af; letter-spacing: 0.5px; }
        .report-title { font-size: 13px; font-weight: 700; color: #374151; margin-top: 2px; }
        .report-subtitle { font-size: 10px; color: #6b7280; margin-top: 2px; }

        .info-bar { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; padding: 8px 12px; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 6px; font-size: 10px; }
        .info-item { display: flex; align-items: center; gap: 4px; }
        .info-label { font-weight: 600; color: #3730a3; }
        .info-value { color: #374151; }

        .plan-card { margin-bottom: 18px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; page-break-inside: avoid; }
        .plan-card-header { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; }
        .plan-card-title { font-size: 12px; font-weight: 700; }
        .plan-card-meta { font-size: 9px; opacity: 0.9; display: flex; gap: 10px; }
        .plan-card-body { padding: 12px; }

        .plan-info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px dashed #e5e7eb; }
        .plan-info-item { font-size: 9px; }
        .plan-info-label { font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; }
        .plan-info-value { color: #1a1a2e; font-weight: 500; }

        .content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .content-section { }
        .content-section-full { grid-column: span 2; }
        .content-heading { font-size: 9px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: flex; align-items: center; gap: 4px; }
        .content-text { font-size: 10px; color: #374151; white-space: pre-wrap; line-height: 1.5; }

        .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: 600; }
        .badge-draft { background: #f3f4f6; color: #6b7280; }
        .badge-submitted { background: #dbeafe; color: #1d4ed8; }
        .badge-reviewed { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-revision { background: #fee2e2; color: #991b1b; }

        .follow-up-section { margin-top: 10px; padding-top: 8px; border-top: 1px dashed #e5e7eb; }
        .fu-item { padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 9px; }
        .fu-item:last-child { border-bottom: none; }
        .fu-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px; }
        .fu-date { font-weight: 600; color: #374151; }
        .fu-status { }
        .fu-details { color: #6b7280; line-height: 1.4; }

        .empty-state { text-align: center; padding: 40px 20px; color: #9ca3af; }
        .empty-state i { font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.3; }

        .footer { margin-top: 20px; padding-top: 8px; border-top: 1px solid #d1d5db; display: flex; justify-content: space-between; font-size: 9px; color: #9ca3af; }

        .no-print { display: flex; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; align-items: center; }
        .btn-print { padding: 6px 16px; border: none; border-radius: 5px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
        .btn-blue { background: #4361ee; color: #fff; }
        .btn-blue:hover { background: #3b5bdb; }
        .btn-outline { background: #fff; color: #4361ee; border: 1px solid #4361ee; }
        .btn-outline:hover { background: #eef2ff; }

        .week-nav { display: flex; align-items: center; gap: 6px; font-size: 11px; margin-left: auto; }
        .week-nav select { padding: 4px 8px; border: 1px solid #c7d2fe; border-radius: 4px; font-size: 11px; }

        @media print {
            .no-print { display: none !important; }
            .page { padding: 8mm 10mm; }
        }
    </style>
</head>
<body>
<div class="page">
    {{-- Print Controls (hidden when printing) --}}
    <div class="no-print">
        <button class="btn-print btn-blue" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        <a href="{{ route('admin.lesson-plans.index') }}" class="btn-print btn-outline"><i class="fas fa-arrow-left"></i> Back to Lesson Plans</a>
        <div class="week-nav">
            <label for="weekSelect"><i class="fas fa-calendar-week"></i> Week:</label>
            <select id="weekSelect" onchange="changeWeek(this.value)">
                <option value="">All Weeks</option>
                @foreach($weeksQuery as $w)
                <option value="{{ $w }}" {{ $weekNumber == $w ? 'selected' : '' }}>Week {{ $w }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Header --}}
    <div class="header">
        <div class="header-inner">
            @if($schoolLogo)<img src="{{ $schoolLogo }}" alt="Logo" class="header-logo">@endif
            <div>
                <div class="school-name">{{ $schoolName }}</div>
                <div class="report-title">Weekly Lesson Plan Detail{{ $weekNumber ? ' — Week ' . $weekNumber : '' }}</div>
                <div class="report-subtitle">
                    {{ $academicYear?->name ?? 'All Years' }}
                    @if($term) &mdash; {{ $term->name }}@endif
                </div>
            </div>
        </div>
    </div>

    {{-- Info Bar --}}
    <div class="info-bar">
        @if($teacher)<div class="info-item"><span class="info-label">Teacher:</span> <span class="info-value">{{ $teacher->full_name }}</span></div>@endif
        @if($subject)<div class="info-item"><span class="info-label">Subject:</span> <span class="info-value">{{ $subject->name }}</span></div>@endif
        @if($classRoom)<div class="info-item"><span class="info-label">Class:</span> <span class="info-value">{{ $classRoom->name }}{{ $section ? ' / '.$section->name : '' }}</span></div>@endif
        @if($weekNumber)<div class="info-item"><span class="info-label">Week:</span> <span class="info-value">{{ $weekNumber }}</span></div>@endif
        <div class="info-item"><span class="info-label">Plans:</span> <span class="info-value">{{ $lessonPlans->count() }}</span></div>
        <div class="info-item"><span class="info-label">Printed:</span> <span class="info-value">{{ now()->format('M d, Y H:i') }}</span></div>
    </div>

    {{-- Weekly Detail Cards --}}
    @if($lessonPlans->count() > 0)
        @foreach($lessonPlans as $plan)
        <div class="plan-card">
            <div class="plan-card-header">
                <div>
                    <div class="plan-card-title">{{ $plan->title }}</div>
                </div>
                <div class="plan-card-meta">
                    @if($plan->lesson_date)<span><i class="fas fa-calendar"></i> {{ $plan->lesson_date->format('M d, Y') }}</span>@endif
                    <span><i class="fas fa-clock"></i> {{ $plan->duration_minutes }} min</span>
                    <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff">{{ ucfirst($plan->status) }}</span>
                </div>
            </div>
            <div class="plan-card-body">
                {{-- Plan Info Grid --}}
                <div class="plan-info-grid">
                    @unless($isTeacher)
                    <div class="plan-info-item">
                        <div class="plan-info-label">Teacher</div>
                        <div class="plan-info-value">{{ $plan->teacher?->full_name ?? '-' }}</div>
                    </div>
                    @endunless
                    <div class="plan-info-item">
                        <div class="plan-info-label">Subject</div>
                        <div class="plan-info-value">{{ $plan->subject?->name ?? '-' }}</div>
                    </div>
                    <div class="plan-info-item">
                        <div class="plan-info-label">Class / Section</div>
                        <div class="plan-info-value">{{ $plan->classRoom?->name ?? '-' }}{{ $plan->section ? ' / '.$plan->section->name : '' }}</div>
                    </div>
                    <div class="plan-info-item">
                        <div class="plan-info-label">Week</div>
                        <div class="plan-info-value">Week {{ $plan->week_number }}</div>
                    </div>
                    <div class="plan-info-item">
                        <div class="plan-info-label">Status</div>
                        <div class="plan-info-value"><span class="badge badge-{{ $plan->status }}">{{ ucfirst($plan->status) }}</span></div>
                    </div>
                </div>

                {{-- Content Grid --}}
                <div class="content-grid">
                    <div class="content-section">
                        <div class="content-heading"><i class="fas fa-bullseye" style="color:#4361ee"></i> Learning Objectives</div>
                        <div class="content-text">{{ $plan->objectives ?: 'Not specified' }}</div>
                    </div>
                    <div class="content-section">
                        <div class="content-heading"><i class="fas fa-tools" style="color:#f59e0b"></i> Teaching Materials</div>
                        <div class="content-text">{{ $plan->materials ?: 'Not specified' }}</div>
                    </div>
                    <div class="content-section content-section-full">
                        <div class="content-heading"><i class="fas fa-tasks" style="color:#10b981"></i> Lesson Activities</div>
                        <div class="content-text">{{ $plan->activities ?: 'Not specified' }}</div>
                    </div>
                    <div class="content-section">
                        <div class="content-heading"><i class="fas fa-clipboard-check" style="color:#3b82f6"></i> Assessment Methods</div>
                        <div class="content-text">{{ $plan->assessment ?: 'Not specified' }}</div>
                    </div>
                    <div class="content-section">
                        <div class="content-heading"><i class="fas fa-pencil-alt" style="color:#ef4444"></i> Homework / Assignment</div>
                        <div class="content-text">{{ $plan->homework ?: 'Not specified' }}</div>
                    </div>
                    @if($plan->notes)
                    <div class="content-section content-section-full">
                        <div class="content-heading"><i class="fas fa-sticky-note" style="color:#6b7280"></i> Additional Notes</div>
                        <div class="content-text">{{ $plan->notes }}</div>
                    </div>
                    @endif
                </div>

                {{-- Follow-ups --}}
                @if($plan->followUps->count() > 0)
                <div class="follow-up-section">
                    <div class="content-heading" style="margin-bottom:6px"><i class="fas fa-clipboard-list" style="color:#10b981"></i> Follow-ups ({{ $plan->followUps->count() }})</div>
                    @foreach($plan->followUps as $fu)
                    <div class="fu-item">
                        <div class="fu-header">
                            <span class="fu-date">{{ $fu->follow_up_date?->format('M d, Y') }} &mdash; {{ $fu->followedUpBy?->name ?? '-' }}</span>
                            <span class="badge badge-{{ $fu->completion_status === 'completed' ? 'approved' : ($fu->completion_status === 'in_progress' ? 'submitted' : 'draft') }}">{{ ucfirst(str_replace('_', ' ', $fu->completion_status)) }}</span>
                        </div>
                        @if($fu->objectives_achieved)<div class="fu-details"><strong>Achieved:</strong> {{ $fu->objectives_achieved }}</div>@endif
                        @if($fu->challenges)<div class="fu-details"><strong>Challenges:</strong> {{ $fu->challenges }}</div>@endif
                        @if($fu->adjustments)<div class="fu-details"><strong>Adjustments:</strong> {{ $fu->adjustments }}</div>@endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endforeach
    @else
        <div class="empty-state">
            <i class="fas fa-file-alt"></i>
            <h3>No lesson plans found</h3>
            <p>Adjust the filters or select a different week.</p>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <span>{{ $schoolName }} &mdash; Weekly Lesson Plan</span>
        <span>Page printed on {{ now()->format('F d, Y') }}</span>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script>
function changeWeek(week) {
    const url = new URL(window.location.href);
    if (week) {
        url.searchParams.set('week_number', week);
    } else {
        url.searchParams.delete('week_number');
    }
    window.location.href = url.toString();
}
</script>
</body>
</html>
