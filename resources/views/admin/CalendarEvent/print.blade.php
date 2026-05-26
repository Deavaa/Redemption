<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Year Calendar{{ $academicYear ? ' — ' . $academicYear->name : '' }}</title>
    <style>
        /* ── Base Reset & Typography ────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 11px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            color: #1a1a2e;
            background: #fff;
            line-height: 1.35;
        }

        /* ── Page Layout ────────────────────────────────────────── */
        .page {
            max-width: 210mm;
            margin: 0 auto;
            padding: 12mm 14mm;
        }

        /* ── Header ─────────────────────────────────────────────── */
        .header {
            text-align: center;
            border-bottom: 3px solid #1b5e20;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-inner {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
        }
        .header-logo {
            height: 50px;
            width: auto;
        }
        .header-text {}
        .school-name {
            font-size: 18px;
            font-weight: 800;
            color: #1b5e20;
            letter-spacing: 0.5px;
        }
        .report-title {
            font-size: 14px;
            font-weight: 700;
            color: #b8860b;
            margin-top: 2px;
        }
        .report-subtitle {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }

        /* ── Filter Bar (screen only) ───────────────────────────── */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
            background: #f5f7f5;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        .filter-bar label {
            font-weight: 600;
            font-size: 10px;
            color: #333;
        }
        .filter-bar select {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 10px;
            color: #333;
        }
        .filter-bar .btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 12px;
            border-radius: 5px;
            border: none;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
        }
        .btn-filter { background: #1b5e20; }
        .btn-filter:hover { background: #2e7d32; }
        .btn-print { background: #b8860b; }
        .btn-print:hover { background: #d4a017; }
        .btn-back { background: #6c757d; }
        .btn-back:hover { background: #5a6268; }

        /* ── Legend ──────────────────────────────────────────────── */
        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
            padding: 6px 10px;
            background: #fafbfa;
            border-radius: 5px;
            border: 1px solid #e8e8e8;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 9px;
            font-weight: 600;
            color: #444;
        }
        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ── Month Grid ─────────────────────────────────────────── */
        .months-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }
        .month-box {
            border: 1px solid #c8e6c9;
            border-radius: 5px;
            overflow: hidden;
            background: #fff;
        }
        .month-header {
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
            color: #fff;
            text-align: center;
            padding: 3px 4px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .month-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .month-table th {
            background: #e8f5e9;
            color: #1b5e20;
            font-size: 7.5px;
            font-weight: 700;
            text-align: center;
            padding: 2px 1px;
            border-bottom: 1px solid #c8e6c9;
        }
        .month-table td {
            text-align: center;
            vertical-align: top;
            padding: 1px 2px;
            height: 26px;
            font-size: 8px;
            border: 1px solid #f0f0f0;
            position: relative;
        }
        .month-table td.empty {
            background: #fafafa;
        }
        .day-number {
            font-size: 7.5px;
            font-weight: 600;
            color: #333;
            display: block;
            line-height: 1;
        }
        .day-dots {
            display: flex;
            justify-content: center;
            gap: 1px;
            margin-top: 1px;
            flex-wrap: wrap;
        }
        .event-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .day-label {
            font-size: 5.5px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            display: block;
            line-height: 1.1;
        }
        .day-overflow {
            font-size: 5.5px;
            color: #999;
            line-height: 1;
        }
        .today-cell {
            background: #fff8e1 !important;
        }
        .today-cell .day-number {
            color: #b8860b;
            font-weight: 800;
        }

        /* ── Event List ─────────────────────────────────────────── */
        .event-list-section {
            margin-top: 10px;
        }
        .event-list-title {
            font-size: 12px;
            font-weight: 800;
            color: #1b5e20;
            border-bottom: 2px solid #1b5e20;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .event-month-group {
            margin-bottom: 10px;
        }
        .event-month-header {
            font-size: 10px;
            font-weight: 700;
            color: #2e7d32;
            background: #e8f5e9;
            padding: 3px 8px;
            border-radius: 3px;
            margin-bottom: 4px;
        }
        .event-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 3px 8px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 9px;
        }
        .event-row:last-child {
            border-bottom: none;
        }
        .event-date-col {
            width: 75px;
            flex-shrink: 0;
            color: #555;
            font-weight: 600;
        }
        .event-cat-col {
            width: 65px;
            flex-shrink: 0;
        }
        .event-cat-badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: 700;
            color: #fff;
        }
        .event-title-col {
            flex: 1;
            font-weight: 600;
            color: #1a1a2e;
        }
        .event-desc-col {
            flex: 1;
            color: #777;
            font-size: 8px;
        }

        /* ── Footer ─────────────────────────────────────────────── */
        .footer {
            margin-top: 14px;
            border-top: 1px solid #c8e6c9;
            padding-top: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8px;
            color: #888;
        }
        .footer-left {}
        .footer-right {
            text-align: right;
        }
        .no-events {
            text-align: center;
            padding: 40px 20px;
            color: #999;
            font-size: 12px;
        }

        /* ── Print Styles ───────────────────────────────────────── */
        @media print {
            html { font-size: 10px; }
            body { background: #fff; }
            .page { padding: 8mm 10mm; max-width: none; }
            .filter-bar { display: none !important; }
            .btn-print, .btn-back { display: none !important; }
            .month-box { box-shadow: none; }
            .month-header { background: #1b5e20 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .month-table th { background: #e8f5e9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .event-cat-badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .legend-dot { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .event-dot { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .today-cell { background: #fff8e1 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            /* Page break handling */
            .months-grid { page-break-inside: avoid; }
            .event-list-section { page-break-before: auto; }
            .event-month-group { page-break-inside: avoid; }

            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }

        /* ── Screen-only tweaks ─────────────────────────────────── */
        @media screen {
            .page {
                box-shadow: 0 2px 20px rgba(0,0,0,0.08);
                margin: 20px auto;
                border-radius: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        {{-- Header --}}
        <div class="header">
            <div class="header-inner">
                @if($schoolLogo)
                    <img src="{{ $schoolLogo }}" alt="School Logo" class="header-logo">
                @endif
                <div class="header-text">
                    <div class="school-name">{{ $schoolName }}</div>
                    <div class="report-title">Academic Year Calendar</div>
                    <div class="report-subtitle">
                        @if($academicYear)
                            {{ $academicYear->name }}
                            &middot;
                            {{ $academicYear->start_date->format('M d, Y') }} &mdash; {{ $academicYear->end_date->format('M d, Y') }}
                        @else
                            All Events
                        @endif
                        @if($branch)
                            &middot; {{ $branch->name }} Branch
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar (hidden when printing) --}}
        <div class="filter-bar">
            <form method="GET" action="{{ route('admin.calendar.print') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <label for="ay">Academic Year:</label>
                <select name="academic_year_id" id="ay">
                    <option value="">-- All --</option>
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ $academicYearId == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                    @endforeach
                </select>

                @if($branches->count() > 1)
                    <label for="br">Branch:</label>
                    <select name="branch_id" id="br">
                        <option value="">-- All Branches --</option>
                        @foreach($branches as $br)
                            <option value="{{ $br->id }}" {{ $branchId == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                        @endforeach
                    </select>
                @endif

                <button type="submit" class="btn btn-filter">Apply</button>
            </form>

            <div style="margin-left:auto; display:flex; gap:6px;">
                <button class="btn btn-print" onclick="window.print()">Print</button>
                <a href="{{ route('admin.calendar.index') }}" class="btn btn-back">Back to Calendar</a>
            </div>
        </div>

        {{-- Legend --}}
        <div class="legend">
            @foreach($categoryColors as $key => $color)
                <span class="legend-item">
                    <span class="legend-dot" style="background:{{ $color }}"></span>
                    {{ $categoryList[$key] ?? ucfirst($key) }}
                </span>
            @endforeach
        </div>

        {{-- Month Calendar Grid --}}
        @if(count($monthGrids) > 0)
            <div class="months-grid">
                @foreach($monthGrids as $monthKey => $grid)
                    <div class="month-box">
                        <div class="month-header">{{ $grid['name'] }}</div>
                        <table class="month-table">
                            <thead>
                                <tr>
                                    <th>Mon</th>
                                    <th>Tue</th>
                                    <th>Wed</th>
                                    <th>Thu</th>
                                    <th>Fri</th>
                                    <th>Sat</th>
                                    <th>Sun</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grid['weeks'] as $week)
                                    <tr>
                                        @foreach($week as $day)
                                            @php
                                                $dateKey = $day ? $day->format('Y-m-d') : null;
                                                $dayEvents = $dateKey && isset($eventsByDate[$dateKey]) ? $eventsByDate[$dateKey] : [];
                                                $isToday = $day && $day->isToday();
                                            @endphp
                                            <td class="{{ !$day ? 'empty' : '' }} {{ $isToday ? 'today-cell' : '' }}">
                                                @if($day)
                                                    <span class="day-number">{{ $day->day }}</span>
                                                    @if(count($dayEvents) > 0)
                                                        <div class="day-dots">
                                                            @foreach(array_slice($dayEvents, 0, 4) as $evt)
                                                                <span class="event-dot" style="background:{{ $evt->color }}" title="{{ $evt->title }}"></span>
                                                            @endforeach
                                                        </div>
                                                        @if(count($dayEvents) > 0)
                                                            <span class="day-label">{{ $dayEvents[0]->title }}</span>
                                                        @endif
                                                        @if(count($dayEvents) > 1)
                                                            <span class="day-overflow">+{{ count($dayEvents) - 1 }} more</span>
                                                        @endif
                                                    @endif
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-events">
                No calendar events found for the selected academic year.
            </div>
        @endif

        {{-- Full Event List by Month --}}
        @if($eventsByMonth->count() > 0)
            <div class="event-list-section">
                <div class="event-list-title">Event Details</div>
                @foreach($eventsByMonth as $monthKey => $monthEvents)
                    <div class="event-month-group">
                        <div class="event-month-header">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->format('F Y') }}
                            ({{ $monthEvents->count() }} event{{ $monthEvents->count() !== 1 ? 's' : '' }})
                        </div>
                        @foreach($monthEvents as $event)
                            <div class="event-row">
                                <div class="event-date-col">
                                    {{ $event->start_date->format('M d') }}
                                    @if($event->end_date && $event->end_date->format('Y-m-d') !== $event->start_date->format('Y-m-d'))
                                        &ndash; {{ $event->end_date->format('M d') }}
                                    @endif
                                    @if(!$event->is_all_day && $event->start_time)
                                        <br>{{ $event->start_time }}
                                    @endif
                                </div>
                                <div class="event-cat-col">
                                    <span class="event-cat-badge" style="background:{{ $event->color }}">{{ $categoryList[$event->category] ?? ucfirst($event->category) }}</span>
                                </div>
                                <div class="event-title-col">{{ $event->title }}</div>
                                <div class="event-desc-col">{{ $event->description }}</div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <div class="footer-left">
                Generated on {{ now()->format('F d, Y \a\t h:i A') }}
                @if($schoolAddress)
                    &middot; {{ $schoolAddress }}
                @endif
            </div>
            <div class="footer-right">
                @if($schoolPhone){{ $schoolPhone }} &middot; @endif
                @if($schoolEmail){{ $schoolEmail }}@endif
            </div>
        </div>
    </div>
</body>
</html>
