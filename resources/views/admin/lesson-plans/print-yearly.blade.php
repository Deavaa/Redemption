<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yearly Lesson Plan{{ $academicYear ? ' — ' . $academicYear->name : '' }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 11px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif; color: #1a1a2e; background: #fff; line-height: 1.4; }

        .page { max-width: 210mm; margin: 0 auto; padding: 10mm 12mm; }

        .header { text-align: center; border-bottom: 3px solid #1b5e20; padding-bottom: 8px; margin-bottom: 12px; }
        .header-inner { display: flex; align-items: center; justify-content: center; gap: 12px; }
        .header-logo { height: 45px; width: auto; }
        .school-name { font-size: 16px; font-weight: 800; color: #1b5e20; letter-spacing: 0.5px; }
        .report-title { font-size: 13px; font-weight: 700; color: #374151; margin-top: 2px; }
        .report-subtitle { font-size: 10px; color: #6b7280; margin-top: 2px; }

        .info-bar { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; padding: 8px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; font-size: 10px; }
        .info-item { display: flex; align-items: center; gap: 4px; }
        .info-label { font-weight: 600; color: #166534; }
        .info-value { color: #374151; }

        .term-goals-section { margin-bottom: 16px; padding: 10px 14px; border: 1px solid #bbf7d0; border-radius: 6px; background: #f0fdf4; page-break-inside: avoid; }
        .term-goals-title { font-size: 12px; font-weight: 700; color: #1b5e20; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
        .term-goals-list { padding-left: 18px; font-size: 10px; color: #374151; line-height: 1.6; }
        .term-goals-list li { margin-bottom: 3px; }
        .yearly-overview-text { font-size: 10px; color: #374151; line-height: 1.6; margin-top: 6px; }

        .week-section { margin-bottom: 16px; page-break-inside: avoid; }
        .week-header { display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; background: #1b5e20; color: #fff; border-radius: 4px 4px 0 0; }
        .week-title { font-size: 11px; font-weight: 700; }
        .week-meta { font-size: 9px; opacity: 0.9; }

        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        table th { background: #f0fdf4; padding: 5px 8px; text-align: left; font-weight: 600; color: #166534; border: 1px solid #d1d5db; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; }
        table td { padding: 5px 8px; border: 1px solid #e5e7eb; vertical-align: top; color: #374151; }
        table tr:nth-child(even) td { background: #fafbfc; }

        .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: 600; }
        .badge-draft { background: #f3f4f6; color: #6b7280; }
        .badge-submitted { background: #dbeafe; color: #1d4ed8; }
        .badge-reviewed { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-revision { background: #fee2e2; color: #991b1b; }

        .content-cell { max-width: 200px; white-space: pre-wrap; word-wrap: break-word; font-size: 9px; line-height: 1.4; max-height: 80px; overflow: hidden; }

        .daily-table { margin-top: 4px; width: 100%; font-size: 9px; }
        .daily-table th { background: #e8f5e9; font-size: 8px; padding: 3px 6px; }
        .daily-table td { padding: 3px 6px; font-size: 8.5px; }

        .signature-section { margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; page-break-inside: avoid; }
        .signature-box { text-align: center; }
        .signature-line { border-top: 1px solid #374151; margin-top: 40px; padding-top: 4px; font-size: 9px; color: #6b7280; }

        .footer { margin-top: 20px; padding-top: 8px; border-top: 1px solid #d1d5db; display: flex; justify-content: space-between; font-size: 9px; color: #9ca3af; }

        .empty-state { text-align: center; padding: 40px 20px; color: #9ca3af; }
        .empty-state i { font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.3; }

        /* Filter bar (hidden when printing) */
        .filter-bar { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; margin-bottom: 12px; }
        .filter-bar-title { font-size: 11px; font-weight: 700; color: #374151; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 8px; align-items: end; }
        .filter-group label { display: block; font-size: 9px; font-weight: 600; color: #6b7280; margin-bottom: 3px; text-transform: uppercase; }
        .filter-group select, .filter-group input { width: 100%; padding: 5px 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 10px; background: #fff; }
        .filter-actions { display: flex; gap: 6px; margin-top: 8px; }

        .no-print { margin-bottom: 10px; }
        .btn-print { padding: 6px 16px; border: none; border-radius: 5px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
        .btn-print-green { background: #1b5e20; color: #fff; }
        .btn-print-green:hover { background: #2e7d32; }
        .btn-print-outline { background: #fff; color: #1b5e20; border: 1px solid #1b5e20; }
        .btn-print-outline:hover { background: #f0fdf4; }
        .btn-print-blue { background: #4361ee; color: #fff; }
        .btn-print-blue:hover { background: #3b5bdb; }

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
            <div class="filter-bar-title"><i class="fas fa-filter"></i> Select Filters for Yearly Print</div>
            <form method="GET" action="{{ route('admin.lesson-plans.print-yearly') }}" id="yearlyFilterForm">
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
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-print btn-print-blue"><i class="fas fa-search"></i> Apply Filters</button>
                    <button type="button" class="btn-print btn-print-green" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                    <a href="{{ route('admin.lesson-plans.index') }}" class="btn-print btn-print-outline"><i class="fas fa-arrow-left"></i> Back to Lesson Plans</a>
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
                <div class="report-title">Yearly Lesson Plan Overview</div>
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
        <div class="info-item"><span class="info-label">Total Plans:</span> <span class="info-value">{{ $lessonPlans->count() }}</span></div>
        <div class="info-item"><span class="info-label">Printed:</span> <span class="info-value">{{ now()->format('M d, Y H:i') }}</span></div>
    </div>

    {{-- Yearly Overview Table --}}
    @if($lessonPlans->count() > 0)
        {{-- Term Goals (if any plan has them) --}}
        @php
            $firstPlanWithGoals = $lessonPlans->first(fn($p) => !empty($p->term_goals));
            $firstPlanWithOverview = $lessonPlans->first(fn($p) => !empty($p->yearly_overview));
        @endphp

        @if($firstPlanWithOverview)
        <div class="term-goals-section">
            <div class="term-goals-title"><i class="fas fa-globe"></i> Yearly Overview</div>
            <div class="yearly-overview-text">{{ $firstPlanWithOverview->yearly_overview }}</div>
        </div>
        @endif

        @if($firstPlanWithGoals)
        <div class="term-goals-section">
            <div class="term-goals-title"><i class="fas fa-bullseye"></i> Term Goals</div>
            <ul class="term-goals-list">
                @foreach($firstPlanWithGoals->term_goals as $goal)
                <li>{{ is_string($goal) ? $goal : (is_array($goal) ? ($goal['goal'] ?? $goal['title'] ?? json_encode($goal)) : $goal) }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @foreach($groupedPlans as $week => $plans)
        <div class="week-section">
            <div class="week-header">
                <span class="week-title"><i class="fas fa-calendar-week"></i> Week {{ $week }}
                    @if($plans->first()->week_start_date)
                        ({{ $plans->first()->week_start_date->format('M d') }}{{ $plans->first()->week_end_date ? ' - ' . $plans->first()->week_end_date->format('M d') : '' }})
                    @endif
                </span>
                <span class="week-meta">{{ $plans->count() }} plan(s) &middot; {{ $plans->where('status', 'approved')->count() }} approved</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width:30px">#</th>
                        <th style="width:160px">Title</th>
                        @unless($isTeacher)<th style="width:100px">Teacher</th>@endunless
                        <th style="width:80px">Subject</th>
                        <th style="width:60px">Date</th>
                        <th>Objectives</th>
                        <th style="width:70px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plans as $i => $plan)
                    <tr>
                        <td style="text-align:center">{{ $loop->iteration }}</td>
                        <td style="font-weight:600">{{ $plan->title }}</td>
                        @unless($isTeacher)<td>{{ $plan->teacher?->full_name ?? '-' }}</td>@endunless
                        <td>{{ $plan->subject?->name ?? '-' }}</td>
                        <td>{{ $plan->lesson_date?->format('M d') ?? '-' }}</td>
                        <td class="content-cell">{{ Str::limit($plan->objectives, 120) }}</td>
                        <td style="text-align:center"><span class="badge badge-{{ $plan->status }}">{{ ucfirst($plan->status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Daily Breakdown for weekly-type plans --}}
            @php $weeklyPlans = $plans->filter(fn($p) => $p->plan_type === 'weekly' && !empty($p->daily_breakdown)); @endphp
            @foreach($weeklyPlans as $wp)
            @if(is_array($wp->daily_breakdown) && count($wp->daily_breakdown) > 0)
            <table class="daily-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Topic / Activity</th>
                        <th>Method</th>
                        <th>Resources</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($wp->daily_breakdown as $day)
                    <tr>
                        <td>{{ $day['day'] ?? $day['date'] ?? ($loop->iteration) }}</td>
                        <td>{{ $day['topic'] ?? $day['activity'] ?? '-' }}</td>
                        <td>{{ $day['method'] ?? '-' }}</td>
                        <td>{{ $day['resources'] ?? $day['materials'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
            @endforeach
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
            <p>Select filters above and apply, or create lesson plans first.</p>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <span>{{ $schoolName }} &mdash; Yearly Lesson Plan</span>
        <span>Page printed on {{ now()->format('F d, Y') }}</span>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>
