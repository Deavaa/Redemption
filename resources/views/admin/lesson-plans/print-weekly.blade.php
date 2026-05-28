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

        /* Daily breakdown table */
        .daily-breakdown { margin-top: 10px; }
        .daily-breakdown-title { font-size: 9px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: flex; align-items: center; gap: 4px; }
        .daily-table { width: 100%; border-collapse: collapse; font-size: 9px; margin-top: 4px; }
        .daily-table th { background: #eef2ff; padding: 4px 8px; text-align: left; font-weight: 600; color: #3730a3; border: 1px solid #c7d2fe; font-size: 8px; text-transform: uppercase; }
        .daily-table td { padding: 4px 8px; border: 1px solid #e5e7eb; vertical-align: top; color: #374151; }
        .daily-table tr:nth-child(even) td { background: #f8fafc; }
        .day-label { font-weight: 600; color: #4361ee; }

        /* Weekly schedule grid */
        .weekly-grid { margin-top: 10px; }
        .weekly-grid-title { font-size: 9px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: flex; align-items: center; gap: 4px; }
        .schedule-table { width: 100%; border-collapse: collapse; font-size: 9px; }
        .schedule-table th { background: #4361ee; color: #fff; padding: 6px 8px; text-align: center; font-weight: 600; border: 1px solid #3b5bdb; }
        .schedule-table td { padding: 6px 8px; border: 1px solid #e5e7eb; vertical-align: top; min-height: 40px; }
        .schedule-day-header { background: #eef2ff; font-weight: 600; color: #3730a3; text-align: center; width: 80px; }

        .follow-up-section { margin-top: 10px; padding-top: 8px; border-top: 1px dashed #e5e7eb; }
        .fu-item { padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 9px; }
        .fu-item:last-child { border-bottom: none; }
        .fu-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px; }
        .fu-date { font-weight: 600; color: #374151; }
        .fu-details { color: #6b7280; line-height: 1.4; }

        .signature-section { margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; page-break-inside: avoid; }
        .signature-box { text-align: center; }
        .signature-line { border-top: 1px solid #374151; margin-top: 40px; padding-top: 4px; font-size: 9px; color: #6b7280; }

        .empty-state { text-align: center; padding: 40px 20px; color: #9ca3af; }
        .empty-state i { font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.3; }

        .footer { margin-top: 20px; padding-top: 8px; border-top: 1px solid #d1d5db; display: flex; justify-content: space-between; font-size: 9px; color: #9ca3af; }

        /* Filter bar (hidden when printing) */
        .filter-bar { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; margin-bottom: 12px; }
        .filter-bar-title { font-size: 11px; font-weight: 700; color: #374151; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 8px; align-items: end; }
        .filter-group label { display: block; font-size: 9px; font-weight: 600; color: #6b7280; margin-bottom: 3px; text-transform: uppercase; }
        .filter-group select, .filter-group input { width: 100%; padding: 5px 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 10px; background: #fff; }
        .filter-actions { display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap; }

        .no-print { margin-bottom: 10px; }
        .btn-print { padding: 6px 16px; border: none; border-radius: 5px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
        .btn-blue { background: #4361ee; color: #fff; }
        .btn-blue:hover { background: #3b5bdb; }
        .btn-outline { background: #fff; color: #4361ee; border: 1px solid #4361ee; }
        .btn-outline:hover { background: #eef2ff; }
        .btn-green { background: #1b5e20; color: #fff; }
        .btn-green:hover { background: #2e7d32; }

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
    {{-- Controls (hidden when printing) --}}
    <div class="no-print">
        {{-- Filter Bar --}}
        <div class="filter-bar">
            <div class="filter-bar-title"><i class="fas fa-filter"></i> Select Filters for Weekly Print</div>
            <form method="GET" action="{{ route('admin.lesson-plans.print-weekly') }}" id="weeklyFilterForm">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label>Academic Year</label>
                        <select name="academic_year_id">
                            <option value="">All Years</option>
                            @foreach(\App\Models\AcademicYear::orderBy('name')->get() as $ay)
                            <option value="{{ $ay->id }}" {{ request('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Term</label>
                        <select name="term_id">
                            <option value="">All Terms</option>
                            @foreach(\App\Models\Term::orderBy('name')->get() as $t)
                            <option value="{{ $t->id }}" {{ request('term_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @unless($isTeacher)
                    <div class="filter-group">
                        <label>Teacher</label>
                        <select name="teacher_id">
                            <option value="">All Teachers</option>
                            @foreach(\App\Models\Teacher::orderBy('full_name')->get() as $t)
                            <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endunless
                    <div class="filter-group">
                        <label>Subject</label>
                        <select name="subject_id">
                            <option value="">All Subjects</option>
                            @foreach(\App\Models\Subject::active()->ordered()->get() as $s)
                            <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Class</label>
                        <select name="class_id">
                            <option value="">All Classes</option>
                            @foreach(\App\Models\ClassRoom::orderBy('name')->get() as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Section</label>
                        <select name="section_id">
                            <option value="">All Sections</option>
                            @foreach(\App\Models\Section::orderBy('name')->get() as $sec)
                            <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Week</label>
                        <select name="week_number">
                            <option value="">All Weeks</option>
                            @foreach($weeksQuery as $w)
                            <option value="{{ $w }}" {{ $weekNumber == $w ? 'selected' : '' }}>Week {{ $w }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-print btn-blue"><i class="fas fa-search"></i> Apply Filters</button>
                    <button type="button" class="btn-print btn-green" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                    <a href="{{ route('admin.lesson-plans.index') }}" class="btn-print btn-outline"><i class="fas fa-arrow-left"></i> Back to Lesson Plans</a>
                </div>
            </form>
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

    {{-- Weekly Schedule Grid (if multiple plans for different days) --}}
    @if($lessonPlans->count() > 1 && $weekNumber)
    <div class="weekly-grid">
        <div class="weekly-grid-title"><i class="fas fa-table" style="color:#4361ee"></i> Weekly Schedule Overview</div>
        <table class="schedule-table">
            <thead>
                <tr>
                    <th style="width:80px">Day</th>
                    <th>Subject / Title</th>
                    <th style="width:100px">Objectives</th>
                    <th style="width:80px">Activities</th>
                    <th style="width:60px">Duration</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $dayName)
                @php
                    $dayPlans = $lessonPlans->filter(function($p) use ($dayName) {
                        if ($p->lesson_date) {
                            return $p->lesson_date->format('l') === $dayName;
                        }
                        return false;
                    });
                @endphp
                <tr>
                    <td class="schedule-day-header">{{ $dayName }}</td>
                    @if($dayPlans->count() > 0)
                        <td>
                            @foreach($dayPlans as $dp)
                            <div style="margin-bottom:3px"><strong>{{ $dp->subject?->name ?? '' }}</strong>: {{ $dp->title }}</div>
                            @endforeach
                        </td>
                        <td>{{ Str::limit($dayPlans->first()->objectives, 80) }}</td>
                        <td>{{ Str::limit($dayPlans->first()->activities, 60) }}</td>
                        <td style="text-align:center">{{ $dayPlans->first()->duration_minutes }} min</td>
                    @else
                        <td style="color:#d1d5db;text-align:center" colspan="4">—</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

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
                    @if($plan->plan_type)
                    <div class="plan-info-item">
                        <div class="plan-info-label">Plan Type</div>
                        <div class="plan-info-value">{{ ucfirst($plan->plan_type) }}</div>
                    </div>
                    @endif
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

                {{-- Daily Breakdown (for weekly plan type) --}}
                @if($plan->plan_type === 'weekly' && is_array($plan->daily_breakdown) && count($plan->daily_breakdown) > 0)
                <div class="daily-breakdown">
                    <div class="daily-breakdown-title"><i class="fas fa-calendar-day" style="color:#8b5cf6"></i> Daily Breakdown</div>
                    <table class="daily-table">
                        <thead>
                            <tr>
                                <th style="width:80px">Day</th>
                                <th>Topic / Activity</th>
                                <th style="width:120px">Teaching Method</th>
                                <th style="width:120px">Resources</th>
                                <th style="width:80px">Assessment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plan->daily_breakdown as $day)
                            <tr>
                                <td class="day-label">{{ $day['day'] ?? $day['date'] ?? 'Day ' . $loop->iteration }}</td>
                                <td>{{ $day['topic'] ?? $day['activity'] ?? '-' }}</td>
                                <td>{{ $day['method'] ?? '-' }}</td>
                                <td>{{ $day['resources'] ?? $day['materials'] ?? '-' }}</td>
                                <td>{{ $day['assessment'] ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- Term Goals (for yearly plan type) --}}
                @if($plan->plan_type === 'yearly' && is_array($plan->term_goals) && count($plan->term_goals) > 0)
                <div class="daily-breakdown">
                    <div class="daily-breakdown-title"><i class="fas fa-flag" style="color:#10b981"></i> Term Goals</div>
                    <ul style="padding-left:18px; font-size:10px; color:#374151; line-height:1.6;">
                        @foreach($plan->term_goals as $goal)
                        <li>{{ is_string($goal) ? $goal : (is_array($goal) ? ($goal['goal'] ?? $goal['title'] ?? json_encode($goal)) : $goal) }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

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

        {{-- Signature Section --}}
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Prepared by (Teacher)</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Checked by (Dept. Head)</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Approved by (Principal)</div>
            </div>
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-file-alt"></i>
            <h3>No lesson plans found</h3>
            <p>Select filters above or choose a different week.</p>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <span>{{ $schoolName }} &mdash; Weekly Lesson Plan</span>
        <span>Page printed on {{ now()->format('F d, Y') }}</span>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>
