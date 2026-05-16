@extends('parent.layout')

@section('title', 'Progress - ' . $student->first_name . ' ' . $student->last_name)

@section('content')
<div class="page-header">
    <div>
        <h4><i class="fas fa-chart-line me-2" style="color: var(--primary);"></i> Academic Progress</h4>
        <div class="page-header-sub">
            {{ $student->first_name }} {{ $student->last_name }}
            &bull; {{ $student->classroom->name ?? 'No Class' }}
            @if($student->section) &mdash; {{ $student->section->name }}@endif
        </div>
    </div>
    <a href="{{ route('parent.dashboard') }}" class="btn-modern btn-modern-outline">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

@if(!$activeAy)
<div class="empty-state">
    <i class="fas fa-calendar"></i>
    <h5>No Active Academic Year</h5>
    <p>There is no active academic year set. Please contact the school administration.</p>
</div>
@elseif(count($termAverages) === 0)
<div class="empty-state">
    <i class="fas fa-chart-bar"></i>
    <h5>No Progress Data</h5>
    <p>No marks have been recorded for this academic year yet.</p>
</div>
@else

{{-- Overview Stats --}}
<div class="stat-cards" style="grid-template-columns: repeat({{ min(count($termAverages), 4) }}, 1fr); margin-bottom: 20px;">
    @foreach($termAverages as $ta)
    <div class="stat-card">
        <div class="stat-icon {{ $ta['average'] >= 70 ? 'green' : ($ta['average'] >= 50 ? 'amber' : 'red') }}">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $ta['average'] }}%</h3>
            <p>{{ $ta['term']->name }} Average</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Visual Trend Bar Chart --}}
<div class="info-card" style="margin-bottom: 20px;">
    <div class="info-card-header">
        <h5><i class="fas fa-signal me-2" style="color: var(--primary);"></i> Term-by-Term Trend</h5>
        @if($activeAy)
            <span class="modern-badge modern-badge-light">{{ $activeAy->name }}</span>
        @endif
    </div>
    <div class="info-card-body" style="padding:0;">
        @foreach($termAverages as $ta)
        <div class="trend-item">
            <div class="trend-term-name">{{ $ta['term']->name }}</div>
            <div class="trend-bar-wrap">
                <div class="trend-bar" style="width: {{ min($ta['average'], 100) }}%;">
                    <span>{{ $ta['average'] }}%</span>
                </div>
            </div>
            <div class="trend-details">
                Highest: {{ $ta['highest'] }} &bull; Lowest: {{ $ta['lowest'] }}
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Detailed Table --}}
<div class="info-card">
    <div class="info-card-header">
        <h5><i class="fas fa-table me-2" style="color: var(--primary);"></i> Detailed Progress</h5>
    </div>
    <div class="info-card-body" style="padding:0; overflow-x:auto;">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Term</th>
                    <th style="text-align:center;">Subjects</th>
                    <th style="text-align:center;">Average Score</th>
                    <th style="text-align:center;">Highest Score</th>
                    <th style="text-align:center;">Lowest Score</th>
                    <th style="text-align:center;">Performance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($termAverages as $ta)
                <tr>
                    <td><strong>{{ $ta['term']->name }}</strong></td>
                    <td style="text-align:center;">{{ $ta['count'] }}</td>
                    <td style="text-align:center;">
                        <strong style="font-size:15px;">{{ $ta['average'] }}%</strong>
                    </td>
                    <td style="text-align:center; color:var(--success);">{{ $ta['highest'] }}</td>
                    <td style="text-align:center; color:var(--danger);">{{ $ta['lowest'] }}</td>
                    <td style="text-align:center;">
                        @if($ta['average'] >= 80)
                            <span class="modern-badge modern-badge-green"><i class="fas fa-arrow-up me-1"></i>Excellent</span>
                        @elseif($ta['average'] >= 70)
                            <span class="modern-badge modern-badge-green"><i class="fas fa-check me-1"></i>Good</span>
                        @elseif($ta['average'] >= 60)
                            <span class="modern-badge modern-badge-blue"><i class="fas fa-minus me-1"></i>Average</span>
                        @elseif($ta['average'] >= 50)
                            <span class="modern-badge modern-badge-amber"><i class="fas fa-exclamation me-1"></i>Below Avg</span>
                        @else
                            <span class="modern-badge modern-badge-red"><i class="fas fa-arrow-down me-1"></i>Poor</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endif
@endsection
